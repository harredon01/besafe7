<?php

namespace App\Services;

use Validator;
use App\Models\Group;
use App\Models\User;
use App\Jobs\PostEmergencyEnd;
use App\Jobs\PostEmergency;
use App\Models\Report;
use App\Models\Translation;
use App\Models\Message;
use App\Models\Notification;
use Mail;
use DB;
use PushNotification;

class EditAlerts {

    const GROUP_AVATAR = 'group_avatar';
    const GROUP_LEAVE = 'group_leave';
    const GROUP_REMOVED = 'group_removed';
    const GROUP_ACTIVE = 'group_active';
    const GROUP_PENDING = 'group_pending';
    const GROUP_EXPELLED = 'group_expelled';
    const USER_AVATAR = 'user_avatar';
    const USER_MESSAGE_TYPE = 'user_message';
    const GROUP_MESSAGE_TYPE = 'group_message';
    const GROUP_PRIVATE_MESSAGE_TYPE = 'group_private_message';
    const GROUP_ADMIN = 'group_admin';
    const GROUP_ADMIN_NEW = 'group_admin_new';
    const NEW_CONTACT = 'new_contact';
    const CONTACT_BLOCKED = 'contact_blocked';
    const NEW_GROUP = 'new_group';
    const GROUP_TYPE = 'group';
    const USER_TYPE = 'user';
    const RED_MESSAGE_TYPE = 'emergency';
    const RED_SECRET_TYPE = 'emergency_secret';
    const OBJECT_USER = 'user';
    const OBJECT_LOCATION = 'Location';
    const OBJECT_REPORT = 'Report';
    const OBJECT_Merchant = 'Merchant';
    const RED_MESSAGE_END = 'emergency_end';
    const RED_MESSAGE_MEDICAL_TYPE = 'medical_emergency';
    const NOTIFICATION_LOCATION = 'notification_location';
    const LOCATION_FIRST = 'location_first';
    const LOCATION_LAST = 'location_last';
    const TRACKING_LIMIT_FOLLOWER = 'tracking_limit_follower';
    const TRACKING_LIMIT_TRACKING = 'tracking_limit_tracking';
    const NOTIFICATION_APP = 'notification_app';
    const ACCESS_USER_OBJECT = 'userables';
    const ACCESS_USER_OBJECT_HISTORIC = 'userables_historic';
    const ACCESS_USER_OBJECT_ID = 'userable_id';
    const ACCESS_USER_OBJECT_TYPE = 'userable_type';
    const MESSAGE_AUTHOR_ID = 'user_id';
    const MESSAGE_RECIPIENT_ID = 'messageable_id';
    const MESSAGE_RECIPIENT_TYPE = 'messageable_type';
    const REQUEST_PING = "request_ping";
    const REPLY_PING = "reply_ping";

    /**
     * Gets the messages between two users.
     *
     * @return Response
     */
    public function getChat(User $user, array $data) {
        if ($data['type'] == self::GROUP_TYPE) {
            $group = Group::find($data['to_id']);
            if ($group) {
                if ($group->is_public && $group->isActive()) {
                    $recipients = DB::select("select 
                        user_id as id
                            from
                            group_user where group_id = $group->id and user_id = $user->id and is_admin = 1 and status = 'active';");
                    if (count($recipients) > 0) {
                        $messages = DB::select('select * from messages where ' . self::MESSAGE_RECIPIENT_TYPE . ' = "group_message" AND ( (' . self::MESSAGE_RECIPIENT_ID . ' = ? AND ' . self::MESSAGE_AUTHOR_ID . ' = ? ) OR ( ' . self::MESSAGE_AUTHOR_ID . ' = ? AND ' . self::MESSAGE_RECIPIENT_ID . ' = ? )) order by id desc limit 10 ', [$data['to_id'], $user->id, $data['to_id'], $user->id]);
                        return array_reverse($messages);
                    } else {
                        $messages = DB::select('select * from messages where ' . self::MESSAGE_RECIPIENT_TYPE . ' = "group_message" AND is_public = 1 AND ( (' . self::MESSAGE_RECIPIENT_ID . ' = ? AND ' . self::MESSAGE_AUTHOR_ID . ' = ? ) OR ( ' . self::MESSAGE_AUTHOR_ID . ' = ? AND ' . self::MESSAGE_RECIPIENT_ID . ' = ? )) order by id desc limit 10 ', [$data['to_id'], $user->id, $data['to_id'], $user->id]);
                        return array_reverse($messages);
                    }
                } else if (!$group->is_public) {
                    $messages = DB::select('select * from messages where ' . self::MESSAGE_RECIPIENT_TYPE . ' = "group_message" AND  ( (' . self::MESSAGE_RECIPIENT_ID . ' = ? AND ' . self::MESSAGE_AUTHOR_ID . ' = ? ) OR ( ' . self::MESSAGE_AUTHOR_ID . ' = ? AND ' . self::MESSAGE_RECIPIENT_ID . ' = ? )) order by id desc limit 10 ', [$data['to_id'], $user->id, $data['to_id'], $user->id]);
                    return array_reverse($messages);
                } else {
                    return null;
                }
            }
        } elseif ($data['type'] == self::USER_TYPE) {
            $messages = DB::select('select * from messages where ' . self::MESSAGE_RECIPIENT_TYPE . ' = "user_message" AND ( (' . self::MESSAGE_RECIPIENT_ID . ' = ? AND ' . self::MESSAGE_AUTHOR_ID . ' = ? ) OR ( ' . self::MESSAGE_AUTHOR_ID . ' = ? AND ' . self::MESSAGE_RECIPIENT_ID . ' = ? )) order by id desc limit 10 ', [$data['to_id'], $user->id, $data['to_id'], $user->id]);
            return array_reverse($messages);
        }
    }

    public function makeUserTrip(User $user) {
        if ($user->is_tracking != 1 || $user->trip == 0) {
            $exists = true;
            $number = 0;
            if ($user->is_tracking != 1) {
                $user->is_tracking = 1;
            }

            while ($exists) {
                $number = time() - 1477256930 + $user->id;
                $test = User::where("trip", $number)->first();
                if ($test) {
                    $exists = true;
                } else {
                    $exists = false;
                }
            }
            $user->trip = $number;
            $user->save();
        }
        return $user;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function addFollower(array $data, User $user) {
//        $file = '/home/hoovert/access.log';
//        // Open the file to get existing content
//        $current = file_get_contents($file);
//        //$daarray = json_decode(json_encode($data));
//        // Append a new person to the file
//
//        $current .= json_encode($data);
//        $current .= PHP_EOL;
//        $current .= PHP_EOL;
//        $current .= PHP_EOL;
//        $current .= PHP_EOL;
//        file_put_contents($file, $current);
        $followers = array();
        $validator = $this->validatorFollower($data);
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }

        if ($user->id == intval($data["follower"]) && $data["type"] == self::USER_TYPE) {
            
        } else {
            $type = $data["type"];
            $object = $data["object"];
            $object_id;
            if ($object == self::OBJECT_LOCATION) {
                $user = $this->makeUserTrip($user);

                $object_id = $user->trip;
                if ($type == self::GROUP_TYPE) {
                    $group = Group::find($data["follower"]);
                    if ($group) {
                        $res = $group->users()->where('user_id', $user->id)->get();
                        if (count($res) > 0) {
                            if ($group->is_public && $group->isActive()) {
                                $followers = DB::select("SELECT user_id as id FROM group_user WHERE group_id=? AND is_admin=1 AND status = 'active' AND user_id NOT IN (SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " where " . self::ACCESS_USER_OBJECT_ID . " = $user->id and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_LOCATION . "' and object_id = $object_id ); ", [intval($data["follower"])]);
                            } else if (!$group->is_public) {
                                $followers = DB::select("SELECT user_id as id FROM group_user WHERE group_id=? AND  status = 'active' AND group_user.user_id NOT IN (SELECT user_id FROM contacts where contact_id = $user->id AND level = '" . self::CONTACT_BLOCKED . "') AND user_id NOT IN (SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " where " . self::ACCESS_USER_OBJECT_ID . " = $user->id and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_LOCATION . "' and object_id = $object_id ); ", [intval($data["follower"])]);
                            } else {
                                return null;
                            }
                        } else {
                            return null;
                        }
                    } else {
                        return null;
                    }
                } else if ($type == self::USER_TYPE) {
                    $numbers = explode(",", $data["follower"]);
                    $bindingsString = trim(str_repeat('?,', count($numbers)), ',');
                    $sql = "SELECT id FROM users WHERE  id IN ({$bindingsString}) AND id NOT IN (SELECT user_id FROM contacts where contact_id = $user->id AND level = '" . self::CONTACT_BLOCKED . "') AND id NOT IN (SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " where " . self::ACCESS_USER_OBJECT_ID . " = $user->id and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_LOCATION . "'  and object_id = $object_id ); ";
                    $followers = DB::select($sql, $numbers);
                }
                $payload = array("trip" => $user->trip, "first_name" => $user->firstName, "last_name" => $user->lastName);
                $data = [
                    "trigger_id" => $user->id,
                    "message" => "",
                    "payload" => $payload,
                    "type" => self::NOTIFICATION_LOCATION,
                    "user_status" => $this->getUserNotifStatus($user)
                ];
                $this->sendMassMessage($data, $followers, $user, false);
            } else if ($object == self::OBJECT_REPORT || $object == self::OBJECT_MERCHANT) {
                if (array_key_exists("object_id", $data)) {
                    $classp = "App\\Models\\" . $object;
                    $objectActive = $classp::find($data['object_id']);
                    if ($objectActive) {
                        if ($objectActive->user_id == $user->id || !$objectActive->private) {
                            $object_id = $objectActive->id;
                            if ($type == self::GROUP_TYPE) {
                                $group = Group::find($data["follower"]);
                                if ($group) {
                                    $recipients = DB::select("select 
                                            user_id as id
                                                from
                                                group_user where group_id = $group->id and user_id = $user->id and is_admin = 1 and status = 'active';");
                                    if (count($recipients) > 0) {
                                        if ($group->is_public && $group->isActive()) {
                                            $followers = DB::select("SELECT user_id as id FROM group_user WHERE group_id=? AND status = 'active' AND user_id NOT IN (SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " where " . self::ACCESS_USER_OBJECT_ID . " = $user->id and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_REPORT . "' and object_id = $objectActive->id ); ", [intval($data["follower"])]);
                                        } else if (!$group->is_public) {
                                            $followers = DB::select("SELECT user_id as id FROM group_user WHERE group_id=? AND  status = 'active' AND group_user.user_id NOT IN (SELECT user_id FROM contacts where contact_id = $user->id AND level = '" . self::CONTACT_BLOCKED . "') AND user_id NOT IN (SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " where " . self::ACCESS_USER_OBJECT_ID . " = $user->id and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_REPORT . "' and object_id = $objectActive->id ); ", [intval($data["follower"])]);
                                        } else {
                                            return null;
                                        }
                                    } else {
                                        $recipients = DB::select("select 
                                            user_id as id
                                                from
                                                group_user where group_id = $group->id and user_id = $user->id and status = 'active';");
                                        if (count($recipients) > 0) {
                                            if ($group->is_public && $group->isActive()) {
                                                $followers = DB::select("SELECT user_id as id FROM group_user WHERE group_id=? AND is_admin = 1  AND status = 'active' AND user_id NOT IN (SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " where " . self::ACCESS_USER_OBJECT_ID . " = $user->id and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_REPORT . "' and object_id = $objectActive->id ); ", [intval($data["follower"])]);
                                            } else if (!$group->is_public) {
                                                $followers = DB::select("SELECT user_id as id FROM group_user WHERE group_id=? AND  status = 'active' AND group_user.user_id NOT IN (SELECT user_id FROM contacts where contact_id = $user->id AND level = '" . self::CONTACT_BLOCKED . "') AND user_id NOT IN (SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " where " . self::ACCESS_USER_OBJECT_ID . " = $user->id and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_REPORT . "' and object_id = $objectActive->id ); ", [intval($data["follower"])]);
                                            } else {
                                                return null;
                                            }
                                        }
                                    }
                                } else {
                                    return null;
                                }
                            } else if ($type == self::USER_TYPE) {
                                $numbers = explode(",", $data["follower"]);
                                $bindingsString = trim(str_repeat('?,', count($numbers)), ',');
                                $sql = "SELECT id FROM users WHERE  id IN ({$bindingsString}) AND id NOT IN (SELECT user_id FROM contacts where contact_id = $user->id AND level = '" . self::CONTACT_BLOCKED . "')  AND id NOT IN (SELECT user_id FROM "
                                        . self::ACCESS_USER_OBJECT . " where " . self::ACCESS_USER_OBJECT_ID . " = $user->id and "
                                        . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_REPORT . "'  and object_id = $objectActive->id  ); ";
                                $followers = DB::select($sql, $numbers);
                            }
                            $this->notifyObjectFollowers($user, $followers, $objectActive, $object);
                        } else {
                            return null;
                        }
                    } else {
                        return null;
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
                    $item = ['user_id' => $dafollower, 'object_id' => $object_id, self::ACCESS_USER_OBJECT_TYPE => $object, self::ACCESS_USER_OBJECT_ID => $user->id, 'created_at' => date("Y-m-d h:i:sa"), 'updated_at' => date("Y-m-d h:i:sa")];
                    array_push($dafollowers, $item);
                }
            }
            if (count($dafollowers) > 0) {
                DB::table(self::ACCESS_USER_OBJECT)->insert($dafollowers);
                return ['status' => 'success', 'message' => 'followers saved'];
            } else {
                return ['status' => 'error', 'message' => 'no followers saved'];
            }
        }
    }

    public function markAsDownloaded(User $user, array $data) {
        $numbers = explode(",", $data["read"]);
        $bindingsString = trim(str_repeat('?,', count($numbers)), ',');
        $sql = "update notifications set status='downloaded' WHERE  notification_id IN ({$bindingsString}) AND user_id = $user->id; ";
        DB::update($sql, $numbers);
        return ['success' => 'notifications updated'];
    }

    public function postNotificationLocation(User $user, $type, $code) {
        $payload = array("trip" => $user->trip, "first_name" => $user->firstName, "last_name" => $user->lastName);
        $followers = DB::select("SELECT user_id as id FROM " . self::ACCESS_USER_OBJECT . " WHERE " . self::ACCESS_USER_OBJECT_ID . "=? and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_LOCATION . "'; ", [$user->id]);
        if ($type == self::LOCATION_LAST) {
            if ($code) {
                $result = $this->checkUserCode($user, $code);
                $payload['status'] = $result['status'];
            }
        }
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "payload" => $payload,
            "type" => $type,
            "user_status" => $this->getUserNotifStatus($user)
        ];
        return $this->sendMassMessage($data, $followers, $user, true);
    }

    public function sendMassMessage(array $data, array $recipients, User $userSending, $push) {
        $arrayPushAndroid = array();
        $arrayPushIos = array();
        $arrayEmail = array();
        $arrayContent = array();
        $notification = null;
        if ($userSending->id > 0) {
            $translation = Translation::where('language', 'en-us')->where("code", $data['type'])->first();
            $arrayPayload = $data['payload'];
            $data['subject'] = str_replace("{user}", $userSending->name, $translation->value);
            $pos = strpos("e" . $data['type'], 'Report');
            if ($pos) {
                $data['subject'] = str_replace("{trigger}", $arrayPayload['object_type'], $data['subject']);
            }
            $pos = strpos("e" . $data['type'], 'group');
            if ($pos) {
                $data['subject'] = str_replace("{trigger}", $arrayPayload['group_name'], $data['subject']);
            }
            $pos = strpos($data['subject'], '{group}');
            if ($pos) {
                $data['subject'] = str_replace("{group}", $arrayPayload['group_name'], $data['subject']);
            }
        } else {
            $translation = Translation::where('language', 'en-us')->where("code", $data['type'])->first();
            $arrayPayload = $data['payload'];
            $data['subject'] = $translation->value;
        }

        $data['notification_id'] = time();
        $data['status'] = "unread";
        $checkSame = false;
        $data['payload'] = json_encode($data['payload']);
        //$daarray = json_decode(json_encode($data));
        // Append a new person to the file

        if ($userSending) {
            $checkSame = true;
        }
        if (count($recipients) > 0) {
            foreach ($recipients as $recipient) {
                $user = User::find($recipient->id);
                if ($user) {
                    if ($checkSame && $user->id == $userSending->id && $data['type'] != self::RED_SECRET_TYPE) {
                        continue;
                    }
                    $data['user_id'] = $user->id;
                    $notification = new Notification($data);
                    $notification->save();
                    $arrayContent[] = $data;
                    if ($user->emailNotifications) {
                        array_push($arrayEmail, array("name" => $user->name, "email" => $user->email));
                    }

                    if ($user->pushNotifications && $push) {

                        if ($user->platform == "android") {
                            array_push($arrayPushAndroid, $user->token);
                        }
                        if ($user->platform == "ios") {
                            array_push($arrayPushIos, $user->token);
                        }
                    }
                }
            }
            if ($notification) {
                $data['payload'] = $arrayPayload;
                $data['created_at'] = $notification->created_at;
                $data['updated_at'] = $notification->created_at;
                $data['msg'] = $data['message'];
                $data['name'] = $userSending->firstName . " " . $userSending->lastName;
                if (count($arrayPushAndroid) > 0) {
                    $this->sendMessage($data, $arrayPushAndroid, $arrayEmail, 'android');
                }
                if (count($arrayPushIos) > 0) {
                    $this->sendMessage($data, $arrayPushIos, $arrayEmail, 'ios');
                }
            }
        }
    }

    public function isBlocked($origin, $destination) {
        $followers = DB::select("select *
                    from
                        contacts where contact_id = ? and user_id = ? and level = '" . self::CONTACT_BLOCKED . "' ;", [$origin, $destination]);
        if (count($followers > 0)) {
            return true;
        }
        return false;
    }

    public function requestPing(User $user, $pingee) {
        $pingingUser = User::find($pingee);
        if (!$this->isBlocked($user->id, $pingingUser->id)) {
            $payload = array("first_name" => $user->firstName, "last_name" => $user->lastName);
            $followers = array($pingingUser);
            $data = [
                "trigger_id" => $user->id,
                "message" => "",
                "payload" => $payload,
                "type" => self::REQUEST_PING,
                "user_status" => $this->getUserNotifStatus($user)
            ];
            return $this->sendMassMessage($data, $followers, $user, true);
        }
    }

    public function replyPing(User $user, array $data) {
        if (array_key_exists("code", $data)) {
            if (array_key_exists("user_id", $data)) {
                $pingingUser = User::find($data["user_id"]);

                if ($pingingUser) {

                    $code = $data['code'];
                    $reply = $this->checkUserCode($user, $code);

                    $payload = array("first_name" => $user->firstName, "last_name" => $user->lastName, "status" => $reply['status']);
                    if ($reply['status'] == "success") {
                        $followers = array($pingingUser);
                        $data = [
                            "trigger_id" => $user->id,
                            "message" => "",
                            "payload" => $payload,
                            "type" => self::REPLY_PING,
                            "user_status" => $this->getUserNotifStatus($user)
                        ];
                        return $this->sendMassMessage($data, $followers, $user, true);
                    } else if ($reply['status'] == "info") {
                        $followers = array($pingingUser);
                        $data = [
                            "trigger_id" => $user->id,
                            "message" => "",
                            "payload" => $payload,
                            "type" => self::REPLY_PING,
                            "user_status" => $this->getUserNotifStatus($user)
                        ];
                        return $this->sendMassMessage($data, $followers, $user, true);
                    } else if ($reply['status'] == "alert") {
                        
                    }
                }
            }
        }
    }

    public function checkUserCode(User $user, $code) {
        $payload = array("first_name" => $user->firstName, "last_name" => $user->lastName);
        if ($user->green == $code) {
            $data = array('code' => $code);
            if ($user->is_alerting == 1) {
                dispatch(new PostEmergencyEnd($user, $data));
            }

            return ['status' => 'success', "message" => "Code received"];
        } else if ($user->red == $code) {
            $result = array();
            $result["type"] = self::RED_MESSAGE_TYPE;
            dispatch(new PostEmergency($user, $result, true));
            return ['status' => 'alert', "message" => "Code received"];
        } else {
            return ['status' => 'info', "message" => "Code received"];
        }
    }

    public function getUserNotifStatus(User $user) {
        if ($user->is_alerting) {
            return $user->alert_type;
        }
        if ($user->is_tracking) {
            return "tracking";
        }
        return "normal";
    }

    public function notifyFollowers(array $followers, array $tracking) {

        $stop = array();
        $counter = 0;
        $length = count($followers);
        $model = new User(['name' => 'foo', 'id' => -1]);
        if ($length > 0) {
            $activeuser = $followers[0]->user_id;
            $stop[] = [
                "user_id" => $followers[0]->user_id,
                "trip" => $followers[0]->object_id
            ];
            foreach ($followers as $follower) {
                $counter++;
                if ($activeuser == $follower->user_id) {
                    if ($counter > 1) {
                        $stop[] = [
                            "user_id" => $follower->userable_id,
                            "trip" => $follower->object_id
                        ];
                    }
                } else {
                    $notification = [
                        "trigger_id" => -1,
                        "message" => "",
                        "payload" => $stop,
                        "type" => self::TRACKING_LIMIT_FOLLOWER,
                        "user_status" => "normal"
                    ];
                    $recipients = array($follower);
                    $this->sendMassMessage($notification, $recipients, $model, false);
                    $activeuser = $follower->user_id;
                    $stop = array();
                    $stop[] = [
                        "user_id" => $follower->userable_id,
                        "trip" => $follower->object_id
                    ];
                }
                if ($counter == $length) {
                    $notification = [
                        "trigger_id" => -1,
                        "message" => "",
                        "payload" => $stop,
                        "type" => self::TRACKING_LIMIT_FOLLOWER,
                        "user_status" => "normal"
                    ];
                    $recipients = array($follower);

                    $this->sendMassMessage($notification, $recipients, $model, false);
                }
            }
        }


        $notification = [
            "trigger_id" => -1,
            "message" => "",
            "payload" => "",
            "type" => self::TRACKING_LIMIT_TRACKING,
            "user_status" => "normal"
        ];
        $this->sendMassMessage($notification, $tracking, $model, false);
        return ['success' => 'followers notified'];
    }

    public function notifyObjectFollowers(User $user, array $followers, $object, $type) {
        $daobject = array("object_id" => $object->id, "object_type" => $object->type, "first_name" => $user->firstName, "last_name" => $user->lastName
        );
        $notification = [
            "trigger_id" => $user->id,
            "message" => "",
            "type" => $type,
            "payload" => $daobject,
            "user_status" => $this->getUserNotifStatus($user)
        ];
        $this->sendMassMessage($notification, $followers, $user, true);
        return ['success' => 'followers notified'];
    }

    public function deleteGroupNotifs(User $user, $group_id) {

        DB::delete('delete from notifications where user_id = ? and trigger_id = ? and ( '
                . 'type = "' . self::GROUP_LEAVE . '" OR '
                . 'type = "' . self::GROUP_AVATAR . '" OR '
                . 'type = "' . self::GROUP_MESSAGE_TYPE . '" OR '
                . 'type = "' . self::NEW_GROUP . '" '
                . ')', [$user->id, $group_id]);
        return ['status' => 'success', "message" => 'Group deleted'];
    }

    public function deleteUserNotifs(User $user, $trigger_id) {
        DB::delete('delete from notifications where user_id = ? and trigger_id = ? and ( '
                . 'type = "' . self::USER_AVATAR . '" OR '
                . 'type = "' . self::USER_MESSAGE_TYPE . '" OR '
                . 'type = "' . self::NEW_CONTACT . '" OR '
                . 'type = "' . self::RED_MESSAGE_TYPE . '" OR '
                . 'type = "' . self::RED_MESSAGE_END . '" OR '
                . 'type = "' . self::RED_MESSAGE_MEDICAL_TYPE . '" OR '
                . 'type = "' . self::NOTIFICATION_LOCATION . '" OR '
                . 'type = "' . self::LOCATION_FIRST . '" OR '
                . 'type = "' . self::LOCATION_LAST . '" OR '
                . 'type = "' . self::TRACKING_LIMIT_FOLLOWER . '" OR '
                . 'type = "' . self::TRACKING_LIMIT_TRACKING . '" '
                . ')', [$user->id, $trigger_id]);
        return ['status' => 'success', "message" => 'Group deleted'];
    }

    public function deleteNotification(User $user, $trigger_id) {
        $notification = Notification::find($trigger_id);
        if ($notification) {
            if ($notification->user_id == $user->id) {
                $notification->delete();
                return ['status' => 'success', "message" => 'Notification deleted'];
            }
            return ['status' => 'error', "message" => 'Notification does not belong to user'];
        }
        return ['status' => 'error', "message" => 'Notification does not exist'];
    }

    public function notifyGroup(User $user, Group $group, $filename, $type) {
        if ($group->is_public && $group->isActive()) {
            
        } else if (!$group->is_public) {
            
        } else {
            return null;
        }
        $payload = array(
            "group_id" => $group->id,
            "group_name" => $group->name,
            "first_name" => $user->firstName,
            "last_name" => $user->lastName,
            "user_id" => $user->id
        );
        $push = true;
        if ($type == self::GROUP_AVATAR) {
            $payload["filename"] = $filename;
            $group->avatar = $filename;
            $group->save();
            $message = "";
            $followers = DB::select("select 
                        user_id as id
                    from
                        group_user where group_id = $group->id and user_id <> $user->id AND status = 'active' ;");
            //$push = false;
        } else if ($type == self::GROUP_LEAVE) {
            if ($filename) {
                $payload["admin_id"] = $filename;
            }
            $message = "";
            $followers = DB::select("select 
                        user_id as id
                    from
                        group_user where group_id = $group->id and user_id <> $user->id AND status = 'active';");
            //$push = false;
        } else if ($type == self::GROUP_REMOVED) {
            if (count($filename) > 0) {
                $bindingsString = trim(str_repeat('?,', count($filename)), ',');
                $sql = "SELECT user_id as id FROM group_user WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id ; ";
                $followers = DB::select($sql, $filename);
                $message = "";
                $data = [
                    "trigger_id" => $group->id,
                    "message" => $message,
                    "type" => $type,
                    "payload" => $payload,
                    "user_status" => $this->getUserNotifStatus($user)
                ];
                $this->sendMassMessage($data, $followers, $user, true);
                if ($group->isActive() && $group->is_public) {
                    $sql = "UPDATE group_user set status = 'blocked' WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id ; ";
                } else {
                    $sql = "DELETE FROM group_user WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id ; ";
                }

                DB::statement($sql, $filename);
                if ($group->is_public) {
                    $followers = array();
                } else {
                    $sql = "SELECT user_id as id FROM group_user WHERE  group_id = $group->id AND status = 'active'; ";
                    $followers = DB::select($sql, $filename);
                }
            } else {
                $followers = array();
            }
            $type = self::GROUP_EXPELLED;
            $payload["party"] = $filename;
            $message = "";
        } else if ($type == self::GROUP_ACTIVE) {
            $followers = array();
            if (count($filename) > 0) {
                if ($group->isActive() && $group->is_public) {
                    $bindingsString = trim(str_repeat('?,', count($filename)), ',');
                    $sql = "SELECT user_id as id FROM group_user WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id ; ";
                    $followers = DB::select($sql, $filename);
                    $sql = "UPDATE group_user set status = 'active' WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id; ";
                    DB::statement($sql, $filename);
                }
            }
            $message = "";
            $push = true;
        } else if ($type == self::GROUP_PENDING) {
            $followers = array();
            if ($group->isActive() && $group->is_public) {
                $sql = "SELECT user_id as id FROM group_user WHERE group_id = $group->id and is_admin = true; ";
                $followers = DB::select($sql);
                $message = "";
            }
            $message = "";
            $push = true;
        } else if ($type == self::GROUP_ADMIN) {
            if (count($filename) > 0) {
                if (!$group->is_public) {
                    $bindingsString = trim(str_repeat('?,', count($filename)), ',');
                    $sql = "SELECT user_id as id FROM group_user WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id; ";
                    $followers = DB::select($sql, $filename);
                    $message = "";
                    $data = [
                        "trigger_id" => $group->id,
                        "message" => $message,
                        "type" => $type,
                        "payload" => $payload,
                        "user_status" => $this->getUserNotifStatus($user)
                    ];
                    $this->sendMassMessage($data, $followers, $user, true);
                    $sql = "UPDATE group_user set is_admin = true WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id ; ";
                    DB::statement($sql, $filename);
                    $sql = "SELECT user_id as id FROM group_user WHERE  user_id NOT IN ({$bindingsString}) AND group_id = $group->id AND status = 'active'; ";
                    $followers = DB::select($sql, $filename);
                }
            } else {
                $followers = array();
            }
            $type = self::GROUP_ADMIN_NEW;
            $payload["party"] = $filename;
            $message = "";
        }
        $data = [
            "trigger_id" => $group->id,
            "message" => $message,
            "type" => $type,
            "payload" => $payload,
            "user_status" => $this->getUserNotifStatus($user)
        ];
        return $this->sendMassMessage($data, $followers, $user, true);
        return ['success' => 'members notified'];
    }

    public function notifyContacts(User $user, $filename) {
        $followers = DB::select("SELECT contact_id as id FROM contacts WHERE user_id= $user->id and level <> '" .
                        self::CONTACT_BLOCKED . "'   ");
        $payload = array(
            "user_id" => $user->id,
            "first_name" => $user->firstName,
            "last_name" => $user->lastName,
            "filename" => $filename);
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "type" => self::USER_AVATAR,
            "payload" => $payload,
            "user_status" => $this->getUserNotifStatus($user)
        ];
        return $this->sendMassMessage($data, $followers, $user, true);
    }

    public function notifyUser($user, $actionUser, $group, $type) {
        $followers = DB::select("SELECT user_id as id FROM group_user WHERE user_id= $actionUser->id and group_id = $group->id AND status = 'active'  ");
        $payload = array(
            "user_id" => $user->id,
            "first_name" => $user->firstName,
            "last_name" => $user->lastName);

        $data = [
            "trigger_id" => $group->id,
            "message" => "",
            "type" => $type,
            "payload" => $payload,
            "user_status" => $this->getUserNotifStatus($user)
        ];
        return $this->sendMassMessage($data, $followers, $user, false);
    }

    /**
     * Gets the messages between two users.
     *
     * @return Response
     */
    public function readNotifications(User $user, array $data, $status) {
        $sql = "UPDATE notifications SET status='$status' WHERE user_id = $user->id AND notification_id in ( ";
        $total = count($data);
        if ($total < 1) {
            $data = [
                "notifications" => "false",
                "user" => $user
            ];
            return $data;
        }
        $i = 1;
        foreach ($data as $value) {
            if ($i < $total) {
                if (intval($value["id"]) > 0) {
                    $sql .= intval($value["id"]) . ", ";
                }
            } else {
                if (intval($value["id"]) > 0) {
                    $sql .= intval($value["id"]);
                }
            }
            $i++;
        }
        $sql .= " ) ";
        $notifications = DB::statement($sql);
        $data = [
            "notifications" => $notifications,
            "user" => $user
        ];
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function countNotificationsUnread(User $user) {
        $count = Notification::where('user_id', $user->id)->where('status', "unread")->count();
        return ['status' => 'success', "message" => "notification deleted", "total" => $count];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postMessage(User $user, array $data) {

        $validator = $this->validatorMessage($data);
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }
        if ($data['type'] == self::USER_MESSAGE_TYPE) {
            $recipient = User::find(intval($data['to_id']));
            if ($recipient) {
                $followers = DB::select("select 
                        user_id 
                    from
                        contacts where contact_id = $user->id and user_id = $recipient->id and level = '" . self::CONTACT_BLOCKED . "' ;");
                if (count($followers) == 0) {
                    $confirm = [
                        "message" => $data['message'],
                        self::MESSAGE_RECIPIENT_TYPE => self::USER_MESSAGE_TYPE,
                        self::MESSAGE_AUTHOR_ID => $user->id,
                        "status" => "unread",
                        self::MESSAGE_RECIPIENT_ID => $data['to_id'],
                    ];
                    $message = Message::create($confirm);
                    $dauser = array();
                    $recipients = array($recipient);
                    $dauser['first_name'] = $user->firstName;
                    $dauser['last_name'] = $user->lastName;
                    $dauser['message_id'] = $message->id;
                    $data = [
                        "trigger_id" => $user->id,
                        "message" => $data['message'],
                        "payload" => $dauser,
                        "type" => self::USER_MESSAGE_TYPE,
                        "user_status" => $this->getUserNotifStatus($user)
                    ];
                    $confirm['id'] = $message->id;
                    $confirm['created_at'] = $message->created_at;
                    $confirm['updated_at'] = $message->updated_at;
                    $confirm['result'] = $this->sendMassMessage($data, $recipients, $user, true);
                    return $confirm;
                }
            }
        } elseif ($data['type'] == self::GROUP_MESSAGE_TYPE) {
            $group = Group::find(intval($data['to_id']));
            if ($group) {
                $res = $group->users()->where('user_id', $user->id)->where('status', 'active')->get();
                if (count($res) > 0) {
                    if ($group->is_public && $group->isActive()) {
                        $recipients = DB::select("select 
                        user_id as id
                            from
                            group_user where group_id = $group->id and user_id = $user->id and is_admin = 1 AND status = 'active';");
                        if (count($recipients) > 0) {
                            if (array_key_exists("target_id", $data)) {
                                $followers = DB::select("select 
                                user_id as id
                                    from
                                    group_user where group_id = $group->id AND status = 'active' and (is_admin = 1 or user_id = ? ) ;", [$data['target_id']]);
                            } else {
                                $followers = DB::select("select 
                                user_id as id
                                    from
                                    group_user where group_id = $group->id AND status = 'active' ;");
                            }
                        } else {
                            $followers = DB::select("select 
                        user_id as id
                            from
                            group_user where group_id = $group->id and is_admin = 1 AND status = 'active';");
                            $data['target_id'] = $user->id;
                        }
                    } else if (!$group->is_public) {
                        $followers = DB::select("select 
                        user_id as id
                            from
                            group_user where group_id = $group->id AND status = 'active' and group_user.user_id NOT IN (SELECT user_id from contacts WHERE contact_id = $user->id AND level = '" . self::CONTACT_BLOCKED . "') ;");
                    } else {
                        return null;
                    }
                    $dauser = array();
                    $dauser['first_name'] = $user->firstName;
                    $dauser['group_name'] = $group->name;
                    $dauser['last_name'] = $user->lastName;
                    $dauser['from_user'] = $user->id;

                    $dauser['public'] = $group->is_public;
                    if (array_key_exists("target_id", $data)) {
                        $dauser['target_id'] = $data['target_id'];
                        $message = Message::create([
                                    "status" => 'unread',
                                    "message" => $data['message'],
                                    self::MESSAGE_RECIPIENT_TYPE => self::GROUP_MESSAGE_TYPE,
                                    self::MESSAGE_AUTHOR_ID => $user->id,
                                    self::MESSAGE_RECIPIENT_ID => $group->id,
                                    'is_public' => $group->is_public,
                                    'target_id' => $data['target_id']
                        ]);
                    } else {
                        $message = Message::create([
                                    "status" => 'unread',
                                    "message" => $data['message'],
                                    self::MESSAGE_RECIPIENT_TYPE => self::GROUP_MESSAGE_TYPE,
                                    self::MESSAGE_AUTHOR_ID => $user->id,
                                    self::MESSAGE_RECIPIENT_ID => $group->id,
                                    'is_public' => $group->is_public,
                        ]);
                    }
                    $dauser['message_id'] = $message->id;
                    $data = [
                        "trigger_id" => $group->id,
                        "message" => $data['message'],
                        "payload" => $dauser,
                        "type" => self::GROUP_MESSAGE_TYPE,
                        "user_status" => $this->getUserNotifStatus($user)
                    ];
                    $this->sendMassMessage($data, $followers, $user, true);
                    return $message;
                }
            }
        }
    }

    public function postEmergency(User $user, array $data, $secret) {
        $user->is_alerting = 1;
        $user->alert_type = $data['type'];
        $user = $this->makeUserTrip($user);
        $user->write_report = true;
        $user->save();
        $followers = DB::select("select 
                        contact_id as id,object_id
                    from
                        contacts c
                            left join
                        userables u ON c.contact_id = u.user_id
                            and u.userable_type = '" . self::OBJECT_LOCATION . "'
                            and userable_id = $user->id where c.user_id = $user->id  and level='" . self::RED_MESSAGE_TYPE . "' "
                        . " and c.contact_id NOT IN ( SELECT user_id FROM contacts WHERE contact_id = $user->id and level = '" . self::CONTACT_BLOCKED . "');  ");
        $payload = array("first_name" => $user->firstName, "last_name" => $user->lastName);
        $followersInsert = array();
        $followersPush = array();
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "payload" => $payload,
            "type" => $data['type'],
            "user_status" => $this->getUserNotifStatus($user)
        ];
        foreach ($followers as $follower) {

            array_push($followersPush, $follower);
            if (!property_exists('follower', 'object_id')) {
                $dafollower = $follower->id;
                if ($user->id != intval($dafollower)) {
                    $item = ['user_id' => $dafollower, 'object_id' => $user->trip, self::ACCESS_USER_OBJECT_TYPE => self::OBJECT_LOCATION, self::ACCESS_USER_OBJECT_ID => $user->id, 'created_at' => date("Y-m-d h:i:sa"), 'updated_at' => date("Y-m-d h:i:sa")];
                    array_push($followersInsert, $item);
                }
            }
        }
        $this->sendMassMessage($data, $followersPush, $user, true);
        if (count($followersInsert) > 0) {
            DB::table(self::ACCESS_USER_OBJECT)->insert($followersInsert);
        }
        if ($secret) {
            $recipient = array($user);
            $data = [
                "trigger_id" => $user->id,
                "message" => "",
                "payload" => $payload,
                "type" => self::RED_SECRET_TYPE,
                "user_status" => $this->getUserNotifStatus($user)
            ];
            $this->sendMassMessage($data, $recipient, $user, true);
        }

        return ['success' => 'Message sent to all contacts'];
    }

    public function sendMessage(array $msg, array $userPush, array $userEmail, $platform) {
        //$result['notification'] = $notification;

        if (count($userPush) > 0) {
            $content = array(
                "en" => 'English Message'
            );
            if ($platform == "android") {
                $fields = array(
                    'app_id' => env('ONESIGNAL_APP_ID_ANDROID'),
                    'include_player_ids' => $userPush,
                    'data' => $msg,
                    'contents' => $content
                );
                $auth = 'Authorization: Basic ' . env('ONESIGNAL_REST_KEY_ANDROID');
            } elseif ($platform == "ios") {
                $fields = array(
                    'app_id' => env('ONESIGNAL_APP_ID_ANDROID'),
                    'include_player_ids' => $userPush,
                    'data' => $msg,
                    'contents' => $content
                );
                $auth = 'Authorization: Basic ' . env('ONESIGNAL_REST_KEY_IOS');
            }

            $fields = json_encode($fields);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                $auth));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            $response = curl_exec($ch);
            curl_close($ch);
            $result['push'] = $response;
        }
        if (count($userEmail) > 0) {
            $mail = Mail::send('emails.order', ["message" => $msg], function($message) {
                        $message->from('noreply@hoovert.com', 'Hoove');
                        $message->to($userEmail)->subject($msg['subject']);
                    });
            $result['mail'] = $mail;
        }
        return $result;
    }

    public function postStopEmergency(User $user, $code) {
        if ($user->green == $code['code']) {
            if ($user->is_alerting) {
                $user->is_alerting = 0;
                $user->alert_type = "";
                $user->hash = "";
                $payload = array("status" => "success", "trip" => $user->trip, "first_name" => $user->firstName, "last_name" => $user->lastName);
                $user->save();
            } else {
                return array("status" => "info", "message" => "User not in emergency");
            }
        } else if ($user->red == $code['code']) {
            $payload = array("status" => "alert", "first_name" => $user->firstName, "last_name" => $user->lastName);
        } else {
            $payload = array("status" => "info", "first_name" => $user->firstName, "last_name" => $user->lastName);
        }
        $followers = DB::select("SELECT contact_id as id FROM contacts WHERE user_id= $user->id and level = 'emergency' and contacts.contact_id NOT IN ( SELECT user_id FROM contacts WHERE contact_id = $user->id and level = '" . self::CONTACT_BLOCKED . "') ");
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "payload" => $payload,
            "type" => self::RED_MESSAGE_END,
            "user_status" => $this->getUserNotifStatus($user)
        ];
        $this->sendMassMessage($data, $followers, $user, true);
        if ($user->green == $code['code']) {
            return array("status" => "success", "message" => "Emergency Ended");
        } else if ($user->red == $code['code']) {
            return array("status" => "alert", "message" => "Emergency Ended");
        }
        return array("status" => "info", "message" => "Emergency Ended");
    }

    /**
     * Show the application registration form.userable_id
     *
     * @return \Illuminate\Http\Response
     */
    public function moveUserFollowing($user) {
        $following = DB::table(self::ACCESS_USER_OBJECT)->where(self::ACCESS_USER_OBJECT_ID, '=', $user->id)
                        ->where(self::ACCESS_USER_OBJECT_TYPE, '=', self::OBJECT_LOCATION)->get();
        $total = array();
        foreach ($following as $value) {
            $item = [
                'user_id' => $value->user_id,
                'object_id' => $value->object_id,
                self::ACCESS_USER_OBJECT_TYPE => self::OBJECT_LOCATION,
                self::ACCESS_USER_OBJECT_ID => $value->userable_id,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
            ];
            array_push($total, $item);
        }
        if (sizeof($following) > 0) {
            DB::table(self::ACCESS_USER_OBJECT_HISTORIC)->insert($total);
            $following = DB::table(self::ACCESS_USER_OBJECT)->where(self::ACCESS_USER_OBJECT_ID, '=', $user->id)->where(self::ACCESS_USER_OBJECT_TYPE, '=', self::OBJECT_LOCATION)->delete();
        }
    }

    public function moveOldUserFollowing() {
        $following = DB::select("SELECT user_id as id,user_id,object_id,userable_id,created_at,updated_at from " . self::ACCESS_USER_OBJECT . " WHERE DATEDIFF(CURDATE(),created_at) > 1 and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_LOCATION . "' order by user_id");
        if (sizeof($following) > 0) {
            $tracking = DB::select("SELECT userable_id as id from " . self::ACCESS_USER_OBJECT . " WHERE DATEDIFF(CURDATE(),created_at) > 1 and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_LOCATION . "'  group by " . self::ACCESS_USER_OBJECT_ID . " ");
            $this->notifyFollowers($following, $tracking);
        }
        $total = array();
        foreach ($following as $value) {
            $item = [
                'user_id' => $value->user_id,
                'object_id' => $value->object_id,
                self::ACCESS_USER_OBJECT_TYPE => self::OBJECT_LOCATION,
                self::ACCESS_USER_OBJECT_ID => $value->userable_id,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
            ];
            array_push($total, $item);
        }
        if (sizeof($following) > 0) {
            DB::table(self::ACCESS_USER_OBJECT_HISTORIC)->insert($total);
            $following = DB::table(self::ACCESS_USER_OBJECT)->whereRaw(" DATEDIFF(CURDATE(),created_at) > 1")->where(self::ACCESS_USER_OBJECT_TYPE, '=', self::OBJECT_LOCATION)->delete();
        }
    }

    public function moveOldReportsSharing() {
        $following = DB::select("SELECT * from " . self::ACCESS_USER_OBJECT . " WHERE DATEDIFF(CURDATE(),created_at) > 5 and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_REPORT . "'");
        $total = array();
        foreach ($following as $value) {
            $item = [
                'user_id' => $value->user_id,
                'object_id' => $value->object_id,
                self::ACCESS_USER_OBJECT_TYPE => self::OBJECT_REPORT,
                self::ACCESS_USER_OBJECT_ID => $value->userable_id,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
            ];
            array_push($total, $item);
        }
        if (sizeof($following) > 0) {
            DB::table(self::ACCESS_USER_OBJECT_HISTORIC)->insert($total);
            $following = DB::table(self::ACCESS_USER_OBJECT)->whereRaw(" DATEDIFF(CURDATE(),created_at) > 5")->where(self::ACCESS_USER_OBJECT_TYPE, '=', self::OBJECT_REPORT)->delete();
        }
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorMessage(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'message' => 'required|max:255',
        ]);
    }

    public function sendNotification(array $data, array $userPush, array $userEmail, $platform) {

        if (count($userEmail) > 0) {
            $mail = Mail::send('emails.order', ["message" => $data], function($message) {
                        $message->from('noreply@hoovert.com', 'Hoove');
                        $message->to($userEmail)->subject($data['subject']);
                    });
            $result['mail'] = $mail;
        }
        if (count($userPush) > 0) {
            $deviceCollection = array();
            foreach ($userPush as $value) {
                $deviceCollection[] = PushNotification::Device($value);
            }
            $devices = PushNotification::DeviceCollection($deviceCollection);
            $message = PushNotification::Message($data['subject'], array(
                        'a' => $data
            ));
            if ($platform == "android") {
                PushNotification::app('appNameAndroid')
                        ->to($devices)
                        ->send($message);
            } elseif ($platform == "ios") {
                PushNotification::app('appNameIOS')
                        ->to($devices)
                        ->send($message);
            }
        }
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorGetMessage(array $data) {
        return Validator::make($data, [
                    'recipient_id' => 'required|max:255',
                    'type' => 'required|max:255',
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
