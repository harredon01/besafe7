<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Services\Food;
use App\Services\Routing;
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
    private $auth;
    
    private $food;
    
    private $routing;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, Food $food,Routing $routing) {
        $this->auth = $auth;
        $this->food = $food;
        $this->routing = $routing;
        $this->middleware('auth', ['except' => ['buildScenarioRouteId', 'buildScenarioPositive','buildCompleteScenario','getScenarioStructure','cancelUserCredit','cancelDelivery']]);
        $this->middleware('admin', ['except' => ['buildScenarioRouteId', 'buildScenarioPositive','buildCompleteScenario','getScenarioStructure','cancelUserCredit','cancelDelivery']]);
    }


    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getPolygons() {
        $user = $this->auth->user();
        $polygons = CoveragePolygon::where('lat', "<>", 0)->where('long', "<>", 0)->get();
        return view(config("app.views").'.food.polygons')->with('polygons', $polygons);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getScenarioStructure($scenario,$provider,$status, $hash) {
        $routes = Route::where("type", $scenario)->where("status", "pending")->with(['stops.address'])->limit(1)->orderBy('id', 'asc')->get();
        $check = $this->routing->checkScenario($routes, $hash);
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
            return view(config("app.views").'.food.buildScheduled')->with('message', "El detalle de las rutas fue enviado a tu correo. Tambien puedes verlo en la pagina");
        } else {
            return view(config("app.views").'.food.buildScheduled')->with('message', "La validacion no fue exitosa");
        }
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function cancelUserCredit($user,$hash) {
        $users = User::where('id',$user)->limit(1)->get();
        $check = $this->routing->checkScenario($users, $hash);
        if (true) {
            $this->food->suspendDelivery($users[0],"cancel");
            return view(config("app.views").'.food.buildScheduled')->with('message', "El detalle de las rutas fue enviado a tu correo. Tambien puedes verlo en la pagina");
        } else {
            return view(config("app.views").'.food.buildScheduled')->with('message', "La validacion no fue exitosa");
        }
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function cancelDelivery($user,$hash) {
        $users = User::where('id',$user)->limit(1)->get();
        $check = $this->routing->checkScenario($users, $hash);
        if (true) {
            $this->food->suspendDelivery($users[0],"trade");
            return view(config("app.views").'.food.buildScheduled')->with('message', "El detalle de las rutas fue enviado a tu correo. Tambien puedes verlo en la pagina");
        } else {
            return view(config("app.views").'.food.buildScheduled')->with('message', "La validacion no fue exitosa");
        }
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioRouteId($id, $hash) {
        dispatch(new \App\Jobs\BuildScenarioRouteId($id, $hash,true));
        /*$routes = Route::where("id", $id)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
        $checkResult = $this->routing->checkScenario($routes, $hash);
        if ($checkResult) {
            $this->routing->buildScenarioTransit($routes);
        }*/
        return view(config("app.views").'.food.buildScheduled')->with('message', "Ruta enviada a rapigo. Si la autenticacion pasa sera construida");
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioPositive($scenario,$provider, $hash) {
        dispatch(new \App\Jobs\BuildScenarioPositive($scenario,$provider, $hash,true));
        /*$routes = Route::where("type", $scenario)->where("status", "pending")->where("provider", $provider)->with(['deliveries.user'])->orderBy('id')->get();
        $checkResult = $this->routing->checkScenario($routes, $hash);
        if ($checkResult) {
            $routes = Route::whereColumn('unit_price', '>', 'unit_cost')->where("status", "pending")->where("provider", $provider)->where("type", $scenario)->with(['deliveries.user'])->orderBy('id')->get();
            $this->routing->buildScenarioTransit($routes);
        }*/
        return view(config("app.views").'.food.buildScheduled')->with('message', "Escenario enviado a rapigo. Si la autenticacion pasa sera construido");
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildCompleteScenario($scenario,$provider, $hash) {
        dispatch(new \App\Jobs\BuildCompleteScenario($scenario,$provider, $hash,true));
        /*$routes = Route::where("type", $scenario)->where("status", "pending")->where("provider", $provider)->with(['deliveries.user'])->orderBy('id')->get();
        $checkResult = $this->routing->checkScenario($routes, $hash);
        if ($checkResult) {
            $this->routing->buildScenarioTransit($routes);
        }*/
        return view(config("app.views").'.food.buildScheduled')->with('message', "Escenario positivo enviado a rapigo. Si la autenticacion pasa sera construido");
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function removeCreditUser($user, $hash) {
        $results = $this->food->removeCreditUser($user, $hash);
        return view(config("app.views").'.food.buildScheduled')->with('data', $results);
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
        $debug = env('APP_DEBUG');
        if($debug == 'true'){
            return response()->json(array("status" => "success", "message" => "Debug mode doing nothing"));
        }
        $this->food->reprogramDeliveries();
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getRoutes() {
        $user = $this->auth->user();

        return view(config("app.views").'.food.routesDashboard')->with('user', $user);
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function updateDeliveries() { 
        $this->food->updateDeliveries();
        return ['status'=>'success'];
    }
     
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getLargestAddresses() {
        $user = $this->auth->user();

        return view(config("app.views").'.food.addressesDashboard')->with('user', $user);
    }
}
