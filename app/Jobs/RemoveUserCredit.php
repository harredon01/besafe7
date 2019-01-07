<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\Food;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RemoveUserCredit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $hash;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $hash )
    {
        $this->user_id = $user_id;
        $this->hash = $hash;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food)
    {
        $food->removeUserCredit($this->user_id, $this->hash); 
    }
}
