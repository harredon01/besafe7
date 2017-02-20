<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'attributes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name','description','type'];

    public function attributeOptions() {
        return $this->hasMany('App\Models\AttributeOption');
    }
    public function products() {
        return $this->belongsToMany('App\Models\Product','product_variant_attribute_option','attribute_id','product_id')->withTimestamps();
    }
    public function productVariants() {
        return $this->belongsToMany('App\Models\ProductVariant','product_variant_attribute_option','attribute_id','product_variant_id')->withTimestamps();
    }
}
