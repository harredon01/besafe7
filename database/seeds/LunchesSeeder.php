<?php

use Illuminate\Database\Seeder;
use App\Services\FoodImport;
use App\Models\Article;

class LunchesSeeder extends Seeder {

    /**
     * The edit profile implementation.
     *
     */
    protected $food;

    public function __construct(FoodImport $food) {
        $this->food = $food;
    }

    public function run() {
        Article::where("id",">",0)->delete();
        $this->food->importDishes();
        //Mail::to($user)->send(new ScenarioSelect($results['resultsPre'], $results['resultsSimple'], $results['winner'], $value->id));
    }
    
    

}
