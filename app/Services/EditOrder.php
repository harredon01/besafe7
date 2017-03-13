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
use App\Models\PaymentMethod;
use App\Models\Address;
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

    public function getCart() {
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
        $result['items'] = $data;
        $result['totalItems'] = count($data);
        return $result;
    }

    public function getCheckoutCart() {
        $data = array();
        $items = Cart::getContent();
        $result = array();
        $is_shippable = false;
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
            $attrs = json_decode($item->attributes, true);
            if ($attrs) {
                if ($attrs['is_shippable'] == true) {
                    $is_shippable = true;
                }
            }
            // Note that attribute returns ItemAttributeCollection object that extends the native laravel collection
            // so you can do things like below:
            array_push($result, $dataitem);
        }
        $subTotal = Cart::getSubTotal();
        $shippingItems = Cart::getConditionsByType("shipping");
        $shippingTotal = 0;
        foreach ($shippingItems as $item) {
            $shippingTotal += $item->getCalculatedValue($subTotal);
        }
        $taxItems = Cart::getConditionsByType("tax");
        $taxTotal = 0;
        foreach ($taxItems as $item) {
            $taxTotal += $item->getCalculatedValue($subTotal);
        }
        $saleItems = Cart::getConditionsByType("sale");
        $saleTotal = 0;
        foreach ($saleItems as $item) {
            $saleTotal += $item->getCalculatedValue($subTotal);
        }
        $couponItems = Cart::getConditionsByType("coupon");
        $couponTotal = 0;
        foreach ($couponItems as $item) {
            $couponTotal += $item->getCalculatedValue($subTotal);
        }
        $total = Cart::getTotal();
        $data['items'] = $result;
        $data['subtotal'] = $subTotal;
        $data['shipping'] = $shippingTotal;
        $data['is_shippable'] = $is_shippable;
        if ($taxTotal == 0) {
            $temptotal = $subTotal / 1.16;
            $taxTotal = $subTotal - $temptotal;
            $subTotal = $temptotal;
        }
        $data['tax'] = $taxTotal;
        $data['sale'] = $saleTotal;
        $data['coupon'] = $couponTotal;
        $data['discount'] = $saleTotal + $couponTotal;
        $data['totalItems'] = count($result);
        $data['total'] = $total;
        return $data;
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
        $data = $this->getCheckoutCart();
        //$order->status = "holding";
        $order->subtotal = $data["subtotal"];
        $order->tax = $data["tax"];
        $order->shipping = $data["shipping"];
        $order->discount = $data["discount"];
        $order->total = $data["total"];
        $order->save();
        return $order;
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
            $order->conditions()->where('type', "shipping")->delete();
            $order->conditions()->save($theCondition);
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
    public function setCouponCondition(User $user, $coupon) {
        $theCondition = Condition::where('coupon', $coupon);
        if ($theCondition) {
            // add single condition on a cart bases
            if ($theCondition->isReusable || (!$theCondition->isReusable && !$theCondition->used)) {
                $condition = new CartCondition(array(
                    'name' => $theCondition->name,
                    'type' => $theCondition->type,
                    'target' => $theCondition->target,
                    'value' => $theCondition->value,
                    'order' => $theCondition->order
                ));
                $order = $this->getOrder($user);
                Cart::condition($condition);
                $order->conditions()->save($theCondition);
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
    public function setOrderDetails(User $user, array $data) {
        $order = $this->getCart($user);

        $paymentMethod = PaymentMethod::find(intval($data["payment_method_id"]));
        $test["payment_method_id"] = $data["payment_method_id"];
        $test["payment"] = $paymentMethod;
        $test["order"] = $order;
        /* if($order->id==3){
          dd($test);
          } */
        if ($paymentMethod) {
            if (array_key_exists("comments", $data)) {
                $order->comments = $data['comments'];
            }
            if (array_key_exists("cash_for_change", $data)) {
                $order->cash_for_change = $data['cash_for_change'];
            }
            $order->payment_method_id = $paymentMethod->id;
            $order->status = "holding";
            return array("status" => "success", "message" => "Email sent to merchant");
        }
        return array("status" => "error", "message" => "Invalid Payment Method");
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
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function clearCart(User $user) {
        Cart::clear();
        Item::where('user_id', $user->id)->where('order_id', null)->delete();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function clearCartSession() {
        Cart::clear();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function loadActiveCart(User $user) {
        $items = Item::where('user_id', $user->id)->whereNull('order_id')->get();
        foreach ($items as $item) {
            $productVariant = $item->productVariant;
            $product = $productVariant->product;
            $conditions = $productVariant->conditions()->where('isActive', true)->get();
            $applyConditions = array();
            foreach ($conditions as $condition) {
                $itemCondition = new CartCondition(array(
                    'name' => $condition->name,
                    'type' => $condition->type,
                    'target' => $condition->target,
                    'value' => $condition->value,
                ));
                array_push($applyConditions, $itemCondition);
            }
            $conditions = $product->conditions()->where('isActive', true)->get();
            foreach ($conditions as $condition) {
                $itemCondition = new CartCondition(array(
                    'name' => $condition->name,
                    'type' => $condition->type,
                    'target' => $condition->target,
                    'value' => $condition->value,
                ));
                array_push($applyConditions, $itemCondition);
            }
            $attrs = json_decode($item->attributes, true);
            Cart::add(array(
                'id' => $productVariant->id,
                'name' => $product->name,
                'price' => $productVariant->price,
                'quantity' => $item->quantity,
                'attributes' => $attrs,
                'conditions' => $applyConditions
            ));
        }
        return array("status" => "success", "message" => "Cart Loaded");
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  User, array  $data
     * 
     */
    public function addCartItem(User $user, array $data) {

        if ((int) $data['quantity'] <= 0) {
            return array("status" => "error", "message" => "amount must be a positive integer");
        }
        $item = Item::where('product_variant_id', $data['product_variant_id'])
                        ->where('user_id', $user->id)
                        ->whereNull('order_id')->first();


        if ($item) {
            $quantity = $item->quantity + (int) $data['quantity'];
            $productVariant = $item->productVariant;
            if ($productVariant->quantity >= $quantity) {
                Cart::update($productVariant->id, array(
                    'quantity' => (int) $data['quantity'], // so if the current product has a quantity of 4, another 2 will be added so this will result to 6
                ));
                $item->quantity = $quantity;
                $item->save();
                return array("status" => "success", "message" => "item added to cart successfully");
            } else {
                return array("status" => "error", "message" => "No more stock of that product");
            }
        } else {
            $productVariant = ProductVariant::find(intval($data['product_variant_id']));
            if ($productVariant) {
                if ((int) $productVariant->quantity >= (int) $data['quantity']) {
                    $conditions = $productVariant->conditions()->where('isActive', true)->get();
                    $applyConditions = array();
                    foreach ($conditions as $condition) {
                        $itemCondition = new CartCondition(array(
                            'name' => $condition->name,
                            'type' => $condition->type,
                            'target' => $condition->target,
                            'value' => $condition->value,
                        ));
                        array_push($applyConditions, $itemCondition);
                    }
                    $product = $productVariant->product;
                    $conditions = $product->conditions()->where('isActive', true)->get();
                    foreach ($conditions as $condition) {
                        $itemCondition = new CartCondition(array(
                            'name' => $condition->name,
                            'type' => $condition->type,
                            'target' => $condition->target,
                            'value' => $condition->value,
                        ));
                        array_push($applyConditions, $itemCondition);
                    }
                    $losAttributes = json_decode($productVariant->attributes, true);
                    if (!$losAttributes) {
                        $losAttributes = array();
                    }
                    $losAttributes['is_digital'] = $productVariant->is_digital;
                    $losAttributes['is_shippable'] = $productVariant->is_shippable;
                    Cart::add(array(
                        'id' => $productVariant->id,
                        'name' => $product->name,
                        'price' => $productVariant->price,
                        'quantity' => (int) $data['quantity'],
                        'attributes' => $losAttributes,
                        'conditions' => $applyConditions
                    ));
                    $item = Item::create([
                                'product_variant_id' => $productVariant->id,
                                'name' => $product->name,
                                'user_id' => $user->id,
                                'price' => $productVariant->price,
                                'quantity' => (int) $data['quantity'],
                                'attributes' => json_encode($losAttributes)
                    ]);
                    return array("status" => "success", "message" => "item added to cart successfully");
                } else {
                    return array("status" => "error", "message" => "No more stock of that product");
                }
            }
            return array("status" => "error", "message" => "product does not exist");
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  User, array  $data
     * 
     */
    public function updateCartItem(User $user, array $data) {
        $item = Item::where('product_variant_id', intval($data['product_variant_id']))
                        ->where('user_id', $user->id)
                        ->where('order_id', null)->first();
        if ($item) {
            $productVariant = $item->productVariant;
            if ((int) $data['quantity'] > 0) {
                if ($productVariant->quantity >= ((int) $data['quantity'] )) {
                    $item->quantity = (int) $data['quantity'];
                    $item->save();
                    Cart::update($productVariant->id, array(
                        'quantity' => array(
                            'relative' => false,
                            'value' => $item->quantity
                        ),
                    ));
                    return array("status" => "success", "message" => "item updated successfully");
                } else {
                    return array("status" => "error", "message" => "No more stock of that product");
                }
            } else if ((int) $data['quantity'] == 0) {
                Cart::remove($productVariant->id);
                $item->delete();
                return array("status" => "success", "message" => "Item deleted from cart");
            } else if ((int) $data['quantity'] < 0) {
                return array("status" => "error", "message" => "amount must be positive");
            }
        } else {
            return array("status" => "error", "message" => "item does not exist on the cart");
        }
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorAddCart(array $data) {
        return Validator::make($data, [
                    'product_id' => 'required|max:255',
                    'quantity' => 'required|max:255',
        ]);
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
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditGroupMessage() {
        return 'There was a problem editing your group';
    }

}
