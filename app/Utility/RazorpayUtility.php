<?php

namespace App\Utility;

use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\Order;
use App\Models\SellerPackage;
use App\Models\SellerPackagePayment;
use App\Models\Wallet;
use Illuminate\Support\Facades\Cache;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayUtility
{
    public static function api()
    {
        $key = env('RAZOR_KEY');
        $secret = env('RAZOR_SECRET');
        if (empty($key) || empty($secret)) {
            throw new \Exception('Razorpay is not configured.');
        }

        return new Api($key, $secret);
    }

    public static function currency()
    {
        return env('RAZOR_CURRENCY', 'INR');
    }

    public static function amountToPaise($amount)
    {
        return (int) round(((float) $amount) * 100);
    }

    /**
     * Create (or reuse) a Razorpay order. Amount is taken from the server, not the client.
     */
    public static function createOrder($amount, $receipt, $notes = array())
    {
        $amountPaise = self::amountToPaise($amount);
        if ($amountPaise < 100) {
            throw new \Exception('Invalid payment amount.');
        }

        $existingOrderId = isset($notes['razorpay_order_id']) ? $notes['razorpay_order_id'] : null;
        unset($notes['razorpay_order_id']);

        $api = self::api();

        if (!empty($existingOrderId)) {
            try {
                $existing = $api->order->fetch($existingOrderId);
                if ($existing && $existing['amount'] == $amountPaise && $existing['status'] != 'paid') {
                    return $existing;
                }
            } catch (\Exception $e) {
                // create a new order
            }
        }

        foreach ($notes as $key => $value) {
            if ($value === null) {
                unset($notes[$key]);
                continue;
            }
            $notes[$key] = (string) $value;
        }

        $payload = array(
            'receipt' => substr((string) $receipt, 0, 40),
            'amount' => $amountPaise,
            'currency' => self::currency(),
            'payment_capture' => 1,
            'notes' => $notes,
        );

        return $api->order->create($payload);
    }

    public static function persistRazorpayOrderOnCombinedOrder($combined_order_id, $razorpay_order_id)
    {
        $combined_order = CombinedOrder::find($combined_order_id);
        if ($combined_order == null) {
            return;
        }

        foreach ($combined_order->orders as $order) {
            if ($order->payment_status == 'paid') {
                continue;
            }
            $details = json_decode($order->payment_details, true);
            if (!is_array($details)) {
                $details = array();
            }
            $details['razorpay_order_id'] = $razorpay_order_id;
            $details['gateway_status'] = 'created';
            $order->payment_details = json_encode($details);
            $order->save();
        }
    }

    public static function verifyCheckoutSignature($razorpay_order_id, $razorpay_payment_id, $razorpay_signature)
    {
        if (empty($razorpay_order_id) || empty($razorpay_payment_id) || empty($razorpay_signature)) {
            return false;
        }

        try {
            self::api()->utility->verifyPaymentSignature(array(
                'razorpay_order_id' => $razorpay_order_id,
                'razorpay_payment_id' => $razorpay_payment_id,
                'razorpay_signature' => $razorpay_signature,
            ));
            return true;
        } catch (SignatureVerificationError $e) {
            return false;
        }
    }

    public static function verifyWebhookSignature($payload, $signature)
    {
        if (empty($payload) || empty($signature)) {
            return false;
        }

        $secret = env('RAZOR_WEBHOOK_SECRET');
        if (empty($secret)) {
            $secret = env('RAZOR_SECRET');
        }
        if (empty($secret)) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    /**
     * Fetch payment from Razorpay and ensure it matches the expected order and amount.
     */
    public static function fetchValidatedPayment($razorpay_payment_id, $expected_order_id = null, $expected_amount_paise = null)
    {
        if (empty($razorpay_payment_id)) {
            throw new \Exception('Missing Razorpay payment ID.');
        }

        $payment = self::api()->payment->fetch($razorpay_payment_id);
        if ($payment == null || empty($payment['id'])) {
            throw new \Exception('Unable to fetch Razorpay payment.');
        }

        if (!empty($expected_order_id)) {
            $paymentOrderId = isset($payment['order_id']) ? $payment['order_id'] : null;
            if ($paymentOrderId != $expected_order_id) {
                throw new \Exception('Razorpay payment does not belong to the expected order.');
            }
        }

        if ($expected_amount_paise !== null && (int) $payment['amount'] !== (int) $expected_amount_paise) {
            throw new \Exception('Razorpay payment amount does not match the order amount.');
        }

        $currency = isset($payment['currency']) ? $payment['currency'] : null;
        if ($currency != null && strtoupper($currency) != strtoupper(self::currency())) {
            throw new \Exception('Razorpay payment currency does not match.');
        }

        return $payment;
    }

    /**
     * Capture only when still authorized. Auto-captured payments are used as-is.
     */
    public static function ensureCaptured($payment)
    {
        $status = isset($payment['status']) ? $payment['status'] : '';

        if ($status === 'captured') {
            return $payment;
        }

        if ($status === 'authorized') {
            return self::api()->payment->fetch($payment['id'])->capture(array(
                'amount' => $payment['amount'],
                'currency' => isset($payment['currency']) ? $payment['currency'] : self::currency(),
            ));
        }

        throw new \Exception('Razorpay payment is not successful. Status: ' . $status);
    }

    public static function paymentDetailsJson($payment)
    {
        return json_encode(array(
            'id' => $payment['id'],
            'razorpay_payment_id' => $payment['id'],
            'razorpay_order_id' => isset($payment['order_id']) ? $payment['order_id'] : null,
            'method' => isset($payment['method']) ? $payment['method'] : null,
            'amount' => $payment['amount'],
            'currency' => isset($payment['currency']) ? $payment['currency'] : self::currency(),
            'status' => isset($payment['status']) ? $payment['status'] : 'captured',
            'gateway_status' => 'paid',
        ));
    }

    public static function notes($payment)
    {
        $notes = isset($payment['notes']) ? $payment['notes'] : array();
        if (is_object($notes) && method_exists($notes, 'toArray')) {
            $notes = $notes->toArray();
        }
        if (!is_array($notes)) {
            $notes = (array) $notes;
        }
        return $notes;
    }

    public static function alreadyProcessed($razorpay_payment_id)
    {
        return Cache::get(self::processedCacheKey($razorpay_payment_id)) === '1';
    }

    public static function markProcessed($razorpay_payment_id)
    {
        Cache::put(self::processedCacheKey($razorpay_payment_id), '1', now()->addDays(30));
    }

    protected static function processedCacheKey($razorpay_payment_id)
    {
        return 'razorpay_processed_' . $razorpay_payment_id;
    }

    /**
     * Apply a verified, captured Razorpay payment to the matching local order/wallet/package.
     * Idempotent: paid records are never downgraded; duplicate callbacks do not double-credit.
     */
    public static function fulfillCapturedPayment($payment, $fallback_context = array())
    {
        $payment = self::ensureCaptured($payment);
        $notes = self::notes($payment);
        if (!empty($fallback_context['combined_order_id']) && !empty($notes['combined_order_id'])
            && (string) $fallback_context['combined_order_id'] !== (string) $notes['combined_order_id']) {
            throw new \Exception('Razorpay payment does not belong to the expected order.');
        }

        $context = $notes;
        if (empty($context['payment_type'])) {
            if (!empty($payment['order_id'])) {
                $localOrder = Order::where('payment_details', 'like', '%' . $payment['order_id'] . '%')->first();
                if ($localOrder) {
                    $context['payment_type'] = 'cart_payment';
                    $context['combined_order_id'] = $localOrder->combined_order_id;
                }
            }
        }
        if (empty($context['payment_type'])) {
            $context = array_merge($fallback_context, $notes);
        }
        if (!empty($fallback_context['combined_order_id']) && !empty($context['combined_order_id'])
            && (string) $fallback_context['combined_order_id'] !== (string) $context['combined_order_id']) {
            throw new \Exception('Razorpay payment does not belong to the expected order.');
        }
        if (!empty($fallback_context['combined_order_id']) && empty($notes['combined_order_id']) && !empty($payment['order_id'])) {
            $belongs = Order::where('combined_order_id', $fallback_context['combined_order_id'])
                ->where('payment_details', 'like', '%' . $payment['order_id'] . '%')
                ->exists();
            if (!$belongs) {
                throw new \Exception('Razorpay payment does not belong to the expected order.');
            }
        }

        $payment_details = self::paymentDetailsJson($payment);
        $payment_id = $payment['id'];

        $payment_type = isset($context['payment_type']) ? $context['payment_type'] : null;

        $lockKey = 'razorpay_lock_' . $payment_id;
        if (self::alreadyProcessed($payment_id)) {
            return array(
                'ok' => true,
                'duplicate' => true,
                'payment_details' => $payment_details,
                'payment_type' => $payment_type,
            );
        }
        if (!Cache::add($lockKey, '1', now()->addMinutes(5))) {
            if ($payment_type == 'cart_payment' && !empty($context['combined_order_id'])) {
                $combined_order = CombinedOrder::find($context['combined_order_id']);
                $paid = $combined_order && $combined_order->orders->every(function ($order) {
                    return $order->payment_status == 'paid';
                });
                if ($paid) {
                    return array(
                        'ok' => true,
                        'duplicate' => true,
                        'payment_details' => $payment_details,
                        'payment_type' => $payment_type,
                    );
                }
            }
            if (self::walletAlreadyCredited($payment_id) || self::sellerPackageAlreadyPaid($payment_id)) {
                return array(
                    'ok' => true,
                    'duplicate' => true,
                    'payment_details' => $payment_details,
                    'payment_type' => $payment_type,
                );
            }
            throw new \Exception('Payment is being processed. Please retry shortly.');
        }

        try {
        if ($payment_type == 'cart_payment') {
            $combined_order_id = isset($context['combined_order_id']) ? $context['combined_order_id'] : null;
            if (empty($combined_order_id)) {
                throw new \Exception('Missing combined order ID.');
            }
            $combined_order = CombinedOrder::find($combined_order_id);
            if ($combined_order == null) {
                throw new \Exception('Order not found.');
            }
            $expected = self::amountToPaise($combined_order->grand_total);
            if ((int) $payment['amount'] !== $expected) {
                throw new \Exception('Razorpay payment amount does not match the order amount.');
            }
            checkout_done($combined_order_id, $payment_details);
        } elseif ($payment_type == 'wallet_payment') {
            $user_id = isset($context['user_id']) ? $context['user_id'] : null;
            if (empty($user_id)) {
                throw new \Exception('Missing user ID.');
            }
            if (self::walletAlreadyCredited($payment_id)) {
                self::markProcessed($payment_id);
                return array(
                    'ok' => true,
                    'duplicate' => true,
                    'payment_details' => $payment_details,
                    'payment_type' => $payment_type,
                );
            }
            $amount = $payment['amount'] / 100;
            wallet_payment_done($user_id, $amount, 'Razorpay', $payment_details);
        } elseif ($payment_type == 'customer_package_payment') {
            $user_id = isset($context['user_id']) ? $context['user_id'] : null;
            $package_id = isset($context['package_id']) ? $context['package_id'] : (isset($context['customer_package_id']) ? $context['customer_package_id'] : null);
            if (empty($user_id) || empty($package_id)) {
                throw new \Exception('Missing package payment data.');
            }
            $package = CustomerPackage::find($package_id);
            if ($package == null) {
                throw new \Exception('Package not found.');
            }
            if ((int) $payment['amount'] !== self::amountToPaise($package->amount)) {
                throw new \Exception('Razorpay payment amount does not match the package amount.');
            }
            purchase_payment_done($user_id, $package_id);
        } elseif ($payment_type == 'seller_package_payment') {
            $user_id = isset($context['user_id']) ? $context['user_id'] : null;
            $package_id = isset($context['package_id']) ? $context['package_id'] : (isset($context['seller_package_id']) ? $context['seller_package_id'] : null);
            if (empty($user_id) || empty($package_id)) {
                throw new \Exception('Missing seller package payment data.');
            }
            if (self::sellerPackageAlreadyPaid($payment_id)) {
                self::markProcessed($payment_id);
                return array(
                    'ok' => true,
                    'duplicate' => true,
                    'payment_details' => $payment_details,
                    'payment_type' => $payment_type,
                );
            }
            $package = SellerPackage::find($package_id);
            if ($package == null) {
                throw new \Exception('Seller package not found.');
            }
            if ((int) $payment['amount'] !== self::amountToPaise($package->amount)) {
                throw new \Exception('Razorpay payment amount does not match the package amount.');
            }
            seller_purchase_payment_done($user_id, $package_id, $package->amount, 'Razorpay', $payment_details);
        } else {
            throw new \Exception('Unknown payment type.');
        }

        self::markProcessed($payment_id);

        return array(
            'ok' => true,
            'duplicate' => false,
            'payment_details' => $payment_details,
            'payment_type' => $payment_type,
        );
        } catch (\Exception $e) {
            Cache::forget($lockKey);
            throw $e;
        }
    }

    public static function markCombinedOrderFailed($combined_order_id, $reason, $extra = array())
    {
        $combined_order = CombinedOrder::find($combined_order_id);
        if ($combined_order == null) {
            return;
        }

        foreach ($combined_order->orders as $order) {
            if ($order->payment_status == 'paid') {
                continue;
            }
            $details = json_decode($order->payment_details, true);
            if (!is_array($details)) {
                $details = array();
            }
            $details['gateway_status'] = 'failed';
            $details['failure_reason'] = $reason;
            $details = array_merge($details, $extra);
            $order->payment_details = json_encode($details);
            $order->payment_status = 'unpaid';
            $order->save();
            foreach ($order->orderDetails as $orderDetail) {
                if ($orderDetail->payment_status != 'paid') {
                    $orderDetail->payment_status = 'unpaid';
                    $orderDetail->save();
                }
            }
        }
    }

    public static function extractPaymentIdFromDetails($payment_details)
    {
        if (empty($payment_details)) {
            return null;
        }
        if (is_array($payment_details)) {
            $decoded = $payment_details;
        } else {
            $decoded = json_decode($payment_details, true);
        }
        if (!is_array($decoded)) {
            return is_string($payment_details) && strpos($payment_details, 'pay_') === 0 ? $payment_details : null;
        }
        if (!empty($decoded['razorpay_payment_id'])) {
            return $decoded['razorpay_payment_id'];
        }
        if (!empty($decoded['id']) && strpos($decoded['id'], 'pay_') === 0) {
            return $decoded['id'];
        }
        return null;
    }

    protected static function walletAlreadyCredited($razorpay_payment_id)
    {
        return Wallet::where('payment_details', 'like', '%' . $razorpay_payment_id . '%')->exists();
    }

    protected static function sellerPackageAlreadyPaid($razorpay_payment_id)
    {
        return SellerPackagePayment::where('payment_details', 'like', '%' . $razorpay_payment_id . '%')->exists();
    }

    public static function processVerifiedCheckout($input, $fallback_context = array())
    {
        $payment_id = isset($input['razorpay_payment_id']) ? $input['razorpay_payment_id'] : null;
        $order_id = isset($input['razorpay_order_id']) ? $input['razorpay_order_id'] : null;
        $signature = isset($input['razorpay_signature']) ? $input['razorpay_signature'] : null;

        if (empty($payment_id)) {
            throw new \Exception('Missing Razorpay payment ID.');
        }
        if (empty($order_id)) {
            throw new \Exception('Missing Razorpay order ID.');
        }
        if (empty($signature) || !self::verifyCheckoutSignature($order_id, $payment_id, $signature)) {
            throw new \Exception('Invalid Razorpay signature.');
        }

        $expectedAmount = self::expectedAmountPaiseFromContext($fallback_context);
        $payment = self::fetchValidatedPayment($payment_id, $order_id, $expectedAmount);

        return self::fulfillCapturedPayment($payment, $fallback_context);
    }

    public static function processByPaymentId($payment_id, $fallback_context = array())
    {
        $expectedAmount = self::expectedAmountPaiseFromContext($fallback_context);
        $expectedOrderId = isset($fallback_context['razorpay_order_id']) ? $fallback_context['razorpay_order_id'] : null;
        $payment = self::fetchValidatedPayment($payment_id, $expectedOrderId, $expectedAmount);
        $status = isset($payment['status']) ? $payment['status'] : '';
        if ($status != 'captured' && $status != 'authorized') {
            throw new \Exception('Razorpay payment is not successful. Status: ' . $status);
        }

        return self::fulfillCapturedPayment($payment, $fallback_context);
    }

    public static function expectedAmountPaiseFromContext($context)
    {
        $payment_type = isset($context['payment_type']) ? $context['payment_type'] : null;
        if ($payment_type == 'cart_payment' && !empty($context['combined_order_id'])) {
            $combined_order = CombinedOrder::find($context['combined_order_id']);
            return $combined_order ? self::amountToPaise($combined_order->grand_total) : null;
        }
        if ($payment_type == 'wallet_payment' && isset($context['amount'])) {
            return self::amountToPaise($context['amount']);
        }
        if ($payment_type == 'customer_package_payment') {
            $package_id = isset($context['package_id']) ? $context['package_id'] : (isset($context['customer_package_id']) ? $context['customer_package_id'] : null);
            $package = $package_id ? CustomerPackage::find($package_id) : null;
            return $package ? self::amountToPaise($package->amount) : null;
        }
        if ($payment_type == 'seller_package_payment') {
            $package_id = isset($context['package_id']) ? $context['package_id'] : (isset($context['seller_package_id']) ? $context['seller_package_id'] : null);
            $package = $package_id ? SellerPackage::find($package_id) : null;
            return $package ? self::amountToPaise($package->amount) : null;
        }
        return null;
    }
}
