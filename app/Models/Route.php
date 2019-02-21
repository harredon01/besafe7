<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'routes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['description','weight','length','height','width','unit_price','unit_cost','unit','status','vehicle_id','coverage','availability','type','provider'];

    public function vehicle() {
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function cargos()
    {
        return $this->hasMany('App\Models\Cargo');
    }
    public function stops()
    {
        return $this->hasMany('App\Models\Stop');
    }
    public function deliveries()
    {
        return $this->belongsToMany('App\Models\Delivery');
    }
}
