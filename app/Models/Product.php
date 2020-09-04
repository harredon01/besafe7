<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cache;
use Carbon\Carbon;
use DB;

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
    protected $fillable = ['id', 'name', 'description', 'hash', 'isActive'];
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
    public function categories() {
        return $this->belongsToMany('App\Models\Category')->withTimestamps();
    }

    public function groups() {
        return $this->belongsToMany('App\Models\Group')->withTimestamps();
    }

    public function category() {
        return $this->belongsToMany('App\Models\Category')->withTimestamps();
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTimestamps();
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
        $this->clearCache();
    }

    public function checkAddImg($user, $type) {
        if ($this->user_id == $user->id) {
            return $this->id;
        }
        return null;
    }


    public function clearCache() {
        $access = false;
        $merchants = DB::select('SELECT 
                                            DISTINCT(m.id) as merchant_id
                                        FROM
                                            merchants m
                                        WHERE
                                                m.status = "active"
                                                AND m.id IN ( 
                                                SELECT merchant_id from merchant_product WHERE product_id = ? 
                                                )

                ;', [$this->id]);
        foreach ($merchants as $value) {
            Cache::forget('products_merchant_' . $value->merchant_id . "_1");
            Cache::forget('products_merchant_' . $value->merchant_id . "_2");
            Cache::forget('products_merchant_' . $value->merchant_id . "_3");
        }
        $groups = DB::select('SELECT 
                                            DISTINCT(g.id) as group_id
                                        FROM
                                            groups g
                                                JOIN
                                            group_merchant gm ON gm.group_id = g.id
                                                JOIN
                                            merchants m ON gm.merchant_id = m.id
                                        WHERE
                                            m.status = "active"
                                                AND g.status = "active"
                                                AND m.status = "active"
                                                AND gm.merchant_id IN ( 
                                                SELECT merchant_id from merchant_product WHERE product_id = ? 
                                                )

                    ;', [$this->id]);
        foreach ($groups as $value) {
            Cache::forget('products_group_' . $value->group_id . "_1");
            Cache::forget('products_group_' . $value->group_id . "_2");
            Cache::forget('products_group_' . $value->group_id . "_3");
        }
        Cache::forget('products_' . $this->id);
    }

}
