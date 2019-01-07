<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Services\Food;
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
        $this->middleware('auth');
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
    public function getSummaryShipping($polygon) {
        $user = $this->auth->user();
        $results = $this->food->getShippingCosts($polygon);
        return view('food.summary')->with('data', $results)->with('polygon_id', $polygon);
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getPolygons() {
        $user = $this->auth->user();
        $polygons = CoveragePolygon::where('lat',"<>",0)->where('long',"<>",0)->get();
        return view('food.polygons')->with('polygons', $polygons);
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getScenarioStructure($scenario) {
        $results = $this->food->getTotalEstimatedShipping($scenario);
        return view('food.choose')->with('data', $results['routes'])->with('result', $results['result']);
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioRouteId($id,$hash) {
        $results = $this->food->buildScenarioRouteId($id,$hash);
        return view('food.buildScheduled')->with('data', $results);
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildScenarioPositive($scenario,$hash) {
        $results = $this->food->buildScenarioPositive($scenario,$hash);
        return view('food.buildScheduled')->with('data', $results);
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function buildCompleteScenario($scenario,$hash) {
        $results = $this->food->buildCompleteScenario($scenario,$hash);
        return view('food.buildScheduled')->with('data', $results);
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function removeCreditUser($user,$hash) {
        $results = $this->food->removeCreditUser($user,$hash);
        return view('food.buildScheduled')->with('data', $results);
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function regenerateScenarios($polygon,$hash) {
        $this->food->regenerateScenarios($polygon,$hash);
        return view('food.simulationScheduled');
    }
    

}
