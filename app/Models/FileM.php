<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileM extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type','trigger_id','file','extension','user_id'];


    public function article() {
	return $this->hasMany('App\Models\Article');
    }	
}
