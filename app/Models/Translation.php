<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','code','language','value','body'];

}
