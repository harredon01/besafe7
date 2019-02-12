<?php

namespace App\Services;

use Validator;
use App\Models\User;
use App\Models\Push;
use App\Models\Delivery;

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
                    $details["meal"] = $dish;
                    $delivery->type_id = $data['type_id'];
                    $delivery->starter_id = $data['starter_id'];
                    $delivery->main_id = $data['main_id'];
                    $delivery->dessert_id = $data['dessert_id'];
                }


                $delivery->observation = $data['observation'];
                $delivery->details = json_encode($details);
                $delivery->status = "enqueue";
                $delivery->save();
                return array("status" => "success", "message" => "Delivery scheduled for transit");
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
                    'starter_id' => 'required|max:255',
                    'main_id' => 'required|max:255',
                    'dessert_id' => 'required|max:255',
                    'observation' => 'required|max:255',
        ]);
    }

}
