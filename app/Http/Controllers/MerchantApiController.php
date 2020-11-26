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
        $this->middleware('auth:api')->except(['index', 'show', 'getObject','textSearch','getNearby','getNearbyMerchants','getCoverageMerchants']);
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
//            DB::enableQueryLog();
            $result = $queryBuilder->build()->paginate();
//            dd(DB::getQueryLog());
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
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserMerchant(Request $request) {
        $user = $request->user();
        $query = " select id,name from merchants where id in (select merchant_id from merchant_user where user_id = $user->id)";
        $objects = DB::select($query);
        return array(
            "status" => "success",
            "message" => "",
            "data" => $objects);
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
    public function textSearch(Request $request) {
        return response()->json($this->editMapObject->textSearchMerchant($request->all()));
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

        $results = $this->editMapObject->buildCoverageQuery( $data);
        $merchants = $results['data'];
//        dd($merchants);
//        dd(DB::getQueryLog());
        $merchants = $this->editMapObject->buildIncludes($merchants, $data);
        $results['data'] = $merchants;
        return $results;
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
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getActiveCategoriesMerchant($id) {
        return response()->json($this->editMapObject->getActiveCategoriesMerchant($id));
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

}
