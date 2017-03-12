<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\EditGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class InviteUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $data;
    protected $is_new;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, array $data, $is_new)
    {
        $this->user = $user;
        $this->data = $data;
        $this->is_new = $is_new;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditGroup $editGroup)
    {
        $editGroup->inviteUsers($this->user, $this->data,$this->is_new); 
    }
}

