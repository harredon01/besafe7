<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Vehicle;
use App\Models\City;
use App\Models\Region;
use App\Services\EditOperation;
use App\Services\EditVehicle;

class VehiclesOperationsSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */

    /**
     * The edit operations implementation.
     *
     */
    protected $editOperations;

    /**
     * The edit vehicles implementation.
     *
     */
    protected $editVehicles;

    public function __construct(EditOperation $editOperations, EditVehicle $editVehicles) {
        $this->editOperations = $editOperations;
        $this->editVehicles = $editVehicles;
    }

    public function run() {
        $this->createCategories();
        $this->createVehicles();

        $this->editVehicles();
        $this->createCargos();
        $this->editCargos();
        $this->createRoutes();
        $this->editRoutes();
        $this->createRouteStops();
    }
    
    public function createCategories() {
        $category = Category::create([
                    'type' => 'vehicle',
                    'name' => 'small truck',
                    'level' => 1,
                    'description' => ' big el truckows',
        ]);
        $category = Category::create([
                    'type' => 'vehicle',
                    'name' => 'medium truck',
                    'level' => 1,
                    'description' => ' big el truckows',
        ]);
        $category = Category::create([
                    'type' => 'cargo',
                    'name' => 'small cargo',
                    'level' => 1,
                    'description' => ' big el cargo',
        ]);
        $category = Category::create([
                    'type' => 'cargo',
                    'name' => 'medium cargo',
                    'level' => 1,
                    'description' => ' big el cargo',
        ]);
    }

    public function createVehicles() {
        $users = User::all();
        $category = Category::create([
                    'type' => 'vehicle',
                    'name' => 'big truck',
                    'level' => 1,
                    'description' => ' big el truckows',
        ]);
        foreach ($users as $user) {
            for ($x = 0; $x <= 4; $x++) {
                $data = [
                    'category_id' => $category->id,
                    'axis' => 3 + $x,
                    'plates' => 'BNN 98' . $x,
                    'vin_number' => 'BNNasdfasdf98' . $x,
                    'make' => 'make' . $x,
                    'model' => 'model' . $x,
                    'color' => 'color' . $x,
                    'image' => 'img' . $x,
                    'year' => 1990 + $x,
                    'full_length' => 14 + $x,
                    'horse_power' => "101sdfg" . $x,
                    'description' => "the best el truco number " . $x,
                    'cargo_width' => 15 + $x,
                    'cargo_length' => 15 + $x,
                    'cargo_height' => 19 + $x,
                    'cargo_weight' => 134 + $x,
                ];
                $this->editVehicles->saveOrCreateVehicle($data, $user);
            }
        }
    }

    public function editVehicles() {
        $users = User::all();
        foreach ($users as $user) {
            $x = 10;
            foreach ($user->vehicles as $vehicle) {
                $data = [
                    'vehicle_id' => $vehicle->id,
                    'category_id' => $vehicle->category_id,
                    'axis' => 3 + $x,
                    'plates' => 'BNN 98' . $x,
                    'vin_number' => 'BNNasdfasdf98' . $x,
                    'make' => 'make' . $x,
                    'model' => 'model' . $x,
                    'color' => 'color' . $x,
                    'image' => 'img' . $x,
                    'year' => 1990 + $x,
                    'full_length' => 14 + $x,
                    'horse_power' => "101sdfg" . $x,
                    'description' => "the best el truco number " . $x,
                    'cargo_width' => 15 + $x,
                    'cargo_length' => 15 + $x,
                    'cargo_height' => 19 + $x,
                    'cargo_weight' => 134 + $x,
                ];
                $x++;
                $this->editVehicles->saveOrCreateVehicle($data, $user);
            }
        }
    }

    public function editCargos() {
        $users = User::all();
        foreach ($users as $user) {
            $x = 10;
            foreach ($user->cargos as $cargo) {
                $regionnum = rand(1, 5);
                $city = (($regionnum - 1) * 5) + (rand(1, 5));
                $regionnum2 = rand(1, 4);
                $city2 = (($regionnum2 - 1) * 5) + (rand(1, 5));
                $date = date_create("2015-" . rand(1, 12) . "-" . rand(1, 28) . "");
                $dacity = City::find(intval($city));
                $daregion = Region::find(intval($regionnum));
                $dacity2 = City::find(intval($city2));
                $daregion2 = Region::find(intval($regionnum2));
                $data = [
                    'cargo_id' => $cargo->id,
                    'category_id' => $cargo->category_id,
                    'from_city_id' => $city,
                    'from_region_id' => $regionnum,
                    'from_country_id' => 1,
                    'from_city_name' => $dacity->name,
                    'from_region_name' => $daregion->name,
                    'from_country_name' => 'Colombia',
                    'to_city_id' => $city2,
                    'to_region_id' => $regionnum2,
                    'to_country_id' => 1,
                    'to_city_name' => $dacity2->name,
                    'to_region_name' => $daregion2->name,
                    'to_country_name' => 'Colombia',
                    'arrival' => $date,
                    'width' => 15 + $x,
                    'status' => 1,
                    'description' => "the description2",
                    'image' => "daimage2.jpg",
                    'offer' => 15 + $x,
                    'length' => 15 + $x,
                    'height' => 15 + $x,
                    'weight' => 15 + $x,
                ];
                $x++;
                $this->editOperations->saveOrCreateCargo($data, $user);
            }
        }
    }

    public function createCargos() {
        $users = User::all();
        $category = Category::create([
                    'type' => 'cargo',
                    'name' => 'big cargo',
                    'level' => 1,
                    'description' => ' big el cargo',
        ]);
        foreach ($users as $user) {
            for ($x = 0; $x <= 4; $x++) {
                $regionnum = rand(1, 5);
                $city = (($regionnum - 1) * 5) + (rand(1, 5));
                $regionnum2 = rand(1, 4);
                $city2 = (($regionnum2 - 1) * 5) + (rand(1, 5));
                $date = date_create("2015-" . rand(1, 12) . "-" . rand(1, 28) . "");
                $dacity = City::find(intval($city));
                $daregion = Region::find(intval($regionnum));
                $dacity2 = City::find(intval($city2));
                $daregion2 = Region::find(intval($regionnum2));
                /* $dateto = $date;
                  date_add($dateto, date_interval_create_from_date_string(rand(10, 28)." days")); */
                $data = [
                    'category_id' => $category->id,
                    'from_city_id' => $city,
                    'from_region_id' => $regionnum,
                    'from_country_id' => 1,
                    'from_city_name' => $dacity->name,
                    'from_region_name' => $daregion->name,
                    'from_country_name' => 'Colombia',
                    'to_city_id' => $city2,
                    'to_region_id' => $regionnum2,
                    'to_country_id' => 1,
                    'to_city_name' => $dacity2->name,
                    'to_region_name' => $daregion2->name,
                    'to_country_name' => 'Colombia',
                    'arrival' => $date,
                    'width' => 15 + $x,
                    'status' => 1,
                    'description' => "the description",
                    'image' => "daimg.jpg",
                    'offer' => 15 + $x,
                    'length' => 15 + $x,
                    'height' => 15 + $x,
                    'weight' => 15 + $x,
                ];
                $this->editOperations->saveOrCreateCargo($data, $user);
            }
        }
    }

    public function createRoutes() {
        $users = User::all();
        foreach ($users as $user) {
            foreach ($user->vehicles as $vehicle) {
                for ($x = 0; $x <= 4; $x++) {
                    $data = [
                        'vehicle_id' => $vehicle->id,
                        'description' => "descripcion de la ruta ".($x+1),
                        'width' => 15 + $x,
                        'status' => 1,
                        'length' => 15 + $x,
                        'height' => 15 + $x,
                        'weight' => 15 + $x,
                        'unit_price' => 15 + $x,
                        'unit' => "precio por kilo por metro",
                        'weight' => 15 + $x,
                    ];
                    $this->editOperations->saveOrCreateRoute($data, $user);
                }
            }
        }
    }

    public function editRoutes() {
        $users = User::all();
        foreach ($users as $user) {
            $x = 10;
            foreach ($user->vehicles as $vehicle) {
                foreach ($vehicle->routes as $route) {
                    $vechicle = Vehicle::first();
                    $data = [
                        'id' => $route->id,
                        'description' => "mega descripcion de la ruta ".($x+1),
                        'vehicle_id' => $vechicle->id,
                        'width' => 15 + $x,
                        'status' => 1,
                        'length' => 15 + $x,
                        'height' => 15 + $x,
                        'weight' => 15 + $x,
                        'unit_price' => 15 + $x,
                        'unit' => "precio por kilo por metrowerwer",
                        'weight' => 15 + $x,
                    ];
                    $this->editOperations->saveOrCreateRoute($data, $user);
                    $x++;
                }
            }
        }
    }

    public function createRouteStops() {
        $users = User::all();
        foreach ($users as $user) {
            foreach ($user->routes as $route) {
                for ($x = 0; $x <= 4; $x++) {
                    $regionnum = rand(1, 5);
                    $city = (($regionnum - 1) * 5) + (rand(1, 5));
                    $date = date_create("2015-" . rand(1, 12) . "-" . rand(1, 28) . "");
                    $dacity = City::find(intval($city));
                    $daregion = Region::find(intval($regionnum));
                    $data = [
                        'route_id' => $route->id,
                        'stop_order' => $x + 1,
                        'city_id' => $city,
                        'city_name' => $dacity->name,
                        'region_id' => $regionnum,
                        'region_name' => $daregion->name,
                        'country_id' => 1,
                        'country_name' => "colombia",
                        'arrival' => $date,
                    ];
                    $this->editOperations->setRouteStop($user, $data);
                }
            }
        }
    }

}
