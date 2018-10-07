<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use App\Models\OrderCondition;
use Darryldecode\Cart\CartCondition;
use Cart;
use DB;

class EditOrderFood {

    const OBJECT_ORDER = 'Order';
    const OBJECT_ORDER_REQUEST = 'OrderRequest';
    const ORDER_PAYMENT = 'order_payment';
    const ORDER_PAYMENT_REQUEST = 'order_payment_request';

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function checkOrder(User $user, Order $order, array $data, $platform) {
        $items = $order->items();
        $push = $user->push()->where("platform", $platform)->first();
        $requiredCredits = 0;
        $requiredBuyers = 1;
        foreach ($items as $value) {
            $attributes = json_decode($value->attributes, true);
            if (array_key_exists("requires_credit", $attributes)) {
                if ($attributes['requires_credit']) {
                    $requiredCredits += $attributes['credits'];
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
                }
            }
        }
        $address = $order->orderAddresses()->where('type', "shipping")->get();
        if (!$address) {
            return array("status" => "error", "message" => "Order does not have Shipping Address");
        }
        if ($requiredCredits > 0) {
            $creditHolders = Push::whereIn('user_id', $data['payers'])->where("credits", ">", 0)->where("platform", $platform)->count();
            if ($push->credits > 0) {
                $creditHolders++;
            }
            if ($creditHolders < $requiredCredits) {
                return array("status" => "error", "message" => "Order does not have enough payers");
            }
        }
        return array("status" => "success", "message" => "Order Passed validation", "order" => $order);
    }

    public function addDiscounts(User $user, Order $order) {
        Cart::session($user->id)->removeConditionsByType("food");
        $order->orderConditions()->where("type", "food")->delete();
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
                    $discount = (($control2 - 1) * $buyers * 11000);
                } else {
                    $discount = ($control2 * $buyers * 11000);
                }
                $condition = new OrderCondition(array(
                    'name' => "Descuento por compromiso orden: " . $order->id,
                    'target' => "subtotal",
                    'type' => "food",
                    'value' => "-" . $discount,
                    'total' => $discount,
                ));
                array_push($conditions, $condition);
                $order->orderConditions()->save($condition);
                $condition2 = new CartCondition(array(
                    'name' => $condition->name,
                    'type' => $condition->target,
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
    public function prepareOrder(User $user, Order $order, $platform, array $info, $cart) {

        $checkResult = $this->checkOrder($user, $order, $info, $platform);
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
                        $this->splitOrder($user, $order, $info['payers'], $platform);
                        $totalBuyers = count($info['payers'])+1;
                    }
                }
            }
            $buyerSubtotal = $order->total / $totalBuyers;
            $buyerTax = $order->tax / $totalBuyers;
            $transactionCost = 0;
            if ($totalBuyers > 1) {
                $transactionCost = $buyerSubtotal * (0.0349) + 900;
                $order->total = $order->total + ($transactionCost * $totalBuyers);
                $order->tax = $order->tax + (0);
                //$order->status = "payment_created";
            }
            $payment = Payment::where("order_id", $order->id)->where("user_id", $user->id)->where("status", "pending")->first();
            if ($payment) {
                
            } else {
                $payment = new Payment;
            }
            $payment->user_id = $user->id;
            $payment->address_id = $order->address_id;
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
        foreach ($items as $item) {
            $data = json_decode($item->attributes, true);
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
                    $this->createMealPlan($order, $item, $data);
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

    public function createMealPlan(Order $order, Item $item, $data) {
        $usersSent = null;
        $itemUsers = 0;
        if (array_key_exists("item_users", $data)) {
            $itemUsers = $data['item_users'];
        }

        if (array_key_exists("users", $data)) {
            $users = $data['users'];
            if ($users) {
                foreach ($users as $value) {

                    $itemUsers--;
                }
            }
        }
        $user = $order->user();
        for ($x = 0; $x <= $itemUsers; $x++) {
            $this->createDeliveries($user->id, $item, $data);
        }
    }

    public function submitOrder(Order $order) {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    private function splitOrder(User $user, Order $order, $buyers, $platform) {
        if ($order->user_id == $user->id) {
            $items = $order->items;
            $splitTotal = $order->total;
            $depositTotal = 0;
            foreach ($items as $value) {
                $attributes = json_decode($value->attributes, true);
                if (array_key_exists("is_credit", $attributes)) {
                    if ($attributes['is_credit']) {
                        $splitTotal -= 10000;
                    }
                }
                if (array_key_exists("requires_credit", $attributes)) {
                    if ($attributes['requires_credit']) {
                        $depositTotal += $attributes['credits'] * 10000;
                    }
                }
            }
            $totalBuyers = count($buyers) + 1;
            $buyerSubtotal = $splitTotal / $totalBuyers;
            $buyerTax = $order->tax / $totalBuyers;
            $transactionCost = $buyerSubtotal * (0.0349) + 900;
            $followers = array();
            foreach ($buyers as $buyerItem) {
                $buyer = User::find($buyerItem);
                if ($buyer) {
                    $buyerTotal = $buyerSubtotal;
                    if ($depositTotal > 0) {
                        $push = $buyer->push()->where("platform", $platform)->first();
                        if ($push->credits == 0) {
                            $buyerTotal += 10000;
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
            return $platFormService->sendMassMessage($data, $followers, $user, true, $date, $platform);
        }
    }

}
