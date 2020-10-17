<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\Booking;
use App\Models\Favorite;
use App\Jobs\CreateGoogleEvent;
use App\Models\User;
use Validator;
use DB;
use Carbon\Carbon;
use Spatie\GoogleCalendar\Event;

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
    const IN_COMFIRMATION = 'in_confirmation';
    const PENDING = 'pending';
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
        $dayofweek = date('l', (strtotime($from) - date('Z', strtotime($from))));
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
    }

    public function addOrUpdateGoogleEvent($booking_id) {
        $booking = Booking::find($booking_id);
        if ($booking) {
            //create a new event
            $eventExists = false;
            if ($booking->options['google_event_id']) {
                try {
                    $event = Event::find($booking->options['google_event_id']);
                } catch (\Exception $ex) {
                    $event = null;
                }

                if ($event) {
                    $event->startDateTime = $booking->starts_at;
                    $event->endDateTime = $booking->ends_at;
                    $event->save();
                    $eventExists = true;
                }
            }
            if ($booking->options['google_personal_event_id']) {
                try {
                    $event = Event::find($booking->options['google_personal_event_id']);
                } catch (\Exception $ex) {
                    $event = null;
                }

                if ($event) {
                    $event->startDateTime = $booking->starts_at;
                    $event->endDateTime = $booking->ends_at;
                    $event->save();
                    $eventExists = true;
                }
            }
            if (!$eventExists) {
                $client = $booking->customer;
                $bookable = $booking->bookable;
                $attributes = $bookable->attributes;
                if (array_key_exists('google_calendar', $attributes)) {
                    if ($attributes['google_calendar']) {
                        $event2 = $this->createGoogleEvent($booking, $bookable, $client, $attributes['google_calendar']);
                        if ($event2) {
                            $booking->options['google_personal_event_id'] = $event2->id;
                        }
                    }
                }
                $event = $this->createGoogleEvent($booking, $bookable, $client, null);
                $booking->options['google_event_id'] = $event->id;
                $options = $booking->options;
                $booking->touch();
                $updateData = [
                    "options" => json_encode($options),
                    "total_paid" => $booking->total_paid,
                    "updated_at" => date_add(date_create(), date_interval_create_from_date_string(date('Z') . " seconds"))
                ];
                Booking::where("id", $booking->id)->update($updateData);
            }
        }
    }

    private function createGoogleEvent($booking, $bookable, $client, $calendar) {
        $event = null;
        $attendees = [
            ['email' => $client->email, 'displayName' => $client->name],
            ['email' => $bookable->email, 'displayName' => $bookable->name],
//                ['email' => 'harredon01@gmail.com', 'displayName' => "Hoovert"],
//                ['email' => 'camilasaca82@gmail.com', 'displayName' => 'Camila'],
        ];
        if ($calendar) {
            if ($calendar == $bookable->email) {
                return null;
            }
            $name = "PetWorld Reserva con: " . $bookable->name;
            $data = [
                "name" => $name,
                "startDateTime" => $booking->starts_at,
                "endDateTime" => $booking->ends_at,
                "sendUpdates" => "all"
            ];
            if ($booking->options['location']) {
                $data['location'] = $booking->options['location'];
            }

            $data['attendees'] = $attendees;
            $event = Event::create($data, $calendar);
        } else {
            $event = new Event;
            $event->sendUpdates = "all";
            $event->name = "PetWorld Reserva con: " . $bookable->name;
            if ($booking->options['location']) {
                $event->location = $booking->options['location'];
            }
            $event->startDateTime = $booking->starts_at;
            $event->endDateTime = $booking->ends_at;

            foreach ($attendees as $value) {
                $event->addAttendee($value);
            }
            $calendarEvent = $event->save();
            return $calendarEvent;
        }
        return $event;
    }

    public function checkGoogleBookings($data) {
        $date = new Carbon($data['from']);
        $date2 = new Carbon($data['to']);
        $events = Event::get($date, $date2, [], 'camilasaca82@gmail.com');
        foreach ($events as $event) {
            dd($event);
        }
    }

    public function getGoogleBookingsDay($data, $object) {
        $attributes = $object->attributes;
        $events = [];
        if (array_key_exists("google_calendar", $attributes)) {
            $google_calendar = $attributes['google_calendar'];
            if ($google_calendar) {
                $date = new Carbon($data['from'] . " 00:00:00");
                $date2 = new Carbon($data['from'] . " 23:59:59");
                $results = Event::get($date, $date2, [], $google_calendar);
                foreach ($results as $event) {
                    array_push($events, [
                        'id' => -1,
                        'name' => $event->name,
                        "starts_at" => $event->startDateTime,
                        "ends_at" => $event->endDateTime,
                    ]);
                }
            }
        }
        $datea = new Carbon($data['from'] . " 00:00:00");
        $datea2 = new Carbon($data['from'] . " 23:59:59");
        $results2 = Event::get($datea, $datea2)->toArray();
        foreach ($results2 as $event) {
            array_push($events, [
                'id' => -1,
                'name' => $event->name,
                "starts_at" => $event->startDateTime,
                "ends_at" => $event->endDateTime,
            ]);
        }
        return array(
            "status" => "success",
            "message" => "",
            "data" => $events);
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
                    "attributes" => $attributes->toArray(),
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
        if (array_key_exists("attributes", $data)) {
            if (array_key_exists("virtual_provider", $data['attributes'])) {
                if ($data['attributes']['virtual_provider'] == "zoom") {
                    $checkZoom = true;
                }
            }
        }
        if (false) {
            return $this->checkBookingGoogle($data, $object, $maxPerHour, $checkZoom);
        } else {
            return $this->checkBookingLocal($data, $object, $maxPerHour, $checkZoom);
        }
    }

    public function checkBookingLocal($data, $object, $maxPerHour, $checkZoom) {
        $attributes = $object->attributes;
        if (array_key_exists("google_calendar", $attributes)) {
            $google_calendar = $attributes['google_calendar'];
            if ($google_calendar) {
                $dateT = new Carbon($data['from']);
                $date2T = new Carbon($data['to']);
                $events = Event::get($dateT, $date2T, [], $google_calendar);
                if (count($events) > 0) {
                    return ['status' => "error", "message" => "merchant_limit"];
                }
            }
        }
        $date = date_create($data['from']);
        date_sub($date, date_interval_create_from_date_string("1 seconds"));
        $date2 = date_create($data['to']);
        date_add($date2, date_interval_create_from_date_string("1 seconds"));
        if ($checkZoom) {
            /* $booking1 = Booking::whereBetween('starts_at', [date_format($date, "Y-m-d H:i:s"), date_format($date2, "Y-m-d H:i:s")])
              ->whereColumn("price", "total_paid")->where("notes", self::PENDING)->count();

              $booking2 = Booking::whereBetween('ends_at', [date_format($date, "Y-m-d H:i:s"), date_format($date2, "Y-m-d H:i:s")])
              ->whereColumn("price", "total_paid")->where("notes", self::PENDING)->count();
              $booking3 = Booking::where('starts_at', '<=', date_format($date, "Y-m-d H:i:s"))->where('ends_at', '>=', date_format($date2, "Y-m-d H:i:s"))
              ->whereColumn("price", "total_paid")->where("notes", self::PENDING)->count(); */
            $booking1 = Booking::where('starts_at', '<=', date_format($date, "Y-m-d H:i:s"))
                    ->where('ens_at', '>', date_format($date, "Y-m-d H:i:s"))
                    ->whereColumn("price", "total_paid")->count();

            $booking2 = Booking::where('starts_at', '>', date_format($date, "Y-m-d H:i:s"))
                    ->where('starts_at', '<', date_format($date2, "Y-m-d H:i:s"))
                    ->whereColumn("price", "total_paid")->count();
            if ($booking1 >= self::VIRTUAL_BOOKING || $booking2 >= self::VIRTUAL_BOOKING) {
                return ['status' => "error", "message" => "zoom_limit"];
            }
            return ['status' => "success", "message" => "zoom_ok"];
        } else {
            $array1 = $object->bookingsStartsBetween(date_format($date, "Y-m-d H:i:s"), date_format($date2, "Y-m-d H:i:s"))
                            ->whereColumn("price", "total_paid")->where("notes", self::PENDING)->get();
            $array2 = $object->bookingsEndsBetween(date_format($date, "Y-m-d H:i:s"), date_format($date2, "Y-m-d H:i:s"))
                            ->whereColumn("price", "total_paid")->where("notes", self::PENDING)->get();
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

    public function checkBookingGoogle($data, $object, $maxPerHour, $checkZoom) {
        $attributes = $object->attributes;
        if (array_key_exists("google_calendar", $attributes)) {
            $google_calendar = $attributes['google_calendar'];
            if ($google_calendar) {
                $date = new Carbon($data['from']);
                $date2 = new Carbon($data['to']);
                $events = Event::get($date, $date2, [], $google_calendar);
                if (count($events) > 0) {
                    return ['status' => "error", "message" => "merchant_limit"];
                }
            }
        }
        $date = new Carbon($data['from']);
        $date2 = new Carbon($data['to']);
        $events = Event::get($date, $date2);
        if ($checkZoom) {
            $zoomEvents = 0;
            foreach ($events as $event) {
                if ($event->location == "zoom") {
                    $zoomEvents++;
                }
            }
            if ($zoomEvents >= self::VIRTUAL_BOOKING) {
                return ['status' => "error", "message" => "zoom_limit"];
            }
            return ['status' => "success", "message" => "zoom_ok"];
        } else {
            $eventSlots = 0;
            foreach ($events as $event) {
                foreach ($event->attendees as $atendee) {
                    if ($atendee->email == $object->email) {
                        $eventSlots++;
                    }
                }
            }
            if ($eventSlots >= $maxPerHour) {
                return ['status' => "error", "message" => "merchant_limit"];
            }
            return ['status' => "success", "message" => "merchant_ok"];
        }
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function addBookingObject(array $data, User $user) {
        $data['call'] = false;
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
                if ($result['status'] == 'success' || $data['call']) {
                    $result = $this->checkBookingAvailability($data, $object);
                    if ($result['status'] == 'success' || $data['call']) {
                        return $this->createBooking($object, $user, $data);
                    }
                    return $result;
                }
                return $result;
            }
        }
        return array("status" => "error", "message" => "not_found");
    }

    public function createBooking($object, $user, $data) {
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
        if ($data['call']) {
            $isCall = true;
        }
        if (array_key_exists("virtual_meeting", $data)) {
            if ($data['virtual_meeting']) {
                $update["options"]["virtual_meeting"] = true;
                if (array_key_exists("virtual_provider", $attributes)) {
                    $update["options"]["location"] = $attributes["virtual_provider"];
                }
            }
        }

        $requiresAuth = false;
        $update["options"]["status"] = "approved";
        $update["options"]["call"] = $data['call'];
        if (!$isCall) {
            if (array_key_exists("booking_requires_authorization", $attributes)) {
                if ($attributes["booking_requires_authorization"]) {
                    $update["notes"] = "in_confirmation";
                    $requiresAuth = true;
                    $update["options"]["status"] = "pending";
                }
            }
        }
        $update["starts_at"] = $data['from'];
        $update["ends_at"] = $data['to'];
        $booking = new Booking;
        $booking = $booking->make($update)
                ->customer()->associate($user)
                ->bookable()->associate($object)
                ->save();
        $booking = Booking::where('id', DB::getPdo()->lastInsertId())->with('bookable')->first();
        if ($requiresAuth) {
            $this->handleAuthorizationRequest($user, $booking, $object);
        }
        if (array_key_exists("is_admin", $data)) {
            if ($data['is_admin']) {
                if ($object->checkAdminAccess($user->id)) {
                    $update["total_paid"] = $update["price"];
                    $booking->total_paid = $booking->price;
                    unset($update['bookable']);
                    unset($update['client']);
                    Booking::where("id", $booking->id)->update($update);
                }
            }
        }
        return array("status" => "success", "message" => "Booking created", "booking" => $booking, "requires_auth" => $requiresAuth);
    }

    public function createBookingFromItem($object_id, $item) {
        $user = $item->user;
        $type = $data['type'];
        $class = self::MODEL_PATH . $type;

        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                
            }
        }
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
                    $result = $this->checkTime($data['from'], $object);
                    if ($result['status'] == 'success') {
                        $admin = $object->checkAdminAccess($user->id);
                        if ($admin || $booking->customer_id == $user->id) {
                            if ($booking->notes == self::PENDING || $booking->notes == self::IN_COMFIRMATION) {
                                $result = $this->checkBookingAvailability($data, $object);
                                if ($result['status'] == 'success') {
                                    return $this->editBooking($booking, $object, $user, $data, $admin);
                                }
                                return $result;
                            }
                            return array("status" => "error", "message" => "booking_not_editable");
                        }
                        return $result;
                    }
                    return array("status" => "error", "message" => "too_soon");
                }
                return array("status" => "error", "message" => "not_found");
            }
        }
        return array("status" => "error", "message" => "not_found");
    }

    private function editBooking($booking, $object, $user, $data, $admin) {
        $dateFrom = date_create($data['from']);
        date_add($dateFrom, date_interval_create_from_date_string(date('Z') . " seconds"));
        $dateTo = date_create($data['to']);
        date_add($dateTo, date_interval_create_from_date_string(date('Z') . " seconds"));
        $booking->starts_at = $dateFrom;
        $booking->ends_at = $dateTo;
        $attributes = $booking->options;
        $reqAttrs = $data['attributes'];
        foreach ($reqAttrs as $key => $value) {
            $attributes[$key] = $reqAttrs[$key];
        }
        if ($admin && $booking->notes == self::IN_COMFIRMATION) {
            $booking->notes = self::PENDING;
        }
        if ($booking->customer_id == $user->id) {
            $attributes2 = $object->attributes;
            if (array_key_exists("booking_requires_authorization", $attributes2)) {
                if ($attributes2["booking_requires_authorization"]) {
                    $booking->notes = self::IN_COMFIRMATION;
                }
            }
        }
        $booking->options = $attributes;
        $booking->updated_at = date_add(date_create(), date_interval_create_from_date_string(date('Z') . " seconds"));
        ;
        $update = $booking->toArray();
        $update["options"] = json_encode($attributes);
        $booking->touch();
        //dd($update);
        $booking->total_paid = $update['total_paid'];
        $booking->starts_at = $data['from'];
        $booking->ends_at = $data['to'];
        unset($update['created_at']);
        Booking::where("id", $booking->id)->update($update);
        if ($booking->price == $booking->total_paid) {
            dispatch(new CreateGoogleEvent($booking->id));
        }
        //$this->handleAuthorizationRequest($user, $booking, $object);
        return array("status" => "success", "message" => "Booking update request sent", "booking" => $booking);
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
        $date2 = date_create($starts);
        $now = date_format($date, "Y-m-d H:i:s");
        $starts = date_format($date2, "Y-m-d H:i:s");
        $datetimestampDelivery = strtotime($starts);
        $dateTimestampNow = strtotime($now);
        $diff = ($datetimestampDelivery - $dateTimestampNow ) / 60 / 60;
        if ($diff < 1) {
            return array("status" => "error", "message" => "too_soon", "limit" => $diff, "diff" => ($diff < 14));
        }
        return array("status" => "success", "message" => "Booking in limit");
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
                    $updateData["notes"] = self::PENDING;
                    $typeAlert = self::BOOKING_BOOKABLE_APPROVED;
                } elseif ($status == "denied") {
                    $updateData["notes"] = self::IN_COMFIRMATION;
                    if (array_key_exists("reason", $data)) {
                        $updateData["options"]["reason"] = $data["reason"];
                        $payload['reason'] = $data["reason"];
                    }
                    $typeAlert = self::BOOKING_BOOKABLE_DENIED;
                }
                $options = $updateData["options"];
                $updateData["options"] = json_encode($updateData["options"]);
                Booking::where("id", $booking->id)->update($updateData);
                //$booking->touch();
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
        $class = '';
        if (strpos($data['type'], "Models") !== false) {
            $class = $data['type'];
        } else {
            $class = self::MODEL_PATH . $data['type'];
        }
        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                if ($object->checkAdminAccess($user->id)) {
                    if (!array_key_exists("id", $data)) {
                        $data['id'] = null;
                    }
                    if ($data['id']) {
                        $data['from'] = date_format(date_create($data['from']), "h:i a");
                        $data['to'] = date_format(date_create($data['to']), "h:i a");
                        $id = $data["id"];
                        if (strpos($data['type'], "Models") !== false) {
                            $data["bookable_type"] = $data['type'];
                        } else {
                            $data["bookable_type"] = self::MODEL_PATH . $data['type'];
                        }

                        $data["bookable_id"] = $data['object_id'];
                        $data['updated_at'] = date_add(date_create(), date_interval_create_from_date_string(date('Z') . " seconds"));
                        unset($data["id"]);
                        unset($data["object_id"]);
                        unset($data["type"]);
                        Availability::where("id", $id)->update($data);
                        $serviceBooking = Availability::find($id);
                        return array("status" => "success", "message" => "Availability updated", "availability" => $serviceBooking);
                    } else {
                        $serviceBooking = new Availability();
                        $dateFrom = date_create($data['from']);
                        $dateTo = date_create($data['to']);
                        $serviceBooking->make(['range' => $data['range'], 'from' => date_format($dateFrom, "h:i a"), 'to' => date_format($dateTo, "h:i a"), 'is_bookable' => true])
                                ->bookable()->associate($object)
                                ->save();
                        $serviceBooking = Availability::find(DB::getPdo()->lastInsertId());
                        return array("status" => "success", "message" => "Availability created", "availability" => $serviceBooking);
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
        $booking = Booking::where('id', $booking)->with(["bookable", "customer"])->first();
        if ($booking) {
            $client = $booking->customer;
            $bookable = $booking->bookable;
            $send = false;
            if ($user->id == $client->id) {
                $send = true;
                //$booking = $user->bookings->where('id',$booking->id)->first();
            }
            if (!$send) {
                if ($bookable->checkAdminAccess($user->id)) {
                    $send = true;
                    //$booking = $bookable->bookings->where('id',$booking->id)->first();
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
                "data" => $user->futureBookings()
                        ->where('total_paid', 0)
                        ->with("bookable")
                        ->orderBy('starts_at')->get());
        } else if ($query == "customer_unapproved") {
            return array(
                "status" => "success",
                "message" => "",
                "data" => $user->bookings()
                        ->where('notes', self::IN_COMFIRMATION)
                        ->with("bookable")
                        ->orderBy('starts_at')->get());
        } else if ($query == "customer_past") {
            return array(
                "status" => "success",
                "message" => "",
                "data" => $user->pastBookings()
                        ->whereColumn('price', 'total_paid')
                        ->orderBy('starts_at')
                        ->with("bookable")->get());
        } else if ($query == "customer_upcoming") {
            return array(
                "status" => "success",
                "message" => "",
                "data" => $user->futureBookings()
                        ->where('notes', self::PENDING)
                        ->whereColumn('price', 'total_paid')
                        ->orderBy('starts_at')->with("bookable")->get());
        } else if ($query == "customer_all") {
            return array(
                "status" => "success",
                "message" => "",
                "data" => $user->futureBookings()
                        ->with("bookable")
                        ->orderBy('starts_at')->get());
        }
        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                if ($query == "day") {
                    return $this->getGoogleBookingsDay($data, $object);
                    $results = $object->bookingsStartsBetween($data['from'] . " 00:00:00", $data['from'] . " 23:59:59")
                                    ->orderBy('starts_at')
                                    ->where('notes', self::PENDING)
                                    ->whereColumn('price', 'total_paid')->get();
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
                                "data" => $object->futureBookings()
                                        ->whereColumn('price', 'total_paid')
                                        ->where('notes', self::PENDING)
                                        ->orderBy('starts_at')
                                        ->with("customer")->get());
                        } else if ($query == "bookable_past") {
                            return array(
                                "status" => "success",
                                "message" => "",
                                "data" => $object->pastBookings()
                                        ->whereColumn('price', 'total_paid')
                                        ->orderBy('starts_at')
                                        ->with("customer")->get());
                        } else if ($query == "bookable_unapproved") {
                            return array(
                                "status" => "success",
                                "message" => "",
                                "data" => $object->futureBookings()
                                        ->where('notes', self::IN_COMFIRMATION)
                                        ->orderBy('starts_at')
                                        ->with("customer")->get());
                        } else if ($query == "bookable_all") {
                            return array(
                                "status" => "success",
                                "message" => "",
                                "data" => $object->futureBookings()->where(function($query) {
                                                    $query->whereColumn('price', 'total_paid')
                                                    ->orWhere('notes', self::IN_COMFIRMATION);
                                                })->orderBy('starts_at')
                                        ->with("customer")->get());
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
                return array("status" => "success", "message" => "", "data" => $object->availabilities2);
            }
        }
        return array("status" => "error", "message" => "not_found");
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
