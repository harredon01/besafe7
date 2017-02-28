<?php namespace App\Models;

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
    protected $fillable = ['status','subtotal','shipping','discount','tax','total','comments','total','billing_address_id',
        'shipping_address_id','user_id','is_digital','is_shippable'];


    public function merchant() {
        return $this->belongsTo('App\Models\Merchant');
    }
    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function items() {
        return $this->hasMany('App\Models\Item');
    }
    public function paymentMethod() {
        return $this->belongsTo('App\Models\PaymentMethod');
    }
    public function conditions() {
        return $this->belongsToMany('App\Models\Condition','condition_order','order_id','condition_id');
    }

}
