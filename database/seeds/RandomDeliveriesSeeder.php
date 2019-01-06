<?php

use Illuminate\Database\Seeder;
use App\Services\Food;
use App\Models\Delivery;
use App\Models\Route;
use App\Models\OrderAddress;

class RandomDeliveriesSeeder extends Seeder {
    /**
     * The edit profile implementation.
     *
     */
    protected $food;

    public function __construct(Food $food ) {
        $this->food = $food;
    }

    public function run() {
        $this->deleteOldData();
        $this->food->generateRandomDeliveries(4.670129, -74.051013);
    }

    public function deleteOldData() {
        $deliveries = Delivery::where("user_id", 1)->get();
        foreach ($deliveries as $item) {
            DB::table('delivery_stop')
                    ->where('delivery_id', $item->id)
                    ->delete();
            DB::table('delivery_route')
                    ->where('delivery_id', $item->id)
                    ->delete();
            $item->delete();
        }
        $routes = Route::where("status", "pending")->get();
        foreach ($routes as $value) {
            $value->stops()->delete();
        }
        Route::where("status", "pending")->delete();

        OrderAddress::where("name", "test")->delete();
    }

}
