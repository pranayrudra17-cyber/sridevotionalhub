<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredEmailManager extends Mailable
{
    use Queueable, SerializesModels;

    public $array;

    public function __construct($array)
    {
        $this->array = $array;
    }

    /**
     * @return $this
     */
    public function build()
    {
        return $this->view($this->array['view'])
                    ->from($this->array['from'], env('MAIL_FROM_NAME'))
                    ->subject($this->array['subject'])
                    ->with(array(
                        'order' => $this->array['order'],
                        'orderDetail' => $this->array['orderDetail'],
                    ));
    }
}
