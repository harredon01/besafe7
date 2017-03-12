<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\EditAlerts;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RequestPing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $pingee;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $pingee )
    {
        $this->user = $user;
        $this->pingee = $pingee;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditAlerts $editAlerts)
    {
        $editAlerts->requestPing($this->user, $this->pingee); 
    }
}