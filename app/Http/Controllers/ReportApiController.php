<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditMapObject;
use App\Services\ShareObject;
use App\Services\MerchantImport;
use App\Services\CleanSearch;
use App\Querybuilders\ReportQueryBuilder;
use App\Models\Report;
use DB;

class ReportApiController extends Controller {

    const OBJECT_REPORT = 'Report';

    /**
     * The edit profile implementation.
     *
     */
    protected $editMapObject;

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
     * The edit profile implementation.
     *
     */
    protected $shareObject;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditMapObject $editMapObject, MerchantImport $merchantImport, CleanSearch $cleanSearch, ShareObject $shareObject) {
        $this->editMapObject = $editMapObject;
        $this->merchantImport = $merchantImport;
        $this->cleanSearch = $cleanSearch;
        $this->shareObject = $shareObject;
        $this->middleware('auth:api')->except(['index', 'show', 'getObject','textSearch']);;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $request2 = $this->cleanSearch->handleReportExternal($request);
        if ($request2) {
            $queryBuilder = new ReportQueryBuilder(new Report, $request2);
            //DB::enableQueryLog();
            $result = $queryBuilder->build()->paginate();
            //dd(DB::getQueryLog());
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

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getNearbyReports(Request $request) {
        $validator = $this->editMapObject->validatorLat($request->all());
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $data = $request->all();
        $data['type']="Report";
        return response()->json($this->editMapObject->getNearbyObjects($data));
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function textSearch(Request $request) {
        $data = $request->all();
        return response()->json($this->editMapObject->textSearchReport($data));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function updateObjectStatus(Request $request, $code) {
        $user = $request->user();
        $data = $request->only(['status',"group_id"]);
        $data['id'] = $code;
        return $this->editMapObject->updateObjectStatus($user, $data, self::OBJECT_REPORT);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request) {
        $data = $request->all();
        $data['type'] = "Report";
        $data['object_id'] = $id;
        if (!array_key_exists('includes', $data)) {
            $data['includes'] = "ratings,files";
        }
        return $this->editMapObject->getObject($data);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getObject(Request $request) {
        $data = $request->all();
        $data['type'] = "Report";
        return $this->editMapObject->getObject($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
            'groups',
            'type',
            'name',
            'email',
            'telephone',
            'address',
            'report_time',
            'private',
            'anonymous',
            'group_id',
            'lat',
            'long',
            'city_id',
            'region_id',
            'country_id'
        ]);
        return response()->json($this->editMapObject->saveOrCreateObject($user, $data, self::OBJECT_REPORT));
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
            'id',
            'type',
            'name',
            'email',
            'telephone',
            'address',
            'report_time',
            'private',
            'anonymous',
            'group_id',
            'lat',
            'long',
            'city_id',
            'region_id',
            'country_id'
        ]);
        $data['id'] = $id;
        return response()->json($this->editMapObject->saveOrCreateObject($user, $data, self::OBJECT_REPORT));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function removeObjectGroup($group,$object, Request $request) {
        $user = $request->user();
        return response()->json($this->editMapObject->deleteObjectFromGroup($user, $group,$object, self::OBJECT_REPORT));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request) {
        $user = $request->user();
        return response()->json($this->editMapObject->deleteObject($user, $id, self::OBJECT_REPORT));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getObjectUser($reportId, Request $request) {
        $user = $request->user();
        return response()->json($this->editMapObject->getObjectUser($user, $reportId, self::OBJECT_REPORT));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function approveReport($reportId, Request $request) {
        $user = $request->user();
        return response()->json($this->editMapObject->approveReport($user, $reportId));
    }

}
