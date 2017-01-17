<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payment_methods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'price', 'tax', 'total', 'quantity'];

    public function merchants() {
        return $this->belongsToMany('App\Models\Merchant', 'merchant_payment_methods', 'merchant_id', 'payment_method_id')->withTimestamps();
    }

    public function orders() {
        return $this->hasMany('App\Models\Order');
    }

}
