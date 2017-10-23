<?php

namespace App\Services;

use Validator;
use App\Models\Country;
use App\Models\User;
use App\Models\Order;
use App\Models\Region;
use App\Models\City;
use App\Models\Plan;
use App\Jobs\ApproveOrder;
use App\Jobs\DenyOrder;
use App\Jobs\PendingOrder;
use App\Models\Source;
use Carbon\Carbon;
use App\Models\Subscription;
use App\Models\Transaction;

class PayU {

    public function payCreditCard(User $user, array $data, Order $order) {
        $validator = $this->validatorCC($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $billing = $order->orderAddresses()->where('type', "billing")->first();
        if ($billing) {


            $billingCountry = Country::find($billing->country_id);
            $billingRegion = Region::find($billing->region_id);

            $deviceSessionId = md5(session_id() . microtime());
            $accountId = "512321";
            $apiLogin = env('PAYU_LOGIN');
            $apiKey = env('PAYU_KEY');
            $reference = "besafe_test_1_" . $order->id;
            $order->referenceCode = $reference;
            $order->save();
            $currency = "COP";
            $merchantId = "508029";
            $str = $apiKey . "~" . $merchantId . "~" . $reference . "~" . number_format($order->total, 0, '.', '') . "~" . $currency;
            $sig = sha1($str);
            $merchant = [
                'apiLogin' => $apiLogin,
                'apiKey' => $apiKey
            ];
            $additionalValues = [
                'value' => number_format($order->total, 2, '.', ''),
                'currency' => $currency
            ];
            $additionalValuesCont = [
                'TX_VALUE' => $additionalValues
            ];

            $buyerAddress = [
                "street1" => $billing->address,
                "street2" => "",
                "city" => $billing->city,
                "state" => $billingRegion->name,
                "country" => $billingCountry->code,
                "postalCode" => $billing->postal,
                "phone" => $billing->phone
            ];
            $buyer = [
                "merchantBuyerId" => "1",
                "fullName" => $billing->name,
                "emailAddress" => $data['payer_email'],
                "contactPhone" => $billing->phone,
                "dniNumber" => $data['payer_id'],
                "shippingAddress" => $buyerAddress
            ];
            $shipping = $order->orderAddresses()->where('type', "shipping")->first();
            if ($shipping) {
                $shippingCountry = Country::find($shipping->country_id);
                $shippingRegion = Region::find($shipping->region_id);
                $ShippingAddress = [
                    "street1" => $shipping->address,
                    "street2" => "",
                    "city" => $shipping->city,
                    "state" => $shippingRegion->name,
                    "country" => $shippingCountry->code,
                    "postalCode" => $shipping->postal,
                    "phone" => $shipping->phone
                ];
            } else {
                $ShippingAddress = [
                    "street1" => $billing->address,
                    "street2" => "",
                    "city" => $billing->city,
                    "state" => $billingRegion->name,
                    "country" => $billingCountry->code,
                    "postalCode" => $billing->postal,
                    "phone" => $billing->phone
                ];
            }
            $orderCont = [
                "accountId" => $accountId,
                "referenceCode" => $reference,
                "description" => "besafe payment test",
                "language" => "es",
                "signature" => $sig,
                "notifyUrl" => "http://www.tes.com/confirmation",
                "additionalValues" => $additionalValuesCont,
                "buyer" => $buyer,
                "shippingAddress" => $ShippingAddress
            ];

            $payerAddress = [
                "street1" => $billing->address,
                "street2" => "",
                "city" => $billing->city,
                "state" => $billingRegion->name,
                "country" => $billingCountry->code,
                "postalCode" => $billing->postal,
                "phone" => $billing->phone
            ];
            $payer = [
                "merchantPayerId" => "1",
                "fullName" => $billing->city,
                "emailAddress" => $data['payer_email'],
                "contactPhone" => $billing->phone,
                "dniNumber" => $data['payer_id'],
                "billingAddress" => $payerAddress
            ];
            $creditCard = [
                "number" => $data['cc_number'],
                "securityCode" => $data['cc_security_code'],
                "expirationDate" => $data['cc_expiration_year'] . "/" . $data['cc_expiration_month'],
                "name" => $billing->name
            ];
            $extraParams = [
                "INSTALLMENTS_NUMBER" => 1
            ];
            $transaction = [
                "order" => $orderCont,
                "payer" => $payer,
                "creditCard" => $creditCard,
                "extraParameters" => $extraParams,
                "type" => "AUTHORIZATION_AND_CAPTURE",
                "paymentMethod" => $data['cc_branch'],
                "paymentCountry" => $billingCountry->code,
                "deviceSessionId" => $deviceSessionId,
                "ipAddress" => $data['ip_address'],
                "cookie" => "pt1t38347bs6jc9ruv2ecpv7o2",
                "userAgent" => $data['user_agent']
            ];
            $dataSent = [
                "language" => "es",
                "command" => "SUBMIT_TRANSACTION",
                "merchant" => $merchant,
                "transaction" => $transaction,
                "test" => false,
            ];
//        return $dataSent;
            $result = $this->sendRequest($dataSent, env('PAYU_PAYMENTS'));
            return $this->handleTransactionResponse($result, $user, $order);
        }
        return array("status" => "error", "message" => "missing billing Address");
    }

    public function useSource(User $user, array $data, Order $order) {
        $validator = $this->validatorUseSource($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $billing = $order->orderAddresses()->where('type', "billing")->first();
        if ($billing) {
            $creditCardTokenId = $data['source'];


            $billingCountry = Country::find($billing->country_id);
            $billingRegion = Region::find($billing->region_id);

            $deviceSessionId = md5(session_id() . microtime());
            $accountId = "512321";
            $apiLogin = env('PAYU_LOGIN');
            $apiKey = env('PAYU_KEY');
            $reference = "besafe_test_1_" . $order->id;
            $order->referenceCode = $reference;
            $order->save();
            $currency = "COP";
            $merchantId = "508029";
            $str = $apiKey . "~" . $merchantId . "~" . $reference . "~" . number_format($order->total, 0, '.', '') . "~" . $currency;
            $sig = sha1($str);
            $merchant = [
                'apiLogin' => $apiLogin,
                'apiKey' => $apiKey
            ];
            $additionalValues = [
                'value' => number_format($order->total, 2, '.', ''),
                'currency' => $currency
            ];
            $additionalValuesCont = [
                'TX_VALUE' => $additionalValues
            ];

            $buyerAddress = [
                "street1" => $billing->address,
                "street2" => "",
                "city" => $billing->city,
                "state" => $billingRegion->name,
                "country" => $billingCountry->code,
                "postalCode" => $billing->postal,
                "phone" => $billing->phone
            ];
            $buyer = [
                "merchantBuyerId" => "1",
                "fullName" => $billing->name,
                "emailAddress" => $data['payer_email'],
                "contactPhone" => $billing->phone,
                "dniNumber" => $data['payer_id'],
                "shippingAddress" => $buyerAddress
            ];
            $shipping = $order->orderAddresses()->where('type', "shipping")->first();
            if ($shipping) {
                $shippingCountry = Country::find($shipping->country_id);
                $shippingRegion = Region::find($shipping->region_id);
                $ShippingAddress = [
                    "street1" => $shipping->address,
                    "street2" => "",
                    "city" => $shipping->city,
                    "state" => $shippingRegion->name,
                    "country" => $shippingCountry->code,
                    "postalCode" => $shipping->postal,
                    "phone" => $shipping->phone
                ];
            } else {
                $ShippingAddress = [
                    "street1" => $billing->address,
                    "street2" => "",
                    "city" => $billing->city,
                    "state" => $billingRegion->name,
                    "country" => $billingCountry->code,
                    "postalCode" => $billing->postal,
                    "phone" => $billing->phone
                ];
            }
            $orderCont = [
                "accountId" => $accountId,
                "referenceCode" => $reference,
                "description" => "besafe payment test",
                "language" => "es",
                "signature" => $sig,
                "notifyUrl" => "http://www.tes.com/confirmation",
                "additionalValues" => $additionalValuesCont,
                "buyer" => $buyer,
                "shippingAddress" => $ShippingAddress
            ];

            $payerAddress = [
                "street1" => $billing->address,
                "street2" => "",
                "city" => $billing->city,
                "state" => $billingRegion->name,
                "country" => $billingCountry->code,
                "postalCode" => $billing->postal,
                "phone" => $billing->phone
            ];
            $payer = [
                "merchantPayerId" => "1",
                "fullName" => $billing->name,
                "emailAddress" => $data['payer_email'],
                "contactPhone" => $billing->phone,
                "dniNumber" => $data['payer_id'],
                "billingAddress" => $payerAddress
            ];

            $extraParams = [
                "INSTALLMENTS_NUMBER" => 1
            ];
            $transaction = [
                "order" => $orderCont,
                "payer" => $payer,
                "creditCardTokenId" => $creditCardTokenId,
                "extraParameters" => $extraParams,
                "type" => "AUTHORIZATION_AND_CAPTURE",
                "paymentMethod" => $data['cc_branch'],
                "paymentCountry" => $billingCountry->code,
                "deviceSessionId" => $deviceSessionId,
                "ipAddress" => $data['ip_address'],
                "cookie" => "pt1t38347bs6jc9ruv2ecpv7o2",
                "userAgent" => $data['user_agent']
            ];
            $dataSent = [
                "language" => "es",
                "command" => "SUBMIT_TRANSACTION",
                "merchant" => $merchant,
                "transaction" => $transaction,
                "test" => false,
            ];
//        return $dataSent;
            $result = $this->sendRequest($dataSent, env('PAYU_PAYMENTS'));
            return $this->handleTransactionResponse($result, $user, $order);
        }
        return array("status" => "error", "message" => "missing billing Address");
    }

    public function makeCharge(User $user, Order $order, array $payload) {
        $method = 'pay' . studly_case(str_replace('.', '_', $payload['type']));
        if (method_exists($this, $method)) {
            $result = $this->{$method}($user, $payload, $order);
            return $this->saveTransaction($user, $result);
            ;
        } else {
            return $this->missingMethod();
        }
    }

    public function payDebitCard(User $user, array $data, Order $order) {
        $validator = $this->validatorDebit($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $billing = $order->orderAddresses()->where('type', "billing")->first();
        if ($billing) {
            $billingCountry = Country::find($billing->country_id);
            $deviceSessionId = md5(session_id() . microtime());
            $accountId = "512321";
            $apiLogin = env('PAYU_LOGIN');
            $apiKey = env('PAYU_KEY');
            $reference = "besafe_test_1_" . $order->id;
            $order->referenceCode = $reference;
            $order->save();
            $currency = "COP";
            $merchantId = "508029";
            $str = $apiKey . "~" . $merchantId . "~" . $reference . "~" . number_format($order->total, 0, '.', '') . "~" . $currency;
            $sig = sha1($str);
            $merchant = [
                'apiLogin' => $apiLogin,
                'apiKey' => $apiKey
            ];
            $additionalValues = [
                'value' => number_format($order->total, 2, '.', ''),
                'currency' => $currency
            ];
            $additionalValuesCont = [
                'TX_VALUE' => $additionalValues
            ];

            $buyer = [
                "emailAddress" => $data['payer_email'],
            ];

            $orderCont = [
                "accountId" => $accountId,
                "referenceCode" => $reference,
                "description" => "besafe payment test",
                "language" => "es",
                "signature" => $sig,
                "notifyUrl" => "http://www.tes.com/confirmation",
                "additionalValues" => $additionalValuesCont,
                "buyer" => $buyer
            ];

            $payer = [
                "fullName" => $billing->name,
                "emailAddress" => $data['payer_email'],
                "contactPhone" => $billing->phone,
            ];
            $extraParams = [
                "RESPONSE_URL" => "http://www.test.com/response",
                "PSE_REFERENCE1" => $data['ip_address'],
                "FINANCIAL_INSTITUTION_CODE" => $data['financial_institution_code'],
                "USER_TYPE" => $data['user_type'],
                "PSE_REFERENCE2" => $data['pse_reference2'],
                "PSE_REFERENCE3" => $data['pse_reference3']
            ];
            $transaction = [
                "order" => $orderCont,
                "payer" => $payer,
                "extraParameters" => $extraParams,
                "type" => "AUTHORIZATION_AND_CAPTURE",
                "paymentMethod" => "PSE",
                "paymentCountry" => $billingCountry->code,
                "deviceSessionId" => $deviceSessionId,
                "ipAddress" => $data['ip_address'],
                "cookie" => $data['cookie'],
                "userAgent" => $data['user_agent']
            ];
            $dataSent = [
                "language" => "es",
                "command" => "SUBMIT_TRANSACTION",
                "merchant" => $merchant,
                "transaction" => $transaction,
                "test" => false,
            ];

            return $this->sendRequest($dataSent, env('PAYU_PAYMENTS'));
        }
        return array("status" => "error", "message" => "missing billing Address");
    }

    public function payCash(User $user, array $data, Order $order) {
        $validator = $this->validatorCash($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $accountId = "512321";
        $apiLogin = env('PAYU_LOGIN');
        $apiKey = env('PAYU_KEY');
        $reference = "besafe_test_1_" . $order->id;
        $order->referenceCode = $reference;
        $order->save();
        $currency = "COP";
        $merchantId = "508029";
        $date = date_create();
        date_add($date, date_interval_create_from_date_string("7 days"));
        $date = date_format($date, "Y-m-d") . "T" . date_format($date, "G:i:s");
        $str = $apiKey . "~" . $merchantId . "~" . $reference . "~" . number_format($order->total, 0, '.', '') . "~" . $currency;
        $sig = sha1($str);
        $merchant = [
            'apiLogin' => $apiLogin,
            'apiKey' => $apiKey
        ];
        $additionalValues = [
            'value' => number_format($order->total, 2, '.', ''),
            'currency' => $currency
        ];
        $additionalValuesCont = [
            'TX_VALUE' => $additionalValues
        ];

        $buyer = [
            "emailAddress" => $data['payer_email'],
        ];
        $orderCont = [
            "accountId" => $accountId,
            "referenceCode" => $reference,
            "description" => "besafe payment test",
            "language" => "es",
            "signature" => $sig,
            "notifyUrl" => "http://www.tes.com/confirmation",
            "additionalValues" => $additionalValuesCont,
            "buyer" => $buyer
        ];
        $transaction = [
            "order" => $orderCont,
            "type" => "AUTHORIZATION_AND_CAPTURE",
            "paymentMethod" => "BALOTO",
            "paymentCountry" => "CO",
            "ipAddress" => $data['ip_address'],
            "expirationDate" => $date,
        ];
        $dataSent = [
            "language" => "es",
            "command" => "SUBMIT_TRANSACTION",
            "merchant" => $merchant,
            "transaction" => $transaction,
            "test" => false,
        ];
        return $this->sendRequest($dataSent, env('PAYU_PAYMENTS'));
    }

    public function getBanks() {
        $apiLogin = env('PAYU_LOGIN');
        $apiKey = env('PAYU_KEY');
        $merchant = [
            'apiLogin' => $apiLogin,
            'apiKey' => $apiKey
        ];

        $bankListInformation = [
            "paymentMethod" => "PSE",
            "paymentCountry" => "CO"
        ];
        $dataSent = [
            "language" => "es",
            "command" => "GET_BANKS_LIST",
            "merchant" => $merchant,
            "bankListInformation" => $bankListInformation,
            "test" => false,
        ];
        return $this->sendRequest($dataSent, env('PAYU_PAYMENTS'));
    }

    public function createSource(Source $source, array $data) {
        $validator = $this->validatorSource($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $address = [
            "line1" => $data['line1'],
            "line2" => "",
            "line3" => "",
            "postalCode" => $data['postalCode'],
            "city" => $data['city'],
            "state" => $data['state'],
            "country" => $data['country'],
            "phone" => $data['phone'],
        ];
        $datasent = [
            "name" => $data['name'],
            "document" => $data['document'],
            "number" => $data['number'],
            "expMonth" => $data['expMonth'],
            "expYear" => $data['expYear'],
            "type" => $data['branch'],
            "address" => $address
        ];
        $response = $this->sendPost($datasent, env('PAYU_REST') . 'customers/' . $source->client_id . '/creditCards');
        if (array_key_exists("token", $response)) {
            if (array_key_exists("default", $response)) {
                if ($data['default'] == true) {
                    $source->source = $response["token"];
                    $source->has_default = true;
                    $source->save();
                }
            }
            $response['status'] = 'success';
        }


        return $response;
    }

    public function editSource(Source $source, array $data) {

//        $address = [
//            "line1" => $data['line1'],
//            "line2" => $data['line2'],
//            "line3" => $data['line3'],
//            "postalCode" => $data['postalCode'],
//            "city" => $data['city'],
//            "state" => $data['state'],
//            "country" => $data['country'],
//            "phone" => $data['phone'],
//        ];
//        $source = [
//            "name" => $data['type'],
//            "document" => $data['document'],
//            "number" => $data['number'],
//            "expMonth" => $data['expMonth'],
//            "expYear" => $data['expYear'],
//            "type" => $data['type']
//        ];
//        $response = $this->sendRequest($source, 'https://sandbox.api.payulatam.com/rest/v4.9/creditCards/' . $data['source']);
//        if (array_key_exists("token", $response)) {
//            $source->source = $response["token"];
//            $source->save();
//        }
//        return $response;
    }

    public function deleteSource(Source $source, $token) {
        $url = env('PAYU_REST') . 'customers/' . $source->client_id . '/creditCards/' . $token;
        $result = $this->sendDelete($url);
        $result['status'] = "success";
        return $result;
    }

    public function getSources(Source $source) {
        $client = $this->getClient($source);
        if ($client) {
            if (array_key_exists('creditCards', $client)) {
                $sources = $client['creditCards'];
                $result = array();
                foreach ($sources as $item) {
                    if ($item['token'] == $source->source) {
                        $item['is_default'] = true;
                    } else {
                        $item['is_default'] = false;
                    }
                    array_push($result, $item);
                }
                return $result;
            }
            return array();
        }
        return array();
    }

    public function getSource(Source $source, $token) {
        return null;
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
        $source->has_default = true;
        $source->save();
    }

    public function createClient(User $user) {
        $dataSent = [
            "fullName" => $user->name,
            "email" => $user->email,
        ];
        $response = $this->sendPost($dataSent, env('PAYU_REST') . 'customers/');
        if (array_key_exists("id", $response)) {
            $source = new Source([
                "gateway" => "PayU",
                "client_id" => $response['id']
            ]);
            $user->sources()->save($source);
            return $source;
        }
        return null;
    }

    public function getClient(Source $source) {
        $url = env('PAYU_REST') . 'customers/' . $source->client_id;
        return $this->sendGet($url);
    }

    public function deleteClient(User $user, $client) {
        $sources = $user->sources()->where('gateway', "PayU")
                        ->where('client_id', $client)->get();
        if ($sources) {
            $url = env('PAYU_REST') . 'customers/' . $client;
            $this->sendDelete($url);
            return $user->sources()->where('gateway', "PayU")->where('client_id', $client)->delete();
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
        $validator = $this->validatorEditSubscription($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $subscription = $user->subscriptions()->where('gateway', 'payu')->where('source_id', $id)->first();
        if ($subscription) {
            if ($subscription->is_active()) {
                $datetime1 = strtotime($subscription->ends_at);
                $datetime2 = strtotime("now");
                $secs = $datetime2 - $datetime1; // == return sec in difference
                $days = $secs / 86400;
                $data['trialDays'] = $days;
            } else {
                $data['trialDays'] = 0;
            }
            $url = env('PAYU_REST');
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
                "installments" => $data['installments'],
                "trialDays" => $data['trialDays'],
                "customer" => $customer,
                "plan" => $plan
            ];
            $response = $this->sendRequest($dataSent, $url);
            if (array_key_exists("id", $response)) {
                $this->deleteSubscription($user, $subscription->source_id);
                $subscription->gateway = "PayU";
                $subscription->status = "active";
                $subscription->type = $planL->type;
                $subscription->name = $planL->name;
                $subscription->source_id = $response['id'];
                $subscription->client_id = $source->client_id;
                $subscription->interval = $planL->interval;
                $subscription->interval_type = $planL->interval_type;
                $subscription->quantity = $data['quantity'];
                $subscription->ends_at = Date($response['currentPeriodEnd'] / 1000);
                $subscription->save();
                return $response['id'];
            }
        }
    }

    public function createSubscription(User $user, Source $source, Plan $planL, array $data) {
        $validator = $this->validatorSubscription($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $url = env('PAYU_REST') . 'subscriptions/';
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
                "status" => "createSubscription" . $response['currentPeriodEnd'],
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
        $url = env('PAYU_REST') . 'plans/';
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
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $url = env('PAYU_REST') . 'subscriptions/';
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
                "status" => "createSubscriptionExistingSource" . $response['currentPeriodEnd'],
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
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $url = env('PAYU_REST') . 'subscriptions/';
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
            "expYear" => $data['expYear'],
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
                "ends_at" => Date($response['currentPeriodEnd'] / 1000)
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

    public function createAll(User $user, Source $source, Plan $planL, array $data) {
        $validator = $this->validatorSubscriptionSource($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $url = env('PAYU_REST') . 'subscriptions/';
        $additionalValues = [
            "name" => "PLAN_VALUE",
            "value" => "20000",
            "currency" => "COP"
        ];

        $plan = [
            "accountId" => "512321",
            "planCode" => "Plan-id",
            "description" => "Plan-id",
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
            "name" => "Hoovert Arredondo",
            "document" => "1231231232",
            "number" => "4242424242424242",
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
            "notifyUrl" => "http://hoovert.com/api/payu",
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
                "ends_at" => Date($response['currentPeriodEnd'])
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
        $url = env('PAYU_PAYMENTS') . "/rest/v4.9/subscriptions/" . $subscription;
        $this->sendDelete($url);
        return $user->subscriptions()->where('gateway', "PayU")->where('source_id', $subscription)->delete();
    }

    public function getPaymentMethods() {
        $apiLogin = env('PAYU_LOGIN');
        $apiKey = env('PAYU_KEY');
        $merchant = [
            'apiLogin' => $apiLogin,
            'apiKey' => $apiKey
        ];
        $dataSent = [
            "language" => "es",
            "command" => "GET_PAYMENT_METHODS",
            "merchant" => $merchant,
            "test" => false,
        ];

        return $this->sendRequest($dataSent, env('PAYU_PAYMENTS'));
    }

    public function getStatusOrderId($order_id) {
        $apiLogin = env('PAYU_LOGIN');
        $apiKey = env('PAYU_KEY');
        $merchant = [
            'apiLogin' => $apiLogin,
            'apiKey' => $apiKey
        ];
        $details = [
            'orderId' => $order_id
        ];
        $dataSent = [
            "language" => "es",
            "command" => "ORDER_DETAIL",
            "merchant" => $merchant,
            "details" => $details,
            "test" => false,
        ];

        return $this->sendRequest($dataSent, env('PAYU_REPORTS'));
    }

    public function getStatusOrderRef($order_ref) {
        $apiLogin = env('PAYU_LOGIN');
        $apiKey = env('PAYU_KEY');
        $merchant = [
            'apiLogin' => $apiLogin,
            'apiKey' => $apiKey
        ];
        $details = [
            'referenceCode' => $order_ref
        ];
        $dataSent = [
            "language" => "es",
            "command" => "ORDER_DETAIL_BY_REFERENCE_CODE",
            "merchant" => $merchant,
            "details" => $details,
            "test" => false,
        ];

        return $this->sendRequest($dataSent, env('PAYU_REPORTS'));
    }

    public function getStatusTransaction($transaction_id) {
        $apiLogin = env('PAYU_LOGIN');
        $apiKey = env('PAYU_KEY');
        $merchant = [
            'apiLogin' => $apiLogin,
            'apiKey' => $apiKey
        ];
        $details = [
            'transactionId' => $transaction_id
        ];
        $dataSent = [
            "language" => "es",
            "command" => "TRANSACTION_RESPONSE_DETAIL",
            "merchant" => $merchant,
            "details" => $details,
            "test" => false,
        ];

        return $this->sendRequest($dataSent, env('PAYU_PAYMENTS'));
    }

    public function handleTransactionResponse($response, User $user, Order $order) {
        if ($response['code'] == "SUCCESS") {
            if ($user) {
                $transactionResponse = $response['transactionResponse'];
                $transactionResponse['order_id'] = $order->id;
                $transactionResponse['referenceCode'] = $order->referenceCode;
                $transactionResponse['user_id'] = $user->id;
                $transactionResponse['gateway'] = 'payu';
                /* if (array_key_exists("extras", $transactionResponse)) {
                  $extras = $transactionResponse['extras'];
                  unset($transactionResponse['extras']);
                  $transactionResponse['extras'] = json_encode($extras);
                  } */
                $transaction = Transaction::create($transactionResponse);
                if ($transactionResponse['state'] == 'APPROVED') {
                    dispatch(new ApproveOrder($order));
                } else if ($transactionResponse['state'] == 'PENDING') {
                    dispatch(new DenyOrder($order));
                } else {
                    dispatch(new PendingOrder($order));
                }
                return ["status" => "success", "transaction" => $transaction, "response" => $response];
            } else {
                return $response;
            }
        }
    }

    public function sendRequest(array $data, $query) {
        $data_string = json_encode($data);
        $curl = curl_init($query);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string),
            'Accept: application/json')
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode(str_replace("\\", "", $response), true);
        return $response;
    }

    public function sendPost(array $data, $query) {
        $data_string = json_encode($data);
        $curl = curl_init($query);
        $auth = base64_encode(env('PAYU_LOGIN') . ":" . env('PAYU_KEY'));
        $headers = array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string),
            'Accept: application/json',
            'Accept-language: es',
            'Authorization: Basic ' . $auth,
        );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode(str_replace("\\", "", $response), true);
        return $response;
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
        $response = json_decode(str_replace("\\", "", $response), true);
        return $response;
    }

    public function sendPut(array $data, $query) {
        $auth = base64_encode(env('PAYU_LOGIN') . ":" . env('PAYU_KEY'));
        $headers = array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ',
            'Accept: application/json',
            'Accept-language: es',
            'Authorization: Basic ' . $auth,
        );
        $data_string = json_encode($data);
        $curl = curl_init($query);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode(str_replace("\\", "", $response), true);
        return $response;
    }

    public function sendDelete($query) {
        $auth = base64_encode(env('PAYU_LOGIN') . ":" . env('PAYU_KEY'));
        $headers = array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ',
            'Accept: application/json',
            'Accept-language: es',
            'Authorization: Basic ' . $auth,
        );
        $curl = curl_init($query);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode(str_replace("\\", "", $response), true);
        return $response;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function checkOrders() {
        $transactions = Transaction::where("state", "pending")->get();
        foreach ($transactions as $transaction) {
            $response = $this->getStatusOrderId($transaction->orderId);
            if ($response['code'] == "SUCCESS") {
                $result = $response['result'];
                $payload = $result['payload'];
                $transactionResponse = $payload['transactions'][0]['transactionResponse'];
                $order = $transaction->order;
                if ($transactionResponse['state'] == 'APPROVED') {
                    dispatch(new ApproveOrder($order));
                } else if ($transactionResponse['state'] == 'PENDING') {
                    continue;
                } else {
                    dispatch(new DenyOrder($order));
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
        $ApiKey = env('PAYU_KEY');
        $merchant_id = $data['merchantId'];
        $referenceCode = $data['referenceCode'];
        $TX_VALUE = $data['TX_VALUE'];
        $New_value = number_format($TX_VALUE, 1, '.', '');
        $currency = $data['currency'];
        $transactionState = $data['transactionState'];
        $firma_cadena = "$ApiKey~$merchant_id~$referenceCode~$New_value~$currency~$transactionState";
        $firmacreada = md5($firma_cadena);
        $firma = $data['signature'];
        if (strtoupper($firma) == strtoupper($firmacreada)) {
            $transaction = $this->saveTransaction($data);
            if ($data['transactionState'] == 4) {
                dispatch(new ApproveOrder($order));
                $transaction->description = "Transacción aprobada";
            } else if ($data['transactionState'] == 6) {
                dispatch(new DenyOrder($order));
                $transaction->description = "Transacción rechazada";
            } else if ($data['transactionState'] == 104) {
                dispatch(new DenyOrder($order));
                $transaction->description = "Error";
            } else if ($data['transactionState'] == 7) {
                dispatch(new PendingOrder($order));
            } else {
                $transaction->description = $data['mensaje'];
            }
            return ["status" => "success", "message" => "transaction processed", "data" => $data];
        } else {
            
        }
    }

    public function saveTransaction(User $user, array $data) {
        $transactionId = $data['transactionId'];
        $transaction = Transaction::where("transactionId", $transactionId)->where('gateway', 'payu')->first();
        if ($transaction) {
            $transaction->currency = $data['currency'];
            $transaction->state = $data['transactionState'];
            $transaction->description = $data['description'];
            $transaction->paymentNetworkResponseCode = $data['polResponseCode'];
            $transaction->trazabilityCode = $data['cus'];
            $transaction->transactionDate = $data['processingDate'];
            $transaction->authorizationCode = $data['authorizationCode'];
            $transaction->extras = json_encode($data);
            $transaction->save();
        } else {
            $data["state"] = $data["transactionState"];
            unset($data["transactionState"]);
            $data["paymentNetworkResponseCode"] = $data["polResponseCode"];
            unset($data["polResponseCode"]);
            $data["trazabilityCode"] = $data["cus"];
            unset($data["cus"]);
            $data["transactionDate"] = $data["processingDate"];
            unset($data["processingDate"]);
            $data["extras"] = json_encode($data);
            $transaction = Transaction::create($data);
        }
        return $transaction;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function returnPayu(array $data) {
        $ApiKey = env('PAYU_KEY');
        $merchant_id = $data['merchantId'];
        $referenceCode = $data['referenceCode'];
        $TX_VALUE = $data['TX_VALUE'];
        $New_value = number_format($TX_VALUE, 1, '.', '');
        $currency = $data['currency'];
        $transactionState = $data['transactionState'];
        $firma_cadena = "$ApiKey~$merchant_id~$referenceCode~$New_value~$currency~$transactionState";
        $firmacreada = md5($firma_cadena);
        $firma = $data['signature'];
        $transactionId = $data['transactionId'];
        if (strtoupper($firma) == strtoupper($firmacreada)) {
            $transaction = Transaction::where("transactionId", $transactionId)->where('gateway', 'payu')->first();
            if ($transaction) {
                $transaction->currency = $data['currency'];
                $transaction->state = $data['transactionState'];
                $transaction->description = $data['description'];
                $transaction->paymentNetworkResponseCode = $data['polResponseCode'];
                $transaction->trazabilityCode = $data['cus'];
                $transaction->transactionDate = $data['processingDate'];
                $transaction->authorizationCode = $data['authorizationCode'];
                $transaction->extras = json_encode($data);
                $order = $transaction->order;
                if ($data['transactionState'] == 4) {
                    dispatch(new ApproveOrder($order));
                    $transaction->description = "Transacción aprobada";
                } else if ($data['transactionState'] == 6) {
                    dispatch(new DenyOrder($order));
                    $transaction->description = "Transacción rechazada";
                } else if ($data['transactionState'] == 104) {
                    dispatch(new DenyOrder($order));
                    $transaction->description = "Error";
                } else if ($data['transactionState'] == 7) {
                    dispatch(new PendingOrder($order));
                } else {
                    $transaction->description = $data['mensaje'];
                }
                $transaction->save();
                return ["status" => "successed", "message" => "transaction Processed"];
            }
        } else {
            
        }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorCC(array $data) {
        return Validator::make($data, [
                    'cc_branch' => 'required|max:255',
                    'cc_expiration_month' => 'required|max:255',
                    'cc_expiration_year' => 'required|max:255',
                    'cc_name' => 'required|max:255',
                    'cc_number' => 'required|max:255',
                    'cc_security_code' => 'required|max:255',
                    'payer_id' => 'required|max:255',
                    'payer_email' => 'required|email|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorDebit(array $data) {
        return Validator::make($data, [
                    'financial_institution_code' => 'required|max:255',
                    'user_type' => 'required|max:255',
                    'pse_reference2' => 'required|max:255',
                    'pse_reference3' => 'required|max:255',
                    'payer_email' => 'required|email|max:255',
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
                    'payer_email' => 'required|email|max:255',
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
                    'source' => 'required|max:255',
//                    'installments' => 'required|max:255',
                    'plan_id' => 'required|max:255',
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
