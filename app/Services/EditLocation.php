<?php

namespace App\Services;

use Validator;
use DB;
use App\Models\User;
use App\Models\Location;
use App\Models\HistoricLocation;
use App\Models\HistoricLocation2;
use App\Models\Country;
use Hash;
use App\Models\Region;
use App\Models\City;
use App\Services\EditAlerts;
use App\Services\EditMerchant;

class EditLocation {

    const USER_LOCATION_TYPE = 'user';
    const GROUP_LOCATION_TYPE = 'group';
    const LOCATION_TYPE = 'location';
    const ACCESS_USER_OBJECT = 'userables';
    const ACCESS_USER_OBJECT_HISTORIC = 'userables_historic';
    const ACCESS_USER_OBJECT_ID = 'userable_id';
    const ACCESS_USER_OBJECT_TYPE = 'userable_type';
    const OBJECT_USER = 'user';
    const OBJECT_LOCATION = 'Location';

    protected $editAlerts;
    protected $editMerchant;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditAlerts $editAlerts, EditMerchant $editMerchant) {
        $this->editAlerts = $editAlerts;
        $this->editMerchant = $editMerchant;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserLocations(array $data) {
        $locations = DB::select('SELECT lo.* FROM locations lo join users u on u.id = lo.user_id where u.hash = ? and lo.id > ? ;', [$data['hash'], $data['after']]);
        if ($locations) {
            $result = array('locations' => $locations, "status" => "success", "message" => "normal");
            return $result;
        } else {
            $user = User::where("hash", $data['hash'])->first();
            if ($user) {
                if ($user->is_tracking == 1) {
                    $result = array('locations' => "", "status" => "waiting", "message" => "Waiting for locations from user");
                    return $result;
                } else {
                    $result = array('locations' => "", "status" => "finished", "message" => "User is no longer sharing location");
                    return $result;
                }
            }
            $result = array('locations' => "", "status" => "error", "message" => "User not found");
            return $result;
        }
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function moveOldLocations() {
        $locations = Location::whereRaw(" DATEDIFF(CURDATE(),created_at) > 1")->get()->toarray();
        if (sizeof($locations) > 0) {
            HistoricLocation::insert($locations);
            DB::delete("DELETE from locations where DATEDIFF(CURDATE(),created_at) > 1");
        }
        $locations = HistoricLocation::whereRaw(" DATEDIFF(CURDATE(),created_at) > 4")->get()->toarray();
        if (sizeof($locations) > 0) {
            HistoricLocation2::insert($locations);
            DB::delete("DELETE from historic_location where DATEDIFF(CURDATE(),created_at) > 4");
        }
        $this->editAlerts->moveOldUserFollowing();
        $this->editAlerts->moveOldReportsSharing();
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function getUserHash(User $user) {
        $hashExists = true;
        while ($hashExists) {
            $hash = str_random(40);
            $locations = DB::select("SELECT * from users where hash = ? ", [$hash]);
            if ($locations) {
                $hashExists = true;
            } else {
                $hashExists = false;
                $user->hash = $hash;
                $user->notify_location = 1;
                $user->is_tracking = 1;
                $user->save();
            }
        }
        return $hash;
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function getCitiesFrom(array $data) {
        return DB::select("SELECT id,name,country_id,region_id,ABS(lat - ? ) + ABS(`long`  - ? ) as diff "
                        . " FROM cities where name like '%{$data['name']}%' order by diff asc limit 5;", [$data['latitude'], $data['longitude']]);
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function getCitiesCloseTo(array $data) {
        return DB::select("SELECT id,name,country_id,region_id,ABS(lat - ? ) + ABS(`long`  - ? ) as diff "
                        . " FROM cities order by diff asc limit 1;", [$data['lat'], $data['long']]);
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function postLocation(array $data, User $user) {
        //

        /* $validator = $this->validatorLocation($data);
          if ($validator->fails()) {
          return $validator->getMessageBag();
          } */
        //return $data['location']['coords']['latitude'];
        $store = false;
        $saveuser = false;
        $location = $data['location'];
        unset($data['location']);
        if (array_key_exists("extras", $location)) {
            $extras = $location['extras'];
            if (array_key_exists("isLocation", $extras)) {
                return true;
            }
        }

        $data["lat"] = $location['coords']['latitude'];
        $time = strtotime($location['timestamp']);
        $data["report_time"] = date("Y-m-d H:i:s", $time);
        $data["long"] = $location['coords']['longitude'];
        $data["speed"] = $location['coords']['speed'];
        $data["accuracy"] = $location['coords']['accuracy'];
        $data["altitude"] = $location['coords']['altitude'];
        $data["heading"] = $location['coords']['heading'];
        $data["status"] = "active";
        $data["user_id"] = $user->id;
        $data["name"] = $user->name;
        $data["phone"] = "+" . $user->area_code . " " . $user->cellphone;


        $data["battery"] = $location['battery']['level'];
        $data["is_charging"] = $location['battery']['is_charging'];
        $data["is_moving"] = $location['is_moving'];
//            $data["report_time"] = str_replace("T", " ", $data["report_time"]);
//            $data["report_time"] = date_create($data["report_time"]);
        if (array_key_exists("activity", $location)) {
            $activity = $location['activity'];
            if (array_key_exists("type", $activity) && array_key_exists("confidence", $activity)) {
                $data["activity"] = $activity['type'];
                $data["confidence"] = $activity['confidence'];
            }
        }
        if ($user->notify_location == 1) {
            $user->notify_location = 0;
            $saveuser = true;
            $this->editAlerts->postNotificationLocation($user, 'location_first', '');
        }
        if ($user->is_tracking != 1 || $user->trip == 0) {
            $user = $this->editAlerts->makeUserTrip($user);
            $saveuser = true;
        }
        if ($user->write_report) {
            $user->write_report = false;
            $saveuser = true;
            $result = $this->getCitiesCloseTo($data);
            $result = $result[0];
            $sheet = [
                "user_id" => $user->id,
                "name" => "Report " . date("Y-m-d h:i:sa"),
                "private" => false,
                "type" => "event",
                "anonymous" => true,
                "lat" => $data['lat'],
                "long" => $data['long'],
                "city_id" => $result->id,
                "region_id" => $result->region_id,
                "country_id" => $result->country_id
            ];
            $this->editMerchant->saveOrCreateReport($user, $sheet);
        }
        $data["trip"] = $user->trip;
        if (array_key_exists("extras", $location)) {
            $extras = $location['extras'];
            if (array_key_exists("islast", $extras)) {
                if (array_key_exists("code", $extras)) {
                    $this->editAlerts->postNotificationLocation($user, "location_last", $extras['code']);
                } else {
                    $this->editAlerts->postNotificationLocation($user, "location_last", '');
                }

                $data["status"] = "stopped";
                $data["islast"] = true;

                $store = true;
                $user->is_tracking = 0;
                $saveuser = true;
                $user->hash = "";
                $user->trip = 0;
            }
        }


        $location = Location::create($data);
        if ($store) {
            $user->is_tracking = 0;
            $saveuser = true;
            $user->hash = "";
            $user->trip = 0;
            $locations = Location::where('user_id', '=', $user->id)
                            ->get()->toArray();
            if (sizeof($locations) > 0) {
                Location::where('user_id', '=', $user->id)
                        ->delete();
                HistoricLocation::insert($locations);
            }
            $this->editAlerts->moveUserFollowing($user);
        }
        if ($saveuser) {
            $user->save();
        }
        return ['success' => 'location saved'];
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorLocation(array $data) {
        return Validator::make($data, [
                    'lat' => 'required|max:255',
                    'long' => 'required|max:255',
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
                    'follower' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorGroup(array $data) {
        return Validator::make($data, [
                    'lat' => 'required|max:255',
                    'long' => 'required|max:255',
                    'status' => 'required|max:255',
                    'group_id' => 'required|max:255',
        ]);
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditProfileMessage() {
        return 'There was a problem editing your profile';
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditAddressMessage() {
        return 'There was a problem editing your address';
    }

}
