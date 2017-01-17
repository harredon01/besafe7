<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditVehicle;

class VehiclesApiController extends Controller {

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
    protected $editVehicles;
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function __construct(Guard $auth, EditVehicle $editVehicles) {
        $this->editVehicles = $editVehicles;
        $this->auth = $auth;
        $this->middleware('jwt.auth');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function deleteVehicle(Request $request) {
        $user = $this->auth->user();
        $vehicle = $this->editVehicles->deleteVehicle($user, $request->only('vehicle_id'));
        return response()->json(compact('vehicle'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getVehicleRoutes(Request $request) {
        $routes = $this->editVehicles->getVehicleRoutes($request->all());
        return response()->json(compact('routes'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getVehicle($vehicleId) {
        $user = $this->auth->user();
        $vehicle = $this->editVehicles->getVehicle($user, $vehicleId);
        return response()->json(compact('vehicle'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function postVehicle(Request $request) {
        $user = $this->auth->user();
        $vehicle = $this->editVehicles->saveOrCreateVehicle($request->all(),$user);
        return response()->json(compact('vehicle'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getVehiclesUser() {
        $user = $this->auth->user();
        $user = $this->editVehicles->getVehiclesUser($user);
        return response()->json(compact('user'));
    }

}
