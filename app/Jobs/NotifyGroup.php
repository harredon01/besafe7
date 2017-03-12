<?php

namespace App\Jobs;
use App\Models\User;
use App\Models\Group;
use App\Services\EditAlerts;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NotifyGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $group;
    protected $filename;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Group $group, $filename, $type )
    {
        $this->user = $user;
        $this->group = $group;
        $this->filename = $filename;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditAlerts $editAlerts)
    {
        $editAlerts->notifyGroup($this->user,$this->group, $this->filename,$this->type ); 
    }
}
