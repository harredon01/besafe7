<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cache;
use Carbon\Carbon; 

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
    protected $fillable = ['id', 'name', 'description', 'availability', 'hash', 'isActive','rating','ends_at','user_id'];
    
    protected $dates = [
        'created_at',
        'updated_at',
        'ends_at'
    ];
    
    public function isActive() {
        return $this->ends_at && Carbon::now()->lt($this->ends_at);
    }

    public function merchants() {
        return $this->belongsToMany('App\Models\Merchant')->withTimestamps();
    }
    public function groups() {
        return $this->belongsToMany('App\Models\Group')->withTimestamps();
    }

    public function category() {
        return $this->belongsToMany('App\Models\Category')->withTimestamps();
    }
    public function user() {
        return $this->belongsTo('App\Models\Category')->withTimestamps();
    }

    public function conditions() {
        return $this->hasMany('App\Models\Condition');
    }

    public function productVariants() {
        return $this->hasMany('App\Models\ProductVariant');
    }

    public function attributes() {
        return $this->belongsToMany('App\Models\Attribute', 'product_variant_attribute_option', 'product_id', 'attribute_id')->withTimestamps();
    }
    public function postAddImg() {
        return null;
    }

    public function checkAddImg($user,$type) {
        $merchant = $this->merchant;
        if ($merchant) {
            if ($merchant->user_id == $user->id) {
                Cache::forget('products_merchant_' . $merchant->id);
                return $this->id;
            }
        }
        return null;
    }

}
