<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditMerchant;
use App\Services\MerchantImport;
use App\Services\CleanSearch;
use App\Querybuilders\MerchantQueryBuilder;
use App\Models\Merchant;

class MerchantApiController extends Controller {

    const OBJECT_MERCHANT = 'Merchant';

    /**
     * The edit profile implementation.
     *
     */
    protected $editMerchant;

    /**
     * The edit profile implementation.
     *
     */
    protected $merchantImport;

    /**
     * The edit profile implementation.
     *
     */
    protected $cleanSearch;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditMerchant $editMerchant, MerchantImport $merchantImport, CleanSearch $cleanSearch) {
        $this->editMerchant = $editMerchant;
        $this->merchantImport = $merchantImport;
        $this->cleanSearch = $cleanSearch;
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $request2 = $this->cleanSearch->handleMerchant($request);
        if ($request2) {
            $queryBuilder = new MerchantQueryBuilder(new Merchant, $request2);
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

    public function importMerchant(Request $request) {
        $user = $request->user();
        $filename = "merchant test.xlsx";
        return response()->json($this->merchantImport->importNewMerchant($user, $filename));
    }

    public function exportMerchant(Request $request) {
        $user = $request->user();
        $filename = "merchant test.xlsx";
        $merchantid = "5";
        return response()->json($this->merchantImport->exportMerchant($user, $filename, $merchantid));
    }

    public function exportMerchantOrders(Request $request) {
        $user = $request->user();
        $filename = "merchant test.xlsx";
        $merchantid = "5";
        return response()->json($this->merchantImport->exportMerchantOrders($user, $filename, $merchantid));
    }

    public function importUpdateMerchant(Request $request) {
        $user = $request->user();
        $filename = "Filename13.xlsx";
        $merchantid = "5";
        return response()->json($this->merchantImport->importUpdateMerchant($user, $filename, $merchantid));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getNearby(Request $request) {
        $user = $request->user();
        $validator = $this->editMerchant->validatorLat($request->all());
        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        return response()->json($this->editMerchant->getNearby( $request->all()));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getNearbyMerchants(Request $request) {
        $validator = $this->editMerchant->validatorLat($request->all());
        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        return response()->json($this->editMerchant->getNearbyMerchants($request->all()));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getPaymentMethodsMerchant($id) {
        return response()->json($this->editMerchant->getPaymentMethodsMerchant($id));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request) {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id, Request $request) {
        $user = $request->user();
        return $this->editMerchant->getObjectUser($user, $id, self::OBJECT_MERCHANT);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function updateObjectStatus(Request $request,$code) {
        $user = $request->user();
        $data = $request->only(['status']);
        $data['id'] = $code;
        return $this->editMerchant->updateObjectStatus($user, $data, self::OBJECT_MERCHANT);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $user = $request->user();
        $data = $request->all([
            'id',
            'type',
            'name',
            'email',
            'telephone',
            'address',
            'group_id',
            'lat',
            'long',
            'city_id',
            'region_id',
            'country_id'
        ]);
        return response()->json($this->editMerchant->saveOrCreateObject($user, $data, self::OBJECT_MERCHANT));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $user = $request->user();
        $data = $request->all([
            'type',
            'name',
            'email',
            'telephone',
            'address',
            'group_id',
            'lat',
            'long',
            'city_id',
            'region_id',
            'country_id'
        ]);
        $data['id'] = $id;
        return response()->json($this->editMerchant->saveOrCreateObject($user, $data, self::OBJECT_MERCHANT));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,Request $request) {
        $user = $request->user();
        return response()->json($this->editMerchant->deleteObject($user, $id, self::OBJECT_MERCHANT));
    }

    public function getMerchantHash($reportId, Request $request) {
        $user = $request->user();
        return response()->json($this->editMerchant->getObjectHash($user, $reportId, self::OBJECT_MERCHANT));
    }
}
