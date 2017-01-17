<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditVehicle;
use App\Services\EditOperation;


class OperationsApiController extends Controller {

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
     * The edit profile implementation.
     *
     */
    protected $editOperations;
    
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function __construct(Guard $auth, EditVehicle $editVehicles, EditOperation $editOperations) {
        $this->editVehicles = $editVehicles;
        $this->editOperations = $editOperations;
        $this->auth = $auth;
        $this->middleware('jwt.auth');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function deleteRoute(Request $request) {
        $user = $this->auth->user();
        $route = $this->editOperations->deleteRoute($user, $request->only('route_id'));
        return response()->json(compact('route'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function deleteCargo(Request $request) {
        $user = $this->auth->user();
        $cargo = $this->editOperations->deleteCargo($user, $request->only('cargo_id'));
        return response()->json(compact('cargo'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getRoute($routeId) {
        $route = $this->editOperations->getRoute($routeId);
        return response()->json(compact('route'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getCargo($cargoId) {
        $user = $this->auth->user();
        $cargo = $this->editOperations->getCargo($cargoId);
        return response()->json(compact('cargo'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getCargosUser() {
        $user = $this->auth->user();
        $cargos = $this->editOperations->getCargosUser($user);
        return response()->json(compact('cargos'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function postRoute(Request $request) {
        $user = $this->auth->user();
        $route = $this->editOperations->saveOrCreateRoute($request->all(), $user );
        return response()->json(compact('route'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function postCargo(Request $request) {
        $user = $this->auth->user();
        $cargo = $this->editOperations->saveOrCreateCargo($request->all(), $user );
        return response()->json(compact('cargo'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function setCargoRoute(Request $request) {
        $user = $this->auth->user();
        $cargo = $this->editOperations->setCargoRoute($user, $request->all()  );
        return response()->json(compact('cargo'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function findOperations(Request $request) {
        $user = $this->auth->user();
        $operations = $this->editOperations->findOperations($request->all());
        return response()->json(compact('operations'));
    }
}