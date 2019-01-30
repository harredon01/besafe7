<?php

namespace App\Jobs;

use App\Services\Food;
use App\Models\CoveragePolygon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RegenerateDeliveriesAndScenarios implements ShouldQueue {

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food) {
        $food->deleteRandomDeliveriesData();
        $polygons = CoveragePolygon::where('lat', "<>", 0)->where('long', "<>", 0)->get();
        foreach ($polygons as $value) {
            $food->generateRandomDeliveries($value);
        }
        $food->prepareRoutingSimulation($polygons);
    }

}
