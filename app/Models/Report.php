<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

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
        'icon', 'lat', 'long', 'minimum', 'status', 'user_id', "private", "hash", "anonymous","object",'report_time','group_id'];
    protected $hidden = ['user_id'];

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

}
