<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Runner;

class RunnerApiController extends Controller {

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
    protected $runner;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, Runner $runner) {
        $this->runner = $runner;
        $this->auth = $auth;
        $this->middleware('auth:api');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postRouteStarted(Request $request) {
        $user = $request->user();
        return $this->runner->routeStarted($request->all(),$user);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postRouteCompleted(Request $request) {
        $user = $request->user();
        return $this->runner->routeCompleted($request->all(),$user);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postStopArrived(Request $request) {
        $user = $request->user();
        return $this->runner->stopArrived($request->all());
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postStopCompleted(Request $request) {
        $user = $request->user();
        return $this->runner->stopComplete($request->all());
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postStopFailed(Request $request) {
        $user = $request->user();
        return $this->runner->stopFailed($request->all());
    }

}
