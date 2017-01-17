<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricNotification extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'historic_notification';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['text','type','status','priority','subject','payload','trigger_id','user_id'];
    
    public function user() {
        return $this->belongsTo('App\Models\User');
    }

}
