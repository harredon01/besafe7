<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'regions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name','code', 'facebook_id', 'facebook_country_id', 'country_id'];

    public function country() {
        return $this->belongsTo('App\Models\Country');
    }
    public function cities() {
        return $this->hasMany('App\Models\City');
    }
    public function addresses() {
        return $this->hasMany('App\Models\Address');
    }

}
