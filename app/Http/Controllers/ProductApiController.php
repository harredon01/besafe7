<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Querybuilders\ProductQueryBuilder;
use App\Http\Controllers\Controller;
use App\Services\EditProduct;
use App\Services\ShareObject;
use Illuminate\Http\Request;

class ProductApiController extends Controller {

    const OBJECT_MERCHANT = 'Merchant';
    const OBJECT_PRODUCT = 'Product';

    /**
     * The edit profile implementation.
     *
     */
    protected $editProduct;

    /**
     * The edit profile implementation.
     *
     */
    protected $shareObject;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditProduct $editProduct, ShareObject $shareObject) {
        $this->editProduct = $editProduct;
        $this->shareObject = $shareObject;
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request) {
        $user = $request->user();
        $data = $request->only("mine");
        $request2 = null;
        $result['access'] = null;
        if ($data) {
            if ($data["mine"]) {
                $data2 = $request->only("order_by");
                if ($data2) {
                    $request2 = Request::create("?user_id=" . $user->id . "&order_by=" . $data2['order_by'], 'GET');
                } else {
                    $request2 = Request::create("?user_id=" . $user->id, 'GET');
                }
            }
            if ($request2) {
                $queryBuilder = new ProductQueryBuilder(new Product, $request2);
                $result = $queryBuilder->build()->paginate();
                return response()->json([
                            'data' => $result->items(),
                            "total" => $result->total(),
                            "per_page" => $result->perPage(),
                            "page" => $result->currentPage(),
                            "last_page" => $result->lastPage(),
                ]);
            }
        }
        return response()->json([
                    'status' => "error",
                    'message' => "illegal parameter"
                        ], 401);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request) {
        $user = $request->user();
        $data = $request->all([
            'merchant_id',
            'name',
            'description',
            'availability',
            'hash',
            'isActive',
        ]);
        return response()->json($this->editProduct->createOrUpdateProduct($user, $data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request) {
        $user = $request->user();
        $data = $request->all([
            'merchant_id',
            'id',
            'name',
            'description',
            'availability',
            'hash',
            'isActive',
        ]);
        return response()->json($this->editProduct->createOrUpdateProduct($user, $data));
    }

    public function getProductHash($productId, Request $request) {
        $user = $request->user();
        return response()->json($this->shareObject->getObjectHash($user, $productId, self::OBJECT_PRODUCT));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id, Request $request) {
        $user = $request->user();
        return response()->json($this->editProduct->getProduct($user, $id));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getProductsMerchant($merchant,$page, Request $request) {
        $user = $request->user();
        return response()->json($this->editProduct->getProductsMerchant($user, $merchant,$page));
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getProductsPrivateMerchant($merchant,$page, Request $request) {
        $user = $request->user();
        return response()->json($this->editProduct->getProductsPrivateMerchant($user, $merchant,$page));
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getProductsGroup($group,$page, Request $request) {
        $user = $request->user();
        return response()->json($this->editProduct->getProductsGroup($user, $group,$page));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, Request $request) {
        $user = $request->user();
        $data = $request->all([
            'merchant_id',
            'id',
            'name',
            'description',
            'availability',
            'hash',
            'isActive',
        ]);
        $data['id'] = $id;
        return response()->json($this->editProduct->createOrUpdateProduct($user, $data));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function changeProductOwners($id, Request $request) {
        $user = $request->user();
        $data = $request->all([
            'merchants',
            'operation',
        ]);
        $data['product_id'] = $id;
        return response()->json($this->editProduct->changeProductOwners($user, $data));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id, Request $request) {
        $user = $request->user();
        return response()->json($this->editProduct->deleteProduct($user, $id));
    }

}
