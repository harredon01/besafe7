<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'order_addresses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','firstName', 'lastName', 'address', 'city_id', 'country_id', 'region_id','lat','long','type','order_id','address_id',
        'postal','phone'];

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
    public function order() {
        return $this->belongsTo('App\Models\Order');
    }
    public function address() {
        return $this->belongsTo('App\Models\Address');
    }
}