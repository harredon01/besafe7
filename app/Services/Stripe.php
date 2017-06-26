<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Source;
use App\Models\Subscription;
use Illuminate\Http\Response;
use Validator;
use Stripe\Event as StripeEvent;

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

    public function saveTransaction() {
        
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
                    "gateway" => "Stripe",
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
            if (array_key_exists("default", $data)) {
                if ($data["default"] == true) {
                    $source->source = $token;
                    $source->has_default = true;
                    $source->save();
                }
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

        $customer = \Stripe\Customer::retrieve($source->client_id);
        $token = $data['source'];
        $customer->source = $token;
        $customer->save();

        $source->source = $token;
        $source->has_default = true;
        $source->save();
    }

    public function getSources(Source $source) {
        $result = \Stripe\Customer::retrieve($source->client_id)->sources->all(array(
            'limit' => 20));
        $sources = $result['data'];
        $dest = array();
        foreach ($sources as $item) {
            if ($item->id == $source->source) {
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
            if ($source->source == $token) {
                $source->has_default = false;
                $source->source = null;
                $source->save();
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

    public function getSource(Source $source, $id) {
        $customer = \Stripe\Customer::retrieve($source->client_id);
        $customer->sources->retrieve($id);
    }

    public function createSubscriptionSourceClient(User $user,Plan $planL, array $data) {
        try {
            $validator = $this->validatorSubscriptionSource($data);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
            }
            $customer = \Stripe\Customer::create(array(
                        "email" => $user->email,
                        "source" => $data['source'],
            ));
            if ($customer) {

                    unset($data['source']);
                    $subscription = \Stripe\Subscription::create(array(
                                "customer" => $customer->id,
                                "plan" => $planL->plan_id,
                                "metadata" => $data,
                    ));
                    $source = new Source([
                        "gateway" => "Stripe",
                        "client_id" => $customer->id,
                        "source" => $data['source'],
                        "has_default" => true
                    ]);
                    $user->sources()->save($source);

                    $subscriptionL = new Subscription([
                        "gateway" => "Stripe",
                        "status" => "active",
                        "type" => $planL->type,
                        "name" => $planL->name,
                        "plan" => $planL->plan_id,
                        "plan_id" => $planL->id,
                        "level" => $planL->level,
                        "source_id" => $subscription->id,
                        "client_id" => $customer->id,
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

    public function createSubscriptionSource(User $user, Source $source,Plan $planL, array $data) {

        try {
            $validator = $this->validatorSubscriptionSource($data);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
            }
            $customer = \Stripe\Customer::retrieve($source->client_id);
            if ($customer) {

                    $token = $data['source'];
                    $customer->sources->create(array("source" => $token));
                    unset($data['source']);
                    $subscription = \Stripe\Subscription::create(array(
                                "customer" => $customer->id,
                                "plan" => $planL->plan_id,
                                "metadata" => $data,
                    ));

                    if ($data['default']) {
                        $source->source = $token;
                        $source->has_default = true;
                        $source->save();
                    }



                    $subscriptionL = new Subscription([
                        "gateway" => "Stripe",
                        "status" => "active",
                        "type" => $planL->type,
                        "name" => $planL->name,
                        "plan" => $planL->plan_id,
                        "plan_id" => $planL->id,
                        "level" => $planL->level,
                        "source_id" => $subscription->id,
                        "client_id" => $customer->id,
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

    public function createSubscriptionExistingSource(User $user, Source $source,Plan $planL, array $data) {
        try {
            $validator = $this->validatorSubscriptionSource($data);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
            }
            $customer = \Stripe\Customer::retrieve($source->client_id);
            if ($customer) {
                $token = $data['source'];
                unset($data['source']);

                    $subscription = \Stripe\Subscription::create(array(
                                "customer" => $customer->id,
                                'source' => $token,
                                "plan" => $planL->plan_id,
                                "metadata" => $data,
                    ));
                    if ($data['default']) {
                        $source->source = $token;
                        $source->has_default = true;
                        $source->save();
                    }
                    $subscriptionL = new Subscription([
                        "gateway" => "Stripe",
                        "status" => "active",
                        "type" => $planL->type,
                        "name" => $planL->name,
                        "plan" => $planL->plan_id,
                        "plan_id" => $planL->id,
                        "level" => $planL->level,
                        "source_id" => $subscription->id,
                        "client_id" => $customer->id,
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

    public function createSubscription(User $user, Source $source,Plan $planL, array $data) {
        try {
            $validator = $this->validatorSubscription($data);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
            }
            $customer = \Stripe\Customer::retrieve($source->client_id);
            if ($customer) {
                if ($customer->default_source) {

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
                            "plan" => $planL->plan_id,
                            "plan_id" => $planL->id,
                            "level" => $planL->level,
                            "source_id" => $subscription->id,
                            "client_id" => $customer->id,
                            "object_id" => $data['object_id'],
                            "interval" => $planL->interval,
                            "interval_type" => $planL->interval_type,
                            "quantity" => $data['quantity'],
                            "ends_at" => Date($subscription->current_period_end)
                        ]);
                        $user->subscriptions()->save($subscriptionL);
                        return $subscriptionL;
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

    public function editSubscription(User $user, Source $source,Plan $planL, $subscription, array $data) {
        $validator = $this->validatorEditSubscription($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        try {
            $sub = \Stripe\Subscription::retrieve($subscription);
            if ($sub) {
                $subscription = $user->subscriptions()->where('gateway', "Stripe")->where('source_id', $subscription)->first();
                if ($subscription) {
                        $sub->plan = $planL->plan_id;
                        $sub->save();
                        $subscription->status = "active";
                        $subscription->type = $planL->type;
                        $subscription->name = $planL->name;
                        if (array_key_exists("object_id", $data)) {
                            $subscription->object_id = $data['object_id'];
                        }
                        $subscription->interval = $planL->interval;
                        $subscription->interval_type = $planL->interval_type;
                        $subscription->quantity = 1;
                        $subscription->ends_at = Date($sub->current_period_end);
                        $subscription->save();
                        return $subscription;
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
                $source = $user->subscriptions()->where('gateway', "Stripe")->where('source_id', $subscription)->first();
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
        $result = \Stripe\Subscription::all(array('customer' => $source->client_id));
        return $result['data'];
    }

    public function useSource(User $user, Order $order, array $data) {
        try {
            $validator = $this->validatorUseSource($data);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
            }
            $sources = $user->sources()->where('gateway', "Stripe")->get();
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
        $subscriptionL = Subscription::where('gateway', 'Stripe')->where('source_id', $payload['data']['object']['id'])->first();
        if ($subscriptionL) {
            $subscriptionL->delete();
            return new Response('Webhook Handled', 200);
        }
        return new Response('Subscription not found', 400);
    }

    protected function handleCustomerSubscriptionCreated(array $payload) {
        $source = Source::where('gateway', 'Stripe')->where('client_id', $payload['data']['object']['customer'])->first();
        if ($source) {
            $plan = $payload['data']['object']['plan']['id'];
            $planL = Plan::where('plan_id', $plan)->first();
            if ($planL) {
                Subscription::create([
                    "gateway" => "Stripe",
                    "status" => "active",
                    "type" => $planL->type,
                    "name" => $planL->name,
                    "plan" => $planL->plan_id,
                    "plan_id" => $planL->id,
                    "level" => $planL->level,
                    "interval" => $planL->interval,
                    "interval_type" => $planL->interval_type,
                    'user_id' => $source->user_id,
                    "source_id" => $payload['data']['object']['id'],
                    "client_id" => $payload['data']['object']['customer'],
                    "ends_at" => Date($payload['data']['object']['current_period_end'])
                ]);
                return new Response('Webhook Handled', 200);
            }
            return new Response('Plan for subscription not found', 400);
        }
        return new Response('Source for Client not found', 400);
    }

    protected function handleCustomerSubscriptionUpdated(array $payload) {
        $subscriptionL = Subscription::where('gateway', 'Stripe')->where('source_id', $payload['data']['object']['id'])->first();
        if ($subscriptionL) {
            $plan = $payload['data']['object']['plan']['id'];
            $planL = Plan::where('plan_id', $plan)->first();
            if ($planL) {
                $data = $payload['data']['object']['metadata'];
                $subscriptionL->object_id = $data['object_id'];
                $subscriptionL->quantity = $data['quantity'];
                $subscriptionL->ends_at = Date($payload['data']['object']['current_period_end']);
                $subscriptionL->type = $planL->type;
                $subscriptionL->name = $planL->name;
                $subscriptionL->plan = $planL->plan_id;
                $subscriptionL->plan_id = $planL->id;
                $subscriptionL->level = $planL->level;
                $subscriptionL->interval = $planL->interval;
                $subscriptionL->interval_type = $planL->interval_type;
                $objectType = "App\\Models\\" . $subscriptionL->type;
                $object = new $objectType;
                $target = $object->find($data["object_id"]);
                $target->ends_at = Date($payload['data']['object']['current_period_end']);
                if ($subscriptionL->type == "Group") {
                    $target->level = $planL->level;
                }
                $target->save();
                $subscriptionL->save();
                return new Response('Webhook Handled', 200);
            }
            return new Response('Plan not found', 400);
        }
        return new Response('Subscription not found', 400);
    }

    /**
     * Handle a cancelled customer from a Stripe subscription.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleInvoicePaymentSuccedded(array $payload) {
        $subscriptionL = Subscription::where('gateway', 'Stripe')->where('source_id', $payload['data']['object']['id'])->first();
        if ($subscriptionL) {
            $subscriptionL->ends_at = Date($payload['data']['object']['current_period_end']);
            $objectType = "App\\Models\\" . $subscriptionL->type;
            $object = new $objectType;
            $target = $object->find($subscriptionL->object_id);
            $target->ends_at = Date($payload['data']['object']['current_period_end']);
            $target->save();
            $subscriptionL->save();
            return new Response('Webhook Handled', 200);
        }
        return new Response('Subscription not found', 400);
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
            $result = !is_null(StripeEvent::retrieve($id, config('services.stripe.secret')));
            return $result;
        } catch (Stripe_InvalidRequestError $e) {
            return false;
        } catch (Stripe_AuthenticationError $e) {
            return false;
            // (maybe you changed API keys recently)
        } catch (Stripe_ApiConnectionError $e) {
            return false;
        } catch (Stripe_Error $e) {
            return false;
            // yourself an email
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
        return new Response('Event not monitored', 200);
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

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorEditSubscription(array $data) {
        return Validator::make($data, [
                    'plan_id' => 'required|max:255',
        ]);
    }

}
