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
    protected $fillable = ['name','description','body','status','options','attributes','pagetitle','metadescription','slug','start_date','end_date','file_id'];

    public function file() {
        return $this->belongsTo('App\Models\FileM');
    }
}