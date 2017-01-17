<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','name','code','lat','long','region_id','country_id', 'facebook_id', 'facebook_country_id'];

    public function country() {
        return $this->belongsTo('App\Models\Country');
    }
    public function region() {
        return $this->belongsTo('App\Models\Region');
    }
    public function addresses() {
        return $this->hasMany('App\Models\Address');
    }

}
