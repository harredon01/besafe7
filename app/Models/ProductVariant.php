<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model {

	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'product_variant';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['sku','product_id','ref2','is_digital','is_shippable','price','sale','tax','quantity'];

    public function product() {
        return $this->belongsTo('App\Models\Product');
    }
    public function items() {
        return $this->hasMany('App\Models\Items');
    }
    public function plan() {
        return $this->hasOne('App\Models\Plan');
    }
    public function conditions() {
        return $this->hasMany('App\Models\Condition');
    }
    public function attributes() {
        return $this->belongsToMany('App\Models\Attribute','product_variant_attribute_option','product_variant_id','attribute_id')->withTimestamps();
    }
    public function attributeOptions() {
        return $this->belongsToMany('App\Models\AttributeOption','product_variant_attribute_option','product_variant_id','attribute_option_id')->withTimestamps();
    }
}
