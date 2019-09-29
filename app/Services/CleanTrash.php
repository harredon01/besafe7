<?php

namespace App\Services;

use App\Models\User;
use App\Models\Location;
use App\Models\HistoricLocation;
use App\Models\HistoricLocation2; 
use DB;
use App\Services\Notifications;

class CleanTrash {

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
    const OBJECT_USER = 'user';
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

    /**
     * The EditAlert implementation.
     *
     */
    protected $notifications;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(Notifications $notifications) {
        $this->notifications = $notifications;
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

    public function notifyFollowers(array $followers, array $tracking,$date) {

        $stop = array();
        $contacts = array();
        $counter = 0;
        $length = count($followers);
        $model = new User(['name' => 'foo', 'id' => -1]);
        if ($length > 0) {
            $activeuser = $followers[0]->user_id;
            $stop[] = [
                "user_id" => $followers[0]->user_id,
                "trip" => $followers[0]->object_id
            ];
            $contacts = [$follower->userable_id];
            foreach ($followers as $follower) {
                $counter++;
                if ($activeuser == $follower->user_id) {
                    if ($counter > 1) {
                        $stop[] = [
                            "user_id" => $follower->userable_id,
                            "trip" => $follower->object_id
                        ];
                        $contacts = [$follower->userable_id];
                    }
                } else {
                    $notification = [
                        "trigger_id" => -1,
                        "message" => "",
                        "payload" => $stop,
                        "object" => "System",
                        "sign" => true,
                        "type" => self::TRACKING_LIMIT_FOLLOWER,
                        "user_status" => "normal"
                    ];
                    $recipients = array($follower);
                    DB::table('contacts')
                        ->where('contacts.contact_id',  $activeuser)
                        ->whereIn('contacts.user_id', $contacts)
                        ->update(array("last_significant" => $date));
                    $this->notifications->sendMassMessage($notification, $recipients, $model, false,$date);
                    $activeuser = $follower->user_id;
                    $stop = array();
                    $contacts = array();
                    $stop[] = [
                        "user_id" => $follower->userable_id,
                        "trip" => $follower->object_id
                    ];
                    $contacts = [$follower->userable_id];
                }
                if ($counter == $length) {
                    $notification = [
                        "trigger_id" => -1,
                        "message" => "",
                        "payload" => $stop,
                        "object" => "System",
                        "sign" => true,
                        "type" => self::TRACKING_LIMIT_FOLLOWER,
                        "user_status" => "normal"
                    ];
                    $recipients = array($follower);
                    DB::table('contacts')
                        ->where('contacts.contact_id',  $activeuser)
                        ->whereIn('contacts.user_id', $contacts)
                        ->update(array("last_significant" => $date));

                    $this->notifications->sendMassMessage($notification, $recipients, $model, false,$date);
                }
            }
        }


        $notification = [
            "trigger_id" => -1,
            "message" => "",
            "payload" => "",
            "object" => "System",
            "sign" => true,
            "type" => self::TRACKING_LIMIT_TRACKING,
            "user_status" => "normal"
        ];
        $this->notifications->sendMassMessage($notification, $tracking, $model, false,null);
        return ['success' => 'followers notified'];
    }

    public function moveOldUserFollowing() {
        $date = date("Y-m-d");
        $following = DB::select("SELECT user_id as id,user_id,object_id,userable_id,created_at,updated_at from " . self::ACCESS_USER_OBJECT . " WHERE DATEDIFF(CURDATE(),created_at) > 1 and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_LOCATION . "' order by user_id");
        if (sizeof($following) > 0) {
            $tracking = DB::select("SELECT userable_id as id from " . self::ACCESS_USER_OBJECT . " WHERE DATEDIFF(CURDATE(),created_at) > 1 and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_LOCATION . "'  group by " . self::ACCESS_USER_OBJECT_ID . " ");
            $this->notifyFollowers($following, $tracking,$date);
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
     * returns all current shared locations for the user
     *
     * @return Location
     */
    public function moveOld() {
        $this->moveOldLocations();
        $this->moveOldUserFollowing();
        $this->moveOldReportsSharing();
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
    }

}
