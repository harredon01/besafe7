<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailPaymentCash extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var Order
     */
    public $payment;
    
    /**
     * The order instance.
     *
     * @var Order
     */
    public $user;
    
    /**
     * The order instance.
     *
     * @var Order
     */
    public $url;
    
    /**
     * The order instance.
     *
     * @var Order
     */
    public $pdf;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Payment $payment, User $user,$url,$pdf)
    {
        $this->payment = $payment;
        $this->user = $user;
        $this->url = $url;
        $this->pdf = $pdf;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.email-payment-cash-email');
    }
}
