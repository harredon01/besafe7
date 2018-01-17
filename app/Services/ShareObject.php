<?php

namespace App\Services;

use App\Services\EditAlerts;
use App\Models\Group;
use App\Models\User;
use DB;
use Validator;

class ShareObject {

    const GROUP_TYPE = 'group';
    const USER_TYPE = 'user';
    const OBJECT_GROUP = 'Group';
    const OBJECT_USER = 'User';
    const OBJECT_LOCATION = 'Location';
    const ACCESS_USER_OBJECT = 'userables';
    const ACCESS_USER_OBJECT_HISTORIC = 'userables_historic';
    const ACCESS_USER_OBJECT_ID = 'userable_id';
    const ACCESS_USER_OBJECT_TYPE = 'userable_type';
    const NOTIFICATION_LOCATION = 'notification_location';

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
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function addFollower(array $data, User $user) {
        $file = '/home/hoovert/access.log';
        // Open the file to get existing content
        $current = file_get_contents($file);
        //$daarray = json_decode(json_encode($data));
        // Append a new person to the file

        $current .= json_encode($data);
        $current .= PHP_EOL;
        $current .= PHP_EOL;
        $current .= PHP_EOL;
        $current .= PHP_EOL;
        file_put_contents($file, $current);
        $followers = array();
        $validator = $this->validatorFollower($data);
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }

        if ($user->id == intval($data["follower"]) && $data["type"] == self::USER_TYPE) {
            
        } else {
            $triggerName = "";
            $object = $data["object"];
            $object_id;
            if ($object == self::OBJECT_LOCATION) {
                $user->makeTrip();
                $object_id = $user->trip;
                $type = $data["type"];
                if ($type == self::GROUP_TYPE) {
                    $group = Group::find($data["follower"]);
                    $triggerName = self::OBJECT_GROUP;
                    if ($group) {
                        $followers = $group->getRecipientsObject($user, $object, $object_id);
                    }
                } else if ($type == self::USER_TYPE) {
                    $triggerName = self::OBJECT_USER;
                    $followers = $user->getRecipientsObject($data["follower"], $object, $object_id);
                }

                $payload = array("trip" => $user->trip, "first_name" => $user->firstName, "last_name" => $user->lastName);
                $data = [
                    "trigger_id" => $user->id,
                    "message" => "",
                    "payload" => $payload,
                    "object" => $triggerName,
                    "sign" => true,
                    "type" => self::NOTIFICATION_LOCATION,
                    "user_status" => $user->getUserNotifStatus()
                ];
                $this->editAlerts->sendMassMessage($data, $followers, $user, true);
            } else {
                if (array_key_exists("object_id", $data)) {
                    $classp = "App\\Models\\" . $object;
                    if (class_exists($classp)) {
                        $objectActive = $classp::find($data['object_id']);
                        if ($objectActive) {
                            if ($objectActive->user_id == $user->id || !$objectActive->private) {
                                $object_id = $objectActive->id;
                                $type = $data["type"];
                                if ($type == self::GROUP_TYPE) {
                                    $triggerName = self::OBJECT_GROUP;
                                    $group = Group::find($data["follower"]);
                                    if ($group) {
                                        $followers = $group->getRecipientsObject($user, $object, $object_id);
                                    }
                                } else if ($type == self::USER_TYPE) {
                                    $triggerName = self::OBJECT_USER;
                                    $followers = $user->getRecipientsObject($data["follower"], $object, $object_id);
                                }
                                $this->notifyObjectFollowers($user, $followers, $objectActive, $object,$triggerName);
                            } else {
                                return null;
                            }
                        } else {
                            return null;
                        }
                    }
                } else {
                    return null;
                }
            }
            $dafollowers = array();
            foreach ($followers as $follower) {
                $dafollower = $follower->id;
                if ($user->id == intval($dafollower)) {
                    
                } else {
                    $item = ['user_id' => $dafollower, 
                        'object_id' => $object_id, 
                        self::ACCESS_USER_OBJECT_TYPE => $object, 
                        self::ACCESS_USER_OBJECT_ID => $user->id, 
                        'created_at' => date("Y-m-d h:i:sa"), 
                        'updated_at' => date("Y-m-d h:i:sa")];
                    array_push($dafollowers, $item);
                }
            }
            if (count($dafollowers) > 0) {
                DB::table(self::ACCESS_USER_OBJECT)->insert($dafollowers);
                if ($object == self::OBJECT_LOCATION) {
                    $user->updateFollowersDate();
                }
                return ['status' => 'success', 'message' => 'followers saved'];
            } else {
                return ['status' => 'error', 'message' => 'no followers saved'];
            }
        }
    }

    /**
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function getObjectHash(User $user, $id, $type) {
        $type = "App\\Models\\" . $type;
        if (class_exists($type)) {
            $object = $type::find($id);
            if ($object) {
                if ($object->user_id == $user->id || !$object->private) {
                    if ($object->hash) {
                        return ['status' => 'success', "hash" => $object->hash];
                    }
                    $hashExists = true;
                    while ($hashExists) {
                        $hash = str_random(40);
                        $objects = $type::where("hash", $hash)->first();
                        if ($objects) {
                            $hashExists = true;
                        } else {
                            $hashExists = false;
                            $object->hash = $hash;
                            $object->save();
                            return ['status' => 'success', "hash" => $hash];
                        }
                    }
                }
                return ['status' => 'error', "message" => "report does not belong to user"];
            }
            return ['status' => 'error', "message" => "report id invalid"];
        }
        return ['status' => 'error', "message" => "type not supported"];
    }

    public function notifyObjectFollowers(User $user, array $followers, $object, $type, $triggerName) {
        $daobject = array(
            "object_id" => $object->id, 
            "object_type" => $object->type, 
            "object_name" => $object->name, 
            "first_name" => $user->firstName, 
            "last_name" => $user->lastName
        );
        $notification = [
            "trigger_id" => $user->id,
            "message" => "",
            "type" => $type,
            "object" =>$triggerName,
            "sign" => true,
            "payload" => $daobject,
            "user_status" => $user->getUserNotifStatus()
        ];
        $this->editAlerts->sendMassMessage($notification, $followers, $user, true);
        return ['success' => 'followers notified'];
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

}
