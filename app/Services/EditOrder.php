<?php

namespace App\Services;

use Validator;
use App\Models\Order;
use App\Models\OrderCondition;
use App\Models\OrderAddress;
use App\Models\Payment;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use App\Models\ProductVariant;
use App\Models\Address;
use App\Models\Merchant;
use App\Services\EditCart;
use App\Services\PayU;
use App\Services\Stripe;
use App\Mail\OrderApproved;
use App\Mail\OrderApprovedInvoice;
use Mail;
use Darryldecode\Cart\CartCondition;
use Cart;
use DB;

class EditOrder {

    const OBJECT_ORDER = 'Order';
    const TRANSACTION_CONDITION = 'transaction';
    const OBJECT_ORDER_REQUEST = 'OrderRequest';
    const ORDER_PAYMENT = 'order_payment';
    const ORDER_PAYMENT_REQUEST = 'split_order_payment';
    const PAYMENT_APPROVED = 'payment_approved';
    const PAYMENT_STATUS = 'payment_status';
    const ORDER_STATUS = 'order_status';
    const PAYMENT_DENIED = 'payment_denied';
    const PLATFORM_NAME = 'Food';

    /**
     * The Auth implementation.
     *
     */
    protected $auth;

    /**
     * The Auth implementation.
     *
     */
    protected $editCart;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $payU;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $stripe;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $notifications;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $geolocation;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(PayU $payU, Stripe $stripe, EditCart $editCart) {
        $this->payU = $payU;
        $this->notifications = app('Notifications');
        $this->stripe = $stripe;
        $this->editCart = $editCart;
        $this->geolocation = app('Geolocation');
    }

    public function getOrder(User $user) {
        $order = Order::where('status', 'pending')->where('user_id', $user->id)->first();
        if ($order) {
            return $order;
        } else {
            $order = Order::create([
                        'status' => 'pending',
                        'price' => 0,
                        'tax' => 0,
                        'delivery' => 0,
                        'is_digital' => 0,
                        'is_shippable' => 1,
                        'total' => 0,
                        'user_id' => $user->id
            ]);
            return $order;
        }
    }

    public function setOrderRecurringType(User $user, $order_id, array $data) {
        $validator = $this->validatorOrderRecurring($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $order = Order::find($order_id);
        if ($order) {
            if ($user->id == $order->user_id) {
                if ($data["recurring"]) {
                    $order->recurring_type = $data["recurring_type"];
                    $order->recurring_value = $data["recurring_value"];
                } else {
                    $order->recurring_type = null;
                    $order->recurring_value = null;
                }


                $order->save();
                $order->items;
                $order->order_conditions = $order->orderConditions;
                return response()->json(array("status" => "success", "message" => "order updated", "order" => $order), 200);
            }
            return response()->json(array("status" => "error", "message" => "order does not belong to user"), 400);
        }
        return response()->json(array("status" => "error", "message" => "order not found"), 400);
    }

    public function addItemsToOrder(User $user, Order $order) {
        $cart = $this->editCart->getCheckoutCart($user);
        Item::where('user_id', $user->id)
                ->whereNull('order_id')
                ->update(['order_id' => $order->id, 'updated_at' => date("Y-m-d H:i:s")]);
        $order->total = $cart['total'];
        $order->subtotal = $cart['subtotal'];
        $order->shipping = $cart['shipping'];
        return $order;
//        foreach ($cartConditions as $condition) {
//            $cond = array();
//            $cond['target'] = $condition->getTarget(); // the target of which the condition was applied
//            $cond['name'] = $condition->getName(); // the name of the condition
//            $cond['type'] = $condition->getType(); // the type
//            $cond['value'] = $condition->getValue(); // the value of the condition
//            $cond['order'] = $cart['subtotal']; // the order of the condition
//            $cond['attributes'] = json_encode($condition->getAttributes()); // the attributes of the condition, returns an empty [] if no attributes added
//            $value = $condition->getCalculatedValue($cart['subtotal']);
//            $cond['order_id'] = $order->id; // the name of the condition
//            $cond['total'] = $value;
//            OrderCondition::insert($cond);
//        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    private function splitOrder(User $user, Order $order, $address, $buyers, $depositTotal, $splitTotal, $item, $platform) {
        if ($order->user_id == $user->id) {
            $totalBuyers = count($buyers) + 1;
            $buyerSubtotal = $splitTotal / $totalBuyers;
            $buyerTax = $order->tax / $totalBuyers;
            $followers = array();
            foreach ($buyers as $buyerItem) {
                $buyer = User::find($buyerItem);
                if ($buyer) {
                    //dd($buyer->toArray());
                    $buyerTotal = $buyerSubtotal;
                    if ($depositTotal > 0) {
                        $buyersCredts = [$buyer->id];
                        $result = $this->checkUsersCredits($buyersCredts, $platform);
                        if ($result < 1) {
                            $buyerTotal += $item->price;
                        }
                    }
                    $transactionCost = $this->getTransactionTotal($buyerTotal);
                    array_push($followers, $buyer);
                    $payment = Payment::where("order_id", $order->id)->where("user_id", $buyer->id)->where("status", "pending")->first();
                    if ($payment) {
                        
                    } else {
                        $payment = new Payment;
                    }
                    $payment->user_id = $buyer->id;
                    if ($address) {
                        $payment->address_id = $address->id;
                    } else {
                        $payment->address_id = null;
                    }
                    $payment->order_id = $order->id;
                    $payment->status = "pending";
                    $payment->subtotal = round($buyerTotal);
                    $payment->transaction_cost = round($transactionCost);
                    $payment->total = round($buyerTotal + $transactionCost);
                    $payment->tax = $buyerTax;
                    $payment->save();
                    $payload = [
                        "payment_id" => $payment->id,
                        "payment_total" => $payment->total,
                        "order_id" => $order->id,
                        "first_name" => $user->firstName,
                        "last_name" => $user->lastName,
                    ];
                    $data = [
                        "trigger_id" => $payment->id,
                        "message" => "",
                        "subject" => "",
                        "object" => self::OBJECT_ORDER,
                        "sign" => true,
                        "payload" => $payload,
                        "type" => self::ORDER_PAYMENT_REQUEST,
                        "user_status" => $user->getUserNotifStatus()
                    ];
                    $date = date("Y-m-d H:i:s");
                    $this->notifications->sendMassMessage($data, $followers, $user, true, $date, true);
                }
            }
        }
    }

    public function getTransactionTotal($total) {
        return ((($total * 6.5) / 100) + 900);
    }

    public function removeTransactionCost(Order $order) {
        Cart::session($order->user_id)->removeConditionsByType(self::TRANSACTION_CONDITION);
        $order->orderConditions()->where("type", self::TRANSACTION_CONDITION)->delete();
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    public function prepareOrder(User $user, $platform, array $info) {
        if (array_key_exists("order_id", $info)) {
            $order = Order::find($info['order_id']);
            if ($order) {
                $cart = $this->editCart->getCheckoutCart($user);
                if (count($cart['items']) > 0) {
                    if (array_key_exists("merchant_id", $info)) {
                        $merchant = Merchant::find($info['merchant_id']);
                        if ($merchant) {
                            $order->merchant_id = $merchant->id;
                        }
                    }
                    $order->subtotal = $cart["subtotal"];
//                    if($condition){
//                        
//                    }
                    $order->tax = 0;
                    $transactionCost = 0;
                    $splitOrder = false;
                    $order->shipping = $cart["shipping"];
                    $order->discount = 0;
                    $order->total = $cart["total"];
                    $checkResult = $this->checkOrder($user, $order, $info);
                    $result = null;
                    if ($checkResult['status'] == "success") {
                        $order = $checkResult['order'];
                        $totalBuyers = 1;
                        $address = $order->orderAddresses()->where("type", "shipping")->first();
                        if (array_key_exists("split_order", $info)) {
                            if ($info['split_order']) {
                                if (array_key_exists("payers", $info)) {
                                    $splitOrder = true;
                                    $this->splitOrder($user, $order, $address, $info['payers'], $checkResult['totalDeposit'], $checkResult['split'], $checkResult['creditItem'], $platform);
                                    $totalBuyers = count($info['payers']) + 1;
                                }
                            }
                        } else {
                            Payment::where("order_id", $order->id)->where("user_id", "<>", $user->id)->where("status", "pending")->delete();
                        }

                        $buyerSubtotal = $checkResult['split'] / $totalBuyers;
                        $buyerTax = $order->tax / $totalBuyers;
                        if ($checkResult['totalDeposit'] > 0) {
                            $usersArray = [$user->id];
                            $userCredits = $this->checkUsersCredits($usersArray, $platform);
                            if ($userCredits == 0) {
                                $creditItem = $checkResult['creditItem'];
                                $buyerSubtotal += $creditItem->price;
                            }
                        }
                        if (array_key_exists("payers", $info)) {
                            if (count($info['payers']) > 0) {
                                
                            } else {
                                $info['payers'] = [];
                            }
                        } else {
                            $info['payers'] = [];
                        }
                        $thePayersArray = $info['payers'];
                        array_push($thePayersArray, $user->id);

                        $records = [
                            "buyers" => $thePayersArray
                        ];
                        $transactionCost = $this->getTransactionTotal($buyerSubtotal);

                        $payment = Payment::where("order_id", $order->id)->where("user_id", $user->id)->where("status", "pending")->first();
                        if ($payment) {
                            
                        } else {
                            $payment = new Payment;
                        }
                        if ($splitOrder) {
                            $records["split_order"] = $splitOrder;
                        }
                        $payment->subtotal = round($buyerSubtotal);
                        $payment->total = round($buyerSubtotal + $transactionCost);
                        $order->attributes = json_encode($records);
                        $payment->transaction_cost = $transactionCost;
                        $payment->user_id = $user->id;
                        if ($address) {
                            $payment->address_id = $address->id;
                        } else {
                            $payment->address_id = null;
                        }
                        $payment->order_id = $order->id;
                        $payment->status = "pending";

                        $payment->tax = $buyerTax;
                        $payment->save();
                        $result = array("status" => "success", "message" => "Order submitted, payment created", "payment" => $payment, "order" => $order);
                        if (array_key_exists("recurring", $info)) {
                            if ($info["recurring"] == true) {
                                $order->is_recurring = true;
                                $order->recurring_type = $info["recurring_type"];
                                $order->recurring_value = $info["recurring_value"];
                            }
                        }

                        $order->save();
                        return $result;
                    }
                    return $checkResult;
                } else {
                    return array("status" => "error", "message" => "Empty cart");
                }
            }
        }
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    public function checkOrder(User $user, Order $order, array $data) {
        $results = $this->checkOrderCreditsInternal($user, $order, $data);
        $results['status'] = "success";
        $results['message'] = "Order passed validation";
        if ($results['requiredBuyers'] > 0) {
            $results['status'] = "error";
            $results['message'] = "Order does not have enough buyers";
            $results['required_buyers'] = $results['requiredBuyers'];
            $results['type'] = "buyers";
            return $results;
        }
        if ($results['requiredCredits'] > 0) {
            $results['status'] = "error";
            $results['message'] = "Order does not have enough deposits";
            $results['required_credits'] = $results['requiredCredits'];
            $results['type'] = "credits";
            return $results;
        }
        if ($results['requiresShipping'] > 0) {
            $results['status'] = "error";
            $results['order'] = $order;
            $results['message'] = "Order requires shipping condition";
            $results['type'] = "shipping";
            return $results;
        }
        if ($results['requiresDelivery'] > 0) {
            $results['status'] = "error";
            $results['order'] = $order;
            $results['message'] = "Order requires delivery date";
            $results['type'] = "delivery";
            return $results;
        }
        $address = $order->orderAddresses()->where('type', "shipping")->get();
        if (!$address) {
            return array("status" => "error", "message" => "Order does not have Shipping Address");
        }

        return $results;
    }

    public function checkUsersCredits($usersArray, $platform) {
        $users = User::whereIn("id", $usersArray)->with(['push' => function ($query) use ($platform) {
                        $query->where('platform', strtolower($platform));
                    }])->get();
//        $users = User::whereIn("id", $usersArray)->with(['push' => function ($query) use ($platform) {
//                        $query->where('platform', strtolower($platform));
//                    }, 'deliveries' => function ($query) {
//                        $query->where('status', 'pending');
//                    }])->get();
        $openCredits = 0;
        foreach ($users as $user) {
            $credits = 0;
            $usedCredits = 0;
            $push = $user->toArray();
            if (count($push['push']) > 0) {
                $credits = $push['push'][0]['credits'];
            }
//            $deliveries = $user->deliveries;
//            foreach ($deliveries as $delivery) {
//                $attributes = json_decode($delivery->details, true);
//                if ($attributes) {
//                    if (array_key_exists("pickup", $attributes)) {
//                        if ($attributes["deliver"] == "envase") {
//                            $usedCredits = 1;
//                        }
//                    }
//                    if (array_key_exists("deliver", $attributes)) {
//                        if ($attributes["deliver"] == "deposit") {
//                            $credits = 1;
//                        }
//                    }
//                }
//            }


            $openCredits += ($credits - $usedCredits);
        }
        return $openCredits;
    }

    protected function checkOrderCreditsInternal(User $user, Order $order, array $data) {
        $items = $order->items;
        $requiredCredits = 0;
        $requiredDeposit = 0;
        $creditHolders = 0;
        $requiredBuyers = 0;
        $requiresShipping = 0;
        $requiresDelivery = 0;
        $splitTotal = $order->total;
        $totalDeposit = 0;
        $totalCredit = 0;
        $creditItem = null;
        $creditItemMerchant = "";
        foreach ($items as $value) {
            $attributes = json_decode($value->attributes, true);
            if (array_key_exists("requires_credits", $attributes)) {
                if ($attributes['requires_credits']) {
                    if (!$creditItem) {
                        $creditItem = ProductVariant::where("type", "deposit")->where("merchant_id", $value->merchant_id)->first();
                    }
                    if ($attributes['type'] == "meal-plan") {
                        $requiredDeposit += ($creditItem->price * $attributes['credits']);
                    }

                    $requiredCredits += $attributes['credits'];
                }
            }
            if (array_key_exists("multiple_buyers", $attributes)) {
                if ($attributes['multiple_buyers']) {
                    $requiredBuyers += $attributes['buyers'];
                }
            }
            if (array_key_exists("requires_delivery", $attributes)) {
                if ($attributes['requires_delivery']) {
                    $requiresDelivery = 1;
                }
            }
            if (array_key_exists("is_credit", $attributes)) {
                if ($attributes['is_credit']) {
                    if ($creditItemMerchant == 1299) {
                        $requiredCredits -= $value->quantity;
                        $splitTotal -= ($value->price * $value->quantity);
                        $requiredDeposit -= ($value->price * $value->quantity);
                        $totalDeposit += ($value->price * $value->quantity);
                        $totalCredit++;
                    } else {
                        $totalCredit++;
                    }
                }
            }
            $creditItemMerchant = $value->merchant_id;
            $order->merchant_id = $creditItemMerchant;
            if (array_key_exists("is_shippable", $attributes)) {
                if ($attributes['is_shippable']) {
                    $requiresShipping++;
                }
            }
        }
        if (array_key_exists("payers", $data)) {
            if (count($data['payers']) > 0) {
                if ($creditItemMerchant == 1299) {
                    $totalBuyingDeposit = count($data['payers']) + 1 - $totalCredit;
                } else {
                    $totalBuyingDeposit = count($data['payers']) + 1;
                }
                $payerSplitNotIncludingDeposit = $splitTotal / (count($data['payers']) + 1);
                $payerSplitIncludingDeposit = 0;
                $payertransactionCostNoDeposit = $this->getTransactionTotal($payerSplitNotIncludingDeposit);
                $payertransactionCostDeposit = 0;
                if ($creditItemMerchant == 1299) {
                    if ($totalCredit > 0) {
                        if (!$creditItem) {
                            $creditItem = ProductVariant::where("type", "deposit")->where("merchant_id", $creditItemMerchant)->first();
                        }
                        $payerSplitIncludingDeposit = $payerSplitNotIncludingDeposit + $creditItem->price;
                    }
                }
            }
        }
        if ($requiredCredits > 0) {
            if (array_key_exists("payers", $data)) {
                $checkCredits = $data['payers'];
            } else {
                $checkCredits = [];
            }
            if ($creditItemMerchant == 1299) {
                array_push($checkCredits, $user->id);
                $creditHolders = $this->checkUsersCredits($checkCredits, $data['platform']);
                $requiredCredits -= $creditHolders;
                $requiredDeposit -= ($creditItem->price * $creditHolders);
            } else if ($creditItemMerchant == 1301) {
                $requiredCredits = 1;
                if ($totalCredit > 0) {
                    $requiredCredits = 0;
                }
                $requiredDeposit = 0;
            }
        }
        if ($requiresDelivery > 0) {
            if (array_key_exists("delivery_date", $data)) {
                if ($data['delivery_date']) {
                    $requiresDelivery = 0;
                }
            }
        }
        if ($requiredBuyers > 0) {
            if (array_key_exists("payers", $data)) {
                $checkBuyers = $data['payers'];
            } else {
                $checkBuyers = [];
            }
            array_push($checkBuyers, $user->id);
            $requiredBuyers -= count($checkBuyers);
        }
        if ($requiresShipping == 0) {
            $order->orderConditions()->where('type', "shipping")->delete();
            Cart::removeConditionsByType("shipping");
        } else {
            $shippingCondition = $order->orderConditions()->where('type', "shipping")->first();
            if ($shippingCondition) {
                $requiresShipping = 0;
            }
        }
        return array(
            "split" => $splitTotal,
            "creditHolders" => $creditHolders,
            "creditItem" => $creditItem,
            "creditItemMerchant" => $creditItemMerchant,
            "requiredCredits" => $requiredCredits,
            "requiredBuyers" => $requiredBuyers,
            "totalDeposit" => $totalDeposit,
            "requiresShipping" => $requiresShipping,
            "requiresDelivery" => $requiresDelivery,
            "order" => $order,
        );
    }

    public function processModel(array $data) {
        $class = "App\\Models\\" . $data["model"];
        $model = $class::find($data['id']);
        $interval = $data['interval'];
        $interval_type = $data['interval_type'];
        if ($model->isActive()) {
            $date = $model->ends_at;
        } else {
            $date = date("Y-m-d");
        }

        //increment 
        $mod_date = strtotime($date . "+ " . $interval . " " . $interval_type);
        $newdate = date("Y-m-d", $mod_date);
        $model->ends_at = $newdate;
        $model->save();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function setShippingAddress(User $user, $data) {
        $address = $data['address_id'];
        $theAddress = Address::find(intval($address));
        if ($theAddress) {
            if ($theAddress->user_id == $user->id) {
                $order = $this->getOrder($user);
                $result = $this->geolocation->checkMerchantPolygons($theAddress->lat, $theAddress->long, $data['merchant_id'] ,null);
                if ($result["status"] == "success") {
                    $orderAddresses = $theAddress->toarray();
                    unset($orderAddresses['id']);
                    unset($orderAddresses['is_default']);
                    $orderAddresses['order_id'] = $order->id;
                    $orderAddresses['type'] = "shipping";
                    Payment::where("order_id", $order->id)->update(['address_id' => null]);
                    $order->orderAddresses()->where('type', "shipping")->delete();
                    $attributes = json_decode($order->attributes, true);
                    $polygon = $result['polygon'];
                    $attributes['polygon'] = $polygon->id;
                    $attributes['origin'] = $polygon->address_id;
                    $order->attributes = json_encode($attributes);
                    $order->merchant_id = $data['merchant_id'];
                    $order->save();
                    OrderAddress::insert($orderAddresses);
                    return array("status" => "success", "message" => "Address added to order", "order" => $order);
                }
                return $result;
            }
            return array("status" => "error", "message" => "Address does not belong to user");
        }
        return array("status" => "error", "message" => "Address does not exist");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function setBillingAddress(User $user, $address) {
        $order = $this->getOrder($user);
        $address = $address['address_id'];
        $theAddress = Address::find(intval($address));
        if ($theAddress) {
            if ($theAddress->user_id == $user->id) {
                $order = $this->getOrder($user);
                $orderAddresses = $theAddress->toarray();
                $orderAddresses['address_id'] = $theAddress->id;
                unset($orderAddresses['id']);
                $orderAddresses['order_id'] = $order->id;
                $orderAddresses['type'] = "billing";
                $data = [];
                if ($theAddress->country_id) {
                    $data["country_id"] = $theAddress->country_id;
                }
                if ($theAddress->region_id) {
                    $data["region_id"] = $theAddress->region_id;
                }
                $this->setTaxesCondition($user, $data);
                $order->orderAddresses()->where('type', "billing")->delete();
                OrderAddress::insert($orderAddresses);
                return array("status" => "success", "message" => "Billing Address added to order", "order" => $order);
            }
            return array("status" => "error", "message" => "Address does not belong to user");
        }
        return array("status" => "error", "message" => "Address does not exist");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function setShippingCondition(User $user, $condition) {
        $condition = $condition['condition_id'];
        $theCondition = Condition::find(intval($condition));
        if ($theCondition) {
            Cart::removeConditionsByType("shipping");
            $order = $this->getOrder($user);
            // add single condition on a cart bases
            $condition = new CartCondition(array(
                'name' => $theCondition->name,
                'type' => "shipping",
                'target' => $theCondition->target,
                'value' => $theCondition->value,
                'order' => $theCondition->order
            ));
            $insertCondition = $theCondition->toArray();
            unset($insertCondition['id']);
            $insertCondition['order_id'] = $order->id;
            $order->orderConditions()->where('type', "shipping")->delete();
            OrderCondition::insert($insertCondition);
            Cart::session($user->id)->condition($condition);
            return array("status" => "success", "message" => "Shipping condition set on the cart", "order" => $order);
        }
        return array("status" => "error", "message" => "Address does not exist");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function setPlatformShippingCondition(User $user, $order_id, $platform) {


        $order = Order::find($order_id);
        if ($order) {
            if ($order->user_id == $user->id) {
                // add single condition on a cart bases
                $destination = $order->orderAddresses()->where('type', "shipping")->first();
                $attributes = json_decode($order->attributes, true);
                if (array_key_exists("origin", $attributes)) {
                    $origin = Address::find($attributes['origin']);
                    if ($origin) {
                        $className = "App\\Services\\" . $platform;
                        $gateway = new $className;
                        $result = $gateway->getOrderShippingPrice($origin->toArray(), $destination->toArray());
                        if ($result['status'] == 'success') {
                            $insertCondition = array(
                                'name' => "Servicio de transporte",
                                'type' => "shipping",
                                'target' => 'total',
                                'value' => $result['price'],
                                'order' => 0
                            );
                            $condition = new CartCondition($insertCondition);
                            $insertCondition['order_id'] = $order->id;
                            $insertCondition['total'] = $result['price'];
                            $insertCondition['attributes'] = json_encode(["platform" => $platform]);
                            $order->orderConditions()->where('type', "shipping")->delete();
                            Cart::removeConditionsByType("shipping");
                            OrderCondition::insert($insertCondition);
                            Cart::session($user->id)->condition($condition);
                            $cart = $this->editCart->getCheckoutCart($user);
                            $order->subtotal = $cart["subtotal"];
                            $order->tax = $cart["tax"];
                            $order->shipping = $cart["shipping"];
                            $order->discount = $cart["discount"];
                            $order->total = $cart["total"];
                            $order->save();
                            return array("status" => "success", "message" => "Shipping condition set on the cart", "order" => $order, "cart" => $cart);
                        }
                        return array("status" => "error", "message" => "Shipping provider does not have coverage");
                    }
                    return array("status" => "error", "message" => "Order has no origin" );
                }
                return array("status" => "error", "message" => "Order does not have an origin address");
            }
            return array("status" => "error", "message" => "Order not users");
        }
        return array("status" => "error", "message" => "Order not found");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function setTaxesCondition(User $user, array $data) {
        $order = $this->getOrder($user);
        Cart::session($user->id)->removeConditionsByType("tax");
        $order->orderConditions()->where('type', "tax")->delete();


        if (array_key_exists("country_id", $data)) {
            if ($data['country_id']) {
                $conditions = Condition::where('country_id', $data['country_id'])
                                ->where('type', 'tax')->whereNull('region_id')->get();
                $this->setOrderConditions($user, $order, $conditions, "tax");
            }
        }
        if (array_key_exists("region_id", $data)) {
            if ($data['region_id']) {
                $conditions = Condition::where('region_id', $data['region_id'])
                                ->where('type', 'tax')->get();
                $this->setOrderConditions($user, $order, $conditions, "tax");
            }
        }
        return array("status" => "success", "message" => "Tax conditions set on the cart", "cart" => $this->editCart->getCheckoutCart($user));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function loadOrderConditions(User $user, Order $order) {
        $conditions = $order->orderConditions()->get();
        $applyConditions = array();
        foreach ($conditions as $condition) {
            $itemCondition = new CartCondition(array(
                'name' => $condition->name,
                'type' => $condition->type,
                'target' => $condition->target,
                'value' => $condition->value,
            ));
            Cart::session($user->id)->condition($itemCondition);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function paymentStatusUpdate(Payment $payment, Order $order) {
        $user = $payment->user;
        $followers = [];
        array_push($followers, $user);
        $payload = [
            "order_id" => $order->id,
            "first_name" => $user->firstName,
            "last_name" => $user->lastName,
            "payment_id" => $payment->id,
            "payment_total" => $payment->total,
            "payment_status" => $payment->status,
            "order_total" => $order->total,
            "order_status" => $order->status
        ];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => self::OBJECT_ORDER,
            "sign" => true,
            "payload" => $payload,
            "type" => self::PAYMENT_STATUS,
            "user_status" => $user->getUserNotifStatus()
        ];
        $date = date("Y-m-d H:i:s");
        $this->notifications->sendMassMessage($data, $followers, $user, true, $date, true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function OrderStatusUpdate(Order $order) {
        $followers = [];
        $payments = $order->payments()->with('user')->get();
        $merchant = $order->merchant;
        if($merchant){
            $admins = $merchant->users;
        } else {
            $admins = User::whereIn("id",[2,77])->get();
        }
        $sendEmail = true;
        if ($order->status == "approved") {
            $shipping = $order->orderAddresses()->where("type", "shipping")->first();
            $sendEmail = false;
        }
        foreach ($payments as $item) {
            $followers = [];
            $user = $item->user;
            if ($order->status == "approved") {
                Mail::to($user)->send(new OrderApproved($order, $user, $shipping, "no"));
                Mail::to($admins)->send(new OrderApproved($order, $user, $shipping, "yes"));
                unset($order->payment);
                unset($order->totalCost);
                unset($order->totalPlatform);
                unset($order->totalDeposit);
            }
            array_push($followers, $user);
            $payload = [
                "order_id" => $order->id,
                "payment_id" => $item->id,
                "payment_total" => $item->total,
                "payment_status" => $item->status,
                "order_total" => $order->total,
                "order_status" => $order->status
            ];
            $data = [
                "trigger_id" => $order->id,
                "message" => "",
                "subject" => "",
                "object" => self::OBJECT_ORDER,
                "sign" => true,
                "payload" => $payload,
                "type" => self::ORDER_STATUS,
                "user_status" => "normal"
            ];
            $date = date("Y-m-d H:i:s");
            $this->notifications->sendMassMessage($data, $followers, null, true, $date, $sendEmail);
        }
    }

    public function approvePayment(Payment $payment, $platform) {
        $payment->status = "approved";
        $order = $payment->order;
        $payment->save();

        if ($order) {
            $payments = $order->payments()->where("status", "<>", "approved")->where("id", "<>", $payment->id)->count();
            if ($payments > 0) {
                $order->status = "Pending-" . $payments;
                $order->save();
                $this->paymentStatusUpdate($payment, $order);
                return array("status" => "success", "message" => "Payment approved, still payments pending");
            } else {
                $order->status = "approved";
                $this->orderStatusUpdate($order);
                $updateData = [
                    "paid_status" => "paid",
                    "updated_at" => date("Y-m-d hh:m:s")
                ];
                Item::where("order_id", $order->id)->update($updateData);
                $className = "App\\Services\\EditOrder" . $platform;
                $platFormService = new $className(); //// <--- this thing will be autoloaded
                return $platFormService->approveOrder($order);
            }
        }
    }

    public function denyPayment(Payment $payment, $platform) {
        $payment->status = "denied";
        $order = $payment->order;
        $payment->save();
        $this->paymentStatusUpdate($payment, $order);
        $className = "App\\Services\\EditOrder" . ucfirst($platform);
        $platFormService = new $className; //// <--- this thing will be autoloaded
        return $platFormService->denyPayment($payment);
    }

    public function pendingPayment(Payment $payment, $platform) {
        $payment->status = "pending";
        $payment->save();
        $className = "App\\Services\\EditOrder" . ucfirst($platform);
        $platFormService = new $className; //// <--- this thing will be autoloaded
        return $platFormService->pendingPayment($payment);
    }

    public function submitOrder(Order $order) {
        
    }

    public function validatePrevOrders($user, $theCondition) {
        $sql = "select * from orders o join order_conditions oc on oc.order_id = o.id where o.status = 'approved' and oc.condition_id = $theCondition->id and o.user_id = $user->id;";
        $orders = DB::select($sql);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function setCouponCondition(User $user, $data) {
        $theCondition = Condition::where('coupon', $data['coupon'])->where('status', 'active')->first();

        if ($theCondition) {
            // add single condition on a cart bases
            if ($theCondition->isReusable || (!$theCondition->isReusable && $theCondition->used < 1)) {
                $order = $this->getOrder($user);
                $type = "";
                $target = "";
                $value = "";
                $result = $this->checkCoupon($user, $order, $theCondition);
                if ($result['status'] == "error") {
                    return $result;
                } else {
                    $value = $result['value'];
                    $type = $result['type'];
                    $target = $result['target'];
                }
                $theCondition->used = $theCondition->used + 1;
                $theCondition->save();

                Cart::session($user->id)->removeConditionsByType($type);
                $order->orderConditions()->where('type', $type)->delete();

                $insertCondition = array(
                    'name' => $theCondition->name,
                    'condition_id' => $theCondition->id,
                    'type' => $type,
                    'target' => $target,
                    'value' => $value,
                    'order' => $theCondition->order,
                );


                $condition = new CartCondition($insertCondition);
                Cart::session($user->id)->condition($condition);
                $insertCondition['order_id'] = $order->id;
                $insertCondition['condition_id'] = $theCondition->id;
                $insertCondition['total'] = $condition->getCalculatedValue(Cart::session($user->id)->getSubTotal());
                OrderCondition::insert($insertCondition);
                return array("status" => "success", "message" => "Cart condition set on the cart", "cart" => $this->editCart->getCheckoutCart($user));
            }
        }
        return array("status" => "error", "message" => "Coupon does not exist");
    }

    private function checkCoupon($user, $order, $theCondition) {
        $type = "coupon";
        $target = "total";
        $value = $theCondition->value;
        $attributes = json_decode($theCondition->attributes, true);

        //Coupon used once per order
        $theConditionApplied = $order->orderConditions()->where('condition_id', $theCondition->id)->first();
        if ($theConditionApplied) {
            return array("status" => "error", "message" => "Cart condition already exists in order");
        }
        //Product specific coupon
        if ($theCondition->target == "product_variant_id" || $theCondition->target == "product_id") {
            $item = $order->items()->whereIn($theCondition->target, $attributes["targets"])->first();
            if (!$item) {
                return array("status" => "error", "message" => "Product missing");
            }
            if (array_key_exists("minquantity", $attributes)) {
                if ($item->quantity < $attributes["minquantity"]) {
                    return array("status" => "error", "message" => "Coupon quantity");
                }
            }
            if ($item->quantity > 22) {
                return array("status" => "error", "message" => "Coupon quantity");
            }
        }

        //User limit
        if (array_key_exists("user_limit", $attributes)) {
            $sql = "select DISTINCT(o.id) from orders o join order_conditions oc on oc.order_id = o.id where o.status = 'approved' and oc.condition_id = $theCondition->id and o.user_id = $order->user_id;";
            $orders = DB::select($sql);
            if (count($orders) > $attributes["user_limit"]) {
                return array("status" => "error", "message" => "Coupon quantity");
            }
        }

        //Address limit
        if (array_key_exists("address", $attributes)) {
            $address = $order->orderAddresses()->first();
            $requiredAddress = $attributes["address"];
            if ($requiredAddress["city_id"] != $address->city_id || $requiredAddress["country_id"] != $address->country_id || trim($requiredAddress["address"]) != trim($address->address)) {
                return array("status" => "error", "message" => "Address Error");
            }
        }

        //User Id
        if (array_key_exists("user_id", $attributes)) {
            if ($attributes["user_id"] != $user->id) {
                return array("status" => "error", "message" => "User Error");
            }
        }

        if (array_key_exists("per_person", $attributes)) {
            if ($attributes["per_person"] > 0) {
                $attributes2 = json_decode($item->attributes, true);
                $value = "" . ((int) $theCondition->value * (int) $attributes2["credits"]);
            }
        }
        return array("status" => "success", "value" => $value, "type" => $type, "target" => $target);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    private function setOrderConditions(User $user, Order $order, $conditions, $type) {

        foreach ($conditions as $value) {
            $insertCondition = array(
                'name' => $value->name,
                'type' => $type,
                'target' => $value->target,
                'value' => $value->value,
                'order' => $value->order
            );
            $condition = new CartCondition($insertCondition);
            $insertCondition['order_id'] = $order->id;
            $insertCondition['condition_id'] = $value->id;
            OrderCondition::insert($insertCondition);
            Cart::session($user->id)->condition($condition);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    private function setOrderAttributes(User $user, $orderId, $attributes) {
        $order = Order::find($orderId);
        if ($order) {
            if ($order->user_id == $user->id) {
                $order->attributes = json_encode($attributes);
                $order->save();
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getShippingConditions(User $user, $address_id) {
        $address = Address::find(intval($address_id));
        if ($address) {
            $conditions = Condition::where('type', 'shipping')
                    ->where('isActive', true)
                    ->where(function ($query) use ($address) {
                        $query->where('city_id', $address->city_id)
                        ->orWhere('region_id', $address->region_id)
                        ->orWhere('country_id', $address->country_id);
                    })
                    ->get();
            if ($conditions) {
                return $conditions;
            }
            return array("status" => "error", "message" => "No shipping conditions for that address");
        }
        return array("status" => "error", "message" => "Address not found");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function updateInventory(Order $order) {
        foreach ($order->items as $item) {
            //$product = Product::find($item->product_id)->first();
            $productVariant = $item->productVariant;
            if ($productVariant->quantity > -1) {
                $productVariant->quantity -= $item->quantity;
                if ($productVariant->quantity < 1) {
                    $productVariant->quantity = 0;
                }
                $productVariant->save();
            }
        }
    }

    /**
     * Test Email
     *
     * @return Response
     */
    public function emailSales(User $user, Order $order) {
        /* Mail::send('emails.order', ['user' => $user, "order" => $order], function($message) {
          $message->from('noreply@hoovert.com', 'Hoove');
          $message->to('harredon01@gmail.com', 'Hoovert Arredondo')->subject('Exitoo!');
          }); */
    }

    /**
     * Test Email
     *
     * @return Response
     */
    public function emailCustomer(Order $order) {
        $user = $order->user;
        if ($order->status == "accepted") {
            /* Mail::send('emails.order', ['user' => $user, "order" => $order], function($message) {
              $message->from('noreply@hoovert.com', 'Hoove');
              $message->to('harredon01@gmail.com', 'Hoovert Arredondo')->subject('Orden Confirmada');
              }); */
        } else {
            /* Mail::send('emails.order', ['user' => $user, "order" => $order], function($message) {
              $message->from('noreply@hoovert.com', 'Hoove');
              $message->to('harredon01@gmail.com', 'Hoovert Arredondo')->subject('Orden Rechazada');
              }); */
        }
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorUpdate(array $data) {
        return Validator::make($data, [
                    'item_id' => 'required|max:255',
                    'quantity' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorOrderRecurring(array $data) {
        return Validator::make($data, [
                    'recurring' => 'required|max:255',
                    'recurring_type' => 'required|max:255',
                    'recurring_value' => 'required|max:255',
        ]);
    }

}
