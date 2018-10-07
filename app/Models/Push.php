<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Push extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'push';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'platform', 'object_id','credits'];
    
    public function user() {
        return $this->belongsTo('App\Models\User');
    }
}
