<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'deliveries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type_id','starter_id','main_id','dessert_id','code','observation','group_id','user_id','route_id','delivery'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function group() {
        return $this->belongsTo('App\Models\Group');
    }
    public function route() {
        return $this->belongsTo('App\Models\Route');
    }
}