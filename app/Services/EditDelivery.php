<?php

namespace App\Services;

use Validator;
use App\Models\User;
use App\Jobs\RecurringOrder;
use App\Jobs\AddDeliveryInfo;
use App\Models\Push;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\Address;
use App\Models\Article;
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
                $results = $this->checkDeliveryTime($delivery);
                if ($results['status'] == 'success') {
                    $details = json_decode($delivery->details, true);
                    $delivery->observation = $data['observation'];
                    if ($delivery->status == "deposit") {
                        $this->suspendCreditDeposits($delivery);
                        $details["deposit"] = true;
                        $delivery->details = json_encode($details);
                        $delivery->status = "enqueue";
                        $delivery->save();
                    } else {
                        $validator = $this->validatorDeliveryMeal($data);
                        if ($validator->fails()) {
                            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
                        }
                        $starter = "";
                        if (array_key_exists('starter_id', $data)) {
                            if ($data['starter_id']) {
                                $starter = $data['starter_id'];
                            }
                        }
                        $drink = "";
                        if (array_key_exists('drink_id', $data)) {
                            if ($data['drink_id']) {
                                $drink = $data['drink_id'];
                            }
                        }
                        $dessert = "";
                        if (array_key_exists('dessert_id', $data)) {
                            if ($data['dessert_id']) {
                                $dessert = $data['dessert_id'];
                            }
                        }
                        $dish = [
                            'type_id' => $data['type_id'],
                            'starter_id' => $starter,
                            'drink_id' => $drink,
                            'main_id' => $data['main_id'],
                            'dessert_id' => $dessert
                        ];
                        $details["dish"] = $dish;
                        $delivery->details = json_encode($details);
                        $delivery->status = "enqueue";
                        $delivery->save();
                        $this->checkRecurringPosibility($user, $data['ip_address']);
                        dispatch(new AddDeliveryInfo($delivery));
                    }
                    $date = date_create($delivery->delivery);
                    $pending = Delivery::where("user_id", $user->id)->where("status", "pending")->count();
                    $data["date"] = date_format($date, "Y/m/d");
                    Mail::to($user)->send(new DeliveryScheduled($data));

                    return array("status" => "success", "message" => "Delivery scheduled for transit", "details" => $details, "date" => $date, "pending_count" => $pending);
                } else {
                    return $results;
                }
            }
            return array("status" => "error", "message" => "Delivery does not belong to user");
        }
        return array("status" => "error", "message" => "Delivery does not exist");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function adminPostDeliveryOptions(array $data) {
        $validator = $this->validatorDelivery($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $delivery = Delivery::find($data['delivery_id']);
        if ($delivery) {
            $details = json_decode($delivery->details, true);
            $validator = $this->validatorDeliveryMeal($data);
            if ($validator->fails()) {
                return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
            }
            if (!array_key_exists('starter_id', $data)) {
                $data['starter_id'] = "";
            }
            if (!array_key_exists('drink_id', $data)) {
                $data['drink_id'] = "";
            }
            if (!array_key_exists('dessert_id', $data)) {
                $data['dessert_id'] = "";
            }
            $dish = [
                'type_id' => $data['type_id'],
                'starter_id' => $data['starter_id'],
                'drink_id' => $data['drink_id'],
                'main_id' => $data['main_id'],
                'dessert_id' => $data['dessert_id']
            ];
            $details["dish"] = $dish;
            $delivery->details = json_encode($details);
            $delivery->status = "enqueue";
            $delivery->save();
            dispatch(new AddDeliveryInfo($delivery));
            return array("status" => "success", "message" => "Delivery scheduled for transit", "details" => $details);
        }
        return array("status" => "error", "message" => "Delivery does not exist");
    }

    public function changeDeliveryDate(User $user, array $data) {
        if (array_key_exists("delivery", $data)) {
            $sameDay = Delivery::where("user_id", $user->id)->where("delivery", $data["delivery"])->where("details", "like", 'deliver":"envase')->count();
            if ($sameDay > 0) {
                $push = Push::where("user_id", $user->id)->where("platform", "food")->first();
                $suspend = false;
                $sameDay++;
                if ($push) {
                    if ($push->credits < $sameDay) {
                        $suspend = true;
                    }
                }
                if ($suspend) {
                    $pending2 = Delivery::where("user_id", $user->id)->where("status", "pending")->count();
                    if ($pending2 > 1) {
                        $suspendedDelivery = Delivery::where("user_id", $user->id)->where("status", "pending")->orderBy('delivery', 'desc')->first();
                        $suspendedDelivery->status = "suspended";
                        $suspendedDelivery->save();
                        $push->credits = $push->credits + 1;
                        $push->save();
                    } else {
                        return array("status" => "error", "message" => "Not Enough Pending");
                    }
                }
            }
            $date = $this->getNextValidDate(date_create($data["delivery"]));
            $reprogramDelivery = Delivery::where("user_id", $user->id)->where("status", "pending")->orderBy('delivery', 'desc')->first();
            $reprogramDelivery->delivery = date_format(date_create($data["delivery"]), "Y-m-d") . " 12:00:00";
            $reprogramDelivery->save();
            $deliveryDetails = json_decode($reprogramDelivery->details, true);
            if (array_key_exists("deliver", $deliveryDetails)) {
                if (strpos($deliveryDetails['deliver'], 'envase') !== false) {
                    if ($sameDay > 1) {
                        $nextDelivery = Delivery::where("user_id", $user->id)->where("delivery", date_format($date, "Y-m-d") . " 12:00:00")->whereIn("status", ["pending", "deposit"])->first();
                        if ($nextDelivery) {
                            $details = json_decode($nextDelivery->details, true);
                            $details["pickup"] = $sameDay . " envases completos";
                            $nextDelivery->details = json_encode($details);
                            $nextDelivery->save();
                        }
                    }
                }
            }
            return array("status" => "success", "message" => "Delivery date changed", "delivery" => $reprogramDelivery);
        }
    }

    public function changeDeliveryAddress(User $user, array $data) {
        $validator = $this->validatorUpdateAddress($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $delivery = Delivery::find($data["delivery_id"]);
        if ($delivery->user_id != $user->id) {
            return array("status" => "error", "message" => "Access denied");
        }
        $attributes = json_decode($delivery->details, true);
        $products = $attributes["products"];
        $product = $products["product"];
        if ($product == 210 || $product == 220) {
            $theAddress = Address::find($data["address_id"]);
            $geolocation = app('Geolocation');
            $result = $geolocation->checkMerchantPolygons($theAddress->lat, $theAddress->long, $data['merchant_id'], $delivery->provider);
            if ($result["status"] == "success") {
                $orderAddresses = $theAddress->toarray();
                unset($orderAddresses['id']);
                unset($orderAddresses['is_default']);
                $orderAddresses['order_id'] = $attributes["order_id"];
                $orderAddresses['type'] = "shipping";
                $polygon = $result['polygon'];
                $orderAddresses['polygon_id'] = $polygon->id;
                $newAddress = new OrderAddress();
                $newAddress->fill($orderAddresses);
                $newAddress->save();
                if ($data['all']) {
                    Delivery::where("user_id", $user->id)->where("status", "pending")->update(['address_id' => $newAddress->id, "updated_at" => date("Y-m-d H:i:s")]);
                } else {
                    $delivery->address_id = $newAddress->id;
                    $delivery->save();
                }

                return array("status" => "success", "message" => "Delivery Address Updated", "delivery" => $delivery);
            }
            return $result;
        }
        return array("status" => "error", "message" => "Forbidden product");
    }

    public function checkDeliveryTime(Delivery $delivery) {

        if ($delivery->status == "pending" || $delivery->status == "deposit" || $delivery->status == "enqueue") {
            
        } else {
            return array("status" => "error", "message" => "No se puede programar esa entrega");
        }
        //return array("status" => "success", "message" => "Delivery in limit");
        $date = date_create();
        $now = date_format($date, "Y-m-d H:i:s");
        $datetimestampDelivery = strtotime($delivery->delivery);
        $dateTimestampNow = strtotime($now);
        $diff = ($datetimestampDelivery - $dateTimestampNow) / 60 / 60;
        if ($diff < 14) {
            return array("status" => "error", "message" => "Limit passed");
        }
        return array("status" => "success", "message" => "Delivery in limit");
    }

    public function cancelDeliverySelection(User $user, $delivery) {
        $delivery = Delivery::find($delivery);
        if ($delivery) {
            if ($delivery->user_id == $user->id) {
                $results = $this->checkDeliveryTime($delivery);
                if ($results['status'] == 'success') {
                    $details = json_decode($delivery->details, true);
                    if (array_key_exists("deliver", $details)) {
                        if ($details["deliver"] == "deposit") {
                            $delivery->status = "deposit";
                            $this->activateCreditDeposits($delivery);
                        } else {
                            $delivery->status = "pending";
                            unset($details['dish']);
                        }
                    } else {
                        $delivery->status = "pending";
                        unset($details['dish']);
                    }
                    $delivery->observation = "";
                    $delivery->details = json_encode($details);

                    $delivery->save();
                    return array("status" => "success", "message" => "Delivery canceled");
                } else {
                    return $results;
                }
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
                $deliveries = Delivery::where("user_id", $deliveryDep->user_id)->where("status", "pending")->get();
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

    private function activateCreditDeposits(Delivery $deliveryDep) {
        $push = Push::where("user_id", $deliveryDep->user_id)->where("platform", "food")->first();
        if ($push) {
            $oldCredits = $push->credits;
            $push->credits = $push->credits + 1;
            $push->save();
            if ($oldCredits == 0) {
                $deliveries = Delivery::where("user_id", $deliveryDep->user_id)->where("status", "suspended")->get();
                foreach ($deliveries as $delivery) {
                    $details = json_decode($delivery->details, true);
                    if (array_key_exists("pickup", $details)) {
                        if ($details["pickup"] == "envase") {
                            $delivery->status = "pending";
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
            $order = Order::where("user_id", $user->id)->where("recurring_type", "limit")->where("status", "approved")->orderBy('id', 'desc')->first();
            if ($order) {
                dispatch(new RecurringOrder($order, $ip_address));
            }
        }
    }

    public function addInfoToDelivery(Delivery $delivery) {
        $details = json_decode($delivery->details, true);
        $dish = $details["dish"];
        $additionalInfo = [
            "foto_entrada" => "",
            "foto_plato" => "",
            "foto_postre" => "",
            "p_principal" => 0,
            "p_harinas" => 0,
            "p_verduras" => 0,
            "p_otro" => 0,
        ];
        $article = Article::find($dish["type_id"]);
        if ($article) {
            $attributes = json_decode($article->attributes, true);
            foreach ($attributes['entradas'] as $value) {
                if ($dish["starter_id"] == $value['codigo']) {
                    if (array_key_exists("imagen", $value)) {
                        $additionalInfo["foto_entrada"] = $value["imagen"];
                    }
                    if (array_key_exists("p_principal", $value)) {
                        if ($value["p_principal"]) {
                            $additionalInfo["p_principal"] += intval($value["p_principal"]);
                        }
                    }
                    if (array_key_exists("p_harinas", $value)) {
                        if ($value["p_harinas"]) {
                            $additionalInfo["p_harinas"] += intval($value["p_harinas"]);
                        }
                    }
                    if (array_key_exists("p_verduras", $value)) {
                        if ($value["p_verduras"]) {
                            $additionalInfo["p_verduras"] += intval($value["p_verduras"]);
                        }
                    }
                    if (array_key_exists("p_otro", $value)) {
                        if ($value["p_verduras"]) {
                            $additionalInfo["p_otro"] += intval($value["p_otro"]);
                        }
                    }
                }
            }
            foreach ($attributes['plato'] as $value) {
                if ($dish["main_id"] == $value['codigo']) {
                    if (array_key_exists("imagen", $value)) {
                        $additionalInfo["foto_plato"] = $value["imagen"];
                    }
                    if (array_key_exists("p_principal", $value)) {
                        if ($value["p_principal"]) {
                            $additionalInfo["p_principal"] += intval($value["p_principal"]);
                        }
                    }
                    if (array_key_exists("p_harinas", $value)) {
                        if ($value["p_harinas"]) {
                            $additionalInfo["p_harinas"] += intval($value["p_harinas"]);
                        }
                    }
                    if (array_key_exists("p_verduras", $value)) {
                        if ($value["p_verduras"]) {
                            $additionalInfo["p_verduras"] += intval($value["p_verduras"]);
                        }
                    }
                    if (array_key_exists("p_otro", $value)) {
                        if ($value["p_verduras"]) {
                            $additionalInfo["p_otro"] += intval($value["p_otro"]);
                        }
                    }
                }
            }
            foreach ($attributes['postre'] as $value) {
                if ($dish["dessert_id"] == $value['codigo']) {
                    if (array_key_exists("imagen", $value)) {
                        $additionalInfo["foto_postre"] = $value["imagen"];
                    }
                    if (array_key_exists("p_principal", $value)) {
                        if ($value["p_principal"]) {
                            $additionalInfo["p_principal"] += intval($value["p_principal"]);
                        }
                    }
                    if (array_key_exists("p_harinas", $value)) {
                        if ($value["p_harinas"]) {
                            $additionalInfo["p_harinas"] += intval($value["p_harinas"]);
                        }
                    }
                    if (array_key_exists("p_verduras", $value)) {
                        if ($value["p_verduras"]) {
                            $additionalInfo["p_verduras"] += intval($value["p_verduras"]);
                        }
                    }
                    if (array_key_exists("p_otro", $value)) {
                        if ($value["p_verduras"]) {
                            $additionalInfo["p_otro"] += intval($value["p_otro"]);
                        }
                    }
                }
            }
            $details['add'] = $additionalInfo;
            $delivery->details = json_encode($details);
            $delivery->save();
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

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorUpdateAddress(array $data) {
        return Validator::make($data, [
                    'delivery_id' => 'required|max:255',
                    'address_id' => 'required|max:255',
                    'merchant_id' => 'required|max:255',
                    'all' => 'required|max:255',
        ]);
    }

    public function getNextValidDate($date) {
        $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
        if ($dayofweek > 0 && $dayofweek < 5) {
            date_add($date, date_interval_create_from_date_string("1 days"));
        } else if ($dayofweek == 5) {
            date_add($date, date_interval_create_from_date_string("3 days"));
        } else if ($dayofweek == 6) {
            date_add($date, date_interval_create_from_date_string("2 days"));
        } else if ($dayofweek == 0) {
            date_add($date, date_interval_create_from_date_string("1 days"));
        }

        $isHoliday = $this->checkIsHoliday($date);

        while ($isHoliday) {
            $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
            if ($dayofweek == 5) {
                date_add($date, date_interval_create_from_date_string("3 days"));
            } else if ($dayofweek == 6) {
                date_add($date, date_interval_create_from_date_string("2 days"));
            } else {
                date_add($date, date_interval_create_from_date_string("1 days"));
            }
            $isHoliday = $this->checkIsHoliday($date);
        }
        return $date;
    }

    public function checkIsHoliday($date) {
        $holidays = [
            "2020-01-01",
            "2020-01-02",
            "2020-01-03",
            "2020-01-06",
            "2020-03-23",
            "2020-04-09",
            "2020-04-10",
            "2020-05-01",
            "2020-05-25",
            "2020-06-15",
            "2020-06-22",
            "2020-06-29",
            "2020-07-20",
            "2020-08-07",
            "2020-08-17",
            "2020-10-12",
            "2020-11-02",
            "2020-11-16",
            "2020-12-08",
            "2020-12-24",
            "2020-12-25",
            "2020-12-31",
        ];
        foreach ($holidays as $day) {
            if ($day == date_format($date, "Y-m-d")) {
                return true;
            }
        }
        return false;
    }

}
