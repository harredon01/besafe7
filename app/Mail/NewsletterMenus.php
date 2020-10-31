<?php

namespace App\Mail;

use App\Models\Stop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewsletterMenus extends Mailable
{
    use Queueable, SerializesModels;

    public $days;
    public $month1;
    public $month2;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($days,$month1,$month2)
    {
        $this->days = $days;
        $this->month1 = $month1;
        $this->month2 = $month2;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view(config("app.views").'.emails.food.newsletter-menus-4')->subject("Conoce nuestros deliciosos platos de la semana!");
    }
}
