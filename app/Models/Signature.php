<?php

namespace App;

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
    protected $fillable = ['user_id','signature','auto_validate','signable_id','signable_type'];
    //
    public function user() {
        return $this->hasOne('App\Models\User');
    }
    public function signable()
    {
        return $this->morphTo();
    }
}
