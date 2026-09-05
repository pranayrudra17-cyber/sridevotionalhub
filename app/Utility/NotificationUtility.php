<?php

namespace App\Utility;

use App\Mail\InvoiceEmailManager;
use App\Mail\OrderDeliveredEmailManager;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\SmsTemplate;
use App\Http\Controllers\OTPVerificationController;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderNotification;
use App\Models\FirebaseNotification;

class NotificationUtility
{
    public static function sendOrderPlacedNotification($order, $request = null)
    {       
        //sends email to customer with the invoice pdf attached
        $array['view'] = 'emails.invoice';
        $array['subject'] = translate('A new order has been placed') . ' - ' . $order->code;
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['order'] = $order;
        try {
            $customerArray = $array;
            $customerArray['include_delivery_qr'] = true;
            Mail::to($order->user->email)->queue(new InvoiceEmailManager($customerArray));
            Mail::to($order->orderDetails->first()->product->user->email)->queue(new InvoiceEmailManager($array));
            // Added by Pranay Rudra
            $staff = User::where('user_type','staff')->get()->first();
            if(!empty($staff))
                Mail::to($staff->email)->queue(new InvoiceEmailManager($array));
            // End
        } catch (\Exception $e) {

        }

        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'order_placement')->first()->status == 1) {
            try {
                $otpController = new OTPVerificationController;
                $otpController->send_order_code($order);
            } catch (\Exception $e) {

            }
        }

        //sends Notifications to user
        self::sendNotification($order, 'placed');
        if ($request !=null && get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order placed !";
            $request->text = "An order {$order->code} has been placed";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            self::sendFirebaseNotification($request);
        }
    }

    public static function sendNotification($order, $order_status)
    {        
        if ($order->seller_id == \App\Models\User::where('user_type', 'admin')->first()->id) {
            $users = User::findMany([$order->user->id, $order->seller_id]);
        } else {
            $users = User::findMany([$order->user->id, $order->seller_id, \App\Models\User::where('user_type', 'admin')->first()->id]);
        }

        $order_notification = array();
        $order_notification['order_id'] = $order->id;
        $order_notification['order_code'] = $order->code;
        $order_notification['user_id'] = $order->user_id;
        $order_notification['seller_id'] = $order->seller_id;
        $order_notification['status'] = $order_status;

        Notification::send($users, new OrderNotification($order_notification));
    }

    public static function sendFirebaseNotification($req)
    {        
        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = array
        (
            'to' => $req->device_token,
            'notification' => [
                'body' => $req->text,
                'title' => $req->title,
                'sound' => 'default' /*Default sound*/
            ],
            'data' => [
                'item_type' => $req->type,
                'item_type_id' => $req->id,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ]
        );

        //$fields = json_encode($arrayToSend);
        $headers = array(
            'Authorization: key=' . env('FCM_SERVER_KEY'),
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        curl_close($ch);

        $firebase_notification = new FirebaseNotification;
        $firebase_notification->title = $req->title;
        $firebase_notification->text = $req->text;
        $firebase_notification->item_type = $req->type;
        $firebase_notification->item_type_id = $req->id;
        $firebase_notification->receiver_id = $req->user_id;

        $firebase_notification->save();
    }

    /**
     * Customer email after a QR scan marks an order item delivered.
     * Must be called only after the delivery update is committed.
     *
     * @param \App\Models\Order $order
     * @param \App\Models\OrderDetail $orderDetail
     * @return void
     */
    public static function sendOrderItemDeliveredNotification($order, $orderDetail)
    {
        if (!$order instanceof Order || !$orderDetail instanceof OrderDetail) {
            return;
        }

        $order->loadMissing(array('user', 'orderDetails.product'));
        $orderDetail->loadMissing(array('product'));

        $email = null;
        if ($order->user && !empty($order->user->email)) {
            $email = $order->user->email;
        } else {
            $shipping = json_decode($order->shipping_address);
            if ($shipping && !empty($shipping->email)) {
                $email = $shipping->email;
            }
        }

        if (empty($email)) {
            Log::warning('Order delivered email skipped: missing customer email', array(
                'order_id' => $order->id,
                'order_detail_id' => $orderDetail->id,
            ));
            return;
        }

        $array = array();
        $array['view'] = 'emails.order_delivered';
        $array['subject'] = translate('Your Order Has Been Successfully Delivered') . ' - ' . $order->code;
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['order'] = $order;
        $array['orderDetail'] = $orderDetail;

        try {
            Mail::to($email)->queue(new OrderDeliveredEmailManager($array));
        } catch (\Exception $e) {
            Log::error('Order delivered email failed', array(
                'order_id' => $order->id,
                'order_detail_id' => $orderDetail->id,
                'error' => $e->getMessage(),
            ));
        }
    }
}
