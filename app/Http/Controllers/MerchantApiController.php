<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditMerchant;
use App\Services\MerchantImport;
use App\Services\CleanSearch;
use App\Querybuilders\ReportQueryBuilder;
use Unlu\Laravel\Api\QueryBuilder;
use App\Models\Report;
use App\Models\Merchant;
use App\Models\FileM;
use Image;
use File;

class MerchantApiController extends Controller {

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

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
    public function __construct(Guard $auth, EditMerchant $editMerchant, MerchantImport $merchantImport, CleanSearch $cleanSearch) {
        $this->editMerchant = $editMerchant;
        $this->merchantImport = $merchantImport;
        $this->cleanSearch = $cleanSearch;
        $this->auth = $auth;
        $this->middleware('jwt.auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getReports2(Request $request) {
        $request2 = $this->cleanSearch->handleReport($request);
        if ($request2) {
            $queryBuilder = new ReportQueryBuilder(new Report, $request2);
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
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function deleteReportImage(Request $request) {
        $user = $this->auth->user();
        return response()->json($this->editMerchant->deleteReportImage($user, $request->all()));
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

    public function importMerchant() {
        $user = $this->auth->user();
        $filename = "merchant test.xlsx";
        return response()->json($this->merchantImport->importNewMerchant($user, $filename));
    }

    public function exportMerchant() {
        $user = $this->auth->user();
        $filename = "merchant test.xlsx";
        $merchantid = "5";
        return response()->json($this->merchantImport->exportMerchant($user, $filename, $merchantid));
    }

    public function exportMerchantOrders() {
        $user = $this->auth->user();
        $filename = "merchant test.xlsx";
        $merchantid = "5";
        return response()->json($this->merchantImport->exportMerchantOrders($user, $filename, $merchantid));
    }

    public function importUpdateMerchant() {
        $user = $this->auth->user();
        $filename = "Filename13.xlsx";
        $merchantid = "5";
        return response()->json($this->merchantImport->importUpdateMerchant($user, $filename, $merchantid));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function saveOrCreateReport(Request $request) {
        $user = $this->auth->user();
        return response()->json($this->editMerchant->saveOrCreateReport($user, $request->all()));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getUserReports(Request $request) {
        $user = $this->auth->user();
        return response()->json($this->editMerchant->getUserReports($user, $request->all()));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getReports(Request $request) {
        $user = $this->auth->user();
        return response()->json($this->editMerchant->getReports($user, $request->all()));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getReportUser($reportId) {
        $user = $this->auth->user();
        return response()->json($this->editMerchant->getReportUser($user, $reportId));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getReport($reportId) {
        $user = $this->auth->user();
        return response()->json($this->editMerchant->getReport($user, $reportId));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function deleteReport(Request $request) {
        $user = $this->auth->user();
        return response()->json($this->editMerchant->deleteReport($user, $request->all()));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function deleteUserReport(Request $request) {
        $user = $this->auth->user();
        return response()->json($this->editMerchant->deleteUserReport($user, $request->all()));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function approveReport($reportId) {
        $user = $this->auth->user();
        return response()->json($this->editMerchant->approveReport($user, $reportId));
    }
    public function getReportHash($reportId) {
        $user = $this->auth->user();
        return response()->json($this->editMerchant->getReportHash($user, $reportId));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getNearby(Request $request) {
        $user = $this->auth->user();
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
