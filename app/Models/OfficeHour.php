<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeHour extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'office_hours';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['day','open','close'];


    public function merchant() {
        return $this->belongsTo('App\Models\Merchant');
    }

}
