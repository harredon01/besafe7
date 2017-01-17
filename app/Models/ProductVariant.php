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
    protected $fillable = ['sku','product_id','ref2','price','sale','tax','quantity'];

    public function product() {
        return $this->belongsTo('App\Models\Product');
    }
    public function items() {
        return $this->hasMany('App\Models\Product');
    }
    public function conditions() {
        return $this->hasMany('App\Models\Condition');
    }
}
