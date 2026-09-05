<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\ProductDeliveryQrService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceEmailManager extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $array;

    public function __construct($array)
    {
        $this->array = $array;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
     public function build()
     {
         $order = $this->array['order'];
         $includeDeliveryQr = !empty($this->array['include_delivery_qr']);
         $deliveryQrPngs = array();

         if ($includeDeliveryQr && $order instanceof Order) {
             $order->loadMissing(array('orderDetails.product', 'pickup_point', 'user'));
             foreach ($order->orderDetails as $orderDetail) {
                 $png = ProductDeliveryQrService::pngBinary($orderDetail->deliveryQrToken(), 200);
                 if ($png !== null) {
                     $deliveryQrPngs[$orderDetail->id] = $png;
                 }
             }
         }

         return $this->view($this->array['view'])
                     ->from($this->array['from'], env('MAIL_FROM_NAME'))
                     ->subject($this->array['subject'])
                     ->with([
                         'order' => $order,
                         'include_delivery_qr' => $includeDeliveryQr,
                         'delivery_qr_pngs' => $deliveryQrPngs,
                     ]);
     }
}
