<?php

namespace App\Services;

use Validator;
use App\Models\Order;
use App\Models\OrderCondition;
use App\Models\Plan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailPaymentCash;
use App\Mail\EmailPaymentPse;
use App\Mail\EmailPaymentInBank;
use App\Services\EditGroup;
use App\Services\EditOrder;
use App\Services\EditCart;
use Cart;

class EditBilling {

    const TRANSACTION_CONDITION = 'transaction';

    /**
     * The EditAlert implementation.
     *
     */
    protected $editGroup;

    /**
     * The EditAlert implementation.
     *
     */
    protected $editOrder;

    /**
     * The EditAlert implementation.
     *
     */
    protected $editCart;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditGroup $editGroup, EditOrder $editOrder, EditCart $editCart) {
        $this->editGroup = $editGroup;
        $this->editOrder = $editOrder;
        $this->editCart = $editCart;
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

    public function retryPayment(User $user, $payment_id) {
        $payment = Payment::find($payment_id);
        if ($payment) {
            if ($user->id == $payment->user_id) {
                $payment->referenceCode = "payment_" . $payment->id . "_order_" . $payment->order_id . "_" . time();
                $payment->save();
                return ['status' => 'success', 'message' => "", "payment" => $payment];
            }
            return ['status' => 'error', 'message' => "Access denied"];
        }
        return ['status' => 'error', 'message' => "Payment not found"];
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

    public function getRawSources(User $user, $gateway) {
        $source = $user->sources()->where('gateway', $gateway)->first();
        if ($source) {
            return $source;
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
        /* foreach ($subsc as $item) {
          $objectType = "App\\Models\\" . $item->type;
          $object = new $objectType;
          $item->object = $object->find($item->object_id);
          } */
        return $subsc;
    }

    public function getSubscriptionsObject(User $user, $type, $object) {

        $subsc = array();
        if ($type == "user") {
            $subsc = Subscription::where("object_id", $user->id)->where("type", $type)->get();
        } else if ($type == "Group") {
            $users = $this->editGroup->checkAdminGroup($user->id, $object);
            if (count($users) == 1) {
                $subsc = Subscription::where("object_id", $object)->where("type", $type)->get();
            }
        } else {
            $subsc = Subscription::where("object_id", $object)->where("type", $type)->get();
        }
        return $subsc;
    }

    public function deleteSubscription(User $user, $gateway, $id) {
        $className = "App\\Services\\" . $gateway;
        $source = $user->sources()->where('gateway', strtolower($gateway))->first();
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
                if (array_key_exists("object_id", $data)) {
                    $subs = Subscription::where("object_id", $data['object_id'])
                                    ->where("type", $plan->type)->where("status", "active")->first();
                    if ($subs) {
                        return response()->json(['status' => 'error', 'message' => "Object subscription exists"]);
                    }
                }
                //$reply = $gateway->createPlan($plan);
                $class = "App\\Models\\" . $plan->type;
                $model = $class::find($data['object_id']);
                if ($model) {
                    $source = $user->sources()->where('gateway', $source)->first();
                    if ($source) {
                        if (array_key_exists("source", $data)) {
                            $result = $gateway->createSubscriptionExistingSource($user, $source, $plan, $data);
                        } else {
                            if (array_key_exists("new", $data)) {
                                $result = $gateway->createSubscriptionSource($user, $source, $plan, $data);
                            } else {
                                if ($source->has_default) {
                                    $result = $gateway->createSubscription($user, $source, $plan, $data);
                                } else {
                                    $result = $gateway->createSubscriptionSource($user, $source, $plan, $data);
                                }
                            }
                        }
                    } else {
                        $result = $gateway->createSubscriptionSourceClient($user, $plan, $data);
                    }
                    if (array_key_exists("status", $result)) {
                        if ($result['status'] == "success") {
                            $subscription = $result['subscription'];
                            $model->plan = $subscription->plan;
                            $model->status = "active";
                            $model->ends_at = $subscription->ends_at;
                            $model->save();
                            $result['model'] = $model;
                        }
                    }
                    return $result;
                }
                return response()->json(['status' => 'error', 'message' => "Model not found"]);
            }
            return response()->json(['status' => 'error', 'message' => "Plan not found"]);
        }
        return response()->json(['status' => 'error', 'message' => "Plan id is required"]);
    }

    public function editSubscription(User $user, $gateway, $id, array $data) {
        $className = "App\\Services\\" . $gateway;
        $data['trialDays'] = 0;
        $data['quantity'] = 1;
        if (array_key_exists("plan_id", $data)) {
            $plan = Plan::where("plan_id", $data['plan_id'])->first();
            if ($plan) {
                $source = $user->sources()->where('gateway', strtolower($gateway))->first();
                if ($source) {
                    $gateway = new $className;
                    $result = $gateway->editSubscription($user, $source, $plan, $id, $data);
                    if (array_key_exists("status", $result)) {
                        if ($result['status'] == "success") {
                            $class = "App\\Models\\" . $plan->type;
                            $model = $class::find($data['object_id']);
                            if ($model) {
                                $subscription = $result['subscription'];
                                $model->plan = $subscription->plan;
                                $model->status = "active";
                                $model->ends_at = $subscription->ends_at;
                                $model->save();
                                $result['model'] = $model;
                            }
                        }
                    }
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

    /**
     * Get the failed login message.
     *
     * @return string
     */
    public function saveCard(User $user, array $data, $source) {
        $className = "App\\Services\\" . $source;
        $gateway = new $className; //// <--- this thing will be autoloaded
        $gateway->createToken($user, $data);
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    public function extractBuyer(array $data) {
        $buyerAddress = [
            "buyer_address" => $data['buyer_address'],
            "buyer_city" => $data['buyer_city'],
            "buyer_state" => $data['buyer_state'],
            "buyer_country" => $data['buyer_country'],
            "buyer_postal" => $data['buyer_postal'],
            "buyer_phone" => $data['buyer_phone']
        ];
        return $buyerAddress;
    }

    public function payCreditCard(User $user, $source, array $data) {
        if (array_key_exists("payment_id", $data)) {
            $payment = Payment::find($data['payment_id']);

            if ($payment) {
                if ($payment->status == 'payment_in_bank') {
                    return array("status" => "error", "message" => "Payment must be in bank");
                }
                $this->changeOrderStatus($payment->order_id);
                $className = "App\\Services\\" . $source;
                $gateway = new $className; //// <--- this thing will be autoloaded
                $payment->referenceCode = "payment_" . $payment->id . "_order_" . $payment->order_id . "_" . time();
                $payment->status = "payment_created";
                if (array_key_exists('buyer_address', $data)) {
                    $payment->attributes = json_encode($this->extractBuyer($data));
                }

                $payment->save();
                return $gateway->useCreditCardOptions($user, $data, $payment, $data['platform']);
            }
        }
        return array("status" => "error", "message" => "Invalid order");
    }

    public function payInBank(User $user, $source, array $data) {
        if (array_key_exists("payment_id", $data)) {
            $payment = Payment::find($data['payment_id']);
            if ($payment) {
                $order = $payment->order()->with("items","orderConditions")->first();
                if (Payment::where("order_id",$payment->order_id)->count() == 1) {
                    $order->orderConditions()->where("type", self::TRANSACTION_CONDITION)->delete();
                    $order->total = $payment->total - $payment->transaction_cost;
                    $order->subtotal = $order->total - $order->shipping;
                    $order->save();
                    $this->changeOrderStatus($order->id);
                }
                $payment->total = $payment->total - $payment->transaction_cost;
                $payment->transaction_cost = 0;
                $payment->referenceCode = "payment_" . $payment->id . "_order_" . $payment->order_id . "_" . time();
                $payment->status = "payment_in_bank";
                if (array_key_exists('buyer_address', $data)) {
                    $payment->attributes = json_encode($this->extractBuyer($data));
                }
                $payment->save();
                $payment->order = $order;
                Mail::to($user)->send(new EmailPaymentInBank($payment, $user));
                return array("status" => "success", "message" => "Payment Created", "payment" => $payment);
            }
        }
        return array("status" => "error", "message" => "Invalid order");
    }

    public function addTransactionCosts(User $user, $payment_id) {
        $payment = Payment::find($payment_id);
        if ($payment) {
            if ($payment->user_id == $user->id) {
                if ($payment->status == "payment_in_bank") {
                    $order = $payment->order()->with("items","orderConditions")->first();
                    $payment->transaction_cost = $this->editOrder->getTransactionTotal($payment->total);
                    $payment->total = $payment->total + $payment->transaction_cost;
                    $payment->referenceCode = "payment_" . $payment->id . "_order_" . $payment->order_id . "_" . time();
                    $payment->status = "pending";
                    $payment->save();
                    if (Payment::where("order_id",$payment->order_id)->count() == 1) {
                        $conditionV = new OrderCondition(array(
                            'name' => "Costo variable transaccion",
                            'target' => "total",
                            'type' => self::TRANSACTION_CONDITION,
                            'value' => "3.49%",
                            'total' => $payment->transaction_cost-900,
                        ));
                        $conditionF = new OrderCondition(array(
                            'name' => "Costo fijo transaccion",
                            'target' => "subtotal",
                            'type' => self::TRANSACTION_CONDITION,
                            'value' => "+900",
                            'total' => 900,
                        ));
                        $order->orderConditions()->saveMany([$conditionF, $conditionV]);
                        $order->total = $payment->total;
                        $order->save();
                    }
                    $payment->order = $order;
                    return array("status" => "success", "message" => "Payment Created", "payment" => $payment);
                }
                return array("status" => "error", "message" => "Transaction cost cant be added to payment");
            }
        }
        return array("status" => "error", "message" => "Invalid payment");
    }

    private function changeOrderStatus($order_id) {
        $order = Order::find($order_id);
        if ($order) {
            if ($order->status == "pending") {
                $order->status = "payment_created";
                $order->save();
                $this->editCart->clearCartSession($order->user_id);
            }
        }
    }

    public function payDebitCard(User $user, $source, array $data) {
        if (array_key_exists("payment_id", $data)) {
            $payment = Payment::find($data['payment_id']);
            if ($payment) {
                if ($payment->status == 'payment_in_bank') {
                    return array("status" => "error", "message" => "Payment must be in bank");
                }
                $this->changeOrderStatus($payment->order_id);
                $className = "App\\Services\\" . $source;
                $gateway = new $className; //// <--- this thing will be autoloaded
                $payment->referenceCode = "payment_" . $payment->id . "_order_" . $payment->order_id . "_" . time();
                $payment->status = "payment_created";
                $payment->save();
                $results = $gateway->payDebitCard($user, $data, $payment, $data['platform']);
                $url = "";
                if (array_key_exists("email", $data)) {
                    if ($results['code'] == "SUCCESS") {
                        if ($results['transactionResponse']['state'] == "PENDING") {
                            $url = $results['transactionResponse']['extraParameters']['BANK_URL'];
                            Mail::to($user)->send(new EmailPaymentPse($payment, $user, $url));
                        }
                    }
                }
                return $results;
            }
        }
        return array("status" => "error", "message" => "Invalid order");
    }

    public function payCash(User $user, $source, array $data) {
        if (array_key_exists("payment_id", $data)) {
            $payment = Payment::find($data['payment_id']);

            if ($payment) {
                if ($payment->status == 'payment_in_bank') {
                    return array("status" => "error", "message" => "Payment must be in bank");
                }
                $this->changeOrderStatus($payment->order_id);
                $className = "App\\Services\\" . $source;
                $gateway = new $className; //// <--- this thing will be autoloaded
                $payment->referenceCode = "payment_" . $payment->id . "_order_" . $payment->order_id . "_" . time();
                $payment->status = "payment_created";
                $payment->save();
                $results = $gateway->payCash($user, $data, $payment, $data['platform']);
                $url = "";
                if (array_key_exists("email", $data)) {
                    if ($results['code'] == "SUCCESS") {
                        if ($results['transactionResponse']['state'] == "PENDING") {
                            $url = $results['transactionResponse']['extraParameters']['URL_PAYMENT_RECEIPT_HTML'];
                            $pdf = $results['transactionResponse']['extraParameters']['URL_PAYMENT_RECEIPT_PDF'];
                            Mail::to($user)->send(new EmailPaymentCash($payment, $user, $url, $pdf));
                        }
                    }
                }
                return $results;
            }
        }
        return array("status" => "error", "message" => "Invalid order");
    }

}
