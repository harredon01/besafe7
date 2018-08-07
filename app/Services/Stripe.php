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
            $response = \Stripe\Customer::all(["limit" => 1, "email" => $user->email]);
            $customers = $response->data;
            $customer = null;
            if (count($customers) > 0) {
                $customer = $customers[0];
            } else {
                $customer = \Stripe\Customer::create(array(
                            "email" => $user->email
                ));
            }

            if ($customer->id) {
                $preitems = ["items" => json_encode([])];
                $subscription = \Stripe\Subscription::create(array(
                            "customer" => $customer->id,
                            "items" => array(
                                array(
                                    "plan" => "basic-plan",
                                ),
                            ),
                            "metadata" => $preitems,
                ));
                $source = new Source([
                    "gateway" => "Stripe",
                    "client_id" => $customer->id,
                    "extra" => $subscription->id
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
                return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
            }
            $token = $data['source'];
            $customer = \Stripe\Customer::retrieve($source->client_id);
            $customer->default_source = $token;
            $customer->save();
            $source->source = $token;
            $source->has_default = true;
            $source->save();
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
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }

        $customer = \Stripe\Customer::retrieve($source->client_id);
        $token = $data['source'];
        $customer->default_source = $token;
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

    public function createSubscriptionSourceClient(User $user, Plan $planL, array $data) {
        try {
            $validator = $this->validatorSubscriptionSource($data);
            if ($validator->fails()) {
                return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
            }
            $customer = \Stripe\Customer::create(array(
                        "email" => $user->email,
            ));

            if ($customer) {
                $token = $data['source']['id'];
                $preitems = ["items" => json_encode([])];
                $customer->sources->create(array("source" => $token));
                $subscription = \Stripe\Subscription::create(array(
                            "customer" => $customer->id,
                            "items" => array(
                                array(
                                    "plan" => "basic-plan",
                                ),
                            ),
                            "metadata" => $preitems,
                ));
                $source = new Source([
                    "gateway" => "Stripe",
                    "client_id" => $customer->id,
                    "source" => $token,
                    "extra" => $subscription->id,
                    "has_default" => true
                ]);
                $user->sources()->save($source);

                $subscriptionL = $this->handleCreateSubscription($customer, $source, $planL, $data);
                $user->subscriptions()->save($subscriptionL);
                $result = array("status" => "success", "message" => "Subscription Created", "subscription" => $subscriptionL);
                return $result;
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

    public function createSubscriptionSource(User $user, Source $source, Plan $planL, array $data) {

        try {
            $validator = $this->validatorSubscriptionSource($data);
            if ($validator->fails()) {
                return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
            }
            $customer = \Stripe\Customer::retrieve($source->client_id);
            if ($customer) {

                $token = $data['source'];
                $customer->sources->create(array("source" => $token));
                unset($data['source']);
                $source->source = $token;
                $source->has_default = true;
                $source->save();
                $subscriptionL = $this->handleCreateSubscription($customer, $source, $planL, $data);
                $user->subscriptions()->save($subscriptionL);
                $result = array("status" => "success", "message" => "Subscription Created", "subscription" => $subscriptionL);
                return $result;
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

    public function createSubscriptionExistingSource(User $user, Source $source, Plan $planL, array $data) {
        try {
            $validator = $this->validatorSubscriptionSource($data);
            if ($validator->fails()) {
                return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
            }
            $customer = \Stripe\Customer::retrieve($source->client_id);
            if ($customer) {
                $token = $data['source'];
                $source->source = $token;
                $source->has_default = true;
                $source->save();
                $customer->default_source = $token;
                $customer->save();
                unset($data['source']);
                $subscriptionL = $this->handleCreateSubscription($customer, $source, $planL, $data);
                $user->subscriptions()->save($subscriptionL);
                $result = array("status" => "success", "message" => "Subscription Created", "subscription" => $subscriptionL);
                return $result;
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

    private function handleCreateSubscription($customer, Source $source, Plan $planL, array $data) {

        $sub = \Stripe\Subscription::retrieve($source->extra);
        if ($sub) {
            $subItems = $sub["items"]["data"];
            $found = false;
            foreach ($subItems as $value) {

                if ($value['plan']['id'] == $planL->plan_id) {
                    $found = true;
                    $subscriptionItem = \Stripe\SubscriptionItem::retrieve($value['id']);
                    $quantity = $subscriptionItem->quantity;
                    $quantity++;
                    $subscriptionItem->quantity = $quantity;
                    $subscriptionItem->save();
                }
            }
            if (!$found) {
                $subscriptionItem = \Stripe\SubscriptionItem::create(array(
                            "subscription" => $source->extra,
                            "plan" => $planL->plan_id,
                            "quantity" => 1,
                ));
            }

            $subscriptionL = new Subscription([
                "gateway" => "Stripe",
                "status" => "active",
                "type" => $planL->type,
                "name" => $planL->name,
                "other" => $subscriptionItem->id,
                "plan" => $planL->plan_id,
                "plan_id" => $planL->id,
                "level" => $planL->level,
                "source_id" => $source->extra,
                "client_id" => $customer->id,
                "object_id" => $data['object_id'],
                "interval" => $planL->interval,
                "interval_type" => $planL->interval_type,
                "quantity" => 1,
                "ends_at" => Date($sub->current_period_end)
            ]);



            if ($sub->metadata) {
                $predata = $sub->metadata;
                if (!$predata) {
                    $predata = [];
                }
            } else {
                $predata = [];
            }

            $savedItems = [];

            if (array_key_exists('items', $predata)) {
                $savedItems = $predata['items'];
                if (is_string($savedItems)) {
                    $savedItems = json_decode($savedItems, true);
                    if (!$savedItems) {
                        $savedItems = [];
                    }
                }
            }
            if ($predata->items) {
                $savedItems = $predata->items;
                if (is_string($savedItems)) {
                    $savedItems = json_decode($savedItems, true);
                    if (!$savedItems) {
                        $savedItems = [];
                    }
                }
            }
            array_push($savedItems, [
                "item_id" => $subscriptionItem->id,
                "object_id" => $data['object_id'],
                "type" => $planL->type,
            ]);
            $predata['items'] = json_encode($savedItems);
            $sub->metadata = $predata;
            $sub->save();
            return $subscriptionL;
        }
    }

    public function createSubscription(User $user, Source $source, Plan $planL, array $data) {
        try {
            $validator = $this->validatorSubscription($data);
            if ($validator->fails()) {
                return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
            }
            $customer = \Stripe\Customer::retrieve($source->client_id);
            if ($customer) {
                if ($customer->default_source) {
                    $subscriptionL = $this->handleCreateSubscription($customer, $source, $planL, $data);
                    $user->subscriptions()->save($subscriptionL);
                    $result = array("status" => "success", "message" => "Subscription Created", "subscription" => $subscriptionL);
                    return $result;
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

    public function editSubscription(User $user, Source $source, Plan $planL, $subscriptionI, array $data) {
        $validator = $this->validatorEditSubscription($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        try {
            $subscription = $user->subscriptions()
                    ->where('gateway', "Stripe")
                    ->where("type", $data['type'])
                    ->where("object_id", $data['object_id'])
                    ->first();
            if ($subscription) {
                $sub = \Stripe\Subscription::retrieve($subscription->source_id);
                $subscriptionItemPast = \Stripe\SubscriptionItem::retrieve($subscription->other);

                if ($sub) {
                    $subItems = $sub["items"]["data"];
                    $found = false;
                    $newId = 0;
                    $oldId = 0;

                    for ($x = 0; $x < count($subItems); $x++) {
                        $value = $subItems[0];
                        if ($value['plan']['id'] == $planL->plan && $value["id"] != $subscription->other) {
                            $found = true;
                            $subscriptionItem = \Stripe\SubscriptionItem::retrieve($value['id']);
                            $quantity = $subscriptionItem->quantity;
                            $quantity++;
                            $newId = $subscriptionItem->id;
                            $subscriptionItem->quantity = $quantity;
                            $subscriptionItem->save();
                            $subscription->other = $subscriptionItem->id;
                        }
                    }
                    if (!$found) {
                        $subscriptionItem = \Stripe\SubscriptionItem::create(array(
                                    "subscription" => $subscription->source_id,
                                    "plan" => $planL->plan_id,
                                    "quantity" => 1,
                        ));
                        $newId = $subscriptionItem->id;
                        $subscription->other = $subscriptionItem->id;
                    }

                    if ($subscriptionItemPast->quantity > 1) {
                        $quantity = $subscriptionItemPast->quantity;
                        $quantity--;
                        $subscriptionItemPast->quantity = $quantity;
                        $subscriptionItemPast->save();
                    } else {
                        $subscriptionItemPast->delete();
                    }
                    $subItemsTemp = $sub->metadata;
                    if (array_key_exists("items", $subItemsTemp)) {
                        $subItems = json_decode($subItemsTemp['items'], true);
                    } else {
                        $subItems = [];
                        array_push($subItems, [
                            "item_id" => $subscriptionItem->id,
                            "object_id" => $subscription->object_id,
                            "type" => $planL->type,
                        ]);
                    }

                    for ($x = 0; $x < count($subItems); $x++) {
                        if ($subItems[$x]['object_id'] == $subscription->object_id && $subItems[$x]['type'] == $subscription->type) {
                            $subItems[$x]['item_id'] = $newId;
                        }
                    }
                    $subItemsTemp['items'] = json_encode($subItems);
                    $sub->metadata = $subItemsTemp;
                    $sub->save();
                }
                $subscription->status = "active";
                $subscription->name = $planL->name;
                $subscription->interval = $planL->interval;
                $subscription->plan_id = $planL->id;
                $subscription->plan = $planL->plan_id;
                $subscription->interval_type = $planL->interval_type;
                $subscription->quantity = 1;
                $subscription->ends_at = Date($sub->current_period_end);
                $subscription->save();
                $result = array("status" => "success", "message" => "Subscription Created", "subscription" => $subscription);
                return $result;
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
        $theType = "";
        $theObjectId = 0;
        try {
            $sub = \Stripe\SubscriptionItem::retrieve($subscription);
            $subMain = \Stripe\Subscription::retrieve($sub->subscription);
            $subItemsTemp = $subMain->metadata;
            if ($sub) {
                $sublocal = $user->subscriptions()->where('gateway', "Stripe")->where('other', $subscription)->first();
                if ($sublocal) {
                    $theType = $sublocal->type;
                    $theObjectId = $sublocal->object_id;
                    if ($sub->quantity > 1) {
                        $quant = $sub->quantity;
                        $quant--;
                        $sub->quantity = $quant;
                        $sub->save();
                    } else {
                        $sub->delete();
                    }
                    $sublocal->delete();
                }
            }

            if ($subMain) {
                if (array_key_exists("items", $subItemsTemp)) {
                    $subItems = json_decode($subItemsTemp['items'], true);
                } else {
                    $subItems = [];
                }
                for ($x = 0; $x < count($subItems); $x++) {
                    if ($subItems[$x]['object_id'] == $theObjectId && $subItems[$x]['type'] == $theType) {
                        unset($subItems[$x]);
                    }
                }
                $subItemsTemp['items'] = json_encode($subItems);
                $subMain->metadata = $subItemsTemp;
                $subMain->save();
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
                return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
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
        if (!$source) {
            $customer = \Stripe\Customer::retrieve($payload['data']['object']['customer']);
            $user = User::where('email' . $customer->email)->first();
            if ($user) {

                $items = $payload['data']['object']['items']['data'];
                $metadata = $payload['data']['object']['metadata'];
                if ($metadata) {

                    if (array_key_exists("items", $metadata)) {
                        $preitems = $metadata['items'];
                        if (is_string($preitems)) {
                            $preitems = json_decode($preitems, true);
                            if (!$preitems) {
                                $preitems = [];
                            }
                        }
                    } else {
                        $metadata['items'] = [];
                    }
                } else {
                    $metadata['items'] = [];
                }


                for ($i = 0; $i < count($preitems); $i++) {
                    $value = $items[$i];

                    $subscriptionL = Subscription::where('gateway', 'Stripe')->where('other', $value['id'])->first();
                    if ($subscriptionL) {
                        
                    } else {
                        $existingItem = $preitems[$i];
                        $existingItem['item_id'] = $value['id'];
                        $plan = $value['plan']['id'];
                        $planL = Plan::where('plan_id', $plan)->first();
                        if ($planL) {
                            $subscriptionL = Subscription::create([
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
                                        "object_id" => $existingItem['object_id'],
                                        "ends_at" => Date($payload['data']['object']['current_period_end'])
                            ]);
                            $class = "App\\Models\\" . $plan->type;
                            $model = $class::find($existingItem['object_id']);
                            if ($model) {
                                $model->plan = $subscriptionL->plan;
                                $model->ends_at = $subscriptionL->ends_at;
                                $model->status = "active";
                                $model->save();
                            }
                        }
                    }
                }
                $metadata['items'] = json_encode($metadata['items']);
                $subscription = \Stripe\Subscription::create(array(
                            "customer" => $customer->id,
                            "items" => array(
                                array(
                                    "plan" => "basic-plan",
                                ),
                            ),
                            "metadata" => $metadata,
                ));
                $source = new Source([
                    "gateway" => "Stripe",
                    "client_id" => $customer->id,
                    "extra" => $subscription->id
                ]);
                $user->sources()->save($source);
            } else {
                return new Response('Webhook Handled', 200);
            }
        }

        return new Response('Webhook Handled', 200);
    }

    protected function handleCustomerSubscriptionUpdated(array $payload) {
        $items = $payload['data']['object']['items']['data'];
        $metadata = $payload['data']['object']['metadata'];
        if ($metadata) {

            if (array_key_exists("items", $metadata)) {
                $preitems = $metadata['items'];
                if (is_string($preitems)) {
                    $preitems = json_decode($preitems, true);
                    if (!$preitems) {
                        $preitems = [];
                    }
                }
            } else {
                $metadata['items'] = [];
            }
        } else {
            $metadata['items'] = [];
        }
        for ($i = 0; $i < count($preitems); $i++) {
            $value = $items[$i];
            $existingItem = $preitems[$i];
            $subscriptionL = Subscription::where('gateway', 'Stripe')->where('other', $value['id'])->first();
            if ($subscriptionL) {
                $plan = $value['plan']['id'];
                $planL = Plan::where('plan_id', $plan)->first();
                if ($planL) {
                    $data = $payload['data']['object']['metadata'];
                    $subscriptionL->object_id = $existingItem['object_id'];
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
                    $target->status = "active";
                    $target->ends_at = Date($payload['data']['object']['current_period_end']);
                    if ($subscriptionL->type == "Group") {
                        $target->level = $planL->level;
                    }
                    $target->save();
                    $subscriptionL->save();
                    return new Response('Webhook Handled', 200);
                }
                return new Response('Plan not found', 400);
            } else {
                $existingItem = $preitems[$i];
                $existingItem['item_id'] = $value['id'];
                $plan = $value['plan']['id'];
                $planL = Plan::where('plan_id', $plan)->first();
                if ($planL) {
                    $subscriptionL = Subscription::create([
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
                                "object_id" => $existingItem['object_id'],
                                "ends_at" => Date($payload['data']['object']['current_period_end'])
                    ]);
                    $class = "App\\Models\\" . $plan->type;
                    $model = $class::find($existingItem['object_id']);
                    if ($model) {
                        $model->plan = $subscriptionL->plan;
                        $model->ends_at = $subscriptionL->ends_at;
                        $model->status = "active";
                        $model->save();
                    }
                }
            }
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
        $received = $payload['data']['object'];
        if (array_key_exists("lines", $received)) {
            $lines = $received['lines'];
            $source = Source::where('gateway', 'Stripe')->where('client_id', $received['customer'])->first();
            if (array_key_exists("data", $lines)) {
                if (count($lines['data'] > 0)) {
                    $subscription = $lines['data'][0];
                    $items = Subscription::where('gateway', 'Stripe')->where('source_id', $subscription['id'])->get();
                    foreach ($items as $subscriptionL) {
                        $subscriptionL->ends_at = Date($subscription['period']['ends']);
                        $objectType = "App\\Models\\" . $subscriptionL->type;
                        $object = new $objectType;
                        $target = $object->find($subscriptionL->object_id);
                        $target->status = "active";
                        $target->ends_at = Date($subscription['period']['ends']);
                        $target->save();
                        $subscriptionL->save();
                    }
                    Payment::create([
                        "gateway" => "Stripe",
                        "status" => "succedded",
                        "type" => "subscription",
                        "subtotal" => $received['subtotal'],
                        "tax" => $received['tax'],
                        "discount" => 0,
                        "total" => $received['total'],
                        'user_id' => $source->user_id,
                        "date" => Date($received['date']),
                        "payload" => json_encode($received)
                    ]);
                    return new Response('Webhook Handled', 200);
                }
            }
        }
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
