<?php

namespace App\Services;

use Validator;
use App\Models\User;
use App\Models\Item;
use App\Models\ProductVariant;
use Darryldecode\Cart\CartCondition;
use Cart;

class EditCart {

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
        $is_subscription = false;
        $requires_authorization = false;
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
        Cart::clear();
        Item::where('user_id', $user->id)->where('order_id', null)->delete();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function checkCartMerchant(User $user, ProductVariant $variant) {
        $item = Item::where('user_id', $user->id)->where('order_id', null)->first();
        if (!$item) {
            return true;
        } else {
            $tester = $item->productVariant;
            if ($tester) {
                if ($tester->merchant_id == $variant->merchant_id) {
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
        $item = null;
        if (array_key_exists('item_id',$data)) {
            $item = Item::where('id', intval($data['item_id']))
                            ->where('user_id', $user->id)
                            ->where('order_id', null)->first();
        }

        if ($item) {
            $quantity = $item->quantity + (int) $data['quantity'];
            $productVariant = $item->productVariant;
            if ($productVariant->quantity >= $quantity || $productVariant->is_digital) {
                Cart::update($item->id, array(
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
                $resultCheck = $this->checkCartMerchant($user, $productVariant);
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
                        if (array_key_exists("etras", $data)) {
                            $losAttributes['extras'] = $data['extras'];
                        }
                        $losAttributes['is_digital'] = $productVariant->is_digital;
                        $losAttributes['is_shippable'] = $productVariant->is_shippable;
                        $losAttributes['requires_authorization'] = $productVariant->requires_authorization;
                        if (array_key_exists("extras", $data)) {
                            foreach ($age as $x => $x_value) {
                                $losAttributes[$x] = $x_value;
                            }
                        }

                        $item = Item::create([
                                    'product_variant_id' => $productVariant->id,
                                    'name' => $product->name,
                                    'user_id' => $user->id,
                                    'price' => $productVariant->price,
                                    'quantity' => (int) $data['quantity'],
                                    'attributes' => json_encode($losAttributes),
                                    'status' => 'active'
                        ]);
                        Cart::add(array(
                            'id' => $item->id,
                            'name' => $product->name,
                            'price' => $productVariant->price,
                            'quantity' => (int) $data['quantity'],
                            'attributes' => $losAttributes,
                            'conditions' => $applyConditions
                        ));

                        return array("status" => "success", "message" => "item added to cart successfully");
                    } else {
                        return array("status" => "error", "message" => "No more stock of that product");
                    }
                } else {
                    return array("status" => "error", "message" => "You must clear your cart to add products from a different merchant");
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
        $item = Item::where('id', intval($data['item_id']))
                        ->where('user_id', $user->id)
                        ->where('order_id', null)->first();
        if ($item) {
            $productVariant = $item->productVariant;
            if ((int) $data['quantity'] > 0) {
                if ($productVariant->quantity >= ((int) $data['quantity'] ) || $productVariant->is_digital) {
                    $item->quantity = (int) $data['quantity'];
                    $item->save();
                    Cart::update($item->id, array(
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

}
