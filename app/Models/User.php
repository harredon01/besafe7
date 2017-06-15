<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Encryptable;
class User extends Authenticatable
{
//    use HasApiTokens, Notifiable;
    use HasApiTokens, Notifiable,Encryptable;

    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','firstName','lastName','name','gender', 'area_code', 'cellphone',
        'docType', 'docNum','username', 'email', 'avatar','password','language'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token','notify_location','is_alerting','is_tracking','alert_type','docType', 'docNum','write_report',
       'emailNotifications','pushNotifications','platform','token','green','red','trip','hash','token','platform','card_brand','card_last_four'];
    protected $encryptable = [
        'green',
        'red',
        'card_brand',
        'card_last_four'
    ];
    public function userSocials() {
        return $this->hasMany('App\Models\UserSocial');
    }
    public function merchants() {
        return $this->belongsToMany('App\Models\Merchant')->withTimestamps();
    }
    public function addresses() {
        return $this->hasMany('App\Models\Address');
    }
    public function locations() {
        return $this->hasMany('App\Models\Location');
    }
    public function orders() {
        return $this->hasMany('App\Models\Order');
    }
    public function notifications() {
        return $this->hasMany('App\Models\Notification');
    }
    public function groups() {
        return $this->belongsToMany('App\Models\Group')->withPivot('color')->withTimestamps();
    }
    public function vehicles() {
        return $this->hasMany('App\Models\Vehicle');
    }
    public function routes() {
        return $this->hasMany('App\Models\Route');
    }
    public function cargos() {
        return $this->hasMany('App\Models\Cargo');
    }
    public function items() {
        return $this->hasMany('App\Models\Item');
    }
    public function subscriptions() {
        return $this->hasMany('App\Models\Subscription');
    }
    public function sources() {
        return $this->hasMany('App\Models\Source');
    }
    public function medical() {
        return $this->hasOne('App\Models\Medical');
    }
    public function messages() {
        return $this->morphMany('App\Models\Message','messageable');
    }
}
