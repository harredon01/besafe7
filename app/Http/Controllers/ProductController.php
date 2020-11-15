<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\EditProduct;
use App\Models\Merchant;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use DB;

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
        $product = Product::where('slug', $hash)->with(['files', 'ratings', 'productVariants', 'merchants', 'categories'])->first();
        $data = [];
        $categories = $product->categories;
        $products = [];
        if (count($categories) > 0) {
            $data['category_id'] = $categories[0]->id;
            $data['includes'] = "files";
            $data['per_page'] = 6;
            $results = $this->editProduct->getProductsMerchant($data);
            $products = $this->editProduct->buildProducts($results);
        }
        $results = ['product' => $product, 'related_products' => $products];
        return view(config("app.views") . '.products.detail', ['data' => $results]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function textSearch(Request $request) {
        $data = $request->all();
        if (!isset($data['q'])) {
            $data['q'] = "";
        }
        $textSearchQuery = Product::search($data['q'])->whereIn('id', function($query) use($data) {
                    $query = $this->editProduct->baseProductQuery($data, $query);
                    $query->from('products');
                    $query->select('products.id');
                })->where('isActive', true);
        $textSearchCountQuery = $textSearchQuery;
        $total = $textSearchCountQuery->count();
        $textSearchQuery->with(["merchants", 'files', 'variants']);
        $pageRes = $this->editProduct->paginateQueryFromArray($textSearchQuery, $data);
        $textSearchQuery = $pageRes['query'];
        $products = $textSearchQuery->get();
        foreach ($products as $value) {
            $value->description = nl2br($value->description);
            $value->description = str_replace(array("\r", "\n"), '', $value->description);
            foreach ($value->variants as $item) {
                $item->attributes = json_decode($item->attributes);
            }
        }
        $cat = ["products" => $products, 'id' => -1];
        $results = [];
        $results['categories'] = [$cat];
        $results['page'] = $pageRes['page'];
        $results['last_page'] = ceil($total / $pageRes['per_page']);
        $results['per_page'] = $pageRes['per_page'];
        $results['total'] = $total;
        $results['category'] = [];
        $results['side_categories'] = [];
        return view(config("app.views") . '.products.productsMerchant', ["data" => $results]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getProducts(Request $request, $category) {
        $category = Category::where('url', $category)->first();

        $data = $request->all();
        $side_categories = [];

        $data['category_id'] = $category->id;
        $data['includes'] = "files";
        $results = $this->editProduct->getProductsMerchant($data);
        $visualResults = $this->editProduct->buildProducts($results);

        if (!$visualResults) {
            $visualResults = [];
        }
        $returnResults = ["categories" => $visualResults,
            "per_page" => $results['per_page'],
            "page" => $results['page'],
            "last_page" => $results['last_page'],
            "total" => $results['total'],
            "category" => $category,
            "side_categories" => $side_categories
        ];
        if (isset($data['merchant_id']) && $data['merchant_id']) {
            $results = $this->editProduct->getActiveCategoriesMerchant($data['merchant_id']);
            $results = $results['data'];
            $results = array_map(function ($value) {
                return (array) $value;
            }, $results);
            $returnResults["side_categories"] = $results;
            $returnResults["merchant_id"] = $data['merchant_id'];
        }
        return view(config("app.views") . '.products.productsMerchant', ["data" => $returnResults]);
    }

    public function getProductsMerchant(Request $request, $slug) {
        $merchant = Merchant::where("slug", $slug)->first();
        $data = $request->all();
        $side_categories = $this->editProduct->getActiveCategoriesMerchant($merchant->id);
        $data['includes'] = "files";
        $data['merchant_id'] = $merchant->id;
        $results = $this->editProduct->getProductsMerchant($data);
        $visualResults = $this->editProduct->buildProducts($results);
        for ($x = 0; $x < count($visualResults); $x++) {
            for ($y = 0; $y < count($visualResults[$x]['products']); $y++) {
                $visualResults[$x]['products'][$y]['description'] = nl2br($visualResults[$x]['products'][$y]['description']);
                $visualResults[$x]['products'][$y]['description'] = str_replace(array("\r", "\n"), '', $visualResults[$x]['products'][$y]['description']);
                unset($visualResults[$x]['products'][$y]['merchant_description']);
//                foreach ($value['variants'] as $variant) {
//                    if (is_string($variant['attributes'])) {
//                        $variant['attributes'] = json_decode($variant['attributes']);
//                        if ($variant['attributes']) {
//                            dd($variant['attributes']);
//                        }
//                    }
//                }
                //dd($value);
            }
        }

        if (!$visualResults) {
            $visualResults = [];
        }
        $returnResults = ["categories" => $visualResults,
            "per_page" => $results['per_page'],
            "page" => $results['page'],
            "last_page" => $results['last_page'],
            "total" => $results['total'],
            "category" => null,
            "side_categories" => $side_categories
        ];
        $results = $this->editProduct->getActiveCategoriesMerchant($merchant->id);
        $results = $results['data'];
        $results = array_map(function ($value) {
            return (array) $value;
        }, $results);
        $returnResults["side_categories"] = $results;
        //dd($visualResults);
        return view(config("app.views") . '.products.productsMerchant', ["data" => $returnResults]);
    }

}
