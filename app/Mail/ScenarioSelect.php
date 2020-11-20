<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ScenarioSelect extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var Order
     */
    public $preData;
    /**
     * The order instance.
     *
     * @var Order
     */
    public $simpleData;
    /**
     * The order instance.
     *
     * @var Order
     */
    public $winner;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($preData,$simpleData,$winner)
    {
        $this->preData = $preData;
        $this->simpleData = $simpleData;
        $this->winner = $winner;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(config("app.views").'.emails.food.scenario-select');
    }
}
