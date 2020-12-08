<?php

namespace App\Http\Controllers;

use App\Services\Rapigo;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Unlu\Laravel\Api\QueryBuilder;

class RapigoController extends Controller
{

    /**
     * The edit alerts implementation.
     *
     */
    protected $rapigo;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Rapigo $rapigo) {
        $this->rapigo = $rapigo;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getActiveRoutesUpdate() {
        $debug = env('APP_DEBUG');
        if($debug == 'true'){
            return response()->json(array("status" => "success", "message" => "Debug mode doing nothing"));
        }
        return $this->rapigo->getActiveRoutesUpdate();
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getContent() {
        return view(config("app.views").'.rapigo-track');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getKeyStatus(Request $request,$key) {
        return response()->json([
                    'status' => "success",
                    'message' => "search successful",
                    "result" => $this->rapigo->checkStatus($key)
                        ], 200);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function webhook(Request $request) {
        return response()->json([
                    'status' => "success",
                    'message' => "webhook successful",
                    "result" => $this->rapigo->webhook($request->all())
                        ], 200);
    }
}
