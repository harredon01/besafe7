<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        // $this->call(UsersTableSeeder::class);
        $this->call(LocationsSeeder::class);
        $this->command->info('Locations seeded!');
        $this->call(UserTableSeeder::class);
        $this->command->info('User table seeded!');
//                $this->call('VehiclesOperationsSeeder');
//                $this->command->info('Vehicles Operations seeded!');
        $this->call(MerchantTableSeeder::class);
        $this->command->info('Merchants seeded!');
        
//        $this->call(FoodMerchantSeeder::class);
//        $this->command->info('Food seeded!'); 

        /* $this->call('MerchantTableSeeder');
          $this->command->info('Merchant table seeded!'); */
    }

}
