<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\EditAlerts;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PostEmergency implements ShouldQueue {

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    protected $user;
    protected $data;
    protected $secret;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, array $data, $secret) {
        $this->user = $user;
        $this->data = $data;
        $this->secret = $secret;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditAlerts $editAlerts) {
        $editAlerts->postEmergency($this->user, $this->data, $this->secret);
    }

}
