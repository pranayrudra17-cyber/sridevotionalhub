<?php


namespace App\Http\Controllers\Api\V2;


use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Models\User;
use App\Utility\RazorpayUtility;
use Illuminate\Http\Request;

class RazorpayController
{

    public function payWithRazorpay(Request $request)
    {
        $payment_type = $request->payment_type;
        $combined_order_id = $request->combined_order_id;
        $amount = $request->amount;
        $user_id = $request->user_id;
        $user = User::find($user_id);

        $package_id = 0;
        if (isset($request->package_id)) {
            $package_id = $request->package_id;
        }

        try {
            $context = $this->requestContext($request);
            $razorpayOrder = RazorpayUtility::createOrder(
                $context['amount'],
                $context['receipt'],
                $context['notes']
            );
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => translate('Unable to start Razorpay payment')]);
        }

        if ($payment_type == 'cart_payment') {
            RazorpayUtility::persistRazorpayOrderOnCombinedOrder($combined_order_id, $razorpayOrder['id']);
            $combined_order = CombinedOrder::find($combined_order_id);
            $shipping_address = json_decode($combined_order->shipping_address, true);
            return view('frontend.razorpay.order_payment', compact('user', 'combined_order', 'shipping_address', 'razorpayOrder'));
        } elseif ($payment_type == 'wallet_payment') {
            return view('frontend.razorpay.wallet_payment', compact('user', 'amount', 'razorpayOrder'));
        } elseif ($payment_type == 'seller_package_payment') {
            return view('frontend.razorpay.wallet_payment', compact('user', 'amount', 'package_id', 'razorpayOrder'));
        } elseif ($payment_type == 'customer_package_payment') {
            return view('frontend.razorpay.wallet_payment', compact('user', 'amount', 'package_id', 'razorpayOrder'));
        }

        return response()->json(['result' => false, 'message' => translate('Invalid payment type')]);
    }

    public function payment(Request $request)
    {
        try {
            $fallback = $this->requestContext($request);
            $result = RazorpayUtility::processVerifiedCheckout($request->all(), $fallback['notes']);

            return response()->json([
                'result' => true,
                'message' => translate("Payment Successful"),
                'payment_details' => $result['payment_details']
            ]);
        } catch (\Exception $e) {
            $combined_order_id = $request->get('combined_order_id');
            if (!empty($combined_order_id)) {
                RazorpayUtility::markCombinedOrderFailed($combined_order_id, $e->getMessage(), array(
                    'razorpay_payment_id' => $request->get('razorpay_payment_id'),
                    'razorpay_order_id' => $request->get('razorpay_order_id'),
                ));
            }

            return response()->json(['result' => false, 'message' => translate('Payment Failed'), 'payment_details' => '']);
        }
    }

    public function cancel(Request $request)
    {
        $combined_order_id = $request->get('combined_order_id');
        if (!empty($combined_order_id)) {
            RazorpayUtility::markCombinedOrderFailed($combined_order_id, 'Payment cancelled or checkout dismissed');
        }

        return response()->json(['result' => false, 'message' => translate('Payment Failed')]);
    }

    public function success(Request $request)
    {
        try {
            $fallback = $this->requestContext($request);
            $payment_id = $request->get('razorpay_payment_id');
            if (empty($payment_id)) {
                $payment_id = RazorpayUtility::extractPaymentIdFromDetails($request->get('payment_details'));
            }

            if ($request->filled('razorpay_signature') && $request->filled('razorpay_order_id')) {
                RazorpayUtility::processVerifiedCheckout($request->all(), $fallback['notes']);
            } elseif (!empty($payment_id)) {
                RazorpayUtility::processByPaymentId($payment_id, $fallback['notes']);
            } else {
                return response()->json(['result' => false, 'message' => translate('Payment Failed')]);
            }

            return response()->json(['result' => true, 'message' => translate("Payment is successful")]);
        } catch (\Exception $e) {
            $combined_order_id = $request->get('combined_order_id');
            if (!empty($combined_order_id)) {
                RazorpayUtility::markCombinedOrderFailed($combined_order_id, $e->getMessage());
            }
            return response()->json(['result' => false, 'message' => translate('Payment Failed')]);
        }
    }

    public function webhook(Request $request)
    {
        return (new \App\Http\Controllers\Payment\RazorpayController())->webhook($request);
    }

    protected function requestContext(Request $request)
    {
        $payment_type = $request->get('payment_type');
        $user_id = $request->get('user_id');
        $combined_order_id = $request->get('combined_order_id');
        $package_id = $request->get('package_id');
        $amount = $request->get('amount');

        $notes = array(
            'payment_type' => $payment_type,
            'user_id' => $user_id,
        );

        $receipt = 'rzp_' . time();
        $resolvedAmount = $amount;

        if ($payment_type == 'cart_payment') {
            $combined_order = CombinedOrder::find($combined_order_id);
            $resolvedAmount = $combined_order ? $combined_order->grand_total : 0;
            $receipt = 'co_' . $combined_order_id;
            $notes['combined_order_id'] = $combined_order_id;
        } elseif ($payment_type == 'wallet_payment') {
            $receipt = 'wallet_' . $user_id . '_' . time();
            $notes['amount'] = $amount;
        } elseif ($payment_type == 'customer_package_payment') {
            $package = $package_id ? CustomerPackage::find($package_id) : null;
            $resolvedAmount = $package ? $package->amount : $amount;
            $receipt = 'cpkg_' . $package_id;
            $notes['package_id'] = $package_id;
            $notes['customer_package_id'] = $package_id;
        } elseif ($payment_type == 'seller_package_payment') {
            $package = $package_id ? SellerPackage::find($package_id) : null;
            $resolvedAmount = $package ? $package->amount : $amount;
            $receipt = 'spkg_' . $package_id;
            $notes['package_id'] = $package_id;
            $notes['seller_package_id'] = $package_id;
        }

        return array(
            'payment_type' => $payment_type,
            'amount' => $resolvedAmount,
            'receipt' => $receipt,
            'notes' => $notes,
        );
    }
}
