<?php

use Illuminate\Database\Seeder;
use App\Services\GoogleSheets;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use App\Models\Delivery;
use App\Models\Route;
use App\Models\OrderAddress;

class SheetsSeeder extends Seeder {



    /**
     * The edit profile implementation.
     *
     */
    protected $sheets;

    public function __construct(GoogleSheets $sheets) {
        $this->sheets = $sheets;
    }

    public function run() {
        $data = [
            ['name'=>"test"],
            ['name'=>"test2"]
        ];
        $this->sheets->createSpreadsheet("test2",$data);
    }

}
