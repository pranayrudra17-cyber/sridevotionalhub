<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Utility\RazorpayUtility;
use Session;
use Auth;


class RazorpayController extends Controller
{
    public function pay()
    {
        if (!Session::has('payment_type')) {
            flash(translate('Invalid payment session'))->error();
            return redirect()->route('home');
        }

        try {
            $context = $this->sessionContext();
            $razorpayOrder = RazorpayUtility::createOrder(
                $context['amount'],
                $context['receipt'],
                $context['notes']
            );
        } catch (\Exception $e) {
            flash(translate('Unable to start Razorpay payment'))->error();
            return redirect()->route('home');
        }

        Session::put('razorpay_order_id', $razorpayOrder['id']);

        if ($context['payment_type'] == 'cart_payment') {
            RazorpayUtility::persistRazorpayOrderOnCombinedOrder($context['combined_order_id'], $razorpayOrder['id']);
            $combined_order = CombinedOrder::findOrFail($context['combined_order_id']);
            return view('frontend.razor_wallet.order_payment_Razorpay', compact('combined_order', 'razorpayOrder'));
        } elseif ($context['payment_type'] == 'wallet_payment') {
            return view('frontend.razor_wallet.wallet_payment_Razorpay', compact('razorpayOrder'));
        } elseif ($context['payment_type'] == 'customer_package_payment') {
            return view('frontend.razor_wallet.customer_package_payment_Razorpay', compact('razorpayOrder'));
        } elseif ($context['payment_type'] == 'seller_package_payment') {
            return view('frontend.razor_wallet.seller_package_payment_Razorpay', compact('razorpayOrder'));
        }

        flash(translate('Invalid payment session'))->error();
        return redirect()->route('home');
    }

    public function payment(Request $request)
    {
        $context = $this->sessionContext(false);
        $combined_order_id = isset($context['combined_order_id']) ? $context['combined_order_id'] : Session::get('combined_order_id');

        try {
            if (!empty($context['razorpay_order_id']) && $request->filled('razorpay_order_id')
                && $request->razorpay_order_id != $context['razorpay_order_id']) {
                throw new \Exception('Razorpay order ID mismatch.');
            }

            RazorpayUtility::processVerifiedCheckout($request->all(), $context['notes']);
        } catch (\Exception $e) {
            if (!empty($combined_order_id)) {
                RazorpayUtility::markCombinedOrderFailed($combined_order_id, $e->getMessage(), array(
                    'razorpay_payment_id' => $request->get('razorpay_payment_id'),
                    'razorpay_order_id' => $request->get('razorpay_order_id'),
                ));
            }
            flash(translate('Payment failed'))->error();
            return $this->failureRedirect($context);
        }

        return $this->successRedirect($context);
    }

    public function fail(Request $request)
    {
        $context = $this->sessionContext(false);
        $combined_order_id = isset($context['combined_order_id']) ? $context['combined_order_id'] : Session::get('combined_order_id');
        $reason = $request->get('description', $request->get('reason', 'Payment cancelled or failed'));

        if (!empty($combined_order_id)) {
            RazorpayUtility::markCombinedOrderFailed($combined_order_id, $reason, array(
                'razorpay_payment_id' => $request->get('razorpay_payment_id'),
            ));
        }

        flash(translate('Payment failed'))->error();
        return $this->failureRedirect($context);
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();

        if (!RazorpayUtility::verifyWebhookSignature($payload, $signature)) {
            return response()->json(array('status' => 'invalid signature'), 400);
        }

        $event = $request->input('event');
        $entity = $request->input('payload.payment.entity');

        try {
            if (($event == 'payment.captured' || $event == 'order.paid') && is_array($entity) && !empty($entity['id'])) {
                $payment = RazorpayUtility::fetchValidatedPayment($entity['id']);
                RazorpayUtility::fulfillCapturedPayment($payment);
            } elseif ($event == 'payment.failed' && is_array($entity)) {
                if (is_array($entity)) {
                    $notes = RazorpayUtility::notes($entity);
                    $combined_order_id = isset($notes['combined_order_id']) ? $notes['combined_order_id'] : null;
                    if (!empty($combined_order_id)) {
                        RazorpayUtility::markCombinedOrderFailed(
                            $combined_order_id,
                            isset($entity['error_description']) ? $entity['error_description'] : 'payment.failed',
                            array(
                                'razorpay_payment_id' => isset($entity['id']) ? $entity['id'] : null,
                                'razorpay_order_id' => isset($entity['order_id']) ? $entity['order_id'] : null,
                            )
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json(array('status' => 'error'), 500);
        }

        return response()->json(array('status' => 'ok'));
    }

    protected function sessionContext($strict = true)
    {
        $payment_type = Session::get('payment_type');
        $payment_data = Session::get('payment_data', array());
        $user_id = Auth::check() ? Auth::id() : null;
        $razorpay_order_id = Session::get('razorpay_order_id');

        $context = array(
            'payment_type' => $payment_type,
            'amount' => 0,
            'receipt' => 'rzp_' . time(),
            'combined_order_id' => Session::get('combined_order_id'),
            'razorpay_order_id' => $razorpay_order_id,
            'notes' => array(
                'payment_type' => $payment_type,
                'user_id' => $user_id,
            ),
        );

        if ($payment_type == 'cart_payment') {
            $combined_order = CombinedOrder::find(Session::get('combined_order_id'));
            if ($combined_order == null) {
                if ($strict) {
                    throw new \Exception('Order not found.');
                }
                return $context;
            }
            $context['amount'] = $combined_order->grand_total;
            $context['receipt'] = 'co_' . $combined_order->id;
            $context['combined_order_id'] = $combined_order->id;
            $context['notes']['combined_order_id'] = $combined_order->id;
        } elseif ($payment_type == 'wallet_payment') {
            $context['amount'] = isset($payment_data['amount']) ? $payment_data['amount'] : 0;
            $context['receipt'] = 'wallet_' . $user_id . '_' . time();
            $context['notes']['amount'] = $context['amount'];
        } elseif ($payment_type == 'customer_package_payment') {
            $package_id = isset($payment_data['customer_package_id']) ? $payment_data['customer_package_id'] : null;
            $package = $package_id ? CustomerPackage::find($package_id) : null;
            $context['amount'] = $package ? $package->amount : 0;
            $context['receipt'] = 'cpkg_' . $package_id;
            $context['notes']['package_id'] = $package_id;
            $context['notes']['customer_package_id'] = $package_id;
        } elseif ($payment_type == 'seller_package_payment') {
            $package_id = isset($payment_data['seller_package_id']) ? $payment_data['seller_package_id'] : null;
            $package = $package_id ? SellerPackage::find($package_id) : null;
            $context['amount'] = $package ? $package->amount : 0;
            $context['receipt'] = 'spkg_' . $package_id;
            $context['notes']['package_id'] = $package_id;
            $context['notes']['seller_package_id'] = $package_id;
        } elseif ($strict) {
            throw new \Exception('Invalid payment session.');
        }

        if (!empty($razorpay_order_id)) {
            $context['notes']['razorpay_order_id'] = $razorpay_order_id;
        }

        return $context;
    }

    protected function successRedirect($context)
    {
        $payment_type = isset($context['payment_type']) ? $context['payment_type'] : Session::get('payment_type');

        if ($payment_type == 'cart_payment') {
            Session::put('combined_order_id', $context['combined_order_id']);
            return redirect()->route('order_confirmed');
        }

        Session::forget('payment_data');
        Session::forget('payment_type');
        Session::forget('razorpay_order_id');

        if ($payment_type == 'wallet_payment') {
            flash(translate('Payment completed'))->success();
            return redirect()->route('wallet.index');
        }
        if ($payment_type == 'customer_package_payment') {
            flash(translate('Package purchasing successful'))->success();
            return redirect()->route('dashboard');
        }
        if ($payment_type == 'seller_package_payment') {
            flash(translate('Package purchasing successful'))->success();
            if (\Illuminate\Support\Facades\Route::has('seller.packages_payment_list')) {
                return redirect()->route('seller.packages_payment_list');
            }
            return redirect()->route('dashboard');
        }

        flash(translate('Payment completed'))->success();
        return redirect()->route('home');
    }

    protected function failureRedirect($context)
    {
        $payment_type = isset($context['payment_type']) ? $context['payment_type'] : Session::get('payment_type');

        if ($payment_type == 'wallet_payment') {
            return redirect()->route('wallet.index');
        }
        if ($payment_type == 'customer_package_payment') {
            return redirect()->route('customer_packages_list_show');
        }
        if ($payment_type == 'seller_package_payment' && \Illuminate\Support\Facades\Route::has('seller.packages_payment_list')) {
            return redirect()->route('seller.packages_payment_list');
        }
        if ($payment_type == 'cart_payment' && !empty($context['combined_order_id'])) {
            try {
                return redirect()->route('purchase_history.details', encrypt($context['combined_order_id']));
            } catch (\Exception $e) {
                return redirect()->route('home');
            }
        }

        return redirect()->route('home');
    }
}
