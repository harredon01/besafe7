<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\Booking;
use App\Models\Favorite;
use App\Models\User;
use Validator;
use DB;

class EditBooking {

    const MODEL_PATH = 'App\\Models\\';
    const BOOKING_APPROVED = 'booking_approved';
    const OBJECT_BOOKING = 'Booking';
    const BOOKING_CREATED_BOOKABLE_PENDING = 'booking_created_bookable_pending';
    const BOOKING_BOOKABLE_APPROVED = 'booking_bookable_approved';
    const BOOKING_BOOKABLE_DENIED = 'booking_bookable_denied';
    const BOOKING_DENIED = 'booking_denied';
    const BOOKING_CANCELLED = 'booking_cancelled';
    const BOOKING_REMINDER = 'booking_reminder';
    const BOOKING_STARTING = 'booking_starting';
    const BOOKING_WAITING = 'booking_waiting';
    const BOOKING_COMPLETED = 'booking_completed';

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

    private function checkAvailable(array $data, $object) {
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
                    return true;
                }
            }

            return false;
        }
        return true;
    }
    
    public function checkBookingAvailability($data, $object){
        if($this->checkAvailable($data, $object)){
            return $this->checkBooking($data, $object);
        }
        return false;
    }

    public function checkBooking($data, $object) {
        $attributes = $object->attributes;
        $maxPerHour = 1;
        if (array_key_exists("max_per_hour", $attributes)) {
            $maxPerHour = $attributes['max_per_hour'];
        }
        $date = date_create($data['from']);
        date_sub($date, date_interval_create_from_date_string("1 seconds"));
        $date2 = date_create($data['to']);
        date_add($date2, date_interval_create_from_date_string("1 seconds"));
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
            return false;
        } else {
            return true;
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
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $type = $data['type'];
        $class = self::MODEL_PATH . $type;

        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                $result = $this->checkBookingAvailability($data, $object);
                if ($result || ($object->status == 'online'&&$data['call'])) {
                    $booking = $object->newBooking($user, $data['from'], $data['to']);
                    $attributes = $object->attributes;
                    $requiresAuth = false;
                    $update = [];
                    $update["notes"] = "pending";
                    $update["options"] = [];
                    if (array_key_exists("attributes", $data)) {
                        $update["options"] = $data["attributes"];
                    }
                    if (array_key_exists("location", $data)) {
                        $update["options"]["location"] = $data['location'];
                    }
                    $update["options"]["status"] = "approved";
                    $update["options"]["call"] = $data['call'];
                    if (array_key_exists("booking_requires_authorization", $attributes) &&! ($object->status == 'online'&&$data['call'])) {
                        if ($attributes["booking_requires_authorization"]) {
                            $update["total_paid"] = -1;
                            $booking->total_paid = -1;
                            $this->handleAuthorizationRequest($user, $booking, $object);
                            $requiresAuth = true;
                            $update["options"]["status"] = "pending";
                        }
                    }
                    $update["options"] = json_encode($update["options"]);
                    Booking::where("id", $booking->id)->update($update);
                    return array("status" => "success", "message" => "Booking created", "booking" => $booking, "requires_auth" => $requiresAuth);
                }
                return response()->json(array("status" => "error", "message" => "Not available"));
            }
        }
        return response()->json(array("status" => "error", "message" => "Object not found"));
    }

    private function handleAuthorizationRequest($user, $booking, $object) {
        $owner = $object->users()->first();
        $followers = [$owner];
        $payload = [
            "booking_id" => $booking->id,
            "first_name" => $user->firstName,
            "last_name" => $user->lastName,
            "booking_date" => $booking->starts_at,
            "status" => "pending"
        ];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => "Merchant",
            "sign" => true,
            "payload" => $payload,
            "type" => self::BOOKING_CREATED_BOOKABLE_PENDING,
            "user_status" => $user->getUserNotifStatus()
        ];
        $date = date("Y-m-d H:i:s");
        $this->notifications->sendMassMessage($data, $followers, $user, true, $date, true);
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function changeStatusBookingObject(array $data, User $user) {
        $validator = $this->validatorStatusBookings($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $status = $data["status"];
        $booking = Booking::find($data['booking_id']);
        $object = $booking->bookable;
        if ($object) {
            if ($object->checkAdminAccess($user)) {
                $typeAlert = "";
                $customer = $booking->customer;
                $options = $booking->options->toArray();
                $updateData = [
                    "options" => $options,
                    "updated_at" => date_create()
                ];
                $followers = [$customer];
                $updateData["options"]["status"] = $status;
                if ($status == "approved") {
                    $updateData["total_paid"] = 0;
                    $typeAlert = self::BOOKING_BOOKABLE_APPROVED;
                } elseif ($status == "denied") {
                    $updateData["total_paid"] = -1;
                    $updateData["options"]["reason"] = $data["reason"];
                    $typeAlert = self::BOOKING_BOOKABLE_DENIED;
                } 
                $options = $updateData["options"];
                $updateData["options"] = json_encode($updateData["options"]);
                Booking::where("id", $booking->id)->update($updateData);
                $payload = [
                    "booking_id" => $booking->id,
                    "first_name" => $user->firstName,
                    "last_name" => $user->lastName,
                    "booking_date" => $booking->starts_at,
                    "options" => $options
                ];
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
                return response()->json(array("status" => "success", "message" => "Booking approved", "booking" => $booking));
            }
            return response()->json(array("status" => "error", "message" => "Access denied"), 400);
        }
        return response()->json(array("status" => "error", "message" => "Object not found"));
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
            return array("status" => "success", "message" => "Connection registered", "booking" => $booking);
        }
        return array("status" => "error", "message" => "Object not found");
    }

    public function createChatroom($bookingId) {
        $booking = Booking::find($bookingId);
        if ($booking) {
            $bookable = $booking->bookable;
            $options = $booking->options;
            $options = $options->toArray();
            $users = [];
            $payload = ["booking_id" => $booking->id, "location" => $options['location'], "booking" => $booking];
            $user = $bookable->users()->first();
            $bookableUserContainer = ["id" => $user->id];

            if ($options['location'] == 'opentok') {
                $openTok = app("OpenTok");
                $session = $openTok->createSession();
                $bookableToken = $session->generateToken();
                //$bookableToken = $session->generateToken(array('expireTime' => time()+(intval($booking->formula["total_units"]) * 60 * 60)));
                $bookableUserContainer = ["id" => $user->id, "token" => $bookableToken];
                $sessionId = $session->getSessionId();
                $booking->options["session_id"] = $sessionId;
                $payload["sessionId"] = $sessionId;
                $payload["token"] = $bookableToken;
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
            if ($options['location'] == 'opentok') {
                $clientToken = $session->generateToken();
//                $clientToken = $session->generateToken(array('expireTime' => time()+(intval($booking->formula["total_units"]) * 60 * 60)));
                $bookableClientContainer = ["id" => $client->id, "token" => $clientToken];
                $payload["sessionId"] = $sessionId;
                $payload["token"] = $clientToken;
                $data['payload'] = $payload;
            }

            array_push($users, $bookableClientContainer);
            $booking->options["users"] = $users;
            //dd($booking);
            $booking->save();
            $bookable->status = "busy";
            $bookable->save();
            $this->notifications->sendMassMessage($data, $followers, null, true, $date, true);
            Booking::where("id", $booking->id)->update(['notes' => 'ready','total_paid' =>$booking->total_paid]);
        }
    }

    public function terminateOpenChatRooms() {
        $bookings = Booking::where("notes", "started")->where("ends_at", ">", date("Y-m-d H:i:s"));
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
                        $userM["connection_id"]=null;
                    }
                }
                if($userM["connection_id"]){
                    $activecount++;
                }
            }
            $booking->options["users"] = $users;
            $booking->save();
            if($booking->notes == 'started' && $activecount == 0){
                Booking::where("id", $booking->id)->update(['notes' => 'completed','total_paid' =>$booking->total_paid]);
            }
            $bookable = $booking->bookable;
            if ($bookable->checkAdminAccess($user->id)) {
                $bookable->status = "online";
                $bookable->save();
            }
            return ["status"=>"success","message"=>"left meeting"];
        }
        return ["status"=>"error","message"=>"booking not found"];
    }

    public function endChatroom(Booking $booking) {
        $openTok = app("OpenTok");
        $followers = [];
        //$options = $booking->options;
        foreach ($booking->options["users"] as $user) {
            if ($booking->options["location"] == 'opentok') {
                if (array_key_exists("connection_id", $user)) {
                    $openTok->forceDisconnect($booking->options["session_id"], $user["connection_id"]);
                }
            }
            array_push($followers, (object) $user);
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
                if ($user->id == $object->user_id) {
                    $serviceBooking = new Availability();
                    $dateFrom = date_create($data['from']);
                    $dateTo = date_create($data['to']);
                    $serviceBooking->make(['range' => $data['range'], 'from' => date_format($dateFrom, "h:i a"), 'to' => date_format($dateTo, "h:i a"), 'is_bookable' => true])
                            ->bookable()->associate($object)
                            ->save();
                    return array("status" => "success", "message" => "Availability created", "availability" => $serviceBooking);
                }
                return array("status" => "error", "message" => "Access denied");
            }
        }
        return array("status" => "error", "message" => "Object not found");
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
                if ($user->id == $object->user_id) {
                    $availability = Availability::find($data['id']);
                    if ($availability) {
                        if ($availability->bookable_id == $object->id) {
                            $availability->delete();
                            return array("status" => "success", "message" => "Availability created");
                        }
                    }
                }
                return array("status" => "error", "message" => "Access denied");
            }
        }
        return array("status" => "error", "message" => "Object not found");
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getObjectsWithBookingUser(User $user) {
        $query = " select id,name from merchants where user_id = $user->id and id in ( select distinct( bookable_id) from bookable_bookings )";
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
    public function getBooking(User $user,$booking) {
        $booking = Booking::find($booking);
        $client = $booking->customer;
        $bookable = $booking->bookable;
        $send = false;
        if($user->id == $client->id){
            $send = true;
        }
        if($bookable->checkAdminAccess($user->id)){
            $send = true;
        }
        if($send){
            return array(
                    "status" => "success",
                    "message" => "",
                    "booking" => $booking);
        }
        return array(
                    "status" => "error",
                    "message" => "no access");
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBookingsObject(array $data, User $user) {
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
                    return array(
                                "status" => "success",
                                "message" => "",
                                "data" => $object->bookingsStartsBetween($data['from'] . " 00:00:00", $data['from'] . " 23:59:59")->orderBy('starts_at')->whereColumn('price', 'total_paid')->get());
                } else {
                    if ($user->id == $object->user_id) {
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
                                        "data" => $object->futureBookings()->whereColumn('price', 'total_paid')->orderBy('starts_at')->with("customer")->get());
                        }
                    }
                }
                return array("status" => "error", "message" => "Access denied");
            }
        }
        return array("status" => "error", "message" => "Object not found");
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
        return array("status" => "error", "message" => "Object not found");
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
    public function sendStartReminder() {
        $bookings = DB::select(""
                        . "SELECT 
                                b.*,m.name as merchant_name,m.id as merchant_id
                            FROM
                                bookable_bookings b join merchants m on m.id=b.bookable_id
                            WHERE
                                starts_at < DATE_ADD(NOW(), INTERVAL 2 HOUR)
                                    AND notes = 'pending' AND b.price = b.total_paid");
        foreach ($bookings as $booking) {
            $payload = [
                "booking_id" => $booking->id,
                "booking_date" => $booking->starts_at
            ];
            $data = [
                "trigger_id" => $booking->merchant_id,
                "message" => "",
                "subject" => "",
                "object" => "Merchant",
                "sign" => true,
                "payload" => $payload,
                "type" => "booking_soon",
                "user_status" => "active"
            ];
            $user = json_decode(json_encode(["id" => $booking->customer_id]), false);
            $followers = [$user];
            $date = date("Y-m-d H:i:s");
            $this->notifications->sendMassMessage($data, $followers, null, true, $date, true);
        }
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function startMeeting() {
        $bookings = DB::select("SELECT 
                                    id
                                FROM
                                    bookable_bookings b
                                WHERE
                                    starts_at < DATE_ADD(NOW(), INTERVAL 2 MINUTE)
                                        AND notes = 'pending' and b.price=b.total_paid
                                ORDER BY b.bookable_id");
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
            return array("status" => "error", "message" => "Access denied");
        }
        return array("status" => "error", "message" => "Booking not found");
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
