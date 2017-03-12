<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','status','avatar','code', 'admin_id','is_private'];

    public function users() {
        return $this->belongsToMany('App\Models\User')->withPivot('color')->withTimestamps();
    }
    public function subscription() {
        return $this->hasOne('App\Models\Subscription');
    }
    public function messages() {
        return $this->morphMany('App\Models\Message','messageable');
    }
}
