<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cache;
use Carbon\Carbon; 

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
        'icon', 'lat','long', 'minimum','delivery_time','delivery_price','status','user_id',"hash",'private','group_id','rating','ends_at','plan'];
    protected $dates = [
        'created_at',
        'updated_at',
        'ends_at'
    ];
    
    public function isActive() {
        return $this->ends_at && Carbon::now()->lt($this->ends_at);
    }

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
    public function checkAddImg($user,$type) {
        if ($this->user_id == $user->id) {
            Cache::forget('Merchant_' . $this->id); 
            return $this->id;
        }
        return null;
    }
    public function checkUserAccess($user) {
        $test = DB::table('userables')
                        ->where('user_id', $user->id)
                        ->where('userable_type', "Merchant")
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
