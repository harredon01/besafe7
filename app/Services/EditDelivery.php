<?php

namespace App\Services;

use Validator;
use App\Models\User;
use App\Jobs\RecurringOrder;
use App\Models\Push;
use App\Models\Delivery;
use App\Mail\DeliveryScheduled;
use Illuminate\Support\Facades\Mail;

class EditDelivery {

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postDeliveryOptions(User $user, array $data) {
        $validator = $this->validatorDelivery($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $delivery = Delivery::find($data['delivery_id']);
        if ($delivery) {
            if ($delivery->user_id == $user->id) {
                if ($delivery->status == "suspended") {
                    return array("status" => "error", "message" => "Delivery suspended");
                }
                $date = date_create();
                $now = date_format($date, "Y-m-d H:i:s");
                $dayofweek = date('w', strtotime($now));
                $today = date_format($date, "Y-m-d");
                $dateTimestampToday = strtotime($dateToday);
                $datetimestampDelivery = strtotime($delivery->delivery);
                $dateTimestampNow = strtotime($now);
                if ($dayofweek > 0 && $dayofweek < 5) {
                    date_add($date, date_interval_create_from_date_string("1 days"));
                    $tomorrow = date_format($date, "Y-m-d");
                    $dateTomorrow = $tomorrow . " 23:59:59";
                    $dateToday = $today . " 22:00:00";
                    $dateTimestampTomorrow = strtotime($dateTomorrow);
                    if ($datetimestampDelivery < $dateTimestampTomorrow) {
                        if ($dateTimestampNow > $dateTimestampToday) {
                            return array("status" => "error", "message" => "Limit passed");
                        }
                    }
                } else if ($dayofweek == 5) {
                    
                } else if ($dayofweek == 6) {
                    date_add($date, date_interval_create_from_date_string("2 days"));
                    $monday = date_format($date, "Y-m-d");
                    $dateMonday = $monday . " 23:59:59";
                    $dateToday = $today . " 22:00:00";
                    $dateTimestampMonday = strtotime($dateMonday);
                    if ($datetimestampDelivery < $dateTimestampMonday) {
                        $dateTimestampNow = strtotime($now);
                        $dateToday = $today . " 12:00:00";
                        $dateTimestampToday = strtotime($dateToday);
                        if ($dateTimestampNow > $dateTimestampToday) {
                            return array("status" => "error", "message" => "Limit passed");
                        }
                    }
                } else if ($dayofweek == 0) {
                    date_add($date, date_interval_create_from_date_string("1 days"));
                    $tomorrow = date_format($date, "Y-m-d");
                    $dateTomorrow = $tomorrow . " 23:59:59";
                    $dateToday = $today . " 22:00:00";
                    $dateTimestampTomorrow = strtotime($dateTomorrow);
                    if ($datetimestampDelivery < $dateTimestampTomorrow) {
                        return array("status" => "error", "message" => "Limit passed");
                    }
                }

                $details = json_decode($delivery->details, true);
                if ($delivery->status == "deposit") {
                    $this->suspendCreditDeposits($delivery);
                    $details["deposit"] = true;
                } else {
                    $validator = $this->validatorDeliveryMeal($data);
                    if ($validator->fails()) {
                        return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
                    }
                    $dish = [
                        'type_id' => $data['type_id'],
                        'starter_id' => $data['starter_id'],
                        'main_id' => $data['main_id'],
                        'dessert_id' => $data['dessert_id']
                    ];
                    $details["dish"] = $dish;
                    $this->checkRecurringPosibility($user, $data['ip_address']);
                }

                $delivery->observation = $data['observation'];
                $delivery->details = json_encode($details);
                $delivery->status = "enqueue";
                $delivery->save();
                $date = date_create($delivery->delivery);
                $data["date"] = date_format($date, "Y/m/d");
                Mail::to($user)->send(new DeliveryScheduled($data));
                return array("status" => "success", "message" => "Delivery scheduled for transit", "details" => $details);
            }
            return array("status" => "error", "message" => "Delivery does not belong to user");
        }
        return array("status" => "error", "message" => "Delivery does not exist");
    }

    private function suspendCreditDeposits(Delivery $deliveryDep) {
        $push = Push::where("user_id", $deliveryDep->user_id)->where("platform", "food")->first();
        if ($push) {
            $push->credits = $push->credits - 1;
            $push->save();
            if ($push->credits == 0) {
                $deliveries = Delivery::where("user_id", $delivery->user_id)->where("status", "pending")->get();
                foreach ($deliveries as $delivery) {
                    $details = json_decode($delivery->details, true);
                    if (array_key_exists("pickup", $details)) {
                        if ($details["pickup"] == "envase") {
                            $delivery->status = "suspended";
                            $delivery->save();
                        }
                    }
                }
            }
        }
    }

    private function checkRecurringPosibility(User $user, $ip_address) {
        $deliveries = Delivery::where("user_id", $user->id)->where("status", "pending")->get();
        if (count($deliveries) == 1) {
            $order = Order::where("user_id", $user->id)->where("is_recurring", true)->where("status", "approved")->orderBy('id', 'desc')->first();
            dispatch(new RecurringOrder($order, $ip_address));
        }
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorDelivery(array $data) {
        return Validator::make($data, [
                    'delivery_id' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorDeliveryMeal(array $data) {
        return Validator::make($data, [
                    'delivery_id' => 'required|max:255',
                    'type_id' => 'required|max:255',
                    'main_id' => 'required|max:255',
        ]);
    }

}
