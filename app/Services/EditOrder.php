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
use App\Services\EditCart;
use App\Services\PayU;
use App\Services\Stripe;
use App\Services\EditAlerts;
use Mail;
use Darryldecode\Cart\CartCondition;
use Cart;
use DB;

class EditOrder {

    const OBJECT_ORDER = 'Order';
    const OBJECT_ORDER_REQUEST = 'OrderRequest';
    const ORDER_PAYMENT = 'order_payment';
    const ORDER_PAYMENT_REQUEST = 'order_payment_request';

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
    protected $editAlerts;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(PayU $payU, Stripe $stripe, EditCart $editCart, EditAlerts $editAlerts) {
        $this->payU = $payU;
        $this->editAlerts = $editAlerts;
        $this->stripe = $stripe;
        $this->editCart = $editCart;
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

    public function addItemsToOrder(User $user, Order $order) {
        Item::where('user_id', $user->id)
                ->whereNull('order_id')
                ->update(['order_id' => $order->id, 'updated_at' => date("Y-m-d H:i:s")]);
        $cartConditions = Cart::session($user->id)->getConditions();
        $resultConditions = [];
        $order->orderConditions()->delete();
        $cart = $this->editCart->getCart($user);
        foreach ($cartConditions as $condition) {
            $cond = array();
            $cond['target'] = $condition->getTarget(); // the target of which the condition was applied
            $cond['name'] = $condition->getName(); // the name of the condition
            $cond['type'] = $condition->getType(); // the type
            $cond['value'] = $condition->getValue(); // the value of the condition
            $cond['order'] = $condition->getOrder(); // the order of the condition
            $cond['attributes'] = json_encode($condition->getAttributes()); // the attributes of the condition, returns an empty [] if no attributes added
            $value = $condition->getCalculatedValue($cart['subtotal']);
            $cond['order_id'] = $order->id; // the name of the condition
            $cond['total'] = $value;
            OrderCondition::insert($cond);
        }
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    public function prepareOrder(User $user, $platform, array $data) {
        if (array_key_exists("order_id", $data)) {
            $order = Order::find($data['order_id']);
            if ($order) {

                $className = "App\\Services\\EditOrder" . ucfirst($platform);
                $platFormService = new $className(); //// <--- this thing will be autoloaded
                if ($platFormService) {
                    $cart = $this->editCart->getCheckoutCart($user);
                    if ($cart['total'] > 0) {
                        $order->subtotal = $cart["subtotal"];
                        $order->tax = 0;
                        $order->shipping = 0;
                        $order->discount = 0;
                        $order->total = $cart["total"];
                        return $platFormService->prepareOrder($user, $order, $data, $cart);
                    } else {
                        return array("status" => "error", "message" => "Empty cart");
                    }
                }
            }
        }
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
    public function setShippingAddress(User $user, $address) {
        $address = $address['address_id'];
        $theAddress = Address::find(intval($address));
        if ($theAddress) {
            if ($theAddress->user_id == $user->id) {
                $order = $this->getOrder($user);
                $orderAddresses = $theAddress->toarray();
                unset($orderAddresses['id']);
                $orderAddresses['order_id'] = $order->id;
                $orderAddresses['type'] = "shipping";
                Payment::where("order_id", $order->id)->update(['address_id' => null]);
                $order->orderAddresses()->where('type', "shipping")->delete();
                OrderAddress::insert($orderAddresses);
                return array("status" => "success", "message" => "Address added to order", "order" => $order);
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

    public function approvePayment(Payment $payment, $platform) {
        $payment->status = "approved";
        $payment->save();
        $className = "App\\Services\\EditOrder" . ucfirst($platform);
        $platFormService = new $className; //// <--- this thing will be autoloaded
        return $platFormService->approvePayment($payment);
    }
    public function denyPayment(Payment $payment, $platform) {
        $className = "App\\Services\\EditOrder" . ucfirst($platform);
        $platFormService = new $className; //// <--- this thing will be autoloaded
        return $platFormService->denyPayment($payment);
    }
    public function pendingPayment(Payment $payment, $platform) {
        $className = "App\\Services\\EditOrder" . ucfirst($platform);
        $platFormService = new $className; //// <--- this thing will be autoloaded
        return $platFormService->pendingPayment($payment);
    }

    public function submitOrder(Order $order) {
        
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
                $theConditionApplied = $order->orderConditions()->where('condition_id', $theCondition->id)->first();
                if ($theConditionApplied) {
                    //return array("status" => "info", "message" => "Cart condition already exists in order", "cart" => $this->editCart->getCart($user));
                }
                Cart::session($user->id)->removeConditionsByType("coupon");
                $order->orderConditions()->where('type', "coupon")->delete();
                $insertCondition = array(
                    'name' => $theCondition->name,
                    'type' => 'coupon',
                    'target' => $theCondition->target,
                    'value' => $theCondition->value,
                    'order' => $theCondition->order,
                );
                $condition = new CartCondition($insertCondition);
                Cart::session($user->id)->condition($condition);
                $theCondition->used = $theCondition->used + 1;
                $theCondition->save();
                $insertCondition['order_id'] = $order->id;
                $insertCondition['condition_id'] = $theCondition->id;
                OrderCondition::insert($insertCondition);


                return array("status" => "success", "message" => "Cart condition set on the cart", "cart" => $this->editCart->getCheckoutCart($user));
            }
        }
        return array("status" => "error", "message" => "Coupon does not exist");
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
    private function splitOrder(User $user, Order $order, $buyers, $platform) {
        if ($order->user_id == $user->id) {
            $totalBuyers = count($buyers) + 1;
            $buyerSubtotal = $order->total / $totalBuyers;
            $buyerTax = $order->tax / $totalBuyers;
            $transactionCost = $buyerSubtotal * (0.0349) + 900;
            $followers = array();
            foreach ($buyers as $buyerItem) {
                $buyer = User::find($buyerItem);
                if ($buyer) {
                    array_push($followers, $buyer);
                    $payment = new Payment;
                    $payment->user_id = $buyer->id;
                    $payment->address_id = $order->address_id;
                    $payment->order_id = $order->id;
                    $payment->status = "pending";
                    $payment->total = $buyerSubtotal + $transactionCost;
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
                "object" => self::OBJECT_ORDER,
                "sign" => true,
                "payload" => $payload,
                "type" => self::ORDER_PAYMENT,
                "user_status" => $user->getUserNotifStatus()
            ];
            $date = date("Y-m-d H:i:s");
            return $this->editAlerts->sendMassMessage($data, $followers, $user, true, $date, $platform);
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

}
