<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Food;
use App\Services\Routing;
use App\Services\EditDelivery;
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
use Artisan;

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
    private $routing;
    private $delivery;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Food $food, Routing $routing, EditDelivery $editDelivery) {
        $this->food = $food;
        $this->routing = $routing;
        $this->delivery = $editDelivery;
        $this->middleware('auth:api')->except('getZones', 'getActiveIndicators', 'getTips');
        $this->middleware('admin')->except(['getRouteInfo', 'getZones', 'getActiveIndicators', 'getTips']);
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
        $results = $this->routing->getShippingCosts($user, $status);
        return response()->json(array("status" => "success", "message" => "Summary shipping cost calculation queued"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getPurchaseOrder() {
        $debug = env('APP_DEBUG');
        if ($debug == 'true') {
            return response()->json(array("status" => "success", "message" => "Debug mode doing nothing"));
        }
        $date = date_create();
        $date = $this->food->getNextValidDate($date);
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
        $debug = env('APP_DEBUG');
        if ($debug == 'true') {
            return response()->json(array("status" => "success", "message" => "Debug mode doing nothing"));
        }
        $date = date_create();
        $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
        if ($dayofweek > 5) {
            return response()->json(array("status" => "success", "message" => "Reminder Sent"));
        }
        $this->food->sendReminder();
        return response()->json(array("status" => "success", "message" => "Reminder Sent"));
    }

    public function backups() {
        $debug = env('APP_DEBUG');
        if ($debug == 'true') {
            return response()->json(array("status" => "success", "message" => "Debug mode doing nothing"));
        }
        Artisan::call('db:backup');
    }

    public function sendNewsletter() {
        $this->food->sendNewsletter();
        //dispatch(new \App\Jobs\SendNewsletter());
    }

    public function getActiveIndicators() {
        $data = [
            ["name" => "Cal.", "totals" => 56000],
            ["name" => "Carb.", "totals" => 8250],
            ["name" => "Prot.", "totals" => 1500],
            ["name" => "Grasas.", "totals" => 1800],
            ["name" => "Fibra.", "totals" => 840]
        ];
        return ["status" => 'success', "results" => $data];
    }

    public function getTips() {
        $data = [
            ["Profesionales en la cocina, expertos en nutrición."],
            ["Un servicio práctico y delicioso que te ayudará a llevar una alimentación sana y balanceada."],
            ["Cada día 3 opciones de menús diferentes (selecciona 1 entrada y 1 plato fuerte)"],
            ["Personaliza y confirma tu menú antes de las 10 Pm. del día anterior."],
            ["Entregamos tus Almuerzos frescos entre las 10:30 Am y las 12:30 Pm  (puedes calentar en microondas)."],
            ["¡Tus almuerzos no se vencen! Prográmalos en cualquier fechas durante todo un año."],
        ];
        return ["status" => 'success', "results" => $data];
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
        $this->routing->buildScenarioLogistics($request->all());

        //dispatch(new \App\Jobs\BuildScenarioLogistics($request->all()));
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
    public function updateUserDeliveriesAddress($user, $address) {
        return response()->json(array("status" => "success", "message" => $this->routing->updateUserDeliveriesAddress($user, $address)));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function updateMissingDish(Request $request) {
        return response()->json($this->delivery->adminPostDeliveryOptions($request->all()));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function approvePayment($id) {
        $payment = \App\Models\Payment::find($id);
        dispatch(new \App\Jobs\ApprovePayment($payment, "Food"));
        return response()->json(array("status" => "success", "message" => "Payment scheduled"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioRouteId($id) {
        dispatch(new \App\Jobs\BuildScenarioRouteId($id, "", false));
        /* $routes = Route::where("id", $id)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
          $checkResult = $this->routing->checkScenario($routes, $hash);
          if ($checkResult) {
          $this->routing->buildScenarioTransit($routes);
          } */
        return response()->json(array("status" => "success", "message" => "Scenario Scheduled"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioPositive($scenario, $provider) {
        dispatch(new \App\Jobs\BuildScenarioPositive($scenario, $provider, "", false));
        /* $routes = Route::where("type", $scenario)->where("status", "pending")->where("provider", $provider)->with(['deliveries.user'])->orderBy('id')->get();
          $checkResult = $this->routing->checkScenario($routes, $hash);
          if ($checkResult) {
          $routes = Route::whereColumn('unit_price', '>', 'unit_cost')->where("status", "pending")->where("provider", $provider)->where("type", $scenario)->with(['deliveries.user'])->orderBy('id')->get();
          $this->routing->buildScenarioTransit($routes);
          } */
        return response()->json(array("status" => "success", "message" => "Scenario Scheduled"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildCompleteScenario($scenario, $provider) {
        dispatch(new \App\Jobs\BuildCompleteScenario($scenario, $provider, "", false));
        /* $routes = Route::where("type", $scenario)->where("status", "pending")->where("provider", $provider)->with(['deliveries.user'])->orderBy('id')->get();
          $checkResult = $this->routing->checkScenario($routes, $hash);
          if ($checkResult) {
          $this->routing->buildScenarioTransit($routes);
          } */
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
        $this->routing->regenerateScenarios();
        return response()->json(array("status" => "success", "message" => "Scenarios Scheduled for regeneration"));
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function delegateDeliveries(Request $request) {
        $this->routing->delegateDeliveries($request->all());
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
        $result = $this->routing->getRouteInfo($delivery);
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
        $results = $this->routing->getLargestAddresses();
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
    public function getDeliveries(Request $request) {
        $queryBuilder = new QueryBuilder(new Delivery, $request);
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
