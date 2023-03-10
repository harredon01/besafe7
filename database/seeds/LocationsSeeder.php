<?php

use Illuminate\Database\Seeder;
use App\Services\MerchantImport;

class LocationsSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */

    /**
     * The edit profile implementation.
     *
     */
    protected $merchantImport;

    public function __construct(MerchantImport $merchantImport) {
        $this->merchantImport = $merchantImport;
        /* $this->middleware('location.group', ['only' => 'postGroupLocation']);
          $this->middleware('location.group', ['only' => 'getGroupLocation']); */
    }

    public function run() {
        DB::table('subscriptions')->delete();
        DB::table('plans')->delete();
        DB::table('sources')->delete();
        DB::table('files')->delete();
        DB::table('reports')->delete();
        DB::table('medicals')->delete();
        DB::table('cart_conditions')->delete();
        DB::table('stops')->delete();
        DB::table('routes')->delete();
        DB::table('vehicles')->delete();
        DB::table('cargos')->delete();
        DB::table('translations')->delete();
        DB::table('order_addresses')->delete();
        DB::table('coverage_polygons')->delete();

        DB::table('categories')->delete();
        DB::table('notifications')->delete();
        DB::table('userables')->delete();
        DB::table('userables_historic')->delete();
        DB::table('items')->delete();
        DB::table('orders')->delete();
        DB::table('historic_location2')->delete();
        DB::table('historic_location')->delete();
        DB::table('locations')->delete();
        DB::table('messages')->delete();
        DB::table('group_user')->delete();
        DB::table('contacts')->delete();
        DB::table('merchant_payment_methods')->delete();
        DB::table('merchant_user')->delete();
        DB::table('merchant_product')->delete();
        DB::table('addresses')->delete();
        DB::table('articles')->delete();
        
        DB::table('category_product')->delete();
        DB::table('category_merchant')->delete();
        DB::table('product_variant_attribute_option')->delete();
        DB::table('attribute_options')->delete();
        DB::table('attributes')->delete();
        DB::table('product_variant')->delete();
        DB::table('products')->delete();
        DB::table('categories')->delete();
        DB::table('office_hours')->delete();
        DB::table('merchant_payment_methods')->delete();
        DB::table('merchants')->delete();
        DB::table('payment_methods')->delete();
        DB::table('cities')->delete();
        DB::table('regions')->delete();
        DB::table('countries')->delete();
        DB::table('groups')->delete();
        DB::table('users')->delete();
        $this->createLocations();
    }

    public function createLocations() {
        //$this->merchantImport->exportMerchantJson("/home/hoovert/hospitales.json");
        $this->merchantImport->importCountries("countries.xlsx");
        //$this->merchantImport->importCountries2("GeoLite2-Country-Locations-en.xlsx");
        //$this->merchantImport->importCountriesBlocks("GeoLite2-Country-Blocks-IPv4.xlsx");
        $this->merchantImport->importCountriesAreas("countries.xlsx");
        $this->merchantImport->importRegions("regions.xlsx");
        /*$this->merchantImport->importCities2("GeoLite2-City-Locations-en.xlsx");
        $this->merchantImport->importCitiesBlocks("GeoLite2-City-Blocks-IPv4.xlsx");
        $this->merchantImport->importCitiesData("cities.xlsx");*/
        $this->merchantImport->importCities("cities.xlsx");
    }

}
