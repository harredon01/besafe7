<?php

namespace App\Services;

use App\Models\User;
use App\Models\Article;
use App\Models\Delivery;
use App\Models\OrderAddress;
use App\Models\Route;
use App\Models\Stop;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Address;
use App\Models\CoveragePolygon;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RouteDeliver;
use App\Mail\RouteOrganize;
use App\Mail\PurchaseOrder;
use App\Mail\ScenarioSelect;
use App\Services\Rapigo;
use DB;
use Excel;

class Food {

    const OBJECT_ORDER = 'Order';
    const CREDIT_PRICE = 10000;
    const LUNCH_ROUTE = 15;
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

    public function suspendDelvery(User $user, $option) {
        $className = "App\\Services\\EditAlerts";
        $platFormService = new $className();
        $payload = [];
        $date = date("Y-m-d H:i:s");
        $followers = [$user];
        if ($option == "cancel") {
            Delivery::where("user_id", $user->id)->where("status", "pending")->update(['status' => 'suspended']);
            Delivery::where("user_id", $user->id)->where("status", "deposit")->delete();
            $push = $user->push()->where("platform", "food")->first();
            $push->credits = 0;
            $push->save();
            $data = [
                "trigger_id" => $user->id,
                "message" => "",
                "subject" => "",
                "object" => "Lonchis",
                "sign" => true,
                "payload" => $payload,
                "type" => "food_meal_suspended",
                "user_status" => "normal"
            ];
            $platFormService->sendMassMessage($data, $followers, null, true, $date, true);
        } else {
            $delivery = Delivery::where("user_id", $user->id)->where("status", "pending")->orderBy('id', 'desc')->first();
            if ($delivery) {
                $delivery->delete();
                $data = [
                    "trigger_id" => $user->id,
                    "message" => "",
                    "subject" => "",
                    "object" => "Lonchis",
                    "sign" => true,
                    "payload" => $payload,
                    "type" => "food_meal_traded",
                    "user_status" => "normal"
                ];
                $platFormService->sendMassMessage($data, $followers, null, true, $date, true);
            } else {
                $deposit = Delivery::where("user_id", $user->id)->where("status", "deposit")->orderBy('id', 'desc')->first();
                if ($deposit) {
                    $deposit->delete();
                }
                $push = $user->push()->where("platform", "food")->first();
                $push->credits = 0;
                $push->save();
                $data = [
                    "trigger_id" => $user->id,
                    "message" => "",
                    "subject" => "",
                    "object" => "Lonchis",
                    "sign" => true,
                    "payload" => $payload,
                    "type" => "food_meal_suspended",
                    "user_status" => "normal"
                ];
                $platFormService->sendMassMessage($data, $followers, null, true, $date, true);
            }
        }
    }

    public function loadDayConfig() {
        //$date = date("Y-m-d");
//        $deliveries = Delivery::where('delivery', $date)->get();
        //$articles = Article::where('start_date',$date)->get();
        $articles = Article::where('start_date', "2019-10-02")->get();
        $father = [];
        //$keywords = ['fruit', 'soup', 'meat', 'chicken'];
        $keywords = ['fruit', 'soup'];
        foreach ($articles as $article) {

            $attributes = json_decode($article->attributes, true);
            $entradas = [];
            $entradasNombre = [];
            foreach ($attributes['entradas'] as $value) {
                $entradas[$value['codigo']] = 0;
                $entradasNombre[$value['codigo']] = $value['valor'];
            }
            $main = [];
            $mainNombre = [];
            foreach ($attributes['plato'] as $item) {
                $main[$item['codigo']] = 0;
                $mainNombre[$item['codigo']] = $item['valor'];
            }
            foreach ($keywords as $keyword) {
                $father['keywords'][$keyword] = 0;
            }
//            array_push($plate, [0 => $main]);
//            array_push($plate, [0 => $entradas]);
            $father[$article->id]['count'] = 0;
            $father[$article->id]['starter'] = $entradas;
            $father[$article->id]['main'] = $main;
            $father[$article->id]['name'] = $article->name;
            $father[$article->id]['starter_name'] = $entradasNombre;
            $father[$article->id]['main_name'] = $mainNombre;
        }
        return array("father" => $father, "articles" => $articles, "keywords" => $keywords);
    }

    public function countConfigElements($deliveries, $config) {
        $father = $config['father'];
        $keywords = $config['keywords'];
        foreach ($deliveries as $value) {
            $details = json_decode($value->details, true);
            $dish = $details["dish"];
            $father[$dish['type_id']]['count'] ++;
            $father[$dish['type_id']]['starter'][$dish['starter_id']] ++;
            $father[$dish['type_id']]['main'][$dish['main_id']] ++;
            foreach ($keywords as $word) {
                if (strpos($dish['starter_id'], $word) !== false) {
                    $father["keywords"][$word] ++;
                }
                if (strpos($dish['main_id'], $word) !== false) {
                    $father["keywords"][$word] ++;
                }
            }
        }
        return $father;
    }

    public function printTotalsConfig($config) {
        $articles = $config['articles'];
        $father = $config['father'];
        $keywords = $config['keywords'];
        $finalresult = [];
        $finalresult['keywords'] = [];
        $finalresult['totals'] = [];
        $finalresult['dish'] = [];
        foreach ($keywords as $keyword2) {
            if ($father['keywords'][$keyword2] > 0) {
                $keyPrint = "Total " . $keyword2 . ": " . $father['keywords'][$keyword2];
                array_push($finalresult['keywords'], $keyPrint);
            }
        }
        foreach ($articles as $art) {
            $attributes = json_decode($art->attributes, true);
            if ($father[$art->id]['count'] > 0) {
                $title = "Total " . $father[$art->id]['name'] . ": " . $father[$art->id]['count'];
                array_push($finalresult['totals'], $title);
            }
//            foreach ($attributes['entradas'] as $art2) {
//                if ($father[$art->id]['starter'][$art2['codigo']] > 0) {
//                    $entrada = "Total " . $father[$art->id]['name'] . " entrada: " . $father[$art->id]['starter_name'][$art2['codigo']] . ": " . $father[$art->id]['starter'][$art2['codigo']];
//                    array_push($finalresult['dish'], $entrada);
//                }
//            }
            foreach ($attributes['plato'] as $art3) {
                if ($father[$art->id]['main'][$art3['codigo']] > 0) {
                    $fuerte = $father[$art->id]['name'] . " \nprincipal: " . $father[$art->id]['main_name'][$art3['codigo']] . ": " . $father[$art->id]['main'][$art3['codigo']];
                    array_push($finalresult['dish'], $fuerte);
                }
            }
        }

        return $finalresult;
    }

    public function getPurchaseOrder($deliveries) {
        $dayConfig = $this->loadDayConfig();
//        $articles = $dayConfig['articles'];
//        $father = $dayConfig['father'];
//        $keywords = $dayConfig['keywords'];
        //dd($father);
        $dayConfig['father'] = $this->countConfigElements($deliveries, $dayConfig);
        //dd($father);
        $dayConfig['totals'] = $this->printTotalsConfig($dayConfig);
        $user = User::find(2);
        Mail::to($user)->send(new PurchaseOrder($dayConfig));
        return $dayConfig;
    }

    public function getTotalEstimatedShipping($scenario, $type) {
        if ($type == "pending") {
            $routes = Route::where("type", $scenario)->where("status", "pending")->with(['stops.address'])->get();
        } else {
            $routes = Route::where("status", $scenario)->with(['stops.address'])->get();
        }

        $totalCost = 0;
        $totalIncomeShipping = 0;
        $totalLunches = 0;
        $scenarioHash = "";
        if (count($routes) > 0) {
            $scenroute = $routes[0];
            $scenarioHash = $this->generateHash($scenroute->id, $scenroute->created_at, $scenroute->updated_at);
        }
        $className = "App\\Services\\Rapigo";
        $platFormService = new $className();
        foreach ($routes as $route) {
            $stops = $route->stops;
            $queryStops = [];
            $routeCost = self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST;
            foreach ($stops as $stop) {
                $address = $stop->address;
                $querystop = [
                    "address" => $address->address,
                    "description" => $address->name,
                    "type" => "point",
                    "phone" => $address->phone
                ];
                array_push($queryStops, $querystop);
            }
//            $rapigoResponse = $platFormService->getEstimate($queryStops);
//            if ((self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST) > $rapigoResponse['price']) {
//                $routeCost = $rapigoResponse['price'];
//            }
            $route->unit_cost = $routeCost;
            $route->save();
            $route->hash = $this->generateHash($route->id, $route->created_at, $route->updated_at);
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
            "scenario_hash" => $scenarioHash
        ];
        if ($result['ShippingCostEstimate'] < $result['shipping_income']) {
            $result['status'] = "success";
        } else {
            $result['status'] = "failure";
        }
        return array("routes" => $routes, "result" => $result);
    }

    public function checkScenario($results, $hash) {
        if (count($results) > 0) {
            $scenroute = $results[0];
            return $this->checkHash($scenroute->id, $scenroute->created_at, $scenroute->updated_at, $hash);
        }
        return false;
    }

    public function regenerateScenarios() {
        $this->deleteOldData();
        $polygons = CoveragePolygon::where('merchant_id', 1299)->get();
        $this->prepareRoutingSimulation($polygons);
    }

    public function checkUser(User $user) {
        if ($user->id == 1 || $user->id == 2 || $user->id == 3) {
            return true;
        }
        return false;
    }

    public function buildScenarioRouteId($id, $hash) {
        $routes = App\Models\Route::where("id", $id)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
        $checkResult = $this->checkScenario($routes, $hash);
        if ($checkResult) {
            $this->buildScenarioTransit($routes);
        }
    }

    public function buildScenarioPositive($scenario, $hash) {
        $routes = App\Models\Route::where("type", $scenario)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
        $checkResult = $this->checkScenario($routes, $hash);
        if ($checkResult) {
            $routes = App\Models\Route::whereColumn('unit_price', '>', 'unit_cost')->where("status", "pending")->where("type", $scenario)->with(['deliveries.user'])->orderBy('id')->get();
            $this->buildScenarioTransit($routes);
        }
    }

    public function buildCompleteScenario($scenario, $hash) {
        $routes = App\Models\Route::where("type", $scenario)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
        $checkResult = $this->checkScenario($routes, $hash);
        if ($checkResult) {
            $this->buildScenarioTransit($routes);
        }
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

    public function createRoutesFromDeliveries($deliveriesArray, $shipping) {
        $results = [];
        $bindingsString = trim(str_repeat('?,', count($deliveriesArray)), ',');
        $deliveries = DB::select(""
                        . "SELECT DISTINCT(d.id), d.delivery,d.details,d.user_id,d.address_id,status,shipping, lat,`long`, 
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  lat  ) ) ) ) AS Distance 
                   FROM deliveries d join order_addresses a on d.address_id = a.id
                   WHERE  id IN ({$bindingsString})");
        $stops = $this->turnDeliveriesIntoStops($deliveries, true);
        $results = $this->createRoutes($stops, $results, 'preorganize', true, $shipping);
        $this->completeRoutes($results);
        $date = date_create();
        $data = [
            "status" => "scheduled",
            "updated_at" => $date
        ];
        Delivery::whereIn("id", $deliveriesArray)->update($data);
    }

    public function buildScenarioLogistics() {
        $routes = App\Models\Route::where("status", "enqueue")->with(['deliveries.user'])->orderBy('id')->get();
        $totalCost = 0;
        $config = $this->loadDayConfig();
        $totalIncomeShipping = 0;
        $className = "App\\Services\\Rapigo";
        $platFormService = new $className();
        $totalLunches = 0;
        foreach ($routes as $route) {
            $stops = $route->stops()->with(['address', 'deliveries.user'])->get();
            $queryStops = [];
            foreach ($stops as $stop) {
                $deliveries = "";
                foreach ($stop->deliveries as $stopDel) {
                    $stopDel->status = "scheduled";
                    $delUser = $stopDel->user;
                    $descr = "Usuario: " . $delUser->firstName . " " . $delUser->lastName . " ";
                    if (array_key_exists("pickup", $details)) {
                        if ($details['pickup'] == "envase") {
                            $descr = $descr . "Recoger envase, ";
                        }
                    }
                    $descr = $descr . "Entregar id: " . $stopDel->id . ".<br/> " . json_encode($delTotals);
                    $stopDel->region_name = $descr;
                    $stopDel->save();

                    $details = json_decode($stopDel->details, true);
                    $delConfig = $config;
                    $dels = [$stopDel];
                    $delConfig['father'] = $this->countConfigElements($dels, $delConfig);
                    $delTotals = $this->printTotalsConfig($delConfig);
                    $stopDel->totals = $delTotals;

                    $deliveries = $deliveries . $descr;
                }
                $address = $stop->address;
                if ($stop->stop_order == 2) {
                    $stop->region_name = $deliveries;
                }
                $querystop = [
                    "address" => $address->address,
                    "description" => $stop->region_name,
                    "type" => "point",
                    "phone" => $address->phone
                ];
                array_push($queryStops, $querystop);
            }
            $route->status = "enqueue";
            $shippingResponse = $platFormService->getEstimate($queryStops);
            $type = "stops";
            if ((self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST) > $shippingResponse['price']) {
                $totalCost += $shippingResponse['price'];
                $route->unit_cost = $shippingResponse['price'];
            } else {
                $type = "hour";
                $totalCost += self::ROUTE_HOUR_COST * self::ROUTE_HOUR_COST;
                $shippingResponse['price2 '] = self::ROUTE_HOUR_COST * self::ROUTE_HOUR_COST;
                $route->unit_cost = self::ROUTE_HOUR_COST * self::ROUTE_HOUR_COST;
            }
            $route = $platFormService->createRoute($queryStops, $type, $route, $stops);
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
        ];
        if ($result['ShippingCostEstimate'] < $result['shipping_income']) {
            $result['status'] = "success";
        } else {
            $result['status'] = "failure";
        }
        return $result;
    }

    public function generateHash($id, $created_at, $updated_at) {
        return base64_encode(Hash::make($id . $created_at . $updated_at . env('LONCHIS_KEY')));
    }

    public function checkHash($id, $created_at, $updated_at, $hash) {
        $keyDecoded = base64_decode($hash);
        $generated = Hash::make($id . $created_at . $updated_at . env('LONCHIS_KEY'));
        if (Hash::check($generated, $keyDecoded)) {
            return true;
        } else {
            return false;
        }
    }

    public function buildScenario($routes) {
        $config = $this->loadDayConfig();
        foreach ($routes as $route) {
            $routeConfig = $config;
            $deliveries = $route->deliveries;
            $routeConfig['father'] = $this->countConfigElements($deliveries, $routeConfig);
            $routeTotals = $this->printTotalsConfig($routeConfig);
            $stops = $route->stops()->with(['address', 'deliveries.user'])->get();
            $queryStops = [];
            foreach ($stops as $stop) {
                //$stopDeliveries = $stop->deliveries;
                $deliveries = "";
                $stopConfig = $config;
                $stopConfig['father'] = $this->countConfigElements($stop->deliveries, $stopConfig);
                $stopTotals = $this->printTotalsConfig($stopConfig);
                $stop->totals = $stopTotals;
                foreach ($stop->deliveries as $stopDel) {
                    $details = json_decode($stopDel->details, true);
                    $delConfig = $config;
                    $dels = [$stopDel];
                    $delConfig['father'] = $this->countConfigElements($dels, $delConfig);
                    $delTotals = $this->printTotalsConfig($delConfig);
                    $stopDel->totals = $delTotals;
                    $delUser = $stopDel->user;
                    $descr = "Usuario: " . $delUser->firstName . " " . $delUser->lastName . " ";
                    if (array_key_exists("pickup", $details)) {
                        if ($details['pickup'] == "envase") {
                            $descr = $descr . "Recoger envase, ";
                        }
                    }
                    $descr = $descr . "Entregar id: " . $stopDel->id . ".<br/> ";
                    $stopDel->region_name = $descr;
                    $deliveries = $deliveries . $descr;
                }
                $address = $stop->address;

                if ($stop->stop_order == 2) {
                    $stop->region_name = $deliveries;
                }

                $querystop = [
                    "address" => $address->address,
                    "description" => $stop->region_name,
                    "type" => "point",
                    "phone" => $address->phone
                ];
                array_push($queryStops, $querystop);
            }
            $totalDeliveries = [];
            foreach ($route->deliveries as $del) {
                array_push($totalDeliveries, $stopDel);
                $delConfig = $config;
                $dels = [$del];
                $delConfig['father'] = $this->countConfigElements($dels, $delConfig);
                $delTotals = $this->printTotalsConfig($delConfig);
                $del->totals = $delTotals;
            }

            $route->hash = $this->generateHash($route->id, $route->created_at, $route->updated_at);
            $route->totals = $routeTotals;
            $route->stops = $stops;
        }
        return $routes;
        //dd($results);
    }

    public function buildScenarioCredits(Route $route) {
        $stops = $route->stops()->with(['address', 'deliveries.user'])->get();
        foreach ($stops as $stop) {
            //$stopDeliveries = $stop->deliveries;
            $deliveries = "";
            foreach ($stop->deliveries as $stopDel) {
                $details = json_decode($stopDel->details, true);
                $delUser = $stopDel->user;
                $descr = "Usuario: " . $delUser->firstName . " " . $delUser->lastName . " ";
                if (array_key_exists("pickup", $details)) {
                    if ($details['pickup'] == "envase") {
                        $descr = $descr . "Recoger envase, ";
                    }
                }
                $descr = $descr . "Entregar id: " . $stopDel->id . ".<br/> ";
                $stopDel->region_name = $descr;
                $stopDel->hash = $this->generateHash($stopDel->id, $stopDel->created_at, $stopDel->updated_at);
                $stopDel->detauls = $details;
                $deliveries = $deliveries . $descr;
            }
            if ($stop->stop_order == 2) {
                $stop->region_name = $deliveries;
            }
        }
        $route->stops = $stops;
        return [$route];
    }

    public function prepareRouteModel($results, $preOrganize, CoveragePolygon $polygon) {
//        dd($thedata);
        $date = date_create();
        $la = date_format($date, "Y-m-d");
        $thedata = [
            'lat' => $polygon->lat,
            'lat2' => $polygon->lat,
            'long' => $polygon->long,
            'polygon' => $polygon->id,
            'date1' => $la . " 00:00:00",
            'date2' => $la . " 23:59:59",
        ];

        $deliveries = DB::select(""
                        . "SELECT DISTINCT(d.id), d.delivery,d.details,d.user_id,d.address_id,status,shipping, lat,`long`, 
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  lat  ) ) ) ) AS Distance 
                   FROM deliveries d join order_addresses a on d.address_id = a.id
                    WHERE
                        status = 'enqueue'
                        AND d.delivery >= :date1 AND d.delivery <= :date2 
                            AND a.polygon_id = :polygon order by Distance asc"
                        . "", $thedata);
        //echo "Query params: ". json_encode($thedata). PHP_EOL;
        //echo "Query results: " . count($deliveries) . PHP_EOL;
        $stops = $this->turnDeliveriesIntoStops($deliveries, $preOrganize);

        if ($preOrganize) {
            $results = $this->createRoutes($stops, $results, 'preorganize', true, "Rapigo");
        } else {
            $results = $this->createRoutes($stops, $results, 'simple', true, "Rapigo");
        }
        //dd($results);
        return $results;
    }

    public function getLargestAddresses() {
        $thedata = [];
        $thedata = DB::select(""
                        . "SELECT count(d.id) as total,oa.address FROM deliveries d "
                        . "join order_addresses oa on oa.id = d.address_id "
                        . "where d.status='enqueue' group by oa.address limit 30;");
        return $thedata;
    }

    public function prepareQuadrantLimits($lat, $long) {
//        $R = 6371;
//        if ($x < 4) {
//            $radiusInf = 0;
//            $radius = 7;
//        } else {
//            $radiusInf = 3;
//            $radius = 7;
//        }
        $thedata = [
            'lat' => $lat,
            'lat2' => $lat,
            'long' => $long
        ];
        return $thedata;
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

    private function addToNewRouteAndStop($save, $scenario, $stopContainer, $shipping) {
        $route = new Route();
        $routestops = [$stopContainer];
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
        $route->unit = $stopContainer['amount'];
        $route->height = 1;
        $route->unit_price = $stopContainer['shipping'];
        if ($save) {
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

            $firstStop = Stop::create([
                        "address_id" => $address->id,
                        "amount" => 0,
                        "shipping" => 0,
                        "route_id" => $route->id,
                        "stop_order" => 1,
                        "details" => json_encode($details),
                        "region_name" => "Recoger los almuerzos de la ruta " . $route->id,
            ]);

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
                if ($shipping == "Basilikum") {
                    $realDel->status = 'scheduled';
                }
                $route->deliveries()->save($realDel);
            }
        } else {
            $route->stops2 = $routestops;
            $route->deliveries2 = $stopContainer["deliveries"];
        }
        return $route;
    }

    private function addStopToRoute(Route $route, $save, $stopContainer) {
        $route->unit += $stopContainer['amount'];
        $route->unit_price += $stopContainer['shipping'];
        if ($save) {
            $itemSavedStop = $route->stops()->where("address_id", $stopContainer["address_id"])->where("route_id", $route->id)->where("stop_order", 2)->first();
            if ($itemSavedStop) {
                $this->addToExistingStop($itemSavedStop, $route, $stopContainer);
            } else {
                $this->addToNewStop($route, $stopContainer);
            }
            $route->height++;
            $route->save();
        } else {
            $routeStops = $route->stops2;
            $foundInRoutes = false;
            foreach ($routeStops as $rts) {
                if ($rts['address_id'] == $stopContainer["address_id"]) {
                    $foundInRoutes = true;
                    $key = array_search($rts, $routeStops);
                    $routeStops[$key]['amount'] += $stopContainer['amount'];
                }
            }
            if (!$foundInRoutes) {
                array_push($routeStops, $stopContainer);
                $route->height++;
            }
            $route->stops2 = $routeStops;
            $route->deliveries2 = array_merge($route->deliveries2, $stopContainer["deliveries"]);
        }
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
                if ($shipping == "Basilikum") {
                    $available = 30 - $route->unit;
                }

                if (($available > 0 && $available >= $stopContainer['amount']) && !$found) {
                    $found = true;
                    $this->addStopToRoute($route, $save, $stopContainer);
                    break;
                }
            }
            if ($found == false) {
                $route = $this->addToNewRouteAndStop($save, $scenario, $stopContainer, $shipping);
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
        $firstStop = Stop::create([
                    "address_id" => $address->id,
                    "amount" => 0,
                    "route_id" => $route->id,
                    "region_name" => "Entregar los envases recogidos",
                    "stop_order" => 3,
                    "details" => json_encode($details)
        ]);
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

    public function showScenario() {
        
    }

    public function deleteUserScenario(User $user, $type, $date) {
        $routes = Route::where("user_id", $user->id)->where("availability", $date)->get();
        foreach ($routes as $rt) {

            DB::table('delivery_route')
                    ->where('route_id', $rt->id)
                    ->delete();
            $stops = $rt->stops;
            $stopsnum = 0;
            foreach ($stops as $st) {
                DB::table('delivery_stop')
                        ->where('stop_id', $st->id)
                        ->delete();
            }
            $rt->stops()->delete();
            $rt->delete();
        }
    }

    public function getShippingCostArray() {
        return [
            "1" => 3050.00,
            "2" => 2600.00,
            "3" => 2700.00,
            "4" => 2800.00,
            "5" => 2300.00,
            "6" => 2350.00,
            "7" => 2350.00,
            "8" => 2400.00,
            "9" => 19500.00,
            "10" => 1950.00,
            "11" => 1450.00,
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
            $initialAddress = $deliveries[0]->address_id;
            $deliveryCounter = 0;
            $deliveryShipping = 0;
            $packages = [];
            $pickups = [];
            $totalCounter = 0;
            foreach ($deliveries as $delivery) {
                $attributes = json_decode($delivery->details, true);

                if ($preorganize) {
                    $totalCounter++;
                    if ($delivery->address_id == $initialAddress) {
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
                        $stop = [
                            "amount" => $deliveryCounter,
                            "address_id" => $initialAddress,
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
                        $initialAddress = $delivery->address_id;
                    }
                    if ($totalCounter == count($deliveries)) {
                        $stop = [
                            "amount" => $deliveryCounter,
                            "address_id" => $initialAddress,
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
            if ($preorganize) {
                usort($stops, array($this, 'cmp'));
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
        Route::whereIn("status", ["pending", "enqueue"])->delete();

        OrderAddress::where("name", "test")->delete();
    }

    public function generateRandomDeliveries(CoveragePolygon $polygon) {
        $lat = $polygon->lat;
        $long = $polygon->long;
        $radius = 1; // in miles
        $R = 6371;
        $lat_max = ($lat + rad2deg($radius / $R)) * 1000000000;
        $lat_min = ($lat - rad2deg($radius / $R)) * 1000000000;
        $lng_max = ($long + rad2deg(asin($radius / $R) / cos(deg2rad($lat)))) * 1000000000;
        $lng_min = ($long - rad2deg(asin($radius / $R) / cos(deg2rad($lat)))) * 1000000000;
        $date = date("Y-m-d");
        $shipping = $this->getShippingCostArray();
        //$articles = Article::where('start_date',$date)->get();
        $articles = Article::where('start_date', "2019-10-02")->get();
        //$date = date_create();
        $total = rand(0, 4);
        for ($x = 0; $x <= $total; $x++) {
            $latit = rand($lat_min, $lat_max) / 1000000000;
            $longit = rand($lng_min, $lng_max) / 1000000000;
            $address = OrderAddress::create([
                        "user_id" => 5,
                        "name" => "test",
                        "city_id" => 524,
                        "region_id" => 11,
                        "country_id" => 1,
                        "polygon_id" => $polygon->id,
                        "address" => "Carrera 7a # 64-44",
                        "lat" => $latit,
                        "long" => $longit,
                        "phone" => "3105507245"
            ]);
            $amountDeliveries = rand(1, 6);
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
                            "delivery" => $date,
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
        $routes = Route::where("status", "pending")->get();
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
        $routes = Route::where("status", "pending")->delete();
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
            $results2 = $this->prepareRouteModel($results2, false, $polygon, $shipping);
            $results = $this->prepareRouteModel($results, true, $polygon, $shipping);
        }
        $this->completeRoutes($results);
        $this->completeRoutes($results2);
    }

    public function runCompleteSimulation() {
        $polygons = CoveragePolygon::where('merchant_id', 1299)->get();
        foreach ($polygons as $polygon) {
            $this->generateRandomDeliveries($polygon);
        }
        $this->prepareRoutingSimulation($polygons);
        $this->getShippingCosts("pending");
    }

    public function runRecurringTask() {
        $polygons = CoveragePolygon::where('merchant_id', 1299)->get();
        $user = User::find(2);
        foreach ($polygons as $polygon) {
            $this->generateRandomDeliveries($polygon);
        }
        $this->prepareRoutingSimulation($polygons);
        $results = $this->getShippingCosts("pending");
        Mail::to($user)->send(new ScenarioSelect($results['resultsPre'], $results['resultsSimple'], $results['winner']));
        $deliveries = Delivery::where("status", "transit")->get();
        $this->getPurchaseOrder($deliveries);
    }

    public function runInstructions() {
        $user = User::find(2);
        $routes = Route::where("status", "built")->with(['deliveries.user'])->get();
        $results = $this->buildScenario($routes);
        Mail::to($user)->send(new RouteOrganize($results));
        Mail::to($user)->send(new RouteDeliver($results));
    }

    public function testDataCompleteSimulation() {
        $polygons = CoveragePolygon::where('merchant_id', 1299)->get();
        foreach ($polygons as $polygon) {
            $this->generateRandomDeliveries($polygon);
        }
    }

    public function getRouteInfo($delivery_id) {
        $theData = ["delivery" => $delivery_id];
        $routes = DB::select(" select route_id from delivery_route where delivery_id = :delivery", $theData);
        if (count($routes) > 0) {
            $route = Route::where("id", $routes[0]->route_id)->with(['stops.address'])->first();
            return $route;
        }
        return null;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getShippingCosts($type) {
        if ($type == "pending") {
            $resultsPre = $this->getTotalEstimatedShipping("preorganize", $type);
            $resultsPre = $resultsPre['result'];
            $resultsSimple = $this->getTotalEstimatedShipping("simple", $type);
            $resultsSimple = $resultsSimple['result'];
        } else {
            $resultsPre = $this->getTotalEstimatedShipping("enqueue", $type);
            $resultsPre = $resultsPre['result'];
            $resultsSimple = $resultsPre;
        }

        $winningScenario = "";
        if ($resultsPre["day_profit"] > $resultsSimple["day_profit"]) {
            $winningScenario = "Preorganize";
        } else {
            $winningScenario = "Simple";
        }

        return array("winner" => $winningScenario, "resultsPre" => $resultsPre, "resultsSimple" => $resultsSimple);
    }

    public function reprogramDeliveries() {
        $date = date_create();
//        $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
//        dd($dayofweek);
//        if ($dayofweek < 5 && $dayofweek > 0) {
//            date_add($date, date_interval_create_from_date_string("1 days"));
//        } else if ($dayofweek == 5) {
//            date_add($date, date_interval_create_from_date_string("3 days"));
//        } else {
//            return null;
//        }
        $la = date_format($date, "Y-m-d");
//        $date = date_create($la . " 23:59:59");
//        dd($date);
        $deliveries = Delivery::whereIn('status', ['pending', 'deposit'])->where('delivery', '<', $la . " 23:59:59")->orderBy('delivery', 'desc')->get();
        foreach ($deliveries as $item) {
            $delivery = Delivery::where('id', "<>", $item->id)->where('user_id', $item->user_id)->where('delivery', '>', $item->delivery)->orderBy('delivery', 'desc')->first();
            if ($delivery) {
                $date = date_create($delivery->delivery);
            } else {
                $date = date_create();
            }

            $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
            if ($dayofweek < 5) {
                date_add($date, date_interval_create_from_date_string("1 days"));
            } else if ($dayofweek == 5) {
                date_add($date, date_interval_create_from_date_string("3 days"));
            } else {
                return null;
            }
            if ($delivery) {
                if ($delivery->status == "deposit") {
                    $item->delivery = $delivery->delivery;
                    $delivery->delivery = $date;
                    $delivery->save();
                } else {
                    $item->delivery = $date;
                }
            } else {
                $item->delivery = $date;
            }
            $item->save();
        }
    }

}
