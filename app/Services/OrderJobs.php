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
    public function __construct(PayU $payU, EditCart $editCart, EditAlerts $editAlerts, Geolocation $geolocation, EditOrder $editOrder,EditBilling $editBilling) {
        $this->payU = $payU;
        $this->editAlerts = $editAlerts;
        $this->editCart = $editCart;
        $this->editOrder = $editOrder;
        $this->editBilling = $editBilling;
    }

    public function RecurringOrder(Order $order,$ip_address) {
        $user = $order->user;
        $this->editCart->clearCartSession($order->user_id);

        if ($order) {
            $newOrder = Order::create([
                        'status' => 'pending',
                        'subtotal' => 0,
                        'tax' => 0,
                        'is_digital' => 0,
                        'is_shippable' => 1,
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
            if (!array_key_exists('buyers', $orderAttributes)) {
                $orderAttributes['buyers'] = [];
            }
            if (!array_key_exists('split_order', $orderAttributes)) {
                $orderAttributes['split_order'] = false;
            }
            
            $address = $order->orderAddresses()->where("type", "shipping")->first();
            if ($address) {
                $addressCont = $address->toArray();
                unset($addressCont['id']);
                $newAddress = new OrderAddress($addressCont);
                $newOrder->orderAddresses()->save($newAddress);
                $container = [
                    "order_id" => $newOrder->id,
                    "payers" => $orderAttributes['buyers'],
                    "split_order" => $orderAttributes["split_order"],
                    "platform" => "Food"
                ];
                $result = $this->editOrder->checkOrder($user, $newOrder, $container);
                if ($result['status'] == "success") {
                    $result2 = $this->editOrder->prepareOrder($user, $container['platform'], $container);
                    if ($result2['status'] == "success") {
                        $payment = $result2['payment'];
                        $data = [
                            "payment_id" =>$payment->id,
                            "quick" => true,
                            "platform" => "Food",
                            "ip_address" =>$ip_address
                        ];
                        $result3 = $this->editBilling->payCreditCard($user,"PayU",$data);
                        dd($result3);
                    }
                    dd($result2);
                }
                return $result;
            } else {
                $newOrder->items()->delete();
                $newOrder->delete();
            }

            
        }
    }

    public function generateDigitalSignature() {
        
    }

}
