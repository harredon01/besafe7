<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['order_id','type', 'gateway', 'description','payment_method', 'currency','user_id',
        'transaction_state', 'reference_sale', 'transaction_id', 'transaction_date','response_code' ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function order() {
        return $this->belongsTo('App\Models\Order');
    }
    public function payments() {
        return $this->belongsToMany('App\Models\Payment');
    }
}
