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
    protected $fillable = ['name','sku','ref2','price','priceSum','priceConditions','priceSumConditions','tax','cost','paid_status','fulfillment',
        'is_subscription','quantity','user_id','product_variant_id','order_id','requires_authorization','merchant_id','attributes'];


    public function productVariant() {
        return $this->belongsTo('App\Models\ProductVariant');
    }
    public function order() {
        return $this->belongsTo('App\Models\Order');
    }
    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function merchant() {
        return $this->belongsTo('App\Models\Merchant');
    }
    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
