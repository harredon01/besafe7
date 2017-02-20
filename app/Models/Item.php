<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','sku','ref2','price','quantity','user_id','product_variant_id','order_id','attributes'];


    public function productVariant() {
        return $this->belongsTo('App\Models\ProductVariant');
    }
    public function order() {
        return $this->belongsTo('App\Models\Order');
    }
    public function user() {
        return $this->belongsTo('App\Models\User');
    }
}
