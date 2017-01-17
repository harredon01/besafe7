<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cargos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['description','weight','length','height','width','status','arrival','image','category_id','from_city_name','from_region_name','from_country_name','to_city_name','to_region_name','to_country_name'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function category() {
        return $this->belongsTo('App\Models\Category');
    }
    public function route() {
        return $this->belongsTo('App\Models\Route');
    }
    public function fromCity() {
        return $this->belongsTo('App\Models\City','from_city_id');
    }
    public function fromRegion() {
        return $this->belongsTo('App\Models\Region','from_region_id');
    }
    public function fromCountry() {
        return $this->belongsTo('App\Models\Country','from_country_id');
    }
    public function toCity() {
        return $this->belongsTo('App\Models\City','to_city_id');
    }
    public function toRegion() {
        return $this->belongsTo('App\Models\Region','to_region_id');
    }
    public function toCountry() {
        return $this->belongsTo('App\Models\Country','to_country_id');
    }
}