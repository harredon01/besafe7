<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FileM;
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
    protected $fillable = ['sku','product_id','ref2','type','description','is_digital','is_on_sale','is_shippable',
        'price','sale','tax','cost','quantity','min_quantity','weight','merchant_id','requires_authorization','attributes','isActive'];

    public function product() {
        return $this->belongsTo('App\Models\Product');
    }
    public function items() {
        return $this->hasMany('App\Models\Item');
    }
    public function plan() {
        return $this->hasOne('App\Models\Plan');
    }
    public function merchant() {
        return $this->hasOne('App\Models\Merchant');
    }
    public function conditions() {
        return $this->hasMany('App\Models\Condition');
    }
    public function variantAttributes() {
        return $this->belongsToMany('App\Models\Attribute','product_variant_attribute_option','product_variant_id','attribute_id')->withTimestamps();
    }
    public function attributeOptions() {
        return $this->belongsToMany('App\Models\AttributeOption','product_variant_attribute_option','product_variant_id','attribute_option_id')->withTimestamps();
    }
    
    public function getCartImg() {
        $file = FileM::where("type","Variant")->where("trigger_id", $this->id)->first();
        if($file){
            return $file;
        }
        $file = FileM::where("type","Product")->where("trigger_id", $this->product_id)->first();
        if($file){
            return $file;
        }
        return null;
    }
    public function getActivePrice() {
        if($this->is_on_sale){
            return $this->sale;
        }
        return $this->price;
    }
}
