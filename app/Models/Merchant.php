<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model {

/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'merchants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['merchant_id','city_id','region_id','country_id','name','type', 'email','telephone','address', 'description',
        'icon', 'lat','long', 'minimum','delivery_time','delivery_price','status','user_id',"hash",'private','group_id'];


    public function products() {
        return $this->hasMany('App\Models\Product');
    }
    public function hours() {
        return $this->hasMany('App\Models\OfficeHour');
    }
    public function city() {
        return $this->hasOne('App\Models\City');
    }
    public function group() {
        return $this->belongsTo('App\Models\Group');
    }
    public function region() {
        return $this->hasOne('App\Models\Region');
    }
    public function country() {
        return $this->hasOne('App\Models\Country');
    }
    public function orders() {
        return $this->hasMany('App\Models\Order');
    }
    public function categories() {
        return $this->belongsToMany('App\Models\Category')->withTimestamps();
    }
    public function paymentMethods() {
        return $this->belongsToMany('App\Models\PaymentMethod', 'merchant_payment_methods', 'merchant_id', 'payment_method_id')->withTimestamps();
    }
    public function user() {
        return $this->hasOne('App\Models\User');
    }

}
