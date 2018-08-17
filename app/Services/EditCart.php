<?php

namespace App\Services;

use Validator;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Item;
use App\Models\Order;
use App\Models\ProductVariant;
use Darryldecode\Cart\CartCondition;
use Cart;

class EditCart {

    /**
     * The Auth implementation.
     *
     */
    protected $auth;

    public function getCart(User $user) {
        $items = Cart::session($user->id)->getContent();
        $data = array();
        $result = array();
        foreach ($items as $item) {
            $item->priceSum = $item->getPriceSum(); // the subtotal without conditions applied
            $item->priceWithConditions = $item->getPriceWithConditions(); // the single price with conditions applied
            $item->priceSumWithConditions = $item->getPriceSumWithConditions(); // the subtotal with conditions applied
            // Note that attribute returns ItemAttributeCollection object that extends the native laravel collection
            // so you can do things like below:
            array_push($data, $item);
        }
        $result['items'] = $data;
        $result['totalItems'] = count($data);
        return $result;
    }

    public function getCheckoutCart(User $user) {
        $data = array();
        $items = Cart::session($user->id)->getContent();
        $result = array();
        $is_shippable = false;
        $is_subscription = false;
        $requires_authorization = false;
        foreach ($items as $item) {
            $dataitem = array();
            $item->priceSum = $item->getPriceSum(); // the subtotal without conditions applied
            $item->priceWithConditions = $item->getPriceWithConditions(); // the single price with conditions applied
            $item->priceSumWithConditions = $item->getPriceSumWithConditions(); // the subtotal with conditions applied
            // Note that attribute returns ItemAttributeCollection object that extends the native laravel collection
            // so you can do things like below:
            array_push($data, $item);
            $attrs = json_decode($item->attributes, true);
            if ($attrs) {
                if (array_key_exists("is_shippable", $attrs)) {
                    if ($attrs['is_shippable'] == true) {
                        $is_shippable = true;
                    }
                }
                if (array_key_exists("is_subscription", $attrs)) {
                    if ($attrs['is_subscription'] == true) {
                        $is_subscription = true;
                    }
                }
                if (array_key_exists("requires_authorization", $attrs)) {
                    if ($attrs['requires_authorization'] == true) {
                        $requires_authorization = true;
                    }
                }
            }
            array_push($result, $dataitem);
        }
        $subTotal = Cart::session($user->id)->getSubTotal();
        $shippingItems = Cart::session($user->id)->getConditionsByType("shipping");
        $shippingTotal = 0;
        foreach ($shippingItems as $item) {
            $shippingTotal += $item->getCalculatedValue($subTotal);
        }
        $taxItems = Cart::session($user->id)->getConditionsByType("tax");
        $taxTotal = 0;
        foreach ($taxItems as $item) {
            $taxTotal += $item->getCalculatedValue($subTotal);
        }
        $saleItems = Cart::session($user->id)->getConditionsByType("sale");
        $saleTotal = 0;
        foreach ($saleItems as $item) {
            $saleTotal += $item->getCalculatedValue($subTotal);
        }
        $couponItems = Cart::session($user->id)->getConditionsByType("coupon");
        $couponTotal = 0;
        foreach ($couponItems as $item) {
            $couponTotal += $item->getCalculatedValue($subTotal);
        }
        $total = Cart::session($user->id)->getTotal();
        $data['items'] = $result;
        $data['subtotal'] = $subTotal;
        $data['shipping'] = $shippingTotal;
        $data['is_shippable'] = $is_shippable;
        $data['requires_authorization'] = $requires_authorization;
        $data['is_subscription'] = $is_subscription;
        $data['tax'] = $taxTotal;
        $data['sale'] = $saleTotal;
        $data['coupon'] = $couponTotal;
        $data['discount'] = $saleTotal + $couponTotal;
        $data['totalItems'] = count($result);
        $data['total'] = $total;
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function clearCart(User $user) {
        Cart::session($user->id)->clear();
        Item::where('user_id', $user->id)->where('order_id', null)->delete();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function checkCartAuth(User $user, $requires_authorization, $order_id, $merchant_id) {
        $item = Item::where('user_id', $user->id)->where('order_id', $order_id)->first();
        if (!$item) {
            return true;
        } else {
            if ($item->requires_authorization == $requires_authorization) {
                if ($item->merchant_id == $merchant_id) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function clearCartSession(User $user) {
        Cart::session($user->id)->clear();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function loadActiveCart(User $user) {
        $items = Item::where('user_id', $user->id)->whereNull('order_id')->get();
        $this->loadItemsToCart($items);
        return array("status" => "success", "message" => "Cart Loaded");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function loadOrderToCart(User $user, $order_id) {
        $order = Order::find($order_id);
        if ($order) {
            if ($order->is_editable) {
                if ($order->user_id == $user->id || $order->supplier_id == $user->id) {
                    $items = Item::where('order_id', $order->id)->get();
                    $this->clearCartSession();
                    $this->loadItemsToCart($items);
                    $data = $this->getCart();
                    return array("status" => "success", "message" => "Cart Loaded", 'cart' => $data);
                }
                return array("status" => "error", "message" => "Access to order denied");
            }
            return array("status" => "error", "message" => "Order not editable");
        }
        return array("status" => "error", "message" => "Order not found");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function checkAddToOrder(User $user, array $data) {
        if (array_key_exists('order_id', $data)) {
            $order = Order::find($data['order_id']);
            if ($order) {
                if ($order->is_editable) {
                    if ($order->user_id == $user->id || $order->supplier_id == $user->id) {
                        return $order->id;
                    }
                }
            }
        }
        return null;
    }

    public function loadItemsToCart($items) {
        foreach ($items as $item) {
            $productVariant = $item->productVariant;
            if ($productVariant) {
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
                Cart::session($user->id)->add(array(
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'attributes' => $attrs,
                    'conditions' => $applyConditions
                ));
            } else {
                $attrs = json_decode($item->attributes, true);
                Cart::session($user->id)->add(array(
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'attributes' => $attrs,
                    'conditions' => $applyConditions
                ));
            }
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
        if (array_key_exists('quantity', $data)) {
            if ((int) $data['quantity'] <= 0) {
                return array("status" => "error", "message" => "amount must be a positive integer");
            }
        } else {
            $data['quantity'] = 1;
        }
        if (array_key_exists('merchant_id', $data)) {
            $merchant = Merchant::find($data['merchant_id']);
            if (!$merchant) {
                return array("status" => "error", "message" => "invalid merchant");
            }
        } else {
            return array("status" => "error", "message" => "missing merchant id");
        }
        $order_id = $this->checkAddToOrder($user, $data);

        $item = null;
        if (array_key_exists('item_id', $data)) {
            $item = Item::where('id', intval($data['item_id']))
                            ->where('user_id', $user->id)
                            ->where('order_id', $order_id)->first();
        } else if (array_key_exists('product_variant_id', $data)) {
            $item = Item::where('product_variant_id', intval($data['product_variant_id']))
                            ->where('user_id', $user->id)
                            ->where('order_id', $order_id)->first();
        }

        if ($item) {
            $quantity = $item->quantity + (int) $data['quantity'];
            $productVariant = $item->productVariant;
            if ($productVariant->quantity >= $quantity || $productVariant->is_digital) {
                Cart::session($user->id)->update($item->id, array(
                    'quantity' => $quantity, // so if the current product has a quantity of 4, another 2 will be added so this will result to 6
                ));

                $item->quantity = $quantity;
                $item->save();
                return array("status" => "success", "message" => "item added to cart successfully", "item" => $item);
            } else {
                return array("status" => "error", "message" => "No more stock of that product");
            }
        } else {
            $productVariant = ProductVariant::find(intval($data['product_variant_id']));
            if ($productVariant) {
                $resultCheck = $this->checkCartAuth($user, $productVariant->requires_authorization, $order_id, $data['merchant_id']);
                if ($resultCheck) {
                    if ((int) $productVariant->quantity >= (int) $data['quantity'] || $productVariant->is_digital) {
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
                        $losAttributes['conditions'] = $applyConditions;
                        $losAttributes['image'] = $productVariant->getCartImg();
                        $losAttributes['is_shippable'] = $productVariant->is_shippable;
                        $losAttributes['requires_authorization'] = $productVariant->requires_authorization;
                        $losAttributes['merchant_id'] = $data['merchant_id'];
                        $losAttributes['product_variant_id'] = $productVariant->id;
                        $losAttributes['requires_authorization'] = $productVariant->requires_authorization;
                        if (array_key_exists("extras", $data)) {
                            foreach ($data["extras"] as $x => $x_value) {
                                $losAttributes[$x] = $x_value;
                            }
                        }

                        $item = Item::create([
                                    'product_variant_id' => $productVariant->id,
                                    'name' => $product->name,
                                    'user_id' => $user->id,
                                    'price' => $productVariant->getActivePrice(),
                                    'requires_authorization' => $productVariant->requires_authorization,
                                    'paid_status' => "unpaid",
                                    'fulfillment' => "unfulfilled",
                                    'quantity' => (int) $data['quantity'],
                                    'merchant_id' => $data['merchant_id'],
                                    'attributes' => json_encode($losAttributes),
                                    'status' => 'active',
                                    'order_id' => $order_id,
                        ]);
                        $item->attributes = $losAttributes;
                        Cart::session($user->id)->add(array(
                            'id' => $item->id,
                            'name' => $product->name,
                            'price' => $productVariant->getActivePrice(),
                            'quantity' => (int) $data['quantity'],
                            'attributes' => $losAttributes,
                            'conditions' => $applyConditions
                        ));
                        return array("status" => "success", "message" => "item added to cart successfully", "cart" => Cart::session($user->id)->getContent(), "item" => $item);
                    } else {
                        return array("status" => "error", "message" => "SOLD_OUT");
                    }
                } else {
                    return array("status" => "error", "message" => "CLEAR_CART");
                }
            }
            return array("status" => "error", "message" => "NO_PRODUCT");
        }
    }

    public function addCustomCartItem(User $user, array $data) {
        if (array_key_exists('merchant_id', $data)) {
            if ($data['merchant_id']) {
                $merchant = Merchant::find($data['merchant_id']);
                if (!$merchant) {
                    return array("status" => "error", "message" => "invalid merchant");
                }
                if (array_key_exists('quantity', $data)) {
                    if ((int) $data['quantity'] <= 0) {
                        return array("status" => "error", "message" => "amount must be a positive integer");
                    }
                } else {
                    $data['quantity'] = 1;
                }
                $order_id = $this->checkAddToOrder($user, $data);
                $resultCheck = $this->checkCartAuth($user, true, $order_id, $data['merchant_id']);
                if ($resultCheck) {

                    $losAttributes = array();
                    $losAttributes['is_digital'] = 1;
                    $losAttributes['is_shippable'] = 0;
                    $losAttributes['requires_authorization'] = 1;
                    $losAttributes['requires_authorization'] = $productVariant->requires_authorization;
                    $losAttributes['merchant_id'] = $data['merchant_id'];
                    if (array_key_exists("extras", $data)) {
                        foreach ($data["extras"] as $x => $x_value) {
                            $losAttributes[$x] = $x_value;
                        }
                    }
                    $item = Item::create([
                                'name' => $losAttributes['name'],
                                'user_id' => $user->id,
                                'price' => $losAttributes['price'],
                                'paid_status' => "unpaid",
                                'fulfillment' => "unfulfilled",
                                'quantity' => (int) $data['quantity'],
                                'merchant_id' => $data['merchant_id'],
                                'attributes' => json_encode($losAttributes),
                                'status' => 'active',
                                'order_id' => $order_id
                    ]);
                    $item->attributes = $losAttributes;
                    Cart::session($user->id)->add(array(
                        'id' => $item->id,
                        'name' => $losAttributes['name'],
                        'price' => $losAttributes['price'],
                        'quantity' => (int) $data['quantity'],
                        'attributes' => $losAttributes
                    ));
                    return array("status" => "success",
                        "message" => "item added to cart successfully",
                        "cart" => Cart::session($user->id)->getContent(),
                        "item" => $item
                    );
                } else {
                    return array("status" => "error", "message" => "CLEAR_CART");
                }
            }
            return array("status" => "error", "message" => "NO_MERCHANT");
        }
        return array("status" => "error", "message" => "NO_MERCHANT");
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  User, array  $data
     * 
     */
    public function updateCartItem(User $user, array $data) {
        $order_id = $this->checkAddToOrder($user, $data);
        $item = Item::where('id', intval($data['item_id']))
                        ->where('user_id', $user->id)
                        ->where('order_id', $order_id)->first();
        if ($item) {
            $productVariant = $item->productVariant;
            if ((int) $data['quantity'] > 0) {
                if ($productVariant->quantity >= ((int) $data['quantity'] ) || $productVariant->is_digital) {
                    $item->quantity = (int) $data['quantity'] + $item->quantity;
                    $item->save();
                    $item->attributes = json_decode($item->attributes, true);
                    Cart::session($user->id)->update($item->id, array(
                        'quantity' => array(
                            'relative' => false,
                            'value' => $item->quantity
                        ),
                    ));
                    return array("status" => "success", "message" => "item updated successfully", "item" => $item);
                } else {
                    return array("status" => "error", "message" => "No more stock of that product");
                }
            } else if ((int) $data['quantity'] == 0) {
                Cart::session($user->id)->remove($productVariant->id);
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
     * Create a new user instance after a valid registration.
     *
     * @param  User, array  $data
     * 
     */
    public function updateCustomCartItem(User $user, array $data ) {
        $order_id = $this->checkAddToOrder($user, $data);
        $item = Item::where('id', intval($data['item_id']))
                        ->where('user_id', $user->id)
                        ->where('order_id', $order_id)->first();
        if ($item) {
            if ($item->productVariant) {
                return array("status" => "error", "message" => "This is not a custom item");
            }
            $result = [];
            $result['id'] = $item->id;
            $losAttributes = array();
            $losAttributes['is_digital'] = 1;
            $losAttributes['is_shippable'] = 0;
            $losAttributes['requires_authorization'] = 1;
            if (array_key_exists("extras", $data)) {
                foreach ($data["extras"] as $x => $x_value) {
                    $losAttributes[$x] = $x_value;
                }
            }
            if (array_key_exists('name', $losAttributes)) {
                $item->name = $losAttributes['name'];
                $result['name'] = $losAttributes['name'];
            }
            if (array_key_exists('quantity', $data)) {
                $item->quantity = $data['quantity'];
                $result['quantity'] = $data['quantity'];
            }
            if (array_key_exists('price', $losAttributes)) {
                $item->quantity = $losAttributes['price'];
                $result['price'] = $losAttributes['price'];
            }

            $result['attributes'] = $losAttributes;
            $item->save();
            Cart::session($user->id)->update($item->id, $result);
            return array("status" => "success", "message" => "item updated successfully", "item" => $item);
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
    public function updateCartItems(User $user, array $data) {
        $items = $data['items'];
        foreach ($items as $value) {
            $value['order_id'] = $data['order_id'];
            $this->updateCartItem($user, $value, false);
        }
        return $this->getCart();
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

}
