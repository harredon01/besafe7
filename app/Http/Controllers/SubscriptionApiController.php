<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditBilling;

class SubscriptionApiController extends Controller {

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
    public function index(Request $request) {
        $user = $request->user();
        $subscriptions = $this->editBilling->getSubscriptions($user);
        return response()->json(array("user" => $user, "subscriptions" => $subscriptions));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSubscriptionsObject(Request $request, $type, $object) {
        $user = $request->user();
        $subscriptions = $this->editBilling->getSubscriptionsObject($user, $type, $object);
        return response()->json(array("user" => $user, "subscriptions" => $subscriptions));
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
        return response()->json($this->editBilling->createSubscription($user, $source, $data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeExistingSource(Request $request, $source) {
        $user = $request->user();
        $data = $request->all();
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');
        $data['cookie'] = $request->cookie('name');
        return response()->json($this->editBilling->createSubscriptionExistingSource($user, $source, $data));
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
        return response()->json($this->editBilling->editSubscription($user, $source, $id, $data));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $source, $id) {
        $user = $request->user();
        return response()->json($this->editBilling->deleteSubscription($user, $source, $id));
    }

}
