<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'plans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['plan_id', 'name','type', 'duration', 'level','interval','amount'];

    public function productVariant() {
        return $this->belongsTo('App\Models\ProductVariant');
    }
    public function subscription() {
        return $this->hasOne('App\Models\Subscription');
    }
}
