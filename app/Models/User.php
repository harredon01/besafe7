<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\Storage;
use App\Jobs\NotifyContacts;
use App\Models\FileM;
use DB;
use DateTime;
use Carbon\Carbon; 

class User extends Authenticatable {

//    use HasApiTokens, Notifiable;
    use HasApiTokens,
        Notifiable,
        Encryptable;
    const ACCESS_USER_OBJECT = 'userables';
    const ACCESS_USER_OBJECT_ID = 'userable_id';
    const ACCESS_USER_OBJECT_TYPE = 'userable_type';
    const CONTACT_BLOCKED = 'contact_blocked';
    const OBJECT_LOCATION = 'Location';
    const RED_MESSAGE_TYPE = 'emergency';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'firstName', 'lastName', 'name', 'gender', 'area_code','plan', 'cellphone',
        'docType', 'docNum', 'username', 'email', 'avatar', 'password', 'language'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'notify_location', 'is_alerting', 'is_tracking', 'alert_type', 'docType', 'docNum', 'write_report',
        'emailNotifications', 'pushNotifications', 'platform', 'token', 'green', 'red', 'trip', 'hash', 'token', 'platform', 'card_brand', 'card_last_four', 'ends_at'];
    protected $encryptable = [
        'green',
        'red',
        'card_brand',
        'card_last_four'
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'ends_at'
    ];

    public function userSocials() {
        return $this->hasMany('App\Models\UserSocial');
    }

    public function merchants() {
        return $this->belongsToMany('App\Models\Merchant')->withTimestamps();
    }

    public function addresses() {
        return $this->hasMany('App\Models\Address');
    }

    public function locations() {
        return $this->hasMany('App\Models\Location');
    }

    public function orders() {
        return $this->hasMany('App\Models\Order');
    }

    public function notifications() {
        return $this->hasMany('App\Models\Notification');
    }

    public function groups() {
        return $this->belongsToMany('App\Models\Group')->withPivot('color')->withTimestamps();
    }

    public function vehicles() {
        return $this->hasMany('App\Models\Vehicle');
    }

    public function routes() {
        return $this->hasMany('App\Models\Route');
    }

    public function cargos() {
        return $this->hasMany('App\Models\Cargo');
    }

    public function items() {
        return $this->hasMany('App\Models\Item');
    }

    public function subscriptions() {
        return $this->hasMany('App\Models\Subscription');
    }

    public function sources() {
        return $this->hasMany('App\Models\Source');
    }

    public function medical() {
        return $this->hasOne('App\Models\Medical');
    }

    public function messages() {
        return $this->morphMany('App\Models\Message', 'messageable');
    }
    
    public function isActive() {
        return $this->ends_at && Carbon::now()->lt($this->ends_at);
    }

    public function checkAddImg() {
        return $this->id;
    }

    public function postAddImg($user, $type, $filename) {
        if ($type == "user_avatar") {
            Storage::delete($this->avatar);
            FileM::where("file", $this->avatar)->delete();
            $this->avatar = $filename;
            $this->save();
            dispatch(new NotifyContacts($this, $filename));
        }
    }

    public function makeTrip() {
        if ($this->is_tracking != 1 || $this->trip == 0) {
            $exists = true;
            $number = 0;
            if ($this->is_tracking != 1) {
                $this->is_tracking = 1;
            }
            if ($this->trip == 0) {
                while ($exists) {
                    $number = time() - 1477256930 + $this->id;
                    $test = User::where("trip", $number)->first();
                    if ($test) {
                        $exists = true;
                    } else {
                        $exists = false;
                    }
                }
                $this->trip = $number;
            }
            $this->save();
        }
    }
    public function getUserNotifStatus() {
        if ($this->is_alerting) {
            return $this->alert_type;
        }
        if ($this->is_tracking) {
            return "tracking";
        }
        return "normal";
    }

    public function getRecipientsObject($followers,$object,$objectActive_id) {
        $numbers = explode(",", $followers);
        $bindingsString = trim(str_repeat('?,', count($numbers)), ',');
        $sql = "SELECT id FROM users WHERE  id IN ({$bindingsString}) AND id NOT IN ("
        . "SELECT user_id FROM contacts where contact_id = $this->id AND level = '" . self::CONTACT_BLOCKED . "')  "
                . "AND id NOT IN ("
                . "SELECT user_id FROM ". self::ACCESS_USER_OBJECT . " "
                . "where " . self::ACCESS_USER_OBJECT_ID . " = $this->id "
                . "and ". self::ACCESS_USER_OBJECT_TYPE . " = '" . $object . "'  "
                . "and object_id = $objectActive_id  ); ";
        $recipients = DB::select($sql, $numbers);
        return $recipients;
    }
    public function getNonBlockedUser($objectActive_id) {
        $followers = DB::select("select user_id as id from contacts "
                . "where contact_id = $this->id "
                . "and user_id = $objectActive_id "
                . "and level <> '" . self::CONTACT_BLOCKED . "' ;");
        return $followers;
    }
    public function getCurrentFollowers() {
        $followers = DB::select("SELECT user_id as id FROM " . self::ACCESS_USER_OBJECT 
                . " WHERE " . self::ACCESS_USER_OBJECT_ID . "=? "
                . " and " . self::ACCESS_USER_OBJECT_TYPE . " = '" . self::OBJECT_LOCATION . "'; ", [$this->id]);
        return $followers;
    }
    public function updateFollowersDate(){
        //$date = DateTime::createFromFormat('d-m-Y H:i:s');
        $followers = DB::table(self::ACCESS_USER_OBJECT )
                        ->where(self::ACCESS_USER_OBJECT_ID,  $this->id)
                        ->where(self::ACCESS_USER_OBJECT_TYPE, self::OBJECT_LOCATION)->pluck('id');
        DB::table('contacts')
                        ->where('contacts.contact_id',  $this->id)
                        ->whereIn('contacts.user_id', $followers)
                        ->update(array("updated_at" => date("Y-m-d H:i:s")));
    }
    public function getNonBlockedContacts() {
        $followers = DB::select("select user_id as id from contacts "
                . "where user_id = $this->id "
                . "and contact_id not in ( "
                . " select user_id from contacts"
                . " where contact_id = $this->id"
                . " and level = '" . self::CONTACT_BLOCKED ."' )"
                . "and level <> '" . self::CONTACT_BLOCKED . "' ;");
        return $followers;
    }
    public function getEmergencyAndCurrentFollowerContacts() {
        $followers = DB::select("select 
                        contact_id as id,object_id
                    from
                        contacts c
                            left join
                        userables u ON c.contact_id = u.user_id
                            and u.userable_type = '" . self::OBJECT_LOCATION . "'
                            and userable_id = $this->id where c.user_id = $this->id  and level='" . self::RED_MESSAGE_TYPE . "' "
                        . " and c.contact_id NOT IN ( "
                . "SELECT user_id FROM contacts WHERE contact_id = $this->id and level = '" . self::CONTACT_BLOCKED . "');  ");
        return $followers;
    }
    public function getEmergencyContacts(){
        $followers = DB::select("SELECT contact_id as id FROM contacts WHERE user_id= $this->id "
                . "and level = 'emergency' and contacts.contact_id NOT IN ( "
                . "SELECT user_id FROM contacts WHERE contact_id = $this->id and level = '" . self::CONTACT_BLOCKED . "'"
                . ") ");
        return $followers;
    }
    public function isBlocked($destination) {
        $followers = DB::select("select *
                    from
                        contacts where contact_id = ? and user_id = ? and level = '" . self::CONTACT_BLOCKED . "' ;", [$this->id, $destination]);
        if (count($followers > 0)) {
            return true;
        }
        return false;
    }
    
    public function updateAllContactsDate(){
        DB::table('contacts')
                        ->where('contacts.contact_id', '=', $this->id)
                        ->update(array("updated_at" => date("Y-m-d H:i:s")));
    }
    public function updateAllEmergencyContactsDate(){
        DB::table('contacts')
                        ->where('contacts.contact_id', $this->id)
                        ->where('contacts.level',  "emergency")
                        ->update(array("updated_at" => date("Y-m-d H:i:s")));
    }


        public function getRecipientsMessage($objectActive_id) {
        return $this->getNonBlockedUser($objectActive_id);
    }

}
