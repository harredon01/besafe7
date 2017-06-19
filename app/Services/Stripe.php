<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Source;
use App\Models\Subscription;
use Validator;

class Stripe {

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct() {
        \Stripe\Stripe::setApiKey("sk_test_tDJJWJMN72ql5LHKHAtkCrpd");
    }

    public function createClient(User $user) {
        // Create a Customer:
        try {
            // Use Stripe's library to make requests...
            $customer = \Stripe\Customer::create(array(
                        "email" => $user->email
            ));
            if ($customer->id) {
                $source = new Source([
                    "gateway" => "payu",
                    "client_id" => $customer->id
                ]);
                $user->sources()->save($source);
                return $source;
            }
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            return $e->getJsonBody();
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            return $e->getJsonBody();
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            return $e->getJsonBody();
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return $e->getJsonBody();
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            return $e->getJsonBody();
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            return $e->getJsonBody();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return $e->getJsonBody();
        }
    }

    public function createSource(Source $source, array $data) {
        try {
            $validator = $this->validatorSource($data);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
            }
            $token = $data['source'];
            if ($data["default"]) {
                $source->source = $token;
                $source->save();
            }
            $customer = \Stripe\Customer::retrieve($source->client_id);
            return $customer->sources->create(array("source" => $token));
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            return $e->getJsonBody();
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            return $e->getJsonBody();
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            return $e->getJsonBody();
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return $e->getJsonBody();
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            return $e->getJsonBody();
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            return $e->getJsonBody();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return $e->getJsonBody();
        }
    }

    public function editSource(Source $source, array $data) {
//        $customer = \Stripe\Customer::retrieve($data['source']);
//        $token = $data['source'];
//        $card = $customer->sources->retrieve($token);
//        $card->name = $data['source'];
//        $card->save();
//
//        return $customer->sources->create(array("source" => $token));
    }

    public function setAsDefault(Source $source, array $data) {
        // Token is created using Stripe.js or Checkout!
        // Get the payment token submitted by the form:
        $validator = $this->validatorDefault($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $token = $data['source'];
        $source->source = $token;
        $customer = \Stripe\Customer::retrieve($source->client_id);
        $source->save();
        $customer->source = $token;
        $customer->save();
    }

    public function getSources(Source $source) {
        $result = \Stripe\Customer::retrieve($source->client_id)->sources->all(array(
            'limit' => 20));
        $sources = $result['data'];
        $dest = array();
        foreach ($sources as $item) {
            if($item->id==$source->source){
                $item->is_default = true;
            }
            array_push($dest, $item);
        }
        return $dest;
    }

    public function deleteSource(Source $source, $token) {
        try {
            $customer = \Stripe\Customer::retrieve($source->client_id);
            $customer->sources->retrieve($token)->delete();
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            return $e->getJsonBody();
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            return $e->getJsonBody();
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            return $e->getJsonBody();
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return $e->getJsonBody();
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            return $e->getJsonBody();
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            return $e->getJsonBody();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return $e->getJsonBody();
        }
    }

    public function getSource(Source $source, $id) {
        $customer = \Stripe\Customer::retrieve($source->client_id);
        $customer->sources->retrieve($id);
    }

    public function createSubscriptionSourceClient(User $user, array $data) {
        try {
            $validator = $this->validatorSubscriptionSourceClient($data);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
            }
            $customer = \Stripe\Customer::create(array(
                        "email" => $user->email,
                        "source" => $data['source'],
            ));
            if ($customer) {
                $planL = Plan::where("plan_id", $data['plan_id'])->first();
                if ($planL) {
                    $source = new Source([
                        "gateway" => "Stripe",
                        "client_id" => $customer->id,
                        "source" => $data['source'],
                        "has_default" => true
                    ]);
                    $user->sources()->save($source);
                    $subscription = \Stripe\Subscription::create(array(
                                "customer" => $customer->id,
                                "plan" => $planL->plan_id,
                                "metadata" => $data,
                    ));
                    $subscriptionL = new Subscription([
                        "gateway" => "Stripe",
                        "status" => "active",
                        "type" => $planL->type,
                        "name" => $planL->name,
                        'plan_id' => $planL->id,
                        'plan' => $planL->plan_id,
                        "source_id" => $subscription->id,
                        "client_id" => $source->client_id,
                        "object_id" => $data['object_id'],
                        "interval" => $planL->interval,
                        "interval_type" => $planL->interval_type,
                        "quantity" => $data['quantity'],
                        "ends_at" => Date($subscription->current_period_end)
                    ]);
                    $user->subscriptions()->save($subscriptionL);
                    return $subscriptionL;
                }
                return ['status' => 'error', 'message' => "Plan does not exist"];
            }
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            return $e->getJsonBody();
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            return $e->getJsonBody();
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            return $e->getJsonBody();
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return $e->getJsonBody();
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            return $e->getJsonBody();
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            return $e->getJsonBody();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return $e->getJsonBody();
        }
    }

    public function createSubscriptionSource(User $user, Source $source, array $data) {

        try {
            $validator = $this->validatorSubscriptionSource($data);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
            }
            $customer = \Stripe\Customer::retrieve($source->client_id);
            if ($customer) {
                $token = $data['source'];
                if ($data['save']) {
                    $source->source = $token;
                    $source->has_default = true;
                }
                $source->save();
                $planL = Plan::where("plan_id", $data['plan_id'])->first();
                $customer->sources->create(array("source" => $token));
                $subscription = \Stripe\Subscription::create(array(
                            "customer" => $customer->id,
                            "plan" => $planL->code,
                            "metadata" => $data,
                ));
                $subscriptionL = new Subscription([
                    "gateway" => "stripe",
                    "status" => "active",
                    "type" => $planL->type,
                    "name" => $planL->name,
                    "source_id" => $subscription->id,
                    "client_id" => $source->client_id,
                    "object_id" => $data['object_id'],
                    "interval" => $planL->interval,
                    "interval_type" => $planL->interval_type,
                    "quantity" => $data['quantity'],
                    "ends_at" => Date($subscription->current_period_end)
                ]);
                $user->subscriptions()->save($subscriptionL);
                return $subscriptionL;
            }
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            return $e->getJsonBody();
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            return $e->getJsonBody();
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            return $e->getJsonBody();
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return $e->getJsonBody();
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            return $e->getJsonBody();
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            return $e->getJsonBody();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return $e->getJsonBody();
        }
    }

    public function createSubscriptionExistingSource(User $user, Source $source, array $data) {
        try {
            $validator = $this->validatorSubscriptionSource($data);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
            }
            $customer = \Stripe\Customer::retrieve($source->client_id);
            if ($customer) {
                $token = $data['source'];
                unset($data['source']);
                $source->source = $token;
                $source->save();
                $planL = Plan::where("plan_id", $data['plan_id'])->first();
                $customer->sources->create(array("source" => $token));
                $subscription = \Stripe\Subscription::create(array(
                            "customer" => $customer->id,
                            "plan" => $planL->code,
                            "metadata" => $data,
                ));
                $subscriptionL = new Subscription([
                    "gateway" => "stripe",
                    "status" => "active",
                    "type" => $planL->type,
                    "name" => $planL->name,
                    "source_id" => $subscription->id,
                    "client_id" => $source->client_id,
                    "object_id" => $data['object_id'],
                    "interval" => $planL->interval,
                    "interval_type" => $planL->interval_type,
                    "quantity" => $data['quantity'],
                    "ends_at" => Date($subscription->current_period_end)
                ]);
                $user->subscriptions()->save($subscriptionL);
                return $subscriptionL;
            }
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            return $e->getJsonBody();
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            return $e->getJsonBody();
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            return $e->getJsonBody();
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return $e->getJsonBody();
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            return $e->getJsonBody();
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            return $e->getJsonBody();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return $e->getJsonBody();
        }
    }

    public function createSubscription(User $user, Source $source, array $data) {
        try {
            $validator = $this->validatorSubscription($data);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
            }
            $customer = \Stripe\Customer::retrieve($source->client_id);
            if ($customer) {
                if ($customer->default_source) {
                    $planL = Plan::where("plan_id", $data['plan_id'])->first();
                    if ($planL) {
                        $subscription = \Stripe\Subscription::create(array(
                                    "customer" => $customer->id,
                                    "plan" => $planL->plan_id,
                                    "metadata" => $data,
                        ));
                        $subscriptionL = new Subscription([
                            "gateway" => "stripe",
                            "status" => "active",
                            "type" => $planL->type,
                            "name" => $planL->name,
                            "plan" => $planL->plan_id,
                            "plan_id" => $planL->id,
                            "source_id" => $subscription->id,
                            "client_id" => $source->client_id,
                            "object_id" => $data['object_id'],
                            "interval" => $planL->interval,
                            "interval_type" => $planL->interval_type,
                            "quantity" => $data['quantity'],
                            "ends_at" => Date($subscription->current_period_end)
                        ]);
                        $user->subscriptions()->save($subscriptionL);
                        return $subscriptionL;
                    }
                    return ["status" => "error", "message" => "Plan does not exist"];
                }
                return ["status" => "error", "message" => "User does not have default source"];
            }
            return ["status" => "error", "message" => "Stripe Customer does not exist"];
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            return $e->getJsonBody();
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            return $e->getJsonBody();
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            return $e->getJsonBody();
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return $e->getJsonBody();
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            return $e->getJsonBody();
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            return $e->getJsonBody();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return $e->getJsonBody();
        }
    }

    public function editSubscription(User $user, Source $source, $subscription, array $data) {
        $validator = $this->validatorSubscription($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        try {
            $sub = \Stripe\Subscription::retrieve($subscription);
            if ($sub) {
                $source = $user->subscriptions()->where('gateway', "stripe")->where('source_id', $subscription)->first();
                if ($source) {
                    $planL = Plan::where("plan_id", $data['plan_id'])->first();
                    $sub->plan = $planL->code;
                    $sub->save();

                    $subscription->status = "active";
                    $subscription->type = $planL->type;
                    $subscription->name = $planL->name;
                    $subscription->object_id = $data['object_id'];
                    $subscription->interval = $planL->interval;
                    $subscription->interval_type = $planL->interval_type;
                    $subscription->quantity = 1;
                    $subscription->ends_at = Date($sub->current_period_end);
                    $subscription->save();
                }
            }
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            return $e->getJsonBody();
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            return $e->getJsonBody();
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            return $e->getJsonBody();
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return $e->getJsonBody();
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            return $e->getJsonBody();
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            return $e->getJsonBody();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return $e->getJsonBody();
        }
    }

    public function deleteSubscription(User $user, $subscription) {
        try {
            $sub = \Stripe\Subscription::retrieve($subscription);
            if ($sub) {
                $source = $user->subscriptions()->where('gateway', "stripe")->where('source_id', $subscription)->first();
                if ($source) {
                    if ($sub->customer == $source->client_id) {
                        $sub->cancel();
                        $source->delete();
                    }
                }
            }
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            return $e->getJsonBody();
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            return $e->getJsonBody();
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            return $e->getJsonBody();
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return $e->getJsonBody();
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            return $e->getJsonBody();
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            return $e->getJsonBody();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return $e->getJsonBody();
        }
    }

    public function getSubscriptions(Source $source) {
        return \Stripe\Subscription::all(array('customer' => $source->client_id));
    }

    public function useSource(User $user, Order $order, array $data) {
        try {
            $validator = $this->validatorUseSource($data);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
            }
            $sources = $user->sources()->where('gateway', "stripe")->get();
            if ($sources) {
                $customer = \Stripe\Customer::retrieve($sources[0]->client_id);
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
                                    "customer" => $data["source"]
                        ));
                        return $charge;
                    } else {
                        return ["status" => "error", "message" => "No source found"];
                    }
                } else {
                    return ["status" => "error", "message" => "customer not found"];
                }
            }
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            return $e->getJsonBody();
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            return $e->getJsonBody();
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            return $e->getJsonBody();
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return $e->getJsonBody();
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            return $e->getJsonBody();
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            return $e->getJsonBody();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return $e->getJsonBody();
        }
    }

    public function webhook(array $payload) {
        if (!$this->isInTestingEnvironment() && !$this->eventExistsOnStripe($payload['id'])) {
            return;
        }

        $method = 'handle' . studly_case(str_replace('.', '_', $payload['type']));
        $this->saveTransaction($payload);
        if (method_exists($this, $method)) {
            return $this->{$method}($payload);
        } else {
            return $this->missingMethod();
        }
    }

    public function makeCharge(Order $order, array $payload) {
        try {
            $token = $payload['source'];
            $charge = \Stripe\Charge::create(array(
                        "amount" => $order->total,
                        "currency" => "usd",
                        "description" => "Example charge",
                        "metadata" => array("order_id" => $order->id),
                        "source" => $token,
            ));
            $this->saveTransaction($charge);
            return $charge;
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            return $e->getJsonBody();
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            return $e->getJsonBody();
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            return $e->getJsonBody();
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return $e->getJsonBody();
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            return $e->getJsonBody();
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            return $e->getJsonBody();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            return $e->getJsonBody();
        }
    }

    protected function handleCustomerSubscriptionDeleted(array $payload) {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);

        if ($user) {
            $user->subscriptions->filter(function ($subscription) use ($payload) {
                return $subscription->stripe_id === $payload['data']['object']['id'];
            })->each(function ($subscription) {
                $subscription->markAsCancelled();
            });
        }

        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle a cancelled customer from a Stripe subscription.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleChargeSuccedded(array $payload) {
        $user = $this->getOrderByPayload($payload['data']['object']['customer']);

        if ($user) {

            $user->subscriptions->filter(function ($subscription) use ($payload) {
                return $subscription->stripe_id === $payload['data']['object']['id'];
            })->each(function ($subscription) {
                $subscription->markAsCancelled();
            });
        }

        return new Response('Webhook Handled', 200);
    }

    /**
     * Get the billable entity instance by Stripe ID.
     *
     * @param  string  $stripeId
     * @return \Laravel\Cashier\Billable
     */
    protected function getUserByStripeId($stripeId) {
        $model = getenv('STRIPE_MODEL') ?: config('services.stripe.model');

        return (new $model)->where('stripe_id', $stripeId)->first();
    }

    /**
     * Verify with Stripe that the event is genuine.
     *
     * @param  string  $id
     * @return bool
     */
    protected function eventExistsOnStripe($id) {
        try {
            return !is_null(StripeEvent::retrieve($id, config('services.stripe.secret')));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Verify if cashier is in the testing environment.
     *
     * @return bool
     */
    protected function isInTestingEnvironment() {
        return getenv('CASHIER_ENV') === 'testing';
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  array   $parameters
     * @return mixed
     */
    public function missingMethod($parameters = []) {
        return new Response;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorSource(array $data) {
        return Validator::make($data, [
                    'source' => 'required|max:255',
                    'default' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorDefault(array $data) {
        return Validator::make($data, [
                    'source' => 'required|max:255'
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorSubscriptionSource(array $data) {
        return Validator::make($data, [
                    'source' => 'required|max:255',
                    'default' => 'required|max:255',
                    'plan_id' => 'required|max:255',
                    'object_id' => 'required|max:255'
        ]);
    }

    public function validatorSubscriptionSourceClient(array $data) {
        return Validator::make($data, [
                    'source' => 'required|max:255',
                    'plan_id' => 'required|max:255',
                    'default' => 'required|max:255',
                    'object_id' => 'required|max:255'
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorUseSource(array $data) {
        return Validator::make($data, [
                    'source' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorSubscription(array $data) {
        return Validator::make($data, [
                    'plan_id' => 'required|max:255',
                    'object_id' => 'required|max:255',
        ]);
    }

}
