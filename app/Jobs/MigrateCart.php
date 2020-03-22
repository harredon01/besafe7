<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\EditCart;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MigrateCart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $device_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $device_id )
    {
        $this->user = $user;
        $this->device_id = $device_id;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditCart $editCart)
    {
        $editCart->migrateCart($this->user, $this->device_id); 
    }
}
