<?php

namespace App\Jobs;
use App\Services\EditBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateChatroom implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booking_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $booking_id )
    {
        $this->booking_id = $booking_id;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EditBooking $editBooking)
    {
        $editBooking->createChatroom($this->booking_id); 
    }
}
