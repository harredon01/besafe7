<?php

namespace App\Jobs;
use App\Models\Delivery;
use App\Services\EditDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AddDeliveryInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $delivery;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery )
    {
        $this->delivery = $delivery;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditDelivery $editDelivery)
    {
        $editDelivery->addInfoToDelivery($this->delivery); 
    }
}
