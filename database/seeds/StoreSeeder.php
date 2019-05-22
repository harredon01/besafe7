<?php

use Illuminate\Database\Seeder;
use App\Services\StoreExport;


class StoreSeeder extends Seeder {


    /**
     * The edit profile implementation.
     *
     */
    protected $storeExport;


    public function __construct(StoreExport $storeExport) {
        $this->storeExport = $storeExport;
    }

    public function run() {
        $this->storeExport->exportEverything();
    }

}
