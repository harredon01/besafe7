<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Jobs\NotifyGroup;
use App\Models\FileM;
use DB;

class Group extends Model {

    const ACCESS_USER_OBJECT = 'userables';
    const ACCESS_USER_OBJECT_ID = 'userable_id';
    const ACCESS_USER_OBJECT_TYPE = 'userable_type';
    const CONTACT_BLOCKED = 'contact_blocked';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';
    protected $dates = [
        'created_at',
        'updated_at',
        'ends_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'status', 'avatar', 'code', 'max_users', 'is_public', 'ends_at', 'level'];

    public function users() {
        return $this->belongsToMany('App\Models\User')->withPivot('color')->withPivot('status')->withPivot('is_admin')->withTimestamps();
    }

    public function reports() {
        return $this->hasMany('App\Models\Group');
    }

    public function subscription() {
        return $this->hasOne('App\Models\Subscription');
    }

    public function messages() {
        return $this->morphMany('App\Models\Message', 'messageable');
    }

    public function isActive() {
        return $this->ends_at && Carbon::now()->lt($this->ends_at);
    }

    public function isPublicActive() {
        return $this->is_public && $this->isActive();
    }

    public function checkAddImg($user, $type) {
        if ($type == "group_avatar") {
            if ($this->checkAdmin($user)) {
                return $this->id;
            }
        }
        return null;
    }

    public function postAddImg($user, $type, $filename) {
        if ($type == "group_avatar") {
            FileM::where("file", $this->avatar)->delete();
            Storage::delete($this->avatar);
            $this->avatar = $filename;
            $this->save();
            dispatch(new NotifyGroup($user, $this, $filename, $type));
        }
    }

    public function checkMemberType($user) {
        $users = DB::select('select user_id as id, is_admin from group_user where user_id = ? and group_id = ? AND status <> "blocked" limit 1', [$user->id, $this->id]);
        if (count($users) == 1) {
            return $users[0];
        }
        return null;
    }

    public function getAllNewFollowers($user, $object, $objectActive_id) {
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=? AND status = 'active' AND user_id NOT IN ("
                        . "SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " "
                        . "where " . self::ACCESS_USER_OBJECT_ID . " = $user->id "
                        . "and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . $object . "' "
                        . "and object_id = $objectActive_id "
                        . "); ", [$this->id]);
        return $recipients;
    }

    public function getAllAdminMembers() {
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=? AND status = 'active' AND is_admin = 1 ", [$this->id]);
        return $recipients;
    }

    public function getAllMembers() {
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=? AND status = 'active' ", [$this->id]);
        return $recipients;
    }
    public function countAllMembers() {
        return DB::table('group_user')->where('group_id', $this->id)->where('status', 'active')->count();
    }
    public function getAllAdminMembersButActive($user) {
        $followers = DB::select("SELECT user_id as id FROM group_user "
                . "WHERE group_id=?  "
                . "AND is_admin = 1 "
                . "and user_id <>? ", [intval($this->id), $user->id]);
        return $followers;
    }

    public function getAllMembersButActive($user) {
        $followers = DB::select("SELECT user_id as id FROM group_user "
                . "WHERE group_id=?  "
                . "and user_id <>? ", [intval($this->id), $user->id]);
        return $followers;
    }
    public function getAllAdminMembersNonUserBlockedButActive($user) {
        $followers = DB::select("SELECT user_id as id FROM group_user "
                . "WHERE group_id=?  "
                . "AND is_admin = 1 "
                . "AND status <> '" . self::CONTACT_BLOCKED . "' "
                . "and user_id <>? ", [intval($this->id), $user->id]);
        return $followers;
    }

    public function getAllMembersNonUserBlockedButActive($user) {
        $followers = DB::select("SELECT user_id as id FROM group_user "
                . "WHERE group_id=?  "
                . "AND status <> '" . self::CONTACT_BLOCKED . "' "
                . "and user_id <>? ", [intval($this->id), $user->id]);
        return $followers;
    }

    public function getAllNewNonUserBlockedFollowers($user, $object, $objectActive_id) {
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=? AND  status = 'active' AND group_user.user_id NOT IN ("
                        . "SELECT user_id FROM contacts "
                        . "where contact_id = $user->id "
                        . "AND level = '" . self::CONTACT_BLOCKED . "'"
                        . ") "
                        . "AND user_id NOT IN ("
                        . "SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " "
                        . "where " . self::ACCESS_USER_OBJECT_ID . " = $user->id "
                        . "and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . $object . "' "
                        . "and object_id = $objectActive_id "
                        . "); ", [$this->id]);
        return $recipients;
    }

    public function getAllNonUserBlocked($user) {
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=? AND  status = 'active' AND group_user.user_id NOT IN ("
                        . "SELECT user_id FROM contacts "
                        . "where contact_id = $user->id "
                        . "AND level = '" . self::CONTACT_BLOCKED . "'"
                        . "); ", [$this->id]);
        return $recipients;
    }

    public function checkAdmin($user) {
        $users = DB::select('select * from group_user where user_id = ? and is_admin = 1 and group_id = ? AND status <> "blocked" limit 2', [$user->id, $this->id]);
        if (count($users) == 1) {
            return true;
        }
        return false;
    }

    public function getRecipientsObject($user, $object, $objectActive_id) {
        $res = $this->users()->where('user_id', $user->id)->where('status', 'active')->get();
        if (count($res) > 0) {
            if ($res[0]->is_admin) {
                if ($this->isPublicActive()) {
                    return $this->getAllNewFollowers($user, $object, $objectActive_id);
                } else if (!$this->is_public) {
                    return $this->getAllNewNonUserBlockedFollowers($user, $object, $objectActive_id);
                } else {
                    return null;
                }
            } else {
                if ($this->isPublicActive()) {
                    return $this->getAllNewFollowers($user, $object, $objectActive_id);
                } else if (!$this->is_public) {
                    return $this->getAllNewNonUserBlockedFollowers($user, $object, $objectActive_id);
                } else {
                    return null;
                }
            }
        } else {
            return null;
        }
    }

    public function getRecipientsMessage($user, $data) {
        $res = $this->users()->where('user_id', $user->id)->where('status', 'active')->get();
        if (count($res) > 0) {
            if ($this->isPublicActive()) {
                if ($res[0]->is_admin) {
                    if (array_key_exists("target_id", $data)) {
                        $followers = DB::select("select 
                                user_id as id
                                    from
                                    group_user where group_id = $this->id AND status = 'active' and (is_admin = 1 or user_id = ? ) ;", [$data['target_id']]);
                    } else {
                        $followers = $this->getAllMembers();
                    }
                } else {
                    $followers = $this->getAllAdminMembers();
                    $data['target_id'] = $user->id;
                }
            } else if (!$this->is_public) {
                $followers = $this->getAllNonUserBlocked($user);
            } else {
                return null;
            }
        }
        return array("followers" => $followers,"data"=>$data);
    }

}
