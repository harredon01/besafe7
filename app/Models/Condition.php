<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cart_conditions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','status', 'type', 'target', 'value', 'attributes', 'product_id', 'product_variant_id', 'coupon', 'city_id', 'region_id', 'country_id','order','isReusable','used'];

    public function product() {
        return $this->belongsTo('App\Models\Product');
    }

    public function productVariant() {
        return $this->belongsTo('App\Models\ProductVariant');
    }
    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

}
