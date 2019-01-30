<?php

namespace App\Jobs;
use App\Services\Food;
use App\Models\Route;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BuildScenario implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scenario;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($scenario  )
    {
        $this->scenario = $scenario;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food)
    {
        $routes = Route::where("type", $this->scenario)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
        $food->buildScenarioTransit($routes);
    }
}
