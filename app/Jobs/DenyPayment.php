<?php

namespace App\Jobs;
use App\Models\Payment;
use App\Services\EditOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DenyPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Payment $payment )
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditOrder $editOrder)
    {
        $editOrder->denyPayment($this->payment); 
    }
}