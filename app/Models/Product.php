<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {

	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','merchant_id', 'name','description','slug','isActive'];


    public function merchant() {
        return $this->belongsTo('App\Models\Merchant');
    }
    public function category() {
        return $this->belongsToMany('App\Models\Category')->withTimestamps();
    }
    public function conditions() {
        return $this->hasMany('App\Models\Condition');
    }
    public function productVariants() {
        return $this->hasMany('App\Models\ProductVariant');
    }
    public function attributes() {
        return $this->belongsToMany('App\Models\Attribute','product_variant_attribute_option','product_id','attribute_id')->withTimestamps();
    }
}
