<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Services\ShiprocketService;

class ShiprocketController extends Controller
{
    // https://packagist.org/packages/aniket-in/laravel-shiprocket

    // $order_id is shiprocket order id not our site order id

    public function pincode(Request $request) {
        $response = (new ShiprocketService)->serviceability($request);
        return response()->json([
            'result'  => $response['status'],
            'message' => $response['response'],
        ]);
    }

    public function order_create($order) {
        $response = (new ShiprocketService)->create($order);
        // dd($response);
        if($response)
        {
            $order = Order::findOrFail($order->id);
            $order->shipment_details = json_encode($response);
            if(isset($response['status_code']) && ($response['status_code'] == 1))
                $order->tracking_code = $response['order_id'];
            $order->save();
        }
    }

    public function orders() {
        $data = [
            "per_page" => 100
        ];
        $response = (new ShiprocketService)->all($data);
        return $response;
    }

    public function order_details($order_id) {
        $response = (new ShiprocketService)->order_details($order_id);
        return $response;
    }

    public function cancel_order($order_id) {
        $response = (new ShiprocketService)->cancel_order($order_id);
        return $response;
    }

    public function track($order_id) {
        $response = (new ShiprocketService)->track($order_id); // Local order id
        return $response;
    }

    public function tracking_details($awb) {
        $response = (new ShiprocketService)->track_awb($awb);
        return $response;
    }

    public function delivery_status($awb) {
        $response = (new ShiprocketService)->track_awb($awb);
        return $response;
    }

    public function get_balance() {
        $response = (new ShiprocketService)->wallet_balance();
        return $response;
    }

    public function update_orders_delivery_status() {
        $res = [];
        $orders = Order::select('id','tracking_code')
            ->where('delivery_status','!=','delivered')
            ->where('delivery_status','!=','cancelled')
            ->whereNotNull('tracking_code')
            ->get();
        if((count($orders) > 0) && !empty($orders))
        {
            foreach($orders as $orderDetail)
            {
                $response = $this->order_details($orderDetail->tracking_code);
                if($response)
                {
                    $delivery_status = 'pending';
                    if($response['data']['status'] == 'READY TO SHIP')
                        $delivery_status = 'confirmed';
                    if($response['data']['status'] == 'PICKED UP')
                        $delivery_status = 'picked_up';
                    if($response['data']['status'] == 'IN TRANSIT')
                        $delivery_status = 'on_the_way';
                    if($response['data']['status'] == 'OUT FOR DELIVERY')
                        $delivery_status = 'out_for_delivery';
                    if($response['data']['status'] == 'DELIVERED')
                        $delivery_status = 'delivered';
                    if($response['data']['status'] == 'CANCELED')
                        $delivery_status = 'cancelled';

                    $data = array('order_id' => $orderDetail->id, 'status' => $delivery_status);
                    $res[] = $data;
                    $user = User::where('user_type','admin')->first();
                    auth()->login($user, true);
                    if($delivery_status != 'pending')
                        (new OrderController)->update_delivery_status(new Request($data));    
                }
            }
        }
        return $res;
    }

    
}
