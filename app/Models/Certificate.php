<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'certificates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','public_key', 'is_active','expiration'];
    //
    public function user() {
        return $this->hasOne('App\Models\User');
    }
}
