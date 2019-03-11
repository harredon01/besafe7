<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProductImport;
use Unlu\Laravel\Api\QueryBuilder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Merchant;
use App\Models\Category;

class ProductImportApiController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Home Controller
      |--------------------------------------------------------------------------
      |
      | This controller renders your application's "dashboard" for users that
      | are authenticated. Of course, you are free to change or remove the
      | controller as you wish. It is just here to get your app started!
      |
     */

    private $product;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductImport $product) {
        $this->product = $product;
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getProducts(Request $request) {
        $user = $request->user();
        $checkResult = $this->product->checkUser($user);
        if ($checkResult) {
            //$request2 = $this->cleanSearch->handleOrder($user, $request);
            if ($request) {
                $queryBuilder = new QueryBuilder(new Product, $request);
                $result = $queryBuilder->build()->paginate();
                return response()->json([
                            'data' => $result->items(),
                            "total" => $result->total(),
                            "per_page" => $result->perPage(),
                            "page" => $result->currentPage(),
                            "last_page" => $result->lastPage(),
                ]);
            }
            return response()->json([
                        'status' => "error",
                        'message' => "illegal parameter"
                            ], 403);
        }
        return response()->json([
                    'status' => "error",
                    'message' => "user not allowed"
                        ], 403);
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getVariants(Request $request) {
        $user = $request->user();
        $checkResult = $this->product->checkUser($user);
        if ($checkResult) {
            //$request2 = $this->cleanSearch->handleOrder($user, $request);
            if ($request) {
                $queryBuilder = new QueryBuilder(new ProductVariant, $request);
                $result = $queryBuilder->build()->paginate();
                return response()->json([
                            'data' => $result->items(),
                            "total" => $result->total(),
                            "per_page" => $result->perPage(),
                            "page" => $result->currentPage(),
                            "last_page" => $result->lastPage(),
                ]);
            }
            return response()->json([
                        'status' => "error",
                        'message' => "illegal parameter"
                            ], 403);
        }
        return response()->json([
                    'status' => "error",
                    'message' => "user not allowed"
                        ], 403);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getMerchants(Request $request) {
        $user = $request->user();
        $checkResult = $this->product->checkUser($user);
        if ($checkResult) {
            //$request2 = $this->cleanSearch->handleOrder($user, $request);
            if ($request) {
                $queryBuilder = new QueryBuilder(new Merchant, $request);
                $result = $queryBuilder->build()->paginate();
                return response()->json([
                            'data' => $result->items(),
                            "total" => $result->total(),
                            "per_page" => $result->perPage(),
                            "page" => $result->currentPage(),
                            "last_page" => $result->lastPage(),
                ]);
            }
            return response()->json([
                        'status' => "error",
                        'message' => "illegal parameter"
                            ], 403);
        }
        return response()->json([
                    'status' => "error",
                    'message' => "user not allowed"
                        ], 403);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getCategories(Request $request) {
        $user = $request->user();
        $checkResult = $this->product->checkUser($user);
        if ($checkResult) {
            //$request2 = $this->cleanSearch->handleOrder($user, $request);
            if ($request) {
                $queryBuilder = new QueryBuilder(new Category, $request);
                $result = $queryBuilder->build()->paginate();
                return response()->json([
                            'data' => $result->items(),
                            "total" => $result->total(),
                            "per_page" => $result->perPage(),
                            "page" => $result->currentPage(),
                            "last_page" => $result->lastPage(),
                ]);
            }
            return response()->json([
                        'status' => "error",
                        'message' => "illegal parameter"
                            ], 403);
        }
        return response()->json([
                    'status' => "error",
                    'message' => "user not allowed"
                        ], 403);
    }

}
