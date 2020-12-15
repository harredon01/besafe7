<?php

namespace App\Services;

use Validator;
use App\Models\Group;
use App\Models\User;
use App\Models\Message;
use DB;

class EditMessages {

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
    const OBJECT_GROUP = 'Group';
    const OBJECT_LOCATION = 'Location';
    const OBJECT_REPORT = 'Report';
    const OBJECT_MERCHANT = 'Merchant';
    const RED_MESSAGE_END = 'emergency_end';
    const RED_MESSAGE_MEDICAL_TYPE = 'medical_emergency';
    const NOTIFICATION_LOCATION = 'notification_location';
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
    public function __construct() {
        $this->notifications = app('Notifications');
    }

    /**
     * Gets the messages between two users.
     *
     * @return Response
     */
    public function getChat(User $user, array $data) {
        if ($data['type'] == self::GROUP_TYPE) {
            $group = Group::find($data['to_id']);
            if ($group) {
                if ($group->isPublicActive()) {
                    $recipients = DB::select("select 
                        user_id as id
                            from
                            group_user where group_id = $group->id and user_id = $user->id and is_admin = 1 and level = 'active';");
                    if (count($recipients) > 0) {
                        $messages = DB::select('select * from messages '
                                        . 'where ' . self::MESSAGE_RECIPIENT_TYPE . ' = "group_message" '
                                        . 'AND ( '
                                        . '(' . self::MESSAGE_RECIPIENT_ID . ' = ? AND ' . self::MESSAGE_AUTHOR_ID . ' = ? ) '
                                        . 'OR ( ' . self::MESSAGE_AUTHOR_ID . ' = ? AND ' . self::MESSAGE_RECIPIENT_ID . ' = ? )'
                                        . ') order by id desc limit 10 ', [$data['to_id'], $user->id, $data['to_id'], $user->id]);
                        return array_reverse($messages);
                    } else {
                        $messages = DB::select('select * from messages '
                                        . 'where ' . self::MESSAGE_RECIPIENT_TYPE . ' = "group_message" '
                                        . 'AND is_public = 1 '
                                        . 'AND ( '
                                        . '(' . self::MESSAGE_RECIPIENT_ID . ' = ? AND ' . self::MESSAGE_AUTHOR_ID . ' = ? ) '
                                        . 'OR ( ' . self::MESSAGE_AUTHOR_ID . ' = ? AND ' . self::MESSAGE_RECIPIENT_ID . ' = ? )'
                                        . ') order by id desc limit 10 ', [$data['to_id'], $user->id, $data['to_id'], $user->id]);
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

    /**
     * Gets the messages between two users.
     *
     * @return Response
     */
    public function getReceivedChats(User $user) {
//        $messages = DB::select('select u.firstName,u.lastName,u.id as user_id,(select * from messages where ' . self::MESSAGE_RECIPIENT_TYPE . ' = "user_message" AND ' 
//                . self::MESSAGE_RECIPIENT_ID . ' = ?) sorted_list  group by m.' . self::MESSAGE_AUTHOR_ID . ' order by m.id desc ', [ $user->id]);
//        $messages = DB::select('select u.firstName,u.lastName,u.id as user_id,m.id,m.created_at,m.message from messages m join users u on m.' . self::MESSAGE_AUTHOR_ID . '=u.id where ' . self::MESSAGE_RECIPIENT_TYPE . ' = "user_message" AND ' 
//                . self::MESSAGE_RECIPIENT_ID . ' = ? group by m.' . self::MESSAGE_AUTHOR_ID . ' order by m.id desc ', [ $user->id]);
        $messages = DB::select('select u.firstName,u.lastName,u.id as user_id,m.id,m.created_at,m.message from messages m join users u on m.' . self::MESSAGE_AUTHOR_ID . '=u.id where ' . self::MESSAGE_RECIPIENT_TYPE . ' = "user_message" AND '
                        . self::MESSAGE_RECIPIENT_ID . ' = ? order by m.id desc ', [$user->id]);
        return $messages;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postMessage(User $user, array $data) {

        $validator = $this->validatorMessage($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        if ($data['type'] == self::USER_MESSAGE_TYPE) {
            $followers = $user->countRecipientsMessage($data['to_id']);
            $confirm = [
                "message" => $data['message'],
                self::MESSAGE_RECIPIENT_TYPE => self::USER_MESSAGE_TYPE,
                self::MESSAGE_AUTHOR_ID => $user->id,
                "status" => "unread",
                self::MESSAGE_RECIPIENT_ID => $data['to_id'],
            ];
            $message = Message::create($confirm);
            $dauser = array();
            $dauser['first_name'] = $user->firstName;
            $dauser['last_name'] = $user->lastName;
            $dauser['message_id'] = $message->id;
            $data = [
                "to_id" => $data['to_id'],
                "trigger_id" => $user->id,
                "message" => $data['message'],
                "payload" => $dauser,
                "type" => self::USER_MESSAGE_TYPE,
                "object" => self::OBJECT_USER,
                "sign" => true,
                "user_status" => $user->getUserNotifStatus()
            ];
            //dispatch(new SendChat($data, $user, true));
            $this->sendChat($data, $user, true);
            return $message;
        } elseif ($data['type'] == self::GROUP_MESSAGE_TYPE) {
            $group = Group::find(intval($data['to_id']));
            if ($group) {
                $fetched = $group->countRecipientsMessage($user, $data);
                if ($fetched > 0) {
                    $dauser = array();
                    $dauser['first_name'] = $user->firstName;
                    $dauser['group_name'] = $group->name;
                    $dauser['last_name'] = $user->lastName;
                    $dauser['from_user'] = $user->id;
                    $dauser['public'] = $group->is_public;
                    if ($group->is_public) {
                        
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
                        "to_id" => $data['to_id'],
                        "trigger_id" => $group->id,
                        "message" => $data['message'],
                        "payload" => $dauser,
                        "type" => self::GROUP_MESSAGE_TYPE,
                        "object" => self::OBJECT_GROUP,
                        "sign" => true,
                        "user_status" => $user->getUserNotifStatus()
                    ];
                    //dispatch(new SendChat($data, $user, true));
                    $this->sendChat($data, $user, true);
                    return $message;
                }
            }
        }
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function sendChat(array $data, User $user, bool $notif) {
        if ($data['type'] == self::USER_MESSAGE_TYPE) {
            $followers = $user->getRecipientsMessage($data['to_id']);
            $sendEmail = false;
            if (count($followers) > 0) {
                
            } else {
                $followers = [];
                if($data['to_id']==env('ADMIN')){
                    $sendEmail = true;
                }
                $recipient = User::find($data['to_id']);
                array_push($followers, $recipient);
            }
            unset($data['to_id']);
            $this->notifications->sendMassMessage($data, $followers, $user, $notif, null, $sendEmail);
        } elseif ($data['type'] == self::GROUP_MESSAGE_TYPE) {
            $group = Group::find(intval($data['to_id']));
            if ($group) {
                $fetched = $group->getRecipientsMessage($user, $data);
                $followers = $fetched['followers'];
                $data = $fetched['data'];
                if (count($followers) > 0) {
                    unset($data['to_id']);
                    $dauser = $data['payload'];
                    $dauser['is_admin'] = $data['is_admin'];
                    unset($data['is_admin']);
                    $data['payload'] = $dauser;
                    $this->notifications->sendMassMessage($data, $followers, $user, $notif, null);
                }
            }
        }
    }

    public function getSupportAgent($type, $object) {
        if ($type == "platform") {
            if ($object == "food") {
                $user = User::find(77);
                $friend = [
                    "id" => $user->id,
                    "name" => $user->firstName . " " . $user->lastName
                ];
                return $friend;
            } else if ($object == "petworld") {
                $user = User::find(2);
                $friend = [
                    "id" => $user->id,
                    "name" => $user->firstName . " " . $user->lastName
                ];
                return $friend;
            }
        } else if ($type == "Merchant" ) {
            $className = "App\\Models\\".$type;
            $objectActv = $className::find($object);
            if ($objectActv) {
                $user = $objectActv->users()->first();
                $friend = [
                    "id" => $user->id,
                    "name" => $user->firstName . " " . $user->lastName
                ];
                return $friend;
            }
        } else if ($type == "Report") {
            $className = "App\\Models\\".$type;
            $objectActv = $className::find($object);
            if ($objectActv) {
                $user = $objectActv->user;
                $friend = [
                    "id" => $user->id,
                    "name" => $user->firstName . " " . $user->lastName
                ];
                return $friend;
            }
        } else if ($type == "Group") {
            $className = "App\\Models\\".$type;
            $objectActv = $className::find($object);
            if ($objectActv) {
                $admins = $objectActv->getAllAdminMembers();
                $winner = rand(0,(count($admins)-1));
                $user = User::find($admins[$winner]->id);
                $friend = [
                    "id" => $user->id,
                    "name" => $user->firstName . " " . $user->lastName
                ];
                return $friend;
            }
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

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorGetMessage(array $data) {
        return Validator::make($data, [
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
