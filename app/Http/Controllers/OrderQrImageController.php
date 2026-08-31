<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderWhatsAppNotificationService;
use App\Services\ProductDeliveryQrService;
use Illuminate\Support\Facades\Log;

class OrderQrImageController extends Controller
{
    /**
     * Public PNG for Twilio WhatsApp mediaUrl. Authenticated by HMAC, not session.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $order
     * @param string $signature
     * @return \Illuminate\Http\Response
     */
    public function show($order, $signature)
    {
        $orderId = (int) $order;
        $expected = ProductDeliveryQrService::whatsappQrSignature($orderId);

        if ($orderId < 1 || !hash_equals($expected, strtolower((string) $signature))) {
            abort(404);
        }

        $orderModel = Order::with('orderDetails')->find($orderId);
        if ($orderModel === null) {
            abort(404);
        }

        try {
            $payload = app(OrderWhatsAppNotificationService::class)->qrPayload($orderModel);
            $png = ProductDeliveryQrService::pngBinary($payload);
        } catch (\Throwable $e) {
            Log::error('Order WhatsApp QR image generation failed', array(
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ));
            abort(500);
        }

        return response($png, 200, array(
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=300',
        ));
    }
}
