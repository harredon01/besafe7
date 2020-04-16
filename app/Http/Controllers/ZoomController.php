<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ZoomMeetings;
use App\Jobs\PayUCron;
use Illuminate\Http\RedirectResponse;

class ZoomController extends Controller {

    /**
     * The edit order implementation.
     *
     */
    protected $zoom;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ZoomMeetings $zoom) {
        $this->zoom = $zoom;
    }

    public function webhook(Request $request) {
        return response()->json($this->zoom->webhook($request->all()),200);
        //dispatch(new PayUCron());
    }
}
