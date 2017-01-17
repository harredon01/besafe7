<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class UserSocial extends Model implements AuthenticatableContract {

    use Authenticatable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users_social';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['username', 'avatar', 'email', 'provider', 'provider_id', 'user_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['remember_token', 'provider', 'provider_id', 'user_id'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

}
