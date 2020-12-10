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

    const MODEL_PATH = 'App\\Models\\';

    /**
     * The Auth implementation.
     *
     */
    protected $auth;

    public function getCart($user) {
        if ($user->id) {
            $items = Cart::session($user->id)->getContent();
            $data = array();
            $result = array();
            foreach ($items as $item) {
                $dataitem = [];
                $dataitem['id'] = $item->id;
                $dataitem['name'] = $item->name;
                $dataitem['price'] = $item->price;
                $dataitem['quantity'] = $item->quantity;
                $dataitem['attributes'] = $item->attributes;
                $dataitem['priceSum'] = $item->getPriceSum(); // the subtotal without conditions applied
                $dataitem['priceWithConditions'] = $item->getPriceWithConditions(); // the single price with conditions applied
                $dataitem['priceSumWithConditions'] = $item->getPriceSumWithConditions(); // the subtotal with conditions applied
                // Note that attribute returns ItemAttributeCollection object that extends the native laravel collection
                // so you can do things like below:
                array_push($data, $dataitem);
            }
            $result["subtotal"] = Cart::session($user->id)->getSubTotal();
            $result["total"] = Cart::session($user->id)->getTotal();
            $result['items'] = $data;
            $result['totalItems'] = count($data);
            return $result;
        } else {
            return ['subtotal' => 0, 'total' => 0, 'items' => [], 'totalItems' => 0];
        }
    }
    public function checkCart($user) {
        $cart = $this->getCart($user);
        $items = $cart['items'];
        foreach ($items as $value) {
            $variant = $value->productVariant;
            if ($variant->type == "product" && !$variant->is_digital && $value->quantity > $variant->quantity) {
                $data = [
                    "item_id" => $value->id,
                    "quantity" => 0
                ];
                $this->updateCartItem($user, $data);
            }
//            if ($variant->type == "booking") {
//                $losAttributes = json_decode($variant->attributes, true);
//                if ($losAttributes['type'] == "Booking" && isset($losAttributes['id'])) {
//                    $objClass = self::MODEL_PATH . $losAttributes['type'];
//                    $booking = $objClass::where("id", $losAttributes['id'])->get();
//                }
//            }
        }
        return ["status"=>"success"];
    }

    public function getCheckoutCart(User $user) {
//        Cart::session($user->id)->removeConditionsByType("coupon");
//        Cart::session($user->id)->removeConditionsByType("tax");
        $data = array();
        $items = Cart::session($user->id)->getContent();
        $result = array();
        $is_shippable = false;
        $is_subscription = false;
        $requires_authorization = false;
        $totalItems = 0;
        foreach ($items as $item) {
            $totalItems++;
            $dataitem = [];
            $dataitem['id'] = $item->id;
            $dataitem['name'] = $item->name;
            $dataitem['price'] = $item->price;
            $dataitem['quantity'] = $item->quantity;
            $dataitem['attributes'] = $item->attributes;
            $dataitem['priceSum'] = $item->getPriceSum(); // the subtotal without conditions applied
            $dataitem['priceWithConditions'] = $item->getPriceWithConditions(); // the single price with conditions applied
            $dataitem['priceSumWithConditions'] = $item->getPriceSumWithConditions(); // the subtotal with conditions applied
            // Note that attribute returns ItemAttributeCollection object that extends the native laravel collection
            // so you can do things like below:
            array_push($result, $dataitem);
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
        }
        $subTotal = Cart::session($user->id)->getSubTotal();
        /* $saleItems = Cart::session($user->id)->getConditionsByType("sale");
          $saleTotal = 0;
          foreach ($saleItems as $item) {
          $saleTotal += $item->getCalculatedValue($subTotal);
          }
          $couponItems = Cart::session($user->id)->getConditionsByType("coupon");
          $couponTotal = 0;
          foreach ($couponItems as $item) {
          $couponTotal += $item->getCalculatedValue($subTotal);
          } */
        $taxItems = Cart::session($user->id)->getConditionsByType("tax");
        $taxTotal = 0;

        foreach ($taxItems as $item) {
            $taxTotal += $item->getCalculatedValue($subTotal);
        }
        $shippingItems = Cart::session($user->id)->getConditionsByType("shipping");
        $shippingTotal = 0;
        foreach ($shippingItems as $item) {
            $shippingTotal += $item->getCalculatedValue($subTotal);
        }
        $data['shipping'] = $shippingTotal;
        $data['subtotal'] = $subTotal;
        $data['discount'] = 0;
        $data['total'] = Cart::session($user->id)->getTotal();
        $data['tax'] = $taxTotal;
        $cartConditions = Cart::session($user->id)->getConditions();
        $resultConditions = [];
        foreach ($cartConditions as $condition) {
            $cond = array();
            $cond['getTarget'] = $condition->getTarget(); // the target of which the condition was applied
            $cond['getName'] = $condition->getName(); // the name of the condition
            $cond['getType'] = $condition->getType(); // the type
            $cond['getValue'] = $condition->getValue(); // the value of the condition
            $cond['getOrder'] = $condition->getOrder(); // the order of the condition
            $cond['getEffect'] = substr($condition->getValue(), 0, 1); // the order of the condition
            $cond['getAttributes'] = $condition->getAttributes(); // the attributes of the condition, returns an empty [] if no attributes added
            $value = $condition->getCalculatedValue($subTotal);

            $cond['total'] = $value;
            /* if (substr($condition->getValue(), 0, 1) == "-") {
              $subTotal = $subTotal - $value;
              } else {
              $subTotal = $subTotal + $value;
              } */

            array_push($resultConditions, $cond);
        }
        $data['items'] = $result;
        $data['conditions'] = $resultConditions;
        $data['is_shippable'] = $is_shippable;
        $data['requires_authorization'] = $requires_authorization;
        $data['is_subscription'] = $is_subscription;
        $data['totalItems'] = $totalItems;


        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function clearCart($user) {

        Cart::session($user->id)->clear();
        $order = Order::where('status', 'pending')->where('user_id', $user->id)->first();
        if ($order) {
            $order->orderConditions()->delete();
            $order->payments()->where("status", "pending")->delete();
            $order->items()->delete();
            $order->subtotal = 0;
            $order->tax = 0;
            $order->total = 0;
            $order->save();
        }
        if (isset($user->email)) {
            Item::where('user_id', $user->id)->where('order_id', null)->delete();
        } else {
            Item::where('ref2', $user->id)->where('order_id', null)->delete();
        }

        Cart::clearCartConditions();
    }

    public function migrateCart($user, $device) {
        if ($device) {
            $items = Cart::session($device)->getContent();
            if ((!count($items) > 0)) {
                return true;
            }
            $data = array();
            $result = array();
            foreach ($items as $item) {
                $dataitem = $item->attributes;
                $dataitem = $dataitem->toArray();
                if (array_key_exists("product_variant_id", $dataitem)) {
                    $data = [
                        "product_variant_id" => $dataitem["product_variant_id"],
                        "quantity" => $item->quantity,
                        "item_id" => null,
                        "merchant_id" => $dataitem["merchant_id"]
                    ];
                    $this->addCartItem($user, $data);
                } else {
                    $data = [
                        "name" => $dataitem["name"],
                        "quantity" => $item->quantity,
                        "merchant_id" => $dataitem["merchant_id"],
                        "price" => $item->price,
                        "tax" => 0,
                        "cost" => 0,
                        "extras" => [
                            "id" => $dataitem["id"],
                            "type" => $dataitem["type"],
                            "name" => $dataitem["name"],
                        ]
                    ];
                    $this->addCustomCartItem($user, $data);
                }
                // the subtotal with conditions applied
                // Note that attribute returns ItemAttributeCollection object that extends the native laravel collection
                // so you can do things like below:
            }
            $userDel = json_decode(json_encode(["id" => $device]));
            $this->clearCart($userDel);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function checkCartAuth($user, $requires_authorization, $order_id, $merchant_id) {
        if (isset($user->email)) {
            $item = Item::where('user_id', $user->id)->where('order_id', $order_id)->first();
        } else {
            $item = Item::where('ref2', $user->id)->where('order_id', $order_id)->first();
        }

        //dd($item->toArray());
        if (!$item) {
            return true;
        } else {
            if ($item->merchant_id == $merchant_id) {
                return true;
            }
            return false;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function clearCartSession($userId) {
        Cart::session($userId)->clear();
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
    public function checkAddToOrder($user, array $data) {
        if (array_key_exists('order_id', $data)) {
            $order = Order::find($data['order_id']);
            if ($order) {
                if ($order->is_editable) {
                    if ($order->user_id == $user->id || $order->supplier_id == $user->id) {
                        return $order->id;
                    }
                }
                if ($order->status == "pending") {
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
                $conditions = $productVariant->conditions()->where('status', 'active')->get();
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
                $conditions = $product->conditions()->where('status', 'active')->get();
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

    private function getItemUser($user, $order_id, $data) {
        $item = null;
        if (isset($user->email)) {
            if (array_key_exists('item_id', $data)) {
                $item = Item::where('id', intval($data['item_id']))
                                ->where('user_id', $user->id)
                                ->where('order_id', $order_id)->first();
            } else if (array_key_exists('product_variant_id', $data)) {
                $item = Item::where('product_variant_id', intval($data['product_variant_id']))
                                ->where('user_id', $user->id)
                                ->where('order_id', $order_id)->first();
            }
        } else {
            if (array_key_exists('item_id', $data)) {
                $item = Item::where('id', intval($data['item_id']))
                                ->where('ref2', $user->id)
                                ->where('order_id', $order_id)->first();
            } else if (array_key_exists('product_variant_id', $data)) {
                $item = Item::where('product_variant_id', intval($data['product_variant_id']))
                                ->where('ref2', $user->id)
                                ->where('order_id', $order_id)->first();
            }
        }
        return $item;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  User, array  $data
     * 
     */
    public function addCartItem($user, array $data) {

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

        $item = $this->getItemUser($user, $order_id, $data);


        if ($item) {
            $quantity = (int) $data['quantity'];
            $productVariant = $item->productVariant;
            if ($productVariant->quantity >= $quantity || $productVariant->is_digital) {
                if ($productVariant->min_quantity <= $quantity) {
                    Cart::session($user->id)->update($item->id, array(
                        'quantity' => $quantity, // so if the current product has a quantity of 4, another 2 will be added so this will result to 6
                    ));
                    $cartItem = Cart::session($user->id)->get($item->id);
                    $item->priceSum = $cartItem->getPriceSum();
                    $item->priceConditions = $cartItem->getPriceWithConditions();
                    $item->priceSumConditions = $cartItem->getPriceSumWithConditions();
                    $item->quantity = $quantity;
                    $item->save();
                    return array("status" => "success", "message" => "item added to cart successfully", "item" => $item, "cart" => $this->getCart($user), "quantity" => $quantity);
                } else {
                    return array("status" => "error", "message" => "MIN_QUANTITY", "quantity" => $productVariant->min_quantity);
                }
            } else {
                return array("status" => "error", "message" => "No more stock of that product");
            }
        } else {
            $productVariant = ProductVariant::find(intval($data['product_variant_id']));
            if ($productVariant) {
                $resultCheck = $this->checkCartAuth($user, $productVariant->requires_authorization, $order_id, $data['merchant_id']);
                if ($resultCheck) {
                    if ((int) $productVariant->quantity >= (int) $data['quantity'] || $productVariant->is_digital) {
                        if ($productVariant->min_quantity <= (int) $data['quantity']) {
                            $conditions = $productVariant->conditions()->where('status', 'active')->get();
                            $applyConditions = array();
                            foreach ($conditions as $condition) {
                                $itemCondition = new CartCondition(array(
                                    'name' => $condition->name,
                                    'type' => $condition->type,
                                    'value' => $condition->value,
                                ));
                                array_push($applyConditions, $itemCondition);
                            }
                            $product = $productVariant->product;
                            $itemName = $product->name . " " . $productVariant->description;
                            $conditions = $product->conditions()->where('status', 'active')->get();
                            foreach ($conditions as $condition) {
                                $itemCondition = new CartCondition(array(
                                    'name' => $condition->name,
                                    'type' => $condition->type,
                                    'value' => $condition->value,
                                ));
                                array_push($applyConditions, $itemCondition);
                            }

                            $losAttributes = json_decode($productVariant->attributes, true);
                            if (!$losAttributes) {
                                $losAttributes = array();
                            }
                            $losAttributes['is_digital'] = $productVariant->is_digital;
                            $losAttributes['weight'] = $productVariant->weight;
                            $losAttributes['conditions'] = $applyConditions;
                            $losAttributes['type'] = ucfirst($productVariant->type);
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
                            if ($productVariant->attributes) {
                                $productVariant->attributes = json_decode($productVariant->attributes, true);

                                foreach ($productVariant->attributes as $x => $x_value) {
                                    $losAttributes[$x] = $x_value;
                                }
                            }
                            $itemsArr = [
                                'product_variant_id' => $productVariant->id,
                                'name' => $product->name,
                                'price' => $productVariant->getActivePrice(),
                                'cost' => $productVariant->cost,
                                'tax' => $productVariant->tax,
                                'requires_authorization' => $productVariant->requires_authorization,
                                'paid_status' => "unpaid",
                                'fulfillment' => "unfulfilled",
                                'quantity' => (int) $data['quantity'],
                                'merchant_id' => $data['merchant_id'],
                                'attributes' => json_encode($losAttributes),
                                'order_id' => $order_id,
                            ];
                            if (isset($user->email)) {
                                $itemsArr['user_id'] = $user->id;
                            } else {
                                $itemsArr['ref2'] = $user->id;
                            }

                            $item = Item::create($itemsArr);

                            Cart::session($user->id)->add(array(
                                'id' => $item->id,
                                'name' => $product->name,
                                'price' => $productVariant->getActivePrice(),
                                'quantity' => (int) $data['quantity'],
                                'attributes' => $losAttributes,
                                'conditions' => $applyConditions
                            ));
                            $cartItem = Cart::session($user->id)->get($item->id);
                            $item->priceSum = $cartItem->getPriceSum();
                            $item->priceConditions = $cartItem->getPriceWithConditions();
                            $item->priceSumConditions = $cartItem->getPriceSumWithConditions();
                            $item->save();
                            $item->attributes = $losAttributes;
                            if ($losAttributes['type'] == "Booking" && isset($losAttributes['id'])) {
                                $objClass = self::MODEL_PATH . $losAttributes['type'];
                                $objClass::where("id", $losAttributes['id'])->update(['price' => $item->priceSumConditions]);
                            }

                            return array("status" => "success", "message" => "item added to cart successfully", "cart" => $this->getCart($user), "item" => $item);
                        } else {
                            return array("status" => "error", "message" => "MIN_QUANTITY", "quantity" => $productVariant->min_quantity);
                        }
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

    public function addCustomCartItem($user, array $data) {
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
                    $losAttributes['requires_authorization'] = 0;
                    $losAttributes['merchant_id'] = $data['merchant_id'];
                    if (array_key_exists("extras", $data)) {
                        foreach ($data["extras"] as $x => $x_value) {
                            $losAttributes[$x] = $x_value;
                        }
                    }
                    $itemsArr = [
                        'name' => $losAttributes['name'],
                        'price' => $data['price'],
                        'cost' => $data['cost'],
                        'tax' => $data['tax'],
                        'paid_status' => "unpaid",
                        'fulfillment' => "unfulfilled",
                        'quantity' => (int) $data['quantity'],
                        'merchant_id' => $data['merchant_id'],
                        'attributes' => json_encode($losAttributes),
                        'status' => 'active',
                        'order_id' => $order_id
                    ];
                    if (isset($user->email)) {
                        $itemsArr['user_id'] = $user->id;
                    } else {
                        $itemsArr['ref2'] = $user->id;
                    }
                    $item = Item::create($itemsArr);
                    Cart::session($user->id)->add(array(
                        'id' => $item->id,
                        'name' => $data['name'],
                        'price' => $data['price'],
                        'quantity' => (int) $data['quantity'],
                        'attributes' => $losAttributes
                    ));
                    $cartItem = Cart::session($user->id)->get($item->id);
                    $item->priceSum = $cartItem->getPriceSum();
                    $item->priceConditions = $cartItem->getPriceWithConditions();
                    $item->priceSumConditions = $cartItem->getPriceSumWithConditions();
                    $item->save();
                    $item->attributes = $losAttributes;
                    return array("status" => "success",
                        "message" => "item added to cart successfully",
                        "cart" => $this->getCart($user),
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

    private function getCustomItemUser($user, $order_id, $data) {
        $item = null;
        if (isset($user->email)) {
            if ($order_id) {
                $item = Item::where('id', intval($data['item_id']))
                                ->where('user_id', $user->id)
                                ->where('order_id', $order_id)->first();
            } else {
                $item = Item::where('id', intval($data['item_id']))
                                ->where('user_id', $user->id)->first();
            }
        } else {
            if ($order_id) {
                $item = Item::where('id', intval($data['item_id']))
                                ->where('ref2', $user->id)
                                ->where('order_id', $order_id)->first();
            } else {
                $item = Item::where('id', intval($data['item_id']))
                                ->where('ref2', $user->id)->first();
            }
        }
        return $item;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  User, array  $data
     * 
     */
    public function updateCartItem($user, array $data) {
        $order_id = $this->checkAddToOrder($user, $data);
        $item = $this->getItemUser($user, $order_id, $data);
        if ($item) {
            $productVariant = $item->productVariant;
            if ((int) $data['quantity'] > 0) {
                if ($productVariant->quantity >= ((int) $data['quantity'] ) || $productVariant->is_digital) {
                    if ($productVariant->min_quantity <= ((int) $data['quantity'] )) {
                        $losAttributes = json_decode($item->attributes,true);
                        if (array_key_exists("extras", $data)) {
                            foreach ($data["extras"] as $x => $x_value) {
                                $losAttributes[$x] = $x_value;
                            }
                        }
                        $item->quantity = (int) $data['quantity'];
                        Cart::session($user->id)->update($item->id, array(
                            'quantity' => array(
                                'relative' => false,
                                'value' => $item->quantity
                            ),
                            'attributes' => $losAttributes
                        ));
                        $cartItem = Cart::session($user->id)->get($item->id);
                        $item->priceSum = $cartItem->getPriceSum();
                        $item->quantity = $cartItem->quantity;
                        $item->priceConditions = $cartItem->getPriceWithConditions();
                        $item->priceSumConditions = $cartItem->getPriceSumWithConditions();
                        $item->attributes = json_encode($losAttributes);
                        $item->save();
                        
                        if ($losAttributes['type'] == "Booking" && isset($losAttributes['id'])) {
                            $objClass = self::MODEL_PATH . $losAttributes['type'];
                            $objClass::where("id", $losAttributes['id'])->update(['price' => $item->priceSumConditions]);
                        }
                        return array("status" => "success", "message" => "item updated successfully", "cart" => $this->getCart($user), "item" => $item);
                    } else {
                        Cart::session($user->id)->remove($item->id);
                        $item->delete();
                        return array("status" => "success", "message" => "Item deleted from cart", "cart" => $this->getCart($user));
                    }
                } else {
                    return array("status" => "error", "message" => "No more stock of that product");
                }
            } else if ((int) $data['quantity'] == 0) {
                Cart::session($user->id)->remove($item->id);
                $item->delete();
                return array("status" => "success", "message" => "Item deleted from cart", "cart" => $this->getCart($user));
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
    public function updateCustomCartItem($user, array $data) {
        $order_id = $this->checkAddToOrder($user, $data);
        $item = $this->getCustomItemUser($user, $order_id, $data);
        if ($item) {
            if ($item->productVariant) {
                return array("status" => "error", "message" => "This is not a custom item");
            }
            $result = [];
            $result['id'] = $item->id;
            $losAttributes = array();
            $losAttributes['is_digital'] = 1;
            $losAttributes['is_shippable'] = 0;
            $losAttributes['requires_authorization'] = 0;
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
            return array("status" => "success", "message" => "item updated successfully", "cart" => $this->getCart($user), "item" => $item);
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
    public function updateCartItems($user, array $data) {
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
    public function validatorAddCartCustom(array $data) {
        return Validator::make($data, [
                    'merchant_id' => 'required|max:255',
                    'price' => 'required|max:255',
                    'cost' => 'required|max:255',
                    'tax' => 'required|max:255',
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
