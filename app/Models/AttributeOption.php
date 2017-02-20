<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeOption extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'attribute_options';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'valueS','valueI','attribute_id'];


    public function attributes() {
        return $this->belongsTo('App\Models\Attribute');
    }
    public function productVariants() {
        return $this->belongsToMany('App\Models\ProductVariant','product_variant_attribute_option','attribute_option_id','product_variant_id')->withTimestamps();
    }
}
