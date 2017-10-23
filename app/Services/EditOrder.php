<?php

namespace App\Services;

use Validator;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\OrderAddress;
use App\Models\OrderPayment;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use App\Models\ProductVariant;
use App\Models\Address;
use App\Services\EditCart;
use App\Services\PayU;
use App\Services\Stripe;
use Mail;
use Darryldecode\Cart\CartCondition;
use Cart;
use DB;

class EditOrder {

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
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(PayU $payU, Stripe $stripe, EditCart $editCart) {
        $this->payU = $payU;
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

    public function prepareOrder(User $user) {
        $order = $this->getOrder($user);
        $data = $this->editCart->getCheckoutCart($order);
        //$this->addItemsOrder($user, $order);

        $order->is_shippable = $data['is_shippable'];
        $order->is_subscription = $data['is_subscription'];
        $order->requires_authorization = $data['requires_authorization'];
        $order->subtotal = $data["subtotal"];
        $order->tax = $data["tax"];
        $order->shipping = $data["shipping"];
        $order->discount = $data["discount"];
        $order->total = $data["total"];
        $order->save();
        /* $className = "App\\Services\\" . $payment;
          $gateway = new $className; //// <--- this thing will be autoloaded */
        return $order;
    }

    public function processModel(User $user, array $data) {
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
    public function payOrder(User $user, array $data) {
        $order = $this->getOrder($user);
        Item::where('user_id', $user->id)
                ->update(['order_id' => $order->id]);
        $order->status = "holding";
            if ($data['use_default']) {
                
            }
        
        
        $order->save();
        return array("status" => "error", "message" => "Empty Cart");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function setShippingAddress(User $user, $address) {

        $theAddress = Address::find(intval($address));
        if ($theAddress) {
            if ($theAddress->user_id == $user->id) {
                $order = $this->getOrder($user);
                $orderAddresses = $theAddress->toarray();
                $orderAddresses['address_id'] = $theAddress->id;
                unset($orderAddresses['id']);
                $orderAddresses['order_id'] = $order->id;
                $orderAddresses['type'] = "shipping";
                $order->orderAddresses()->where('type', "shipping")->delete();
                OrderAddress::insert($orderAddresses);
                return array("status" => "success", "message" => "Address added to order");
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
        $theAddress = Address::find(intval($address));
        if ($theAddress) {
            if ($theAddress->user_id == $user->id) {
                $order = $this->getOrder($user);
                $orderAddresses = $theAddress->toarray();
                $orderAddresses['address_id'] = $theAddress->id;
                unset($orderAddresses['id']);
                $orderAddresses['order_id'] = $order->id;
                $orderAddresses['type'] = "billing";
                $order->orderAddresses()->where('type', "billing")->delete();
                OrderAddress::insert($orderAddresses);
                return array("status" => "success", "message" => "Billing Address added to order");
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
            $order->conditions()->where('type', "shipping")->delete();
            Condition::insert($insertCondition);
            Cart::condition($condition);
            return array("status" => "success", "message" => "Shipping condition set on the cart");
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
        Cart::removeConditionsByType("tax");
        if (array_key_exists("country_id", $data)) {
            if ($data['country_id']) {
                $conditions = Condition::where('country_id', $data['country_id'])
                                ->where('type', 'tax')->get();
                foreach ($conditions as $value) {
                    // add single condition on a cart bases
                    $condition = new CartCondition(array(
                        'name' => $value->name,
                        'type' => "tax",
                        'target' => $value->target,
                        'value' => $value->value,
                        'order' => $value->order
                    ));
                    $insertCondition = $value->toArray();
                    unset($insertCondition['id']);
                    $insertCondition['order_id'] = $order->id;
                    $order->conditions()->where('type', "tax")->delete();
                    Condition::insert($insertCondition);
                    Cart::condition($condition);
                }
            }
        }
        if (array_key_exists("region_id", $data)) {
            if ($data['region_id']) {
                $conditions = Condition::where('region_id', $data['region_id'])
                                ->where('type', 'tax')->get();
                foreach ($conditions as $value) {
                    // add single condition on a cart bases
                    $condition = new CartCondition(array(
                        'name' => $value->name,
                        'type' => "tax",
                        'target' => $value->target,
                        'value' => $value->value,
                        'order' => $value->order
                    ));
                    $insertCondition = $value->toArray();
                    unset($insertCondition['id']);
                    $insertCondition['order_id'] = $order->id;
                    $order->conditions()->where('type', "tax")->delete();
                    Condition::insert($insertCondition);
                    Cart::condition($condition);
                }
            }
        }
        return array("status" => "success", "message" => "Tax conditions set on the cart");
    }

    public function approveOrder(Order $order) {
        $items = Cart::getContent();
        $data = array();
        $result = array();
        foreach ($items as $item) {
            $dataitem = array();
            $dataitem['id'] = $item->id; // the Id of the item
            $dataitem['name'] = $item->name; // the name
            $dataitem['price'] = $item->price; // the single price without conditions applied
            $dataitem['priceSum'] = $item->getPriceSum(); // the subtotal without conditions applied
            $dataitem['priceConditions'] = $item->getPriceWithConditions(); // the single price with conditions applied
            $dataitem['priceSumConditions'] = $item->getPriceSumWithConditions(); // the subtotal with conditions applied
            $dataitem['quantity'] = $item->quantity; // the quantity
            $dataitem['attributes'] = $item->attributes; // the attributes
            // Note that attribute returns ItemAttributeCollection object that extends the native laravel collection
            // so you can do things like below:
            array_push($data, $dataitem);
        }
        $items = $order->items();
        foreach ($items as $item) {
            $data = json_decode($item->attributes);
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
            }
            $attrs = json_decode($item->attributes, true);
            if ($attrs) {
                if (array_key_exists("model", $attrs)) {
                    $class = "App\\Models\\" . $attrs["model"];
                    $model = $class::where("id", $attrs['id']);
                } else {
                    
                }
            }
        }
        return array("status" => "error", "message" => "Address does not exist");
    }

    public function denyOrder(Order $order) {
        
    }

    public function submitOrder(Order $order) {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function setCouponCondition(User $user, $coupon) {
        $theCondition = Condition::where('coupon', $coupon)->where('status', 'active')->first();
        Cart::removeConditionsByType("coupon");
        if ($theCondition) {
            // add single condition on a cart bases
            if ($theCondition->isReusable || (!$theCondition->isReusable && !$theCondition->used)) {
                $condition = new CartCondition(array(
                    'name' => $theCondition->name,
                    'type' => 'coupon',
                    'target' => $theCondition->target,
                    'value' => $theCondition->value,
                    'order' => $theCondition->order
                ));
                $order = $this->getOrder($user);
                $insertCondition = $theCondition->toArray();
                unset($insertCondition['id']);
                $insertCondition['order_id'] = $order->id;
                $order->conditions()->where('type', "coupon")->delete();
                Condition::insert($insertCondition);
                Cart::condition($condition);
                return array("status" => "success", "message" => "Shipping condition set on the cart");
            }
        }
        return array("status" => "error", "message" => "Address does not exist");
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
