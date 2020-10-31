<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\EditProduct;
use App\Models\Merchant;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ProductController extends Controller {

    /**
     * The edit profile implementation.
     *
     */
    protected $editProduct;

    public function __construct(EditProduct $editProduct) {
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
        return view(config("app.views") . '.products.products', ['products' => $product]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getProducts(Request $request, $category) {
        $category = Category::where('url', $category)->first();

        $data = $request->all();
        $data['category_id'] = $category->id;
        $data['includes'] = "categories,files";
        $results = $this->editProduct->getProductsMerchant($data);

        $visualResults = $this->editProduct->buildProducts($results);

        if (!$visualResults) {
            $visualResults = [];
        }
        //dd($visualResults);
        return view(config("app.views") . '.products.productsMerchant', ["categories" => $visualResults]);
    }

    public function getProductsMerchant($slug, $page) {
        $merchant = Merchant::where("url", $slug)->first();
        $products = $this->editProduct->getProductsMerchant(null, $merchant->id, $page);
        $productsCategory = $this->editProduct->buildProducts($products, $merchant->id);
        return view(config("app.views") . '.products.productsMerchant', ['categories' => $productsCategory, "merchant" => $merchant->id]);
    }

}
