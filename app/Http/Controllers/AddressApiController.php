<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Services\EditUserData;

class AddressApiController extends Controller {

    /**
     * The edit profile implementation.
     *
     */
    protected $editUserData;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditUserData $editUserData) {
        $this->editUserData = $editUserData;
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $user = $request->user();
        $addresses = $this->editUserData->getAddresses($user);
        return response()->json(array("user" => $user, "addresses" => $addresses));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $user = $request->user();
        $validator = $this->editUserData->validatorAddress($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        $data = $request->only([
            'address_id',
            'firstName',
            'lastName',
            'address',
            'type',
            'postal',
            'phone',
            'lat',
            'long',
            'city_id',
            'region_id',
            'country_id',
        ]);
        return response()->json($this->editUserData->createOrUpdateAddress($user, $data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $user = $request->user();
        $validator = $this->editUserData->validatorAddress($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        $data = $request->only([
            'address_id',
            'firstName',
            'lastName',
            'address',
            'type',
            'postal',
            'phone',
            'lat',
            'long',
            'city_id',
            'region_id',
            'country_id',
        ]);
        return response()->json($this->editUserData->createOrUpdateAddress($user, $data));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request) {
        $user = $request->user();
        $address = $this->editUserData->getAddress($user, $id);
        return response()->json(array("user" => $user, "address" => $address));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $user = $request->user();
        $validator = $this->editUserData->validatorAddress($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        $data = $request->only([
            'address_id',
            'firstName',
            'lastName',
            'address',
            'type',
            'postal',
            'phone',
            'lat',
            'long',
            'city_id',
            'region_id',
            'country_id',
        ]);
        return response()->json($this->editUserData->createOrUpdateAddress($user, $data));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $user = $request->user();
        $validator = $this->editUserData->validatorAddress($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        $data = $request->only([
            'address_id',
            'firstName',
            'lastName',
            'address',
            'type',
            'postal',
            'phone',
            'lat',
            'long',
            'city_id',
            'region_id',
            'country_id',
        ]);
        return response()->json($this->editUserData->createOrUpdateAddress($user, $data));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($address_id, Request $request) {
        $user = $request->user();
        return response()->json($this->editUserData->deleteAddress($user, $address_id));
    }

}
