<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Services\Food;
use App\Models\Route;
use App\Models\User;
use App\Models\CoveragePolygon;

class FoodController extends Controller {
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, Food $food) {
        $this->auth = $auth;
        $this->food = $food;
        $this->middleware('auth', ['except' => ['buildScenarioRouteId', 'buildScenarioPositive','buildCompleteScenario','getScenarioStructure','cancelUserCredit','cancelDelivery']]);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index() {
        return view('user.editProfile');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function access() {
        $user = $this->auth->user();
        return view('user.editAccess')->with('user', $user);
    }


    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getPolygons() {
        $user = $this->auth->user();
        $polygons = CoveragePolygon::where('lat', "<>", 0)->where('long', "<>", 0)->get();
        return view('food.polygons')->with('polygons', $polygons);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getScenarioStructure($scenario,$provider,$status, $hash) {
        $routes = Route::where("type", $scenario)->where("status", "pending")->with(['stops.address'])->limit(1)->orderBy('id', 'asc')->get();
        $check = $this->food->checkScenario($routes, $hash);
        if ($check) {
            $data =[
                "type"=>$scenario,
                "status"=>$status,
                "provider"=>$provider
            ];
            //dd($data);
            $user = User::find(2);
            dispatch(new \App\Jobs\GetScenarioStructure($user,$data));
            //$this->food->getTotalEstimatedShipping($data);
            return view('food.buildScheduled')->with('message', "El detalle de las rutas fue enviado a tu correo. Tambien puedes verlo en la pagina");
        } else {
            return view('food.buildScheduled')->with('message', "La validacion no fue exitosa");
        }
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function cancelUserCredit($user,$hash) {
        $users = User::where('id',$user)->limit(1)->get();
        $check = $this->food->checkScenario($users, $hash);
        if (true) {
            $this->food->suspendDelivery($users[0],"cancel");
            return view('food.buildScheduled')->with('message', "El detalle de las rutas fue enviado a tu correo. Tambien puedes verlo en la pagina");
        } else {
            return view('food.buildScheduled')->with('message', "La validacion no fue exitosa");
        }
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function cancelDelivery($user,$hash) {
        $users = User::where('id',$user)->limit(1)->get();
        $check = $this->food->checkScenario($users, $hash);
        if (true) {
            $this->food->suspendDelivery($users[0],"trade");
            return view('food.buildScheduled')->with('message', "El detalle de las rutas fue enviado a tu correo. Tambien puedes verlo en la pagina");
        } else {
            return view('food.buildScheduled')->with('message', "La validacion no fue exitosa");
        }
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioRouteId($id, $hash) {
        dispatch(new \App\Jobs\BuildScenarioRouteId($id, $hash));
        //$results = $this->food->buildScenarioRouteId($id, $hash);
        return view('food.buildScheduled')->with('message', "Ruta enviada a rapigo. Si la autenticacion pasa sera construida");
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioPositive($scenario,$provider, $hash) {
        dispatch(new \App\Jobs\BuildScenarioPositive($scenario,$provider, $hash));
        //$results = $this->food->buildScenarioPositive($scenario,$provider, $hash);
        return view('food.buildScheduled')->with('message', "Escenario enviado a rapigo. Si la autenticacion pasa sera construido");
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildCompleteScenario($scenario,$provider, $hash) {
        dispatch(new \App\Jobs\BuildCompleteScenario($scenario,$provider, $hash));
        //$results = $this->food->buildCompleteScenario($scenario,$provider, $hash);
        return view('food.buildScheduled')->with('message', "Escenario positivo enviado a rapigo. Si la autenticacion pasa sera construido");
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function removeCreditUser($user, $hash) {
        $results = $this->food->removeCreditUser($user, $hash);
        return view('food.buildScheduled')->with('data', $results);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function regenerateDeliveries($shipping = "Rapigo") {
        $this->food->deleteRandomDeliveriesData();
        $polygons = CoveragePolygon::where('lat', "<>", 0)->where('long', "<>", 0)->get();
        foreach ($polygons as $value) {
            $this->food->generateRandomDeliveries($value);
        }
        $this->food->prepareRoutingSimulation($polygons,$shipping);
        return view('food.polygons')->with('polygons', $polygons);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function runRecurringTask() {
        $this->food->runRecurringTask();
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function reprogramDeliveries() {
        $this->food->reprogramDeliveries();
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getRoutes() {
        $user = $this->auth->user();

        return view('food.routesDashboard')->with('user', $user);
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getLargestAddresses() {
        $user = $this->auth->user();

        return view('food.addressesDashboard')->with('user', $user);
    }

}
