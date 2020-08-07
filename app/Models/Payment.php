<?php

namespace App\Models;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['status', 'attributes', 'subtotal', 'tax', 'total', 'transaction_cost', 'user_id', 'referenceCode', 'transactionId', 'responseCode', 'extras', 'order_id', 'address_id', 'text'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function order() {
        return $this->belongsTo('App\Models\Order');
    }

    public function address() {
        return $this->belongsTo('App\Models\Address');
    }

    public function transactions() {
        return $this->belongsToMany('App\Models\Transaction');
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

}
