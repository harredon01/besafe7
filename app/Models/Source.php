<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sources';

    /**
     * The attriSourcebutes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','source','client_id','type','gateway', 'extra','is_active','has_default'];
    
    //protected $hidden = ['source'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }
}
