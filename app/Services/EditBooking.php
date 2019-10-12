<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\Booking;
use App\Models\Favorite;
use App\Models\User;
use App\Services\OpenTokService;
use Validator;
use DB;

class EditBooking {

    const MODEL_PATH = 'App\\Models\\';
    const BOOKING_APPROVED = 'booking_approved';
    const BOOKING_CREATED_CLIENT_PENDING = 'booking_created_client_pending';
    const BOOKING_CREATED_CLIENT_APPROVED = 'booking_created_client_approved';
    const BOOKING_CREATED_BOOKABLE_PENDING = 'booking_created_bookable_pending';
    const BOOKING_CREATED_BOOKABLE_APPROVED = 'booking_created_bookable_approved';
    const BOOKING_DENIED = 'booking_denied';
    const BOOKING_CANCELLED = 'booking_cancelled';
    const BOOKING_REMINDER = 'booking_reminder';
    const BOOKING_STARTING = 'booking_starting';
    const BOOKING_COMPLETED = 'booking_completed';

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $notifications;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $openTok;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(OpenTokService $opentok) {
        $this->notifications = app('Notifications');
        $this->openTok = $opentok;
    }

    private function checkAvailable(array $data, $object) {
        $from = $data['from'];
        $to = $data['to'];
        $dayofweek = date('w', strtotime($from));
        $dayName = "";
        if ($dayofweek == 1) {
            $dayName = "monday";
        } else if ($dayofweek == 2) {
            $dayName = "tuesday";
        } else if ($dayofweek == 3) {
            $dayName = "wednesday";
        } else if ($dayofweek == 4) {
            $dayName = "thursday";
        } else if ($dayofweek == 5) {
            $dayName = "friday";
        } else if ($dayofweek == 6) {
            $dayName = "saturday";
        } else if ($dayofweek == 0) {
            $dayName = "sunday";
        }

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
//                $data2 = [
//                    "sfr" => $dateFrom,
//                    "sto" => $dateTo,
//                    "dfr" => $fromString,
//                    "dto" => $toString,
//                    "fr" => $dateTimestampFrom,
//                    "to" => $dateTimestampTo,
//                    "up" => $dateTimestampUpper,
//                    "lo" => $dateTimestampLower,
//                    "a1" => $dateTimestampFrom >= $dateTimestampUpper,
//                    "a2" => $dateTimestampFrom <= $dateTimestampLower,
//                    "b1" => $dateTimestampTo >= $dateTimestampUpper,
//                    "b2" => $dateTimestampTo <= $dateTimestampLower,
//                ];
//                dd($data2);
                if ($dateTimestampFrom >= $dateTimestampUpper && $dateTimestampFrom <= $dateTimestampLower && $dateTimestampTo >= $dateTimestampUpper && $dateTimestampTo <= $dateTimestampLower) {
                    return $this->checkBooking($data,$object);
                }
            }

            return false;
        }
        return $this->checkBooking($data,$object);
    }

    public function checkBooking($data,$object) {
        $attributes = $object->attributes;
        $maxPerHour = 1;
        if(array_key_exists("max_per_hour", $attributes)){
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
                if($item['id']==$value['id']){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                array_push($array1, $value);
            }
        }
        $totalHoping = count($array1)+1;
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
                $result = $this->checkAvailable($data, $object);
                if ($result) {
                    $booking = $object->newBooking($user, $data['from'], $data['to']);
                    $attributes = $object->attributes;
                    $requiresAuth = false;
                    $requiresUpdate = false;
                    $update = [];
                    if (array_key_exists("attributes", $data)) {
                        $requiresUpdate = true;
                        $update["options"] = $data["attributes"];
                    }
                    $update["options"]["status"] = "approved";
                    if (array_key_exists("booking_requires_authorization", $attributes)) {
                        if ($attributes["booking_requires_authorization"]) {
                            $requiresUpdate = true;
                            $update["total_paid"] = -1;
                            $booking->total_paid = -1;
                            $requiresAuth = true;
                            $update["options"]["status"] = "pending";
                        }
                    }
                    if ($requiresUpdate) {
                        $update["options"] = json_encode($update["options"]);
                        Booking::where("id", $booking->id)->update($update);
                    }
                    return response()->json(array("status" => "success", "message" => "Booking created", "booking" => $booking, "requires_auth" => $requiresAuth));
                }
                return response()->json(array("status" => "error", "message" => "Not available"));
            }
        }
        return response()->json(array("status" => "error", "message" => "Object not found"));
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
            if ($user->id == $object->user_id) {
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
                    $typeAlert = self::BOOKING_APPROVED;
                } elseif ($status == "denied") {
                    $updateData["options"]["reason"] = $data["reason"];
                    $typeAlert = self::BOOKING_DENIED;
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
                //$this->notifications->sendMassMessage($data, $followers, $user, true, $date, true);
                return response()->json(array("status" => "success", "message" => "Booking approved", "booking" => $booking));
            }
            return response()->json(array("status" => "error", "message" => "Access denied"), 400);
        }
        return response()->json(array("status" => "error", "message" => "Object not found"));
    }

    public function registerConnection(User $user, array $data) {
        $booking = Booking::find($data["booking_id"]);
        if ($booking) {
            $options = $booking->options;
            $options = $options->toArray();
            $found = false;
            for ($x = 0; $x < count($options["users"]); $x++) {
                $theUser = $options["users"][$x];
                if ($theUser["id"] == $user->id) {
                    $found = true;
                    $theUser["connection_id"] = $data["connection_id"];
                    $options["users"][$x] = $theUser;
                }
            }
            if (!$found) {
                $container = [
                    "id" => $user->id,
                    "connection_id" => $data["connection_id"]
                ];
                array_push($options["users"], $container);
            }
            $booking->options = $options;
            $booking->save();
            return response()->json(array("status" => "success", "message" => "Connection registered", "booking" => $booking));
        }
        return response()->json(array("status" => "error", "message" => "Object not found"));
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function addAvailabilityObject(array $data, User $user) {
        $validator = $this->validatorCreateAvailability($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $type = $data['type'];
        $class = self::MODEL_PATH . $type;
        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                if ($user->id == $object->user_id) {
                    $serviceBooking = new Availability();
                    $serviceBooking->make(['range' => $data['range'], 'from' => $data['from'], 'to' => $data['to'], 'is_bookable' => true])
                            ->bookable()->associate($object)
                            ->save();
                    return response()->json(array("status" => "success", "message" => "Availability created"));
                }
                return response()->json(array("status" => "error", "message" => "Access denied"), 400);
            }
        }
        return response()->json(array("status" => "error", "message" => "Object not found"));
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getObjectsWithBookingUser(User $user) {
        $query = " select id,name from merchants where user_id = $user->id and id in ( select distinct( bookable_id) from bookable_bookings )";
        $objects = DB::select($query);
        return response()->json(array(
                    "status" => "success",
                    "message" => "",
                    "data" => $objects)
        );
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBookingsObject(array $data, User $user) {
        $validator = $this->validatorGetBookings($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $type = $data['type'];
        $query = $data['query'];
        $class = "App\\Models\\" . $type;
        if ($query == "customer_unpaid") {
            return response()->json(array(
                        "status" => "success",
                        "message" => "",
                        "data" => $user->futureBookings()->where('total_paid', 0)->with("bookable")->get())
            );
        } else if ($query == "customer_unapproved") {
            return response()->json(array(
                        "status" => "success",
                        "message" => "",
                        "data" => $user->bookings()->where('total_paid', -1)->with("bookable")->get())
            );
        } else if ($query == "customer_past") {
            return response()->json(array(
                        "status" => "success",
                        "message" => "",
                        "data" => $user->pastBookings()->whereColumn('price', 'total_paid')->with("bookable")->get())
            );
        } else if ($query == "customer_upcoming") {
            return response()->json(array(
                        "status" => "success",
                        "message" => "",
                        "data" => $user->futureBookings()->whereColumn('price', 'total_paid')->with("bookable")->get())
            );
        }
        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                if ($query == "day") {
                    return response()->json(array(
                                "status" => "success",
                                "message" => "",
                                "data" => $object->bookingsStartsBetween($data['from'] . " 00:00:00", $data['from'] . " 23:59:59")->whereColumn('price', 'total_paid')->get())
                    );
                } else if ($query == "bookable_upcoming") {
                    if ($user->id == $object->user_id) {
                        return response()->json(array(
                                    "status" => "success",
                                    "message" => "",
                                    "data" => $object->futureBookings()->whereColumn('price', 'total_paid')->with("customer")->get())
                        );
                    }
                } else if ($query == "bookable_past") {
                    if ($user->id == $object->user_id) {
                        return response()->json(array(
                                    "status" => "success",
                                    "message" => "",
                                    "data" => $object->pastBookings()->whereColumn('price', 'total_paid')->with("customer")->get())
                        );
                    }
                } else if ($query == "bookable_unapproved") {
                    if ($user->id == $object->user_id) {
                        return response()->json(array(
                                    "status" => "success",
                                    "message" => "",
                                    "data" => $object->futureBookings()->where('total_paid', -1)->with("customer")->get())
                        );
                    }
                }
                return response()->json(array("status" => "error", "message" => "Access denied"), 400);
            }
        }
        return response()->json(array("status" => "error", "message" => "Object not found"));
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAvailabilitiesObject(array $data) {
        $validator = $this->validatorGetBookings($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $type = $data['type'];
        $class = "App\\Models\\" . $type;
        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                return response()->json(array("status" => "success", "message" => "", "data" => $object->availabilities));
            }
        }
        return response()->json(array("status" => "error", "message" => "Object not found"));
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
                                    AND starts_at >= DATE_ADD(NOW(), INTERVAL 1 HOUR) and price = total_paid ");
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
    public function startVirtualMeeting() {
        $bookings = DB::select("SELECT 
                                    *
                                FROM
                                    bookable_bookings b join merchants m on m.id=b.bookable_id
                                WHERE
                                    starts_at < DATE_ADD(NOW(), INTERVAL 5 MINUTE)
                                        AND starts_at >= NOW()  and price = total_paid ");
        foreach ($bookings as $booking) {
            $this->openTok->createChatroom($booking);
        }
    }

    public function deleteBooking(User $user, $id) {
        $booking = Booking::find($id);
        if ($booking) {
            if ($booking->customer_id == $user->id) {
                $booking->delete();
                return response()->json(array("status" => "success", "message" => "Booking Deleted"));
            }
            return response()->json(array("status" => "error", "message" => "Access denied"));
        }
        return response()->json(array("status" => "error", "message" => "Booking not found"));
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
