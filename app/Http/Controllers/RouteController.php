<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Stop;
use App\Models\Delivery;
use Unlu\Laravel\Api\QueryBuilder;
use Illuminate\Http\Request;
use App\Services\Routing;

class RouteController extends Controller {

    /**
     * The edit profile implementation.
     *
     */
    protected $routing;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Routing $routing) {
        $this->middleware('auth:api');
        $this->middleware('admin');
        $this->routing = $routing;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
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
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function show(Route $route) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function edit(Route $route) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Route $route) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function destroy(Route $route) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function updateRouteStop($stop, $route) {
        $this->routing->updateRouteStop($route, $stop);
        return response()->json([
                    'status' => "success",
                    'message' => "Stop updated"
        ]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function updateRouteDelivery(Request $request,$stop, $route) {
        $this->routing->updateRouteDelivery($request->all());
        return response()->json([
                    'status' => "success",
                    'message' => "Stop updated"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function sendStopToNewRoute($stop) {
        $this->routing->sendStopToNewRoute($stop);
        return response()->json([
                    'status' => "success",
                    'message' => "Stop added to new route"
        ]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function deleteStop($stop) {
        $this->routing->deleteStop($stop);
        return response()->json([
                    'status' => "success",
                    'message' => "Stop deleted"
        ]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function deleteRoute($route) {
        $this->routing->deleteRoute($route);
        return response()->json([
                    'status' => "success",
                    'message' => "Route deleted"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Route  $route
     * @return \Illuminate\Http\Response
     */
    public function addReturnStop($routeId) {
        $route = Route::find($routeId);
        if ($route) {
            $this->routing->completeRoutes([$route]);
            return response()->json([
                        'status' => "success",
                        'message' => "Stop added to new route"
            ]);
        }
        return response()->json([
                    'status' => "error",
                    'message' => "route not found"
        ]);
    }

}
