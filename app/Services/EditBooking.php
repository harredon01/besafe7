<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\Favorite;
use App\Models\User;
use Validator;

class EditBooking {

    const MODEL_PATH = 'App\\Models\\';

    private function checkAvailable(array $data) {
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
                    $type = $data['type'];
                    $class = self::MODEL_PATH . $type;
                    $object = $class::find($data['object_id']);
                    $results = $object->bookingsStartsBetween($data['from'], $data['to'])->get();
                    if (count($results) > 0) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }

            return false;
        }
        return true;
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
                $result = $this->checkAvailable($data);
                if ($result) {
                    $object->newBooking($user, $data['from'], $data['to']);
                    return response()->json(array("status" => "success", "message" => "Booking created"));
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
                $serviceBooking = new Availability();
                $serviceBooking->make(['range' => $data['range'], 'from' => $data['from'], 'to' => $data['to'], 'is_bookable' => true])
                        ->bookable()->associate($object)
                        ->save();
                return response()->json(array("status" => "success", "message" => "Availability created"));
            }
        }
        return response()->json(array("status" => "error", "message" => "Object not found"));
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBookingsObject(array $data) {
        $validator = $this->validatorGetBookings($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $type = $data['type'];
        $class = "App\\Models\\" . $type;
        if (class_exists($class)) {
            $object = $class::find($data['object_id']);
            if ($object) {
                if ($user->id == $object->user_id) {
                    return response()->json(array("status" => "success", "message" => "", "data" => $object->bookingsStartsBetween($data['from'], $data['to'])->get()));
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
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorGetBookings(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'object_id' => 'required|max:255',
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
