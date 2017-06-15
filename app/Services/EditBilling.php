<?php

namespace App\Services;

use Validator;
use App\Models\Order;
use App\Models\Plan;
use App\Models\OrderAddress;
use App\Models\OrderPayment;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use App\Models\ProductVariant;
use App\Models\PaymentMethod;
use App\Models\Address;
use App\Services\PayU;
use App\Services\Stripe;
use Mail;
use Darryldecode\Cart\CartCondition;
use Cart;
use DB;

class EditBilling {

    /**
     * The Auth implementation.
     *
     */
    protected $auth;

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
    protected $stripe;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(PayU $payU, Stripe $stripe) {
        $this->payU = $payU;
        $this->stripe = $stripe;
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
        $sources = $user->sources()->where('gateway', strtolower($source))->get();
        $source = $sources[0];
        if ($source) {
            return $gateway->createSource($source, $data);
        } else {
            $source=$gateway->createClient($user);
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
        $sources = $user->sources()->where('gateway', strtolower($source))->get();
        $source = $sources[0];
        if ($source) {
            $gateway = new $className;
            return $gateway->getSource($source, $id);
        }
    }


    public function useSource(User $user, $source, array $data) {
        $className = "App\\Services\\" . $source;
        $gateway = new $className; //// <--- this thing will be autoloaded
        return $gateway->useSource($user, $data);
    }

    public function getSources(User $user, $source) {
        $sources = $user->sources()->where('gateway', strtolower($source))->get();
        $source = $sources[0];
        if ($source) {
            $className = "App\\Services\\" . $source;
            $gateway = new $className; //// <--- this thing will be autoloaded
            return $gateway->getSources($source);
        } else {
            return array();
        }
    }

    public function deleteSource(User $user, $source, $id) {
        $sources = $user->sources()->where('gateway', strtolower($source))->get();
        $source = $sources[0];
        if ($source) {
            $className = "App\\Services\\" . $source;
            $gateway = new $className; //// <--- this thing will be autoloaded
            $result = $gateway->deleteSource($source, $id);
            if($result['status']=="success"){
                if($source->source==$id){
                    $source->source = null;
                    $source->save();
                }
            }
            return $result;
        } 
    }

    public function getPlans() {
	    return Plan::all();
    }
    
    public function getSubscriptions(User $user, $source) {
        $sources = $user->sources()->where('gateway', strtolower($source))->get();
        $source = $sources[0];
        if ($source) {
            $className = "App\\Services\\" . $source;
            $gateway = new $className; //// <--- this thing will be autoloaded
            $result = [
                "locals" => $user->subscriptions(),
                "external" => $gateway->getSubscriptions($source),
            ];
            return $result;
        } else {
            return array();
        }
    }

    public function deleteSubscription(User $user, $source, $id) {
        $className = "App\\Services\\" . $source;
        $sources = $user->sources()->where('gateway', strtolower($source))->get();
        $source = $sources[0];
        if ($source) {
            $gateway = new $className;
            return $gateway->deleteSubscription($user, $id);
        } 
    }

    public function createSubscription(User $user, $source, array $data) {
        $className = "App\\Services\\" . $source;
        $gateway = new $className;
        $sources = $user->sources()->where('gateway', strtolower($source))->get();
        $source = $sources[0];
        $data['trialDays'] = 0;
        $data['quantity'] = 1;
        if ($source) {
            if ($source->source) {
                return $gateway->createSubscription($user, $source, $data);
            } else {
                return $gateway->createSubscriptionSource($user, $source, $data);
            }
        } else {
            return $gateway->createSubscriptionSourceClient($user, $data);
        }
    }
    public function createSubscriptionExistingSource(User $user, $source, array $data) {
        $className = "App\\Services\\" . $source;
        
        $sources = $user->sources()->where('gateway', strtolower($source))->get();
        $source = $sources[0];
        $data['trialDays'] = 0;
        $data['quantity'] = 1;
        if ($source) {
            $gateway = new $className;
            $gateway->createSubscriptionExistingSource($user, $source, $data);
        } else {
            return null;
        }
    }

    public function editSubscription(User $user, $source, $id, array $data) {
        $className = "App\\Services\\" . $source;
        $data['trialDays'] = 0;
        $data['quantity'] = 1;
        $sources = $user->sources()->where('gateway', strtolower($source))->get();
        $source = $sources[0];
        if ($source) {
            $gateway = new $className;
            return $gateway->editSubscription($user,$source, $id, $data);
        }
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
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorSubscription(array $data) {
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
