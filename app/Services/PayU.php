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

    public function payCreditCard(User $user, array $data) {
        $validator = $this->validatorCC($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $order = $this->editOrder->prepareOrder($user);
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
            $apiLogin = "pRRXKOl8ikMmt9u";
            $apiKey = "4Vj8eK4rloUd272L48hsrarnUA";
            $reference = "besafe_test_1_" . $order->id;
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
            $result = $this->sendRequest($dataSent);
            return $this->handleTransactionResponse($result, $user, $order);
        }
        return array("status" => "error", "message" => "missing billing Address");
    }

    public function payDebitCard(User $user, array $data) {
        $validator = $this->validatorDebit($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $order = $this->editOrder->prepareOrder($user);
        $billing = $order->orderAddresses()->where('type', "billing")->first();
        if ($billing) {
            $billingCountry = Country::find($billing->country_id);
            $billingCity = City::find($billing->city_id);
            $deviceSessionId = md5(session_id() . microtime());
            $accountId = "512321";
            $apiLogin = "pRRXKOl8ikMmt9u";
            $apiKey = "4Vj8eK4rloUd272L48hsrarnUA";
            $reference = "besafe_test_1_" . $order->id;
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

            $result = $this->sendRequest($dataSent);
            return $this->handleTransactionResponse($result, $user, $order);
        }
        return array("status" => "error", "message" => "missing billing Address");
    }

    public function payCash(User $user, array $data) {
        $validator = $this->validatorCash($data);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->getMessageBag()]);
        }
        $order = $this->editOrder->prepareOrder($user);
        $accountId = "512321";
        $apiLogin = "pRRXKOl8ikMmt9u";
        $apiKey = "4Vj8eK4rloUd272L48hsrarnUA";
        $reference = "besafe_test_1_" . $order->id;
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
        $result = $this->sendRequest($dataSent);
        return $this->handleTransactionResponse($result, $user, $order);
    }

    public function getBanks() {
        $apiLogin = "pRRXKOl8ikMmt9u";
        $apiKey = "4Vj8eK4rloUd272L48hsrarnUA";
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
        return $this->sendRequest($dataSent);
    }

    public function getPaymentMethods() {
        $apiLogin = "pRRXKOl8ikMmt9u";
        $apiKey = "4Vj8eK4rloUd272L48hsrarnUA";
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

        return $this->sendRequest($dataSent);
    }

    public function getStatusOrderId($order_id) {
        $apiLogin = "pRRXKOl8ikMmt9u";
        $apiKey = "4Vj8eK4rloUd272L48hsrarnUA";
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

        return $this->sendRequest($dataSent);
    }

    public function getStatusOrderRef($order_ref) {
        $apiLogin = "pRRXKOl8ikMmt9u";
        $apiKey = "4Vj8eK4rloUd272L48hsrarnUA";
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

        return $this->sendRequest($dataSent);
    }

    public function getStatusTransaction($transaction_id) {
        $apiLogin = "pRRXKOl8ikMmt9u";
        $apiKey = "4Vj8eK4rloUd272L48hsrarnUA";
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

        return $this->sendRequest($dataSent);
    }

    public function handleTransactionResponse($response, User $user, Order $order) {
        if ($response['code'] == "SUCCESS") {
            if ($user) {
                $transactionResponse = $response['transactionResponse'];
                $transactionResponse['order_id'] = $order->id;
                $transactionResponse['user_id'] = $user->id;
                if (array_key_exists("extras", $transactionResponse)) {
                    $extras = $transactionResponse['extras'];
                    unset($transactionResponse['extras']);
                    $transactionResponse['extras'] = json_encode($extras);
                }
                $transaction = Transaction::create($transactionResponse);
                return ["status" => "success", "transaction" => $transaction, "response" => $response];
            } else {
                return $response;
            }
        }
    }

    public function sendRequest(array $data) {

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
        $curl = curl_init('https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi');
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
        return  $response;
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
            dd($response);
            if ($response['code'] == "SUCCESS") {
                $result = $response['result'];
                $payload = $result['payload'];
                if ($payload['state'] == "PENDING") {
                    continue;
                } else {
                    Transaction::where('transactionId', $transaction->transactionId)
                            ->update($payload);
                    $transaction = Transaction::find($transaction->id);
                    $this->editOrder->approveOrder($transaction->order_id);
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
