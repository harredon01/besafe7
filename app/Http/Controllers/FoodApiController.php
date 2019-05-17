<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Food;
use App\Jobs\BuildScenario;
use App\Jobs\RegenerateScenarios;
use App\Mail\RouteOrganize;
use App\Jobs\RegenerateDeliveriesAndScenarios;
use App\Jobs\BuildScenarioRouteIdApi;
use App\Jobs\BuildScenarioPositive;
use App\Jobs\BuildCompleteScenario;
use App\Jobs\ApprovePayment;
use App\Jobs\GetScenarioOrganizationStructure;
use Unlu\Laravel\Api\QueryBuilder;
use App\Models\Article;
use Illuminate\Support\Facades\Mail;
use App\Mail\RouteChoose;
use App\Models\Route;
use App\Models\Delivery;
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
        $this->middleware('admin')->except('getRouteInfo');
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
    public function getSummaryShipping(Request $request, $status) {
        $user = $request->user();
        //dispatch(new \App\Jobs\GetScenariosShippingCosts($user, $status));
        $results = $this->food->getShippingCosts($user, $status);
        return response()->json(array("status" => "success", "message" => "Summary shipping cost calculation queued"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getPurchaseOrder(Request $request) {
        $user = $request->user();
        $date = date_create();
        date_add($date, date_interval_create_from_date_string("1 days"));
        $tomorrow = date_format($date, "Y-m-d");
        $deliveries = Delivery::whereIn("status", ["scheduled", "enqueue"])->where("delivery", "<", $tomorrow . " 23:59:59")->where("delivery", ">", $tomorrow . " 00:00:00")->get();
        $this->food->getPurchaseOrder($deliveries);
        return response()->json(array("status" => "success", "message" => "Summary shipping cost calculation queued"));
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function sendReminder() {
        $this->food->sendReminder();
        return response()->json(array("status" => "success", "message" => "Reminder Sent"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function showOrganizeEmails(Request $request) {
        $user = $request->user();
        $results = $this->food->getStructureEmails($request->all());
        Mail::to($user)->send(new RouteOrganize($results));
        dispatch(new GetScenarioOrganizationStructure($user, $request->all()));
        return response()->json(array("status" => "success", "message" => "Summary shipping cost calculation queued"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getScenarioStructure(Request $request) {
        $user = $request->user();
        dispatch(new \App\Jobs\GetScenarioStructure($user, $request->all()));
        return response()->json(array("status" => "success", "message" => "Scenario shipping calculated"));
//            $data = $this->food->getTotalEstimatedShipping( $request->all()); 
//            $result = Mail::to($user)->send(new RouteChoose($data['routes']));
//            return response()->json(array("status" => "success", "message" => "Scenario shipping calculated","result" => $result));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioLogistics(Request $request) {
        $user = $request->user();
        $this->food->buildScenarioLogistics($user, $request->all());

        //dispatch(new \App\Jobs\BuildScenarioLogistics($user, $request->all()));
        return response()->json(array("status" => "success", "message" => "Scenario sent to build"));
//            $data = $this->food->getTotalEstimatedShipping( $request->all()); 
//            $result = Mail::to($user)->send(new RouteChoose($data['routes']));
//            return response()->json(array("status" => "success", "message" => "Scenario shipping calculated","result" => $result));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioRouteId(Request $request, $id) {
        $user = $request->user();
        dispatch(new \App\Jobs\BuildScenarioRouteIdApi($user, $id));
//            $routes = App\Models\Route::where("id", $id)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
//            $this->food->buildScenarioTransit($routes);
        return response()->json(array("status" => "success", "message" => "Scenario Scheduled"));
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function approvePayment( $id) {
        $payment = \App\Models\Payment::find($id);
        dispatch(new \App\Jobs\ApprovePayment($payment,  "Food"));
        return response()->json(array("status" => "success", "message" => "Payment scheduled"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioPositive(Request $request, $scenario) {
        $user = $request->user();
        dispatch(new BuildScenario($user, $scenario));
//            $routes = App\Models\Route::where("type", $scenario)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
//            $this->food->buildScenarioTransit($routes);
        return response()->json(array("status" => "success", "message" => "Scenario Scheduled"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildCompleteScenario(Request $request, $scenario) {
        $user = $request->user();
        dispatch(new BuildScenario($user, $scenario));
//            $routes = App\Models\Route::where("type", $scenario)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
//            $this->food->buildScenarioTransit($routes);
        return response()->json(array("status" => "success", "message" => "Scenario Scheduled"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function updateCreditsUser(Request $request, $user_id) {
        $results = $this->food->updateCreditsUser($user_id, $request->all());
        return response()->json(array("status" => "success", "message" => "Credits updated", "results" => $results));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function regenerateScenarios(Request $request) {
        //dispatch(new RegenerateScenarios());
        $this->food->regenerateScenarios();
        return response()->json(array("status" => "success", "message" => "Scenarios Scheduled for regeneration"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function delegateDeliveries(Request $request) {
        $this->food->delegateDeliveries($request->all());
        return response()->json(array("status" => "success", "message" => "Scenarios Scheduled for regeneration"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function regenerateDeliveries(Request $request) {
        dispatch(new RegenerateDeliveriesAndScenarios());
        /* $this->food->deleteRandomDeliveriesData();
          $polygons = CoveragePolygon::where('merchant_id', 1299)->where("provider","Rapigo")->get();
          foreach ($polygons as $value) {
          $this->food->generateRandomDeliveries($value);
          } */
        //$this->food->prepareRoutingSimulation($polygons); 
        return response()->json(array("status" => "success", "message" => "Scenarios and Deliveries Scheduled for regeneration"));
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
        $results = $this->food->getLargestAddresses();
        return response()->json(array("status" => "success", "message" => "Most common addresses", "data" => $results));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getMenu(Request $request) {
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

    public function deleteContentItem(Request $request, $item) {
        //$request2 = $this->cleanSearch->handleOrder($user, $request);
        Article::where("id", $item)->delete();
        return response()->json([
                    'status' => "success",
                    "message" => "item deleted",
        ]);
    }

    public function deleteMessageItem(Request $request, $item) {
        Translation::where("id", $item)->delete();
        return response()->json([
                    'status' => "success",
                    "message" => "item deleted",
        ]);
    }

    public function deleteZoneItem(Request $request, $item) {
        CoveragePolygon::where("id", $item)->delete();
        return response()->json([
                    'status' => "success",
                    "message" => "item deleted",
        ]);
    }
    
    public function createZoneItem(Request $request) {
        $data = $request->all();
        $item = CoveragePolygon::create($data);
        return response()->json([
                    'status' => "success",
                    "message" => "item created",
                    "item" => $item
        ]);
    }
    
    public function updateZoneItem(Request $request, $item) {
        $data = $request->all();
        CoveragePolygon::where("id", $item)->update($data);
        return response()->json([
                    'status' => "success",
                    "message" => "item updated",
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getMessages(Request $request) {
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

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getZones(Request $request) {
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

}
