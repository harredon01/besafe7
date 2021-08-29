<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model {

    use HasFactory;

    protected $fillable = [
        'system_logable_id',
        'system_logable_type',
        'user_id',
        'guard_name',
        'module_name',
        'action',
        'old_value',
        'new_value',
        'ip_address'
    ];

}
