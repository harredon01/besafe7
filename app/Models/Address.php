<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class Address extends Model {
    
    use SpatialTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'addresses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','name', 'city', 'address', 'city_id', 'country_id', 'region_id','lat','long','type',
        'postal','phone','is_default','notes'];
    
    protected $spatialFields = [
        'position'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function region() {
        return $this->belongsTo('App\Models\Region');
    }
    public function country() {
        return $this->belongsTo('App\Models\Country');
    }
    public function city() {
        return $this->belongsTo('App\Models\City');
    }
    protected static function booted() {
        static::saving(function ($address) {
            if($address->lat){
                $address->position = new Point($address->lat, $address->long); // (lat, lng)
            }
        });
    }

}
