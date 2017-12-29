<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Cache;
use Carbon\Carbon; 

class Report extends Model {

    use Searchable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['merchant_id', 'city_id', 'region_id', 'country_id', 'name', 'type', 'email', 'telephone', 'address', 'description',
        'icon', 'lat', 'long', 'minimum', 'status', 'user_id', "private", "hash", "anonymous", "object", 'report_time', 'group_id','rating','ends_at'];
    protected $hidden = ['user_id'];
    protected $dates = [
        'created_at',
        'updated_at',
        'ends_at'
    ];
    
    public function isActive() {
        return $this->ends_at && Carbon::now()->lt($this->ends_at);
    }

    public function hours() {
        return $this->hasMany('App\Models\OfficeHour');
    }

    public function city() {
        return $this->hasOne('App\Models\City');
    }

    public function region() {
        return $this->hasOne('App\Models\Region');
    }

    public function country() {
        return $this->hasOne('App\Models\Country');
    }

    public function categories() {
        return $this->belongsToMany('App\Models\Category')->withTimestamps();
    }

    public function group() {
        return $this->belongsTo('App\Models\Group');
    }

    public function checkAddImg($user, $type) {
        if ($this->user_id == $user->id) {
            Cache::forget('Report_' . $this->id);
            return $this->id;
        }
        return null;
    }

    public function checkUserAccess($user) {
        $test = DB::table('userables')
                        ->where('user_id', $user->id)
                        ->where('userable_type', "Report")
                        ->where("object_id", $this->id)->first();
        if ($test) {
            return true;
        }
        return false;
    }
    public function postAddImg() {
        return null;
    }

}
