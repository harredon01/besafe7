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
use App\Models\Source;
use Carbon\Carbon;
use App\Models\Subscription;
use App\Models\Transaction;

class PayU {

    private function populatePaymentContent(Payment $payment, $platform) {
        if ($payment->user_id == 2 || $payment->user_id == 77) {
            $accountId = env('PAYU_TEST_ACCOUNT', "512321");
            $apiKey = env('PAYU_TEST_KEY');
            $merchantId = env('PAYU_TEST_MERCHANT', "508029");
        } else {
            $accountId = env('PAYU_ACCOUNT');
            $apiKey = env('PAYU_KEY');
            $merchantId = env('PAYU_MERCHANT');
        }

        $reference = $payment->referenceCode;
        $paymentTotal = $payment->total;
        $currency = "COP";
        $str = $apiKey . "~" . $merchantId . "~" . $reference . "~" . number_format($paymentTotal, 0, '.', '') . "~" . $currency;
        $sig = sha1($str);
        $orderCont = [
            "accountId" => $accountId,
            "referenceCode" => $reference,
            "description" => "Pago Lonchis app # " . $payment->id,
            "language" => "es",
            "signature" => $sig,
            "notifyUrl" => "https://lonchis.com.co/api/payu/webhook",
        ];
        return $orderCont;
    }

    private function populateBuyer(User $user, array $data) {
        $buyerAddress = [
            "street1" => $data['buyer_address'],
            "street2" => "",
            "city" => $data['buyer_city'],
            "state" => $data['buyer_state'],
            "country" => $data['buyer_country'],
            "postalCode" => $data['buyer_postal'],
            "phone" => $data['buyer_phone']
        ];
        $buyer = [
            "merchantBuyerId" => "1",
            "fullName" => $user->firstName . " " . $user->lastName,
            "emailAddress" => $user->email,
            "contactPhone" => $user->cellphone,
            "dniNumber" => $user->docNum,
            "shippingAddress" => $buyerAddress
        ];
        return $buyer;
    }

    private function populateBuyerAddress(User $user) {
        $address = $user->addresses()->where("type", "buyer")->first();
        if ($address) {
            $region = Region::find($address->region_id);
            $country = Country::find($address->country_id);
            $city = City::find($address->city_id);
            $buyerAddress = [
                "street1" => $address->address,
                "street2" => "",
                "city" => $city->name,
                "state" => $region->name,
                "country" => $country->code,
                "postalCode" => $address->postal,
                "phone" => $address->phone
            ];
            $buyer = [
                "merchantBuyerId" => "1",
                "fullName" => $user->firstName . " " . $user->lastName,
                "emailAddress" => $user->email,
                "contactPhone" => $user->cellphone,
                "dniNumber" => $user->docNum,
                "shippingAddress" => $buyerAddress
            ];
            return $buyer;
        }
        return null;
    }

    private function populateMerchant($user) {
        $apiLogin = env('PAYU_LOGIN');
        $apiKey = env('PAYU_KEY');
        if ($user) {
            if ($user->id == 2 || $user->id == 77) {
                $apiLogin = env('PAYU_TEST_LOGIN');
                $apiKey = env('PAYU_TEST_KEY');
            }
        }

        $merchant = [
            'apiLogin' => $apiLogin,
            'apiKey' => $apiKey
        ];
        return $merchant;
    }

    private function populatePayer(array $data) {

        $payerAddress = [
            "street1" => $data['payer_address'],
            "street2" => "",
            "city" => $data['payer_city'],
            "state" => $data['payer_state'],
            "country" => $data['payer_country'],
            "postalCode" => $data['payer_postal'],
            "phone" => $data['payer_phone']
        ];
        $payer = [
            "merchantPayerId" => "1",
            "fullName" => $data['payer_name'],
            "emailAddress" => $data['payer_email'],
            "contactPhone" => $data['payer_phone'],
            "dniNumber" => $data['payer_id'],
            "dniType" => "CC",
            "billingAddress" => $payerAddress
        ];
        return $payer;
    }

    private function populatePayerSimple(array $data) {

        $payer = [
            "merchantPayerId" => "1",
            "fullName" => $data['payer_name'],
            "emailAddress" => $data['payer_email'],
            "contactPhone" => $data['payer_phone'],
        ];
        return $payer;
    }

    private function populateBuyerSimple(User $user) {
        $buyer = [
            "fullName" => $user->firstName . " " . $user->lastName,
            "emailAddress" => $user->email,
            "contactPhone" => $user->cellphone,
            "dniNumber" => $user->docNum,
        ];
        return $buyer;
    }

    private function populateShipping(array $data) {
        $ShippingAddress = [
            "street1" => $data['shipping_address'],
            "street2" => "",
            "city" => $data['shipping_city'],
            "state" => $data['shipping_state'],
            "country" => $data['shipping_country'],
            "postalCode" => $data['shipping_postal'],
            "phone" => $data['shipping_phone']
        ];
        return $ShippingAddress;
    }

    public function populateShippingFromAddress($addressId, array $data) {
        $address = OrderAddress::find($addressId);
        if ($address) {
            $region = Region::find($address->region_id);
            $country = Country::find($address->country_id);
            $city = City::find($address->city_id);
            $data['shipping_address'] = $address->address;
            $data['shipping_city'] = $city->name;
            $data['shipping_state'] = $region->name;
            $data['shipping_country'] = $country->code;
            $data['shipping_postal'] = $address->postal;
            $data['shipping_phone'] = $address->phone;
        } else {
            $data['shipping_address'] = $data['buyer_address'];
            $data['shipping_city'] = $data['buyer_city'];
            $data['shipping_state'] = $data['buyer_state'];
            $data['shipping_country'] = $data['buyer_country'];
            $data['shipping_postal'] = $data['buyer_postal'];
            $data['shipping_phone'] = $data['buyer_phone'];
        }
        return $this->populateShipping($data);
    }

    private function populateCC(array $data) {

        $creditCard = [
            "number" => $data['cc_number'],
            "securityCode" => $data['cc_security_code'],
            "expirationDate" => "20" . $data['cc_expiration_year'] . "/" . $data['cc_expiration_month'],
            "name" => $data['cc_name']
        ];
        return $creditCard;
    }

    private function populateTotals(Payment $payment, $currency) {
        $additionalValues = [
            'value' => number_format($payment->total, 2, '.', ''),
            'currency' => $currency
        ];
        $additionalValuesTax = [
            'value' => number_format($payment->tax, 2, '.', ''),
            'currency' => $currency
        ];
        $return = 0;
        if ($payment->tax > 0) {
            $return = $payment->total - $payment->tax;
        }

        $additionalValuesReturnBase = [
            'value' => number_format($return, 2, '.', ''),
            'currency' => $currency
        ];
        $additionalValuesCont = [
            'TX_VALUE' => $additionalValues,
            'TX_TAX' => $additionalValuesTax,
            'TX_TAX_RETURN_BASE' => $additionalValuesReturnBase,
        ];
        return $additionalValuesCont;
    }

    public function useCreditCardOptions(User $user, array $data, Payment $payment, $platform) {
        if (array_key_exists("quick", $data)) {
            return $this->quickPayCreditCard($user, $data, $payment, $platform);
        }
        if (array_key_exists("token", $data)) {
            return $this->useToken($user, $data, $payment, $platform);
        } else {
            $paymentResult = $this->payCreditCard($user, $data, $payment, $platform);
            if ($paymentResult['response']['code'] == "SUCCESS") {
                if ($paymentResult['response']['transactionResponse']['state'] == "APPROVED") {
                    if (array_key_exists("save_card", $data)) {
                        if ($data['save_card']) {
                            dispatch(new SaveCard($user, $data, "PayU"));
                            //return $gateway->createToken($user, $data);
                        }
                    }
                }
            }

            return $paymentResult;
        }
    }

    private function getTestVar(User $user) {
        if ($user->id == 2 || $user->id == 77) {
            return "true";
        } else {
            return false;
        }
    }

    private function getTestUrl($user) {
        if ($user) {
            if ($user->id == 2 || $user->id == 77) {
                return "https://sandbox.api.payulatam.com";
            }
        }
        return "https://api.payulatam.com";
    }

    public function quickPayCreditCard(User $user, array $data, Payment $payment, $platform) {
        $source = $user->sources()->where("has_default", true)->where("gateway", "PayU")->first();
        if ($source) {
            
        } else {
            return response()->json(array("status" => "error", "message" => "No default card"), 400);
        }
        $buyer = $this->populateBuyerAddress($user);
        $ShippingAddress = $this->populateShippingFromAddress($payment->address_id, []);
        $extras = json_decode($source->extra, true);
        $payerAddress = [
            "street1" => $extras['billingAddress']['street1'],
            "street2" => "",
            "city" => $extras['billingAddress']['city'],
            "state" => $extras['billingAddress']['state'],
            "country" => $extras['billingAddress']['country'],
            "postalCode" => $extras['billingAddress']['postalCode'],
            "phone" => $extras['billingAddress']['phone']
        ];
        $payer = [
            "merchantPayerId" => "1",
            "fullName" => $extras['fullName'],
            "emailAddress" => $extras['emailAddress'],
            "contactPhone" => $extras['billingAddress']['phone'],
            "dniNumber" => $extras['dniNumber'],
            "dniType" => "CC",
            "billingAddress" => $payerAddress
        ];
        $merchant = $this->populateMerchant($user);
        $orderCont = $this->populatePaymentContent($payment, $platform);
        $additionalValuesCont = $this->populateTotals($payment, "COP");
        $orderCont["additionalValues"] = $additionalValuesCont;
        $orderCont["buyer"] = $buyer;
        $orderCont["shippingAddress"] = $ShippingAddress;
        $extraParams = [
            "INSTALLMENTS_NUMBER" => 1
        ];
        $deviceSessionId = md5(session_id() . microtime());
        $cookie = md5($deviceSessionId);
        $transaction = [
            "order" => $orderCont,
            "payer" => $payer,
            "creditCardTokenId" => $source->source,
            "extraParameters" => $extraParams,
            "type" => "AUTHORIZATION_AND_CAPTURE",
            "paymentMethod" => $extras['method'],
            "paymentCountry" => $extras['billingAddress']['country'],
            "deviceSessionId" => $deviceSessionId,
            "ipAddress" => $data['ip_address'],
            "cookie" => $cookie,
        ];
        $dataSent = [
            "language" => "es",
            "command" => "SUBMIT_TRANSACTION",
            "merchant" => $merchant,
            "transaction" => $transaction,
            "test" => $this->getTestVar($user),
        ];
        $result = $this->sendRequest($dataSent, $this->getTestUrl($user) . env('PAYU_PAYMENTS'));
        return $this->handleTransactionResponse($result, $user, $payment, $dataSent, $platform, "COP");
    }

    public function payCreditCard(User $user, array $data, Payment $payment, $platform) {
        $validator = $this->validatorPayment($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }

        $validator = $this->validatorBuyer($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $validator = $this->validatorPayer($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $validator = $this->validatorCC($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $buyer = $this->populateBuyer($user, $data);
        $ShippingAddress = $this->populateShippingFromAddress($payment->address_id, $data);
        $payer = $this->populatePayer($data);
        $creditCard = $this->populateCC($data);
        $merchant = $this->populateMerchant($user);
        $orderCont = $this->populatePaymentContent($payment, $platform);
        $additionalValuesCont = $this->populateTotals($payment, "COP");
        $orderCont["additionalValues"] = $additionalValuesCont;
        $orderCont["buyer"] = $buyer;
        $orderCont["shippingAddress"] = $ShippingAddress;
        $extraParams = [
            "INSTALLMENTS_NUMBER" => 1
        ];
        $deviceSessionId = md5(session_id() . microtime());
        $cookie = md5($deviceSessionId);
        $transaction = [
            "order" => $orderCont,
            "payer" => $payer,
            "creditCard" => $creditCard,
            "extraParameters" => $extraParams,
            "type" => "AUTHORIZATION_AND_CAPTURE",
            "paymentMethod" => $data['cc_branch'],
            "paymentCountry" => $data['payer_country'],
            "deviceSessionId" => $deviceSessionId,
            "ipAddress" => $data['ip_address'],
            "cookie" => $cookie,
            "userAgent" => $data['user_agent']
        ];
        $dataSent = [
            "language" => "es",
            "command" => "SUBMIT_TRANSACTION",
            "merchant" => $merchant,
            "transaction" => $transaction,
            "test" => $this->getTestVar($user),
        ];
        $result = $this->sendRequest($dataSent, $this->getTestUrl($user) . env('PAYU_PAYMENTS'));
        return $this->handleTransactionResponse($result, $user, $payment, $dataSent, $platform, "COP");
    }

    public function useSource(User $user, array $data, Payment $payment, $platform) {
        $validator = $this->validatorBuyer($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $validator = $this->validatorPayer($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $buyer = $this->populateBuyer($user, $data);
        $ShippingAddress = $this->populateShippingFromAddress($payment->address_id, $data);
        $payer = $this->populatePayer($data);
        $validator = $this->validatorUseSource($data);

        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $merchant = $this->populateMerchant($user);

        $creditCardTokenId = $data['source'];
        $deviceSessionId = md5(session_id() . microtime());
        $additionalValuesCont = $this->populateTotals($payment, "COP");
        $orderCont = $this->populatePaymentContent($payment, $platform);
        $orderCont["additionalValues"] = $additionalValuesCont;
        $orderCont["buyer"] = $buyer;
        $orderCont["shippingAddress"] = $ShippingAddress;
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
            "paymentCountry" => $data['payer_country'],
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
            "test" => $this->getTestVar($user),
        ];
//        return $dataSent;
        $result = $this->sendRequest($dataSent, $this->getTestUrl($user) . env('PAYU_PAYMENTS'));
        return $this->handleTransactionResponse($result, $user, $payment, $dataSent, $platform, "COP");
    }

    public function payDebitCard(User $user, array $data, Payment $payment, $platform) {

        $validator = $this->validatorPayerSimple($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $payer = $this->populatePayerSimple($data);
        $buyer = $this->populateBuyerSimple($user);
        $validator = $this->validatorDebit($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $merchant = $this->populateMerchant($user);
        $deviceSessionId = md5(session_id() . microtime());
        $additionalValuesCont = $this->populateTotals($payment, "COP");
        $orderCont = $this->populatePaymentContent($payment, $platform);
        $orderCont["additionalValues"] = $additionalValuesCont;
        $orderCont["buyer"] = $buyer;


        $extraParams = [
            "RESPONSE_URL" => "https://lonchis.com.co/payu/return",
            "PSE_REFERENCE1" => $data['ip_address'],
            "FINANCIAL_INSTITUTION_CODE" => $data['financial_institution_code'],
            "USER_TYPE" => $data['user_type'],
            "PSE_REFERENCE2" => $data['doc_type'],
            "PSE_REFERENCE3" => $data['payer_id']
        ];
        $transaction = [
            "order" => $orderCont,
            "payer" => $payer,
            "extraParameters" => $extraParams,
            "type" => "AUTHORIZATION_AND_CAPTURE",
            "paymentMethod" => "PSE",
            "paymentCountry" => "CO",
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
            "test" => "false",
        ];

        return $this->sendRequest($dataSent, $this->getTestUrl($user) . env('PAYU_PAYMENTS'));
    }

    public function payCash(User $user, array $data, Payment $payment, $platform) {
        $validator = $this->validatorCash($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }

        $additionalValuesCont = $this->populateTotals($payment, "COP");
        $merchant = $this->populateMerchant($user);
        $buyer = $this->populateBuyerSimple($user);
        $orderCont = $this->populatePaymentContent($payment, $platform);
        $orderCont["additionalValues"] = $additionalValuesCont;
        $orderCont["buyer"] = $buyer;
        $date = date_create();
        date_add($date, date_interval_create_from_date_string("7 days"));
        $date = date_format($date, "Y-m-d") . "T" . date_format($date, "G:i:s");
        $transaction = [
            "order" => $orderCont,
            "type" => "AUTHORIZATION_AND_CAPTURE",
            "paymentMethod" => $data['payment_method'],
            "paymentCountry" => "CO",
            "ipAddress" => $data['ip_address'],
            "expirationDate" => $date,
        ];
        $dataSent = [
            "language" => "es",
            "command" => "SUBMIT_TRANSACTION",
            "merchant" => $merchant,
            "transaction" => $transaction,
            "test" => "false",
        ];
        return $this->sendRequest($dataSent, $this->getTestUrl($user) . env('PAYU_PAYMENTS'));
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

    public function getBanks(User $user) {
        $merchant = $this->populateMerchant($user);

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
        return $this->sendRequest($dataSent, $this->getTestUrl($user) . env('PAYU_PAYMENTS'));
    }

    public function createSource(Source $source, array $data) {
        $validator = $this->validatorSource($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
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
            "expYear" => "20" . $data['expYear'],
            "type" => $data['branch'],
            "address" => $address
        ];
        $response = $this->sendPost($datasent, $this->getTestUrl($source->user) . env('PAYU_REST') . 'customers/' . $source->client_id . '/creditCards');
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
        $url = $this->getTestUrl($source->user) . env('PAYU_REST') . 'customers/' . $source->client_id . '/creditCards/' . $token;
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
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
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
        $response = $this->sendPost($dataSent, $this->getTestUrl($user) . env('PAYU_REST') . 'customers/');
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
        $url = $this->getTestUrl($source->user) . env('PAYU_REST') . 'customers/' . $source->client_id;
        return $this->sendGet($url);
    }

    public function deleteClient(User $user, $client) {
        $sources = $user->sources()->where('gateway', "PayU")
                        ->where('client_id', $client)->get();
        if ($sources) {
            $url = $this->getTestUrl($user) . env('PAYU_REST') . 'customers/' . $client;
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
            "notifyUrl" => "https://lonchis.com.co/api/payu/webhook",
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

    public function createToken(User $user, array $data) {
        $source = $user->sources()->where("gateway", "PayU")->first();
        if ($source) {
            if ($source->source) {
                $this->deleteToken($user);
            }
        }
        $merchant = $this->populateMerchant($user);
        $creditCardToken = [
            "payerId" => $user->id,
            "name" => $data['payer_name'],
            "identificationNumber" => $data['payer_id'],
            "paymentMethod" => $data['cc_branch'],
            "number" => $data['cc_number'],
            "expirationDate" => "20" . $data['cc_expiration_year'] . "/" . $data['cc_expiration_month'],
            "name" => $data['cc_name']
        ];
        $dataSent = [
            "language" => "es",
            "command" => "CREATE_TOKEN",
            "merchant" => $merchant,
            "creditCardToken" => $creditCardToken,
        ];
        $payer = $this->populatePayer($data);
        $payer['method'] = $data['cc_branch'];
        //dd($dataSent);

        $result = $this->sendRequest($dataSent, $this->getTestUrl($user) . env('PAYU_PAYMENTS'));
        if ($result['code'] == "SUCCESS") {
            $token = $result['creditCardToken'];
            if ($source) {
                $source->source = $token['creditCardTokenId'];
                $source->extra = json_encode($payer);
                $source->has_default = true;
                $source->save();
            } else {
                $source = new Source([
                    "gateway" => "PayU",
                    "source" => $token['creditCardTokenId'],
                    "has_default" => true,
                    "extra" => json_encode($payer)
                ]);
                $user->sources()->save($source);
            }
        }
        return $result;
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

    private function checkToken(Source $source) {
        $today = date_create();
        $date = date_create();
        date_add($date, date_interval_create_from_date_string("1 months"));
        $merchant = $this->populateMerchant($source->user);
        $creditCardToken = [
            "payerId" => $source->user_id,
            "creditCardTokenId" => $source->source,
            "startDate" => date_format($today, "Y-m-d") . "T" . date_format($today, "H:m:s"),
            "endDate" => date_format($date, "Y-m-d") . "T" . date_format($date, "H:m:s")
        ];
        $dataSent = [
            "language" => "es",
            "command" => "GET_TOKENS",
            "merchant" => $merchant,
            "creditCardTokenInformation" => $creditCardToken,
        ];
        $result = $this->sendRequest($dataSent, $this->getTestUrl($source->user) . env('PAYU_PAYMENTS'));
        if (!$result["creditCardTokenList"]) {
            $source->has_default = false;
            $source->source = "";
            $source->save();
        }
        return null;
    }

    public function checkTokens() {
        $sources = Source::where("has_default", true)->where("gateway", "PayU")->get();
        foreach ($sources as $value) {
            $this->checkToken($value);
        }
    }

    public function useToken(User $user, array $data, Payment $payment, $platform) {
        $validator = $this->validatorBuyer($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $validator = $this->validatorPayer($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $validator = $this->validatorToken($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $buyer = $this->populateBuyer($user, $data);
        $ShippingAddress = $this->populateShippingFromAddress($payment->address_id, $data);
        $payer = $this->populatePayer($data);
        $merchant = $this->populateMerchant($user);
        $orderCont = $this->populatePaymentContent($payment, $platform);
        $additionalValuesCont = $this->populateTotals($payment, "COP");
        $orderCont["additionalValues"] = $additionalValuesCont;
        $orderCont["buyer"] = $buyer;
        $orderCont["shippingAddress"] = $ShippingAddress;
        $extraParams = [
            "INSTALLMENTS_NUMBER" => 1
        ];
        $deviceSessionId = md5(session_id() . microtime());
        $cookie = md5($deviceSessionId);
        $transaction = [
            "order" => $orderCont,
            "payer" => $payer,
            "creditCardTokenId" => $data['token'],
            "extraParameters" => $extraParams,
            "type" => "AUTHORIZATION_AND_CAPTURE",
            "paymentMethod" => $data['cc_branch'],
            "paymentCountry" => $data['payer_country'],
            "deviceSessionId" => $deviceSessionId,
            "ipAddress" => $data['ip_address'],
            "cookie" => $cookie,
            "userAgent" => $data['user_agent']
        ];
        $dataSent = [
            "language" => "es",
            "command" => "SUBMIT_TRANSACTION",
            "merchant" => $merchant,
            "transaction" => $transaction,
            "test" => $this->getTestVar($user),
        ];
        $result = $this->sendRequest($dataSent, $this->getTestUrl($user) . env('PAYU_PAYMENTS'));
        return $this->handleTransactionResponse($result, $user, $payment, $dataSent, $platform, "COP");
    }

    public function deleteSubscription(User $user, $subscription) {
        $url = $this->getTestUrl($user) . env('PAYU_REST') . 'subscriptions/' . $subscription;
        $result = $this->sendDelete($url);
        $user->subscriptions()->where('gateway', "PayU")->where('source_id', $subscription)->delete();
        return $result;
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

        return $this->sendRequest($dataSent, $this->getTestUrl(null) . env('PAYU_PAYMENTS'));
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
            "command" => "ORDER_DETAIL_BY_REFERENCE_CODE",
            "merchant" => $merchant,
            "details" => $details,
            "test" => false,
        ];

        return $this->sendRequest($dataSent, $this->getTestUrl(null) . env('PAYU_REPORTS'));
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

        return $this->sendRequest($dataSent, $this->getTestUrl(null) . env('PAYU_REPORTS'));
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

        return $this->sendRequest($dataSent, $this->getTestUrl(null) . env('PAYU_PAYMENTS'));
    }

    public function handleTransactionResponse($response, User $user, Payment $payment, $dataSent, $platform, $currency) {
        if ($response['code'] == "SUCCESS") {
            if ($user) {
                $transactionResponse = $response['transactionResponse'];
                $transactionContainer = [];
                $transactionContainer['order_id'] = $payment->order_id;
                $transactionContainer['reference_sale'] = $payment->referenceCode;
                $transactionContainer['user_id'] = $user->id;
                $transactionContainer['gateway'] = 'PayU';
                $transactionContainer['currency'] = $currency;
                $transactionContainer['payment_method'] = 'CreditCard';
                $transactionContainer['description'] = $transactionResponse['responseMessage'];
                $transactionContainer['transaction_id'] = $transactionResponse['transactionId'];
                $transactionContainer['transaction_state'] = $transactionResponse['state'];
                $transactionContainer['response_code'] = $transactionResponse['responseCode'];
                $transactionContainer['transaction_date'] = date("Y-m-d h:m:s", $transactionResponse['operationDate'] / 1000);
                $transactionContainer["extras"] = json_encode($transactionResponse);
                /* if (array_key_exists("extras", $transactionResponse)) {
                  $extras = $transactionResponse['extras'];
                  unset($transactionResponse['extras']);
                  $transactionResponse['extras'] = json_encode($extras);
                  } */
                $transaction = Transaction::create($transactionContainer);
                $payment->transactions()->save($transaction);
                if ($transactionResponse['state'] == 'APPROVED') {
                    dispatch(new ApprovePayment($payment, $platform));
                } else if ($transactionResponse['state'] == 'PENDING') {
                    dispatch(new PendingPayment($payment, $platform));
                } else {
                    dispatch(new DenyPayment($payment, $platform));
                }
                $transaction->ur = $this->getTestUrl($user);
                return ["status" => "success", "transaction" => $transaction, "response" => $response, "message" => $transactionResponse['responseCode']];
            }
        }
        $transactionContainer = [];
        $transactionContainer['order_id'] = $payment->order_id;
        $transactionContainer['orderId'] = $payment->order_id;
        $transactionContainer['reference_sale'] = $payment->referenceCode;
        $transactionContainer['user_id'] = $user->id;
        $transactionContainer['gateway'] = 'PayU';
        $transactionContainer['currency'] = $currency;
        $transactionContainer["url"] = $this->getTestUrl($user);
        $transactionContainer['payment_method'] = 'CreditCard';
        $transactionContainer['description'] = "error";
        $transactionContainer['transaction_id'] = "-1";
        $transactionContainer['transaction_state'] = "ERROR";
        $transactionContainer['response_code'] = "ERROR";
        $transactionContainer['responseMessage'] = "ERROR";
        $transactionContainer['transactionId'] = "-1";
        $transactionContainer['state'] = "ERROR";
        $transactionContainer['responseCode'] = "ERROR";
        $transactionContainer['transaction_date'] = date("Y-m-d h:m:s");
        $transactionContainer['operationDate'] = date("Y-m-d h:m:s");
        $response['transactionResponse'] = $transactionContainer;
        return ["status" => "error", "response" => $response, "message" => $dataSent];
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
            'Accept: application/json'
                )
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

                $response = $this->getStatusOrderRef($payment->referenceCode);
                if ($response['code'] == "SUCCESS") {
                    $result = $response['result'];
                    $payload = $result['payload'];
                    if ($payload) {
                        $transactions = [];
                        foreach ($payload as $result) {
                            if ($result['status'] == "IN_PROGRESS") {
                                break;
                            } else {
                                $transactions = $result['transactions'];
                                $payload = $result;
                                break;
                            }
                        }
                        $transactionResponse = null;
                        if (count($transactions) > 0) {
                            foreach ($transactions as $transaction) {
                                $transactionResponse = $transaction['transactionResponse'];
                                $transactionResponse['id'] = $transaction['id'];
                                $transactionResponse['payment_method'] = $transaction["paymentMethod"];
                                $transactionResponse['reference_code'] = $payload["referenceCode"];
                                break;
                            }
                        }
                        if ($transactionResponse) {
                            $transactionResponse['order_id'] = $payment->order_id;
                            $transactionResponse['user_id'] = $payment->user_id;
                            $transactionResponse['referenceCode'] = $payment->referenceCode;
                            $transactionResponse['gateway'] = 'PayU';
                            $transaction = $this->saveTransactionQuery($transactionResponse, $payment);
                            if ($transactionResponse['state'] == 'APPROVED') {
                                dispatch(new ApprovePayment($payment, "Food"));
                            }
                            if ($transactionResponse['state'] == 'DECLINED' || $transactionResponse['state'] == 'ERROR' || $transactionResponse['state'] == 'EXPIRED') {
                                dispatch(new DenyPayment($payment, "Food"));
                            }
                        }


                        /* if (array_key_exists("extras", $transactionResponse)) {
                          $extras = $transactionResponse['extras'];
                          unset($transactionResponse['extras']);
                          $transactionResponse['extras'] = json_encode($extras);
                          } */


                        //return ["status" => "success", "transaction" => $transaction, "response" => $response];
                    } else {
                        $payment->status = "invisible";
                        $payment->save();
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
//        $current .= PHP_EOL;4Vj8eK4rloUd272L48hsrarnUA~508029~payment_42_order_43_1543377788~72000.0~COP~4
//        $current .= PHP_EOL;
//        $current .= PHP_EOL;
//        file_put_contents($file, $current);
        $ApiKey = env('PAYU_KEY');
        $transactionId = $data['transaction_id'];
        $merchant_id = $data['merchant_id'];
        $referenceCode = $data['reference_sale'];
        $TX_VALUE = $data['value'];
        $New_value = number_format($TX_VALUE, 1, '.', '');
        $currency = $data['currency'];
        $transactionState = $data['state_pol'];
        $firma_cadena = "$ApiKey~$merchant_id~$referenceCode~$New_value~$currency~$transactionState";
        //dd($firma_cadena);
        $firmacreada = sha1($firma_cadena);
        $firma = $data['sign'];
        if ($firmacreada == $firma) {
            $transactionExists = Transaction::where("transaction_id", $transactionId)->where('gateway', 'PayU')->first();
            if ($transactionExists) {
                return ["status" => "success", "message" => "transaction already processed", "data" => $data];
            }
            $payment = Payment::where("referenceCode", $referenceCode)->first();
            if ($payment) {
                $data['user_id'] = $payment->user_id;
                $data['order_id'] = $payment->order_id;
                $transaction = $this->saveTransactionConfirmacion($data, $payment);
                if ($data['state_pol'] == 4) {
                    dispatch(new ApprovePayment($payment, "Food"));
                } else {
                    dispatch(new DenyPayment($payment, "Food"));
                }
            } else {
                if (array_key_exists("reference_recurring_payment", $data)) {
                    if ($data['state_pol'] == 4) {
                        $results = explode("_", $data["reference_recurring_payment"]);
                        $subscriptionL = Subscription::where("source_id", $results[0])->first();
                        if ($subscriptionL) {
                            $subscriptionL->ends_at = Date($data['date_next_payment']);
                            $objectType = "App\\Models\\" . $subscriptionL->type;
                            $object = new $objectType;
                            $target = $object->find($subscriptionL->object_id);
                            if ($target) {
                                $target->ends_at = $subscriptionL->ends_at;
                                $target->save();
                            }
                            $subscriptionL->save();
                        }
                    }
                }
            }

            return ["status" => "success", "message" => "transaction processed", "data" => $data];
        } else {
            return ["status" => "error", "message" => "signature", "data" => $data];
        }
    }

    private function saveTransactionConfirmacion(array $data, Payment $payment) {
        $transactionId = $data['transaction_id'];
        $transaction = Transaction::where("transaction_id", $transactionId)->where('gateway', 'PayU')->first();
        if ($transaction) {
            $transaction->currency = $data['currency'];
            $transaction->transaction_state = $data['response_message_pol'];
            $transaction->description = $data['response_message_pol'];
            $transaction->reference_sale = $data['reference_sale'];
            $transaction->payment_method = $data['payment_method_name'];
            $transaction->transaction_id = $data['transaction_id'];
            $transaction->transaction_date = $data['transaction_date'];
            $transaction->response_code = $data['response_code_pol'];
            $transaction->extras = json_encode($data);
            $transaction->save();
        } else {
            $insert = [];
            $insert["reference_sale"] = $data["reference_sale"];
            $insert["order_id"] = $data["order_id"];
            $insert["user_id"] = $data["user_id"];
            $insert["response_code"] = $data["response_message_pol"];
            $insert["currency"] = $data["currency"];
            $insert["payment_method"] = $data["payment_method_type"];
            $insert["transaction_id"] = $data["transaction_id"];
            $insert["gateway"] = "PayU";
            $insert["description"] = $data["response_message_pol"];
            $insert["transaction_date"] = $data["transaction_date"];
            $insert["transaction_state"] = $data["response_message_pol"];
            $insert["extras"] = json_encode($data);
            $transaction = Transaction::create($insert);
            $payment->transactions()->save($transaction);
        }
        return $transaction;
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
        $transaction = Transaction::where("transaction_id", $transactionId)->where('gateway', 'PayU')->first();
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
            $insert["gateway"] = "PayU";
            $insert["currency"] = "COP";
            $insert["description"] = $data["responseMessage"];
            $insert["transaction_date"] = date("Y-m-d h:m:s", $data['operationDate'] / 1000);
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
    public function returnPayu(array $data) {
        
        $ApiKey = env('PAYU_KEY');
        
        $merchant_id = $data['merchantId'];
        $referenceCode = $data['referenceCode'];
        $TX_VALUE = $data['TX_VALUE'];
        $New_value = number_format($TX_VALUE, 1, '.', '');
        $currency = $data['currency'];
        $transactionState = $data['transactionState'];
        $firma_cadena = "$ApiKey~$merchant_id~$referenceCode~$New_value~$currency~$transactionState";
        $firmacreada = sha1($firma_cadena);
        $firma = $data['signature'];
        $estadoTx = "";
        $transactionId = $data['transactionId'];
        if ($firma == $firmacreada) {
            //if (strtoupper($firma) == strtoupper($firmacreada)) {
            if ($data['transactionState'] == 4) {
                $estadoTx = "Transaction approved";
            } else if ($data['transactionState'] == 6) {
                $estadoTx = "Transaction rejected";
            } else if ($data['transactionState'] == 104) {
                $estadoTx = "Error";
            } else if ($data['transactionState'] == 7) {
                $estadoTx = "Pending payment";
            } else {
                $estadoTx = $data['mensaje'];
            }

            $transaction = Transaction::where("transaction_id", $transactionId)->where('gateway', 'PayU')->first();
            if ($transaction) {
                $data['estadoTx'] = $transaction->transaction_state;
                $data['transaction'] = $transaction;
                return $data;
            }
            $payment = Payment::where("referenceCode", $referenceCode)->first();
            if ($payment) {
                $data['user_id'] = $payment->user_id;
                $data['order_id'] = $payment->order_id;
                $transaction = $this->saveTransactionRespuesta($data, $payment);
                $data['transaction'] = $transaction;
                if ($data['transactionState'] == 4) {
                    $estadoTx = "Transaction approved";
                    dispatch(new ApprovePayment($payment, "Food"));
                } else if ($data['transactionState'] == 6) {
                    $estadoTx = "Transaction rejected";
                    dispatch(new DenyPayment($payment, "Food"));
                } else if ($data['transactionState'] == 104) {
                    $estadoTx = "Error";
                    dispatch(new DenyPayment($payment, "Food"));
                } else if ($data['transactionState'] == 7) {
                    $estadoTx = "Pending payment";
                    dispatch(new PendingPayment($payment, "Food"));
                } else {
                    $estadoTx = $data['mensaje'];
                }
            }
        } else {
            $estadoTx = "Transaction failed security check";
        }
        $data['estadoTx'] = $estadoTx;
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
                    'financial_institution_code' => 'required|max:255',
                    'user_type' => 'required|max:255',
                    'doc_type' => 'required|max:255',
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
                    'payment_method' => 'required|max:255',
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
    public function validatorBuyer(array $data) {
        return Validator::make($data, [
                    'buyer_address' => 'required|max:255',
                    'buyer_city' => 'required|max:255',
                    'buyer_state' => 'required|max:255',
                    'buyer_country' => 'required|max:255',
                    'buyer_postal' => 'required|max:255',
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
                    'buyer_address' => 'required|max:255',
                    'buyer_city' => 'required|max:255',
                    'buyer_state' => 'required|max:255',
                    'buyer_country' => 'required|max:255',
                    'buyer_postal' => 'required|max:255',
                        ]
        );
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
