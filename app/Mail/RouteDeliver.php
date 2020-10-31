<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RouteDeliver extends Mailable
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
    public $attachment;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($theData,$attachment)
    {
        $this->data = $theData;
        $this->attachment = $attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(config("app.views").'.emails.food.transport-distribution')->attachFromStorageDisk('local', $this->attachment);
    }
}
