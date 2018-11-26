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
use App\Services\Rapigo;
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

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function checkOrder(User $user, Order $order, array $data) {
        $items = $order->items();
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
            array_push($data['payers'], $user->id);
            $creditHolders = $this->checkUsersCredits($data['payers']);
            if ($creditHolders < $requiredCredits) {
                return array("status" => "error", "message" => "Order does not have enough payers");
            }
        }
        return array("status" => "success",
            "message" => "Order Passed validation",
            "order" => $order,
            "split" => $splitTotal,
            "creditHolders" => $order,
            "requiredCredits" => $splitTotal,
            "deposit" => $totalDeposit
        );
    }

    public function checkUsersCredits($usersArray) {
        $users = User::whereIn("id", $usersArray)->with(['push' => function ($query) {
                        $query->where('platform', 'food');
                    }, 'deliveries' => function ($query) {
                        $query->where('status', 'pending');
                    }])->get();
        $openCredits = 0;
        foreach ($users as $user) {
            $credits = 0;
            $usedCredits = 0;
            $push = $user->push;
            if (count($push) > 0) {
                $credits = $push[0]->credits;
            }
            $deliveries = $push->deliveries;
            foreach ($deliveries as $delivery) {
                $attributes = json_decode($delivery->details, true);
                if (array_key_exists("pickup", $attributes)) {
                    if ($attributes["pickup"] == "envase") {
                        $usedCredits = 1;
                    }
                }
                if (array_key_exists("deliver", $attributes)) {
                    if ($attributes["deliver"] == "deposit") {
                        $credits = 1;
                    }
                }
            }
            $openCredits += ($credits - $usedCredits);
        }
        return $openCredits;
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function checkOrderCredits(User $user, Order $order, array $data) {
        $items = $order->items();
        $requiredCredits = 0;
        $requiredBuyers = 1;
        $splitTotal = $order->total;
        $totalDeposit = 0;
        $creditItem = "";
        $creditItemMerchant = "";
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
                    $creditItem = $value->product_variant_id;
                }
            }
            $creditItemMerchant = $value->merchant_id;
        }
        if (!$creditItem) {
            $variant = ProductVariant::where("type", "deposit")->where("merchant_id", $creditItemMerchant)->first();
            $creditItem = $variant->id;
        }
        if ($requiredCredits > 0) {
            array_push($data['payers'], $user->id);
            $creditHolders = $this->checkUsersCredits($data['payers']);
            if ($creditHolders < $requiredCredits) {
                return array("status" => "error",
                    "message" => "Order does not have enough payers",
                    "order" => $order,
                    "split" => $splitTotal,
                    "creditHolders" => $order,
                    "creditItem" => $creditItem,
                    "creditItemMerchant" => $creditItemMerchant,
                    "requiredCredits" => $splitTotal,
                    "deposit" => $totalDeposit,
                );
            }
        }
        return array("status" => "success",
            "message" => "Order Passed validation",
            "order" => $order,
            "split" => $splitTotal,
            "creditHolders" => $order,
            "creditItem" => $creditItem,
            "creditItemMerchant" => $creditItemMerchant,
            "requiredCredits" => $splitTotal,
            "deposit" => $totalDeposit,
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
        $user = $payment->user;
        $followers = [];
        array_push($followers, $user);
        $payload = [
            "order_id" => $order->id,
            "first_name" => $user->firstName,
            "last_name" => $user->lastName,
            "payment" => $payment
        ];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => self::OBJECT_ORDER,
            "sign" => true,
            "payload" => $payload,
            "type" => self::PAYMENT_DENIED,
            "user_status" => $user->getUserNotifStatus()
        ];
        $date = date("Y-m-d H:i:s");
        $className = "App\\Services\\EditAlerts";
        $platFormService = new $className(); //// <--- this thing will be autoloaded
        $platFormService->sendMassMessage($data, $followers, $user, true, $date, self::PLATFORM_NAME);
        $order = Order::find($payment->order_id);
        if ($order) {
            $payments = $order->payments()->where("status", "<>", "Paid")->where("id", "<>", $payment->id)->count();
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
        $user = $payment->user;
        $followers = [];
        array_push($followers, $user);
        $payment->status = "denied";
        $payment->save();
        $payload = [
            "order_id" => $order->id,
            "first_name" => $user->firstName,
            "last_name" => $user->lastName,
            "payment" => $payment
        ];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => self::OBJECT_ORDER,
            "sign" => true,
            "payload" => $payload,
            "type" => self::PAYMENT_DENIED,
            "user_status" => $user->getUserNotifStatus()
        ];
        $date = date("Y-m-d H:i:s");
        $className = "App\\Services\\EditAlerts";
        $platFormService = new $className(); //// <--- this thing will be autoloaded
        return $platFormService->sendMassMessage($data, $followers, $user, true, $date, self::PLATFORM_NAME);
    }

    public function pendingPayment(Payment $payment) {
        $payment->status = "Open";
        $payment->save();
    }

    public function getTransactionTotal($total) {
        return ($total * 0.0349 + 900);
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

    public function createMealPlan(Order $order, Item $item, $address_id) {
        $data = json_decode($order->attributes, true);
        $buyers = $data['buyers'];
        for ($x = 0; $x < count($buyers); $x++) {
            $this->createDeliveries($buyers[$x], $item, $address_id);
        }
    }

    public function createDeliveries($user_id, Item $item, $address_id) {
        $delivery = Delivery::where('user_id', $user_id)->orderBy('delivery', 'desc')->first();
        if ($delivery) {

            if (time() < strtotime($delivery->delivery)) {
                $date = date_create($delivery->delivery);
            } else {
                $date = date_create();
            }
        } else {
            $date = date_create();
        }
        $attributes = json_decode($item->attributes, true);
        $shippingPaid = 0;
        if (array_key_exists("shipping", $attributes)) {
            if ($attributes["shipping"] > 0) {
                $shippingPaid = $attributes["shipping"];
            }
        }
        $returnDelivery = false;
        if (array_key_exists("credits", $attributes)) {
            if ($attributes["credits"] > 0) {
                $returnDelivery = true;
            }
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
            $delivery->delivery = $date;
            $details["merchant_id"] = $item->merchant_id;
            $products = ["product" => $item->product_variant_id, "quantity" => 1];
            $details["products"] = $products;
            $delivery->shipping = $shippingPaid;
            $delivery->address_id = $address_id;
            $delivery->status = "pending";
            if ($x > 0 && $returnDelivery) {
                $details["pickup"] = "envase";
            }
            $delivery->details = json_encode($details);
            $delivery->save();
        }
        if ($returnDelivery) {
            $delivery = new Delivery();
            date_add($date, date_interval_create_from_date_string("1 days"));
            $details["merchant_id"] = $item->merchant_id;
            $products = ["product" => $item->product_variant_id, "quantity" => 1];
            $details["products"] = $products;
            $details["deliver"] = "deposit";
            $delivery->details = json_encode($details);
            $delivery->user_id = $user_id;
            $delivery->delivery = $date;
            $delivery->shipping = 2500;
            $delivery->address_id = $address_id;
            $delivery->status = "deposit";
            $delivery->save();
        }
    }

    public function reprogramDeliveries() {
        $date = date_create();
        $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
        if ($dayofweek < 5 && $dayofweek > 0) {
            date_add($date, date_interval_create_from_date_string("1 days"));
        } else if ($dayofweek == 5) {
            date_add($date, date_interval_create_from_date_string("3 days"));
        } else {
            return null;
        }
        $la = date_format($date, "Y-m-d");
//        $date = date_create($la . " 23:59:59");
//        dd($date);
        $deliveries = Delivery::where('status', 'pending')->where('delivery', '<', $la . " 23:59:59")->where('user_id', 1)->orderBy('delivery', 'desc')->get();
        foreach ($deliveries as $item) {
            $delivery = Delivery::where('id', "<>", $item->id)->where('user_id', $item->user_id)->where('delivery', '>', $item->delivery)->orderBy('delivery', 'desc')->first();
            if ($delivery) {
                $date = date_create($delivery->delivery);
            } else {
                $date = date_create($item->delivery);
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
