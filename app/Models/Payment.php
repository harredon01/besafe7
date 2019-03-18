<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['status', 'attributes', 'tax', 'total','transaction_cost', 'user_id', 'referenceCode','transactionId','responseCode', 'extras', 'order_id', 'address_id','text'];
    
    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function order() {
        return $this->belongsTo('App\Models\Order');
    }
    public function address() {
        return $this->belongsTo('App\Models\Address');
    }
    public function transactions() {
        return $this->belongsToMany('App\Models\Transaction');
    }
}
