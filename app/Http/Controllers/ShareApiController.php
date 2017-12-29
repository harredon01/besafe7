<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\AddFollower;
use App\Services\ShareObject;

class ShareApiController extends Controller {

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * The edit alerts implementation.
     *
     */
    protected $shareObject;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, ShareObject $shareObject) {
        $this->shareObject = $shareObject;
        $this->auth = $auth;
        $this->middleware('auth:api');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postAddFollower(Request $request) {
        $user = $request->user();
        dispatch(new AddFollower($user, $request->all()));
        //return $this->shareObject->addFollower($request->all(), $user);
        return response()->json(['status' => 'success', 'message' => 'postAddFollower queued']);
    }

}
