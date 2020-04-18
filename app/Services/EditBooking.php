<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\Booking;
use App\Models\Favorite;
use App\Models\User;
use Validator;
use DB;
use Carbon\Carbon;

class EditBooking {

    const MODEL_PATH = 'App\\Models\\';
    const BOOKING_APPROVED = 'booking_approved';
    const OBJECT_BOOKING = 'Booking';
    const BOOKING_CREATED_BOOKABLE_PENDING = 'booking_created_bookable_pending';
    const BOOKING_UPDATED_BOOKABLE_PENDING = 'booking_updated_bookable_pending';
    const BOOKING_BOOKABLE_APPROVED = 'booking_bookable_approved';
    const BOOKING_BOOKABLE_DENIED = 'booking_bookable_denied';
    const BOOKING_DENIED = 'booking_denied';
    const BOOKING_CANCELLED = 'booking_cancelled';
    const BOOKING_REMINDER = 'booking_reminder';
    const BOOKING_STARTING = 'booking_starting';
    const BOOKING_WAITING = 'booking_waiting';
    const BOOKING_COMPLETED = 'booking_completed';
    const VIRTUAL_BOOKING = 1;

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
        $this->notifications = app('Notifications');
    }

    private function checkAvailable(array $data) {
        $from = $data['from'];
        $to = $data['to'];
        $dayofweek = date('l', strtotime($from));

        $dayName = strtolower($dayofweek);
        $availabilities = Availability::where("range", $dayName)->where("bookable_type", self::MODEL_PATH . $data['type'])->where("bookable_id", $data['object_id'])->get();
        if (count($availabilities) > 0) {
            $dateFrom = date_create($from);
            $fromString = "2019-01-01 " . date_format($dateFrom, "H:i:s");
            $dateTimestampFrom = strtotime($fromString);
            $dateTo = date_create($to);
            $toString = "2019-01-01 " . date_format($dateTo, "H:i:s");
            $dateTimestampTo = strtotime($toString);
            foreach ($availabilities as $av) {
                $dateFrom = date_create($av->from);
                $fromString = "2019-01-01 " . date_format($dateFrom, "H:i:s");
                $dateTimestampUpper = strtotime($fromString);
                $dateTo = date_create($av->to);
                $toString = "2019-01-01 " . date_format($dateTo, "H:i:s");
                $dateTimestampLower = strtotime($toString);
                if ($dateTimestampFrom >= $dateTimestampUpper && $dateTimestampFrom <= $dateTimestampLower && $dateTimestampTo >= $dateTimestampUpper && $dateTimestampTo <= $dateTimestampLower) {
                    return ['status' => "success", "message" => "booking_allowed"];
                }
            }
            return ['status' => "error", "message" => "booking_not_in_range"];
        }
        return ['status' => "success", "message" => "booking_allowed"];
        ;
    }

    public function checkExistingBooking(User $user, $booking_id) {
        $booking = Booking::where('id', $booking_id)->with("bookable")->first();
        if ($booking) {
            if ($booking->customer_id == $user->id) {
                $object = $booking->bookable;
                $attributes = $booking->options;
                $data = [
                    "from" => $booking->starts_at,
                    "to" => $booking->ends_at,
                    "type" => "Merchant",
                    "object_id" => $object->id,
                    "attributes" => $attributes,
                    "virtual_meeting" => $attributes['virtual_meeting']
                ];
                return $this->checkBookingAvailability($data, $object);
            }
            return ['status' => "error", "message" => "access_denied"];
        }
        return ['status' => "error", "message" => "not_found"];
    }

    public function checkBookingAvailability($data, $object) {
        $result = $this->checkAvailable($data);
        if ($result['status'] == 'success') {
            $result = $this->checkBooking($data, $object);
        }
        return $result;
    }

    public function checkBooking($data, $object) {
        $attributes = $object->attributes;
        $maxPerHour = 1;
        $checkZoom = false;
        if (array_key_exists("max_per_hour", $attributes)) {
            $maxPerHour = $attributes['max_per_hour'];
        }
        if (array_key_exists("virtual_provider", $attributes)) {
            if ($attributes['virtual_provider'] == "ZoomMeetings") {
                $checkZoom = true;
            }
        }
        $date = date_create($data['from']);
        date_sub($date, date_interval_create_from_date_string("1 seconds"));
        $date2 = date_create($data['to']);
        date_add($date2, date_interval_create_from_date_string("1 seconds"));
        if ($checkZoom) {
            $booking1 = Booking::whereBetween('starts_at', [date_format($date, "Y-m-d H:i:s"), date_format($date2, "Y-m-d H:i:s")])
                            ->whereColumn("price", "total_paid")->count();
            $booking2 = Booking::whereBetween('ends_at', [date_format($date, "Y-m-d H:i:s"), date_format($date2, "Y-m-d H:i:s")])
                            ->whereColumn("price", "total_paid")->count();
            if ($booking1 > self::VIRTUAL_BOOKING || $booking2 > self::VIRTUAL_BOOKING) {
                return ['status' => "error", "message" => "zoom_limit"];
            }
            return ['status' => "success", "message" => "zoom_ok"];
        } else {
            $array1 = $object->bookingsStartsBetween(date_format($date, "Y-m-d H:i:s"), date_format($date2, "Y-m-d H:i:s"))->whereColumn("price", "total_paid")->get();
            $array2 = $object->bookingsEndsBetween(date_format($date, "Y-m-d H:i:s"), date_format($date2, "Y-m-d H:i:s"))->whereColumn("price", "total_paid")->get();
            $array1 = $array1->toArray();
            $array2 = $array2->toArray();
            foreach ($array2 as $value) {
                $found = false;
                foreach ($array1 as $item) {
                    if ($item['id'] == $value['id']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    array_push($array1, $value);
                }
            }
            $totalHoping = count($array1) + 1;
            if ($totalHoping > $maxPerHour) {
                return ['status' => "error", "message" => "merchant_limit"];
            } else {
                return ['status' => "success", "message" => "merchant_ok"];
            }
        }
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function addBookingObject(array $data, User $user) {
        $validator = $this->validatorCreateBooking($data);
        if ($validator->fails()) {
            return array("status" => "error", "message" => $validator->getMessageBag());
        }
        $type = $data['type'];
        $class = self::MODEL_PATH . $type;

        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                if ($object->status != 'online' && $data['call']) {
                    return array("status" => "error", "message" => "only_allowed_online");
                }
                $result = $this->checkTime($data['from'], $object);
                if ($result['status'] == 'success') {
                    $result = $this->checkBookingAvailability($data, $object);
                    if ($result['status'] == 'success' || $data['call']) {
                        $update = [];
                        $update["notes"] = "pending";
                        $update["options"] = [];
                        $cost = 0;
                        $isCall = false;
                        $returnCost = false;
                        if (array_key_exists("attributes", $data)) {
                            $update["options"] = $data["attributes"];
                        }
                        $attributes = $object->attributes;
                        if (array_key_exists("call", $data)) {
                            if ($data['call']) {
                                $isCall = true;
                                $update["options"]["virtual_meeting"] = true;
                                if (array_key_exists("virtual_provider", $attributes)) {
                                    $update["options"]["location"] = $attributes["virtual_provider"];
                                }
                                $cost = $object->unit_cost;
                                $object->unit_cost = $object->unit_cost * 0.35;
                                $object->save();
                                $returnCost = true;
                            }
                        } else {
                            if (array_key_exists("virtual_meeting", $data)) {
                                if ($data['virtual_meeting']) {
                                    $update["options"]["virtual_meeting"] = true;
                                    if (array_key_exists("virtual_provider", $attributes)) {
                                        $update["options"]["location"] = $attributes["virtual_provider"];
                                    }
                                    $cost = $object->unit_cost;
                                    $object->unit_cost = $object->unit_cost * 0.5;
                                    $object->save();
                                    $returnCost = true;
                                }
                            }
                        }

                        $booking = $object->newBooking($user, $data['from'], $data['to']);
                        $requiresAuth = false;
                        $update["options"]["status"] = "approved";
                        $update["options"]["call"] = $data['call'];
                        if (!$isCall) {
                            if (array_key_exists("booking_requires_authorization", $attributes)) {
                                if ($attributes["booking_requires_authorization"]) {
                                    $update["total_paid"] = -1;
                                    $booking->total_paid = -1;
                                    $this->handleAuthorizationRequest($user, $booking, $object);
                                    $requiresAuth = true;
                                    $update["options"]["status"] = "pending";
                                }
                            }
                        }
                        $update["options"] = json_encode($update["options"]);
                        unset($update['bookable']);
                        unset($update['client']);
                        Booking::where("id", $booking->id)->update($update);
                        if ($returnCost) {
                            $object->unit_cost = $cost;
                            $object->save();
                        }
                        return array("status" => "success", "message" => "Booking created", "booking" => $booking, "requires_auth" => $requiresAuth);
                    }
                    return $result;
                }
                return array("status" => "error", "message" => "too_soon");
            }
        }
        return array("status" => "error", "message" => "not_found");
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function editBookingObject(array $data, User $user) {
        $validator = $this->validatorCreateBooking($data);
        if ($validator->fails()) {
            return array("status" => "error", "message" => $validator->getMessageBag());
        }
        $type = $data['type'];
        $class = self::MODEL_PATH . $type;

        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                $booking = Booking::find($data['booking_id']);
                if ($booking) {
                    if ($object->checkAdminAccess($user->id) || $booking->customer_id == $user->id) {
                        if ($booking->notes == 'pending') {
                            $result = $this->checkBookingAvailability($data, $object);
                            if ($result['status'] == 'success') {
                                if (strpos($data['from'], '.') !== false) {
                                    $a = explode(".", $data['from']);
                                    $data['from'] = str_replace("T", " ", $a[0]);
                                }
                                if (strpos($data['to'], '.') !== false) {
                                    $a = explode(".", $data['to']);
                                    $data['to'] = str_replace("T", " ", $a[0]);
                                }
                                $result = $this->checkTime($data['from'], $object);
                                if ($result['status'] == 'success') {
                                    $booking->starts_at = $data['from'];
                                    $booking->ends_at = $data['to'];
                                    $attributes = $booking->options;
                                    $reqAttrs = $data['attributes'];
                                    foreach ($reqAttrs as $key => $value) {
                                        $attributes[$key] = $reqAttrs[$key];
                                    }
                                    $booking->total_paid = 0;
                                    if ($booking->customer_id == $user->id) {
                                        $attributes2 = $object->attributes;
                                        if (array_key_exists("booking_requires_authorization", $attributes2)) {
                                            if ($attributes2["booking_requires_authorization"]) {
                                                $booking->total_paid = -1;
                                            }
                                        }
                                    }
                                    $booking->options = $attributes;
                                    $booking->updated_at = date("Y-m-d H:i:s");
                                    $update = $booking->toArray();
                                    $update["options"] = json_encode($attributes);
                                    Booking::where("id", $booking->id)->update($update);
                                    $this->handleAuthorizationRequest($user, $booking, $object);
                                    return array("status" => "success", "message" => "Booking update request sent", "booking" => $booking);
                                }
                                return array("status" => "error", "message" => "too_soon");
                            }
                            return $result;
                        }
                        return array("status" => "error", "message" => "booking_not_editable");
                    }
                    return array("status" => "error", "message" => "access_denied");
                }
                return array("status" => "error", "message" => "not_found");
            }
        }
        return array("status" => "error", "message" => "not_found");
    }

    private function handleAuthorizationRequest($user, $booking, $object) {
        $owner = null;
        $type = null;
        $payload = [
            "booking_id" => $booking->id,
            "date" => $booking->starts_at,
            "status" => "pending",
            "bookable" => $booking->bookable->name
        ];
        if ($booking->customer_id == $user->id) {
            $owner = $object->users()->first();
            $payload['bookclient'] = $user->firstName . " " . $user->lastName;
            $type = self::BOOKING_CREATED_BOOKABLE_PENDING;
        } else {
            $owner = $booking->customer;
            $type = self::BOOKING_UPDATED_BOOKABLE_PENDING;
        }
        $followers = [$owner];

        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => "Merchant",
            "sign" => true,
            "payload" => $payload,
            "type" => $type,
            "user_status" => $user->getUserNotifStatus()
        ];
        $date = date("Y-m-d H:i:s");
        $this->notifications->sendMassMessage($data, $followers, $user, true, $date, true);
    }

    public function checkTime($starts) {
        //return array("status" => "success", "message" => "Delivery in limit");
        $date = date_create();
        $now = date_format($date, "Y-m-d H:i:s");
        $datetimestampDelivery = strtotime($starts);
        $dateTimestampNow = strtotime($now);
        $diff = ($datetimestampDelivery - $dateTimestampNow) / 60;
        if ($diff < 14) {
            return array("status" => "error", "message" => "Limit passed");
        }
        return array("status" => "success", "message" => "Delivery in limit");
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function changeStatusBookingObject(array $data, User $user) {
        $validator = $this->validatorStatusBookings($data);
        if ($validator->fails()) {
            return array("status" => "error", "message" => $validator->getMessageBag());
        }
        $status = $data["status"];
        $booking = Booking::find($data['booking_id']);
        $object = $booking->bookable;
        if ($object) {
            if ($object->checkAdminAccess($user->id)) {
                $typeAlert = "";
                $customer = $booking->customer;
                $options = $booking->options->toArray();
                $updateData = [
                    "options" => $options,
                    "updated_at" => date_create()
                ];
                $followers = [$customer];
                $updateData["options"]["status"] = $status;
                $payload = [
                    "booking_id" => $booking->id,
                    "bookable" => $booking->name,
                    "date" => $booking->starts_at,
                    "reason" => ""
                ];
                if ($status == "approved") {
                    $updateData["total_paid"] = 0;
                    $typeAlert = self::BOOKING_BOOKABLE_APPROVED;
                } elseif ($status == "denied") {
                    $updateData["total_paid"] = -1;
                    if (array_key_exists("reason", $data)) {
                        $updateData["options"]["reason"] = $data["reason"];
                        $payload['reason'] = $data["reason"];
                    }
                    $typeAlert = self::BOOKING_BOOKABLE_DENIED;
                }
                $options = $updateData["options"];
                $updateData["options"] = json_encode($updateData["options"]);
                Booking::where("id", $booking->id)->update($updateData);
                $payload['options'] = $options;
                $data = [
                    "trigger_id" => $user->id,
                    "message" => "",
                    "subject" => "",
                    "object" => "Merchant",
                    "sign" => true,
                    "payload" => $payload,
                    "type" => $typeAlert,
                    "user_status" => $user->getUserNotifStatus()
                ];
                $date = date("Y-m-d H:i:s");
                $this->notifications->sendMassMessage($data, $followers, $user, true, $date, true);
                return array("status" => "success", "message" => "Booking approved", "booking" => $booking);
            }
            return array("status" => "error", "message" => "access_denied");
        }
        return array("status" => "error", "message" => "not_found");
    }

    public function remindLates() {
        $bookings = Booking::where('notes', 'waiting')->get();
        foreach ($bookings as $booking) {
            $options = $booking->options;
            $options = $options->toArray();
            $payloads = [];
            $pending = false;
            $found = false;
            for ($x = 0; $x < count($options["users"]); $x++) {
                $theUser = $options["users"][$x];
                if (!array_key_exists("connection_id", $theUser)) {
                    $payload = [
                        "booking_id" => $booking->id,
                        "booking" => $booking
                    ];
                    if (array_key_exists("session_id", $options)) {
                        $payload['sessionId'] = $options['session_id'];
                    }
                    if (array_key_exists("token", $theUser)) {
                        $payload['token'] = $theUser['token'];
                    }
                    $followers = [(object) $theUser];
                    $data = [
                        "trigger_id" => $booking->id,
                        "message" => "",
                        "subject" => "",
                        "object" => self::OBJECT_BOOKING,
                        "sign" => true,
                        "payload" => $payload,
                        "type" => self::BOOKING_WAITING,
                        "user_status" => "normal"
                    ];
                    $date = date("Y-m-d H:i:s");
                    $this->notifications->sendMassMessage($data, $followers, null, true, $date, true);
                }
            }
        }
    }

    public function registerConnection(User $user, array $data) {
        $booking = Booking::find($data["booking_id"]);
        if ($booking) {
            $options = $booking->options;
            $options = $options->toArray();
            $pending = false;
            $found = false;
            for ($x = 0; $x < count($options["users"]); $x++) {
                $theUser = $options["users"][$x];
                if ($theUser["id"] == $user->id) {
                    $found = true;
                    $theUser["connection_id"] = $data["connection_id"];
                    $options["users"][$x] = $theUser;
                }
                if (!array_key_exists("connection_id", $theUser)) {
                    $pending = true;
                }
            }
            $update = [];
            $update['total_paid'] = $booking->total_paid;
            $booking->options = $options;
            $booking->save();
            if ($pending) {
                $update["notes"] = "waiting";
            } else {
                $update["notes"] = "started";
                // si quisiera mover el start date seria aca
            }
            Booking::where("id", $booking->id)->update($update);
            $bookable = $booking->bookable;
            return array("status" => "success", "message" => "connection_registered", "booking" => $booking);
        }
        return array("status" => "error", "message" => "not_found");
    }

    public function createChatroom($bookingId) {
        $booking = Booking::find($bookingId);
        if ($booking) {
            $bookable = $booking->bookable;
            $options = $booking->options;
            $options = $options->toArray();
            $users = [];
            $payload = ["booking_id" => $booking->id, "booking" => $booking];
            $user = $bookable->users()->first();
            $bookableUserContainer = ["id" => $user->id];
            $meeting = null;
            if (array_key_exists('virtual_meeting', $options)) {
                if ($options['virtual_meeting']) {
                    if ($options['virtual_provider'] == 'opentok') {
                        $openTok = app("OpenTok");
                        $session = $openTok->createSession();
                        $bookableToken = $session->generateToken();
                        //$bookableToken = $session->generateToken(array('expireTime' => time()+(intval($booking->formula["total_units"]) * 60 * 60)));
                        $bookableUserContainer = ["id" => $user->id, "token" => $bookableToken];
                        $sessionId = $session->getSessionId();
                        $booking->options["session_id"] = $sessionId;
                        $payload["sessionId"] = $sessionId;
                        $payload["token"] = $bookableToken;
                    } else if ($options['virtual_provider'] == 'zoom') {
                        $zoom = app("ZoomMeetings");
                        $meeting = $zoom->createMeeting($user, $booking);
                        if (array_key_exists('id', $meeting)) {
                            echo "Meeting id: " . $meeting['id'] . PHP_EOL;
                            $booking->options["session_id"] = $meeting['id'];
                            $payload["url"] = $meeting['start_url'];
                        } else {
                            return ["status" => "error", "message" => "Meeting not created", "object" => $meeting];
                        }
                    }
                }
            }
            array_push($users, $bookableUserContainer);

            $followers = [$user];
            $data = [
                "trigger_id" => $booking->id,
                "message" => "",
                "subject" => "",
                "object" => self::OBJECT_BOOKING,
                "sign" => true,
                "payload" => $payload,
                "type" => self::BOOKING_STARTING,
                "user_status" => "normal"
            ];
            $date = date("Y-m-d H:i:s");
            $this->notifications->sendMassMessage($data, $followers, null, true, $date, true);
            $client = $booking->customer;
            $bookableClientContainer = ["id" => $client->id];
            $followers = [$client];
            if (array_key_exists('virtual_meeting', $options)) {
                if ($options['virtual_meeting']) {
                    if ($options['virtual_provider'] == 'opentok') {
                        $clientToken = $session->generateToken();
//                      $clientToken = $session->generateToken(array('expireTime' => time()+(intval($booking->formula["total_units"]) * 60 * 60)));
                        $bookableClientContainer = ["id" => $client->id, "token" => $clientToken];
                        $payload["sessionId"] = $sessionId;
                        $payload["token"] = $clientToken;
                    }
                    if ($options['virtual_provider'] == 'zoom') {
                        unset($payload['url']);
                        $bookableClientContainer = ["id" => $client->id];
                        $payload['url'] = $meeting['join_url'];
                    }
                }
            }
            $data['payload'] = $payload;

            array_push($users, $bookableClientContainer);
            $booking->options["users"] = $users;
            //dd($booking);
            $booking->save();
            $bookable->status = "busy";
            $bookable->save();
            $this->notifications->sendMassMessage($data, $followers, null, true, $date, true);
            Booking::where("id", $booking->id)->update(['notes' => 'ready', 'total_paid' => $booking->price]);
            return $booking;
        }
    }

    public function terminateOpenChatRooms() {
        $query = $this->buildQuery("paid", null, "ready", 'ends_at', "<", 2, 'minute');
        $bookings = $query->get();
        foreach ($bookings as $booking) {
            $this->endChatroom($booking);
        }
        $query = $this->buildQuery("paid", null, "waiting", 'ends_at', "<", 2, 'minute');
        $bookings = $query->get();
        foreach ($bookings as $booking) {
            $this->endChatroom($booking);
        }
        $query = $this->buildQuery("paid", null, "started", 'ends_at', "<", 2, 'minute');
        $bookings = $query->get();
        foreach ($bookings as $booking) {
            $this->endChatroom($booking);
        }
    }

    public function leaveChatroom(User $user, array $data) {
        $booking = Booking::find($data['booking_id']);
        $activecount = 0;
        if ($booking) {
            $users = $booking->options["users"];
            foreach ($users as &$userM) {
                if ($userM['id'] == $user->id) {
                    if ($booking->options["location"] == 'opentok') {
                        $userM["connection_id"] = null;
                    }
                }
                if ($userM["connection_id"]) {
                    $activecount++;
                }
            }
            $booking->options["users"] = $users;
            $booking->save();
            if ($booking->notes == 'started' && $activecount == 0) {
                Booking::where("id", $booking->id)->update(['notes' => 'completed', 'total_paid' => $booking->total_paid]);
            }
            $bookable = $booking->bookable;
            if ($bookable->checkAdminAccess($user->id)) {
                $bookable->status = "online";
                $bookable->save();
            }
            return ["status" => "success", "message" => "left_meeting"];
        }
        return ["status" => "error", "message" => "not_found"];
    }

    public function endChatroom(Booking $booking) {
        $options = $booking->options;
        $options = $options->toArray();
        $followers = [];
        $meetingProvider = null;
        if (array_key_exists('virtual_meeting', $options)) {
            if ($options['virtual_meeting']) {
                if (array_key_exists('virtual_provider', $options)) {
                    if ($options['virtual_provider'] == 'opentok') {
                        $meetingProvider = app("OpenTok");
                    }
                    if ($options['virtual_provider'] == 'zoom') {
                        $meetingProvider = app("ZoomMeetings");
                    }
                }
            }
        }
        //$options = $booking->options;
        if (array_key_exists("users", $options)) {
            foreach ($options["users"] as $user) {
                if ($meetingProvider) {
                    if ($options["virtual_provider"] == 'opentok') {
                        if (array_key_exists("connection_id", $user)) {
                            $meetingProvider->forceDisconnect($options["session_id"], $user["connection_id"]);
                        }
                    }
                }
                array_push($followers, (object) $user);
            }
        }

        if ($meetingProvider) {
            if ($options["virtual_provider"] == 'zoom') {
                if (array_key_exists("session_id", $options)) {
                    $meetingProvider->endMeeting($options["session_id"]);
                }
            }
        }
        $payload = [
            "booking_id" => $booking->id,
        ];
        $data = [
            "trigger_id" => $booking->id,
            "message" => "",
            "subject" => "",
            "object" => self::OBJECT_BOOKING,
            "sign" => true,
            "payload" => $payload,
            "type" => self::BOOKING_COMPLETED,
            "user_status" => "normal"
        ];
        $date = date("Y-m-d H:i:s");
        $this->notifications->sendMassMessage($data, $followers, null, true, $date, true);
        Booking::where("id", $booking->id)->update(['notes' => 'completed', 'total_paid' => $booking->total_paid]);
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function addAvailabilityObject(array $data, User $user) {
        $validator = $this->validatorCreateAvailability($data);
        if ($validator->fails()) {
            return array("status" => "error", "message" => $validator->getMessageBag());
        }
        $type = $data['type'];
        $class = self::MODEL_PATH . $type;
        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                if ($object->checkAdminAccess($user->id)) {
                    $serviceBooking = new Availability();
                    $dateFrom = date_create($data['from']);
                    $dateTo = date_create($data['to']);
                    $serviceBooking->make(['range' => $data['range'], 'from' => date_format($dateFrom, "h:i a"), 'to' => date_format($dateTo, "h:i a"), 'is_bookable' => true])
                            ->bookable()->associate($object)
                            ->save();
                    return array("status" => "success", "message" => "Availability created", "availability" => $serviceBooking);
                }
                return array("status" => "error", "message" => "access_denied");
            }
        }
        return array("status" => "error", "message" => "not_found");
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAvailabilityObject(array $data, User $user) {
        $validator = $this->validatorCreateAvailability($data);
        if ($validator->fails()) {
            return array("status" => "error", "message" => $validator->getMessageBag());
        }
        $type = $data['type'];
        $class = self::MODEL_PATH . $type;
        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                if ($object->checkAdminAccess($user->id)) {
                    $availability = Availability::find($data['id']);
                    if ($availability) {
                        if ($availability->bookable_id == $object->id) {
                            $availability->delete();
                            return array("status" => "success", "message" => "Availability created");
                        }
                    }
                }
                return array("status" => "error", "message" => "access_denied");
            }
        }
        return array("status" => "error", "message" => "not_found");
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getObjectsWithBookingUser(User $user) {
        $query = " select id,name from merchants where id in (select merchant_id from merchant_user where user_id = $user->id) and id in ( select distinct( bookable_id) from bookable_bookings )";
        $objects = DB::select($query);
        return array(
            "status" => "success",
            "message" => "",
            "data" => $objects);
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBooking(User $user, $booking) {
        $booking = Booking::where('id', $booking)->first();
        if ($booking) {
            $client = $booking->customer;
            $bookable = $booking->bookable;
            $send = false;
            if ($user->id == $client->id) {
                $send = true;
            }
            if (!$send) {
                if ($bookable->checkAdminAccess($user->id)) {
                    $send = true;
                }
            }
            if ($send) {
                return array(
                    "status" => "success",
                    "message" => "",
                    "booking" => $booking);
            }
            return array(
                "status" => "error",
                "message" => "access_denied");
        }
        return array(
            "status" => "error",
            "message" => "not_found");
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBookingsObject(array $data, $user) {
        $validator = $this->validatorGetBookings($data);
        if ($validator->fails()) {
            return array("status" => "error", "message" => $validator->getMessageBag());
        }
        $type = $data['type'];
        $query = $data['query'];
        $class = "App\\Models\\" . $type;
        if ($query == "customer_unpaid") {
            return array(
                "status" => "success",
                "message" => "",
                "data" => $user->futureBookings()->where('total_paid', 0)->with("bookable")->orderBy('starts_at')->get());
        } else if ($query == "customer_unapproved") {
            return array(
                "status" => "success",
                "message" => "",
                "data" => $user->bookings()->where('total_paid', -1)->with("bookable")->orderBy('starts_at')->get());
        } else if ($query == "customer_past") {
            return array(
                "status" => "success",
                "message" => "",
                "data" => $user->pastBookings()->whereColumn('price', 'total_paid')->orderBy('starts_at')->with("bookable")->get());
        } else if ($query == "customer_upcoming") {
            return array(
                "status" => "success",
                "message" => "",
                "data" => $user->futureBookings()->whereColumn('price', 'total_paid')->orderBy('starts_at')->with("bookable")->get());
        } else if ($query == "customer_all") {
            return array(
                "status" => "success",
                "message" => "",
                "data" => $user->futureBookings()->with("bookable")->orderBy('starts_at')->get());
        }
        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                if ($query == "day") {
                    $results = $object->bookingsStartsBetween($data['from'] . " 00:00:00", $data['from'] . " 23:59:59")->orderBy('starts_at')->whereColumn('price', 'total_paid')->get();
                    foreach ($results as $value) {
                        unset($value->price);
                        unset($value->total_paid);
                        unset($value->notes);
                        unset($value->formula);
                        unset($value->options);
                    }
                    return array(
                        "status" => "success",
                        "message" => "",
                        "data" => $results);
                } else {
                    if ($object->checkAdminAccess($user->id)) {
                        if ($query == "bookable_upcoming") {
                            return array(
                                "status" => "success",
                                "message" => "",
                                "data" => $object->futureBookings()->whereColumn('price', 'total_paid')->orderBy('starts_at')->with("customer")->get());
                        } else if ($query == "bookable_past") {
                            return array(
                                "status" => "success",
                                "message" => "",
                                "data" => $object->pastBookings()->whereColumn('price', 'total_paid')->orderBy('starts_at')->with("customer")->get());
                        } else if ($query == "bookable_unapproved") {
                            return array(
                                "status" => "success",
                                "message" => "",
                                "data" => $object->futureBookings()->where('total_paid', -1)->orderBy('starts_at')->with("customer")->get());
                        } else if ($query == "bookable_all") {
                            return array(
                                "status" => "success",
                                "message" => "",
                                "data" => $object->futureBookings()->where(function($query) {
                                            $query->whereColumn('price', 'total_paid')
                                                    ->orWhere('total_paid', -1);
                                        })->orderBy('starts_at')->with("customer")->get());
                        }
                    }
                }
                return array("status" => "error", "message" => "access_denied");
            }
        }
        return array("status" => "error", "message" => "not_found");
    }

    public function buildQuery($paidStatus, $authStatus, $bookingStatus, $date, $op, $interval, $units) {
        $query = null;
//        DB::enableQueryLog();
        if ($paidStatus == "paid") {
            $query = Booking::whereColumn('price', 'total_paid');
        } else {
            if ($authStatus == "approved") {
                $query = Booking::where('total_paid', 0);
            } elseif ($authStatus == "unapproved") {
                $query = Booking::where('total_paid', -1);
            } else {
                $query = Booking::where(function($query2) {
                            $query2->where('total_paid', 0)
                                    ->orWhere('total_paid', -1);
                        });
            }
        }
        if ($query) {
            if ($bookingStatus) {
                $query->where('notes', $bookingStatus);
            }
            if ($interval) {
                $now = Carbon::now();
                $now->add($units, $interval);
                $query->where($date, $op, $now);
            }
//            $query->orderBy("id","desc")->limit(3);
//            $results = $query->get()->toArray();
//            dd($results);
//            dd(DB::getQueryLog());
            return $query;
        }
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAvailabilitiesObject(array $data) {
        $validator = $this->validatorGetAvailabilities($data);
        if ($validator->fails()) {
            return array("status" => "error", "message" => $validator->getMessageBag());
        }
        $type = $data['type'];
        $class = "App\\Models\\" . $type;
        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                return array("status" => "success", "message" => "", "data" => $object->availabilities);
            }
        }
        return array("status" => "error", "message" => "not_found");
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteBookingObject(array $data, User $user) {
        Favorite::where('user_id', $user->id)
                ->where('favorite_type', $data['type'])
                ->where('object_id', $data['object_id'])->delete();
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteOldBookings() {
        $query = $this->buildQuery("unpaid", "both", null, 'starts_at', "<", -10, 'minute');
        $query->delete();
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendStartReminder() {
        $query = $this->buildQuery("paid", null, "pending", 'starts_at', "<", 2, 'hour');
//        dd($query->with("bookable")->get()->toArray());
        $bookings = $query->with("bookable")->get();
        foreach ($bookings as $booking) {
            $payload = [
                "booking_id" => $booking->id,
                "booking_date" => $booking->starts_at
            ];
            $data = [
                "trigger_id" => $booking->id,
                "message" => "",
                "subject" => "",
                "object" => "Booking",
                "sign" => true,
                "payload" => $payload,
                "type" => "booking_soon",
                "user_status" => "active"
            ];
            $object = $booking->bookable;
            $admins = $object->users()->first();
            $followers = [];
            array_push($followers, $admins);
            $user = json_decode(json_encode(["id" => $booking->customer_id]), false);
            array_push($followers, $user);
            $date = date("Y-m-d H:i:s");
            $this->notifications->sendMassMessage($data, $followers, null, true, $date, true);
            Booking::where("id", $booking->id)->update(['notes' => 'reminded', 'total_paid' => $booking->price]);
        }
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function startMeeting() {
        $query = $this->buildQuery("paid", null, "reminded", 'starts_at', "<", 5, 'minute');
        $bookings = $query->get();
        foreach ($bookings as $booking) {
            $this->createChatroom($booking->id);
        }
    }

    public function deleteBooking(User $user, $id) {
        $booking = Booking::find($id);
        if ($booking) {
            if ($booking->customer_id == $user->id) {
                $booking->delete();
                return array("status" => "success", "message" => "Booking Deleted");
            }
            return array("status" => "error", "message" => "access_denied");
        }
        return array("status" => "error", "message" => "not_found");
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorGetBookings(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'query' => 'required|max:255',
                    'object_id' => 'required|max:255',
                    'from' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorGetAvailabilities(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'object_id' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorStatusBookings(array $data) {
        return Validator::make($data, [
                    'booking_id' => 'required|max:255',
                    'status' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorScheduleBooking(array $data) {
        return Validator::make($data, [
                    'object_id' => 'required|max:255',
                    'status' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorCreateBooking(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'object_id' => 'required|integer|min:1',
                    'from' => 'required|max:255',
                    'to' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorCreateAvailability(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'object_id' => 'required|integer|min:1',
        ]);
    }

}
