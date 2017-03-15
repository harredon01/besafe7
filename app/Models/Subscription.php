<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','type','status', 'name','other','plan_id','plan','quantity','trial_ends_at','ends_at'];


    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function group() {
        return $this->belongsTo('App\Models\Group');
    }
    public function order() {
        return $this->belongsTo('App\Models\Order');
    }
    public function plan() {
        return $this->belongsTo('App\Models\Plan');
    }
}
