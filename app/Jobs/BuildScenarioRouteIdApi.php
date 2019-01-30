<?php

namespace App\Jobs;
use App\Services\Food;
use App\Models\Route;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BuildScenarioRouteIdApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id  )
    {
        $this->id = $id;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food)
    {
        $routes = Route::where("id", $this->id)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
        $food->buildScenarioTransit($routes);
    }
}
