<?php

use Illuminate\Database\Seeder;
use App\Services\Food;

class FoodMerchantSeeder extends Seeder {

    /**
     * The edit profile implementation.
     *
     */
    protected $food;

    public function __construct(Food $food) {
        $this->food = $food;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        // $this->call(UsersTableSeeder::class);
        $this->food->importMerchants();
        $this->command->info('Food merchants seeded!');
    }

}
