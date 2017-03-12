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

    public function leaveGroup(User $user, $group_id) {
        $group = Group::find($group_id);
        $admin = null;
        if ($group) {
            if ($group->admin_id == $user->id) {
                $users = DB::select('select * from group_user where user_id  <> ? and group_id = ?', [$user->id, $group_id]);
                if (count($users) > 0) {
                    $group->admin_id = $users[0]->user_id;
                    $admin = $users[0]->user_id;
                    $group->save();
                } else {
                    $this->editAlerts->deleteGroupNotifs($user, $group_id);
                    $user->groups()->delete($group);
                    return ['status' => 'success', "message" => 'Group deleted'];
                }
            }
            $deleted = DB::delete('delete from group_user where user_id = ? and group_id = ?', [$user->id, $group_id]);
            $this->editAlerts->deleteGroupNotifs($user, $group_id);
            if ($deleted > 0) {
                if($group->is_public){
                    dispatch(new NotifyGroup($user, $group, $admin, 'group_leave'));
                }
                
                return ['status' => 'success', "message" => 'Group deleted'];
            } else {
                return ['status' => 'error', "message" => 'Group not deleted'];
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
            $value = DB::table('group_user')
                            ->select("color")
                            ->where("group_id", "=", $group->id)
                            ->orderBy('id', 'desc')->first();
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
            $members = array();
            if ($isNew) {
                $i = 0;
            } else {
                $members = DB::select('select user_id as id from group_user where user_id  <> ? and group_id = ?', [$user->id, $group->id]);
                $i = sizeof($members);
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
                $this->editAlerts->sendMassMessage($notification, $inviteUsers, $user);
                $i++;
                if ($isNew) {
                    $invite = array();
                    $invite['group_id'] = $group->id;
                    $invite['user_id'] = $user->id;
                    $invite['color'] = $i;
                    $invite['created_at'] = date("Y-m-d H:i:s");
                    $invite['updated_at'] = date("Y-m-d H:i:s");
                    array_push($invites, $invite);
                } else {
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
                    $this->editAlerts->sendMassMessage($notification, $members, $user);
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
            unset($data['group_id']);
            Group::where('admin_id', $user->id)
                    ->where('id', $groupid)->update($data);
            $group = Group::find($groupid);
            if ($group) {
                return ['status' => 'success', "message" => 'Group updated', "code" => $group->code, "group" => $group];
            }
            return ['status' => 'error', "message" => 'Group not found'];
        } else {
            $invites = array();
            $data["admin_id"] = $user->id;
            $data["status"] = "active";
            $data["avatar"] = "default";
            $group = Group::create($data);
            $i = 0;
            $data["group_id"] = $group->id;
            dispatch(new InviteUsers($user, $data, true));
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
