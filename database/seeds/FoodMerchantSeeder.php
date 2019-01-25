<?php

use Illuminate\Database\Seeder;
use App\Services\FoodImport;

class FoodMerchantSeeder extends Seeder {

    /**
     * The edit profile implementation.
     *
     */
    protected $foodImport;

    public function __construct(FoodImport $foodImport) {
        $this->foodImport = $foodImport;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        // $this->call(UsersTableSeeder::class);
        $this->foodImport->importMerchants();
        $this->command->info('Food merchants seeded!');
    }

}
