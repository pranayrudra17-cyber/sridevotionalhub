<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OrderWhatsAppNotificationService
{
    /**
     * Queue WhatsApp after the HTTP response so order placement is not delayed.
     *
     * @param \App\Models\Order|null $order
     * @return void
     */
    public static function queueAfterResponse($order)
    {
        if ($order === null || empty($order->id)) {
            return;
        }

        $orderId = (int) $order->id;

        try {
            app()->terminating(function () use ($orderId) {
                try {
                    app(self::class)->sendForOrderId($orderId);
                } catch (\Throwable $e) {
                    Log::error('WhatsApp order notification failed', array(
                        'order_id' => $orderId,
                        'error' => $e->getMessage(),
                    ));
                }
            });
        } catch (\Throwable $e) {
            try {
                app(self::class)->sendForOrderId($orderId);
            } catch (\Throwable $inner) {
                Log::error('WhatsApp order notification failed', array(
                    'order_id' => $orderId,
                    'error' => $inner->getMessage(),
                ));
            }
        }
    }

    /**
     * @param int $orderId
     * @return void
     */
    public function sendForOrderId($orderId)
    {
        $twilio = app(TwilioWhatsAppService::class);

        try {
            if (!Schema::hasColumn('orders', 'whatsapp_notification_sent')) {
                Log::error('WhatsApp order notification skipped: whatsapp_notification_sent column is missing. Run migrations.');
                return;
            }

            $order = Order::with(array('user', 'orderDetails.product'))->find($orderId);
            if ($order === null) {
                Log::error('WhatsApp order notification skipped: order not found', array(
                    'order_id' => $orderId,
                ));

                return;
            }

            if (!$this->claimNotificationSlot($order->id)) {
                Log::info('WhatsApp order notification skipped: already sent or in progress', array(
                    'order_id' => $order->id,
                    'customer_id' => $order->user_id,
                ));

                return;
            }

            if (!$twilio->isConfigured()) {
                Log::error('WhatsApp order notification skipped: Twilio credentials missing', array(
                    'order_id' => $order->id,
                    'customer_id' => $order->user_id,
                ));
                $this->releaseNotificationSlot($order->id);

                return;
            }

            $phone = $this->resolveCustomerPhone($order);
            if ($phone === null || trim($phone) === '') {
                Log::error('WhatsApp order notification skipped: missing customer mobile number', array(
                    'order_id' => $order->id,
                    'customer_id' => $order->user_id,
                ));
                $this->releaseNotificationSlot($order->id);

                return;
            }

            $e164 = $twilio->normalizeToE164($phone);
            if ($e164 === null) {
                Log::error('WhatsApp order notification skipped: invalid customer mobile number', array(
                    'order_id' => $order->id,
                    'customer_id' => $order->user_id,
                    'phone_masked' => $twilio->maskPhone($phone),
                ));
                $this->releaseNotificationSlot($order->id);

                return;
            }

            $body = $this->formatMessage($order);
            $mediaUrl = $this->publicQrUrl($order);

            if ($mediaUrl === null) {
                Log::error('WhatsApp QR code URL could not be generated; sending text only', array(
                    'order_id' => $order->id,
                    'customer_id' => $order->user_id,
                ));
            }

            $sent = $twilio->sendWhatsApp($e164, $body, $mediaUrl);

            if ($sent) {
                Log::info('WhatsApp order notification sent', array(
                    'order_id' => $order->id,
                    'customer_id' => $order->user_id,
                    'phone_masked' => $twilio->maskPhone($e164),
                    'has_qr' => $mediaUrl !== null,
                ));

                return;
            }

            $this->releaseNotificationSlot($order->id);
            Log::error('WhatsApp order notification was not sent', array(
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'phone_masked' => $twilio->maskPhone($e164),
            ));
        } catch (\Throwable $e) {
            $this->releaseNotificationSlot($orderId);
            Log::error('WhatsApp order notification failed', array(
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ));
        }
    }

    /**
     * Atomically mark the order so concurrent payment callbacks cannot send twice.
     * The slot is released if sending does not succeed, allowing a later retry.
     *
     * @param int $orderId
     * @return bool
     */
    protected function claimNotificationSlot($orderId)
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::where('id', $orderId)->lockForUpdate()->first();
            if ($order === null) {
                return false;
            }

            if ((int) $order->whatsapp_notification_sent === 1) {
                return false;
            }

            $order->whatsapp_notification_sent = 1;
            $order->save();

            return true;
        });
    }

    /**
     * @param int $orderId
     * @return void
     */
    protected function releaseNotificationSlot($orderId)
    {
        try {
            Order::where('id', $orderId)->update(array('whatsapp_notification_sent' => 0));
        } catch (\Throwable $e) {
            Log::error('Unable to release WhatsApp notification slot', array(
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ));
        }
    }

    /**
     * @param \App\Models\Order $order
     * @return string|null
     */
    protected function resolveCustomerPhone(Order $order)
    {
        $shipping = json_decode($order->shipping_address);
        if (is_object($shipping) && !empty($shipping->phone)) {
            return $shipping->phone;
        }

        if ($order->user && !empty($order->user->phone)) {
            return $order->user->phone;
        }

        return null;
    }

    /**
     * @param \App\Models\Order $order
     * @return string
     */
    public function formatMessage(Order $order)
    {
        $shipping = json_decode($order->shipping_address);
        $customerName = 'Customer';
        if (is_object($shipping) && !empty($shipping->name)) {
            $customerName = $shipping->name;
        } elseif ($order->user && !empty($order->user->name)) {
            $customerName = $order->user->name;
        }

        $orderDate = '';
        if (!empty($order->date)) {
            $orderDate = date('d M Y, h:i A', (int) $order->date);
        } elseif ($order->created_at) {
            $orderDate = $order->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A');
        }

        $productLines = array();
        foreach ($order->orderDetails as $detail) {
            $name = translate('Product Unavailable');
            if ($detail->product) {
                $name = $detail->product->getTranslation('name');
            }
            if (!empty($detail->variation)) {
                $name .= ' (' . $detail->variation . ')';
            }
            $qty = (int) $detail->quantity;
            $productLines[] = '- ' . $name . ' x ' . $qty;
        }

        $productList = count($productLines) ? implode("\n", $productLines) : '-';

        $paymentStatus = ucwords(str_replace('_', ' ', (string) $order->payment_status));
        $total = $this->plainPrice($order->grand_total);
        $address = $this->formatAddress($shipping);

        return "Order Successfully Placed! \xF0\x9F\x8E\x89\n\n"
            . "Hello {$customerName},\n\n"
            . "Your order has been successfully placed.\n\n"
            . "Order No: {$order->code}\n"
            . "Order Date: {$orderDate}\n\n"
            . "Products:\n{$productList}\n\n"
            . "Total Amount: {$total}\n"
            . "Payment Status: {$paymentStatus}\n\n"
            . "Delivery Address:\n{$address}\n\n"
            . "Please keep the QR code sent with this message for order delivery/verification.\n\n"
            . "Thank you for your order!";
    }

    /**
     * @param mixed $shipping
     * @return string
     */
    protected function formatAddress($shipping)
    {
        if (!is_object($shipping)) {
            return 'Not available';
        }

        $parts = array();
        foreach (array('address', 'city', 'state', 'postal_code', 'country') as $field) {
            if (!empty($shipping->{$field})) {
                $parts[] = $shipping->{$field};
            }
        }

        if (count($parts) === 0) {
            return 'Not available';
        }

        return implode(', ', $parts);
    }

    /**
     * @param mixed $amount
     * @return string
     */
    protected function plainPrice($amount)
    {
        if (function_exists('single_price')) {
            return html_entity_decode(strip_tags(single_price($amount)), ENT_QUOTES, 'UTF-8');
        }

        return (string) $amount;
    }

    /**
     * Public URL Twilio can fetch. Uses the same QR payload as invoice/item scan.
     *
     * @param \App\Models\Order $order
     * @return string|null
     */
    public function publicQrUrl(Order $order)
    {
        try {
            $payload = $this->qrPayload($order);
            if ($payload === '') {
                return null;
            }

            $signature = ProductDeliveryQrService::whatsappQrSignature($order->id);
            $filename = $order->id . '-' . $signature . '.png';
            $directory = public_path('uploads/order-qr');
            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new \RuntimeException('Unable to create order QR directory.');
            }

            $absolutePath = $directory . DIRECTORY_SEPARATOR . $filename;
            if (!is_file($absolutePath)) {
                $png = ProductDeliveryQrService::pngBinary($payload);
                if (file_put_contents($absolutePath, $png) === false) {
                    throw new \RuntimeException('Unable to write order QR image.');
                }
            }

            if (function_exists('static_asset')) {
                return static_asset('uploads/order-qr/' . $filename);
            }

            return asset('uploads/order-qr/' . $filename);
        } catch (\Throwable $e) {
            Log::error('WhatsApp QR public URL failed', array(
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ));

            try {
                return route('order.whatsapp.qr', array(
                    'order' => $order->id,
                    'signature' => ProductDeliveryQrService::whatsappQrSignature($order->id),
                ));
            } catch (\Throwable $inner) {
                return null;
            }
        }
    }

    /**
     * Single-item orders use the product delivery token; multi-item orders use
     * the order code (same as the invoice QR).
     *
     * @param \App\Models\Order $order
     * @return string
     */
    public function qrPayload(Order $order)
    {
        $details = $order->relationLoaded('orderDetails')
            ? $order->orderDetails
            : $order->orderDetails()->get();

        if ($details->count() === 1) {
            return ProductDeliveryQrService::tokenForOrderDetail($details->first()->id);
        }

        return (string) $order->code;
    }
}
