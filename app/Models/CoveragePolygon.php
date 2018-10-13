<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoveragePolygon extends Model
{
    //
    protected $table = 'coverage_polygons';
    
    protected $fillable = ['coverage','country_id','region_id','city_id'];
}
