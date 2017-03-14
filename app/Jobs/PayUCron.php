<?php

namespace App\Jobs;
use App\Services\PayU;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PayUCron implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( )
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PayU $payu)
    {
        $payu->checkOrders(); 
    }
}
