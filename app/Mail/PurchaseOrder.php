<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PurchaseOrder extends Mailable
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
    public $path;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($theData,$path)
    {
        $this->data = $theData;
        $this->path = $path;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->path){
            return $this->markdown(config("app.views").'.emails.food.purchase-order')->attachFromStorageDisk('local', $this->path);
        } else {
            return $this->markdown(config("app.views").'.emails.food.purchase-order');
        }
        
    }
}
