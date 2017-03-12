<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\EditGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class LeaveGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $group_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $group_id )
    {
        $this->user = $user;
        $this->group_id = $group_id;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditGroup $editGroup)
    {
        $editGroup->leaveGroup($this->user, $this->group_id); 
    }
}

