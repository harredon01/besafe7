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
use App\Services\EditMapObject;
use App\Services\CleanTrash;

class EditLocation {

    const LOCATION_TYPE = 'location';
    const LOCATION_FIRST = 'location_first';
    const LOCATION_LAST = 'location_last';
    const ACCESS_USER_OBJECT = 'userables';
    const ACCESS_USER_OBJECT_HISTORIC = 'userables_historic';
    const ACCESS_USER_OBJECT_ID = 'userable_id';
    const ACCESS_USER_OBJECT_TYPE = 'userable_type';
    const OBJECT_GROUP = 'Group';
    const OBJECT_USER = 'User';
    const OBJECT_LOCATION = 'Location';
    const OBJECT_REPORT = 'Report';

    protected $editAlerts;
    protected $editMapObject;
    protected $cleanTrash;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditAlerts $editAlerts, EditMapObject $editMapObject, CleanTrash $cleanTrash) {
        $this->editAlerts = $editAlerts;
        $this->editMapObject = $editMapObject;
        $this->cleanTrash = $cleanTrash;
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
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function saveEndTrip(User $user) {

        $locations = Location::where('user_id', '=', $user->id)
                        ->get()->toArray();
        if (sizeof($locations) > 0) {
            HistoricLocation::insert($locations);
            Location::where('user_id', '=', $user->id)
                    ->delete();
        }
        $this->cleanTrash->moveUserFollowing($user);
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function writeReport(User $user, array $data) {
        $result = $this->getCitiesCloseTo($data);
        $result = $result[0];
        $sheet = [
            "user_id" => $user->id,
            "name" => "Report " . date("Y-m-d h:i:sa"),
            "private" => true,
            "type" => "event",
            "anonymous" => true,
            "lat" => $data['lat'],
            "long" => $data['long'],
            "city_id" => $result->id,
            "region_id" => $result->region_id,
            "country_id" => $result->country_id,
            "group_id" => null,
            "id" => null
        ];
        $this->editMapObject->saveOrCreateObject($user, $sheet, self::OBJECT_REPORT);
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function parseLocation($location, User $user) {
        $data = [];
        $data["lat"] = $location['coords']['latitude'];
        if (array_key_exists('uuid', $location)) {
            $data['uuid'] = $location['uuid'];
        }
        $time = strtotime($location['timestamp']);
        $data["report_time"] = date("Y-m-d H:i:s", $time);
        $data["long"] = $location['coords']['longitude'];
        $data["speed"] = $location['coords']['speed'];
        $data["accuracy"] = $location['coords']['accuracy'];
        $data["altitude"] = $location['coords']['altitude'];
        $data["heading"] = $location['coords']['heading'];
        $data["status"] = "active";
        $data["battery"] = $location['battery']['level'];
        $data["is_charging"] = $location['battery']['is_charging'];
        $data["is_moving"] = $location['is_moving'];
        if (array_key_exists("activity", $location)) {
            $activity = $location['activity'];
            if (array_key_exists("type", $activity) && array_key_exists("confidence", $activity)) {
                $data["activity"] = $activity['type'];
                $data["confidence"] = $activity['confidence'];
            }
        }
        $data["user_id"] = $user->id;
        $data["name"] = $user->name;
        $data["phone"] = "+" . $user->area_code . " " . $user->cellphone;
        return $data;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function postLocation(array $data2, User $user) {

//                $file = '/home/hoovert/access.log';
//        // Open the file to get existing content
//        $current = file_get_contents($file);
//        //$daarray = json_decode(json_encode($data));
//        // Append a new person to the file
//
//        $current .= json_encode($data2);
//        $current .= PHP_EOL;
//        $current .= PHP_EOL;
//        $current .= PHP_EOL;
//        $current .= PHP_EOL;
//        file_put_contents($file, $current);
        $storeTripCall = false;
        $saveuser = false;
        $extras = null;
        $location = $data2['location'];
        if (array_key_exists("extras", $location)) {
            $extras = $location['extras'];
            if (array_key_exists("trackingStatus", $extras)) {
                if ($extras['trackingStatus'] == "active" || $extras['trackingStatus'] == "finishing") {
                    
                } else {
                    return ['error' => 'User not tracking'];
                }
            }
        }
        $data = $this->parseLocation($location, $user);
        if (array_key_exists('uuid', $data)) {
            $results = Location::where('uuid', $data['uuid'])->where('user_id', $user->id)->first();

            if ($results) {
                return ['error' => 'location exists'];
            }
            $results = HistoricLocation::where('uuid', $data['uuid'])->where('user_id', $user->id)->first();
            if ($results) {
                return ['error' => 'location exists'];
            }
        }
        unset($data['location']);
        if ($user->is_tracking != 1 || $user->trip == 0) {
            if ($extras['trackingStatus'] == "finishing") {
                return ['error' => 'Trip ended'];
            }
            $user->makeTrip();
            $saveuser = true;
        }
        $data["trip"] = $user->trip;
        $dalocation = Location::create($data);
        if ($extras) {
            if ($extras['trackingStatus'] == "finishing") {
                if (array_key_exists("code", $extras)) {
                    $payload = array("trip" => $user->trip, "first_name" => $user->firstName, "last_name" => $user->lastName);
                    $code = $extras['code'];

                    if ($code) {
                        $result = $this->editAlerts->checkUserCode($user, $code);
                        if ($result['status'] == "success") {
                            $followers = $user->getCurrentFollowers();
                            $payload = array("trip" => $user->trip, "first_name" => $user->firstName, "last_name" => $user->lastName, "status" => "success");
                            $message = [
                                "trigger_id" => $user->id,
                                "message" => "",
                                "payload" => $payload,
                                "type" => self::LOCATION_LAST,
                                "object" => self::OBJECT_USER,
                                "sign" => true,
                                "user_status" => "normal"
                            ];
                            $user->is_tracking = 0;
                            $user->hash = "";
                            $user->trip = 0;
                            $saveuser = true;
                            $date = $user->updateFollowersDate("normal");
                            $this->editAlerts->sendMassMessage($message, $followers, $user, true, $date);
                            $dalocation->status = "stopped";
                            $dalocation->islast = true;
                            $dalocation->save();
                            $storeTripCall = true;
                        }
                    }
                }
            }
        }

        if ($user->write_report) {
            $saveuser = true;
            $user->write_report = false;
            $this->writeReport($user, $data);
        }
        if ($saveuser) {
            $user->save();
        }
        if ($storeTripCall) {
            $this->saveEndTrip($user);
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
