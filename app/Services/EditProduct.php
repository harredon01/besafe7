<?php

namespace App\Services;

use Validator;
use App\Models\Order;
use App\Models\User;
use App\Models\Item;
use App\Models\Product;
use App\Models\PaymentMethod;
use App\Models\Address;
use Mail;
use DB;

class EditProduct {

    /**
     * The Auth implementation.
     *
     */
    protected $auth;

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getProduct($slug) {
        $product = Product::where('slug', '=', $slug)->firstOrFail();
        $product->productVariants;
        return $product;

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getProducts(array $data) {
        
    }
}
