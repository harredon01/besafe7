<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\EditMessages;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendChat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $data;
    protected $push;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data,User $user, bool $push )
    {
        $this->user = $user;
        $this->data = $data;
        $this->push = $push;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditMessages $editMessages)
    {
        $editMessages->sendChat($this->data,$this->user, $this->push); 
    }
}
