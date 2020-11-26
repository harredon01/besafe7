<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'favorites';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'favoritable_type', 'favoritable_id','score'];
    
    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    /**
     * Get the owning favoritable model.
     */
    public function favoritable()
    {
        return $this->morphTo();
    }
}
