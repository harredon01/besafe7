<?php

namespace App\Jobs;
use App\Services\Food;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BuildScenarioRouteId implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $hash;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $hash )
    {
        $this->id = $id;
        $this->hash = $hash;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food)
    {
        $food->buildScenarioRouteId($this->id, $this->hash); 
    }
}
