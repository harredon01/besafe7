<?php

namespace App\Services;

use Validator;
use App\Models\Group;
use App\Jobs\InviteUsers;
use App\Jobs\NotifyGroup;
use App\Jobs\AdminGroup;
use App\Models\User;
use Illuminate\Http\Response;
use App\Services\Notifications;
use DB;

class EditGroup {

    const GROUP_PENDING = 'group_pending';
    const GROUP_EXPELLED = 'group_expelled';
    const GROUP_BLOCKED = 'group_blocked';
    const GROUP_REMOVED = 'group_removed';
    const GROUP_AVATAR = 'group_avatar';
    const GROUP_LEAVE = 'group_leave';
    const NEW_GROUP = 'new_group';
    const GROUP_ACTIVE = 'group_active';
    const GROUP_INVITE = 'group_invite';
    const GROUP_EXPIRED = 'group_expired';
    const OBJECT_GROUP = 'Group';
    const GROUP_ADMIN = 'group_admin';
    const GROUP_ADMIN_NEW = 'group_admin_new';
    const CONTACT_BLOCKED = 'contact_blocked';

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

    public function getGroup($group_id) {
        $group = Group::find($group_id);
        $group->users;
        return $group;
    }

    public function updateExpiredGroups() {
        $groups = Group::where('is_public', true)->where('status', 'active')->whereRaw(" CURDATE() > ends_at")->get();
        foreach ($groups as $group) {
            $group->status = 'suspended';
            $payload = array(
                "group_id" => $group->id,
                "group_name" => $group->name,
            );
            $followers = $group->getAllMembers();
            $group->save();
            $data = [
                "trigger_id" => $group->id,
                "message" => "",
                "type" => self::GROUP_EXPIRED,
                "object" => self::OBJECT_GROUP,
                "sign" => true,
                "payload" => $payload,
                "user_status" => "useless"
            ];
            $this->notifications->sendMassMessage($data, $followers, null, true, $group->updated_at);
        }
    }

    public function getActiveAdminGroups(User $user) {
        return DB::select('select g.* from groups g join group_user gu on g.id = gu.group_id'
                        . ' where gu.user_id  = ? and gu.is_admin = 1 and g.ends_at > CURDATE() AND gu.level <> "' . self::GROUP_BLOCKED . '"  && level <> "' . self::GROUP_PENDING . '";', [$user->id]);
    }

    public function checkAdminGroup($userId, $groupId) {
        return $results = DB::select(' select * from group_user where group_id = ? and user_id = ? and is_admin = 1 AND level = "active"', [$groupId, $userId]);
    }

    public function getGroupCode(User $user, $group_id) {
        $data = [];
        $group = Group::find($group_id);
        if ($group) {
            $users = $this->checkAdminGroup($user->id, $group_id);
            if (count($users) == 1) {
                $data['code'] = $group->code;
                return $data;
            }
            return ['status' => 'error', "message" => 'User not admin'];
        }
        return ['status' => 'error', "message" => 'Group doesnt exist'];
    }

    public function regenerateGroupCode(User $user, $group_id) {

        $data = [];
        $group = Group::find($group_id);
        if ($group) {
            $users = $this->checkAdminGroup($user->id, $group_id);
            if (count($users) == 1) {
                $exists = true;
                $string = "";
                while ($exists) {
                    $string = str_random(20);
                    $test = Group::where("code", $string)->first();
                    if ($test) {
                        $exists = true;
                    } else {
                        $exists = false;
                    }
                }
                $group->code = $string;
                $data['code'] = $string;
                $group->save();
                return $data;
            }
            return ['status' => 'error', "message" => 'User not admin'];
        }
        return ['status' => 'error', "message" => 'Group doesnt exist'];
    }

    public function deleteGroupUser(User $user, Group $group) {
        $profile = $group->checkMemberType($user);
        $deleteGroup = false;
        if ($profile) {
            if ($profile->level != self::GROUP_BLOCKED) {
                $this->notifications->deleteObjectNotifs($user, $group->id, "Group");
                if (!$group->is_public) {
                    $deleted = DB::delete('delete from group_user where user_id = ? and group_id = ? ', [$user->id, $group->id]);
                    if ($profile->is_admin) {
                        $users2 = DB::select('select * from group_user where user_id  <> ? and group_id = ? and is_admin = 1 AND level = "active" limit 1', [$user->id, $group->id]);
                        if (count($users2) == 0) {
                            $usersf = DB::select('select user_id from group_user where user_id  <> ? and group_id = ? limit 1', [$user->id, $group->id]);
                            if (count($usersf) > 0) {
                                $canditate = $usersf[0];
                                $data = [
                                    "user_id" => $canditate->user_id,
                                    "group_id" => $group->id
                                ];
                                $this->setAdminGroup($data);
//                            $this->notifyGroup($user, $group, $canditate->user_id, self::GROUP_LEAVE);
                                dispatch(new NotifyGroup($user, $group, $canditate->user_id, self::GROUP_LEAVE));
                            } else {
                                $deleteGroup = true;
                            }
                        } else {
                            $this->notifyGroup($user, $group, null, self::GROUP_LEAVE);
                        }
                    } else {
                        $this->notifyGroup($user, $group, null, self::GROUP_LEAVE);
                    }

                    if ($deleteGroup) {
                        $group->delete();
                    }
                } else if ($group->isPublicActive()) {
                    if ($profile->is_admin) {
                        return null;
                    }
                    $deleted = DB::delete('delete from group_user where user_id = ? and group_id = ? ', [$user->id, $group->id]);
                }
            }
        }
    }

    public function notifyGroup(User $user, Group $group, $filename, $type) {
        if ($group->isPublicActive()) {
            
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
        $followers = [];
        $date = null;
        if ($type == self::GROUP_AVATAR) {
            $payload["filename"] = $filename;
            $group->avatar = $filename;
            $group->save();
            $message = "";
            $followers = $group->getAllMembersButActive($user);
            //$push = false;
        } else if ($type == self::GROUP_LEAVE) {
            if ($filename) {
                $payload["admin_id"] = $filename;
            }
            $message = "";

            if (!$group->is_public) {
                $followers = $group->getAllMembersButActive($user);
                $group->save();
            }
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
                    "object" => self::OBJECT_GROUP,
                    "sign" => true,
                    "payload" => $payload,
                    "user_status" => $user->getUserNotifStatus()
                ];
                $date = date("Y-m-d H:i:s");
                $this->notifications->sendMassMessage($data, $followers, $user, true, $date);
                if ($group->isPublicActive()) {
                    $sql = "UPDATE group_user set level = '" . self::GROUP_REMOVED . "' WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id ; ";
                } else if (!$group->is_public) {
                    $sql = "DELETE FROM group_user WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id ; ";
                } else {
                    return null;
                }

                DB::statement($sql, $filename);
                if (!$group->is_public) {
                    $followers = $group->getAllMembersButActive($user);
                    $date = $group->updateAllMembersButActiveDate($user);
                    $group->save();
                } else {
                    $followers = array();
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
                if ($group->isPublicActive()) {
                    $bindingsString = trim(str_repeat('?,', count($filename)), ',');
                    $sql = "SELECT user_id as id FROM group_user WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id ; ";
                    $followers = DB::select($sql, $filename);
                    $date = date("Y-m-d H:i:s");
                    $sql = "UPDATE group_user set level = 'active',last_significant = '{$date}' WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id; ";
                    DB::statement($sql, $filename);
                }
            }
            $message = "";
            $push = true;
        } else if ($type == self::GROUP_PENDING) {
            $followers = array();
            if ($group->isPublicActive()) {
                $followers = $group->getAllAdminMembers();
                $message = "";
                $date = $group->updateAllAdminMembersDate();
            }
            $message = "";
            $push = true;
        } else if ($type == self::GROUP_ADMIN) {
            if (count($filename) > 0) {
                if (!$group->is_public) {
                    $date = date("Y-m-d H:i:s");
                    $bindingsString = trim(str_repeat('?,', count($filename)), ',');
                    $sql = "SELECT user_id as id FROM group_user WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id; ";
                    $followers = DB::select($sql, $filename);
                    $message = "";
                    $data = [
                        "trigger_id" => $group->id,
                        "message" => $message,
                        "type" => $type,
                        "object" => self::OBJECT_GROUP,
                        "sign" => true,
                        "payload" => $payload,
                        "user_status" => $user->getUserNotifStatus()
                    ];
                    $this->notifications->sendMassMessage($data, $followers, $user, true, $date);
                    $sql = "UPDATE group_user set is_admin = true,last_significant = '{$date}' WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id ; ";
                    DB::statement($sql, $filename);
                    $sql = "SELECT user_id as id FROM group_user WHERE  user_id NOT IN ({$bindingsString}) AND group_id = $group->id AND level <> '" . self::GROUP_BLOCKED . "' && level <> '" . self::GROUP_PENDING . "'; ";
                    $followers = DB::select($sql, $filename);
                    $group->save();
                } else if ($group->isPublicActive()) {
                    $date = date("Y-m-d H:i:s");
                    $sql = "UPDATE group_user set is_admin = true,last_significant = '{$date}' WHERE  user_id IN ({$bindingsString}) AND group_id = $group->id ; ";
                    DB::statement($sql, $filename);
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
            "message" => "",
            "type" => $type,
            "object" => self::OBJECT_GROUP,
            "sign" => true,
            "payload" => $payload,
            "user_status" => $user->getUserNotifStatus()
        ];
        return $this->notifications->sendMassMessage($data, $followers, $user, true, $date);
    }

    public function requestChangeStatusGroup(User $user, array $data) {
        $validator = $this->validatorStatus($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $group = Group::find($data["group_id"]);

        if ($group) {
            if ($group->checkAdmin($user)) {
                if ($data['status'] == "group_active") {
                    $i = $group->countAllMembers();
                    $invitesLength = count($data["party"]);
                    if (($i + $invitesLength) <= $group->max_users) {
                        dispatch(new NotifyGroup($user, $group, $data["party"], $data["status"]));
                        //return $this->notifyGroup($user, $group, $data["party"], $data["status"]);
                        return ['status' => 'success', "message" => 'Request queued for active'];
                    } else {
                        $max = $group->max_users - $i;
                        return ['status' => 'error', "message" => 'Max users exceeded', "max" => $max];
                    }
                } else if ($data['status'] == self::GROUP_PENDING || $data['status'] == self::GROUP_REMOVED) {
                    dispatch(new NotifyGroup($user, $group, $data["party"], $data["status"]));
                    //return $this->notifyGroup($user, $group, $data["party"], $data["status"]);
                    return ['status' => 'success', "message" => 'Request queued'];
                }
            }
        }
    }

    public function getAdminGroupUsers(User $user, array $data) {
        $group = Group::find($data['group_id']);
        if ($group) {
            if ($group->checkAdmin($user)) {
                $per_page = 10;
                $skip = ($data['page'] - 1) * $per_page;
                $data['result'] = DB::table('group_user')->join('users', 'group_user.user_id', '=', 'users.id')->where('group_id', $data["group_id"])->where('user_id', "<>", $user->id)->where('level', $data['level'])->skip($skip)->take($per_page)->select('name', 'user_id as contact_id', 'avatar', 'level')->get();
                $data['total'] = DB::table('group_user')->join('users', 'group_user.user_id', '=', 'users.id')->where('group_id', $data["group_id"])->where('user_id', "<>", $user->id)->where('level', $data['level'])->count();
                return $data;
            }
            return null;
        }
    }

    public function setAdminGroup(User $user, array $data) {
        $filename = [$data['user_id'], $data['group_id']];
        $sql = "UPDATE group_user set is_admin=1 WHERE  user_id = ? AND group_id = ?; ";

        DB::statement($sql, $filename);
    }

    public function leaveGroup(User $user, $group_id) {
        $group = Group::find($group_id);
        $admin = null;
        if ($group) {
            $this->deleteGroupUser($user, $group);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function joinGroupById(User $user, $code) {
        $group = Group::where('id', '=', $code)->first();
        if ($group) {
            $members = $group->countAllMembers();
            if (count($members) >= $group->max_users) {
                return null;
            }
            $member = $group->checkMemberType($user);
            if ($member) {
                return ['status' => 'error', "message" => 'User cant join'];
            }
            if ($value->color == "") {
                $color = 0;
            } else {
                $color = intval($value->color);
            }
            $color ++;
            $user->groups()->save($group, ['color' => $color, "level" => self::GROUP_PENDING, "is_admin" => false]);
        }
        return $group;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getGroupByCode($code) {
        $group = Group::where('code', '=', $code)->first();
        if ($group) {
            if ($group->isPublicActive()) {
                return ['status' => 'success', "group" => $group];
            }
            return ['status' => 'error', "message" => 'Group not found or inactive'];
        }
        return ['status' => 'error', "message" => 'Group not found or inactive'];
    }

    public function joinGroupByCode(User $user, $code) {
        $group = Group::where('code', '=', $code)->first();

        if ($group) {
            if ($group->isPublicActive()) {
                if ($group->countAllMembers() >= $group->max_users) {
                    return ['status' => 'error', "message" => 'Group Full'];
                }

                $member = $group->checkMemberType($user);

                if ($member) {
                    return ['status' => 'error', "message" => 'User cant join'];
                }

                $user->groups()->save($group, ["level" => self::GROUP_PENDING, "is_admin" => false]);
                $group->is_authorized = false;
                $group->level = self::GROUP_PENDING;
                dispatch(new NotifyGroup($user, $group, null, self::GROUP_PENDING));
                //$this->notifyGroup($user, $group, null, self::GROUP_PENDING);
                return ['status' => 'success', "message" => 'Group joined', "group" => $group];
            }
            return ['status' => 'error', "message" => 'Group not public'];
        }
        return ['status' => 'error', "message" => 'Group not found'];
    }

    public function inviteUsers($user, $data, $isNew, $group) {
        $invites = array();
        $notifs = array();
        if ($group->isPublicActive()) {
            
        } else if (!$group->is_public) {
            
        } else {
            return null;
        }
        $profile = $group->checkMemberType($user);
        if ($profile) {
            
        } else {
            return null;
        }
        if ($profile->level == self::CONTACT_BLOCKED) {
            return null;
        }
        if ($profile->is_admin == 0) {
            return null;
        }
        $members = array();
        if ($isNew) {
            $i = 0;
        } else {
            $i = $group->countAllMembers();
        }
        if ($group->max_users <= $i) {
            return null;
        }
        $is_admin = false;
        if ($group->is_public) {
            $is_admin = true;
        }
        if (array_key_exists("contacts", $data)) {
            $inviteUsers = array();
            foreach ($data['contacts'] as $value) {
                $contact = User::find($value);
                if ($contact) {
                    $test = $group->checkMemberType($contact);
                    if ($test) {
                        continue;
                    }
                    $i++;
                    $invite = array();
                    $notif = array();
                    $notif['group_id'] = $group->id;
                    $notif['contact_id'] = $contact->id;
                    $notif['cellphone'] = $contact->area_code . " " . $contact->cellphone;
                    array_push($notifs, $notif);
                    $invite['group_id'] = $group->id;
                    $invite['user_id'] = $value;
                    $invite['color'] = $i;
                    $invite['level'] = "active";
                    $invite['is_admin'] = $is_admin;
                    $invite['created_at'] = $group->updated_at;
                    $invite['last_significant'] = $group->updated_at;
                    array_push($invites, $invite);
                    array_push($inviteUsers, $contact);
                    if ($group->max_users <= $i) {
                        break;
                    }
                }
            }
            $payload = array(
                "trigger_id" => $group->id,
                "type" => self::NEW_GROUP,
                "group_id" => $group->id,
                "group_name" => $group->name,
                "first_name" => $user->firstName,
                "last_name" => $user->lastName
            );
            $notification = [
                "trigger_id" => $group->id,
                "message" => "",
                "payload" => $payload,
                "object" => self::OBJECT_GROUP,
                "sign" => true,
                "type" => self::NEW_GROUP,
                "user_status" => $user->getUserNotifStatus()
            ];
            $this->notifications->sendMassMessage($notification, $inviteUsers, $user, true, $group->updated_at);
            $i++;
            if ($isNew) {
                
            } else {
                if (!$group->is_public) {
                    $payload = array(
                        "contacts" => $notifs,
                        "group_id" => $group->id,
                        "group_name" => $group->name,
                        "first_name" => $user->firstName,
                        "last_name" => $user->lastName
                    );
                    $notification = [
                        "trigger_id" => $group->id,
                        "message" => "",
                        "object" => self::OBJECT_GROUP,
                        "sign" => true,
                        "payload" => $payload,
                        "type" => self::GROUP_INVITE,
                        "user_status" => $user->getUserNotifStatus()
                    ];
                    $this->notifications->sendMassMessage($notification, $group->getAllMembers(), $user, true, null);
                }
            }
            DB::table('group_user')->insert(
                    $invites
            );
            $group->save();
        }
        return $group;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function saveOrCreateGroup(array $data, User $user) {
        $validator = $this->validatorGroup($data);
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        if (array_key_exists("group_id", $data)) {
            if ($data["group_id"]) {
                $groupid = $data['group_id'];
                $members = DB::select('select user_id as id from group_user where user_id  = ? and group_id = ? and is_admin = 1 AND level = "active" ', [$user->id, $groupid]);
                if (sizeof($members) == 0) {
                    return null;
                }
                unset($data['group_id']);
                foreach ($data as $key => $value) {
                    if (!$value) {
                        unset($data[$key]);
                    }
                }
                Group::where('id', $groupid)->update($data);
                $group = Group::find($groupid);
                if ($group) {
                    return ['status' => 'success', "message" => 'Group updated', "code" => $group->code, "group" => $group];
                }
                return ['status' => 'error', "message" => 'Group not found'];
            }
        }

        $invites = array();
        $admin = array();
        $data["status"] = "active";
        $data["avatar"] = "default";
        $data["max_users"] = 10;
        $invites = $data['contacts'];
        unset($data['contacts']);
        unset($data[0]);
        unset($data[""]);
        if ($data['is_public']) {
            $again = true;
            $data['ends_at'] = date('Y-m-d', strtotime("+1 days"));
            while ($again) {
                $string = str_random(20);
                $group = Group::where("code", $string)->first();
                if ($group) {
                    
                } else {
                    $again = false;
                    $data['code'] = $string;
                }
            }
        } else {
            $data['is_public'] = 0;
        }



        $group = Group::create($data);
        $invite = array();
        $invite['group_id'] = $group->id;
        $invite['user_id'] = $user->id;
        $invite['color'] = 1;
        $invite['level'] = "active";
        $invite['is_admin'] = true;
        $invite['created_at'] = date("Y-m-d H:i:s");
        $invite['last_significant'] = date("Y-m-d H:i:s");
        array_push($admin, $invite);
        DB::table('group_user')->insert(
                $admin
        );
        $i = 0;
        $data['contacts'] = $invites;
        $data["group_id"] = $group->id;

        dispatch(new InviteUsers($user, $data, true, $group));

        //$this->inviteUsers($user, $data, true);
        $group->updated_at = strtotime($group->updated_at);
        return ['status' => 'success', 'message' => 'Group saved', "group" => $group, "strtotime" => strtotime($group->updated_at)];
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorGroup(array $data) {
        return Validator::make($data, [
                    'name' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorInvite(array $data) {
        return Validator::make($data, [
                    'user_id' => 'required|max:255',
                    'group_id' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorStatus(array $data) {
        return Validator::make($data, [
                    'group_id' => 'required|max:255',
                    'status' => 'required|max:255',
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
