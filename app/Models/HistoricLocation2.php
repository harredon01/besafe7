<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricLocation2 extends Model {
    
    use SpatialTrait;
    
    protected $spatialFields = [
        'position'
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'historic_location2';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['lat','long','status','name','islast','trip','user_id','speed','activity','battery','accuracy','report_time','phone',
        'heading','altitude','confidence','is_moving','is_charging','uuid'];
    
    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    protected static function booted() {
        static::saving(function ($location) {
            if($location->lat){
                $location->position = new Point($location->lat, $location->long); // (lat, lng)
            }
        });
    }

}
