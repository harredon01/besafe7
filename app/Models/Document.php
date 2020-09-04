<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'title',
        'description',
        'body',
        'status',
        'type',
        'updated_at'
    ];
    protected $casts = [
        'body' => 'array',
    ];
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
    public function signatures()
    {
        return $this->morphMany('App\Models\Signature', 'signable');
    }
    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function author() {
        return $this->belongsTo('App\Models\User', 'author_id');
    }
    public function files() {
        return $this->morphMany('App\Models\FileM', 'fileable','type','trigger_id','id');
    }
}
