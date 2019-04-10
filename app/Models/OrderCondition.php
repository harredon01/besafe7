<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCondition extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'order_conditions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','status', 'type', 'target', 'value', 'attributes','coupon','order','order_id','condition_id','isReusable','used','total'];

    public function order() {
        return $this->belongsTo('App\Models\Order');
    }
}
