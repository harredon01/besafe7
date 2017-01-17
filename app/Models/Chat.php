<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model {

	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','sku','ref2','price','tax','total', 'quantity','product_id','order_id'];


    public function product() {
        return $this->belongsTo('App\Models\Product');
    }
    public function order() {
        return $this->belongsTo('App\Models\Order');
    }

}
