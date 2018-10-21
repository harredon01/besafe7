<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use App\Models\Delivery;
use App\Models\OrderCondition;
use Darryldecode\Cart\CartCondition;
use Cart;
use DB;

class EditOrderFood {

    const OBJECT_ORDER = 'Order';
    const CREDIT_PRICE = 10000;
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
            $data = $cart;
            $order->subtotal = $data["subtotal"];
            $order->tax = 0;
            $order->shipping = 0;
            $order->discount = 0;
            $order->total = $data["total"];
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

    public function approveOrder(Order $order) {
        $items = Cart::getContent();
        $data = array();
        $items = $order->items();
        $address = $order->orderAddresses()->where("type", "shipping")->first();
        foreach ($items as $item) {
            $data = json_decode($item->attributes, true);
            $item->attributes = $data;
            if (array_key_exists("type", $data)) {
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
                    $this->createMealPlan($order, $item, $address->id);
                }
            }
            $attrs = json_decode($item->attributes, true);
            if ($attrs) {
                if (array_key_exists("model", $attrs)) {
                    $class = "App\\Models\\" . $attrs["model"];
                    $model = $class::where("id", $attrs['id']);
                } else {
                    
                }
            }
            $order->status = "approved";
            $order->save();
        }
        return array("status" => "error", "message" => "Address does not exist");
    }

    public function createMealPlan(Order $order, Item $item, $address_id) {
        $data = json_decode($order->attributes, true);
        $buyers = $data['buyers'];
        for ($x = 0; $x <= count($buyers); $x++) {
            $this->createDeliveries($buyers[$x], $item, $address_id);
        }
    }

    public function createDeliveries($user_id, Item $item, $address_id) {
        $date = date_create();
        for ($x = 0; $x <= $item->quantity; $x++) {
            date_add($date, date_interval_create_from_date_string("1 days"));
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

    public function prepareRouteModel(array $thedata) {
        $deliveries = DB::select(""
                        . "SELECT d.id, name, description, icon, minimum, lat,`long`, type, telephone, address, 
			( 6371 * acos( cos( radians( :lat ) ) *
		         cos( radians( m.lat ) ) * cos( radians(  `long` ) - radians( :long ) ) +
		   sin( radians( :lat2 ) ) * sin( radians(  d.lat  ) ) ) ) AS Distance 
                   FROM deliveries d
                    WHERE
                        status = 'active'
                            AND d.private = 0
                            AND d.type <> ''
                            AND lat BETWEEN :latinf AND :latsup
                            AND `long` BETWEEN :longinf AND :longsup
                    HAVING distance < :radius order by distance asc limit 20 "
                        . "", $thedata);
        if (count($deliveries) > 0) {
            $initialAddress = $deliveries[0]->address_id;
            $deliveryCounter = 0;
            $stops = array();
            $totalCounter = 0;
            foreach ($deliveries as $value) {
                $totalCounter++;
                if ($value->address_id == $initialAddress) {
                    $deliveryCounter++;
                } else {
                    $stop = [
                        "amount" => $deliveryCounter,
                        "address_id" => $initialAddress
                    ];
                    array_push($stops, $stop);
                    $deliveryCounter = 1;
                    $initialAddress = $value->address_id;
                }
                if ($totalCounter == count($deliveries)) {
                    $stop = [
                        "amount" => $deliveryCounter,
                        "address_id" => $initialAddress
                    ];
                    array_push($stops, $stop);
                }
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function prepareRoutingSimulation() {
        $radius = 1;
        $R = 6371;
        $remainders = array();
        $lat = 4.720112;
        $long = -74.064916;
        $maxLat = $lat + rad2deg($radius / $R);
        $minLat = $lat - rad2deg($radius / $R);
        $maxLon = $long + rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
        $minLon = $long - rad2deg(asin($radius / $R) / cos(deg2rad($lat)));
        for ($x = 0; $x <= 8; $x++) {
            $operativeRadius = 0;
            if ($x < 4) {
                $operativeRadius = $radius;
            } else {
                $operativeRadius = $radius * 2;
            }
            if ($x == 0 || $x == 4) {
                $thedata = [
                    'lat' => $lat,
                    'lat2' => $lat,
                    'long' => $long,
                    'latinf' => $lat,
                    'latsup' => $maxLat,
                    'longinf' => $long,
                    'longsup' => $maxLon,
                    'radius' => $operativeRadius
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
                    'radius' => $operativeRadius
                ];
            } else if ($x == 2 || $x == 6) {
                $thedata = [
                    'lat' => $lat,
                    'lat2' => $lat,
                    'long' => $long,
                    'latinf' => $minLat,
                    'latsup' => $lat,
                    'longinf' => $minLon,
                    'longsup' => $long,
                    'radius' => $operativeRadius
                ];
            } else if ($x == 3 || $x == 7) {
                $thedata = [
                    'lat' => $lat,
                    'lat2' => $lat,
                    'long' => $long,
                    'latinf' => $lat,
                    'latsup' => $maxLat,
                    'longinf' => $minLon,
                    'longsup' => $long,
                    'radius' => $operativeRadius
                ];
            }
            $this->prepareRouteModel($thedata);
        }


        return array("deliveries" => $deliveries);
    }

}
