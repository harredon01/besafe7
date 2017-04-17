<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\EditGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AdminGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $user_id;
    protected $group_id;
    

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $user_id, $group_id )
    {
        $this->user = $user;
        $this->user_id = $user_id;
        $this->group_id = $group_id;
    }
 
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditGroup $editGroup)
    {
        $editGroup->setAdminGroup($this->user, $this->user_id,$this->group_id);
    }
}

