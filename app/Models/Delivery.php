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
    protected $fillable = ['type_id','starter_id','main_id','dessert_id','code','observation','address_id',
        'group_id','user_id','shipping','delivery','details'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function group() {
        return $this->belongsTo('App\Models\Group');
    }
    public function routes() {
        return $this->belongsToMany('App\Models\Route');
    }
    public function address() {
        return $this->belongsTo('App\Models\Address');
    }
    public function stops() {
        return $this->belongsToMany('App\Models\Stop');
    }
}
