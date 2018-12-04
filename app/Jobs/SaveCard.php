<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\EditBilling;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SaveCard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    
    protected $data;
    
    protected $source;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user,$data,$source )
    {
        $this->user = $user;
        $this->data = $data;
        $this->source = $source;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditBilling $editBilling)
    {
        $editBilling->saveCard($this->user,$this->data,$this->source); 
    }
}
