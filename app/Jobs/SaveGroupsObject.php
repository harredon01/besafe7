<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\EditMapObject;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SaveGroupsObject implements ShouldQueue {

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    protected $user;
    protected $data;
    protected $type;
    protected $object;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, array $data, $type, $object) {
        $this->user = $user;
        $this->data = $data;
        $this->type = $type;
        $this->object = $object;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditMapObject $editMapObject) {
        $editMapObject->saveToGroups($this->user, $this->data, $this->type, $this->object);
    }

}
