<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Food;
use App\Jobs\BuildScenario;
use App\Jobs\RegenerateScenarios;
use App\Mails\RouteOrganize;
use App\Jobs\RegenerateDeliveriesAndScenarios;
use App\Jobs\BuildScenarioRouteIdApi;
use App\Jobs\GetScenarioOrganizationStructure;
use Unlu\Laravel\Api\QueryBuilder;
use App\Models\Article;
use App\Models\Route;
use App\Models\Translation;
use App\Models\CoveragePolygon;

class FoodApiController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Home Controller
      |--------------------------------------------------------------------------
      |
      | This controller renders your application's "dashboard" for users that
      | are authenticated. Of course, you are free to change or remove the
      | controller as you wish. It is just here to get your app started!
      |
     */

    private $food;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Food $food) {
        $this->food = $food;
        $this->middleware('auth:api');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index() {
        
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getSummaryShipping(Request $request, $type) {
        $user = $request->user();
        $checkResult = $this->food->checkUser($user);
        if ($checkResult) {
            dispatch(new \App\Jobs\GetScenariosShippingCosts($user, $type));
            //$results = $this->food->getShippingCosts($type);
            return response()->json(array("status" => "success", "message" => "Summary shipping cost calculation queued"));
        }
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function showOrganizeEmails(Request $request, $type) {
        $user = $request->user();
        $checkResult = $this->food->checkUser($user);
        if ($checkResult) {
//            $routes = Route::where("status", "pending")->with(['deliveries.user'])->get();
//            $results = $this->food->buildScenario($routes);
//            Mail::to($$user)->send(new RouteOrganize($results));
            dispatch(new GetScenarioOrganizationStructure($user, $type));
            //$results = $this->food->getShippingCosts($type);
            return response()->json(array("status" => "success", "message" => "Summary shipping cost calculation queued"));
        }
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getScenarioStructure(Request $request, $scenario, $type) {
        $user = $request->user();
        $checkResult = $this->food->checkUser($user);
        if ($checkResult) {
            dispatch(new \App\Jobs\GetScenarioStructure($user, $scenario, $type));
            //$this->food->getTotalEstimatedShipping($scenario,$type);
            return response()->json(array("status" => "success", "message" => "Scenario shipping calculated"));
        }
        return response()->json(array("status" => "error", "message" => "User not authorized"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioRouteId(Request $request, $id) {
        $user = $request->user();
        $checkResult = $this->food->checkUser($user);
        if ($checkResult) {
            dispatch(new \App\Jobs\BuildScenarioRouteIdApi($user, $id));
//            $routes = App\Models\Route::where("id", $id)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
//            $this->food->buildScenarioTransit($routes);
            return response()->json(array("status" => "success", "message" => "Scenario Scheduled"));
        }
        return response()->json(array("status" => "error", "message" => "User not authorized"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioPositive(Request $request, $scenario) {
        $user = $request->user();
        $checkResult = $this->food->checkUser($user);
        if ($checkResult) {
            dispatch(new BuildScenario($user, $scenario));
//            $routes = App\Models\Route::where("type", $scenario)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
//            $this->food->buildScenarioTransit($routes);
            return response()->json(array("status" => "success", "message" => "Scenario Scheduled"));
        }
        return response()->json(array("status" => "error", "message" => "User not authorized"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildCompleteScenario(Request $request, $scenario) {
        $user = $request->user();
        $checkResult = $this->food->checkUser($user);
        if ($checkResult) {
            dispatch(new BuildScenario($user, $scenario));
//            $routes = App\Models\Route::where("type", $scenario)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
//            $this->food->buildScenarioTransit($routes);
            return response()->json(array("status" => "success", "message" => "Scenario Scheduled"));
        }
        return response()->json(array("status" => "error", "message" => "User not authorized"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function updateCreditsUser(Request $request, $user_id) {
        $user = $request->user();
        $checkResult = $this->food->checkUser($user);
        if ($checkResult) {
            $results = $this->food->updateCreditsUser($user_id, $request->all());
            return response()->json(array("status" => "success", "message" => "Credits updated", "results" => $results));
        }
        return response()->json(array("status" => "error", "message" => "User not authorized"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function regenerateScenarios(Request $request) {
        $user = $request->user();
        $checkResult = $this->food->checkUser($user);
        if ($checkResult) {
            dispatch(new RegenerateScenarios());
            //$this->food->regenerateScenarios();
            return response()->json(array("status" => "success", "message" => "Scenarios Scheduled for regeneration"));
        }
        return response()->json(array("status" => "error", "message" => "User not authorized"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function regenerateDeliveries(Request $request) {
        $user = $request->user();
        $checkResult = $this->food->checkUser($user);
        if ($checkResult) {
            dispatch(new RegenerateDeliveriesAndScenarios());
            /* $this->food->deleteRandomDeliveriesData();
              $polygons = CoveragePolygon::where('lat', "<>", 0)->where('long', "<>", 0)->get();
              foreach ($polygons as $value) {
              $this->food->generateRandomDeliveries($value);
              }
              $this->food->prepareRoutingSimulation($polygons); */
            return response()->json(array("status" => "success", "message" => "Scenarios and Deliveries Scheduled for regeneration"));
        }
        return response()->json(array("status" => "error", "message" => "User not authorized"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getRouteInfo($delivery) {
        $result = $this->food->getRouteInfo($delivery);
        if ($result) {
            return response()->json(array("status" => "success", "message" => "Route info", "route" => $result));
        }
        return response()->json(array("status" => "error", "message" => "User not authorized"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getLargestAddresses(Request $request) {
        $user = $request->user();
        $checkResult = $this->food->checkUser($user);
        if ($checkResult) {
            $results = $this->food->getLargestAddresses();
            return response()->json(array("status" => "success", "message" => "Most common addresses", "data" => $results));
        }
        return response()->json(array("status" => "error", "message" => "User not authorized"));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getMenu(Request $request) {
        $user = $request->user();
        //$request2 = $this->cleanSearch->handleOrder($user, $request);
        if ($request) {
            $queryBuilder = new QueryBuilder(new Article, $request);
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
    public function getMessages(Request $request) {
        $user = $request->user();
        //$request2 = $this->cleanSearch->handleOrder($user, $request);
        if ($request) {
            $queryBuilder = new QueryBuilder(new Translation, $request);
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
    public function getZones(Request $request) {
        $user = $request->user();
        //$request2 = $this->cleanSearch->handleOrder($user, $request);
        if ($request) {
            $queryBuilder = new QueryBuilder(new CoveragePolygon, $request);
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

}
