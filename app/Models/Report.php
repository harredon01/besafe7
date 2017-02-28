<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model {

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
    protected $fillable = ['merchant_id','city_id','region_id','country_id','name','type', 'email','telephone','address', 'description',
        'icon', 'lat','long', 'minimum','delivery_time','delivery_price','status','user_id',"private","hash","anonymous"];

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
    public function user() {
        return $this->hasOne('App\Models\User');
    }
}
