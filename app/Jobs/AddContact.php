<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\EditUserData;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AddContact implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $contact_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $contact_id )
    {
        $this->user = $user;
        $this->contact_id = $contact_id;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditUserData $editUserData)
    {
        $editUserData->addContact($this->user, $this->contact_id); 
    }
}
