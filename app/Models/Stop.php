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
    protected $fillable = ['stop_order','arrival','city_name','region_name','country_name'];

    public function route()
    {
        return $this->hasMany('App\Models\Route');
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

}
