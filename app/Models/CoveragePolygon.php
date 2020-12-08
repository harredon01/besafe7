<?php

namespace App\Models;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

class CoveragePolygon extends Model
{
    //
    use SpatialTrait;
    protected $table = 'coverage_polygons';
    
    protected $fillable = ['coverage','country_id','region_id','city_id','merchant_id','address_id','lat','long','provider','description'];
    /**
     * The attributes that are spatial fields.
     *
     * @var array
     */
    protected $spatialFields = [
        'geometry'
    ];
    
    public function merchant() {
        return $this->belongsTo('App\Models\Merchant');
    }
    
    public function address() {
        return $this->belongsTo('App\Models\Address');
    }
    public function orderAddresses() {
        return $this->hasMany('App\Models\OrderAddress', 'polygon_id');
    }
}
