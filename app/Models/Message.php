<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model {

	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['message','messageable_type','user_id','priority','status','messageable_id','is_public','target_id'];
    
    public function messageable()
    {
        return $this->morphTo();
    }
    

}
