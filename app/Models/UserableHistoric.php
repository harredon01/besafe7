<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserableHistoric extends Model {


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'userables_historic';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['userable_type','userable_id','object_id'];
    
    public function user() {
        return $this->hasOne('App\Models\User');
    }


}
