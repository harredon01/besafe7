<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditMerchant;
use App\Services\MerchantImport;
use App\Services\CleanSearch;
use Unlu\Laravel\Api\QueryBuilder;
use App\Models\Merchant;

class MerchantApiController extends Controller {

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
     * @return Response
     */
    public function getMerchants(Request $request) {
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
        $validator = $this->editMerchant->validatorGetNearby($request->all());
        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        return response()->json($this->editMerchant->getNearby($user, $request->all()));
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
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function findMerchant(Request $request) {
        return response()->json($this->editMerchant->findMerchant($request->only("name")));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request) {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        return $this->editMerchant->getMerchant($id);
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
    public function update($id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        //
    }

}
