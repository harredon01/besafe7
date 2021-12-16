<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeliveryScheduled extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var Order
     */
    public $data;
    
    /**
     * The order instance.
     *
     * @var Order
     */
    public $address;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($theData,$theAddress)
    {
        $this->data = $theData;
        $this->address = $theAddress;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(config("app.views").'.emails.food.delivery-scheduled');
    }
}
