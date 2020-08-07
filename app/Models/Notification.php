<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
class Notification extends Model {

	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['message','type','status','subject','subject_es','payload','trigger_id','object','user_id','user_status','notification_id'];
    
    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }
}
