<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\WalletController;
use Session;
use Auth;


class ToyyibpayController extends Controller
{
    public function pay()
    {
        $amount=0;
        if(Session::has('payment_type')){
            if(Session::get('payment_type') == 'cart_payment'){
                $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                $amount = round($combined_order->grand_total * 100);
                $combined_order_id = $combined_order->id;
                $billname = 'Ecommerce Cart Payment';
                $first_name = json_decode($combined_order->shipping_address)->name;
                $phone = json_decode($combined_order->shipping_address)->phone;
                $email = json_decode($combined_order->shipping_address)->email;
            }
            elseif (Session::get('payment_type') == 'wallet_payment') {
                $amount = Session::get('payment_data')['amount'] * 100;
                $combined_order_id = rand(10000,99999);
                $billname = 'Wallet Payment';
                $first_name = Auth::user()->name;
                $phone = (Auth::user()->phone != null) ? Auth::user()->phone : '123456789';
                $email = (Auth::user()->email != null) ? Auth::user()->email : 'example@example.com';

            }
            elseif (Session::get('payment_type') == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail(Session::get('payment_data')['customer_package_id']);
                $amount = round($customer_package->amount * 100);
                $combined_order_id = rand(10000,99999);
                $billname = 'Customer Package Payment';
                $first_name = Auth::user()->name;
                $phone = (Auth::user()->phone != null) ? Auth::user()->phone : '123456789';
                $email = (Auth::user()->email != null) ? Auth::user()->email : 'example@example.com';
            }
            elseif (Session::get('payment_type') == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);
                $amount = round($seller_package->amount * 100);
                $combined_order_id = rand(10000,99999);
                $billname = 'Seller Package Payment';
                $first_name = Auth::user()->name;
                $phone = (Auth::user()->phone != null) ? Auth::user()->phone : '123456789';
                $email = (Auth::user()->email != null) ? Auth::user()->email : 'example@example.com';
            }
        }

        
        $option = array(
            'userSecretKey' => config('toyyibpay.key'),
            'categoryCode' => config('toyyibpay.category'),
            'billName' =>  $billname,
            'billDescription' => 'Payment Using ToyyibPay',
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount'=> $amount,
            'billReturnUrl'=> route('toyyibpay-status'),
            'billCallbackUrl' => route('toyyibpay-callback'),
            'billExternalReferenceNo' => $combined_order_id,
            'billTo' => $first_name,
            'billEmail' => $email,
            'billPhone'=> $phone,
            'billSplitPayment' => 0,
            'billSplitPaymentArgs'=>'',
            'billPaymentChannel' => 2,
            'billContentEmail'=>'Thank you for purchasing our product!',
            'billChargeToCustomer'=> 2
        );

        if(get_setting('toyyibpay_sandbox') == 1)
        $site_url='https://dev.toyyibpay.com/';
        else
        $site_url='https://toyyibpay.com/';

        $url = $site_url.'index.php/api/createBill';
        $response = Http::asForm()->post($url, $option);
        $billcode = $response[0]['BillCode'];
        $final_url = $site_url . $billcode;
        return redirect($final_url);

    }


    public function paymentstatus()
    {

        $response= request()->status_id;
        if($response == 1)
        {
            $payment = ["status" => "Success"];
            $payment_type = Session::get('payment_type');

            if ($payment_type == 'cart_payment') {
                flash(translate("Your order has been placed successfully"))->success();
                $checkoutController = new CheckoutController;
                return $checkoutController->checkout_done(session()->get('combined_order_id'), json_encode($payment));
            }

            if ($payment_type == 'wallet_payment') {
                $walletController = new WalletController;
                return $walletController->wallet_payment_done(session()->get('payment_data'), json_encode($payment));
            }

            if ($payment_type == 'customer_package_payment') {
                $customer_package_controller = new CustomerPackageController;
                return $customer_package_controller->purchase_payment_done(session()->get('payment_data'), json_encode($payment));
            }
            if($payment_type == 'seller_package_payment') {
                $seller_package_controller = new SellerPackageController;
                return $seller_package_controller->purchase_payment_done(session()->get('payment_data'), json_encode($payment));
            }
        }
        else
            {
                flash(translate('Payment is cancelled'))->error();
                return redirect()->route('home');   
            }
        
    
    }

    public function callback()
    {

       $response= request()->all(['refno','status','reason','billcode','order_id','amount']);
       Log::info($response);
    }
}


