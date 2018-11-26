<?php

namespace App\Services;

use App\Models\User;
use App\Models\Article;
use App\Models\Delivery;
use App\Models\OrderAddress;
use App\Models\Route;
use App\Models\Stop;
use App\Services\Rapigo;
use DB;

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

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $rapigo;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(Rapigo $rapigo) {
        $this->rapigo = $rapigo;
    }

    public function suspendDelvery($user_id) {
        Delivery::where("user_id", $user_id)->where("status", "<>", "deposit")->update(['status' => 'suspended']);
        Delivery::where("user_id", $user_id)->where("status", "deposit")->delete();
    }

    public function loadDayConfig() {
        //$date = date("Y-m-d");
//        $deliveries = Delivery::where('delivery', $date)->get();
        //$articles = Article::where('start_date',$date)->get();
        $articles = Article::where('start_date', "2018-09-01")->get();
        $father = [];
        $keywords = ['fruit', 'soup', 'meat', 'chicken'];
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
            $father[$value->type_id]['count'] ++;
            $father[$value->type_id]['starter'][$value->starter_id] ++;
            $father[$value->type_id]['main'][$value->main_id] ++;
            foreach ($keywords as $word) {
                if (strpos($value->starter_id, $word) !== false) {
                    $father["keywords"][$word] ++;
                }
                if (strpos($value->main_id, $word) !== false) {
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
                    $fuerte = "Total " . $father[$art->id]['name'] . " principal: " . $father[$art->id]['main_name'][$art3['codigo']] . ": " . $father[$art->id]['main'][$art3['codigo']];
                    array_push($finalresult['dish'], $fuerte);
                }
            }
        }

        return $finalresult;
    }

    public function getPurchaseOrder($deliveries) {
        $dayConfig = $this->loadDayConfig();
        $articles = $dayConfig['articles'];
        $father = $dayConfig['father'];
        $keywords = $dayConfig['keywords'];
        //dd($father);
        $dayConfig['father'] = $this->countConfigElements($deliveries, $dayConfig);
        //dd($father);
        $dayConfig['totals'] = $this->printTotalsConfig($dayConfig);
        return $dayConfig;
    }

    public function getTotalEstimatedShipping($results) {
//        dd($results);
        $totalCost = 0;
        $totalIncomeShipping = 0;
        $totalLunches = 0;
        foreach ($results as $value) {
            $stops = $value->stops;
            $queryStops = [];
            $routeCost = 0;
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
//            $rapigoResponse = $this->rapigo->getEstimate($queryStops);
//            if ((self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST) > $rapigoResponse['price']) {
//                $routeCost = $rapigoResponse['price'];
//            } else {
//                $routeCost = self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST;
//            }
            $routeCost = self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST;
            if($routeCost> $value->unit_price){
                $value->availability = 0;
            } else {
                $value->availability = 1;
            }
            $value->width = $routeCost;
            $value->save();
            $totalCost += $routeCost;
            $totalIncomeShipping += $value->unit_price;
            $totalLunches += $value->unit;
        }
        $totalRoutes = count($results);
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
        echo 'Lunches: ' . $result['lunches'] . PHP_EOL;
        echo 'Routes: ' . $result['routes'] . PHP_EOL;
        echo 'Lunches per route: ' . $result['lunch_route'] . PHP_EOL;
        echo 'Cost Shipping: ' . $result['ShippingCostEstimate'] . PHP_EOL;
        echo 'Income Shipping: ' . $result['shipping_income'] . PHP_EOL;
        echo 'Total Income: ' . $result['total_income'] . PHP_EOL;
        echo 'Total Profit: ' . $result['day_profit'] . PHP_EOL;
        if ($result['ShippingCostEstimate'] < $result['shipping_income']) {
            echo 'Scenario successful!!' . PHP_EOL;
        } else {
            echo 'Scenario FAILED!!' . PHP_EOL;
        }
        return $results;
        //dd($results);
    }

    public function buildScenario($results) {
//        dd($results);

        $totalCost = 0;
        $totalIncomeShipping = 0;
        $totalCost2 = 0;
        $totalLunches = 0;
        $config = $this->loadDayConfig();
        foreach ($results as $value) {
            $routeConfig = $config;
            $deliveries = $value->deliveries;
            $routeConfig['father'] = $this->countConfigElements($deliveries, $routeConfig);
            $routeTotals = $this->printTotalsConfig($routeConfig);
            $stops = $value->stops()->with(['address', 'deliveries.user'])->get();
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
            foreach ($value->deliveries as $del) {
                $delConfig = $config;
                $dels = [$del];
                $delConfig['father'] = $this->countConfigElements($dels, $delConfig);
                $delTotals = $this->printTotalsConfig($delConfig);
                $del->totals = $delTotals;
            }
            $value->status = "transit";

//            $rapigoResponse = $this->rapigo->getEstimate($queryStops);
//            $totalCost2 += $rapigoResponse['price'];
//            if ((self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST) > $rapigoResponse['price']) {
//                $totalCost += $rapigoResponse['price'];
//            } else {
//                $totalCost += self::ROUTE_HOUR_COST * self::ROUTE_HOUR_COST;
//                $rapigoResponse['price2 '] = self::ROUTE_HOUR_COST * self::ROUTE_HOUR_COST;
//            }
//            $value->coverage = json_encode($rapigoResponse);
//            $value->save();
            $value->totals = $routeTotals;
            $value->stops = $stops;
            $totalCost += self::ROUTE_HOUR_COST * 3;
            $totalIncomeShipping += $value->unit_price;
            $totalLunches += $value->unit;
        }
        $totalRoutes = count($results);
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
        echo 'Lunches: ' . $result['lunches'] . PHP_EOL;
        echo 'Routes: ' . $result['routes'] . PHP_EOL;
        echo 'Lunches per route: ' . $result['lunch_route'] . PHP_EOL;
        echo 'Cost Shipping: ' . $result['ShippingCostEstimate'] . PHP_EOL;
        echo 'Income Shipping: ' . $result['shipping_income'] . PHP_EOL;
        echo 'Total Income: ' . $result['total_income'] . PHP_EOL;
        echo 'Total Profit: ' . $result['day_profit'] . PHP_EOL;
        if ($result['ShippingCostEstimate'] < $result['shipping_income']) {
            echo 'Scenario successful!!' . PHP_EOL;
        } else {
            echo 'Scenario FAILED!!' . PHP_EOL;
        }
        //echo json_encode($results) . PHP_EOL;
        return $results;
        //dd($results);
    }

    public function prepareRouteModel(array $thedata, $results, $preOrganize, $x) {
//        dd($thedata);
        $deliveries = DB::select(""
                        . "SELECT DISTINCT(d.id), d.delivery,d.details,d.user_id,d.address_id,status,shipping, lat,`long`, 
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  lat  ) ) ) ) AS Distance 
                   FROM deliveries d join order_addresses a on d.address_id = a.id
                    WHERE
                        status = 'enqueue'
                            AND d.user_id = 1
                            AND d.delivery >= :theDate
                            AND d.delivery <= :theDate2
                            AND lat >= :latinf AND lat < :latsup
                            AND `long` >= :longinf AND `long` < :longsup
                    HAVING Distance <= :radius AND Distance > :radiusInf order by Distance asc"
                        . "", $thedata);
        //echo "Query params: ". json_encode($thedata). PHP_EOL;
        //echo "Query results: " . count($deliveries) . PHP_EOL;
        $stops = $this->turnDeliveriesIntoStops($deliveries, $preOrganize);

        //dd($stops);
        if ($preOrganize) {
            $results = $this->createRoutes($stops, $results, $x, 'preorganize', true);
        } else {
            $results = $this->createRoutes($stops, $results, $x, 'simple', true);
        }
        //dd($results);
        return $results;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function createRoutes($stops, $routes, $x, $scenario, $save) {
        foreach ($stops as $value) {
            $found = false;
            foreach ($routes as $item) {
                $available = self::LUNCH_ROUTE - $item->unit;
                if ($available > 0 && $available >= $value['amount']) {
                    $found = true;
                    $item->unit += $value['amount'];
                    $item->unit_price += $value['shipping'];
                    if ($save) {
                        $itemSavedStop = $item->stops()->where("address_id", $value["address_id"])->where("route_id", $item->id)->where("stop_order", 2)->first();
                        if ($itemSavedStop) {
                            $foundInRoutes = true;
                            $stopDetails = json_decode($itemSavedStop->details, true);
                            if (array_key_exists("pickups", $stopDetails)) {
                                $stopDetails['pickups'] = array_merge($stopDetails['pickups'], $value["pickups"]);
                                $itemSavedStop->details = json_encode($stopDetails);
                            }
                            $itemSavedStop->amount += $value['amount'];
                            foreach ($value["deliveries"] as $del) {
                                $realDel2 = Delivery::find($del->id);
                                $itemSavedStop->deliveries()->save($realDel2);
                                $item->deliveries()->save($realDel2);
                            }
                            $itemSavedStop->save();
                        } else {
                            $details = ["pickups" => []];
                            $details['pickups'] = $value["pickups"];
                            $stopContainer = Stop::create([
                                        "address_id" => $value["address_id"],
                                        "amount" => $value["amount"],
                                        "route_id" => $item->id,
                                        "stop_order" => 2,
                                        "details" => json_encode($details)
                            ]);
                            foreach ($value["deliveries"] as $del) {
                                $realDel = Delivery::find($del->id);
                                $stopContainer->deliveries()->save($realDel);
                                $item->deliveries()->save($realDel);
                            }
                            $item->height++;
                            
                        }
                        $item->save();
                    } else {
                        $routeStops = $item->stops2;
                        $foundInRoutes = false;
                        foreach ($routeStops as $rts) {
                            if ($rts['address_id'] == $value["address_id"]) {
                                $foundInRoutes = true;
                                $key = array_search($rts, $routeStops);
                                $routeStops[$key]['amount'] += $value['amount'];
                            }
                        }
                        if (!$foundInRoutes) {
                            array_push($routeStops, $value);
                            $item->height++;
                        }
                        $item->stops2 = $routeStops;
                        $item->deliveries2 = array_merge($item->deliveries2, $value["deliveries"]);
                    }
                    break;
                }
            }
            if ($found == false) {
                if ($x == 1) {
                    //dd($value["deliveries"]);
                }
                $route = new Route();
                $routestops = [$value];
                $route->status = 'pending';
                $route->description = $scenario;
                $route->unit = $value['amount'];
                $route->height = 1;
                $route->unit_price = $value['shipping'];

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
                                "route_id" => $route->id,
                                "stop_order" => 1,
                                "details" => json_encode($details),
                                "region_name" => "Recoger los almuerzos de la ruta " . $route->id,
                    ]);

                    $details['pickups'] = $value["pickups"];
                    $stopContainer = Stop::create([
                                "address_id" => $value["address_id"],
                                "amount" => $value["amount"],
                                "route_id" => $route->id,
                                "stop_order" => 2,
                                "details" => json_encode($details)
                    ]);
                    foreach ($value["deliveries"] as $del) {
                        $realDel = Delivery::find($del->id);
                        $stopContainer->deliveries()->save($realDel);
                        $route->deliveries()->save($realDel);
                    }
                } else {
                    $route->stops2 = $routestops;
                    $route->deliveries2 = $value["deliveries"];
                }
                array_push($routes, $route);
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
            $totalIncomeShipping += $value->unit_price;
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
            foreach ($deliveries as $value) {
                $attributes = json_decode($value->details, true);

                if ($preorganize) {
                    $totalCounter++;
                    if ($value->address_id == $initialAddress) {
                        $deliveryCounter++;
                        $deliveryShipping += $value->shipping;
                        array_push($packages, $value);
                        if (array_key_exists("pickup", $attributes)) {
                            if ($attributes['pickup'] == 'envase') {
                                $delUser = User::find($value->user_id);
                                $desc = "Envase de " . $delUser->firstName . " " . $delUser->lastName;
                                array_push($pickups, $desc);
                            }
                        }
                    } else {
                        $stop = [
                            "amount" => $deliveryCounter,
                            "address_id" => $initialAddress,
                            "latitude" => $value->lat,
                            "longitude" => $value->long,
                            "shipping" => $deliveryShipping,
                            "deliveries" => $packages,
                            "pickups" => $pickups
                        ];
                        array_push($stops, $stop);
                        $packages = [];
                        array_push($packages, $value);
                        $pickups = [];
                        if (array_key_exists("pickup", $attributes)) {
                            if ($attributes['pickup'] == 'envase') {
                                $delUser = User::find($value->user_id);
                                $desc = "Envase de " . $delUser->firstName . " " . $delUser->lastName;
                                array_push($pickups, $desc);
                            }
                        }
                        $deliveryCounter = 1;
                        $deliveryShipping = $value->shipping;
                        $initialAddress = $value->address_id;
                    }
                    if ($totalCounter == count($deliveries)) {
                        $stop = [
                            "amount" => $deliveryCounter,
                            "address_id" => $initialAddress,
                            "latitude" => $value->lat,
                            "longitude" => $value->long,
                            "shipping" => $deliveryShipping,
                            "deliveries" => $packages,
                            "pickups" => $pickups
                        ];
                        array_push($stops, $stop);
                    }
                } else {
                    if (array_key_exists("pickup", $attributes)) {
                        if ($attributes['pickup'] == 'envase') {
                            $delUser = User::find($value->user_id);
                            $desc = "Envase de " . $delUser->firstName . " " . $delUser->lastName;
                            array_push($pickups, $desc);
                        }
                    }
                    $packages = [$value];
                    $stop = [
                        "amount" => 1,
                        "address_id" => $value->address_id,
                        "latitude" => $value->lat,
                        "longitude" => $value->long,
                        "shipping" => $value->shipping,
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

    public function generateRandomDeliveries($lat, $long) {
        $radius = 5; // in miles
        $R = 6371;
        $lat_max = ($lat + rad2deg($radius / $R)) * 1000000000;
        $lat_min = ($lat - rad2deg($radius / $R)) * 1000000000;
        $lng_max = ($long + rad2deg(asin($radius / $R) / cos(deg2rad($lat)))) * 1000000000;
        $lng_min = ($long - rad2deg(asin($radius / $R) / cos(deg2rad($lat)))) * 1000000000;
        $date = date("Y-m-d");
        $shipping = $this->getShippingCostArray();
        //$articles = Article::where('start_date',$date)->get();
        $articles = Article::where('start_date', "2018-09-01")->get();
        //$date = date_create();
        $total = rand(1, 8);
        for ($x = 0; $x <= $total; $x++) {
            $latit = rand($lat_min, $lat_max) / 1000000000;
            $longit = rand($lng_min, $lng_max) / 1000000000;
            $address = OrderAddress::create([
                        "user_id" => 1,
                        "name" => "test",
                        "city_id" => 524,
                        "region_id" => 11,
                        "country_id" => 1,
                        "address" => "Carrera 7a # 64-44",
                        "lat" => $latit,
                        "long" => $longit,
                        "phone" => "3105507245"
            ]);
            $amountDeliveries = rand(1, 10);
            for ($j = 0; $j <= $amountDeliveries; $j++) {
                $type_num = rand(0, 2);
                $art = $articles[$type_num];
                $attrs = json_decode($art->attributes, true);
                $starter = rand(0, 1);
                $starterPlate = $attrs['entradas'][$starter];
                $main = rand(0, 2);
                $mainPlate = $attrs['plato'][$main];
                $pickingUp = rand(0, 1);
                $pickingUp = 0;
                $details = [];
                if ($pickingUp == 1) {
                    $details['pickup'] = "envase";
                }
                $details['starter_id'] = $starterPlate['codigo'];
                $details['main_id'] = $mainPlate['codigo'];
                $delivery = Delivery::create([
                            "user_id" => 1,
                            "delivery" => $date,
                            "type_id" => $art->id,
                            "shipping" => $shipping[$amountDeliveries],
                            "status" => "enqueue",
                            "starter_id" => $starterPlate['codigo'],
                            "main_id" => $mainPlate['codigo'],
                            "address_id" => $address->id,
                            "details" => json_encode($details)
                ]);
            }
        }
        echo 'lng (min/max): ' . $lng_min . '/' . $lng_max . PHP_EOL;
        echo 'lat (min/max): ' . $lat_min . '/' . $lat_max . PHP_EOL;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function prepareRoutingSimulation($lat, $long) {
        $R = 6371;
        $radius = 6;
        $maxLat = $lat + rad2deg($radius / $R);
        $minLat = $lat - rad2deg($radius / $R);
        $maxLon = $long + rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
        $minLon = $long - rad2deg(asin($radius / $R) / cos(deg2rad($lat)));

        $date = date("Y-m-d");
        $date2 = date('Y-m-d', strtotime($date . ' + 1 days'));
        $results = [];
        $results2 = [];
        //$results['unscheduled'] = [];

        for ($x = 0; $x < 4; $x++) {
            if ($x < 4) {
                $radiusInf = 0;
                $radius = 7;
            } else {
                $radiusInf = 3;
                $radius = 7;
            }
            $maxLat = $lat + rad2deg($radius / $R);
            $minLat = $lat - rad2deg($radius / $R);
            $maxLon = $long + rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
            $minLon = $long - rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
            if ($x == 0 || $x == 4) {
                $thedata = [
                    'lat' => $lat,
                    'lat2' => $lat,
                    'long' => $long,
                    'latinf' => $lat,
                    'latsup' => $maxLat,
                    'longinf' => $long,
                    'longsup' => $maxLon,
                    'theDate' => $date,
                    'theDate2' => $date2,
                    'radiusInf' => $radiusInf,
                    'radius' => $radius
                ];
            } else if ($x == 1 || $x == 5) {
                $thedata = [
                    'lat' => $lat,
                    'lat2' => $lat,
                    'long' => $long,
                    'latinf' => $minLat,
                    'latsup' => $lat,
                    'longinf' => $long,
                    'longsup' => $maxLon,
                    'theDate' => $date,
                    'theDate2' => $date2,
                    'radiusInf' => $radiusInf,
                    'radius' => $radius
                ];
            } else if ($x == 2 || $x == 6) {
                $thedata = [
                    'lat' => $lat,
                    'lat2' => $lat,
                    'long' => $long,
                    'latinf' => $minLat,
                    'latsup' => $lat,
                    'longinf' => $minLon,
                    'theDate' => $date,
                    'theDate2' => $date2,
                    'longsup' => $long,
                    'radiusInf' => $radiusInf,
                    'radius' => $radius
                ];
            } else if ($x == 3 || $x == 7) { 
                $thedata = [
                    'lat' => $lat,
                    'lat2' => $lat,
                    'long' => $long,
                    'latinf' => $lat,
                    'theDate' => $date,
                    'theDate2' => $date2,
                    'latsup' => $maxLat,
                    'longinf' => $minLon,
                    'longsup' => $long,
                    'radiusInf' => $radiusInf,
                    'radius' => $radius
                ];
            }
            $results2 = $this->prepareRouteModel($thedata, $results2, false, $x);
            $results = $this->prepareRouteModel($thedata, $results, true, $x);
        }
        $this->completeRoutes($results);
        $this->completeRoutes($results2);
        $routes =Route::where("description","preorganize")->get();
        $routes2 =Route::where("description","simple")->get();
        $this->getTotalEstimatedShipping($routes);
        $this->getTotalEstimatedShipping($routes2);
    }

}
