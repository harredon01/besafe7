<?php

use Illuminate\Database\Seeder;
use App\Services\Food;
use App\Models\Article;

class LunchesSeeder extends Seeder {

    /**
     * The edit profile implementation.
     *
     */
    protected $food;

    public function __construct(Food $food) {
        $this->food = $food;
    }

    public function run() {
        $this->food->reprogramDeliveries();
        //Mail::to($user)->send(new ScenarioSelect($results['resultsPre'], $results['resultsSimple'], $results['winner'], $value->id));
    }
    
    

}
