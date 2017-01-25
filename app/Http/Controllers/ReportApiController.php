<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditMerchant;
use App\Services\MerchantImport;
use App\Services\CleanSearch;

class ReportApiController extends Controller {

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
    public function index(Request $request)
    {
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();
        return response()->json($this->editMerchant->saveOrCreateReport($user, $request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $user = $request->user();
        return response()->json($this->editMerchant->getReport($user, $id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        return response()->json($this->editMerchant->saveOrCreateReport($user, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $request->user();
        return response()->json($this->editMerchant->deleteReport($user, $request->all()));
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getReportUser($reportId, Request $request) {
        $user = $request->user();
        return response()->json($this->editMerchant->getReportUser($user, $reportId));
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function deleteUserReport(Request $request) {
        $user = $request->user();
        return response()->json($this->editMerchant->deleteUserReport($user, $request->all()));
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function deleteReportImage(Request $request) {
        $user = $request->user();
        return response()->json($this->editMerchant->deleteReportImage($user, $request->all()));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function approveReport($reportId, Request $request) {
        $user = $request->user();
        return response()->json($this->editMerchant->approveReport($user, $reportId));
    }

    public function getReportHash($reportId, Request $request) {
        $user = $request->user();
        return response()->json($this->editMerchant->getReportHash($user, $reportId));
    }
}
