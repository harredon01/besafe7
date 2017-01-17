<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'countries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','name', 'facebook_id',"code","area_code"];

    public function regions() {
        return $this->hasMany('App\Models\Region');
    }
    public function cities() {
        return $this->hasMany('App\Models\Region');
    }
    public function addresses() {
        return $this->hasMany('App\Models\Address');
    }

}
