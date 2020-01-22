<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Bookings\Traits\Bookable;
use Laravel\Scout\Searchable;
use Cache;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Rate;
use Carbon\Carbon; 
use DB;
class Merchant extends Model {
    
    use Bookable;use Searchable;

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
    protected $fillable = ['merchant_id','city_id','region_id','country_id','name','type', 'email','telephone','address', 'description','attributes',
        'icon', 'lat','long', 'minimum','delivery_time','delivery_price','status','user_id','private','ends_at','plan','url','rating','rating_count'];
    protected $dates = [
        'created_at',
        'updated_at',
        'ends_at'
    ];
    protected $casts = [
        'attributes' => 'array',
    ];
    
    public function isActive() {
        return $this->ends_at && Carbon::now()->lt($this->ends_at);
    }

    public function products() {
        return $this->belongsToMany('App\Models\Product')->withTimestamps();
    }
    public function hours() {
        return $this->hasMany('App\Models\OfficeHour');
    }
    public function polygons() {
        return $this->hasMany('App\Models\CoveragePolygon');
    }
    public function deliveries() {
        return $this->hasMany('App\Models\Delivery');
    }
    public function city() {
        return $this->hasOne('App\Models\City');
    }
    public function groups() {
        return $this->belongsToMany('App\Models\Group')->withPivot('status')->withTimestamps();
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
    public function items() {
        return $this->hasMany('App\Models\Item');
    }
    public function categories() {
        return $this->belongsToMany('App\Models\Category')->withTimestamps();
    }
    public function paymentMethods() {
        return $this->belongsToMany('App\Models\PaymentMethod', 'merchant_payment_methods', 'merchant_id', 'payment_method_id')->withTimestamps();
    }
    public function users() {
        return $this->belongsToMany('App\Models\User');
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
    public function checkAdminAccess($user_id) {
        $test = DB::table('merchant_user')
                        ->where('user_id', $user_id)
                        ->where("merchant_id", $this->id)->first();
        if ($test) {
            return true;
        }
        return false;
    }
    public function postAddImg() {
        return null;
    }
    
    /**
     * Get the booking model name.
     *
     * @return string
     */
    public static function getBookingModel(): string
    {
        return Booking::class;
    }

    /**
     * Get the rate model name.
     *
     * @return string
     */
    public static function getRateModel(): string
    {
        return Rate::class;
    }

    /**
     * Get the availability model name.
     *
     * @return string
     */
    public static function getAvailabilityModel(): string
    {
        return Availability::class;
    }

}
