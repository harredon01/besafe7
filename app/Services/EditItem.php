<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\Item;
use App\Models\Favorite;
use App\Models\User;

use Validator;
use DB;

class EditItem {

    const MODEL_PATH = 'App\\Models\\';
    const BOOKING_APPROVED = 'booking_approved';
    const ITEM_FULFILLMENT_STATUS_UPDATE = 'item_fullfillment_status_update';
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
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct() {
        $this->notifications = app('Notifications');
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
    public function changeStatusItem(User $user, array $data) {
        $validator = $this->validatorStatusBookings($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $status = $data["status"];
        $item = Item::find($data['item_id']);
        if ($item) {
            $object = $item->merchant;
            $client = $item->user;
            if ($object) {
                if ($user->id == $object->user_id) {
                    $item->fulfillment = $data['status'];
                    $item->save();
                    $payload = [
                        "item_id" => $item->id,
                        "item_name" => $item->name,
                        "item_total" => $item->priceSumConditions,
                        "first_name" => $user->firstName,
                        "last_name" => $user->lastName,
                        "status" => $status
                    ];
                    $data = [
                        "trigger_id" => $user->id,
                        "message" => "",
                        "subject" => "",
                        "object" => "Merchant",
                        "sign" => true,
                        "payload" => $payload,
                        "type" => self::ITEM_FULFILLMENT_STATUS_UPDATE,
                        "user_status" => $user->getUserNotifStatus()
                    ];

                    $followers = [$client];
                    $date = date("Y-m-d H:i:s");
                    $this->notifications->sendMassMessage($data, $followers, $user, true, $date, true);
                    return response()->json(array("status" => "success", "message" => "Booking approved", "booking" => $booking));
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
