<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\StoreExport;


class StoreExportApiController extends Controller {
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

    private $store;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(StoreExport $store) {
        $this->store = $store;
        $this->middleware('auth:api');
        $this->middleware('admin');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index() {
        
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getStoreExport(Request $request) {
        $data = $request->all();
        $this->store->exportEverything($data['from'],$data['to']);
        return response()->json(array("status" => "success", "message" => "Summary shipping cost calculation queued"));
    }

    

}
