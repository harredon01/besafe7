<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Item;
use App\Models\Push;
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
    const PAYMENT_APPROVED = 'payment_approved';
    const PAYMENT_DENIED = 'payment_denied';
    const PLATFORM_NAME = 'food';
    const TRANSACTION_CONDITION = 'transaction';
    const ORDER_PAYMENT_REQUEST = 'order_payment_request';

    public function addDiscounts(User $user, Order $order) {
        Cart::session($user->id)->removeConditionsByType(self::PLATFORM_NAME);
        $order->orderConditions()->where("type", self::PLATFORM_NAME)->delete();
        Cart::session($user->id)->removeConditionsByType(self::TRANSACTION_CONDITION);
        $order->orderConditions()->where("type", self::TRANSACTION_CONDITION)->delete();
        $items = $order->items;
        $conditions = [];
        $requiresShipping = 0;
        if ($order->merchant_id == 1299) {
            foreach ($items as $value) {
                $attributes = json_decode($value->attributes, true);
                if ($value->quantity > 10) {
                    $control2 = floor($value->quantity / 11);
                    $buyers = 1;
                    if (array_key_exists("multiple_buyers", $attributes)) {
                        if ($attributes['multiple_buyers']) {
                            $buyers = $attributes['buyers'];
                        }
                    }
                    $discount = ($control2 * $buyers * self::UNIT_LOYALTY_DISCOUNT);
                    $condition = new OrderCondition(array(
                        'name' => "Por cada 11 días recibe un descuento de 11 mil pesos",
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
        }

        return array("status" => "success", "message" => "Conditions added", "conditions" => $conditions, "order" => $order);
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
        
    }

    public function denyPayment(Payment $payment) {
        
    }

    public function pendingPayment(Payment $payment) {
        
    }

    public function getTransactionTotal($total) {
        return ($total * 0.0349 + 900);
    }

    public function approveOrder(Order $order) {
        $data = array();
        $items = $order->items;
        $className = "App\\Services\\Geolocation";
        $geo = new $className;
        $address = $order->orderAddresses()->where("type", "shipping")->first();
        $result = $geo->checkMerchantPolygons($address->lat, $address->long, $order->merchant_id);
        $polygon = $result['polygon'];
        $address->polygon_id = $polygon->id;
        $address->save();
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
                if ($data['type'] == "credit") {
                    $this->createDeposit($order);
                }
                if ($data['type'] == "delivery") {
                    //$this->createDelivery($order, $item, $address->id);
                }
            }
            if (array_key_exists("model", $data)) {
                $class = "App\\Models\\" . $data["model"];
                $model = $class::find("id", $data['id']);
            } else {
                
            }
        }
        $order->status = "approved";
        $order->save();
        return array("status" => "success", "message" => "Order approved, subtasks completed", "order" => $order);
    }

    public function createMealPlan(Order $order, Item $item, $address_id) {
        $data = json_decode($order->attributes, true);
        $buyers = $data['buyers'];
        for ($x = 0; $x < count($buyers); $x++) {
            $user = User::find($buyers[$x]);
            if ($user) {
                $this->createDeliveries($user->id, $item, $address_id);
            }
        }
    }

    public function createDelivery(Order $order, Item $item, $address_id) {
        $delivery = new Delivery();
        $delivery->user_id = $order->user_id;

        $details["merchant_id"] = $item->merchant_id;
        $products = ["product" => $item->product_variant_id, "quantity" => 1];
        $details["products"] = $products;
        $delivery->shipping = $order->shipping;
        $delivery->merchant_id = $item->merchant_id;
        $delivery->provider = "Rapigo";
        $delivery->address_id = $address_id;
        $delivery->status = "pending";
        $delivery->details = json_encode($details);
        $delivery->save();
    }

    public function createDeposit(Order $order) {
        $payments = $order->payments()->with("user.push")->get();
        foreach ($payments as $value) {
            $user = $value->user->toArray();
            $push = null;
            foreach ($user['push'] as $item) {
                if ($item['platform'] == "food") {
                    $push = $item;
                }
            }
            if ($push) {
                if (array_key_exists("credits", $push)) {
                    if (!$push['credits'] || $push['credits'] == 0) {
                        $push['credits'] = 1;
                        Push::where("id", $push['id'])->update($push);
                    }
                } else {
                    Push::create(["user_id" => $user['id'],
                        "platform" => "food",
                        'credits' => 1,
                    ]);
                }
            } else {
                Push::create(["user_id" => $user['id'],
                    "platform" => "food",
                    'credits' => 1,
                ]);
            }
            Delivery::where("user_id", $user['id'])->where("status", "suspended")->update(['status' => 'pending']);
        }
    }

    public function createDeliveries($user_id, Item $item, $address_id) {
        $lastDelivery = Delivery::where('user_id', $user_id)->whereIn('status', ["pending", "deposit"])->where('provider', "Rapigo")->orderBy('delivery', 'desc')->first();
//        if($user_id!=1){
//            dd($lastDelivery);
//        }
        $returnDelivery = false;
        $hasDeposit = false;

        $attributes = $item->attributes;
        $shippingPaid = 0;
        if (array_key_exists("shipping", $attributes)) {
            if ($attributes["shipping"] > 0) {
                $shippingPaid = $attributes["shipping"];
            }
        }

        if (array_key_exists("credits", $attributes)) {
            if ($attributes["credits"] > 0) {
                $returnDelivery = true;
            }
        }
        if ($lastDelivery) {
            if ($lastDelivery->status == "deposit" && $returnDelivery) {
                $hasDeposit = true;
                $date = date_create($lastDelivery->delivery);
                date_sub($date, date_interval_create_from_date_string("1 days"));
            } else {
                if (time() < strtotime($lastDelivery->delivery)) {
                    $date = date_create($lastDelivery->delivery);
                } else {
                    $date = date_create();
                }
            }
        } else {
            $date = date_create();
        }
        for ($x = 0; $x < $item->quantity; $x++) {
            date_add($date, date_interval_create_from_date_string("1 days"));

            $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
            if ($dayofweek == 6) {
                date_add($date, date_interval_create_from_date_string("2 days"));
            } else if ($dayofweek == 0) {
                date_add($date, date_interval_create_from_date_string("1 days"));
            }
            $delivery = new Delivery();
            $delivery->user_id = $user_id;

            $details["merchant_id"] = $item->merchant_id;
            $products = ["product" => $item->product_variant_id, "quantity" => 1];
            $details["products"] = $products;
            $delivery->shipping = $shippingPaid;
            $delivery->merchant_id = $item->merchant_id;
            $delivery->address_id = $address_id;
            $delivery->status = "pending";
            $delivery->provider = "Rapigo";
            if ($x > 0 && $returnDelivery) {
                $details["pickup"] = "envase";
            }
            if ($returnDelivery) {
                $details["deliver"] = "envase";
            }
            if ($x == 0 && $hasDeposit) {
                
            }
            $delivery->delivery = date_format($date, "Y-m-d") . " 12:00:00";
            $delivery->details = json_encode($details);
            $delivery->save();
        }
        if ($returnDelivery) {
            date_add($date, date_interval_create_from_date_string("1 days"));
            if ($hasDeposit) {
                $lastDelivery->delivery = $date;
                $lastDelivery->save();
            } else {
                $delivery = new Delivery();

                $details["merchant_id"] = $item->merchant_id;
                $products = ["product" => $item->product_variant_id, "quantity" => 1];
                $details["products"] = $products;
                $details["deliver"] = "deposit";
                $delivery->details = json_encode($details);
                $delivery->user_id = $user_id;
                $delivery->delivery = $date;
                $delivery->merchant_id = $item->merchant_id;
                $delivery->shipping = 2500;
                $delivery->address_id = $address_id;
                $delivery->status = "deposit";
                $delivery->provider = "Rapigo";
                $delivery->save();
            }
        }
    }

}
