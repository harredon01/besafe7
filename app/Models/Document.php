<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'documents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title','description','user_id','body','status','is_signed','signature_date','author_id'];
    public function signatures()
    {
        return $this->morphMany('App\Models\Signature', 'signable');
    }
    public function user() {
        return $this->hasOne('App\Models\User');
    }
    public function author() {
        return $this->hasOne('App\Models\User', 'author_id');
    }
    public function files() {
        return $this->morphMany('App\Models\FileM', 'fileable','type','trigger_id','id');
    }
}
