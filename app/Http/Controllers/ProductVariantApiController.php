<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\EditProduct;
use App\Services\EditMerchant;
use Illuminate\Http\Request;

class ProductVariantApiController extends Controller {

    const OBJECT_MERCHANT = 'Merchant';


    /**
     * The edit profile implementation.
     *
     */
    protected $editProduct;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( EditProduct $editProduct) {
        $this->editProduct = $editProduct;
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request) {
        $user = $request->user();
        $data = $request->only("merchant_id");
        $request2 = null;
        $result['access'] = null;
        if ($data['merchant_id']) {
            $result = $this->editProduct->checkAccess($user, $data['merchant_id'], self::OBJECT_MERCHANT);
        }
        if ($result['access']) {
            $data2 = $request->only("order_by");
            if ($data2['order_by']) {
                $request2 = Request::create("?merchant_id=" . $data['merchant_id'] . "&order_by=" . $data2['order_by'], 'GET');
            } else {
                $request2 = Request::create("?merchant_id=" . $data['merchant_id'], 'GET');
            }
        }
        if ($request2) {
            $queryBuilder = new QueryBuilder(new Product, $request2);
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
                        ], 401);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request) {
        $user = $request->user();
        $data = $request->only([
            'id',
            'sku',
            'product_id',
            'ref2',
            'type',
            'is_digital',
            'is_shippable',
            'price',
            'sale',
            'tax',
            'quantity',
            'merchant_id',
            'requires_authorization'
        ]);
        return response()->json($this->editProduct->createOrUpdateVariant($user, $data));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id, Request $request) {
        $user = $request->user();
        return response()->json($this->editProduct->getVariant($user, $id));
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
        $data = $request->only([
            'id',
            'sku',
            'product_id',
            'ref2',
            'type',
            'is_digital',
            'is_shippable',
            'price',
            'sale',
            'tax',
            'quantity',
            'merchant_id',
            'requires_authorization'
        ]);
        $data['id'] = $id;
        return response()->json($this->editProduct->createOrUpdateVariant($user, $data));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id, Request $request) {
        $user = $request->user();
        return response()->json($this->editProduct->deleteVariant($user, $id));
    }

}
