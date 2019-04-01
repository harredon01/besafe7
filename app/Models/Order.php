<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['status', 'owner_status', 'merchant_status', 'payment_status','attributes','execution_status', 'subtotal', 
        'shipping', 'discount', 'tax', 'total', 'comments', 'total', 'user_id', 'supplier_id','user_id','is_editable',
        'is_digital', 'is_shippable', 'requires_authorization', 'referenceCode', 'extras','is_recurring','recurring_type','recurring_value'];
    protected $hidden = [ 'supplier_id', 'object_id', 'type'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function merchant() {
        return $this->belongsTo('App\Models\Merchant');
    }

    public function items() {
        return $this->hasMany('App\Models\Item');
    }

    public function transactions() {
        return $this->hasMany('App\Models\Transaction');
    }

    public function orderAddresses() {
        return $this->hasMany('App\Models\OrderAddress');
    }
    public function orderConditions() {
        return $this->hasMany('App\Models\OrderCondition');
    }

    public function subscriptions() {
        return $this->hasMany('App\Models\Subscription');
    }
    public function payments() {
        return $this->hasMany('App\Models\Payment');
    }

    public function paymentMethod() {
        return $this->belongsTo('App\Models\PaymentMethod');
    }

}
