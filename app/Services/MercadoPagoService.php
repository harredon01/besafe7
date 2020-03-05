<?php

namespace App\Services;

use Validator;
use App\Models\Payment;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\City;
use App\Models\Plan;
use App\Models\Country;
use App\Models\Region;
use App\Jobs\ApprovePayment;
use App\Jobs\DenyPayment;
use App\Jobs\PendingPayment;
use App\Jobs\SaveCard;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailPaymentCash;
use App\Mail\EmailPaymentPse;
use App\Models\Source;
use Carbon\Carbon;
use App\Models\Subscription;
use App\Models\Transaction;
use MercadoPago;
use MercadoPago\SDK;
use MercadoPago\Customer;
use MercadoPago\Card;
use MercadoPago\Payment as Pago;

class MercadoPagoService {

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $mercado;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $notifications;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct() {
        SDK::setAccessToken(env("MERCADOPAGO_TOKEN"));
        $this->notifications = app('Notifications');
    }

    public function payCreditCardT(User $user, array $data) {
        $localPayment = Payment::find($data["payment_id"]);
        $this->useCreditCardOptions($user, $data, $localPayment, "Mevico");
    }

    public function sendGet($query) {
        $curl = curl_init($query);
        $auth = base64_encode(env('PAYU_LOGIN') . ":" . env('PAYU_KEY'));
        $headers = array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ',
            'Accept: application/json',
            'Accept-language: es',
            'Authorization: Basic ' . $auth,
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    public function getPaymentMethods() {
        $url = "https://api.mercadopago.com/v1/payment_methods?access_token=" . env("MERCADOPAGO_TOKEN");
        return $this->sendGet($url);
    }
    public function getPayment($payment_id) {
        $url = "https://api.mercadopago.com/v1/payments/".$payment_id."?access_token=" . env("MERCADOPAGO_TOKEN");
        return $this->sendGet($url);
    }

    public function useCreditCardOptions(User $user, array $data, Payment $payment, $platform) {
        if (array_key_exists("quick", $data)) {
            return $this->quickPayCreditCard($user, $data, $payment, $platform);
        }
        $paymentResult = $this->payCreditCard($user, $data, $payment, $platform);
        if ($paymentResult['status'] == "success") {
            if (array_key_exists("save_card", $data)) {
                if ($data['save_card']) {
                    //dispatch(new SaveCard($user, $data, "MercadoPago"));
                    return $this->createToken($user, $data, $paymentResult);
                }
            }
        }

        return $paymentResult;
    }

    public function quickPayCreditCard(User $user, array $data, Payment $payment, $platform) {
        $source = $user->sources()->where("gateway", "MercadoPago")->first();
        if ($source) {
            
        } else {
            return response()->json(array("status" => "error", "message" => "No default card"), 400);
        }
        $validator = $this->validatorQuickPayment($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $paymentM = new Pago();
        $paymentM->transaction_amount = $payment->total;
        $paymentM->external_reference = $payment->referenceCode;
        $paymentM->token = $data["token"];
        $paymentM->issuer_id = $data["issuer_id"];
        $paymentM->installments = $data["installments"];
        $paymentM->description = "Pago Mevico app # " . $payment->id;
        $paymentM->payer = array(
            "type" => "customer",
            "id" => $source->client_id
        );
        // Save and posting the payment
        $paymentM->save();
        return $this->handleTransactionResponse($paymentM, $user, $payment, $platform);
    }

    public function payCreditCard(User $user, array $data, Payment $payment, $platform) {
        $validator = $this->validatorPayment($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }

        $paymentM = new Pago();
        $paymentM->transaction_amount = $payment->total;
        $paymentM->token = $data["token"];
        $paymentM->description = "Pago Mevico app # " . $payment->id;
        $paymentM->installments = $data["installments"];
        $paymentM->external_reference = $payment->referenceCode;
        $paymentM->issuer_id = $data["issuer_id"];
        $paymentM->payment_method_id = $data["payment_method_id"];
        $paymentM->payer = array(
            "email" => $data["email"]
        );
        // Save and posting the payment
        $paymentM->save();
        return $this->handleTransactionResponse($paymentM, $user, $payment, $platform);
    }

    public function useSource(User $user, array $data, Payment $payment, $platform) {
        return $this->quickPayCreditCard($user, $data, $payment, $platform);
    }

    public function payDebitCard(User $user, array $data, Payment $payment, $platform) {

        $validator = $this->validatorDebit($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $paymentM = new Pago();
        $paymentM->transaction_amount = $payment->total;
        $paymentM->external_reference = $payment->referenceCode;
        $paymentM->description = "Pago Mevico app # " . $payment->id;
        $paymentM->external_reference = $payment->referenceCode;
        $paymentM->payer = array(
            "email" => $data["email"],
            "identification" => array(
                "type" => $data["doc_type"],
                "number" => $data["payer_id"]
            ),
            "entity_type" => "individual"
        );
        $paymentM->transaction_details = array(
            "financial_institution" => $data["financial_institution"]
        );
        $paymentM->additional_info = array(
            "ip_address" => $data['ip_address']
        );
        $paymentM->callback_url = "http://www.your-site.com";
        $paymentM->payment_method_id = "pse";
        $paymentM->save();
        $url = "";
        if ($paymentM->status_detail == 'pending_waiting_transfer') {
            $transaction = $paymentM->transaction_details;
            $url = $transaction->external_resource_url;
            Mail::to($user)->send(new EmailPaymentPse($payment, $user, $url));
        }
        return ["status" => "success","response" => $paymentM->status, "status_detail" => $paymentM->status_detail, "url" => $url];
    }

    public function payCash(User $user, array $data, Payment $payment, $platform) {
        $validator = $this->validatorCash($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }

        $paymentM = new Pago();
        $paymentM->transaction_amount = $payment->total;
        $paymentM->external_reference = $payment->referenceCode;
        $paymentM->description = "Pago Mevico app # " . $payment->id;
        $paymentM->payer = array(
            "email" => $data["email"]
        );
        $paymentM->payment_method_id = $data["payment_method_id"];
        $paymentM->save();
        if ($paymentM->status_detail == 'pending_waiting_payment') {
            $transaction = $paymentM->transaction_details;
            $url = $transaction->external_resource_url;
            Mail::to($user)->send(new EmailPaymentCash($payment, $user, $url, null));
        }
        return ["status" => "success","response" => $paymentM->status, "status_detail" => $paymentM->status_detail, "url" => $url];
    }

    public function makeCharge(User $user, Order $order, array $payload) {
        $method = 'pay' . studly_case(str_replace('.', '_', $payload['type']));
        if (method_exists($this, $method)) {
            $result = $this->{$method}($user, $payload, $order);
            return $this->saveTransaction($result);
            ;
        } else {
            return $this->missingMethod();
        }
    }

    public function getBanks() {
        $methods = $this->getPaymentMethods();
        foreach ($methods as $item) {
            if ($item['id'] == "pse") {
                return $item['financial_institutions'];
            }
        }
        return null;
    }

    public function getOffsite() {
        $methods = $this->getPaymentMethods();
        $offsite = [];
        foreach ($methods as $item) {
            if ($item['payment_type_id'] == "ticket") {
                array_push($offsite, $item);
            }
        }
        return $offsite;
    }

    public function createCard(Source $source, array $data) {
        $validator = $this->validatorSource($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $user = $source->user;
        $response = $this->createToken($user, $data);
        return $response;
    }

    public function editCard(Source $source, array $data) {
        
    }

    public function deleteCard(Source $source, $cardId) {
        $cards = $this->getCards($source);
        foreach ($cards as $item) {
            if ($item->id == $cardId) {
                $item->delete();
                return true;
            }
        }
    }

    public function getCards(Source $source) {
        $client = $this->getClient($source);
        if ($client) {
            return $client->cards;
        }
        return array();
    }

    public function createToken(User $user, array $data, $paymentResult) {
        $source = $user->sources()->where("gateway", "MercadoPago")->first();
        $customer = null;
        if ($source) {
            $customer = $this->getClient($source);
        }
        if ($customer) {
            $card = new Card();
            $card->token = $data["token"];
            $card->customer_id = $customer->id;
            $card->save();
        } else {
            $results = $this->createClient($user);
            $customer = $results['customer'];
            $source = $results['source'];
            $card = new Card();
            $card->token = $data["token"];
            $card->customer_id = $customer->id;
            $card->save();
        }
        if ($source) {
            $source->source = $data["token"];
            $source->extra = json_encode($card);
            $source->save();
        } else {
            $source = new Source([
                "gateway" => "MercadoPago",
                "client_id" => $customer->id,
                "source" => $data["token"],
                "has_default" => true,
                "extra" => json_encode($card)
            ]);
            $user->sources()->save($source);
        }
        $paymentResult['card'] = $card;
        return $paymentResult;
    }

    public function deleteToken(User $user) {
        $source = $user->sources()->where("gateway", "PayU")->first();
        if ($source) {
            $merchant = $this->populateMerchant($user);
            $creditCardToken = [
                "payerId" => $user->id,
                "creditCardTokenId" => $source->source,
            ];
            $dataSent = [
                "language" => "es",
                "command" => "REMOVE_TOKEN",
                "merchant" => $merchant,
                "removeCreditCardToken" => $creditCardToken,
                "test" => false,
            ];

            $result = $this->sendRequest($dataSent, $this->getTestUrl($user) . env('PAYU_PAYMENTS'));
            if ($result['code'] == "SUCCESS") {
                $source->source = "";
                $source->has_default = false;
                $source->save();
            }
            return null;
        }
    }

    public function getSource(Source $source, $token) {
        return null;
    }

    public function setAsDefault(Source $source, array $data) {
        // Token is created using Stripe.js or Checkout!
        // Get the payment token submitted by the form:
        $validator = $this->validatorDefault($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $token = $data['source'];
        $source->source = $token;
        $source->has_default = true;
        $source->save();
    }

    public function createClient(User $user) {
        $customer = new Customer();
        $customer->email = $user->email;
        $customer->save();
        if ($customer->id) {
            
        } else {
            $filters = array(
                "email" => $user->email
            );
            $customers = Customer::search($filters);
            if(count($customers)>0){
                $customer = $customers[0];
            } else {
                return $customer;
            }
            
        }
        $source = $user->sources()->where("gateway", "MercadoPago")->first();
        if ($source) {
            $source->client_id = $customer->id;
            $source->save();
        } else {
            $source = new Source([
                "gateway" => "MercadoPago",
                "client_id" => $customer->id
            ]);
            $user->sources()->save($source);
        }
        return ["customer" => $customer, "source" => $source];
    }

    public function getClient(Source $source) {
        $customer = Customer::find_by_id($source->client_id);
        if ($customer) {
            return $customer;
        }
        $user = $source->user;
        $filters = array(
            "email" => $user->email
        );
        $customers = Customer::search($filters);
        $results = $customers->results;
        if (count($results) > 0) {
            $source->client_id = $results[0]->id;
            $source->save();
            return $results[0];
        }
        return null;
    }

    public function deleteClient(User $user, $client) {
        $customer = Customer::find_by_id($client);
        if ($customer) {
            $customer->delete();
            return $user->sources()->where('gateway', "MercadoPago")->where('client_id', $client)->delete();
        }
    }

    public function getSubscriptions(Source $source) {
        $client = $this->getClient($source);
        if ($client) {
            return $client['subscriptions'];
        }
        return null;
    }

    public function editSubscription(User $user, Source $source, Plan $planL, $id, array $data) {
        $subscription = $user->subscriptions()->where('gateway', 'PayU')->where('source_id', $id)->first();
        if ($subscription) {
            if ($subscription->isActive()) {
                $datetime1 = strtotime($subscription->ends_at);
                $datetime2 = strtotime("now");
                $secs = $datetime2 - $datetime1; // == return sec in difference
                $days = $secs / 86400;
            } else {
                $days = 0;
            }
            $url = $this->getTestUrl($user) . env('PAYU_REST') . 'subscriptions/';
            $plan = [
                "planCode" => $planL->plan_id
            ];

            $creditCard = [
                "token" => $subscription->other
            ];
            $creditCards = array($creditCard);
            $customer = [
                "id" => $source->client_id,
                "creditCards" => $creditCards,
            ];
            $dataSent = [
                "quantity" => 1,
                "trialDays" => $data['trialDays'],
                "customer" => $customer,
                "plan" => $plan
            ];
            if (array_key_exists("installments", $data)) {
                $dataSent["installments"] = $data['installments'];
            }

            $response = $this->sendPost($dataSent, $url);
            if (array_key_exists("id", $response)) {
                $url = $this->getTestUrl($user) . env('PAYU_REST') . 'subscriptions/' . $subscription->source_id;
                $this->sendDelete($url);
                $subscription->gateway = "PayU";
                $subscription->status = "active";
                $subscription->type = $planL->type;
                $subscription->name = $planL->name;
                $subscription->plan_id = $planL->id;
                $subscription->plan = $planL->plan_id;
                $subscription->source_id = $response['id'];
                $subscription->client_id = $source->client_id;
                $subscription->interval = $planL->interval;
                $subscription->interval_type = $planL->interval_type;
                $subscription->quantity = $data['quantity'];
                $subscription->ends_at = Date($response['currentPeriodEnd'] / 1000);
                $subscription->save();
                $result = array("status" => "success", "message" => "Subscription Created", "subscription" => $subscription);
                return $result;
            }
        }
    }

    public function createSubscription(User $user, Source $source, Plan $planL, array $data) {
        $validator = $this->validatorSubscription($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $url = $this->getTestUrl($user) . env('PAYU_REST') . 'subscriptions/';
        $plan = [
            "planCode" => $planL->plan_id
        ];

        $creditCard = [
            "token" => $source->source
        ];
        $creditCards = array($creditCard);
        $customer = [
            "id" => $source->client_id,
            "creditCards" => $creditCards,
        ];
        $dataSent = [
            "quantity" => 1,
            "installments" => 1,
            "trialDays" => 0,
            "customer" => $customer,
            "plan" => $plan
        ];
        $response = $this->sendPost($dataSent, $url);
        if (array_key_exists("id", $response)) {
            $subscription = new Subscription([
                "gateway" => "PayU",
                "status" => "active",
                "type" => $planL->type,
                "name" => $planL->name,
                "other" => $source->source,
                "plan" => $planL->plan_id,
                "plan_id" => $planL->id,
                "level" => $planL->level,
                "source_id" => $response['id'],
                "client_id" => $source->client_id,
                "object_id" => $data['object_id'],
                "interval" => $planL->interval,
                "interval_type" => $planL->interval_type,
                "quantity" => 1,
                "ends_at" => Date($response['currentPeriodEnd'] / 1000)
            ]);
            $user->subscriptions()->save($subscription);
            $response["status"] = "success";
            $response["subscription"] = $subscription;
            return $response;
        }
        return response()->json(['status' => 'error', 'response' => $response]);
    }

    public function createPlan(Plan $planL) {
        $url = $this->getTestUrl(null) . env('PAYU_REST') . 'plans/';
        $additionalValues = [
            "name" => "PLAN_VALUE",
            "value" => "20000",
            "currency" => "COP"
        ];

        $dataSent = [
            "accountId" => "512321",
            "planCode" => $planL->plan_id,
            "description" => 0,
            "interval" => "MONTH",
            "intervalCount" => "1",
            "maxPaymentsAllowed" => "12",
            "paymentAttemptsDelay" => "1",
            "additionalValues" => array($additionalValues),
        ];
        $response = $this->sendPost($dataSent, $url);
        return $response;
    }

    public function createSubscriptionExistingSource(User $user, Source $source, Plan $planL, array $data) {
        $validator = $this->validatorSubscriptionExisting($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $url = $this->getTestUrl($user) . env('PAYU_REST') . 'subscriptions/';
        $plan = [
            "planCode" => $planL->plan_id
        ];

        $creditCard = [
            "token" => $data['source']
        ];
        $creditCards = array($creditCard);
        $customer = [
            "id" => $source->client_id,
            "creditCards" => $creditCards,
        ];
        $dataSent = [
            "quantity" => 1,
            "installments" => 1,
            "trialDays" => 0,
            "customer" => $customer,
            "plan" => $plan
        ];
        $response = $this->sendPost($dataSent, $url);
        if (array_key_exists("id", $response)) {
            $subscription = new Subscription([
                "gateway" => "PayU",
                "status" => "active",
                "type" => $planL->type,
                "name" => $planL->name,
                "plan" => $planL->plan_id,
                "other" => $data['source'],
                "plan_id" => $planL->id,
                "level" => $planL->level,
                "source_id" => $response['id'],
                "client_id" => $source->client_id,
                "object_id" => $data['object_id'],
                "interval" => $planL->interval,
                "interval_type" => $planL->interval_type,
                "quantity" => 1,
                "ends_at" => Date($response['currentPeriodEnd'] / 1000)
            ]);
            $user->subscriptions()->save($subscription);
            $response["status"] = "success";
            $response["subscription"] = $subscription;
            return $response;
        }
    }

    public function createSubscriptionSource(User $user, Source $source, Plan $planL, array $data) {
        $validator = $this->validatorSubscriptionSource($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $url = $this->getTestUrl($user) . env('PAYU_REST') . 'subscriptions/';
        $plan = [
            "planCode" => $planL->plan_id
        ];
        $address = [
            "line1" => $data['line1'],
            "line2" => "",
            "line3" => "",
            "postalCode" => $data['postalCode'],
            "city" => $data['city'],
            "state" => $data['state'],
            "country" => $data['country'],
            "phone" => $data['phone']
        ];

        $creditCard = [
            "name" => $data['name'],
            "document" => $data['document'],
            "number" => $data['number'],
            "expMonth" => $data['expMonth'],
            "expYear" => "20" . $data['expYear'],
            "type" => $data['branch'],
            "address" => $address
        ];
        $creditCards = array($creditCard);
        $customer = [
            "id" => $source->client_id,
            "creditCards" => $creditCards,
        ];
        $dataSent = [
            "quantity" => 1,
            "installments" => 1,
            "trialDays" => 0,
            "customer" => $customer,
            "plan" => $plan
        ];
        $response = $this->sendPost($dataSent, $url);
        $tokenCreated = "";
        if (array_key_exists("customer", $response)) {
            $customerReply = $response['customer'];
            $response['status'] = "success";
            $response["subscription"] = $subscription;
            if (array_key_exists("creditCards", $customerReply)) {
                $card = $customerReply['creditCards'][0];
                if ($card) {
                    $tokenCreated = $card['token'];
                    if (array_key_exists("default", $data)) {
                        if ($data["default"]) {
                            $source->source = $card['token'];
                            $source->has_default = true;
                            $source->save();
                        }
                    }
                }
            }
        }
        if (array_key_exists("id", $response)) {
            $subscription = new Subscription([
                "gateway" => "PayU",
                "status" => "active",
                "type" => $planL->type,
                "name" => $planL->name,
                "plan" => $planL->plan_id,
                "plan_id" => $planL->id,
                "level" => $planL->level,
                "other" => $tokenCreated,
                "source_id" => $response['id'],
                "client_id" => $source->client_id,
                "object_id" => $data['object_id'],
                "interval" => $planL->interval,
                "interval_type" => $planL->interval_type,
                "quantity" => 1,
                "ends_at" => Date($response['currentPeriodEnd'] / 1000)
            ]);
            $user->subscriptions()->save($subscription);
        }

        return $response;
    }

    public function createAll(User $user, array $data) {
        $planL = Plan::where("plan_id", $data['plan_id'])->first();
        $source = $user->sources()->where("gateway", "PayU")->first();
        $url = $this->getTestUrl($user) . env('PAYU_REST') . 'subscriptions/';
        $additionalValues = [
            "name" => "PLAN_VALUE",
            "value" => "20000",
            "currency" => "COP"
        ];

        $plan = [
            "accountId" => "512321",
            "planCode" => $planL->plan_id,
            "description" => $planL->name,
            "interval" => "MONTH",
            "intervalCount" => "1",
            "maxPaymentsAllowed" => "12",
            "maxPaymentAttempts" => "3",
            "paymentAttemptsDelay" => "1",
            "maxPendingPayments" => "1",
            "trialDays" => "30",
            "additionalValues" => array($additionalValues),
        ];
        $address = [
            "line1" => "Calle 73 # 0 - 24 ",
            "line2" => "Apto 202",
            "line3" => "",
            "postalCode" => "",
            "city" => "Bogota",
            "state" => "Cundinamarca",
            "country" => "CO",
            "phone" => "3105507245"
        ];

        $creditCard = [
            "name" => "APPROVED",
            "document" => "1231231232",
            "number" => "4111111111111111",
            "expMonth" => "12",
            "expYear" => "2020",
            "type" => "VISA",
            "address" => $address
        ];
        $creditCards = array($creditCard);
        $customer = [
            "fullName" => "Hoovert Arredondo",
            "email" => "harredon01@gmail.com",
            "creditCards" => $creditCards,
        ];
        $dataSent = [
            "quantity" => 1,
            "installments" => 1,
            "trialDays" => 0,
            "immediatePayment" => true,
            "recurringBillItems" => array(),
            "extra1" => "",
            "extra2" => "",
            "customer" => $customer,
            "plan" => $plan,
            "deliveryAddress" => $address,
            "notifyUrl" => env('APP_URL') . "/api/payu/webhook",
        ];
        $response = $this->sendPost($dataSent, $url);
        if (array_key_exists("id", $response)) {
            $subscription = new Subscription([
                "gateway" => "PayU",
                "status" => "active",
                "type" => $planL->type,
                "name" => $planL->name,
                "plan" => $planL->plan_id,
                "plan_id" => $planL->id,
                "level" => $planL->level,
                "source_id" => $response['id'],
                "client_id" => $source->client_id,
                "object_id" => $data['object_id'],
                "interval" => $planL->interval,
                "interval_type" => $planL->interval_type,
                "quantity" => 1,
                "ends_at" => date("Y-m-d")
            ]);
            $user->subscriptions()->save($subscription);
        }
        if (array_key_exists("customer", $response)) {
            $customerReply = $response['customer'];
            $response['status'] = "success";
            $response["subscription"] = $subscription;
            if (array_key_exists("creditCards", $customerReply)) {
                $card = $customerReply['creditCards'][0];
                if ($card) {
                    if (array_key_exists("default", $data)) {
                        if ($data["default"]) {
                            $source->source = $card['token'];
                            $source->has_default = true;
                            $source->save();
                        }
                    }
                }
            }
        }
        return $response;
    }

    public function createSubscriptionSourceClient(User $user, Plan $planL, array $data) {
        $source = $this->createClient($user);
        if ($source) {
            return $this->createSubscriptionSource($user, $source, $planL, $data);
        }
        return null;
    }

    public function deleteSubscription(User $user, $subscription) {
        $url = $this->getTestUrl($user) . env('PAYU_REST') . 'subscriptions/' . $subscription;
        $result = $this->sendDelete($url);
        $user->subscriptions()->where('gateway', "PayU")->where('source_id', $subscription)->delete();
        return $result;
    }

    public function getStatusOrderRef($order_ref) {
        $filters = array(
            "external_reference" => $order_ref
        );
        $customers = Payment::search($filters);
        $results = $customers->results;
        if (count($results) > 0) {
            return $results[0];
        }
        return null;
    }

    public function handleTransactionResponse($response, User $user, Payment $payment, $platform) {
        if ($response->status) {
            $transactionContainer = [];
            $transactionContainer['order_id'] = $payment->order_id;
            $transactionContainer['reference_sale'] = $payment->referenceCode;
            $transactionContainer['user_id'] = $user->id;
            $transactionContainer['gateway'] = 'MercadoPago';
            $transactionContainer['currency'] = $response->currency_id;
            $transactionContainer['payment_method'] = $response->payment_type_id;
            $transactionContainer['description'] = $response->status_detail;
            $transactionContainer['transaction_id'] = $response->id;
            $transactionContainer['transaction_state'] = "complete";
            $transactionContainer['response_code'] = $response->id;
            $transactionContainer['transaction_date'] = $response->date_last_updated;
            $transactionContainer["extras"] = json_encode($response);
            $transaction = Transaction::create($transactionContainer);
            $payment->transactions()->save($transaction);
            $message = $this->getMessage($response);
            if ($response->status == "approved") {
                dispatch(new ApprovePayment($payment, $platform));
            } else if ($response->status == "in_process") {
                dispatch(new PendingPayment($payment, $platform));
            } else if ($response->status == "rejected" && (
                    $response->status_detail == "cc_rejected_bad_filled_card_number" || $response->status_detail == "cc_rejected_bad_filled_date" || $response->status_detail == "cc_rejected_bad_filled_other" || $response->status_detail == "cc_rejected_bad_filled_security_code")) {
                return ["status" => "denied", "status_detail" => $response->status_detail, "response" => $response->status, "message" => $message];
            } else {
                dispatch(new DenyPayment($payment, $platform));
            }
            return ["status" => "success", "status_detail" => $response->status_detail, "response" => $response->status, "message" => $message];
        }
        $response = $response->toArray();
        $error = $response['error'];
        return ["status" => "error", "response" => $response, "message" => $error->causes];
    }

    private function getMessage($response) {
        $messages = [
            "accredited" => "Listo, se acreditó tu pago! En tu resumen verás el cargo de amount como {statement_descriptor}.",
            "pending_contingency" => "Estamos procesando el pago. En menos de 2 días hábiles te enviaremos por e-mail el resultado.",
            "pending_review_manual" => "Estamos procesando el pago. En menos de 2 días hábiles te diremos por e-mail si se acreditó o si necesitamos más información.",
            "cc_rejected_bad_filled_card_number" => "Revisa el número de tarjeta.",
            "cc_rejected_bad_filled_date" => "Revisa la fecha de vencimiento.",
            "cc_rejected_bad_filled_other" => "Revisa los datos.",
            "cc_rejected_bad_filled_security_code" => "Revisa el código de seguridad.",
            "cc_rejected_blacklist" => "No pudimos procesar tu pago.",
            "cc_rejected_call_for_authorize" => "Debes autorizar ante {payment_method_id} el pago de amount a Mercado Pago",
            "cc_rejected_card_disabled" => "Llama a {payment_method_id} para que active tu tarjeta. El teléfono está al dorso de tu tarjeta.",
            "cc_rejected_card_error" => "No pudimos procesar tu pago.",
            "cc_rejected_duplicated_payment" => "Ya hiciste un pago por ese valor. Si necesitas volver a pagar usa otra tarjeta u otro medio de pago.",
            "cc_rejected_high_risk" => "Tu pago fue rechazado. Elige otro de los medios de pago, te recomendamos con medios en efectivo.",
            "cc_rejected_insufficient_amount" => "Tu {payment_method_id} no tiene fondos suficientes.",
            "cc_rejected_invalid_installments" => "{payment_method_id} no procesa pagos en installments cuotas.",
            "cc_rejected_max_attempts" => "Llegaste al límite de intentos permitidos. Elige otra tarjeta u otro medio de pago.",
            "cc_rejected_other_reason" => "{payment_method_id} no procesó el pago.",
        ];
        $message = $messages[$response->status_detail];
        $message = str_replace("{statement_descriptor}", $response->statement_descriptor, $message);
        $message = str_replace("{payment_method_id}", $response->payment_method_id, $message);
        return $message;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function checkOrders() {
        $payments = Payment::whereIn("status", ["payment_created", "pending"])->get();
        foreach ($payments as $payment) {
            $currentTime = time();
            //dd(date("Y-m-d h:m:s"));
            $paymentTime = strtotime($payment->updated_at);
            $timeDiff = ($currentTime - $paymentTime) / (60 * 60 * 24);
            if ($timeDiff > 10) {
                $payment->status = "expired";
                $payment->save();
            } else {

                $paymentD = $this->getStatusOrderRef($payment->referenceCode);
                if ($paymentD) {
                    if ($paymentD->status == "approved" || $paymentD->status == "rejected") {
                        $transactionResponse = [];
                        $transactionResponse['id'] = $paymentD->id;
                        $transactionResponse['payment_method'] = $paymentD->payment_method_id;
                        $transactionResponse['reference_code'] = $payment->referenceCode;
                        $transactionResponse['order_id'] = $payment->order_id;
                        $transactionResponse['user_id'] = $payment->user_id;
                        $transactionResponse['state'] = $paymentD->status;
                        $transactionResponse['responseMessage'] = $paymentD->status_detail;
                        $transactionResponse['transactionDate'] = $paymentD->date_last_updated;
                        $transactionResponse['referenceCode'] = $payment->referenceCode;
                        $transactionResponse['gateway'] = 'MercadoPago';
                        $this->saveTransactionQuery($transactionResponse, $payment);
                        if ($paymentD->status == "approved") {
                            dispatch(new ApprovePayment($payment, "Food"));
                        }
                        if ($paymentD->status == "rejected") {
                            dispatch(new DenyPayment($payment, "Food"));
                        }
                    }
                }
            }
        }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function webhook(array $data) {
//        $file = '/home/hoovert/access.log';
//        // Open the file to get existing content
//        $current = file_get_contents($file);
//        //$daarray = json_decode(json_encode($data));
//        // Append a new person to the file
//
//        $current .= json_encode($data);
//        $current .= PHP_EOL;
//        $current .= PHP_EOL;
//        $current .= PHP_EOL;
//        $current .= PHP_EOL;
//        file_put_contents($file, $current);
        switch ($data["type"]) {
            case "payment":
                if ($data["action"] == "payment.updated") {
                    $paymentM = $this->getPayment($data['data']['id']);
                    $paymentM = json_decode(json_encode($paymentM),false);
                    $payment = Payment::where("referenceCode", $paymentM->external_reference)->first();
                    if ($payment) {
                        if ($payment->status == "payment_created" || $payment->status == "pending") {
                            $data['user_id'] = $payment->user_id;
                            $data['order_id'] = $payment->order_id;
                            $insert = [];
                            $insert["reference_sale"] = $paymentM->external_reference;
                            $insert["order_id"] = $payment->order_id;
                            $insert["user_id"] = $payment->user_id;
                            $insert["response_code"] = $paymentM->status;
                            $insert["currency"] = $paymentM->currency_id;
                            $insert["payment_method"] = $paymentM->payment_method_id;
                            $insert["transaction_id"] = $paymentM->id;
                            $insert["gateway"] = "MercadoPago";
                            $insert["description"] = $paymentM->status_detail;
                            $insert["transaction_date"] = $paymentM->date_last_updated;
                            $insert["transaction_state"] = $paymentM->status;
                            $insert["extras"] = json_encode($data);
                            $transaction = $this->saveTransactionConfirmacion($data, $payment);
                            if ($paymentM->status == "approved") {
                                dispatch(new ApprovePayment($payment, "Food"));
                            } else if ($paymentM->status == "rejected") {
                                dispatch(new DenyPayment($payment, "Food"));
                            }
                        }
                    }
                    return ["status" => "success", "message" => "transaction processed"];
                    break;
                }

            case "plan":
                //$plan = MercadoPago\Plan . find_by_id($data["id"]);
                break;
            case "subscription":
                //$plan = MercadoPago\Subscription . find_by_id($data["id"]);
                break;
            case "invoice":
                //$plan = MercadoPago\Invoice . find_by_id($data["id"]);
                break;
        }
    }

    private function saveTransactionConfirmacion(array $data, Payment $payment) {
//        dd($data);
//        $transactionId = $data['transaction_id'];
//        $transaction = Transaction::where("transaction_id", $transactionId)->where('gateway', 'PayU')->first();
//        if ($transaction) {
//            $transaction->currency = $data['currency'];
//            $transaction->transaction_state = $data['response_message_pol'];
//            $transaction->description = $data['response_message_pol'];
//            $transaction->reference_sale = $data['reference_sale'];
//            $transaction->payment_method = $data['payment_method_name'];
//            $transaction->transaction_id = $data['transaction_id'];
//            $transaction->transaction_date = $data['transaction_date'];
//            $transaction->response_code = $data['response_code_pol'];
//            $transaction->extras = json_encode($data);
//            $transaction->save();
//        } else {
//            $insert = [];
//            $insert["reference_sale"] = $data["reference_sale"];
//            $insert["order_id"] = $data["order_id"];
//            $insert["user_id"] = $data["user_id"];
//            $insert["response_code"] = $data["response_message_pol"];
//            $insert["currency"] = $data["currency"];
//            $insert["payment_method"] = $data["payment_method_type"];
//            $insert["transaction_id"] = $data["transaction_id"];
//            $insert["gateway"] = "PayU";
//            $insert["description"] = $data["response_message_pol"];
//            $insert["transaction_date"] = $data["transaction_date"];
//            $insert["transaction_state"] = $data["response_message_pol"];
//            $insert["extras"] = json_encode($data);
//            $transaction = Transaction::create($insert);
//            $payment->transactions()->save($transaction);
//        }
        return $data;
    }

    private function saveTransactionRespuesta(array $data, Payment $payment) {
        $transactionId = $data['transactionId'];
        $transaction = Transaction::where("transaction_id", $transactionId)->where('gateway', 'PayU')->first();
        if ($transaction) {
            $transaction->currency = $data['currency'];
            $transaction->transaction_state = $data['transactionState'];
            $transaction->description = $data['message'];
            $transaction->reference_sale = $data['referenceCode'];
            $transaction->payment_method = $data['polPaymentMethodType'];
            $transaction->transaction_id = $data['transactionId'];
            //$transaction->transaction_date = $data['processingDate'];
            $transaction->response_code = $data['lapResponseCode'];
            $transaction->extras = json_encode($data);
            $transaction->save();
        } else {
            $insert = [];
            $insert["reference_sale"] = $data["referenceCode"];
            $insert["order_id"] = $data["order_id"];
            $insert["user_id"] = $data["user_id"];
            $insert["response_code"] = $data["lapResponseCode"];
            $insert["currency"] = $data["currency"];
            $insert["payment_method"] = $data["polPaymentMethodType"];
            $insert["transaction_id"] = $data["transactionId"];
            $insert["gateway"] = "PayU";
            $insert["description"] = $data["message"];
            //$insert["transaction_date"] = $data["processingDate"];
            $insert["transaction_state"] = $data["transactionState"];
            $insert["extras"] = json_encode($data);
            $transaction = Transaction::create($insert);
            $payment->transactions()->save($transaction);
        }
        return $transaction;
    }

    private function saveTransactionQuery(array $data, Payment $payment) {
        $transactionId = $data['id'];
        $transaction = Transaction::where("transaction_id", $transactionId)->where('gateway', 'MercadoPago')->first();
        if ($transaction) {
            $transaction->transaction_state = $data['state'];
            $transaction->description = $data['responseMessage'];
            $transaction->user_id = $data['user_id'];
            $transaction->order_id = $data['order_id'];
            $transaction->reference_sale = $data['reference_code'];
            $transaction->payment_method = $data['payment_method'];
            $transaction->transaction_id = $data['id'];
            $transaction->transaction_date = $data['transactionDate'];
            $transaction->response_code = $data['responseCode'];
            $transaction->extras = json_encode($data);
            $transaction->save();
        } else {
            $insert = [];
            $insert["reference_sale"] = $data["reference_code"];
            $insert["response_code"] = $data["responseCode"];
            $insert["payment_method"] = $data["payment_method"];
            $insert["transaction_id"] = $data["id"];
            $insert["user_id"] = $data["user_id"];
            $insert["order_id"] = $data["order_id"];
            $insert["gateway"] = "MercadoPago";
            $insert["currency"] = "COP";
            $insert["description"] = $data["responseMessage"];
            $insert["transaction_date"] = $data['transactionDate'];
            $insert["transaction_state"] = $data["state"];
            $insert["extras"] = json_encode($data);
            $transaction = Transaction::create($insert);
            $payment->transactions()->save($transaction);
        }
        return $transaction;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function returnMerc(array $data) {
//        $payment = Payment::where("referenceCode", $referenceCode)->first();
//        if ($payment) {
//            $data['user_id'] = $payment->user_id;
//            $data['order_id'] = $payment->order_id;
//            $transaction = $this->saveTransactionRespuesta($data, $payment);
//            $data['transaction'] = $transaction;
//            if ($data['transactionState'] == 4) {
//                $estadoTx = "Transaction approved";
//                dispatch(new ApprovePayment($payment, "Food"));
//            } else if ($data['transactionState'] == 6) {
//                $estadoTx = "Transaction rejected";
//                dispatch(new DenyPayment($payment, "Food"));
//            } else if ($data['transactionState'] == 104) {
//                $estadoTx = "Error";
//                dispatch(new DenyPayment($payment, "Food"));
//            } else if ($data['transactionState'] == 7) {
//                $estadoTx = "Pending payment";
//                dispatch(new PendingPayment($payment, "Food"));
//            } else {
//                $estadoTx = $data['mensaje'];
//            }
//        }
        return $data;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorDebit(array $data) {
        return Validator::make($data, [
                    'financial_institution' => 'required|max:255',
                    'doc_type' => 'required|max:255',
                    'payer_id' => 'required|max:255',
                    'email' => 'required|max:255',
                    'payment_id' => 'required|max:255',
                    'platform' => 'required|max:255',
                    'entity_type' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorCash(array $data) {
        return Validator::make($data, [
                    'email' => 'required|email|max:255',
                    'payment_method_id' => 'required|max:255',
                    'payment_id' => 'required|max:255',
                    'platform' => 'required|max:255'
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
                    'source' => 'required|email|max:255',
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
    public function validatorSubscriptionExisting(array $data) {
        return Validator::make($data, [
                    'source' => 'required|max:255',
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
                    'installments' => 'required|max:255',
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
                    'plan_id' => 'required|max:255',
//                    'installments' => 'required|max:255',
                    'object_id' => 'required|max:255',
                    'line1' => 'required|max:255',
//                    'line2' => 'required|max:255',
//                    'line3' => 'required|max:255',
//                    'postalCode' => 'required|max:255',
                    'name' => 'required|max:255',
                    'city' => 'required|max:255',
                    'state' => 'required|max:255',
                    'country' => 'required|max:255',
                    'phone' => 'required|max:255',
                    'document' => 'required|max:255',
                    'number' => 'required|max:255',
                    'expMonth' => 'required|max:255',
                    'expYear' => 'required|max:255',
                    'branch' => 'required|max:255'
                        ]
        );
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorQuickPayment(array $data) {
        return Validator::make($data, [
                    'payment_id' => 'required|max:255',
                    'platform' => 'required|max:255',
                    'token' => 'required|max:255',
                    'installments' => 'required|max:255',
                    'issuer_id' => 'required|max:255',
                        ]
        );
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorPayment(array $data) {
        return Validator::make($data, [
                    'token' => 'required|max:255',
                        ]
        );
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorToken(array $data) {
        return Validator::make($data, [
                    'token' => 'required|max:255'
                        ]
        );
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorPayer(array $data) {
        return Validator::make($data, [
                    'payer_address' => 'required|max:255',
                    'payer_city' => 'required|max:255',
                    'payer_state' => 'required|max:255',
                    'payer_country' => 'required|max:255',
                    'payer_postal' => 'required|max:255',
                    'payer_phone' => 'required|max:255',
                    'payer_name' => 'required|max:255',
                    'payer_email' => 'required|max:255',
                    'payer_id' => 'required|max:255',
                        ]
        );
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorPayerSimple(array $data) {
        return Validator::make($data, [
                    'payer_phone' => 'required|max:255',
                    'payer_name' => 'required|max:255',
                    'payer_email' => 'required|max:255',
                    'payer_id' => 'required|max:255',
                        ]
        );
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorDefault(array $data) {
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
    public function validatorSource(array $data) {
        return Validator::make($data, [
                    // 'default' => 'required|max:255',
                    'name' => 'required|max:255',
                    'line1' => 'required|max:255',
//                    'line2' => 'required|max:255',
//                    'line3' => 'required|max:255',
//                    'postalCode' => 'required|max:255',
                    'city' => 'required|max:255',
                    'state' => 'required|max:255',
                    'country' => 'required|max:255',
                    'phone' => 'required|max:255',
                    'document' => 'required|max:255',
                    'number' => 'required|max:255',
                    'expMonth' => 'required|max:255',
                    'expYear' => 'required|max:255',
                    'branch' => 'required|max:255'
        ]);
    }

}
