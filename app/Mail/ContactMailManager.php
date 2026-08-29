<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactMailManager extends Mailable
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
         // dd($array);
         return $this->view($this->array['view'])
                     ->from($this->array['from'], env('MAIL_FROM_NAME'))
                     ->subject($this->array['mail_subject'])
                     ->with([
                         'content' => $this->array['content'],
                         'name' => $this->array['name'],
                         'phone' => $this->array['phone'],
                         'email' => $this->array['email'],
                         'subject' => $this->array['subject'],
                         'description' => $this->array['description'],
                     ]);
     }
}
