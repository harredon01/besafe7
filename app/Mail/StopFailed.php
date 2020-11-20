<?php

namespace App\Mail;

use App\Models\Stop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StopFailed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var Order
     */
    public $stop;
    
    public $runnerName;
    
    public $runnerPhone;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Stop $stop,$runnerName,$runnerPhone)
    {
        $this->stop = $stop;
        $this->runnerName = $runnerName;
        $this->runnerPhone = $runnerPhone;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(config("app.views").'.emails.food.stop-failed');
    }
}
