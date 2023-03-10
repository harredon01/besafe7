<?php

namespace App\Mail;

use App\Models\Stop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProofhubResults extends Mailable 
{
    use Queueable, SerializesModels;

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
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(config("app.views").'.emails.general-notification')->subject("Reports gestion")
        ->attachFromStorageDisk('local', $this->path);
    }
}
