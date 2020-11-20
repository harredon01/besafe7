<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Services\EditLocation;
use App\Querybuilders\LocationQueryBuilder;
use App\Querybuilders\HistoricLocationQueryBuilder;
use Unlu\Laravel\Api\QueryBuilder;
use App\Services\CleanSearch;
use App\Jobs\PostLocation;
use App\Jobs\MoveOld;
use App\Models\UserableHistoric;
use App\Models\Userable;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;

class LocationController extends Controller {

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
    protected $editLocation;

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
    public function __construct(Guard $auth, EditLocation $editLocation, CleanSearch $cleanSearch) {
        $this->editLocation = $editLocation;
        $this->cleanSearch = $cleanSearch; 
        $this->auth = $auth;
        $this->middleware('auth:api', ['except' => ['moveHistoricLocations', 'moveOldLocations','getCountries2','getCities']]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function moveOldLocations() {
        dispatch(new MoveOld());
        return response()->json(null);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getUserHash(Request $request) {
        $user = $request->user();
        $hash = $this->editLocation->getUserHash($user);
        return response()->json(compact('hash'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getCountries2(Request $request) {
        $queryBuilder = new QueryBuilder(new Country, $request);
        $result = $queryBuilder->build()->paginate();
        return response()->json([
                    'data' => $result->items(),
                    "total" => $result->total(),
                    "per_page" => $result->perPage(),
                    "page" => $result->currentPage(),
                    "last_page" => $result->lastPage(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getRegions(Request $request) {
        $queryBuilder = new QueryBuilder(new Region, $request);
        $result = $queryBuilder->build()->paginate();
        return response()->json([
                    'data' => $result->items(),
                    "total" => $result->total(),
                    "per_page" => $result->perPage(),
                    "page" => $result->currentPage(),
                    "last_page" => $result->lastPage(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getCities(Request $request) {
        $queryBuilder = new QueryBuilder(new City, $request);
        $result = $queryBuilder->build()->paginate();
        return response()->json([
                    'data' => $result->items(),
                    "total" => $result->total(),
                    "per_page" => $result->perPage(),
                    "page" => $result->currentPage(),
                    "last_page" => $result->lastPage(),
                    "last_page" => $result->lastPage(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postLocation(Request $request) {

        $user = $request->user();
        //dispatch(new PostLocation($user, $request->all()));
        return response()->json($this->editLocation->postLocation($request->all(),$user));
        return response()->json([
                    'status' => "success",
                    "message" => "Location queued",
        ]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getCitiesFrom(Request $request) {
        return response()->json($this->editLocation->getCitiesFrom($request->all()));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function index(Request $request) {
        $request2 = $this->cleanSearch->handleLocation($request);
        if ($request2) {
            $queryBuilder = new LocationQueryBuilder(new Userable, $request2);
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
                    'message' => "no user id parameter allowed"
                        ], 403);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function historicLocations(Request $request) {
        $request2 = $this->cleanSearch->handleHistoricLocation($request);
        if ($request2) {
            $queryBuilder = new HistoricLocationQueryBuilder(new UserableHistoric, $request2);
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
                    'message' => "Invalid Search"
                        ], 403);
    }

}
