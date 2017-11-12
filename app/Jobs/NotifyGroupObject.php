<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Group;
use App\Services\EditMerchant;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NotifyGroupObject implements ShouldQueue {

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    protected $user;
    protected $group;
    protected $data;
    protected $type;
    protected $object;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Group $group, User $user, array $data, $type, $object) {
        $this->user = $user;
        $this->group = $group;
        $this->data = $data;
        $this->type = $type;
        $this->object = $object;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditMerchant $editMerchant) {
        $editMerchant->notifyGroup($this->group, $this->user, $this->data, $this->type, $this->object);
    }

}
