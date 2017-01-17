<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medical extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'medicals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['gender','birth', 'weight','blood_type', 'antigent', 'surgical_history','obstetric_history', 
        'medications','alergies', 'immunization_history','medical_encounters','prescriptions','emergency_name', 'relationship',
        'number','other', 'user_id','eps'];
    
    public function user() {
        return $this->hasOne('App\Models\User');
    }
}
