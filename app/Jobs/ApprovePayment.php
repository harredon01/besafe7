<?php

namespace App\Jobs;
use App\Models\Payment;
use App\Services\EditOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ApprovePayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;
    
    protected $platform;
    protected $paymentMenthod;
    protected $paymentStatus;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Payment $payment,$platform ,$paymentMethod,$paymentStatus="approved")
    {
        $this->payment = $payment;
        $this->paymentMenthod = $paymentMethod;
        $this->platform = $platform;
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditOrder $editOrder)
    {
        $editOrder->approvePayment($this->payment,$this->platform, $this->paymentMenthod,$this->paymentStatus); 
    }
}
