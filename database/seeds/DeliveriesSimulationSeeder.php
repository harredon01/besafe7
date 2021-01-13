<?php

use Illuminate\Database\Seeder;
use App\Services\Food;
use App\Models\CoveragePolygon;
use App\Mail\ScenarioSelect;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

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
        $pages = [
            ["view"=>"dsfsdf","name"=>"sdfsdfsdf"],
            ["view"=>"dsfsdf","name"=>"sdfsdfsdf"],
            ["view"=>"dsfsdf","name"=>"sdfsdfsdf"]
        ];
        $menu = view('sitemap', compact('pages'))->render();
        dd($menu);
    }
    
    

}
