<?php

namespace App\Jobs;
use Exception;
use App\Services\Proofhub;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProofhubJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 15000;
    public function __construct() 
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Proofhub $proofhub)
    {
        $proofhub->getReport();
    }
}
