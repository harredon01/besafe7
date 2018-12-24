<?php

namespace App\Services;

use Validator;
use App\Services\EditAlerts;
use App\Models\User;
use App\Models\Delivery;

class EditDelivery {

    /**
     * The EditAlert implementation.
     *
     */
    protected $editAlerts;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditAlerts $editAlerts) {
        $this->editAlerts = $editAlerts;
    }

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
                $details = json_decode($delivery->details,true);
                $dish = [
                    'type_id' => $data['type_id'],
                    'starter_id' => $data['starter_id'],
                    'main_id' => $data['main_id'], 
                    'dessert_id' => $data['dessert_id']
                ];
                $details["meal"] = $dish;
                $delivery->delivery = "20" . $data['year'] . "-" . $data['month'] . "-" . $data['day'];
                $delivery->type_id = $data['type_id'];
                $delivery->starter_id = $data['starter_id'];
                $delivery->main_id = $data['main_id'];
                $delivery->dessert_id = $data['dessert_id'];
                $delivery->observation = $data['observation'];
                $delivery->details = json_encode($details);
                $delivery->status = "transit";
                $delivery->save();
                return array("status" => "success", "message" => "Delivery scheduled for transit");
            }
            return array("status" => "error", "message" => "Delivery does not belong to user");
        }
        return array("status" => "error", "message" => "Delivery does not exist");
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
                    'type_id' => 'required|max:255',
                    'starter_id' => 'required|max:255',
                    'main_id' => 'required|max:255',
                    'dessert_id' => 'required|max:255',
                    'observation' => 'required|max:255',
                    'details' => 'required|max:255',
                    'day' => 'required|max:255',
                    'month' => 'required|max:255',
                    'year' => 'required|max:255'
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorFollower(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'object' => 'required|max:255',
                    'follower' => 'required|max:255',
        ]);
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditGroupMessage() {
        return 'There was a problem editing your group';
    }

}
