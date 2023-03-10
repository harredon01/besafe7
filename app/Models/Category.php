<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','type','level','description','parent_id','merchant_id'];


    public function parentCategory() {
        return $this->belongsTo('App\Models\Category', 'foreign_key', 'parent_id');
    }
    public function children() {
        return $this->hasMany('App\Models\Category', 'parent_id', 'id');
    }
    public function merchants() {
        return $this->morphedByMany('App\Models\Merchant', 'categorizable')->withTimestamps();
    }
    public function reports() {
        return $this->morphedByMany('App\Models\Report', 'categorizable')->withTimestamps();
    }
    public function products() {
        return $this->morphedByMany('App\Models\Product', 'categorizable')->withTimestamps();
    }
    public function articles() {
        return $this->morphedByMany('App\Models\Article', 'categorizable')->withTimestamps();
    }
    public function cargos() {
        return $this->hasMany('App\Models\Cargo');
    }
    public function vehicles() {
        return $this->hasMany('App\Models\Vehicles');
    }

}
