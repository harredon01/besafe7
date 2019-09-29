<?php

namespace App\Services;

use App\Models\Stop;
use App\Models\Route;
use App\Models\User;
use App\Models\Delivery;
use App\Models\Location;

class Runner {

    public function stopComplete($data) {
        $stop = Stop::where("code", $data["point"])->with("deliveries")->first();
        if ($stop) {
            foreach ($stop->deliveries as $delivery) {
                $delivery->status = "completed";
                $delivery->save();
            }
            $stop->status = "completed";
            $stop->save();
        }
    }

    public function stopFailed($data) {
        $className = "App\\Services\\Notifications";
        $platFormService = new $className();
        $user = User::find($data["user_id"]);
        Delivery::where("user_id", $user->id)->where("status", "pending")->update(['status' => 'suspended']);
        $payload = [];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => "Lonchis",
            "sign" => true,
            "payload" => $payload,
            "type" => "food_meal_suspended",
            "user_status" => "normal"
        ];
        $date = date("Y-m-d H:i:s");
        $platFormService->sendMassMessage($data, [$user], null, true, $date, true);
        $followers = User::whereIn("id", [2, 77])->get();
        $payload = [];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "El usuario " . $user->firstName . " " . $user->lastName . " " . $user->id . " No devolvio sus cocas",
            "object" => "Lonchis",
            "sign" => true,
            "payload" => $payload,
            "type" => "admin",
            "user_status" => "normal"
        ];
        $date = date("Y-m-d H:i:s");
        $platFormService->sendMassMessage($data, $followers, null, true, $date, true);
    }

    public function stopArrived($info) {
        $stop = Stop::where("id", $info["stop_id"])->with("deliveries.user")->first();
        if ($stop) {
            $stop->status = "arrived";
            $stop->save();
            $followers = [];
            foreach ($stop->deliveries as $delivery) {
                array_push($followers, $delivery->user);
            }
            $payload = [];
            $data = [
                "trigger_id" => 1,
                "message" => "",
                "subject" => "",
                "object" => "Lonchis",
                "sign" => true,
                "payload" => $payload,
                "type" => "food_meal_arriving",
                "user_status" => "normal"
            ];
            $className = "App\\Services\\Notifications";
            $notifications = new $className;
            $date = date("Y-m-d H:i:s");
            $notifications->sendMassMessage($data, $followers, null, true, $date, true);
        }

        return true;
    }

    public function routeCompleted($data, $user) {
        $route = Route::where("id", $data["route_id"])->first();
        if ($route) {
            $route->status = "complete";
            $route->save();
            $user->is_tracking = 0;
            $user->hash = "";
            $user->trip = 0;
            $location = Location::where("user_id", $user->id)->orderBy('id', 'desc')->first();
            $location->status = "stopped";
            $location->islast = true;
            $location->save();
            $user->save();
            $className = "App\\Services\\EditLocation";
            $editLocation = new $className;
            $editLocation->saveEndTrip($user);
            $followers = User::whereIn("id", [2, 77])->get();
            $payload = [];
            $className = "App\\Services\\Notifications";
            $platFormService = new $className;
            $data = [
                "trigger_id" => $user->id,
                "message" => "",
                "subject" => "El usuario " . $user->firstName . " " . $user->lastName . " " . $user->id . " No devolvio sus envases",
                "object" => "Lonchis",
                "sign" => true,
                "payload" => $payload,
                "type" => "admin",
                "user_status" => "normal"
            ];
            $date = date("Y-m-d H:i:s");
            $platFormService->sendMassMessage($data, $followers, null, true, $date, true);
        } else {
            return $data;
        }
    }

    public function routeStarted($data) {
        $route = Route::where("id", $data["route_id"])->first();
        if ($route) {
            $route->status = "transit";
            $route->save();
            return $route;
        } else {
            return $data;
        }
    }

}
