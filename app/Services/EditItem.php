<?php

namespace App\Services;

use App\Models\Item;
use App\Models\CoveragePolygon;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use App\Mail\GeneralNotification;
use App\Services\EditCart;
use App\Services\EditBooking;
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
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $editCart;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $editBooking;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditCart $editCart, EditBooking $editBooking) {
        $this->notifications = app('Notifications');
        $this->editBooking = $editBooking;
        $this->editCart = $editCart;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function changeStatusItems(User $user, array $data) {
        $status = $data["status"];
        $totalWeight = 0;
        $extras = [];
        $originAddress = [];
        $destinyAddress = [];
        $order = Order::where("id", $data['order_id'])->with(['items', 'user', 'merchant', 'orderAddresses', 'orderConditions'])->first();
        $items = $order->items;
        if (count($items) > 0) {
            $merchant = $order->merchant;
            $itemsDesc = "Orden: " . $order->id . ", Items: ";
            if ($merchant) {
                if ($merchant->checkAdminAccess($user->id)) {
                    foreach ($items as $item) {
                        $itemsDesc .= $item->name . ": " . $item->quantity . ", ";
                        $attributes = json_decode($item->attributes, true);
                        if (array_key_exists('weight', $attributes)) {
                            if ($attributes['weight']) {
                                $totalWeight += $attributes['weight'];
                            }
                        }

                        $item->fulfillment = $data['status'];
                        $item->save();
                    }
                    if ($totalWeight) {
                        $extras['weight'] = $totalWeight;
                    }
                    $extras['description_pickup'] = $itemsDesc;
                    $extras['description_delivery'] = $itemsDesc;
                }
            }
            $shippingAddress = $order->orderAddresses()->where('type', 'shipping')->first();
            if ($data["status"] == "fullfill" && $shippingAddress) {

                $extras["request_date"] = date_create();
                $extras['declared_value'] = $order->total;
                if ($order->payment_method_id == 5) {
                    $extras['special_service'] = 2;
                    $extras['value_collection'] = $order->total;
                } else {
                    $extras['special_service'] = 0;
                }
                $attributesM = $merchant->attributes;
                $extras['merchant_id'] = $merchant->id;
                $extras['merchant_email'] = $merchant->email;
                $extras['client_email'] = $order->user->email;
                $extras['user_id'] = $user->id;
                $shippingCondition = $order->orderConditions()->where('type', "shipping")->first();
                $attributes = json_decode($shippingCondition->attributes, true);
                if ($attributes["platform"] == "MerchantShiping") {
                    $polygon = CoveragePolygon::find($attributes["platform_id"]);
                    $extras['platform_id'] = $attributes["platform_id"];
                } else {
                    $polygon = CoveragePolygon::where("merchant_id", $merchant->id)->where('provider', $attributes["platform"])->with("address")->first();
                }

                $origin = $polygon->address;
                $className = "App\\Services\\" . $attributes["platform"];
                $gateway = new $className;
                $result = $gateway->sendOrder($origin->toArray(), $shippingAddress->toArray(), $extras);
                if ($result['status'] == 'success') {
                    $attributesO = json_decode($origin->attributes, true);
                    $attributesO['shipping_provider_id'] = $result['shipping_id'];
                    $attributesO['shipping_provider'] = $attributes['platform'];
                    $order->attributes = json_encode($attributesO);
                    $order->execution_status = "completed";
                    $order->save();
                    $result['subject'] = "Orden No. " . $order->id . " " . $result['subject'];
                    $users = User::whereIn("id", [2, 3, $extras['user_id'], $order->user->id])->get();
                    Mail::to($users)->send(new GeneralNotification($result['subject'], $result['body']));
                } else {
                    $payload = [
                        "order_id" => $order->id,
                        "status" => $data["status"],
                    ];
                    $data = [
                        "trigger_id" => $user->id,
                        "message" => "",
                        "subject" => "Error generating shipment with platform: " . $attributes["platform"],
                        "object" => "Merchant",
                        "sign" => true,
                        "payload" => $payload,
                        "type" => "shipping-error",
                        "user_status" => $user->getUserNotifStatus()
                    ];
                    $client = User::find(2);
                    $followers = [$client];
                    $date = date("Y-m-d H:i:s");
                    $this->notifications->sendMassMessage($data, $followers, $user, true, $date, true);
                }
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
                "type" => self::ORDER_FULFILLMENT_STATUS_UPDATE,
                "user_status" => $user->getUserNotifStatus()
            ];
            $client = $order->user;
            $followers = [$client];
            $date = date("Y-m-d H:i:s");
            //$this->notifications->sendMassMessage($data, $followers, $user, true, $date, true);
            return array("status" => "success", "message" => "Status updated");
        }
        return array("status" => "error", "message" => "Object not found");
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
