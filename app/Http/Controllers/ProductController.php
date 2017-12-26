<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductController extends Controller {

    /**
     * The edit profile implementation.
     *
     */
    protected $editOrder;

    public function __construct( ) {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getProduct($hash) {
        $product = Product::where('hash', '=', $hash)->firstOrFail();
        $product->productVariants;
        return view('products.products', ['products' => $product]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getProducts() {
        $products = Product::with('productVariants')->paginate(8);
        return view('products.products', ['products' => $products]);
    }

}
