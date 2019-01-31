<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class GeneralNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var Order
     */
    public $subject;
    /**
     * The order instance.
     *
     * @var Order
     */
    public $bodyMail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject,$bodyMail)
    {
        $this->subject = $subject;
        $this->bodyMail = $bodyMail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.general-notification')->subject($this->subject);
    }
}
