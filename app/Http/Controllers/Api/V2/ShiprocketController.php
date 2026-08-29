<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
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
}
