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
use App\Services\EditBilling;
use App\Services\Geolocation;
use App\Services\EditAlerts;
use Mail;
use Darryldecode\Cart\CartCondition;
use Cart;
use DB;

class OrderJobs {

    const OBJECT_ORDER = 'Order';
    const OBJECT_ORDER_REQUEST = 'OrderRequest';
    const ORDER_PAYMENT = 'order_payment';
    const ORDER_PAYMENT_REQUEST = 'order_payment_request';
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
    protected $editAlerts;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $geolocation;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $editOrder;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $editBilling;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(PayU $payU, EditCart $editCart, EditAlerts $editAlerts, Geolocation $geolocation, EditOrder $editOrder, EditBilling $editBilling) {
        $this->payU = $payU;
        $this->editAlerts = $editAlerts;
        $this->editCart = $editCart;
        $this->geolocation = $geolocation;
        $this->editOrder = $editOrder;
        $this->editBilling = $editBilling;
    }

    public function createNewOrder(Order $order) {
        $newOrder = Order::create([
                    'status' => 'pending',
                    'subtotal' => 0,
                    'tax' => 0,
                    'is_digital' => 0,
                    'is_shippable' => 1,
                    'is_recurring' => 1,
                    'total' => 0,
                    'user_id' => $user->id
        ]);
        $items = $order->items;
        foreach ($items as $item) {
            $attributes = json_decode($item->attributes, true);
            if (array_key_exists("is_credit", $attributes)) {
                if ($attributes['is_credit']) {
                    continue;
                }
            }
            $data = [
                "product_variant_id" => $item->product_variant_id,
                "quantity" => $item->quantity,
                "order_id" => $newOrder->id,
                "item_id" => null,
                "merchant_id" => $item->merchant_id
            ];
            $this->editCart->addCartItem($user, $data);
        }

        $orderAttributes = json_decode($order->attributes, true);
        if (array_key_exists('buyers', $orderAttributes)) {
            $buyers = $orderAttributes['buyers'];
            $finalBuyers = [];
            foreach ($buyers as $value) {
                if ($value != $user->id) {
                    array_push($finalBuyers, $value);
                }
            }
            $orderAttributes['buyers'] = $finalBuyers;
        } else {
            $orderAttributes['buyers'] = [];
        }
        if (!array_key_exists('split_order', $orderAttributes)) {
            $orderAttributes['split_order'] = false;
        }

        $address = $order->orderAddresses()->where("type", "shipping")->first();
        if ($address) {
            $resultG = $this->geolocation->checkMerchantPolygons($address->lat, $address->long, $data['merchant_id']);
            if ($resultG["status"] == "success") {
                $polygon = $resultG['polygon'];
                $orderAttributes['polygon'] = $polygon->id;
                $orderAttributes['origin'] = $polygon->address_id;
            }
            $addressCont = $address->toArray();
            unset($addressCont['id']);
            $newAddress = new OrderAddress($addressCont);
            $newOrder->orderAddresses()->save($newAddress);
            $newOrder->attributes = $orderAttributes;
            $order->is_recurring = false;
            $order->save();
            return $newOrder;
        } else {
            $newOrder->items()->delete();
            $newOrder->delete();
            return null;
        }
    }

    public function checkOrder(Order $order) {
        $payments = $order->payments;
        if (!$payments) {
            return false;
        }
        $payments = $order->payments()->where("status", "<>", "approved")->get();
        if ($payments) {
            return false;
        }
        return true;
    }

    public function RecurringOrder(Order $order, $ip_address) {
        $user = $order->user;
        $this->editCart->clearCartSession($order->user_id);
        $checkResult = $this->checkOrder($order);
        if($checkResult){
            $newOrder = $this->createNewOrder($order);
            if($newOrder){
                return $this->postPrepareOrder($user, $newOrder, $ip_address);
            }
        } else {
            return $this->postPrepareOrder($user, $order, $ip_address);
        }
    }

    public function handleOrderPrepareError(User $user, Order $order, $result, $ip_address) {
        echo "Order missing: " . $result["type"] . PHP_EOL;
        if ($result["type"] == "shipping") {
            echo "Setting shipping condition" . PHP_EOL;
            if (is_array($order->attributes)) {
                $order->attributes = json_encode($order->attributes);
            }

            $order->save();
            $shippingResult = $this->editOrder->setPlatformShippingCondition($user, $order->id, "Rapigo");
            echo "Shipping condition result" . PHP_EOL;
            echo json_encode($shippingResult) . PHP_EOL;
            if ($shippingResult["status"] == "success") {
                echo "Shipping condition set" . PHP_EOL;
            } else {
                echo "Shipiing condition not set. Terminating." . PHP_EOL;
                return null;
            }
        } else if ($result["type"] == "credits") {
            echo "Adding required credits to order: " . $result["required_credits"] . PHP_EOL;
            $requiredCredits = $result["required_credits"];
            $creditItem = $result["creditItem"];
            $data = [
                "product_variant_id" => $creditItem->id,
                "quantity" => $requiredCredits,
                "order_id" => $order->id,
                "item_id" => null,
                "merchant_id" => $creditItem->merchant_id
            ];
            $this->editCart->addCartItem($user, $data);
            echo "required credits set" . PHP_EOL;
        } else {
            return null;
        }
        $this->postPrepareOrder($user, $order, $ip_address);
    }

    public function handleOrderPrepareSuccess(User $user, Payment $payment, $ip_address) {
        $data = [
            "payment_id" => $payment->id,
            "quick" => true,
            "platform" => "Food",
            "ip_address" => $ip_address
        ];
        return $this->editBilling->payCreditCard($user, "PayU", $data);
    }

    public function postPrepareOrder(User $user, Order $order, $ip_address) {
        echo "Preparing order for payment creation" . PHP_EOL;
        $orderAttributes = $order->attributes;
        if (!is_array($orderAttributes)) {
            $orderAttributes = json_decode($orderAttributes, true);
        }
        $container = [
            "order_id" => $order->id,
            "payers" => $orderAttributes['buyers'],
            "split_order" => $orderAttributes["split_order"],
            "platform" => "Food"
        ];
        $result = $this->editOrder->checkOrder($user, $order, $container);
        if ($result['status'] == "success") {
            $order->attributes = json_encode($order->attributes);
            $order->save();
            echo "Order passed verification" . PHP_EOL;
            $result2 = $this->editOrder->prepareOrder($user, $container['platform'], $container);
            echo "Order preparation result" . PHP_EOL;
            echo json_encode($result2) . PHP_EOL;
            if ($result2['status'] == "success") {
                $payment = $result2['payment'];
                $result3 = $this->handleOrderPrepareSuccess($user, $payment, $ip_address);
            }
        } else {
            $this->handleOrderPrepareError($user, $order, $result, $ip_address);
        }
    }

}
