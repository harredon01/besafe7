<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StoreReports extends Mailable
{
    use Queueable, SerializesModels;


    
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
    public function __construct($attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(config("app.views").'.emails.food.sales-report')->attachFromStorageDisk('local', $this->attachment);
    }
}
