<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'blocks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['country_id','network'];

    public function country() {
        return $this->belongsTo('App\Models\Country');
    }
}