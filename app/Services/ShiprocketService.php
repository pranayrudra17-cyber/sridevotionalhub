<?php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Product;
use AniketIN\Shiprocket\Facades\Shiprocket;

class ShiprocketService {

    public function serviceability(Request $request) {
        $product = Product::findOrFail($request->id);
        $weight = ($product->weight != 0)?($product->weight)*($request->quantity):1;
        // $product->variant;
        $cod = $product->cash_on_delivery;
        $data = [
            "pickup_postcode" => env('PICKUP_POSTCODE'),
            "delivery_postcode" => $request->pincode,
            "weight" => $weight,
            "cod" => $cod
        ];
        $status = false;
        $response = Shiprocket::courier()->serviceability($data)->json();
        if($response['status'] == 200)
        {
            $courier_companies = $response['data']['available_courier_companies'];
            $shiprocket_recommended_courier_id = 0;
            if(!empty($response['data']['shiprocket_recommended_courier_id']))
                $shiprocket_recommended_courier_id = array_search($response['data']['shiprocket_recommended_courier_id'], array_column($courier_companies, 'courier_company_id'));
            $message = add_some_days($courier_companies[$shiprocket_recommended_courier_id]['etd']);
            $status = true;
        }
        else{
            $message = str_replace('between '.env('PICKUP_POSTCODE').' and','',$response['message']);
        }
        return array('response' => $message, 'status' => $status);
    }

    public function create($order) {
        $details = json_decode($order->shipping_address);
        $order_items = [];
        $weight = 0;
        foreach($order->orderDetails as $orderDetail)
        {
            $tax = 0;
            $selling_price = home_base_price($orderDetail->product, false);
            $discount = 0;
            if (home_base_price($orderDetail->product) != home_discounted_base_price($orderDetail->product)){
                // $selling_price = home_discounted_base_price($orderDetail->product, false);
                $discount = (home_base_price($orderDetail->product, false))-(home_discounted_base_price($orderDetail->product, false));
            }
            if(($orderDetail->product->unit_price) != $selling_price)
                $tax = (int)((($selling_price-($orderDetail->product->unit_price))*100)/($orderDetail->product->unit_price));
            $order_items[] = [
                "name" => $orderDetail->product->getTranslation('name'),
                "sku" => $orderDetail->product->slug,
                "units" => $orderDetail->quantity,
                "selling_price" => $selling_price,
                "discount" => $discount,
                "tax" => $tax
            ];
            $weight += ($orderDetail->weight != 0)?($orderDetail->weight)*($orderDetail->quantity):1*($orderDetail->quantity);
        }
        $sub_total = ($order->grand_total+$order->coupon_discount)-$order->orderDetails->sum('shipping_cost');
        $data = [
            "order_id" => $order->code,
            "order_date" => Carbon::parse($order->created_at)->format('Y-m-d H:i'),
            "pickup_location" => "Primary", // The name of the pickup location added in your Shiprocket account. This cannot be a new location.
            "channel_id" => "",
            "billing_customer_name" => $details->name,
            "billing_last_name" => "",
            "billing_address" => $details->address,
            "billing_city" => $details->city,
            "billing_pincode" => $details->postal_code, // integer
            "billing_state" => $details->state,
            "billing_country" => $details->country,
            "billing_email" => $details->email,
            "billing_phone" => str_replace("+91","",$details->phone), // integer
            "shipping_is_billing" => true, // Whether the shipping address is the same as billing address. 1 or 'true' for yes and 0 or 'false' for no.
            "shipping_customer_name" => "", // Name of the customer the order is shipped to. Required in case billing is not same as shipping.
            "shipping_address" => "",
            "shipping_city" => "",
            "shipping_pincode" => "",
            "shipping_country" => "",
            "shipping_state" => "",
            "shipping_phone" => "",
            "order_items" => $order_items,
            "payment_method" => "Prepaid", // The method of payment. Can be either COD (Cash on delivery) Or Prepaid.
            "shipping_charges" => $order->orderDetails->sum('shipping_cost'),
            "giftwrap_charges" => 0,
            "transaction_charges" => 0,
            "total_discount" => $order->coupon_discount,
            "sub_total" => $sub_total, // Calculated sub total amount in Rupee after deductions.
            "length" => 10, // The length of the item in cms. Must be more than 0.5.
            "breadth" => 15,
            "height" => 20,
            "weight" => $weight
        ];
        return Shiprocket::order()->create($data)->json();
    }
    
    public function update(Request $request) {
        return Shiprocket::order()->update([
        ])->json();
    }
    
    public function all($filter) {
        return Shiprocket::order()->all($filter)->json();
    }
    
    public function order_details($order_id) {
        return Shiprocket::order()->detailsById($order_id)->json();
    }
    
    public function cancel_order($order_id) {
        $data = [
            "ids" => [$order_id]
        ];
        return Shiprocket::order()->cancelByIds($data)->json();
    }
    
    public function track($order_id) {
        return Shiprocket::track()->order($order_id)->json(); // Local db order id
    }
    
    public function track_shipment($shipment_id) {
        return Shiprocket::track()->shipment($shipment_id)->json();
    }
    
    public function track_awb($awb) {
        return Shiprocket::track()->awb($awb)->json();
    }
    
    public function wallet_balance() {
        return Shiprocket::wallet()->getBalance()->json();
    }

}