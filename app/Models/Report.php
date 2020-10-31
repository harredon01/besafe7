<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Cache;
use DB;
use Carbon\Carbon; 
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class Report extends Model {

    use Searchable;
    use SpatialTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'reports';
    
    protected $spatialFields = [
        'position'
    ];
    protected $casts = [
        'attributes' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['city_id', 'region_id', 'country_id', 'name', 'type', 'email', 'telephone', 'address', 'description','attributes',
        'icon', 'lat', 'long', 'minimum', 'status', "private", "anonymous", "object", 'report_time', 'ends_at','plan','rating','rating_count'];
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
    public function users() {
        return $this->morphedByMany('App\Models\User', 'reportable')->withTimestamps();
    }
    public function merchants() {
        return $this->morphedByMany('App\Models\Merchant', 'reportable')->withTimestamps();
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

    public function groups() {
        return $this->belongsToMany('App\Models\Group')->withPivot('status')->withTimestamps();
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
    public function checkAdminAccess($user_id) {
        $test = DB::table('reportables')
                        ->where('reportable_id', $user_id)
                        ->where('reportable_type', "App\\Models\\User")
                        ->where("report_id", $this->id)->first();
        if ($test) {
            return true;
        }
        return false;
    }
    public function postAddImg() {
        return null;
    }
    protected static function booted() {
        static::saving(function ($report) {
            if($report->lat){
                $report->position = new Point($report->lat, $report->long); // (lat, lng)
            }
        });
    }

}
