<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = ['firstName','lastName','email','cellphone','message','address','description','type','attributes'];
    protected $casts = [
        'attributes' => 'array',
    ];
}
