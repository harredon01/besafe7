<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\EditProduct;
use App\Models\Merchant;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductController extends Controller {

    /**
     * The edit profile implementation.
     *
     */
    protected $editProduct;

    public function __construct(EditProduct $editProduct ) {
        $this->editProduct = $editProduct;
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
    public function getProductsMerchant($slug,$page) {
        $merchant = Merchant::where("url",$slug)->first();
        $products =$this->editProduct->getProductsMerchant(null, $merchant->id, $page);
        $productsCategory = $this->editProduct->buildProducts($products, $merchant->id);
        return view('products.productsMerchant', ['categories' => $productsCategory,"merchant"=>$merchant->id]);
    }

}
