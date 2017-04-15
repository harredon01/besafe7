<?php

namespace App\Services;

use Validator;
use App\Models\Group;
use App\Jobs\InviteUsers;
use App\Jobs\NotifyGroup;
use App\Models\User;
use Illuminate\Http\Response;
use App\Services\EditAlerts;
use DB;

class EditGroup {

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
                        . ' where gu.user_id  = ? and gu.is_admin = 1 and g.ends_at > CURDATE();', [$user->id]);
    }

    public function leaveGroup(User $user, $group_id) {
        $group = Group::find($group_id);
        $admin = null;
        if ($group) {
            $users = DB::select('select * from group_user where user_id  = ? and group_id = ?', [$user->id, $group_id]);
            if (count($users) > 0) {
                $member = $users[0];
                if ($member->is_admin) {
                    return null;
                } else {
                    $deleted = DB::delete('delete from group_user where user_id = ? and group_id = ?', [$user->id, $group_id]);
                    $this->editAlerts->deleteGroupNotifs($user, $group_id);
                    if ($deleted > 0) {
                        if (!$group->is_public) {
                            dispatch(new NotifyGroup($user, $group, $admin, 'group_leave'));
                        }

                        return ['status' => 'success', "message" => 'Group deleted'];
                    } else {
                        return ['status' => 'error', "message" => 'Group not deleted'];
                    }
                }
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function joinGroupByCode(User $user, $code) {
        $group = Group::where('id', '=', $code)
                        ->where('status', '=', 'active')->first();
        if ($group) {
            $members = DB::select('select user_id as id from group_user where group_id = ?', [ $group->id]);
            if (count($members) >= $group->max_users) {
                return null;
            }
            if ($value->color == "") {
                $color = 0;
            } else {
                $color = intval($value->color);
            }
            $color ++;
            $user->groups()->save($group, ['color' => $color]);
        }
        return $group;
    }

    public function inviteUsers($user, $data, $isNew) {
        $invites = array();
        $notifs = array();
        $group = Group::find(intval($data["group_id"]));

        if ($group) {
            if ($group->is_public && $group->isActive()) {
                
            } else if (!$group->is_public) {
                
            } else {
                return null;
            }
            $members = DB::select('select user_id as id from group_user where user_id  = ? and group_id = ? and is_admin = 1 ', [$user->id, $group->id]);
            if (sizeof($members) == 0) {
                return null;
            }
            $members = array();
            if ($isNew) {
                $i = 0;
            } else {
                $members = DB::select('select user_id as id from group_user where user_id  <> ? and group_id = ?', [$user->id, $group->id]);
                $i = sizeof($members);
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
                        $invite['is_admin'] = $is_admin;
                        $invite['created_at'] = date("Y-m-d H:i:s");
                        $invite['updated_at'] = date("Y-m-d H:i:s");
                        array_push($invites, $invite);
                        array_push($inviteUsers, $contact);
                    }
                }
                $payload = array(
                    "trigger_id" => $group->id,
                    "type" => "new_group",
                    "group_id" => $group->id,
                    "notification" => "Has sido agregado al grupo: " . $group->name,
                );
                $notification = [
                    "trigger_id" => $group->id,
                    "message" => "Has sido agregado al grupo: " . $group->name,
                    "payload" => $payload,
                    "type" => "new_group",
                    "subject" => "Nuevo grupo " . $group->name,
                    "user_status" => $this->editAlerts->getUserNotifStatus($user)
                ];
                $this->editAlerts->sendMassMessage($notification, $inviteUsers, $user, true);
                $i++;
                if ($isNew) {
                    
                } else {
                    if (!$group->is_public) {
                        $payload = array(
                            "contacts" => $notifs
                        );
                        $notification = [
                            "trigger_id" => $group->id,
                            "message" => "Nuevos usuarios al grupo: " . $group->name,
                            "payload" => $payload,
                            "type" => "group_invite",
                            "subject" => "Nuevos usuarios al grupo: " . $group->name,
                            "user_status" => $this->editAlerts->getUserNotifStatus($user)
                        ];
                        $this->editAlerts->sendMassMessage($notification, $members, $user, true);
                    }
                }
                DB::table('group_user')->insert(
                        $invites
                );
            }
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
        if (array_key_exists("group_id", $data)) {
            $groupid = $data['group_id'];
            $members = DB::select('select user_id as id from group_user where user_id  = ? and group_id = ? and is_admin = 1 ', [$user->id, $groupid]);
            if (sizeof($members) == 0) {
                return null;
            }
            unset($data['group_id']);
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
            $invites = $data['contacts'];
            unset($data['contacts']);
            unset($data[""]);
            $group = Group::create($data);
            $invite = array();
            $invite['group_id'] = $group->id;
            $invite['user_id'] = $user->id;
            $invite['color'] = 1;
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
            //dispatch(new InviteUsers($user, $data, true));
            $this->inviteUsers($user, $data, true);
            return $group;
        }
        return ['success' => 'Group saved', "group" => $group];
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
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedEditGroupMessage() {
        return 'There was a problem editing your group';
    }

}
