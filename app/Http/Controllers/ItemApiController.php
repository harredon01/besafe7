<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemApiController extends Controller {

    /**
     * The edit profile implementation.
     *
     */
    protected $editMapObject;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditMapObject $editMapObject) {
        $this->editMapObject = $editMapObject;
        $this->middleware('auth:api')->except('index');
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        //
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fulfillItem(Request $request, $item) {
        if ($request->file('photo')->isValid()) {
            $path = $request->photo->path();
        }
        $user = $request->user();
        return response()->json($this->editOrder->prepareOrder($user, $platform, $request->all()));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function acceptItem(Request $request, $item) {
        $user = $request->user();
        return response()->json($this->editOrder->prepareOrder($user, $platform, $request->all()));
    }

}
