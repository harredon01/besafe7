<?php

namespace App\Services;

use Validator;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\EditGroup;

class EditBilling {

    /**
     * The EditAlert implementation.
     *
     */
    protected $editGroup;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditGroup $editGroup) {
        $this->editGroup = $editGroup;
    }

    public function processModel(User $user, array $data) {
        $class = "App\\Models\\" . $data["model"];
        $model = $class::find($data['id']);
        $interval = $data['interval'];
        $interval_type = $data['interval_type'];
        if ($model->isActive()) {
            $date = $model->ends_at;
        } else {
            $date = date("Y-m-d");
        }

        //increment 
        $mod_date = strtotime($date . "+ " . $interval . " " . $interval_type);
        $newdate = date("Y-m-d", $mod_date);
        $model->ends_at = $newdate;
        $model->save();
    }

    /* public function createClient(User $user, $source) {
      $className = "App\\Services\\" . $source;
      $gateway = new $className; //// <--- this thing will be autoloaded
      return $gateway->createClient($user);
      } */

    public function createSource(User $user, $source, array $data) {
        $className = "App\\Services\\" . $source;
        $gateway = new $className; //// <--- this thing will be autoloaded
        $source = $user->sources()->where('gateway', strtolower($source))->first();
        if ($source) {
            return $gateway->createSource($source, $data);
        } else {
            $source = $gateway->createClient($user);
            return $gateway->createSource($source, $data);
        }
    }

    public function editSource(User $user, $source, $id, array $data) {
//        $className = "App\\Services\\" . $source;
//        $sources = $user->sources()->where('gateway', strtolower($source))->get();
//        $source = $sources[0];
//        if ($source) {
//            $gateway = new $className;
//            return $gateway->editSource($user,$source, $id, $data);
//        }
    }

    public function getSource(User $user, $source, $id, array $data) {
        $className = "App\\Services\\" . $source;
        $source = $user->sources()->where('gateway', strtolower($source))->first();
        if ($source) {
            $gateway = new $className;
            return $gateway->getSource($source, $id);
        }
    }

    public function setAsDefault(User $user, $source, array $data) {
        $className = "App\\Services\\" . $source;
        $source = $user->sources()->where('gateway', strtolower($source))->first();
        if ($source) {
            $gateway = new $className;
            return $gateway->setAsDefault($source, $data);
        }
    }

    public function useSource(User $user, $source, array $data) {
        $className = "App\\Services\\" . $source;
        $gateway = new $className; //// <--- this thing will be autoloaded
        return $gateway->useSource($user, $data);
    }

    public function getSources(User $user, $source) {
        $className = "App\\Services\\" . $source;
        $source = $user->sources()->where('gateway', strtolower($source))->first();
        if ($source) {
            $gateway = new $className; //// <--- this thing will be autoloaded
            return $gateway->getSources($source);
        } else {
            return array();
        }
    }

    public function deleteSource(User $user, $source, $id) {
        $className = "App\\Services\\" . $source;
        $source = $user->sources()->where('gateway', strtolower($source))->first();
        if ($source) {
            $gateway = new $className; //// <--- this thing will be autoloaded
            $result = $gateway->deleteSource($source, $id);
            return $result;
            if (array_key_exists("status", $result)) {
                if ($result['status'] == "success") {
                    if ($source->source == $id) {
                        $source->source = null;
                        $source->save();
                    }
                }
            }
            return $result;
        }
    }

    public function getPlans() {
        return Plan::all();
    }

    public function getSubscriptions(User $user) {
        $subsc = $user->subscriptions;
        foreach ($subsc as $item) {
            $objectType = "App\\Models\\" . $item->type;
            $object = new $objectType;
            $item->object = $object->find($item->object_id);
        }
        return $subsc;
    }

    public function getSubscriptionsObject(User $user, $type, $object) {

        $subsc = array();
        if ($type == "user") {
            $subsc = Subscription::where("object_id", $user->id)->where("type", $type)->get();
        } else if ($type == "group") {
            $users = $this->editGroup->checkAdminGroup($user->id, $object);
            if (count($users) == 1) {
                $subsc = Subscription::where("object_id", $object)->where("type", $type)->get();
            }
        }
        return $subsc;
    }

    public function deleteSubscription(User $user, $source, $id) {
        $className = "App\\Services\\" . $source;
        $source = $user->sources()->where('gateway', strtolower($source))->first();
        if ($source) {
            $gateway = new $className;
            return $gateway->deleteSubscription($user, $id);
        }
    }

    public function createSubscription(User $user, $source, array $data) {
        $className = "App\\Services\\" . $source;
        $gateway = new $className;
        if (array_key_exists("plan_id", $data)) {
            $plan = Plan::where("plan_id", $data['plan_id'])->first();
            if ($plan) {
                //$reply = $gateway->createPlan($plan);
                $class = "App\\Models\\" . $plan->type;
                $model = $class::find($data['object_id']);
                if ($model) {
                    $source = $user->sources()->where('gateway', strtolower($source))->first();
                    if ($source) {
                        if ($source->has_default) {
                            if (array_key_exists("source", $data)) {
                                $result = $gateway->createSubscriptionExistingSource($user, $source, $plan, $data);
                            } if (array_key_exists("new", $data)) {
                                $result = $gateway->createSubscriptionSource($user, $source, $plan, $data);
                            } else {
                                $result = $gateway->createSubscription($user, $source, $plan, $data);
                            }
                        } else {
                            $result = $gateway->createSubscriptionSource($user, $source, $plan, $data);
                        }
                    } else {
                        $result = $gateway->createSubscriptionSourceClient($user, $plan, $data);
                    }
                    if ($result['status'] == "success") {
                        $subscription = $result['subscription'];
                        $model->ends_at = $subscription->ends_at;
                        $model->save();
                    }
                    return $result;
                }
                return response()->json(['status' => 'error', 'message' => "Model not found"]);
            }
            return response()->json(['status' => 'error', 'message' => "Plan not found"]);
        }
        return response()->json(['status' => 'error', 'message' => "Plan id is required"]);
    }

    public function editSubscription(User $user, $source, $id, array $data) {
        $className = "App\\Services\\" . $source;
        $data['trialDays'] = 0;
        $data['quantity'] = 1;
        if (array_key_exists("plan_id", $data)) {
            $plan = Plan::where("plan_id", $data['plan_id'])->first();
            if ($plan) {
                $source = $user->sources()->where('gateway', strtolower($source))->first();
                if ($source) {
                    $gateway = new $className;
                    return $gateway->editSubscription($user, $source, $plan, $id, $data);
                }
                return response()->json(['status' => 'error', 'message' => "Model not found"]);
            }
            return response()->json(['status' => 'error', 'message' => "Plan not found"]);
        }
        return response()->json(['status' => 'error', 'message' => "Plan id is required"]);
    }

    public function receiveSubscriptionPayment(User $user, $source, array $data) {
        $className = "App\\Services\\" . $source;
        $gateway = new $className; //// <--- this thing will be autoloaded
        return $gateway->makeCharge($user, $data);
        return array("status" => "error", "message" => "Empty Cart");
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
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorSource(array $data) {
        return Validator::make($data, [
                    'product_id' => 'required|max:255',
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
