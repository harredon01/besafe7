<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\Food;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BuildCompleteScenario implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scenario;
    protected $hash;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($scenario, $hash )
    {
        $this->scenario = $scenario;
        $this->hash = $hash;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food)
    {
        $food->buildCompleteScenario($this->scenario, $this->hash); 
    }
}
