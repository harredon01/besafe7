<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['order_id','type', 'description', 'state','paymentNetworkResponseCode', 'paymentNetworkResponseErrorMessage', 'trazabilityCode', 'pendingReason', 'responseCode', 'errorCode',
        'operationDate','transactionDate','transactionTime','order_id','user_id','extras','transactionId','authorizationCode','responseMessage','currency','gateway'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function order() {
        return $this->belongsTo('App\Models\Order');
    }
}
