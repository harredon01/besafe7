<?php

namespace App\Services;

use Validator;
use App\Models\User;
use App\Models\Translation;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\GeneralNotification;
use DB;
use PushNotification;

class Notifications {

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

    public function buildMessage($userSending, $data) {
        if ($data['type'] == "admin") {
            $data['subject_es'] = $data['subject'];
            $data['body_es'] = $data['subject'];
            return $data;
        }
        $translation = Translation::where('language', 'en-us')->where("code", $data['type'])->first();
        $translationEsp = Translation::where('language', 'es-co')->where("code", $data['type'])->first();
        if ($data['type'] == "program_reminder2") {
            $data['type'] = "program_reminder";
        }
        $arrayPayload = $data['payload'];
        if ($userSending) {
            if ($translationEsp) {
                $data['subject_es'] = str_replace("{user}", $userSending->name, $translationEsp->value);
                $data['body_es'] = str_replace("{user}", $userSending->name, $translationEsp->body);
            }
            if ($translation) {
                $data['subject'] = str_replace("{user}", $userSending->name, $translation->value);
                $data['body'] = str_replace("{user}", $userSending->name, $translation->body);
            }
        } else {
            if ($translation) {
                $data['subject'] = $translation->value;
                $data['body'] = $translation->body;
            }
            if ($translationEsp) {
                $data['subject_es'] = $translationEsp->value;
                $data['body_es'] = $translationEsp->body;
            }
        }
        $pos = strpos("e" . $data['type'], 'Report');
        if ($pos) {
            $data['subject'] = str_replace("{trigger}", $arrayPayload['object_type'] . " " . $arrayPayload['object_name'], $data['subject']);
            $data['subject_es'] = str_replace("{trigger}", $arrayPayload['object_type'] . " " . $arrayPayload['object_name'], $data['subject_es']);
            $data['body'] = str_replace("{trigger}", $arrayPayload['object_type'] . " " . $arrayPayload['object_name'], $data['body']);
            $data['body_es'] = str_replace("{trigger}", $arrayPayload['object_type'] . " " . $arrayPayload['object_name'], $data['body_es']);
        }
        $pos = strpos("e" . $data['type'], 'Merchant');
        if ($pos) {
            $data['subject'] = str_replace("{trigger}", $arrayPayload['object_type'] . " " . $arrayPayload['object_name'], $data['subject']);
            $data['subject_es'] = str_replace("{trigger}", $arrayPayload['object_type'] . " " . $arrayPayload['object_name'], $data['subject_es']);
            $data['body'] = str_replace("{trigger}", $arrayPayload['object_type'] . " " . $arrayPayload['object_name'], $data['body']);
            $data['body_es'] = str_replace("{trigger}", $arrayPayload['object_type'] . " " . $arrayPayload['object_name'], $data['body_es']);
        }
        $pos = strpos($data['subject'], '{group}');
        if ($pos) {
            $data['subject'] = str_replace("{group}", $arrayPayload['group_name'], $data['subject']);
            $data['subject_es'] = str_replace("{group}", $arrayPayload['group_name'], $data['subject_es']);
            $data['body'] = str_replace("{group}", $arrayPayload['group_name'], $data['body']);
            $data['body_es'] = str_replace("{group}", $arrayPayload['group_name'], $data['body_es']);
        }
        $pos = strpos($data['subject'], '{order}');
        if ($pos) {
            $data['subject'] = str_replace("{order}", $arrayPayload['order_id'], $data['subject']);
            $data['subject_es'] = str_replace("{order}", $arrayPayload['order_id'], $data['subject_es']);
            $data['body'] = str_replace("{order}", $arrayPayload['order_id'], $data['body']);
            $data['body_es'] = str_replace("{order}", $arrayPayload['order_id'], $data['body_es']);
        }
        $pos = strpos($data['subject'], '{orderTotal}');
        if ($pos) {
            $data['subject'] = str_replace("{orderTotal}", $arrayPayload['order_total'], $data['subject']);
            $data['subject_es'] = str_replace("{orderTotal}", $arrayPayload['order_total'], $data['subject_es']);
            $data['body'] = str_replace("{orderTotal}", $arrayPayload['order_total'], $data['body']);
            $data['body_es'] = str_replace("{orderTotal}", $arrayPayload['order_total'], $data['body_es']);
        }
        $pos = strpos($data['subject'], '{orderStatus}');
        if ($pos) {
            $data['subject'] = str_replace("{orderStatus}", $arrayPayload['order_status'], $data['subject']);
            $data['body'] = str_replace("{orderStatus}", $arrayPayload['order_status'], $data['body']);
            $orderStatus = $this->translateStatus($arrayPayload['order_status'], "a");
            $data['subject_es'] = str_replace("{orderStatus}", $orderStatus, $data['subject_es']);
            $data['body_es'] = str_replace("{orderStatus}", $orderStatus, $data['body_es']);
        }
        $pos = strpos($data['subject'], '{payment}');
        if ($pos) {
            $data['subject'] = str_replace("{payment}", $arrayPayload['payment_id'], $data['subject']);
            $data['subject_es'] = str_replace("{payment}", $arrayPayload['payment_id'], $data['subject_es']);
            $data['body'] = str_replace("{payment}", $arrayPayload['payment_id'], $data['body']);
            $data['body_es'] = str_replace("{payment}", $arrayPayload['payment_id'], $data['body_es']);
        }
        $pos = strpos($data['subject'], '{paymentStatus}');
        if ($pos) {
            $data['subject'] = str_replace("{paymentStatus}", $arrayPayload['payment_status'], $data['subject']);
            $data['body'] = str_replace("{paymentStatus}", $arrayPayload['payment_status'], $data['body']);
            $paymentStatus = $this->translateStatus($arrayPayload['payment_status'], "o");
            $data['subject_es'] = str_replace("{paymentStatus}", $paymentStatus, $data['subject_es']);
            $data['body_es'] = str_replace("{paymentStatus}", $paymentStatus, $data['body_es']);
        }
        $pos = strpos($data['subject'], '{paymentTotal}');
        if ($pos) {
            $data['subject'] = str_replace("{paymentTotal}", $arrayPayload['payment_total'], $data['subject']);
            $data['subject_es'] = str_replace("{paymentTotal}", $arrayPayload['payment_total'], $data['subject_es']);
            $data['body'] = str_replace("{paymentTotal}", $arrayPayload['payment_total'], $data['body']);
            $data['body_es'] = str_replace("{paymentTotal}", $arrayPayload['payment_total'], $data['body_es']);
        }
        $pos = strpos($data['subject'], '{scenario}');
        if ($pos) {
            $data['subject'] = str_replace("{scenario}", $arrayPayload['scenario'], $data['subject']);
            $data['subject_es'] = str_replace("{scenario}", $arrayPayload['scenario'], $data['subject_es']);
            $data['body'] = str_replace("{scenario}", $arrayPayload['scenario'], $data['body']);
            $data['body_es'] = str_replace("{scenario}", $arrayPayload['scenario'], $data['body_es']);
        }
        $pos = strpos($data['subject'], '{route}');
        if ($pos) {
            $data['subject'] = str_replace("{route}", $arrayPayload['route'], $data['subject']);
            $data['subject_es'] = str_replace("{route}", $arrayPayload['route'], $data['subject_es']);
            $data['body'] = str_replace("{route}", $arrayPayload['route'], $data['body']);
            $data['body_es'] = str_replace("{route}", $arrayPayload['route'], $data['body_es']);
        }
        $pos = strpos($data['subject'], '{date}');
        if ($pos) { 
            $data['subject'] = str_replace("{date}", $arrayPayload['date'], $data['subject']);
            $data['subject_es'] = str_replace("{date}", $arrayPayload['date'], $data['subject_es']);
            $data['body'] = str_replace("{date}", $arrayPayload['date'], $data['body']);
            $data['body_es'] = str_replace("{date}", $arrayPayload['date'], $data['body_es']);
        } 
        $pos = strpos($data['subject'], '{bookclient}');
        if ($pos) {
            $data['subject'] = str_replace("{bookclient}", $arrayPayload['bookclient'], $data['subject']);
            $data['subject_es'] = str_replace("{bookclient}", $arrayPayload['bookclient'], $data['subject_es']);
            $data['body'] = str_replace("{bookclient}", $arrayPayload['bookclient'], $data['body']);
            $data['body_es'] = str_replace("{bookclient}", $arrayPayload['bookclient'], $data['body_es']);
        }
        $pos = strpos($data['subject'], '{bookable}');
        if ($pos) {
            $data['subject'] = str_replace("{bookable}", $arrayPayload['bookable'], $data['subject']);
            $data['subject_es'] = str_replace("{bookable}", $arrayPayload['bookable'], $data['subject_es']);
            $data['body'] = str_replace("{bookable}", $arrayPayload['bookable'], $data['body']);
            $data['body_es'] = str_replace("{bookable}", $arrayPayload['bookable'], $data['body_es']);
        }
        $pos = strpos($data['body'], '{url}');
        if ($pos) {
            $data['body'] = str_replace("{url}", $arrayPayload['url'], $data['body']);
            $data['body_es'] = str_replace("{url}", $arrayPayload['url'], $data['body_es']);
        }
        return $data;
    }

    public function translateStatus($status, $male) {
        if ($status == "approved") {
            return "aprobad" . $male;
        }
        if ($status == "pending") {
            return "esperando aprobacion";
        }
        if ($status == "denied") {
            return "rechazad" . $male;
        }
        if ($status == "payment_in_bank") {
            return "pago por consignación";
        }
        return $status;
    }

    public function sendMassMessage(array $data, array $recipients, $userSending, $push, $date, $sendEmail = true) {
        $arrayPushHife = array();
        $arrayPushFood = array();
        $arrayEmail = array();
        $arrayContent = array();
        $notification = null;
        $arrayPayload = $data['payload'];
        $sign = $data['sign'];
        unset($data['sign']);
        $data = $this->buildMessage($userSending, $data);
        if ($date) {
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
                    if(array_key_exists('body', $data)){
                        $body = $data['body'];
                    }else {
                        $body = $data['subject'];
                    }
                    if(array_key_exists('body_es', $data)){
                        $bodyEs = $data['body_es'];
                    }else {
                        $bodyEs = $data['subject_es'];
                    }
                    unset($data['body']);
                    unset($data['body_es']);
                    $notification = new Notification($data);
                    $notification->save();
                    $data['body'] = $body;
                    $data['body_es'] = $bodyEs;
                    $arrayContent[] = $data;
                    if ($user->emailNotifications && $sendEmail) {
                        array_push($arrayEmail, array("name" => $user->name, "email" => $user->email));
                    }

                    if ($user->pushNotifications && $push) {
                        $platform = "food";
                        $result = $user->push()->where('platform', $platform)->first();
                        if ($result) {
                            if ($result->platform == "hife") {
                                array_push($arrayPushHife, $result->object_id);
                            } else if ($result->platform == "food") {
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
     * Gets the messages between two users.
     *
     * @return Response
     */
    public function readAllNotifications(User $user) {

        $sql = "UPDATE notifications SET status='read' WHERE user_id = $user->id;";
        $notifications = DB::statement($sql);
        $data = [
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
        return ['status' => 'success', "message" => "notifications unread", "total" => $count];
    }

    public function sendMessage(array $msg, array $userPush, array $userEmail, $platform) {
        //$result['notification'] = $notification;

        if (count($userPush) > 0) {
            if ($platform == "food") {
                $msg['subject'] = $msg['subject_es'];
            }
            $content = array(
                "en" => $msg['subject'],
                "es" => $msg['subject_es']
            );
            $fields = array(
                'app_id' => env('ONESIGNAL_APP_ID_FOOD'),
                'include_player_ids' => $userPush,
                'data' => $msg,
                'contents' => $content 
            );
            $auth = 'Authorization: Basic ' . env('ONESIGNAL_REST_KEY_FOOD');

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
            $mail = Mail::to($userEmail)->send(new GeneralNotification($msg['subject_es'], $msg['body_es']));
            $result['mail'] = $mail;
        }
        return $result;
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
