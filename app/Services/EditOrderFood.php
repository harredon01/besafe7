<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Item;
use App\Models\Article;
use App\Models\Condition;
use App\Models\Delivery;
use App\Models\OrderAddress;
use App\Models\Route;
use App\Models\Stop;
use App\Models\OrderCondition;
use Darryldecode\Cart\CartCondition;
use Cart;
use DB;

class EditOrderFood {

    const OBJECT_ORDER = 'Order';
    const CREDIT_PRICE = 10000;
    const LUNCH_ROUTE = 15;
    const LUNCH_PROFIT = 1100;
    const ROUTE_HOUR_COST = 11000;
    const ROUTE_HOURS_EST = 3;
    const UNIT_LOYALTY_DISCOUNT = 11000;
    const OBJECT_ORDER_REQUEST = 'OrderRequest';
    const ORDER_PAYMENT = 'order_payment';
    const PLATFORM_NAME = 'food';
    const ORDER_PAYMENT_REQUEST = 'order_payment_request';

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function checkOrder(User $user, Order $order, array $data) {
        $items = $order->items();
        $push = $user->push()->where("platform", self::PLATFORM_NAME)->first();
        $requiredCredits = 0;
        $requiredBuyers = 1;
        $splitTotal = $order->total;
        $totalDeposit = 0;
        foreach ($items as $value) {
            $attributes = json_decode($value->attributes, true);
            if (array_key_exists("requires_credit", $attributes)) {
                if ($attributes['requires_credit']) {
                    $requiredCredits += $attributes['credits'];
                    $totalDeposit += (self::CREDIT_PRICE * $attributes['credits']);
                }
            }
            if (array_key_exists("multiple_buyers", $attributes)) {
                if ($attributes['multiple_buyers']) {
                    $requiredBuyers += $attributes['buyers'];
                }
            }
            if (array_key_exists("is_credit", $attributes)) {
                if ($attributes['is_credit']) {
                    $requiredCredits -= $value->quantity;
                    $splitTotal -= ($value->price * $value->quantity);
                }
            }
        }
        $address = $order->orderAddresses()->where('type', "shipping")->get();
        if (!$address) {
            return array("status" => "error", "message" => "Order does not have Shipping Address");
        }
        if ($requiredCredits > 0) {
            $creditHolders = Push::whereIn('user_id', $data['payers'])->where("credits", ">", 0)->where("platform", self::PLATFORM_NAME)->count();
            if ($push->credits > 0) {
                $creditHolders++;
            }
            if ($creditHolders < $requiredCredits) {
                return array("status" => "error", "message" => "Order does not have enough payers");
            }
        }
        return array("status" => "success",
            "message" => "Order Passed validation",
            "order" => $order,
            "split" => $splitTotal,
            "deposit" => $totalDeposit,
            "push" => $push
        );
    }

    public function addDiscounts(User $user, Order $order) {
        Cart::session($user->id)->removeConditionsByType(self::PLATFORM_NAME);
        $order->orderConditions()->where("type", self::PLATFORM_NAME)->delete();
        $items = $order->items;
        $conditions = [];
        foreach ($items as $value) {
            $attributes = json_decode($value->attributes, true);

            if ($value->quantity > 10) {

                $control = $value->quantity / 10;
                $control2 = floor($value->quantity / 10);
                $discount = 0;
                if (array_key_exists("multiple_buyers", $attributes)) {
                    if ($attributes['multiple_buyers']) {
                        $buyers = $attributes['buyers'];
                    }
                }
                if ($control == $control2) {
                    $discount = (($control2 - 1) * $buyers * self::UNIT_LOYALTY_DISCOUNT);
                } else {
                    $discount = ($control2 * $buyers * self::UNIT_LOYALTY_DISCOUNT);
                }
                $condition = new OrderCondition(array(
                    'name' => "Descuento por compromiso orden: " . $order->id,
                    'target' => "subtotal",
                    'type' => self::PLATFORM_NAME,
                    'value' => "-" . $discount,
                    'total' => $discount,
                ));
                array_push($conditions, $condition);
                $order->orderConditions()->save($condition);
                $condition2 = new CartCondition(array(
                    'name' => $condition->name,
                    'type' => $condition->type,
                    'target' => $condition->target, // this condition will be applied to cart's subtotal when getSubTotal() is called.
                    'value' => $condition->value,
                    'order' => 1
                ));
                Cart::session($user->id)->condition($condition2);
            }
        }
        return array("status" => "success", "message" => "Conditions added", "conditions" => $conditions);
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    public function prepareOrder(User $user, Order $order, array $info, $cart) {
        $checkResult = $this->checkOrder($user, $order, $info);
        $result = null;
        if ($checkResult['status'] == "success") {
            $order = $checkResult['order'];
            $totalBuyers = 1;
            if (array_key_exists("split_order", $info)) {
                if ($info['split_order']) {
                    if (array_key_exists("payers", $info)) {
                        $this->splitOrder($user, $order, $info['payers'], $checkResult['deposit'], $checkResult['split']);
                        $totalBuyers = count($info['payers']) + 1;
                    }
                }
            } else {
                Payment::where("order_id", $order->id)->where("user_id", "<>", $user->id)->where("status", "pending")->delete();
            }
            if (array_key_exists("payers", $info)) {
                array_push($info['payers'], $user->id);
                $records = [
                    "buyers" => $info['payers']
                ];
                $order->attributes = json_encode($records);
            }
            $buyerSubtotal = $checkResult['split'] / $totalBuyers;
            $buyerTax = $order->tax / $totalBuyers;
            $transactionCost = 0;
            if ($checkResult['deposit'] > 0) {
                $push = $checkResult['push'];
                if ($push) {
                    if ($push->credits == 0) {
                        $buyerSubtotal += self::CREDIT_PRICE;
                    }
                } else {
                    $buyerSubtotal += self::CREDIT_PRICE;
                }
            }
            if ($totalBuyers > 1) {
                $transactionCost = $this->getTransactionTotal($buyerSubtotal);
                $order->total = $order->total + ($transactionCost * $totalBuyers);
                $order->tax = $order->tax + (0);
                //$order->status = "payment_created";
            }
            $payment = Payment::where("order_id", $order->id)->where("user_id", $user->id)->where("status", "pending")->first();
            if ($payment) {
                
            } else {
                $payment = new Payment;
            }
            $address = $order->orderAddresses()->where("type", "shipping")->first();
            $payment->user_id = $user->id;
            $payment->address_id = $address->id;
            $payment->order_id = $order->id;
            $payment->status = "pending";
            $payment->total = $buyerSubtotal + $transactionCost;
            $payment->tax = $buyerTax;
            $payment->save();
            $result = array("status" => "success", "message" => "Order submitted, payment created", "payment" => $payment, "order" => $order);
            $order->save();
            return $result;
        }
        return $checkResult;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    private function splitOrder(User $user, Order $order, $buyers, $depositTotal, $splitTotal) {
        if ($order->user_id == $user->id) {
            $totalBuyers = count($buyers) + 1;
            $buyerSubtotal = $splitTotal / $totalBuyers;
            $buyerTax = $order->tax / $totalBuyers;
            $transactionCost = $this->getTransactionTotal($buyerSubtotal);

            $followers = array();
            foreach ($buyers as $buyerItem) {
                $buyer = User::find($buyerItem);
                if ($buyer) {
                    $buyerTotal = $buyerSubtotal;
                    if ($depositTotal > 0) {
                        $push = $buyer->push()->where("platform", self::PLATFORM_NAME)->first();
                        if ($push) {
                            if ($push->credits == 0) {
                                $buyerTotal += self::CREDIT_PRICE;
                            }
                        } else {
                            $buyerTotal += self::CREDIT_PRICE;
                        }
                    }
                    array_push($followers, $buyer);
                    $payment = Payment::where("order_id", $order->id)->where("user_id", $buyer->id)->where("status", "pending")->first();
                    if ($payment) {
                        
                    } else {
                        $payment = new Payment;
                    }
                    $payment->user_id = $buyer->id;
                    $payment->address_id = $order->address_id;
                    $payment->order_id = $order->id;
                    $payment->status = "pending";
                    $payment->total = $buyerTotal + $transactionCost;
                    $payment->tax = $buyerTax;
                    $payment->save();
                }
            }
            $payload = [
                "order_id" => $order->id,
                "first_name" => $user->firstName,
                "last_name" => $user->lastName,
            ];
            $data = [
                "trigger_id" => $user->id,
                "message" => "",
                "subject" => "",
                "object" => self::OBJECT_ORDER,
                "sign" => true,
                "payload" => $payload,
                "type" => self::ORDER_PAYMENT,
                "user_status" => $user->getUserNotifStatus()
            ];
            $date = date("Y-m-d H:i:s");
            $className = "App\\Services\\EditAlerts";
            $platFormService = new $className(); //// <--- this thing will be autoloaded
            return $platFormService->sendMassMessage($data, $followers, $user, true, $date, self::PLATFORM_NAME);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    private function setCondition(User $user, Order $order) {
        Cart::session($user->id)->removeConditionsByType("misc");
        $order->conditions()->wherePivot('type', "misc")->detach();
        $theCondition = Condition::find(11);
        if ($theCondition) {
            $insertCondition = array(
                'name' => $theCondition->name,
                'type' => "misc",
                'target' => $theCondition->target,
                'value' => $theCondition->value,
                'order' => $theCondition->order
            );
            $condition = new CartCondition($insertCondition);
            $insertCondition['order_id'] = $order->id;
            $insertCondition['condition_id'] = $theCondition->id;
            Cart::session($user->id)->condition($condition);
            DB::table('condition_order')->insert($insertCondition);
        }
        return array("status" => "error", "message" => "Address does not exist");
    }

    public function approvePayment(Payment $payment) {
        $order = Order::find($payment->order_id);
        if ($order) {
            $payments = $order->payments()->where("status", "<>", "Paid")->count();
            if ($payments > 0) {
                $order->status = "Pending-" . $payments;
                $order->save();
                return array("status" => "success", "message" => "Payment approved, still payments pending");
            } else {
                return $this->approveOrder($order);
            }
        }
    }

    public function denyPayment(Payment $payment) {
        $payment->status = "denied";
        $payment->save();
    }

    public function pendingPayment(Payment $payment) {
        $payment->status = "Open";
        $payment->save();
    }

    public function createMealPlan(Order $order, Item $item, $address_id) {
        $data = json_decode($order->attributes, true);
        $buyers = $data['buyers'];
        for ($x = 0; $x < count($buyers); $x++) {
            $this->createDeliveries($buyers[$x], $item, $address_id);
        }
    }

    protected function approveOrder(Order $order) {
        $data = array();
        $items = $order->items()->get();
        $address = $order->orderAddresses()->where("type", "shipping")->first();
        $status = "approved: items: " . count($items) . " | " . json_encode($items);
        foreach ($items as $item) {
            $status = $status . " :" . $item->attributes;
            $data = json_decode($item->attributes, true);
            $item->attributes = $data;

            if (array_key_exists("type", $data)) {
                $status = $status . " type";
                if ($data['type'] == "subscription") {
                    $object = $data['object'];
                    $id = $data['id'];
                    $payer = $order->user_id;
                    $interval = $data['interval'];
                    $interval_type = $data['interval_type'];
                    $date = date("Y-m-d");
                    //increment 2 days
                    $mod_date = strtotime($date . "+ " . $interval . " " . $interval_type);
                    $newdate = date("Y-m-d", $mod_date);
                    // add date to object
                }
                if ($data['type'] == "meal-plan") {
                    $status = $status . " meal-plan";
                    $this->createMealPlan($order, $item, $address->id);
                }
            }
            if (array_key_exists("model", $data)) {
                $class = "App\\Models\\" . $data["model"];
                $model = $class::find("id", $data['id']);
            } else {
                
            }
        }
        $order->status = $status;
        $order->save();
        return array("status" => "success", "message" => "Order approved, subtasks completed", "order" => $order);
    }

    public function createDeliveries($user_id, Item $item, $address_id) {
        $date = date_create();
        for ($x = 0; $x < $item->quantity; $x++) {
            date_add($date, date_interval_create_from_date_string("1 days"));
            $dayofweek = date('w', strtotime($date));
            if ($dayofweek == 5) {
                date_add($date, date_interval_create_from_date_string("2 days"));
            } else if ($dayofweek == 6) {
                date_add($date, date_interval_create_from_date_string("1 days"));
            }
            $delivery = new Delivery();
            $delivery->user_id = $user_id;
            $delivery->delivery = $date;
            $delivery->address_id = $address_id;
            $delivery->save();
        }
    }

    public function getTransactionTotal($total) {
        return ($total * 0.0349 + 900);
    }

    public function getPurchaseOrder() {
        $date = date("Y-m-d");
        $deliveries = Delivery::where('delivery', $date)->get();

        //$articles = Article::where('start_date',$date)->get();
        $articles = Article::where('start_date', "2018-09-01")->get();
        $father = [];
        $keywords = ['fruit', 'meat', 'soup', 'chicken'];
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
        //dd($father);

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
        //dd($father);
        $counter = 1;
        foreach ($articles as $art) {
            $attributes = json_decode($art->attributes, true);
            echo "Total " . $father[$art->id]['name'] . ": " . $father[$art->id]['count'] . PHP_EOL;
            foreach ($attributes['entradas'] as $art2) {
                echo "Total " . $father[$art->id]['name'] . " entrada: " . $father[$art->id]['starter_name'][$art2['codigo']] . ": " . $father[$art->id]['starter'][$art2['codigo']] . PHP_EOL;
            }
            foreach ($attributes['plato'] as $art3) {
                echo "Total " . $father[$art->id]['name'] . " principal: " . $father[$art->id]['main_name'][$art3['codigo']] . ": " . $father[$art->id]['main'][$art3['codigo']] . PHP_EOL;
            }
        }
        foreach ($keywords as $keyword2) {
            echo "Total " . $keyword2 . ": " . $father['keywords'][$keyword2] . PHP_EOL;
        }
    }

    public function getTotalEstimatedShipping($results) {
//        dd($results);
        $totalCost = 0;
        $totalIncomeShipping = 0;
        $totalCost2 = 0;
        $totalLunches = 0;
        foreach ($results as $value) {
            if ($value->height > 4) {
                $totalCost += self::ROUTE_HOURS_EST * self::ROUTE_HOUR_COST;
            } else {
                $totalCost += self::ROUTE_HOUR_COST * 2;
            }
            $totalIncomeShipping += $value->unit_price;
            $totalLunches += $value->unit;
            if (($value->height + 1) > 7) {
                $totalCost2 += ($value->height + 1) * 5400;
            } else {
                $totalCost2 += ($value->height + 1) * 6400;
            }
        }
        $totalRoutes = count($results);
        $totalIncome = $totalLunches * self::LUNCH_PROFIT;
        $totalProfit = $totalIncomeShipping + $totalIncome;
        $totalGains = 0;
        $bestCost = "";
        if($totalCost > $totalCost2){
            $bestCost = "stops";
            $totalGains = $totalProfit - $totalCost2;
        } else {
            $bestCost = "hourly";
            $totalGains = $totalProfit - $totalCost;
        }
        $result = [
            "hourly_cost" => $totalCost,
            "stops_cost" => $totalCost2,
            "hoov_income" => $totalIncome,
            "shipping_income" => $totalIncomeShipping,
            "total_income"=>$totalProfit,
            "routes" => $totalRoutes,
            "lunches" => $totalLunches,
            "lunch_route" => ($totalLunches / $totalRoutes) ,
            "best_cost" => $bestCost,
            "day_profit" => $totalGains,
        ];
        echo 'Lunches: ' . $result['lunches'] . PHP_EOL;
        echo 'Routes: ' . $result['routes'] . PHP_EOL;
        echo 'Lunches per route: ' . $result['lunch_route'] . PHP_EOL;
        echo 'hourly_cost: ' . $result['hourly_cost'] . PHP_EOL;
        echo 'stops_cost: ' . $result['stops_cost']  . PHP_EOL;
        echo 'Income Shipping: ' . $result['shipping_income'] . PHP_EOL;
        echo 'Total Income: ' . $result['total_income'] . PHP_EOL;
        echo 'Best shipping: ' . $result['best_cost'] . PHP_EOL;
        echo 'Total Profit: ' . $result['day_profit']. PHP_EOL;
        if ($result['best_cost'] < $result['shipping_income']) {
            echo 'Scenario successful!!' . PHP_EOL;
        } else {
            echo 'Scenario FAILED!!' . PHP_EOL;
        }
        return $result;
        //dd($results);
    }

    public function prepareRouteModel(array $thedata, $results, $preOrganize, $x) {
//        dd($thedata);
        $deliveries = DB::select(""
                        . "SELECT DISTINCT(d.id), d.delivery,d.address_id,status, lat,`long`, 
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
        echo "Query results: " . count($deliveries) . PHP_EOL;
        $stops = $this->turnDeliveriesIntoStops($deliveries, $preOrganize);

        //dd($stops);
        if ($preOrganize) {
            $results = $this->createRoutes($stops, $results, $x, 'preorganize', true);
        } else {
            $results = $this->createRoutes($stops, $results, $x, 'simple', true);
        }

        if ($x == 1) {
            //dd($results);
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
                        $itemSavedStop = $item->stops()->where("address_id", $value["address_id"])->first();
                        if ($itemSavedStop) {
                            $foundInRoutes = true;
                            $itemSavedStop->amount += $value['amount'];
                            foreach ($value["deliveries"] as $del) {
                                $realDel = Delivery::find($del->id);
                                $realDel->stop_id = $itemSavedStop->id;
                                $realDel->save();
                            }
                            $itemSavedStop->save();
                        } else {
                            $stopContainer = Stop::create([
                                        "address_id" => $value["address_id"],
                                        "amount" => $value["amount"],
                                        "route_id" => $item->id
                            ]);
                            foreach ($value["deliveries"] as $del) {
                                $realDel = Delivery::find($del->id);
                                $realDel->stop_id = $stopContainer->id;
                                $realDel->save();
                            }
                            $item->height++;
                            $item->save();
                        }
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
                    $stopContainer = Stop::create([
                                "address_id" => $value["address_id"],
                                "amount" => $value["amount"],
                                "route_id" => $route->id
                    ]);
                    foreach ($value["deliveries"] as $del) {
                        $realDel = Delivery::find($del->id);
                        $realDel->stop_id = $stopContainer->id;
                        $realDel->save();
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

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function turnDeliveriesIntoStops($deliveries, $preorganize) {
        $stops = array();
        $shipping = [
            "1" => 3050.00,
            "2" => 5200.00,
            "3" => 8100.00,
            "4" => 11200.00,
            "5" => 11500.00,
            "6" => 14100.00,
            "7" => 16450.00,
            "8" => 19200.00,
            "9" => 17550.00,
            "10" => 19500.00,
            "11" => 21750.00,
        ];
        if (count($deliveries) > 0) {
            $initialAddress = $deliveries[0]->address_id;
            $deliveryCounter = 0;
            $packages = [];
            $totalCounter = 0;
            foreach ($deliveries as $value) {
                if ($preorganize) {
                    $totalCounter++;
                    if ($value->address_id == $initialAddress) {
                        $deliveryCounter++;
                        array_push($packages, $value);
                    } else {
                        $stop = [
                            "amount" => $deliveryCounter,
                            "address_id" => $initialAddress,
                            "latitude" => $value->lat,
                            "longitude" => $value->long,
                            "shipping" => $shipping[$deliveryCounter],
                            "deliveries" => $packages
                        ];
                        array_push($stops, $stop);
                        $packages = [];
                        array_push($packages, $value);
                        $deliveryCounter = 1;
                        $initialAddress = $value->address_id;
                    }
                    if ($totalCounter == count($deliveries)) {
                        $stop = [
                            "amount" => $deliveryCounter,
                            "address_id" => $initialAddress,
                            "latitude" => $value->lat,
                            "longitude" => $value->long,
                            "shipping" => $shipping[$deliveryCounter],
                            "deliveries" => $packages
                        ];
                        array_push($stops, $stop);
                    }
                } else {
                    $packages = [$value];
                    $stop = [
                        "amount" => 1,
                        "address_id" => $value->address_id,
                        "latitude" => $value->lat,
                        "longitude" => $value->long,
                        "shipping" => 2300,
                        "deliveries" => $packages
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
        //$articles = Article::where('start_date',$date)->get();
        $articles = Article::where('start_date', "2018-09-01")->get();
        //$date = date_create();
        $total = rand(0, 10);
        for ($x = 0; $x <= $total; $x++) {
            $latit = rand($lat_min, $lat_max) / 1000000000;
            $longit = rand($lng_min, $lng_max) / 1000000000;
            $address = OrderAddress::create([
                        "user_id" => 1,
                        "name" => "test",
                        "city_id" => 524,
                        "region_id" => 11,
                        "country_id" => 1,
                        "address" => "Calle test",
                        "lat" => $latit,
                        "long" => $longit,
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
                $delivery = Delivery::create([
                            "user_id" => 1,
                            "delivery" => $date,
                            "type_id" => $art->id,
                            "status" => "enqueue",
                            "starter_id" => $starterPlate['codigo'],
                            "main_id" => $mainPlate['codigo'],
                            "address_id" => $address->id
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
        $this->getTotalEstimatedShipping($results);
        $this->getTotalEstimatedShipping($results2);
    }

}
