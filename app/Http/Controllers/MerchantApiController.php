<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditMapObject;
use App\Services\MerchantImport;
use App\Services\ShareObject;
use App\Services\CleanSearch;
use App\Querybuilders\MerchantQueryBuilder;
use App\Models\Merchant;
use DB;

class MerchantApiController extends Controller {

    const OBJECT_MERCHANT = 'Merchant';

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
        $this->middleware('auth:api')->except(['index', 'show', 'getObject']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $request2 = $this->cleanSearch->handleMerchantExternal($request);
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
                        ], 403);
    }

    public function indexPrivate(Request $request) {
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
                        ], 403);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getNearby(Request $request) {
        $user = $request->user();
        $validator = $this->editMapObject->validatorLat($request->all());
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        return response()->json($this->editMapObject->getNearby($request->all()));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getNearbyMerchants(Request $request) {
        $validator = $this->editMapObject->validatorLat($request->all());
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $data = $request->all();
        $data['type'] = "Merchant";
        $results = $this->editMapObject->getNearbyObjects($data);
        $merchants = $results['data'];
        if (array_key_exists('includes', $data)) {
            if ($data['includes']) {
                $relatedObjects = explode(',', $data['includes']);
                $merchantIds = array_column($merchants, 'id');
                $object = "";
                $idColumn = "";
                foreach ($relatedObjects as $item) {
                    if ($item == 'availabilities') {
                        $object = "bookable_availabilities";
                        $idColumn = "bookable_id";
                    }
                    $relationships = $this->editMapObject->getRelation($merchantIds, $object, $idColumn);
                    $merchants = $this->editMapObject->organizeRelation($merchants, $relationships, $item, $idColumn);
                }
            }
        }
        $results['data'] = $merchants;
        return response()->json($results);
    }
    
    public function checkCoverageMerchants(Request $request) {
        $validator = $this->editMapObject->validatorLat($request->all());
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $data = $request->all();
        $lat = $data['lat'];
        $long = $data['long'];
        $merchant_id = $data['merchant_id'];
        $geolocation = app("Geolocation");
        return response()->json($geolocation->checkMerchantPolygons($lat, $long, $merchant_id,null));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getCoverageMerchants(Request $request) {
        $validator = $this->editMapObject->validatorLat($request->all());
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $data = $request->all();

        $lat = $data['lat'];
        $long = $data['long'];
        $category = false;
        $per_page = 25;
        $page = 1;
        if (array_key_exists("category", $data)) {
            if ($data["category"]) {
                $category = true;
            }
        }
        if (array_key_exists("page", $data)) {
            if ($data["page"]) {
                $page = $data["page"];
            }
        }
        if (array_key_exists("per_page", $data)) {
            if ($data["per_page"]) {
                $per_page = $data["per_page"];
            }
        }
        $offset = ($page-1)*$per_page;

        $thedata = [
            'point' => 'POINT(' . $long . ' ' . $lat . ')',
            'limit' => $per_page
        ];
        $additionalQuery = '';
        if ($category) {
            $thedata["category"] = $data["category"];
            $additionalQuery = ' AND id in (SELECT categorizable_id FROM categorizables where category_id in (:category)) ';
        }
        //DB::enableQueryLog();
        $merchants = DB::select(" "
                        . "SELECT id, name, description, icon, lat,`long`, type, telephone, address,rating,rating_count,unit_cost,attributes FROM merchants "
                ." where private = 0 AND status in ('online','active','busy') AND "
                . " id in (SELECT merchant_id FROM coverage_polygons WHERE ST_Contains(`geometry`, ST_GeomFromText(:point)) ) "
                        . $additionalQuery
                        . " LIMIT ".$offset.", :limit", $thedata);
        //dd(DB::getQueryLog());
        $merchants = $this->editMapObject->buildIncludes($merchants, $data);
        return array("data" => $merchants);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getPaymentMethodsMerchant($id) {
        return response()->json($this->editMapObject->getPaymentMethodsMerchant($id));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getCategoriesMerchant($id, $type) {
        return response()->json($this->editMapObject->getCategoriesMerchant($id, $type));
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
    public function show(Request $request, $id) {
        $data = $request->all();
        $data['type'] = "Merchant";
        $data['object_id'] = $id;
        if (!array_key_exists('includes', $data)) {
            $data['includes'] = "ratings,files,availabilities";
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
        $data['type'] = "Merchant";
        return $this->editMapObject->getObject($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function updateObjectStatus(Request $request, $code) {
        $user = $request->user();
        $data = $request->only(['status', "group_id"]);
        $data['id'] = $code;
        return $this->editMapObject->updateObjectStatus($user, $data, self::OBJECT_MERCHANT);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function updateStatus(Request $request) {
        $user = $request->user();
        $data = $request->all();
        return $this->editMapObject->updateStatus($user, $data);
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
            'description',
            'email',
            'telephone',
            'address',
            'groups',
            'lat',
            'long',
            'city_id',
            'region_id',
            'country_id',
            'service1',
            'service2',
            'service3',
            'specialty1',
            'specialty2',
            'specialty3',
            'unit_cost',
            'experience1',
            'experience2',
            'experience3',
            'booking_requires_auth',
            'virtual_meeting',
            'virtual_provider',
            'max_per_hour',
            'years_experience',
            'private'
        ]);
        return response()->json($this->editMapObject->saveOrCreateObject($user, $data, self::OBJECT_MERCHANT));
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
            'description',
            'email',
            'telephone',
            'address',
            'groups',
            'lat',
            'long',
            'city_id',
            'region_id',
            'country_id',
            'service1',
            'service2',
            'service3',
            'specialty1',
            'specialty2',
            'specialty3',
            'unit_cost',
            'experience1',
            'experience2',
            'experience3',
            'virtual_meeting',
            'virtual_provider',
            'booking_requires_auth',
            'max_per_hour',
            'years_experience',
            'private'
        ]);
        $data['id'] = $id;
        return response()->json($this->editMapObject->saveOrCreateObject($user, $data, self::OBJECT_MERCHANT));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function removeObjectGroup($group, $object, Request $request) {
        $user = $request->user();
        return response()->json($this->editMapObject->deleteObjectFromGroup($user, $group, $object, self::OBJECT_REPORT));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request) {
        $user = $request->user();
        return response()->json($this->editMapObject->deleteObject($user, $id, self::OBJECT_MERCHANT));
    }

    public function getMerchantHash($merchantId, Request $request) {
        $user = $request->user();
        return response()->json($this->shareObject->getObjectHash($user, $merchantId, self::OBJECT_MERCHANT));
    }

}
