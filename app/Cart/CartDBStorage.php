<?php

namespace App\Cart;

use App\Models\DatabaseStorage;
use Darryldecode\Cart\CartCollection;

class CartDBStorage {

    public function has($key) {
        return DatabaseStorage::find($key);
    }

    public function get($key) {
        if ($this->has($key)) {
            return new CartCollection(DatabaseStorage::find($key)->cart_data);
        } else {
            return [];
        }
    }

    public function put($key, $value) {
        if ($row = DatabaseStorage::find($key)) {
            // update
            $row->cart_data = $value;
            $row->save();
        } else {
            DatabaseStorage::create([
                'id' => $key,
                'cart_data' => $value
            ]);
        }
    }

}
