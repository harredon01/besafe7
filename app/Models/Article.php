<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'articles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','description','type','body','status','options','attributes','pagetitle','metadescription','slug','start_date','end_date','file_id','icon'];

    public function file() {
        return $this->belongsTo('App\Models\FileM');
    }
    public function categories() { 
        return $this->morphToMany('App\Models\Category', 'categorizable')->withTimestamps();
    }
    public function files() {
        return $this->morphMany('App\Models\FileM', 'fileable','type','trigger_id','categorizable_id');
    }
    public function files2() {
        return $this->morphMany('App\Models\FileM', 'fileable','type','trigger_id','id');
    }
}