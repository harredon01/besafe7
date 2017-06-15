<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditBilling;

class SourceApiController extends Controller {

    /**
     * The edit profile implementation.
     *
     */
    protected $editBilling;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditBilling $editBilling) {
        $this->editBilling = $editBilling;
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $source) {
        $user = $request->user();
        $sources = $this->editBilling->getSources($user, $source);
        return response()->json(array("user" => $user, "sources" => $sources));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $source) {
        $user = $request->user();
        $data = $request->all();
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');
        $data['cookie'] = $request->cookie('name');
        return response()->json($this->editBilling->createSource($user, $source, $data));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $source, $id) {
        $user = $request->user();
        $sources = $this->editBilling->getSource($user, $source, $id);
        return response()->json(array("user" => $user, "sources" => $sources));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $source, $id) {
        $user = $request->user();
        $data = $request->all();
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');
        $data['cookie'] = $request->cookie('name');
        return response()->json($this->editBilling->editSource($user, $source, $id, $data));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $source, $id) {
        $user = $request->user();
        return response()->json($this->editBilling->deleteSource($user, $source, $id));
    }

}
