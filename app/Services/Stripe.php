<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Services\EditOrder;

class Stripe {

    /**
     * The Auth implementation.
     *
     */
    protected $editOrder;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditOrder $editOrder) {
        $this->editOrder = $editOrder;
        \Stripe\Stripe::setApiKey("sk_test_BQokikJOvBiI2HlWgH4olfQ2");
    }

    public function createCustomer(User $user, array $data) {

        // Create a Customer:
        $customer = \Stripe\Customer::create(array(
                    "email" => $user->email
        ));
        $user->stripe_id = $customer->id;
        $user->save();
    }

    public function createSource(User $user, array $data) {
        // Token is created using Stripe.js or Checkout!
        // Get the payment token submitted by the form:
        $token = $data['source'];

        $customer = \Stripe\Customer::retrieve($user->stripe_id);
        $customer->sources->create(array("source" => $token));
    }

    public function setAsDefault(User $user, array $data) {
        // Token is created using Stripe.js or Checkout!
        // Get the payment token submitted by the form:
        $token = $data['source'];

        $customer = \Stripe\Customer::retrieve($user->stripe_id);
        $customer->source = $token;
        $customer->save();
    }

    public function listSources(User $user, array $data) {
        // Token is created using Stripe.js or Checkout!
        // Get the payment token submitted by the form:
        return \Stripe\Customer::retrieve($user->stripe_id)->sources->all(array(
                    'limit' => $data['limit'], 'object' => $data['type']));
    }
    
    public function deleteSource(User $user, $source) {
        // Token is created using Stripe.js or Checkout!
        // Get the payment token submitted by the form:
        $customer = \Stripe\Customer::retrieve($user->stripe_id);
        $customer->sources->retrieve($source)->delete();
    }

    public function createSubscription(User $user, array $data, $plan) {
        \Stripe\Subscription::create(array(
            "customer" => $user->stripe_id,
            "plan" => $plan,
            "metadata" => $data,
        ));
    }

    public function deleteSubscription(User $user, array $data) {
        $sub = \Stripe\Subscription::retrieve($data["subscription"]);
        if ($sub) {
            if ($sub->customer == $user->stripe_id) {
                $sub->cancel();
            }
        }
    }
    public function listSubscriptions(User $user) {
        return \Stripe\Subscription::all(array('customer'=>$user->stripe_id));
    }

    

    public function makeCharge(User $user, Order $order, array $data) {
        // Token is created using Stripe.js or Checkout!
        // Get the payment token submitted by the form:
        $customer = \Stripe\Customer::retrieve($user->stripe_id);
        if ($customer) {
            if ($customer->default_source) {
                $charge = \Stripe\Charge::create(array(
                            "amount" => $order->total,
                            "currency" => 'usd',
                            "metadata" => array("order_id" => $order->id),
                            "description" => "Example charge",
                            "customer" => $customer->id
                ));
                return $charge;
            } else if (array_key_exists("source", $data)) {
                $charge = \Stripe\Charge::create(array(
                            "amount" => $order->total,
                            "currency" => 'usd',
                            "metadata" => array("order_id" => $order->id),
                            "description" => "Example charge",
                            "customer" => $customer->id
                ));
                return $charge;
            } else {
                return ["status" => "error", "message" => "No source found"];
            }
        } else {
            return ["status" => "error", "message" => "customer not found"];
        }
    }

}
