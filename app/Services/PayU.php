<?php

namespace App\Services;

use Validator;
use App\Models\Country;
use App\Models\User;
use App\Models\Order;
use App\Models\Region;
use App\Models\City;
use App\Models\Transaction;
use App\Services\EditOrder;

class PayU {

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
    }

    public function payCreditCard(User $user, array $data, Order $order) {
        $validator = $this->validatorCC($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $billing = $order->orderAddresses()->where('type', "billing")->first();
        if ($billing) {


            $billingCountry = Country::find($billing->country_id);
            $billingRegion = Region::find($billing->region_id);
            $billingCity = City::find($billing->city_id);
            $shippingCountry = Country::find($billing->country_id);
            $shippingRegion = Region::find($billing->region_id);
            $shippingCity = City::find($billing->city_id);
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
                "city" => $billingCity->name,
                "state" => $billingRegion->name,
                "country" => $billingCountry->code,
                "postalCode" => $billing->postal,
                "phone" => $billing->phone
            ];
            $buyer = [
                "merchantBuyerId" => "1",
                "fullName" => $billingCity->name,
                "emailAddress" => $data['payer_email'],
                "contactPhone" => $billing->phone,
                "dniNumber" => $data['payer_id'],
                "shippingAddress" => $buyerAddress
            ];
            $shipping = $order->orderAddresses()->where('type', "shipping")->first();
            if ($shipping) {
                $ShippingAddress = [
                    "street1" => $shipping->address,
                    "street2" => "",
                    "city" => $shippingCity->name,
                    "state" => $shippingRegion->name,
                    "country" => $shippingCountry->code,
                    "postalCode" => $shipping->postal,
                    "phone" => $shipping->phone
                ];
            } else {
                $ShippingAddress = [
                    "street1" => $billing->address,
                    "street2" => "",
                    "city" => $billingCity->name,
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
                "city" => $billingCity->name,
                "state" => $billingRegion->name,
                "country" => $billingCountry->code,
                "postalCode" => $billing->postal,
                "phone" => $billing->phone
            ];
            $payer = [
                "merchantPayerId" => "1",
                "fullName" => $billingCity->name,
                "emailAddress" => $data['payer_email'],
                "contactPhone" => $billing->phone,
                "dniNumber" => $data['payer_id'],
                "billingAddress" => $payerAddress
            ];
            $creditCard = [
                "number" => $data['cc_number'],
                "securityCode" => $data['cc_security_code'],
                "expirationDate" => $data['cc_expiration_year'] . "/" . $data['cc_expiration_month'],
                "name" => $data['cc_name']
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
            $result = $this->sendRequest($dataSent, false);
            return $this->handleTransactionResponse($result, $user, $order);
        }
        return array("status" => "error", "message" => "missing billing Address");
    }

    public function payToken(User $user, array $data, Order $order) {
        $validator = $this->validatorToken($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $billing = $order->orderAddresses()->where('type', "billing")->first();
        if ($billing) {
            $creditCardTokenId = "";


            $billingCountry = Country::find($billing->country_id);
            $billingRegion = Region::find($billing->region_id);
            $billingCity = City::find($billing->city_id);
            $shippingCountry = Country::find($billing->country_id);
            $shippingRegion = Region::find($billing->region_id);
            $shippingCity = City::find($billing->city_id);
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
                "city" => $billingCity->name,
                "state" => $billingRegion->name,
                "country" => $billingCountry->code,
                "postalCode" => $billing->postal,
                "phone" => $billing->phone
            ];
            $buyer = [
                "merchantBuyerId" => "1",
                "fullName" => $billingCity->name,
                "emailAddress" => $data['payer_email'],
                "contactPhone" => $billing->phone,
                "dniNumber" => $data['payer_id'],
                "shippingAddress" => $buyerAddress
            ];
            $shipping = $order->orderAddresses()->where('type', "shipping")->first();
            if ($shipping) {
                $ShippingAddress = [
                    "street1" => $shipping->address,
                    "street2" => "",
                    "city" => $shippingCity->name,
                    "state" => $shippingRegion->name,
                    "country" => $shippingCountry->code,
                    "postalCode" => $shipping->postal,
                    "phone" => $shipping->phone
                ];
            } else {
                $ShippingAddress = [
                    "street1" => $billing->address,
                    "street2" => "",
                    "city" => $billingCity->name,
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
                "city" => $billingCity->name,
                "state" => $billingRegion->name,
                "country" => $billingCountry->code,
                "postalCode" => $billing->postal,
                "phone" => $billing->phone
            ];
            $payer = [
                "merchantPayerId" => "1",
                "fullName" => $billingCity->name,
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
            $result = $this->sendRequest($dataSent, false);
            return $this->handleTransactionResponse($result, $user, $order);
        }
        return array("status" => "error", "message" => "missing billing Address");
    }

    public function createToken(User $user, array $data) {
        $apiLogin = env('PAYU_LOGIN');
        $apiKey = env('PAYU_KEY');
        $merchant = [
            'apiLogin' => $apiLogin,
            'apiKey' => $apiKey
        ];
        $creditCardToken = [
            "payerId" => "10",
            "name" => "full name",
            "identificationNumber" => "32144457",
            "paymentMethod" => "VISA",
            "number" => "4111111111111111",
            "expirationDate" => "2017/01"
        ];
        $request = [
            "command" => "CREATE_TOKEN",
            "merchant" => $merchant,
            "creditCardToken" => $creditCardToken
        ];
    }

    public function payDebitCard(User $user, array $data, Order $order) {
        $validator = $this->validatorDebit($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $billing = $order->orderAddresses()->where('type', "billing")->first();
        if ($billing) {
            $billingCountry = Country::find($billing->country_id);
            $billingCity = City::find($billing->city_id);
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
                "fullName" => $billingCity->name,
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

            $result = $this->sendRequest($dataSent, false);
            return $this->handleTransactionResponse($result, $user, $order);
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
        $result = $this->sendRequest($dataSent, false);
        return $this->handleTransactionResponse($result, $user, $order);
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
        return $this->sendRequest($dataSent, false);
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

        return $this->sendRequest($dataSent, false);
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

        return $this->sendRequest($dataSent, true);
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

        return $this->sendRequest($dataSent, true);
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

        return $this->sendRequest($dataSent, false);
    }

    public function handleTransactionResponse($response, User $user, Order $order) {
        if ($response['code'] == "SUCCESS") {
            if ($user) {
                $transactionResponse = $response['transactionResponse'];
                $transactionResponse['order_id'] = $order->id;
                $transactionResponse['referenceCode'] = $order->referenceCode;
                $transactionResponse['user_id'] = $user->id;
                $transactionResponse['gateway'] = 'payu';
                if (array_key_exists("extras", $transactionResponse)) {
                    $extras = $transactionResponse['extras'];
                    unset($transactionResponse['extras']);
                    $transactionResponse['extras'] = json_encode($extras);
                }
                $transaction = Transaction::create($transactionResponse);
                if ($transactionResponse['state'] == 'APPROVED') {
                    $this->editOrder->approveOrder($order, $transaction->id);
                } else if ($transactionResponse['state'] == 'PENDING') {
                    $this->editOrder->setOrderPending($order, $transaction->id);
                } else {
                    $this->editOrder->cancelOrder($order, $transaction->id);
                }
                return ["status" => "success", "transaction" => $transaction, "response" => $response];
            } else {
                return $response;
            }
        }
    }

    public function sendRequest(array $data, $query) {

//        $client = new Client([
//            // Base URI is used with relative requests
//            'base_uri' => 'https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi',
//            // You can set any number of default request options.
//            'timeout' => 10.0,
//        ]);
//        $headers = [
//            'Content-Type' => 'application/json; charset=utf-8',
//            'Accept' => 'application/json'
//        ];
//        $r = $client->request('POST', 'https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi', [
//            'json' => $data
//        ]);
//        dd($r);
//        return $r->getBody();
        //return $data;
        $data_string = json_encode($data);
        if ($query) {
            $curl = curl_init(env('PAYU_REPORTS'));
        } else {
            $curl = curl_init(env('PAYU_PAYMENTS'));
        }
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
                    $this->editOrder->approveOrder($order, $transaction->id);
                } else if ($transactionResponse['state'] == 'PENDING') {
                    continue;
                } else {
                    $this->editOrder->cancelOrder($order, $transaction->id);
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
    public function webhookPayU(array $data) {
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
                    $this->editOrder->approveOrder($order, $transaction->id);
                    $transaction->description = "Transacción aprobada";
                } else if ($data['transactionState'] == 6) {
                    $this->editOrder->cancelOrder($order, $transaction->id);
                    $transaction->description = "Transacción rechazada";
                } else if ($data['transactionState'] == 104) {
                    $this->editOrder->cancelOrder($order, $transaction->id);
                    $transaction->description = "Error";
                } else if ($data['transactionState'] == 7) {
                    $this->editOrder->setOrderPending($order, $transaction->id);
                } else {
                    $transaction->description = $data['mensaje'];
                }
                $transaction->save();
                return ["status" => "success", "message" => "transaction processed", "data" => $data ];
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
                    $this->editOrder->approveOrder($order, $transaction->id);
                    $transaction->description = "Transacción aprobada";
                } else if ($data['transactionState'] == 6) {
                    $this->editOrder->cancelOrder($order, $transaction->id);
                    $transaction->description = "Transacción rechazada";
                } else if ($data['transactionState'] == 104) {
                    $this->editOrder->cancelOrder($order, $transaction->id);
                    $transaction->description = "Error";
                } else if ($data['transactionState'] == 7) {
                    $this->editOrder->setOrderPending($order, $transaction->id);
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

}
