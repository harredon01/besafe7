<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vehicles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'axis', 'plates', 'vin_number', 'make', 'model', 'color', 'image',
        'full_length', 'length_unit', 'horse_power', 'description',
        'cargo_width', 'cargo_length', 'cargo_height', 'cargo_weight',
        'category_id'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function category() {
        return $this->belongsTo('App\Models\Category');
    }
    public function routes() {
        return $this->hasMany('App\Models\Route');
    }

}
