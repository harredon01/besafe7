<?php

namespace App\Services;

use App\Models\User;
use App\Models\Article;
use App\Models\Delivery;
use App\Models\OrderAddress;
use App\Models\Route;
use App\Models\Merchant;
use App\Models\Stop;
use App\Jobs\RegenerateScenarios;
use App\Models\CoveragePolygon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RouteDeliver;
use App\Exports\ArrayMultipleSheetExport;
use App\Mail\ScenarioSelect;
use DB;
use Excel;

class Routing {

    const OBJECT_ORDER = 'Order';
    const CREDIT_PRICE = 14000;
    const LUNCH_ROUTE = 16;
    const LUNCH_PROFIT = 1100;
    const ROUTE_HOUR_COST = 11000;
    const ROUTE_HOURS_EST = 3;
    const UNIT_LOYALTY_DISCOUNT = 11000;
    const OBJECT_ORDER_REQUEST = 'OrderRequest';
    const ORDER_PAYMENT = 'order_payment';
    const PAYMENT_APPROVED = 'payment_approved';
    const PAYMENT_DENIED = 'payment_denied';
    const PLATFORM_NAME = 'food';
    const ORDER_PAYMENT_REQUEST = 'order_payment_request';

    public function updateUserDeliveriesAddress($user_id, $address_id) {
        $address = OrderAddress::find($address_id);
        if ($address) {
            $deliveries = Delivery::where('user_id', $user_id)->update(['address_id' => $address_id]);
            return $deliveries;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function updateRouteStop($route, $stopContainer) {
        $stop = Stop::find($stopContainer);
        $oldRoute = $stop->route;
        $oldRoute->unit -= $stop->amount;
        $oldRoute->unit_price -= $stop->shipping;
        $oldRoute->save();
        $newRoute = Route::find($route);
        $newRoute->unit += $stop->amount;
        $newRoute->unit_price += $stop->shipping;
        $newRoute->save();
        $stop->route_id = $newRoute->id;

        $stopContainer = $stop->toArray();
        unset($stopContainer['id']);
        $newStop = Stop::create($stopContainer);
        DB::statement("UPDATE delivery_stop set stop_id=$newStop->id where stop_id = $stop->id ;");
        foreach ($stop->deliveries as $value) {
            DB::statement("UPDATE delivery_route set route_id=$newRoute->id where route_id = $oldRoute->id and delivery_id = $value->id ;");
        }
        $stop->delete();
    }

    public function updateRouteDelivery($data) {
        $delivery = Delivery::find($data['delivery_id']);
        $details = json_decode($delivery->details, true);
        $hasPickup = false;
        $pickupString = "";
        if (array_key_exists("pickup", $details)) {
            $user = $delivery->user;
            $hasPickup = true;
            $pickupString = "Envase de " . $user->firstName . " " . $user->lastName;
        }
        $stop = Stop::where("route_id", $data['route_id'])->where("address_id", $delivery->address_id)->first();
        $newRoute = Route::find($data['route_id']);
        $oldStop = Stop::find($data['old_stop_id']);
        $oldRoute = $oldStop->route;
        if ($stop) {
            DB::statement("UPDATE delivery_stop set stop_id=$stop->id where delivery_id = $delivery->id and stop_id = $oldStop->id ;");

            $stop->shipping += $delivery->shipping;
            if ($hasPickup) {
                $details = json_decode($stop->details, true);
                if (array_key_exists("pickups", $details)) {
                    array_push($details["pickups"], $pickupString);
                } else {
                    $details["pickups"] = [$pickupString];
                }
            }
            $stop->amount++;
            $stop->save();
        } else {
            $stopContainer = [
                "deliveries" => [$delivery],
                "address_id" => $delivery->address_id,
                "amount" => 1,
                "shipping" => $delivery->shipping
            ];
            if ($hasPickup) {
                $stopContainer["pickups"] = [$pickupString];
            }
            $this->addToNewStop($newRoute, $stopContainer);
        }
        $oldStop->shipping -= $delivery->shipping;
        $oldStop->amount--;
        $oldStop->save();
        $newRoute->unit++;
        $newRoute->unit_price += $delivery->shipping;
        $newRoute->save();
        $oldRoute->unit--;
        $oldRoute->unit_price = $oldRoute->unit_price - $delivery->shipping;
        $oldRoute->save();
        DB::statement("UPDATE delivery_route set route_id=$newRoute->id where delivery_id = $delivery->id and route_id = $oldRoute->id;");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function deleteRoute($route) {
        $route = Route::find($route);
        if ($route) {
            $deliveries = $route->deliveries;
            if (count($deliveries) > 0) {
                return array("status" => "error", "message" => "Cant delete a route with deliveries");
            } else {
                foreach ($route->stops as $stop) {
                    $stop->delete();
                }
                $route->delete();
                return array("status" => "success", "message" => "Route deleted");
            }
        }
        return array("status" => "error", "message" => "Route not found");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function deleteStop($stop) {
        $stop = Stop::find($stop);
        if ($stop) {
            $deliveries = $stop->deliveries;
            if (count($deliveries) > 0) {
                return array("status" => "error", "message" => "Cant delete a stop with deliveries");
            } else {
                $stop->delete();
                return array("status" => "success", "message" => "Stop deleted");
            }
        }
        return array("status" => "error", "message" => "Stop not found");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function sendStopToNewRoute($stopContainer) {
        $stop = Stop::find($stopContainer);
        $oldRoute = $stop->route;
        $oldRoute->unit -= $stop->amount;
        $oldRoute->unit_price -= $stop->shipping;
        $oldRoute->save();
        $newRoute = $this->createNewRoute($oldRoute->type, $oldRoute->provider);
        $newRoute->unit += $stop->amount;
        $newRoute->unit_price += $stop->shipping;
        $newRoute->save();
        $stop->route_id = $newRoute->id;
        $stopContainer = $stop->toArray();
        unset($stopContainer['id']);
        $newStop = Stop::create($stopContainer);
        foreach ($stop->deliveries as $value) {
            DB::statement("UPDATE delivery_route set route_id=$newRoute->id where route_id = $oldRoute->id and delivery_id = $value->id ;");
        }
        DB::statement("UPDATE delivery_stop set stop_id=$newStop->id where stop_id = $stop->id ;");

        $stop->delete();

        return $newRoute;
    }

    private function calculateRoutesShipping($routes) {
        $totalCost = 0;
        $totalIncomeShipping = 0;
        $totalLunches = 0;
        $scenarioHash = "";
        $scenarioHashId = "";
        if (count($routes) > 0) {
            $scenroute = $routes[0];
            $scenarioHash = $this->generateHash($scenroute->id, $scenroute->created_at);
            $scenarioHashId = $scenroute->id;
        }
        $className = "App\\Services\\Rapigo";
        $rapigo = new $className();
        $className2 = "App\\Services\\Basilikum";
        $basilikum = new $className2();
        foreach ($routes as $route) {
            $stops = $route->stops;
            $queryStops = [];
            $income = 0;
            $routeCost = self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST;
            foreach ($stops as $stop) {
                $income += $stop->shipping;
                $address = $stop->address;
                $querystop = [
                    "address" => $address->address,
                    "description" => $address->name,
                    "type" => "point",
                    "phone" => $address->phone
                ];
                array_push($queryStops, $querystop);
            }
            if ($route->provider == "Rapigo") {
                $rapigoResponse = $rapigo->getEstimate($queryStops);
                //dd($rapigoResponse);
            } else if ($route->provider == "Basilikum") {
                $rapigoResponse = $basilikum->getEstimate($queryStops);
            }
            if (array_key_exists("price", $rapigoResponse)) {
                if ((self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST) > $rapigoResponse['price']) {
                    $routeCost = $rapigoResponse['price'];
                }
            } else {
                $routeCost =self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST;
            }
            $route->unit_price = $income;
            $route->unit_cost = $routeCost;
            $route->save();
            $route->hash = $this->generateHash($route->id, $route->created_at);
            $totalCost += $routeCost;
            $totalIncomeShipping += $route->unit_price;
            $totalLunches += $route->unit;
        }
        $totalRoutes = count($routes);
        $totalIncome = $totalLunches * self::LUNCH_PROFIT;
        $totalProfit = $totalIncomeShipping + $totalIncome;

        $totalGains = $totalProfit - $totalCost;
        $result = [
            "ShippingCostEstimate" => $totalCost,
            "hoov_income" => $totalIncome,
            "shipping_income" => $totalIncomeShipping,
            "total_income" => $totalProfit,
            "routes" => $totalRoutes,
            "lunches" => $totalLunches,
            "lunch_route" => ($totalLunches / $totalRoutes),
            "day_profit" => $totalGains,
            "scenario_hash" => $scenarioHash,
            "scenarioHashId" => $scenarioHashId
        ];
        if ($result['ShippingCostEstimate'] < $result['shipping_income']) {
            $result['status'] = "success";
        } else {
            $result['status'] = "failure";
        }
        return array("routes" => $routes, "result" => $result);
    }

    private function buildRouteQuery(array $input) {
        $query = Route::query();
        foreach ($input as $column => $values) {
            $query->where($column, $values);
        }
        return $query;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getShippingCosts($user, $status) {
        $providers = ["Rapigo", "Basilikum"];
        foreach ($providers as $provider) {
            if ($provider == "Rapigo") {
                $data = [
                    "type" => "preorganize",
                    "status" => $status,
                    "provider" => $provider
                ];

                $resultsPre = $this->getTotalEstimatedShipping($data);
                $resultsPre = $resultsPre['result'];
                $resultsPre['route_provider'] = $provider;
                $resultsPre['route_status'] = "pending";
                $data = [
                    "type" => "simple",
                    "status" => $status,
                    "provider" => $provider
                ];
                $resultsSimple = $this->getTotalEstimatedShipping($data);
                $resultsSimple = $resultsSimple['result'];
                $resultsSimple['route_provider'] = $provider;
                $resultsSimple['route_status'] = "pending";
            } else if ($provider == "Basilikum") {
                $data = [
                    "status" => $status,
                    "provider" => $provider
                ];
                $resultsPre = $this->getTotalEstimatedShipping($data);
                $resultsPre = $resultsPre['result'];
                $resultsPre['route_provider'] = $provider;
                $resultsPre['route_status'] = "pending";
                $resultsSimple = $resultsPre;
            }
            $winningScenario = "";
            if (!array_key_exists("day_profit", $resultsPre)) {
                continue;
            }
            if ($resultsPre["day_profit"] > $resultsSimple["day_profit"]) {
                $winningScenario = "Preorganize";
            } else {
                $winningScenario = "Simple";
            }

            Mail::to($user)->send(new ScenarioSelect($resultsPre, $resultsSimple, $winningScenario));
        }
        return array("status" => "success", "message" => "costs sent");
    }

    public function getTotalEstimatedShipping(array $input) {
        $query = $this->buildRouteQuery($input);
        $routes = $query->with(['stops.address'])->orderBy('id', 'asc')->get();
        if ($routes) {
            if (count($routes) > 0) {
                return $this->calculateRoutesShipping($routes);
            }
        }
        return array("routes" => [], "result" => []);
    }

    public function checkScenario($results, $hash) {
        if (count($results) > 0) {
            $scenroute = $results[0];
            return $this->checkHash($scenroute->id, $scenroute->created_at, $hash);
        }
        return false;
    }

    public function regenerateScenarios() {
        $this->deleteOldData();
        $user = User::find(2);
        $merchants = Merchant::all();
        $shippingProviders = ["Rapigo", "Basilikum"];
        foreach ($merchants as $merchant) {
            foreach ($shippingProviders as $item) {
                $polygons = CoveragePolygon::where('merchant_id', $merchant->id)->where("provider", $item)->get();
                $this->prepareRoutingSimulation($polygons, $item);
            }
        }
        $this->getShippingCosts($user, "pending");
    }

    public function buildScenarioTransit($routes) {
//        dd($results);
        foreach ($routes as $route) {
            $route->status = "enqueue";
            foreach ($route->deliveries as $delivery) {
                $delivery->status = "scheduled";
                $delivery->save();
            }
            $stops = $route->stops()->with(['address', 'deliveries.user'])->get();
            foreach ($stops as $stop) {
                $stop->code = "stop-" . $stop->id;
                $stop->status = "pending";
                $stop->save();
            }
            $route->save();
        }
        return true;
    }

    public function buildScenarioLogistics(array $input) {
        //$input['status'] = "scheduled";
        $query = $this->buildRouteQuery($input);
        $routes = $query->with(['deliveries.user'])->orderBy('id')->get();
        if ($routes) {
            $totalCost = 0;
            $deliveries = $routes[0]->deliveries;

            $totalIncomeShipping = 0;
            $className = "App\\Services\\Rapigo";
            $rapigo = new $className();
            $className2 = "App\\Services\\Basilikum";
            $basilikum = new $className2();
            $className3 = "App\\Services\\Food";
            $platform = new $className3();
            $config = $platform->loadDayConfig($deliveries[0]->delivery);
            $totalLunches = 0;
            $pages = [];
            foreach ($routes as $route) {
                $results = [];
                $arrayRouteTitle = ["Ruta", "Proveedor", "Costo estimado", "Ingreso Envio"];
                array_push($results, $arrayRouteTitle);
                $arrayRoute = [$route->id, $route->provider, $route->unit_cost, $route->unit_price];
                array_push($results, $arrayRoute);
                $arrayDelTitle = ["Parada", "Direccion", "Entrega", "Usuario", "Celular", "Tipo Envase", "Recoger Envase", "Entregar Factura", "Tipo", "Entrada", "Plato", "Observacion"];
                array_push($results, $arrayDelTitle);
                //$results = array_merge($results,$routeTotals['excel']);
                $stops = $route->stops()->with(['address', 'deliveries.user'])->get();
                $queryStops = [];
                foreach ($stops as $stop) {
                    $resultData = $platform->getStopDetails($results, $stop, $config);
                    $querystop = [
                        "address" => $stop->address->address . " " . $stop->address->notes,
                        "description" => $resultData['description'],
                        "type" => "point",
                        "phone" => $resultData['phone']
                    ];
                    array_push($queryStops, $querystop);
                    $results = $resultData['results'];
                }
                if ($route->provider == "Rapigo") {
                    $route = $rapigo->createRoute($queryStops, $route, $stops);
                } else if ($route->provider == "Basilikum") {
                    $route = $basilikum->createRoute($queryStops, $route, $stops);
                }
                $totalCost += $route->unit_cost;
                $totalIncomeShipping += $route->unit_price;
                $totalLunches += $route->unit;
                $page = [
                    "name" => "Ruta-" . $route->id . "-" . $route->provider,
                    "rows" => $results
                ];
                array_push($pages, $page);
            }

            $path = $this->writeFile($pages, "Rutas" . time());
            $users = User::whereIn('id', [2, 77])->get();
            Mail::to($users)->send(new RouteDeliver($routes, $path));
            $totalRoutes = count($routes);
            $totalIncome = $totalLunches * self::LUNCH_PROFIT;
            $totalProfit = $totalIncomeShipping + $totalIncome;

            $totalGains = $totalProfit - $totalCost;
            $result = [
                "ShippingCostEstimate" => $totalCost,
                "hoov_income" => $totalIncome,
                "shipping_income" => $totalIncomeShipping,
                "total_income" => $totalProfit,
                "routes" => $totalRoutes,
                "lunches" => $totalLunches,
                "lunch_route" => ($totalLunches / $totalRoutes),
                "day_profit" => $totalGains,
            ];
            if ($result['ShippingCostEstimate'] < $result['shipping_income']) {
                $result['status'] = "success";
            } else {
                $result['status'] = "failure";
            }
            return $result;
        }
    }

    public function writeFile($data, $title) {
        $file = Excel::store(new ArrayMultipleSheetExport($data), "exports/" . $title . ".xls", "local");
        $path = 'exports/' . $title . ".xls";
        return $path;
        return Excel::create($title, function($excel) use($data, $title) {

                    $excel->setTitle($title);
                    // Chain the setters
                    $excel->setCreator('Hoovert Arredondo')
                            ->setCompany('Lonchis');
                    // Call them separately
                    $excel->setDescription('This report is clasified');
                    foreach ($data as $page) {
                        $excel->sheet(substr($page["name"], 0, 30), function($sheet) use($page) {
                            $sheet->fromArray($page["rows"], null, 'A1', true);
                        });
                    }
                })->store('xlsx', storage_path('app/exports'));
    }

    public function generateHash($id, $created_at) {
        return base64_encode(Hash::make($id . $created_at . env('LONCHIS_KEY')));
    }

    public function checkHash($id, $created_at, $hash) {
        $keyDecoded = base64_decode($hash);
        if (Hash::check($id . $created_at . env('LONCHIS_KEY'), $keyDecoded)) {
            return true;
        } else {
            return false;
        }
    }

    public function prepareRouteModel($results, $preOrganize, CoveragePolygon $polygon) {
//        dd($thedata);
        $date = date_create();
        $datelimit = date_format($date, "Y-m-d");
        $now = date_format($date, "Y-m-d H:i:s");
        $dateTimestampLimit = strtotime($datelimit . " 11:00:00");
        $dateTimestampNow = strtotime($now);
        if ($dateTimestampNow > $dateTimestampLimit) {
            $date = $this->getNextValidDate($date);
        }
        $la = date_format($date, "Y-m-d");
        $thedata = [
            'lat' => $polygon->lat,
            'lat2' => $polygon->lat,
            'long' => $polygon->long,
            'polygon' => $polygon->id,
            'provider' => $polygon->provider,
            'date1' => $la . " 00:00:00",
            'date2' => $la . " 23:59:59",
        ];


# your laravel query builder goes here
        if ($polygon->id == 31) {
//            DB::enableQueryLog();
        }
        $deliveries = DB::select(""
                        . "SELECT DISTINCT(d.id), d.delivery,d.provider,d.details,d.user_id,a.address,d.address_id,status,shipping, lat,`long`, 
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  lat  ) ) ) ) AS Distance 
                   FROM deliveries d join order_addresses a on d.address_id = a.id
                    WHERE
                        status = 'enqueue' AND provider = :provider
                        AND d.delivery >= :date1 AND d.delivery <= :date2 
                            AND a.polygon_id = :polygon order by Distance asc"
                        . "", $thedata);
        if ($polygon->id == 31) {
//            dd(DB::getQueryLog());
        }
        //echo "Query params: ". json_encode($thedata). PHP_EOL;
        //echo "Query results: " . count($deliveries) . PHP_EOL;
        $stops = $this->turnDeliveriesIntoStops($deliveries, $preOrganize);
        if ($polygon->provider == "Basilikum") {
            //dd($stops);
        }
        if ($preOrganize) {
            $results = $this->createRoutes($stops, $results, 'preorganize', true, $polygon->provider);
        } else {
            $results = $this->createRoutes($stops, $results, 'simple', true, $polygon->provider);
        }
        //dd($results);
        return $results;
    }

    public function getLargestAddresses() {
        $results = DB::select(""
                        . "SELECT count(d.id) as total,oa.address, d.provider,d.delivery,d.merchant_id FROM deliveries d "
                        . "join order_addresses oa on oa.id = d.address_id "
                        . "where d.status='enqueue' group by oa.address,d.provider limit 50;");
        return $results;
    }

    public function delegateDeliveries($data) {
        $address = OrderAddress::where("address", $data['address'])->first();
        $geo = app('Geolocation');

        $resultsGeo = $geo->checkMerchantPolygons($address->lat, $address->long, $data['merchant_id'], $data['provider']);
        if (array_key_exists('polygon', $resultsGeo)) {
            $polygon = $resultsGeo['polygon'];
            $thedata = ["address" => $data['address'], "provider" => $data['provider']];
            $thedata2 = ["address" => $data['address']];
            $results = DB::statement(""
                            . " UPDATE deliveries set provider=:provider where address_id in (Select id from order_addresses where address = :address );", $thedata);
            DB::statement(""
                    . " UPDATE order_addresses set polygon_id=$polygon->id where address = :address ;", $thedata2);

            dispatch(new RegenerateScenarios());
            return $results;
        }
        return $resultsGeo;
    }

    private function addToExistingStop(Stop $itemSavedStop, Route $route, $stopContainer) {
        $stopDetails = json_decode($itemSavedStop->details, true);
        if (array_key_exists("pickups", $stopDetails)) {
            $stopDetails['pickups'] = array_merge($stopDetails['pickups'], $stopContainer["pickups"]);
            $itemSavedStop->details = json_encode($stopDetails);
        }
        $itemSavedStop->amount += $stopContainer['amount'];
        $itemSavedStop->shipping += $stopContainer['shipping'];
        foreach ($stopContainer["deliveries"] as $del) {
            $realDel2 = Delivery::find($del->id);
            $itemSavedStop->deliveries()->save($realDel2);
            $route->deliveries()->save($realDel2);
        }
        $itemSavedStop->save();
    }

    private function addToNewStop(Route $route, $stopContainer) {
        $details = ["pickups" => []];
        $details['pickups'] = $stopContainer["pickups"];
        $stop = Stop::create([
                    "address_id" => $stopContainer["address_id"],
                    "amount" => $stopContainer["amount"],
                    "shipping" => $stopContainer["shipping"],
                    "route_id" => $route->id,
                    "stop_order" => 2,
                    "details" => json_encode($details)
        ]);
        foreach ($stopContainer["deliveries"] as $del) {
            $realDel = Delivery::find($del->id);
            $stop->deliveries()->save($realDel);
            $route->deliveries()->save($realDel);
        }
    }

    private function createNewRoute($scenario, $shipping) {
        $route = new Route();
        $route->status = 'pending';
        $route->description = $scenario;
        $route->type = $scenario;
        $route->provider = $shipping;
        $serviceBookResponse = [];
        $location = [
            "runner" => "",
            "runner_phone" => "",
            "lat" => 0,
            "long" => 0
        ];
        $serviceBookResponse["location"] = $location;
        $route->coverage = json_encode($serviceBookResponse);
        $route->unit = 0;
        $route->height = 1;
        $route->unit_price = 0;
        $route->save();
        $address = OrderAddress::create([
                    "user_id" => 1,
                    "name" => "test",
                    "city_id" => 524,
                    "region_id" => 11,
                    "country_id" => 1,
                    "address" => "Carrera 58 D # 130 A - 43",
                    "lat" => 4.721717,
                    "long" => -74.069855,
                    "phone" => "3202261525"
        ]);
        $details = ["pickups" => []];

        Stop::create([
            "address_id" => $address->id,
            "amount" => 0,
            "shipping" => 0,
            "route_id" => $route->id,
            "stop_order" => 1,
            "details" => json_encode($details),
            "region_name" => "Recoger los almuerzos de la ruta " . $route->id,
        ]);
        return $route;
    }

    private function addToNewRouteAndStop($scenario, $stopContainer, $shipping) {
        $route = $this->createNewRoute($scenario, $shipping);
        $route->unit = $stopContainer['amount'];
        $route->unit_price = $stopContainer['shipping'];
        $details['pickups'] = $stopContainer["pickups"];
        $stopCreated = Stop::create([
                    "address_id" => $stopContainer["address_id"],
                    "amount" => $stopContainer["amount"],
                    "shipping" => $stopContainer['shipping'],
                    "route_id" => $route->id,
                    "stop_order" => 2,
                    "details" => json_encode($details)
        ]);
        foreach ($stopContainer["deliveries"] as $del) {
            $realDel = Delivery::find($del->id);
            $stopCreated->deliveries()->save($realDel);
            $route->deliveries()->save($realDel);
        }
        return $route;
    }

    private function addStopToRoute(Route $route, $stopContainer) {
        $route->unit += $stopContainer['amount'];
        $route->unit_price += $stopContainer['shipping'];
        $itemSavedStop = $route->stops()->where("address_id", $stopContainer["address_id"])->where("route_id", $route->id)->where("stop_order", 2)->first();
        if ($itemSavedStop) {
            $this->addToExistingStop($itemSavedStop, $route, $stopContainer);
        } else {
            $this->addToNewStop($route, $stopContainer);
        }
        $route->height++;
        $route->save();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function createRoutes($stops, $routes, $scenario, $save, $shipping) {
        foreach ($stops as $stopContainer) {
            $found = false;
            foreach ($routes as $route) {
                $available = self::LUNCH_ROUTE - $route->unit;
//                if ($shipping == "Basilikum") {
//                    $available = 100 - $route->unit;
//                }

                if (($available > 0 && $available >= $stopContainer['amount']) && !$found) {
                    $found = true;
                    $this->addStopToRoute($route, $stopContainer);
                    break;
                }
            }
            if ($found == false) {
                $route = $this->addToNewRouteAndStop($scenario, $stopContainer, $shipping);
                array_unshift($routes, $route);
            }
        }
        return $routes;
    }

    private static function cmp($a, $b) {
        if ($a['amount'] == $b['amount']) {
            return 0;
        }
        return ($a['amount'] < $b['amount']) ? -1 : 1;
    }

    private function addpickupStop(Route $route) {
        if ($route->provider == "Basilikum") {
            return;
        }
        $addStop = true;
        foreach ($route->stops as $stop) {
            $address = $stop->address;
            if ($address->phone == "3103418432") {
                $addStop = true;
            }
        }
        if ($addStop) {
            $address = OrderAddress::create([
                        "user_id" => 1,
                        "name" => "test",
                        "city_id" => 524,
                        "region_id" => 11,
                        "country_id" => 1,
                        "address" => "Carrera 1 este # 72a-90 Apto 202",
                        "lat" => 4.653610,
                        "long" => -74.049822,
                        "phone" => "3103418432"
            ]);
            $details = ["pickups" => []];
            $firstStop = Stop::create([
                        "address_id" => $address->id,
                        "amount" => 0,
                        "route_id" => $route->id,
                        "region_name" => "Entregar los envases recogidos",
                        "stop_order" => 3,
                        "details" => json_encode($details)
            ]);
        }
    }

    public function completeRoutes($routes) {
        foreach ($routes as $route) {
            $pickup = "";
            $addReturn = false;
            $stops = $route->stops;
            foreach ($stops as $stop) {
                if ($stop->stop_order != 1) {
                    $attributes = json_decode($stop->details, true);
                    if (array_key_exists("pickups", $attributes)) {
                        if (count($attributes['pickups']) > 0) {
                            $addReturn = true;
                        }
                    }
                }
            }
            if ($addReturn) {
                $this->addpickupStop($route);
            }
        }
    }

    public function generateScenario(User $user, array $data) {
        $totalCost = 0;
        $totalCost2 = 0;
        $totalIncome = 0;
        $totalIncomeShipping = 0;
        $totalProfit = 0;
        $totalRoutes = 0;
        $totalLunches = 0;
        $totalGains = 0;
        $routes = $data['routes'];

        foreach ($routes as $rt) {
            $route = Route::create([
                        "status" => "pending",
                        "description" => "user",
                        "user_id" => $user->id,
                        "availability" => $data['date']
            ]);
            $totalRoutes++;
            $stops = $rt['stops'];
            $stopsnum = count($stops);
            foreach ($stops as $st) {
                $stop = Stop::create([
                            "route_id" => $route->id,
                            "address_id" => $st['address_id'],
                ]);
                $deliveries = $st['deliveries'];
                foreach ($deliveries as $del) {
                    $totalLunches++;
                    $delivery = Delivery::find($del['id']);
                    $route->deliveries()->save($delivery);
                    $stop->deliveries()->save($delivery);
                }
            }
            if ($stopsnum > 4) {
                $totalCost += self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST;
            } else {
                $totalCost += self::ROUTE_HOUR_COST * 2;
            }
            $totalIncomeShipping += $rt->unit_price;
            if (($stopsnum + 1) > 7) {
                $totalCost2 += ($stopsnum + 1) * 5400;
            } else {
                $totalCost2 += ($stopsnum + 1) * 6400;
            }
        }
        $result = [
            "hourly_cost" => $totalCost,
            "stops_cost" => $totalCost2,
            "hoov_income" => $totalIncome,
            "shipping_income" => $totalIncomeShipping,
            "total_income" => $totalProfit,
            "routes" => $totalRoutes,
            "lunches" => $totalLunches,
            "lunch_route" => ($totalLunches / $totalRoutes),
            "profit" => $totalGains,
        ];
    }

    public function getShippingCostArray() {
        return [
            "1" => 3050.00,
            "2" => 2800.00,
            "3" => 2800.00,
            "4" => 2700.00,
            "5" => 2700.00,
            "6" => 2600.00,
            "7" => 2500.00,
            "8" => 2500.00,
            "9" => 2400.00,
            "10" => 2200.00,
            "11" => 2000.00,
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function turnDeliveriesIntoStops($deliveries, $preorganize) {
        $stops = array();

        if (count($deliveries) > 0) {
            $initialAddress = $deliveries[0]->address;
            $initialAddressId = $deliveries[0]->address_id;
            $deliveryCounter = 0;
            $deliveryShipping = 0;
            $packages = [];
            $pickups = [];
            $totalCounter = 0;
            foreach ($deliveries as $delivery) {
                $createNew = false;
                $attributes = json_decode($delivery->details, true);
                if ($preorganize) {
                    $totalCounter++;
                    if ($delivery->address == $initialAddress) {
                        if (($delivery->provider == "Rapigo" && $deliveryCounter < self::LUNCH_ROUTE) || ($delivery->provider == "Basilikum" && $deliveryCounter < 80)) {
                            $deliveryCounter++;
                            $deliveryShipping += $delivery->shipping;
                            array_push($packages, $delivery);
                            if (array_key_exists("pickup", $attributes)) {
                                if ($attributes['pickup'] == 'envase') {
                                    $delUser = User::find($delivery->user_id);
                                    $desc = "Envase de " . $delUser->firstName . " " . $delUser->lastName;
                                    array_push($pickups, $desc);
                                }
                            }
                        } else {
                            $createNew = true;
                        }
                    } else {
                        $createNew = true;
                    }
                    if ($createNew) {
                        $stop = [
                            "amount" => $deliveryCounter,
                            "address_id" => $initialAddressId,
                            "latitude" => $delivery->lat,
                            "longitude" => $delivery->long,
                            "shipping" => $deliveryShipping,
                            "deliveries" => $packages,
                            "pickups" => $pickups
                        ];
                        array_push($stops, $stop);
                        $packages = [];
                        array_push($packages, $delivery);
                        $pickups = [];
                        if (array_key_exists("pickup", $attributes)) {
                            if ($attributes['pickup'] == 'envase') {
                                $delUser = User::find($delivery->user_id);
                                $desc = "Envase de " . $delUser->firstName . " " . $delUser->lastName;
                                array_push($pickups, $desc);
                            }
                        }
                        $deliveryCounter = 1;
                        $deliveryShipping = $delivery->shipping;
                        $initialAddress = $delivery->address;
                        $initialAddressId = $delivery->address_id;
                    }
                    if ($totalCounter == count($deliveries)) {
                        $stop = [
                            "amount" => $deliveryCounter,
                            "address_id" => $initialAddressId,
                            "latitude" => $delivery->lat,
                            "longitude" => $delivery->long,
                            "shipping" => $deliveryShipping,
                            "deliveries" => $packages,
                            "pickups" => $pickups
                        ];
                        array_push($stops, $stop);
                    }
                } else {
                    if (array_key_exists("pickup", $attributes)) {
                        if ($attributes['pickup'] == 'envase') {
                            $delUser = User::find($delivery->user_id);
                            $desc = "Envase de " . $delUser->firstName . " " . $delUser->lastName;
                            array_push($pickups, $desc);
                        }
                    }
                    $packages = [$delivery];
                    $stop = [
                        "amount" => 1,
                        "address_id" => $delivery->address_id,
                        "latitude" => $delivery->lat,
                        "longitude" => $delivery->long,
                        "shipping" => $delivery->shipping,
                        "deliveries" => $packages,
                        "pickups" => $pickups
                    ];
                    array_push($stops, $stop);
                }
            }
            //dd($stops);
            if ($preorganize) {
                //usort($stops, array($this, 'cmp'));
            }
        }
        return $stops;
    }

    public function deleteRandomDeliveriesData() {
        $deliveries = Delivery::where("user_id", 5)->get();
        foreach ($deliveries as $item) {
            DB::table('delivery_stop')
                    ->where('delivery_id', $item->id)
                    ->delete();
            DB::table('delivery_route')
                    ->where('delivery_id', $item->id)
                    ->delete();
            $item->delete();
        }
        $routes = Route::whereIn("status", ["pending", "enqueue"])->get();
        foreach ($routes as $value) {
            $value->stops()->delete();
        }
        Route::where("status", "pending")->delete();
        Route::where("status", "enqueue")->delete();

        OrderAddress::where("name", "test")->delete();
    }

    public function generateRandomDeliveries(CoveragePolygon $polygon) {
        $lat = $polygon->lat;
        $long = $polygon->long;
        $radius = 1; // in miles
        $addresses = ["Carrera 7a # 64-44", "Calle 73 # 0 - 24", "Calle 53 # 5 - 74", "Calle 23 # 7 - 74", "Cra 9 # 77-67", "Cra 9 # 71-32", "Cra 9 # 71-32"];
        $R = 6371;
        $lat_max = ($lat + rad2deg($radius / $R)) * 1000000000;
        $lat_min = ($lat - rad2deg($radius / $R)) * 1000000000;
        $lng_max = ($long + rad2deg(asin($radius / $R) / cos(deg2rad($lat)))) * 1000000000;
        $lng_min = ($long - rad2deg(asin($radius / $R) / cos(deg2rad($lat)))) * 1000000000;
        $date = date_create();
        date_add($date, date_interval_create_from_date_string("1 days"));
        $shipping = $this->getShippingCostArray();

        $articles = Article::where('start_date', date_format($date, "Y-m-d"))->get();
        //$articles = Article::where('start_date', "2019-10-02")->get();
        //$date = date_create();
        $total = rand(1, 3);
        for ($x = 0; $x <= $total; $x++) {
            $latit = rand($lat_min, $lat_max) / 1000000000;
            $longit = rand($lng_min, $lng_max) / 1000000000;
            $addressIndex = rand(0, 6);

            $address = OrderAddress::create([
                        "user_id" => 5,
                        "name" => "test",
                        "city_id" => 524,
                        "region_id" => 11,
                        "country_id" => 1,
                        "polygon_id" => $polygon->id,
                        "address" => $addresses[$addressIndex],
                        "lat" => $latit,
                        "long" => $longit,
                        "phone" => "3105507245"
            ]);
            $amountDeliveries = rand(1, 3);
            for ($j = 0; $j <= $amountDeliveries; $j++) {
                $type_num = rand(0, count($articles) - 1);
                $art = $articles[$type_num];
                $dish = [];
                $attrs = json_decode($art->attributes, true);
                if (count($attrs['entradas']) > 0) {
                    $starter = rand(0, count($attrs['entradas']) - 1);
                    $starterPlate = $attrs['entradas'][$starter];
                    $dish['starter_id'] = $starterPlate['codigo'];
                }
                if (count($attrs['plato']) > 0) {
                    $main = rand(0, count($attrs['plato']) - 1);
                    $mainPlate = $attrs['plato'][$main];
                    $dish['main_id'] = $mainPlate['codigo'];
                }
                $pickingUp = rand(0, 1);
                $pickingUp = 0;
                $details = [];
                if ($pickingUp == 1) {
                    $details['pickup'] = "envase";
                }
                $dish['type_id'] = $art->id;
                $dish['dessert_id'] = null;
                $details['dish'] = $dish;
                $delivery = Delivery::create([
                            "user_id" => 5,
                            "delivery" => date_format($date, "Y-m-d") . " 12:00:00",
                            "provider" => "Rapigo",
                            "shipping" => $shipping[$amountDeliveries],
                            "status" => "enqueue",
                            "merchant_id" => 1299,
                            "address_id" => $address->id,
                            "details" => json_encode($details)
                ]);
            }
        }
        echo 'lng (min/max): ' . $lng_min . '/' . $lng_max . PHP_EOL;
        echo 'lat (min/max): ' . $lat_min . '/' . $lat_max . PHP_EOL;
    }

    public function deleteOldData() {
        $routes = Route::whereIn("status", ["pending", "enqueue"])->get();
        foreach ($routes as $value) {
            DB::table('delivery_route')
                    ->where('route_id', $value->id)
                    ->delete();
            $stops = $value->stops;
            foreach ($stops as $item) {
                DB::table('delivery_stop')
                        ->where('stop_id', $item->id)
                        ->delete();
                $item->delete();
            }
        }
        $routes = Route::whereIn("status", ["pending", "enqueue"])->delete();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function prepareRoutingSimulation($polygons, $shipping = "Rapigo") {
        $results = [];
        $results2 = [];
        foreach ($polygons as $polygon) {
            if ($shipping == "Rapigo") {
                $results2 = $this->prepareRouteModel($results2, false, $polygon, $shipping);
            }
            $results = $this->prepareRouteModel($results, true, $polygon, $shipping);
        }
        $this->completeRoutes($results);
        if ($shipping == "Rapigo") {
            $this->completeRoutes($results2);
        }
    }

    public function getRouteInfo($delivery_id) {
        $theData = ["delivery" => $delivery_id];
        $routes = DB::select("select route_id from delivery_route where delivery_id = :delivery", $theData);
        if (count($routes) > 0) {
            $route = Route::where("id", $routes[0]->route_id)->with(['stops.address'])->first();
            return $route;
        }
        return null;
    }

    public function getNextValidDate($date) {
        $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
        if ($dayofweek > 0 && $dayofweek < 6) {
            date_add($date, date_interval_create_from_date_string("1 days"));
        } else if ($dayofweek == 6) {
            date_add($date, date_interval_create_from_date_string("2 days"));
        } else if ($dayofweek == 0) {
            date_add($date, date_interval_create_from_date_string("1 days"));
        }

        $isHoliday = $this->checkIsHoliday($date);

        while ($isHoliday) {
            $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
            if ($dayofweek == 5) {
                date_add($date, date_interval_create_from_date_string("3 days"));
            } else if ($dayofweek == 6) {
                date_add($date, date_interval_create_from_date_string("2 days"));
            } else {
                date_add($date, date_interval_create_from_date_string("1 days"));
            }
            $isHoliday = $this->checkIsHoliday($date);
        }
        return $date;
    }

    public function checkIsHoliday($date) {
        $holidays = [
            "2020-01-01",
            "2020-01-02",
            "2020-01-03",
            "2020-01-06",
            "2020-03-23",
            "2020-04-09",
            "2020-04-10",
            "2020-05-01",
            "2020-05-25",
            "2020-06-15",
            "2020-06-22",
            "2020-06-29",
            "2020-07-20",
            "2020-08-07",
            "2020-08-17",
            "2020-10-12",
            "2020-11-02",
            "2020-11-16",
            "2020-12-08",
            "2020-12-24",
            "2020-12-25",
            "2020-12-31",
        ];
        foreach ($holidays as $day) {
            if ($day == date_format($date, "Y-m-d")) {
                return true;
            }
        }
        return false;
    }

}
