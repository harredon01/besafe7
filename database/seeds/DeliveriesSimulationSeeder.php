<?php

use Illuminate\Database\Seeder;
use App\Services\Food;

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
        $this->food->prepareRoutingSimulation(4.670129, -74.051013);
    }

}
