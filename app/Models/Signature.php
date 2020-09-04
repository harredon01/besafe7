<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'signatures';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    //
    public function user() {
        return $this->hasOne('App\Models\User');
    }
    public function signable()
    {
        return $this->morphTo();
    }
}
