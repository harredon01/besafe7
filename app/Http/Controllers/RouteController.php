<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Stop;
use App\Models\Delivery;
use Unlu\Laravel\Api\QueryBuilder;
use Illuminate\Http\Request;
use App\Services\Food;

class RouteController extends Controller
{
    /**
     * The edit profile implementation.
     *
     */
    protected $food;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Food $food ) {
        $this->food = $food;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //$request2 = $this->cleanSearch->handle($request);
        if (true) {
            $queryBuilder = new QueryBuilder(new Route, $request);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function show(Route $route)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function edit(Route $route)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Route $route)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function destroy(Route $route)
    {
        //
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function buildRoute(Request $request,$route)
    {
        $user = $request->user();
        $routes = Route::where("id",$route)->get();
        $this->food->buildScenario($routes);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function updateRouteStop(Request $request,$route,$stop)
    {
        $user = $request->user();
        $stopContainer = Stop::find($stop);
        $oldRoute = $stop->route;
        $newRoute = Route::find($route);
        $stop->route_id = $newRoute->id;
    }
}
