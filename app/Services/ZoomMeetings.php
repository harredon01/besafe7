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

    public function getUsers($user) {
        return $this->sendGet($this->getTestUrl($user) . "users",[]);
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
            $results = $this->sendGet($this->getTestUrl($user) . "users/" . $user->email, []);
            if (array_key_exists("code", $results)) {
                if ($results['code'] == 1001) {
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
                    $saved = false;
                    if (array_key_exists("id", $dataSent)) {
                        $saved = true;
                        $push = new Push(["platform" => self::PLATFORM, "object_id" => $results['id']]);
                        $user->push()->save($push);
                    }
                    $type = "zoom";
                    if (!$saved) {
                        $type = "zoom_error";
                    }

                    $followers = [$user];
                    $data = [
                        "trigger_id" => $user->id,
                        "message" => "",
                        "subject" => "",
                        "object" => "User",
                        "sign" => true,
                        "payload" => [],
                        "type" => $type,
                        "user_status" => "normal"
                    ];
                    $date = date("Y-m-d H:i:s");
                    $notifications = app('Notifications');
                    //$notifications->sendMassMessage($data, $followers, null, true, $date, true);
                }
            } else if (array_key_exists("id", $results)) {
                $push = new Push(["platform" => self::PLATFORM, "object_id" => $results['id']]);
                $user->push()->save($push);
            }
            return $push;
        }
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
                "topic" => $booking->id,
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
            return $results;
        }
        $this->createUser($user);
        return $this->createMeeting($user, $booking);
    }

    public function endMeeting($meeting) {
        $dataSent = [
            "action" => "end",
        ];
        $this->sendPut($dataSent, $this->getTestUrl($meeting) . "meetings/" . $meeting . "/status");
        $this->sendDelete($dataSent, $this->getTestUrl($meeting) . "meetings/" . $meeting);
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
        echo $query . PHP_EOL;
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
            // 'Content-Length: ' . strlen($data_string),
            // 'Accept: application/json',
            //  'Accept-language: es',
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

    public function sendGet($query, $data) {
        $data_string = json_encode($data);
        $headers = $this->getHeaders($data_string);
        if ($data) {
            $query = $query . "?" . http_build_query($data);
        }
        $curl = curl_init($query);
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
        $file = '/home/hoovert/access.log';
        // Open the file to get existing content
        $current = file_get_contents($file);
        //$daarray = json_decode(json_encode($data));
        // Append a new person to the file

        $current .= json_encode($data);
        $current .= PHP_EOL;
        $current .= PHP_EOL;
        $current .= PHP_EOL;
        $current .= PHP_EOL;
        file_put_contents($file, $current);
        if ($data['event'] == "meeting.started") {
            $bookingId = $data['payload']['object']['topic'];
            $booking = Booking::find($bookingId);
            if ($booking) {
                Booking::where("id", $booking->id)->update(['notes' => 'started', 'total_paid' => $booking->price]);
            }
            return ["status" => "success", "message" => "transaction processed"];
        } else if ($data['event'] == "meeting.ended") {
            $bookingId = $data['payload']['object']['topic'];
            $booking = Booking::find($bookingId);
            if ($booking) {
                $options = $booking->options;
                $options = $options->toArray();
                if (array_key_exists('call', $options)) {
                    if ($options['call']) {
                        $object = $booking->bookable;
                        $object->status = "online";
                        $object->save();
                    }
                }
                Booking::where("id", $booking->id)->update(['notes' => 'ended', 'total_paid' => $booking->price]);
            }

            return ["status" => "success", "message" => "transaction processed"];
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
