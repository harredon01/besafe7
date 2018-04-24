<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'subscriptions';
    
    protected $dates = [
        'created_at',
        'updated_at',
        'trial_ends_at',
        'ends_at',
        'deleted_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','source_id','type','status', 'name','other','plan_id','plan','gateway','client_id','object_id','interval','interval_type','level','quantity','trial_ends_at','ends_at'];


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
    public function isActive()
    {
        return $this->ends_at && Carbon::now()->lt($this->ends_at);
    }
}
