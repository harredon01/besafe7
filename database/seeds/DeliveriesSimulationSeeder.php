<?php

use Illuminate\Database\Seeder;
use App\Services\Food;
use App\Models\CoveragePolygon;
use App\Mail\ScenarioSelect;
use App\Models\User;

class DeliveriesSimulationSeeder extends Seeder {

    /**
     * The edit profile implementation.
     *
     */
    protected $food;

    public function __construct(Food $food) {
        $this->food = $food;
    }

    public function run() {
        $polygons = CoveragePolygon::where('lat',"<>",0)->where('long',"<>",0)->get();
        $user = User::find(2);
        $this->food->deleteOldData();
        $this->food->prepareRoutingSimulation($polygons);
        $results = $this->food->getShippingCosts();
        //Mail::to($user)->send(new ScenarioSelect($results['resultsPre'], $results['resultsSimple'], $results['winner'], $value->id));
    }
    
    

}
