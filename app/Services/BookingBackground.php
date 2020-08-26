<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\Booking;
use App\Models\Favorite;
use App\Models\User;
use Validator;
use DB;
use Carbon\Carbon;

class BookingBackground {

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
            $booking->notes = 'ready';
            $bookable->status = "busy";
            $bookable->save();
            $this->notifications->sendMassMessage($data, $followers, null, true, $date, true);
            Booking::where("id", $booking->id)->update(['notes' => 'ready', 'total_paid' => $booking->price]);
            //$booking->touch();
            return $booking;
        }
    }

    public function terminateOpenChatRooms() {
        $query = $this->buildQuery("paid", null, "ready", 'ends_at', "<", 2, 'minute');
        $bookings = $query->with("bookable")->get();
        foreach ($bookings as $booking) {
            $this->endChatroom($booking);
        }
        $query = $this->buildQuery("paid", null, "waiting", 'ends_at', "<", 2, 'minute');
        $bookings = $query->with("bookable")->get();
        foreach ($bookings as $booking) {
            $this->endChatroom($booking);
        }
        $query = $this->buildQuery("paid", null, "started", 'ends_at', "<", 2, 'minute');
        $bookings = $query->with("bookable")->get();
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
                //$booking->touch();
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
        if (array_key_exists('call', $options)) {
            if ($options['call']) {
                $object = $booking->bookable;
                $object->status = "online";
                $object->save();
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
        //$booking->touch();
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
    public function deleteOldBookings() {
        $query = $this->buildQuery("unpaid", "both", null, 'starts_at', "<", -10, 'minute');
        $query->delete();
    }

    public function remindLates() {
        $query = $this->buildQuery("paid", null, "waiting", 'starts_at', "<", -5, 'minute');
        $bookings = $query->with("bookable")->get();
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

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendStartReminder() {
        $query = $this->buildQuery("paid", null, "pending", 'starts_at', "<", 45, 'minute');
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
            //$booking->touch();
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

}
