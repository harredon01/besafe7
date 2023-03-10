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
    const CONTACT_DELETED = 'contact_deleted';
    const GROUP_PENDING = 'group_pending';
    const GROUP_BLOCKED = 'group_blocked';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'status', 'avatar', 'code', 'max_users', 'is_public', 'ends_at', 'plan', 'level', 'rating'];
    protected $dates = [
        'created_at',
        'updated_at',
        'ends_at'
    ];

    public function users() {
        return $this->belongsToMany('App\Models\User')->withPivot('color')->withPivot('level')->withPivot('is_admin')->withPivot('last_significant');
    }

    public function reports() {
        return $this->belongsToMany('App\Models\Report')->withPivot('status')->withTimestamps();
    }

    public function merchants() {
        return $this->belongsToMany('App\Models\Merchant')->withPivot('status')->withTimestamps();
    }

    public function products() {
        return $this->belongsToMany('App\Models\Product')->withPivot('status')->withTimestamps();
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
        $users = DB::select("select user_id as id, is_admin, level, last_significant from group_user where user_id = ? and group_id = ? "
                        . " AND level <> '" . self::GROUP_PENDING . "' limit 1", [$user->id, $this->id]);
        if (count($users) == 1) {
            return $users[0];
        }
        return null;
    }

    public function getAllNewFollowers($user, $object, $objectActive_id) {
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=? "
                        . " AND level <> '" . self::GROUP_BLOCKED . "' "
                        . " AND level <> '" . self::GROUP_PENDING . "' "
                        . " AND user_id NOT IN "
                        . "("
                        . "SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " "
                        . "where " . self::ACCESS_USER_OBJECT_ID . " = $user->id "
                        . "and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . $object . "' "
                        . "and object_id = $objectActive_id "
                        . "); ", [$this->id]);
        return $recipients;
    }

    public function getAllNewAdminFollowers($user, $object, $objectActive_id) {
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . " WHERE group_id=? AND level <> '" . self::GROUP_BLOCKED . "'"
                        . " AND level <> '" . self::GROUP_PENDING . "'"
                        . " AND is_admin = true "
                        . " AND user_id NOT IN "
                        . "("
                        . "SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " "
                        . "where " . self::ACCESS_USER_OBJECT_ID . " = $user->id "
                        . "and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . $object . "' "
                        . "and object_id = $objectActive_id "
                        . "); ", [$this->id]);
        return $recipients;
    }

    public function getAllAdminMembers() {
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=? AND level <> '" . self::GROUP_BLOCKED . "' AND level <> '" . self::GROUP_PENDING . "' AND is_admin = 1 ", [$this->id]);
        return $recipients;
    }

    public function countAllAdminMembers() {
        $recipients = DB::select("SELECT count(id) as total FROM group_user "
                        . "WHERE group_id=? AND level <> '" . self::GROUP_BLOCKED . "' AND level <> '" . self::GROUP_PENDING . "' AND is_admin = 1 ", [$this->id]);
        $res = $recipients[0];
        return $res->total;
    }

    public function getAllMembers() {
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=? AND level <> '" . self::GROUP_BLOCKED . "' AND level <> '" . self::GROUP_PENDING . "' ", [$this->id]);
        return $recipients;
    }

    public function countAllMembers() {
        $recipients = DB::select("SELECT count(id) as total FROM group_user "
                        . "WHERE group_id=? AND level <> '" . self::GROUP_BLOCKED . "' AND level <> '" . self::GROUP_PENDING . "' ", [$this->id]);
        $res = $recipients[0];
        return $res->total;
    }

    public function getAllAdminMembersButActive($user) {
        $followers = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=?  "
                        . "AND is_admin = 1 "
                        . "and user_id <> ? ", [intval($this->id), $user->id]);
        return $followers;
    }

    public function getAllMembersButActive($user) {
        $followers = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=?  "
                        . "and user_id <>? ", [intval($this->id), $user->id]);
        return $followers;
    }

    public function getSubSetMembersButActive($user, $set) {
        $bindingsString = trim(str_repeat('?,', count($set)), ',');
        $sql = "SELECT user_id as id FROM group_user WHERE  user_id IN ({$bindingsString}) "
                . "AND level <> '" . self::GROUP_BLOCKED . "' AND level <> '" . self::GROUP_PENDING . "' "
                . "AND user_id <> $user->id "
                . "AND group_id = $this->id; ";
        $followers = DB::select($sql, $set);
        return $followers;
    }

    public function getAllAdminMembersNonUserBlockedButActive($user) {
        $followers = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=?  "
                        . "AND is_admin = 1 "
                        . "AND level <> '" . self::GROUP_BLOCKED . "' AND level <> '" . self::GROUP_PENDING . "' "
                        . "and user_id <>? ", [intval($this->id), $user->id]);
        return $followers;
    }

    public function getAllMembersNonUserBlockedButActive($user) {
        $followers = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=?  "
                        . "AND level <> '" . self::GROUP_BLOCKED . "' AND level <> '" . self::GROUP_PENDING . "' "
                        . "and user_id <>? ", [intval($this->id), $user->id]);
        return $followers;
    }

    public function getAllNewNonUserBlockedFollowers($user, $object, $objectActive_id) {
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=? "
                        . " AND level <> '" . self::GROUP_BLOCKED . "'"
                        . " AND level <> '" . self::GROUP_PENDING . "'"
                        . " AND is_admin = true "
                        . " AND group_user.user_id NOT IN ("
                        . "SELECT user_id FROM contacts "
                        . "where contact_id = $user->id "
                        . "AND (level = '" . self::CONTACT_BLOCKED . "' OR level = '" . self::CONTACT_DELETED . "') "
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
                        . "WHERE group_id=? AND  level <> '" . self::GROUP_BLOCKED . "' AND level <> '" . self::GROUP_PENDING . "' "
                        . "AND group_user.user_id NOT IN ("
                        . "SELECT user_id FROM contacts "
                        . "where contact_id = $user->id "
                        . "AND (level = '" . self::CONTACT_BLOCKED . "' OR level = '" . self::CONTACT_DELETED . "') "
                        . "); ", [$this->id]);
        return $recipients;
    }

    public function countAllNonUserBlocked($user) {
        $recipients = DB::select("SELECT count(id) as total FROM group_user "
                        . "WHERE group_id=? AND  level <> '" . self::GROUP_BLOCKED . "' AND level <> '" . self::GROUP_PENDING . "' "
                        . "AND group_user.user_id NOT IN ("
                        . "SELECT user_id FROM contacts "
                        . "where contact_id = $user->id "
                        . "AND (level = '" . self::CONTACT_BLOCKED . "' OR level = '" . self::CONTACT_DELETED . "') "
                        . "); ", [$this->id]);
        $res = $recipients[0];
        return $res->total;
    }

    public function checkAdmin($user) {
        $users = DB::select('select * from group_user where user_id = ? and is_admin = 1 and group_id = ? '
                        . 'AND level <> "' . self::GROUP_BLOCKED . '" '
                        . 'AND level <> "' . self::GROUP_PENDING . '" '
                        . 'limit 1', [$user->id, $this->id]);
        if (count($users) > 0) {
            return true;
        }
        return false;
    }

    public function getRecipientsObject($user, $object, $objectActive_id) {
        $res = $this->checkMemberType($user);
        if ($res) {
            if ($res->level != self::CONTACT_BLOCKED) {
                if ($res->is_admin) {
                    if ($this->isPublicActive()) {
                        return $this->getAllNewFollowers($user, $object, $objectActive_id);
                    } else if (!$this->is_public) {
                        return $this->getAllNewNonUserBlockedFollowers($user, $object, $objectActive_id);
                    } else {
                        return null;
                    }
                } else {
                    if ($this->isPublicActive()) {
                        return $this->getAllNewAdminFollowers($user, $object, $objectActive_id);
                    } else if (!$this->is_public) {
                        return $this->getAllNewNonUserBlockedFollowers($user, $object, $objectActive_id);
                    } else {
                        return null;
                    }
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getRecipientsMessage($user, $data) {
        $res = $this->checkMemberType($user);
        if ($res) {
            if ($res->level != self::CONTACT_BLOCKED) {
                if (!$this->is_public) {
                    if ($res->is_admin) {
                        $data['is_admin'] = true;
                    } else {
                        $data['is_admin'] = false;
                    }
                    $followers = $this->getAllNonUserBlocked($user);
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
        return array("followers" => $followers, "data" => $data);
    }

    public function countRecipientsMessage($user, $data) {
        $res = $this->checkMemberType($user);
        $followers = 0;
        if ($res) {
            if ($res->level != self::CONTACT_BLOCKED) {
                if (!$this->is_public) {
                    $followers = $this->countAllNonUserBlocked($user);
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        }
        return $followers;
    }

    public function updateAllMembersDate($level ) {
        $date = date("Y-m-d H:i:s");
        $data = array("last_significant" => $date);
        if ($level) {
            $data["level"] = $level;
        }
        DB::table('group_user')
                ->where('group_user.group_id', '=', $this->id)
                ->where('level', '<>', self::GROUP_BLOCKED)
                ->where('level', '<>', self::GROUP_PENDING)
                ->update($data);
        return $date;
    }

    public function updateAllAdminMembersDate( ) {
        $date = date("Y-m-d H:i:s");
        $data = array("last_significant" => $date);
        DB::table('group_user')
                ->where('group_user.group_id', '=', $this->id)
                ->where('level', '<>', self::GROUP_BLOCKED)
                ->where('level', '<>', self::GROUP_PENDING)
                ->where('is_admin', true)
                ->update($data);
        return $date;
    }

    public function updateAllNewFollowersDate($user, $object, $objectActive_id) {
        $date = date("Y-m-d H:i:s");
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=? AND level <> '" . self::GROUP_BLOCKED . "' AND level <> '" . self::GROUP_PENDING . "' AND user_id NOT IN ("
                        . "SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " "
                        . "where " . self::ACCESS_USER_OBJECT_ID . " = $user->id "
                        . "and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . $object . "' "
                        . "and object_id = $objectActive_id "
                        . "); ", [$this->id]);
        DB::table('group_user')
                ->where('group_user.group_id', $this->id)
                ->whereIn('group_user.user_id', array_pluck($recipients, 'id'))
                ->update(array("last_significant" => $date));
        return $date;
    }

    public function updateAllMembersButActiveDate($user) {
        $date = date("Y-m-d H:i:s");
        $followers = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=?  "
                        . "and user_id <>? ", [intval($this->id), $user->id]);
        DB::table('group_user')
                ->where('group_user.group_id', $this->id)
                ->whereIn('group_user.user_id', array_pluck($followers, 'id'))
                ->update(array("last_significant" => $date));
        return $date;
    }

    public function updateAllAdminFollowersDate($user, $object, $objectActive_id) {
        $date = date("Y-m-d H:i:s");
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . " WHERE group_id=? AND level <> '" . self::GROUP_BLOCKED . "'"
                        . " AND level <> '" . self::GROUP_PENDING . "'"
                        . " AND is_admin = true "
                        . " AND user_id NOT IN "
                        . "("
                        . "SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " "
                        . "where " . self::ACCESS_USER_OBJECT_ID . " = $user->id "
                        . "and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . $object . "' "
                        . "and object_id = $objectActive_id "
                        . "); ", [$this->id]);
        DB::table('group_user')
                ->where('group_user.group_id', $this->id)
                ->whereIn('group_user.user_id', array_pluck($recipients, 'id'))
                ->update(array("last_significant" => $date));
        return $date;
    }

    public function updateAllNewNonUserBlockedFollowersDate($user, $object, $objectActive_id) {
        $date = date("Y-m-d H:i:s");
        $recipients = DB::select("SELECT user_id as id FROM group_user "
                        . "WHERE group_id=? "
                        . " AND level <> '" . self::GROUP_BLOCKED . "'"
                        . " AND level <> '" . self::GROUP_PENDING . "'"
                        . " AND is_admin = true "
                        . " AND group_user.user_id NOT IN ("
                        . "SELECT user_id FROM contacts "
                        . "where contact_id = $user->id "
                        . "AND (level = '" . self::CONTACT_BLOCKED . "' OR level = '" . self::CONTACT_DELETED . "') "
                        . ") "
                        . "AND user_id NOT IN ("
                        . "SELECT user_id FROM " . self::ACCESS_USER_OBJECT . " "
                        . "where " . self::ACCESS_USER_OBJECT_ID . " = $user->id "
                        . "and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . $object . "' "
                        . "and object_id = $objectActive_id "
                        . "); ", [$this->id]);
        DB::table('group_user')
                ->where('group_user.group_id', $this->id)
                ->whereIn('group_user.user_id', array_pluck($recipients, 'id'))
                ->update(array("last_significant" => $date));
        return $date;
    }

    public function updateRecipientsObjectDate($user, $object, $objectActive_id) {
        $res = $this->checkMemberType($user);
        if ($res) {
            if ($res->level != self::CONTACT_BLOCKED) {
                if ($res->is_admin) {
                    if ($this->isPublicActive()) {
                        return $this->updateAllNewFollowersDate($user, $object, $objectActive_id);
                    } else if (!$this->is_public) {
                        return $this->updateAllNewNonUserBlockedFollowers($user, $object, $objectActive_id);
                    } else {
                        return null;
                    }
                } else {
                    if ($this->isPublicActive()) {
                        return $this->updateAllAdminFollowersDate($user, $object, $objectActive_id);
                    } else if (!$this->is_public) {
                        return $this->updateAllNewNonUserBlockedFollowers($user, $object, $objectActive_id);
                    } else {
                        return null;
                    }
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

}
