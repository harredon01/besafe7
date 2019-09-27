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

    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.food.newsletter-codigos')->subject("Conoce nuestro menu de la semana!");
    }
}
