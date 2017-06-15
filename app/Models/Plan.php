<?php

namespace App\Models;

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
    protected $fillable = ['plan_id', 'name','type', 'duration', 'interval','interval_type','gateway'];

    public function subscription() {
        return $this->hasMany('App\Models\Subscription');
    }
}
