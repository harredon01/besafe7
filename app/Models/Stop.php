<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stop extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'stops';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['stop_order','arrival','amount','region_name','country_name','route_id','address_id'];

    public function route()
    {
        return $this->hasMany('App\Models\Route');
    }
    public function deliveries()
    {
        return $this->hasMany('App\Models\Delivery');
    }
    public function city() {
        return $this->belongsTo('App\Models\City');
    }
    public function region() {
        return $this->belongsTo('App\Models\Region');
    }
    public function country() {
        return $this->belongsTo('App\Models\Country');
    }
    public function address() {
        return $this->belongsTo('App\Models\OrderAddress');
    }

}
