<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'locations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['lat','long','status','name','islast','trip','user_id','speed','activity','battery','report_time','phone'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

}
