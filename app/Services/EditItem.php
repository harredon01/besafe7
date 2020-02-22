<?php

namespace App\Services;

use App\Models\Item;
use App\Models\User;
use Validator;
use DB;

class EditItem {

    const MODEL_PATH = 'App\\Models\\';
    const ORDER_FULFILLMENT_STATUS_UPDATE = 'order_fullfillment_status_update';

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

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function changeStatusItems(User $user, array $data) {
        $validator = $this->validatorStatusBookings($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $status = $data["status"];
        $totalWeight = 0;
        $originAddress = [];
        $destinyAddress = [];
        $items = Item::whereIn("id", $data['items']);
        if (count($items) > 0) {
            $merchant = $items[0]->merchant;
            $order = $items[0]->order;
            if ($merchant) {
                if ($merchant->checkAdminAccess($user->id)) {
                    foreach ($items as $item) {
                        $attributes = json_decode($item->attributes, true);
                        $totalWeight += $attributes['weight'];
                        $item->fulfillment = $data['status'];
                        $item->save();
                    }
                }
            }
            $shippingAddress = $order->orderAddresses()->where('type', 'shipping')->first();
            if ($data["status"] == "fullfill" && $shippingAddress) {
                $originCountry = Country::find($merchant->country_id);
                $originRegion = Region::find($merchant->region_id);
                $originCity = Region::find($merchant->city_id);

                $destinyCountry = Country::find($shippingAddress->country_id);
                $destinyRegion = Region::find($shippingAddress->region_id);
                $destinyCity = Region::find($shippingAddress->city_id);
                $shippingCondition = $order->orderConditions()->where('type', "shipping")->first();
                $attributes = json_encode($shippingCondition->attributes, true);
                $className = "App\\Services\\" . $attributes["platform"];
                $gateway = new $className;
                $result = $gateway->getOrderShippingPrice($origin->toArray(), $destination->toArray());
            }
            $payload = [
                "order_id" => $order->id,
                "status" => $data["status"],
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
            $client = $order->user;
            $followers = [$client];
            $date = date("Y-m-d H:i:s");
            $this->notifications->sendMassMessage($data, $followers, $user, true, $date, true);
            return response()->json(array("status" => "success", "message" => "Status updated"));
        }
        return response()->json(array("status" => "error", "message" => "Object not found"));        
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

}
