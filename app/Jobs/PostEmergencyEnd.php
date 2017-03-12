<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\EditAlerts;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PostEmergencyEnd implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $code;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $code )
    {
        $this->user = $user;
        $this->code = $code;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditAlerts $editAlerts)
    {
        $editAlerts->postStopEmergency($this->user, $this->code); 
    }
}
