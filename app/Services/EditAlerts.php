<?php

namespace App\Services;

use Validator;
use App\Models\Group;
use App\Models\User;
use App\Jobs\PostEmergencyEnd;
use App\Jobs\PostEmergency;
use App\Models\Translation;
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
    const NEW_GROUP = 'new_group';
    const GROUP_TYPE = 'group';
    const USER_TYPE = 'user';
    const RED_MESSAGE_TYPE = 'emergency';
    const RED_SECRET_TYPE = 'emergency_secret';
    const OBJECT_USER = 'User';
    const OBJECT_LOCATION = 'Location';
    const OBJECT_REPORT = 'Report';
    const OBJECT_MERCHANT = 'Merchant';
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
    const CONTACT_BLOCKED = 'contact_blocked';

    public function markAsDownloaded(User $user, array $data) {
        $numbers = explode(",", $data["read"]);
        $bindingsString = trim(str_repeat('?,', count($numbers)), ',');
        $sql = "update notifications set status='downloaded' WHERE  notification_id IN ({$bindingsString}) AND user_id = $user->id; ";
        DB::update($sql, $numbers);
        return ['success' => 'notifications updated'];
    }

    public function sendMassMessage(array $data, array $recipients, $userSending, $push, $date, $platform = NULL) {
        $arrayPushHife = array();
        $arrayPushFood = array();
        $arrayEmail = array();
        $arrayContent = array();
        $notification = null;
        $sign = $data['sign'];
        unset($data['sign']);
        if ($userSending) {
            $translation = Translation::where('language', 'en-us')->where("code", $data['type'])->first();
            $translationEsp = Translation::where('language', 'es-co')->where("code", $data['type'])->first();
            $arrayPayload = $data['payload'];
            if ($translationEsp) {
                $data['subject_es'] = str_replace("{user}", $userSending->name, $translationEsp->value);
            }
            if ($translation) {
                $data['subject'] = str_replace("{user}", $userSending->name, $translation->value);
            }
        } else {
            $translation = Translation::where('language', 'en-us')->where("code", $data['type'])->first();
            $translationesp = Translation::where('language', 'es-co')->where("code", $data['type'])->first();
            $arrayPayload = $data['payload'];
            if ($translation) {
                $data['subject'] = $translation->value;
            }
            if ($translationesp) {
                $data['subject_es'] = $translationesp->value;
            }
        }
        $pos = strpos("e" . $data['type'], 'Report');
        if ($pos) {
            $data['subject'] = str_replace("{trigger}", $arrayPayload['object_type'] . " " . $arrayPayload['object_name'], $data['subject']);
            $data['subject_es'] = str_replace("{trigger}", $arrayPayload['object_type'] . " " . $arrayPayload['object_name'], $data['subject_es']);
        }
        $pos = strpos("e" . $data['type'], 'Merchant');
        if ($pos) {
            $data['subject'] = str_replace("{trigger}", $arrayPayload['object_type'] . " " . $arrayPayload['object_name'], $data['subject']);
            $data['subject_es'] = str_replace("{trigger}", $arrayPayload['object_type'] . " " . $arrayPayload['object_name'], $data['subject_es']);
        }
        $pos = strpos($data['subject'], '{group}');
        if ($pos) {
            $data['subject'] = str_replace("{group}", $arrayPayload['group_name'], $data['subject']);
            $data['subject_es'] = str_replace("{group}", $arrayPayload['group_name'], $data['subject_es']);
        }
        if ($data) {
            $data['notification_id'] = strtotime($date);
        } else {
            $data['notification_id'] = strtotime(date("Y-m-d H:i:s"));
        }

        $data['status'] = "unread";
        $data['payload'] = json_encode($data['payload']);
        //$daarray = json_decode(json_encode($data));
        // Append a new person to the file

        if (count($recipients) > 0) {
            foreach ($recipients as $recipient) {
                $user = User::find($recipient->id);
                if ($user) {
                    if ($userSending) {
                        if ($user->id == $userSending->id && $data['type'] != self::RED_SECRET_TYPE) {
                            continue;
                        }
                    }
                    $data['user_id'] = $user->id;
                    $notification = new Notification($data);
                    $notification->save();
                    $arrayContent[] = $data;
                    if ($user->emailNotifications) {
                        array_push($arrayEmail, array("name" => $user->name, "email" => $user->email));
                    }

                    if ($user->pushNotifications && $push) {
                        if ($platform) {
                            
                        } else {
                            $platform = "hife";
                        }
                        $result = $user->push()->where('platform', $platform)->first();
                        if ($result) {
                            if($result->platform == "hife"){
                                array_push($arrayPushHife, $result->object_id);
                            } else if($result->platform == "food"){
                                array_push($arrayPushFood, $result->object_id);
                            } 
                        }
                    }
                }
            }
            if ($notification) {
                $data['payload'] = $arrayPayload;
                $data['created_at'] = $notification->created_at;
                $data['updated_at'] = $notification->created_at;
                $notification->updated_at = date('Y-m-d H:i:s', $data['notification_id']);
                $data['msg'] = $data['message'];
                if ($userSending) {
                    $data['name'] = $userSending->firstName . " " . $userSending->lastName;
                } else {
                    $data['name'] = "Gohife";
                }
                if (count($arrayPushHife) > 0) {
                    $this->sendMessage($data, $arrayPushHife, $arrayEmail, 'hife');
                }
                if (count($arrayPushFood) > 0) {
                    $this->sendMessage($data, $arrayPushFood, $arrayEmail, 'food');
                }
            }
        }
        return $notification;
    }

    public function requestPing(User $user, $pingee) {
        $pingingUser = User::find($pingee);
        if (!$user->isBlocked($pingingUser->id)) {
            $date = null;
            $date = $user->updateContactDate($pingingUser->id);
            $payload = array("first_name" => $user->firstName, "last_name" => $user->lastName);
            $followers = array($pingingUser);
            $data = [
                "trigger_id" => $user->id,
                "message" => "",
                "object" => self::OBJECT_USER,
                "sign" => true,
                "payload" => $payload,
                "type" => self::REQUEST_PING,
                "user_status" => $user->getUserNotifStatus()
            ];
            return $this->sendMassMessage($data, $followers, $user, true, $date);
        }
    }

    public function replyPing(User $user, array $data) {
        if (array_key_exists("code", $data)) {
            if (array_key_exists("user_id", $data)) {
                $pingingUser = User::find($data["user_id"]);

                if ($pingingUser) {

                    $code = $data['code'];
                    $reply = $this->checkUserCode($user, $code);
                    $date = null;
                    $date = $user->updateContactDate($pingingUser->id);

                    $payload = array("first_name" => $user->firstName, "last_name" => $user->lastName, "status" => $reply['status']);
                    if ($reply['status'] == "success") {
                        $followers = array($pingingUser);
                        $data = [
                            "trigger_id" => $user->id,
                            "message" => "",
                            "payload" => $payload,
                            "object" => self::OBJECT_USER,
                            "sign" => true,
                            "type" => self::REPLY_PING,
                            "user_status" => $user->getUserNotifStatus()
                        ];
                        return $this->sendMassMessage($data, $followers, $user, true, $date);
                    } else if ($reply['status'] == "info") {
                        $followers = array($pingingUser);
                        $data = [
                            "trigger_id" => $user->id,
                            "message" => "",
                            "object" => self::OBJECT_USER,
                            "sign" => true,
                            "payload" => $payload,
                            "type" => self::REPLY_PING,
                            "user_status" => $user->getUserNotifStatus()
                        ];
                        return $this->sendMassMessage($data, $followers, $user, true, $date);
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
            if ($user->is_alerting) {
                //$this->postStopEmergency($user, $data);
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

    public function deleteObjectNotifs(User $user, $trigger_id, $object) {
        DB::delete('delete from notifications where user_id = ? and trigger_id = ? and object="?" ', [$user->id, $trigger_id, $object]);
        return ['status' => 'success', "message" => $object . ' ' . $trigger_id . ' notifs deleted'];
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

    public function notifyContacts(User $user, $filename) {
        $followers = $user->getNonBlockedContacts();

        $payload = array(
            "user_id" => $user->id,
            "first_name" => $user->firstName,
            "last_name" => $user->lastName,
            "filename" => $filename);
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "type" => self::USER_AVATAR,
            "object" => self::OBJECT_USER,
            "sign" => true,
            "payload" => $payload,
            "user_status" => $user->getUserNotifStatus()
        ];
        $date = $user->updateAllContactsDate(null);
        $notification = $this->sendMassMessage($data, $followers, $user, true, $date);
        return $notification;
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

    public function postEmergency(User $user, array $data, $secret) {
        $user->is_alerting = 1;
        $user->alert_type = $data['type'];
        $user->makeTrip();
        $user->write_report = true;
        $user->save();
        $followers = $user->getEmergencyAndCurrentFollowerContacts();
        $followersInsert = array();
        $followersPush = array();
        $payload = array("first_name" => $user->firstName, "last_name" => $user->lastName);
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "payload" => $payload,
            "object" => self::OBJECT_USER,
            "sign" => true,
            "type" => $data['type'],
            "user_status" => $user->getUserNotifStatus()
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
        if (count($followersInsert) > 0) {
            DB::table(self::ACCESS_USER_OBJECT)->insert($followersInsert);
        }
        $date = $user->updateFollowersDate($data['type']);
        $notification = $this->sendMassMessage($data, $followersPush, $user, true, $date);
        if ($secret) {
            $recipient = array($user);
            $data = [
                "trigger_id" => $user->id,
                "message" => "",
                "payload" => $payload,
                "object" => self::OBJECT_USER,
                "sign" => true,
                "type" => self::RED_SECRET_TYPE,
                "user_status" => $user->getUserNotifStatus()
            ];
            $this->sendMassMessage($data, $recipient, $user, true, null);
        }

        return ['success' => 'Message sent to all contacts'];
    }

    public function postGroupEmergency(User $user, array $data) {
        $group = Group::find($data['group_id']);
        if ($group) {
            $profile = $group->checkMemberType($user);
            if ($profile) {
                if ($profile->level != self::CONTACT_BLOCKED) {
                    $followersInsert = array();
                    $followersPush = array();
                    $payload = array("first_name" => $user->firstName, "last_name" => $user->lastName);
                    $data = [
                        "trigger_id" => $user->id,
                        "message" => $data['message'],
                        "payload" => $payload,
                        "object" => self::OBJECT_USER,
                        "sign" => true,
                        "type" => $data['type'],
                        "user_status" => $user->getUserNotifStatus()
                    ];
                    $user->is_alerting = 1;
                    $user->alert_type = $data['type'];
                    $user->makeTrip();
                    $user->write_report = true;
                    $user->save();
                    $date = null;
                    if ($profile->is_admin == 1) {
                        $followers = $group->getAllMembersNonUserBlockedButActive($user);
                        foreach ($followers as $follower) {
                            array_push($followersPush, $follower);
                        }
                        $group->status = $data['type'];
                        $group->save();
                        $date = $group->updated_at;
                    } else {
                        $followers = $group->getAllAdminMembersNonUserBlockedButActive($user);
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
                        $date = $group->updateDateAllAdminMembersNonUserBlockedButActive($user);
                        if (count($followersInsert) > 0) {
                            DB::table(self::ACCESS_USER_OBJECT)->insert($followersInsert);
                        }
                    }
                    $this->sendMassMessage($data, $followersPush, $user, true, $date);
                }
            }
        }
        return ['success' => 'Message sent to all contacts'];
    }

    public function sendMessage(array $msg, array $userPush, array $userEmail, $platform) {
        //$result['notification'] = $notification;

        if (count($userPush) > 0) {
            $content = array(
                "en" => $msg['subject'],
                "es" => $msg['subject_es']
            );
            if ($platform == "hife") {
                $fields = array(
                    'app_id' => env('ONESIGNAL_APP_ID_HIFE'),
                    'include_player_ids' => $userPush,
                    'data' => $msg,
                    'contents' => $content
                );
                $auth = 'Authorization: Basic ' . env('ONESIGNAL_REST_KEY_HIFE');
            } elseif ($platform == "food") {
                $fields = array(
                    'app_id' => env('ONESIGNAL_APP_ID_FOOD'),
                    'include_player_ids' => $userPush,
                    'data' => $msg,
                    'contents' => $content
                );
                $auth = 'Authorization: Basic ' . env('ONESIGNAL_REST_KEY_FOOD');
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
            $file = '/home/hoovert/access.log';
            // Open the file to get existing content
            $current = file_get_contents($file);
            //$daarray = json_decode(json_encode($data));
            // Append a new person to the file

            $current .= json_encode($result);
            $current .= PHP_EOL;
            $current .= PHP_EOL;
            $current .= PHP_EOL;
            $current .= PHP_EOL;
            file_put_contents($file, $current);
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
        $followers = $user->getEmergencyContacts();

        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "payload" => $payload,
            "type" => self::RED_MESSAGE_END,
            "object" => self::OBJECT_USER,
            "sign" => true,
            "user_status" => $user->getUserNotifStatus()
        ];
        $date = $user->updateAllEmergencyContactsDate("normal");
        $this->sendMassMessage($data, $followers, $user, true, $date);
        if ($user->green == $code['code']) {
            return array("status" => "success", "message" => "Emergency Ended");
        } else if ($user->red == $code['code']) {
            return array("status" => "alert", "message" => "Emergency Ended");
        }
        return array("status" => "info", "message" => "Emergency Ended");
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
