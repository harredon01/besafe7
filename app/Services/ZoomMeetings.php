<?php

namespace App\Services;

use Validator;
use App\Models\User;
use App\Models\Push;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ZoomMeetings {

    const PLATFORM = 'ZoomMeetings';

    public function getUsers() {
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
        return $this->sendRequest($dataSent, $this->getTestUrl($user) . "users/");
    }

    public function getUser(User $user) {
        $push = $user->push()->where('platform', self::PLATFORM)->first();
        if ($push) {
            return $this->sendRequest([], $this->getTestUrl($user) . "users/" . $push->object_id);
        }
        return ["status" => "error", "message" => "user must be created"];
    }

    public function createUser(User $user) {
        $push = $user->push()->where('platform', self::PLATFORM)->first();
        if ($push) {
            return $push;
        } else {
            $push = new Push(["platform" => self::PLATFORM, "object_id" => $results['id']]);
            $user->push()->save($push);
        }
        $dataSent = [
            "action" => "create",
            "user_info" => [
                "email" => $user->email,
                "type" => 1,
                "first_name" => $user->firstName,
                "last_name" => $user->lastName,
            ]
        ];
        $results = $this->sendPost($dataSent, $this->getTestUrl($user) . "users/");

        $push->object_id = $results['id'];
        $push->save();
        return ["status" => "success", "message" => "user must created"];
    }
 
    public function updateUser(User $user) {
        $push = $user->push()->where('platform', self::PLATFORM)->first();
        if ($push) {
            $dataSent = [
                "email" => $user->email,
                "type" => 1,
                "first_name" => $user->firstName,
                "last_name" => $user->lastName,
            ];
            $results = $this->sendPatch($dataSent, $this->getTestUrl($user) . "users/" . $push->object_id);
            return ["status" => "success", "message" => "user updated", "result" => $results];
        }
        return ["status" => "error", "message" => "user must created"];
    }

    private function getTestUrl($user) {
        return "https://api.zoom.us/v2/";
    }

    public function deleteUser(User $user) {
        $push = $user->push()->where('platform', self::PLATFORM)->first();
        if ($push) {
            $results = $this->sendDelete([], $this->getTestUrl($user) . "users/" . $push->object_id);
            return ["status" => "success", "message" => "user deleted", "result" => $results];
        }
        return ["status" => "error", "message" => "user not found"];
    }

    public function createMeeting(User $user, Booking $booking) {
        $push = $user->push()->where('platform', self::PLATFORM)->first();
        if ($push) {
            $type = 2;
            if ($booking->options['call']) {
                $type = 1;
            }
            $dataSent = [
                "topic" => "Meeting with: " . $booking->bookable->name,
                "type" => $type,
                "password" => "test",
                "agenda" => "",
                "settings" => [
                    "host_video" => true,
                    "join_before_host" => true,
                    "registrants_email_notification" => true
                ]
            ];
            if (!$booking->options['call']) {
                $date = date_create($booking->starts_at);
                $dateend = date_create($booking->ends_at);
                $date2 = date_format($date, "Y-m-d") . "T" . date_format($date, "G:i:s");
                $duration = (date_timestamp_get($date) - date_timestamp_get($dateend)) / (1000 * 60);
                $dataSent["start_time"] = $date2;
                $dataSent["duration"] = $duration;
            }
            $results = $this->sendPost($dataSent, $this->getTestUrl($user) . "users/" . $push->object_id . "/meetings");
        }
    }

    public function endMeeting($meeting) {
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
        return $this->sendRequest($dataSent, $this->getTestUrl($meeting) . "meetins/" . $meeting . "/status");
    }

    public function addUserToMeeting(User $user) {
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

    public function sendRequest(array $data, $query) {
        $data_string = json_encode($data);
        $curl = curl_init($query);
        $headers = $this->getHeaders($data_string);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    private function getHeaders($data_string) {
        $auth = env('ZOOM_JWT');
        $headers = array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string),
            'Accept: application/json',
            'Accept-language: es',
            'Authorization: Bearer ' . $auth,
        );
        return $headers;
    }

    public function sendPost(array $data, $query) {
        $data_string = json_encode($data);
        $curl = curl_init($query);
        $headers = $this->getHeaders($data_string);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    public function sendGet($query, array $data) {
        $data_string = json_encode($data);
        $curl = curl_init($query);
        $headers = $this->getHeaders($data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    public function sendPut(array $data, $query) {
        $data_string = json_encode($data);
        $headers = $this->getHeaders($data_string);
        $curl = curl_init($query);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    public function sendPatch(array $data, $query) {
        $data_string = json_encode($data);
        $headers = $this->getHeaders($data_string);
        $curl = curl_init($query);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    public function sendDelete(array $data, $query) {
        $data_string = json_encode($data);
        $headers = $this->getHeaders($data_string);
        $curl = curl_init($query);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
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
