<?php

namespace App\Services;

use Validator;
use App\Models\Group;
use App\Jobs\InviteUsers;
use App\Jobs\NotifyGroup;
use App\Jobs\AdminGroup;
use App\Models\User;
use Illuminate\Http\Response;
use App\Services\EditAlerts;
use DB;

class EditGroup {

    const GROUP_REMOVE = 'group_remove';
    const CONTACT_BLOCKED = 'contact_blocked';
    const GROUP_LEAVE = 'group_leave';
    const NEW_GROUP = 'new_group';
    const GROUP_ACTIVE = 'group_active';
    const GROUP_INVITE = 'group_invite';
    const GROUP_PENDING = 'group_pending';
    const OBJECT_GROUP = 'Group';

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

    public function getGroup($group_id) {
        $group = Group::find($group_id);
        $group->users;
        return $group;
    }

    public function getActiveAdminGroups(User $user) {
        return DB::select('select g.* from groups g join group_user gu on g.id = gu.group_id'
                        . ' where gu.user_id  = ? and gu.is_admin = 1 and g.ends_at > CURDATE() AND gu.status = "active";', [$user->id]);
    }

    public function checkAdminGroup($userId, $groupId) {
        return $results = DB::select(' select * from group_user where group_id = ? and user_id = ? and is_admin = 1 AND status = "active"', [$groupId, $userId]);
    }

    public function deleteGroupUser(User $user, Group $group) {
        $users = $group->checkMemberType($user);
        $deleteGroup = false;
        if (count($users) > 0) {

            if (!$group->is_public) {

                $profile = $users[0];
                if ($profile->is_admin) {
                    $users2 = DB::select('select * from group_user where user_id  <> ? and group_id = ? and is_admin = 1 AND status = "active" limit 1', [$user->id, $group->id]);
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
                        $this->notifyGroup($user, $group, null, false);
                    }
                } else {
                    $this->notifyGroup($user, $group, null, false);
                }
                $deleted = DB::delete('delete from group_user where user_id = ? and group_id = ? ', [$user->id, $group->id]);
                if ($deleteGroup) {
                    $group->delete();
                }
            } else {
                $profile = $users[0];
                if ($profile->is_admin) {
                    return null;
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
                    "object" => self::OBJECT_GROUP,
                    "sign" => true,
                    "payload" => $payload,
                    "user_status" => $user->getUserNotifStatus()
                ];
                $this->editAlerts->sendMassMessage($data, $followers, $user, true);
                if ($group->isPublicActive()) {
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
                if ($group->isPublicActive()) {
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
            if ($group->isPublicActive()) {
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
                        "object" => self::OBJECT_GROUP,
                        "sign" => true,
                        "payload" => $payload,
                        "user_status" => $user->getUserNotifStatus()
                    ];
                    $this->editAlerts->sendMassMessage($data, $followers, $user, true);
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
        $group->save();
        $data = [
            "trigger_id" => $group->id,
            "message" => "",
            "type" => $type,
            "object" => self::OBJECT_GROUP,
            "sign" => true,
            "payload" => $payload,
            "user_status" => $user->getUserNotifStatus()
        ];
        return $this->editAlerts->sendMassMessage($data, $followers, $user, true);
        return ['success' => 'members notified'];
    }

    public function requestChangeStatusGroup(User $user, array $data) {
        $validator = $this->validatorStatus($data);
        if ($validator->fails()) {
            return $validator->getMessageBag();
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
                } else {
                    dispatch(new NotifyGroup($user, $group, $data["party"], $data["status"]));
                    //return $this->notifyGroup($user, $group, $data["party"], $data["status"]);
                    return ['status' => 'success', "message" => 'Request queued'];
                }
            }
        }
    }

    public function getAdminGroupUsers(User $user, array $data) {
        $users = $this->checkAdminGroup($user->id, $data["group_id"]);
        if (count($users) == 1) {
            $per_page = 10;
            $skip = ($data['page'] - 1) * $per_page;
            $data['result'] = DB::table('group_user')->join('users', 'group_user.user_id', '=', 'users.id')->where('group_id', $data["group_id"])->where('user_id', "<>", $user->id)->where('status', $data['level'])->skip($skip)->take($per_page)->select('name', 'user_id as contact_id', 'avatar')->get();
            $data['total'] = DB::table('group_user')->join('users', 'group_user.user_id', '=', 'users.id')->where('group_id', $data["group_id"])->where('user_id', "<>", $user->id)->where('status', $data['level'])->select('name', 'user_id as contact_id', 'avatar')->count();
            return $data;
        }
        return null;
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
            if ($value->color == "") {
                $color = 0;
            } else {
                $color = intval($value->color);
            }
            $color ++;
            $user->groups()->save($group, ['color' => $color, "status" => "pending", "is_admin" => false]);
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

                $members = $group->checkMemberType($user);

                if (count($members) > 0) {
                    return ['status' => 'error', "message" => 'User cant join'];
                }

                $user->groups()->save($group, ["status" => "pending", "is_admin" => false]);
                $group->is_authorized = false;
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
        $members = $group->checkMemberType($user);
        $profile = $members[0];
        if (sizeof($members) == 0) {
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
        if ($profile->is_admin) {
            $is_admin = true;
        }
        if (array_key_exists("contacts", $data)) {
            $inviteUsers = array();
            foreach ($data['contacts'] as $value) {
                $contact = User::find($value);
                if ($contact) {
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
                    $invite['status'] = "active";
                    $invite['is_admin'] = $is_admin;
                    $invite['created_at'] = date("Y-m-d H:i:s");
                    $invite['updated_at'] = date("Y-m-d H:i:s");
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
            $this->editAlerts->sendMassMessage($notification, $inviteUsers, $user, true);
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
                    $this->editAlerts->sendMassMessage($notification, $group->getAllMembers(), $user, true);
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
            return $validator->getMessageBag();
        }
        if ($data["group_id"]) {
            $groupid = $data['group_id'];
            $members = DB::select('select user_id as id from group_user where user_id  = ? and group_id = ? and is_admin = 1 AND status = "active" ', [$user->id, $groupid]);
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
        } else {
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
                    $string = str_random(12);
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
            $invite['status'] = "active";
            $invite['is_admin'] = true;
            $invite['created_at'] = date("Y-m-d H:i:s");
            $invite['updated_at'] = date("Y-m-d H:i:s");
            array_push($admin, $invite);
            DB::table('group_user')->insert(
                    $admin
            );
            $i = 0;
            $data['contacts'] = $invites;
            $data["group_id"] = $group->id;
            dispatch(new InviteUsers($user, $data, true, $group));
            //$this->inviteUsers($user, $data, true);
            return ['status' => 'success', 'message' => 'Group saved', "group" => $group];
        }
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
