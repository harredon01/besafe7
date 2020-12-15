<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Bookings\Traits\Bookable;
use App\Traits\FullTextSearch;
use Illuminate\Support\Facades\Storage;
use App\Models\FileM;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Cache;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Rate;
use Carbon\Carbon;
use DB;

class Merchant extends Model {

    use Bookable;
    use FullTextSearch;
    use SpatialTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'merchants';
    protected $spatialFields = [
        'position'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['merchant_id', 'city_id', 'region_id', 'country_id', 'name', 'type', 'email', 'telephone', 
        'address', 'description', 'attributes','facebook','instagram','twitter','keywords',
        'icon', 'lat', 'long', 'minimum', 'delivery_time', 'delivery_price', 'status', 'private', 'ends_at', 'plan', 'url', 'rating', 'rating_count', 'unit_cost', 'base_cost', 'unit', 'currency'];
    protected $dates = [
        'created_at',
        'updated_at',
        'ends_at'
    ];
    protected $casts = [
        'attributes' => 'array',
    ];
    protected $searchable = [
        'name',
        'type',
        'email',
        'description',
        'attributes',
        'keywords'
    ];

    public function isActive() {
        return $this->ends_at && Carbon::now()->lt($this->ends_at);
    }

    public function products() {
        return $this->belongsToMany('App\Models\Product')->withTimestamps();
    }

    public function availabilities() {
        return $this->morphMany('App\Models\Availability', 'available', 'bookable_type', 'bookable_id', 'categorizable_id');
    }

    public function availabilities2() {
        return $this->morphMany('App\Models\Availability', 'available', 'bookable_type', 'bookable_id', 'id');
    }
    
    public function files() {
        return $this->morphMany('App\Models\FileM', 'fileable', 'type', 'trigger_id', 'id');
    }
    
    public function ratings() {
        return $this->morphMany('App\Models\Rating', 'rateable', 'type', 'object_id', 'id');
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
        return $this->morphToMany('App\Models\Category', 'categorizable')->withTimestamps();
    }
    public function reports() {
        return $this->morphToMany('App\Models\Report', 'reportable')->withTimestamps();
    }

    public function paymentMethods() { 
        return $this->belongsToMany('App\Models\PaymentMethod', 'merchant_payment_methods', 'merchant_id', 'payment_method_id')->withTimestamps();
    }

    public function users() {
        return $this->belongsToMany('App\Models\User');
    }

    public function checkAddImg($user, $type) {
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

    public function postAddImg($user, $type, $filename) {
        if ($type == "Merchant_avatar") {
            Storage::delete($this->icon);
            FileM::where("file", $this->icon)->delete();
            $this->icon = $filename;
            $this->save();
        }
    }

    /**
     * Get the booking model name.
     *
     * @return string
     */
    public static function getBookingModel(): string {
        return Booking::class;
    }

    /**
     * Get the rate model name.
     *
     * @return string
     */
    public static function getRateModel(): string {
        return Rate::class;
    }

    /**
     * Get the availability model name.
     *
     * @return string
     */
    public static function getAvailabilityModel(): string {
        return Availability::class;
    }

    protected static function booted() {
        static::saving(function ($merchant) {
            if($merchant->lat){
                $merchant->position = new Point($merchant->lat, $merchant->long); // (lat, lng)
            }
        });
    }

}
