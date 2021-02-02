<?php

namespace App\Http\Controllers;

use App\Services\MiPaquete;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Unlu\Laravel\Api\QueryBuilder;

class MiPaqueteController extends Controller
{

    /**
     * The edit alerts implementation.
     *
     */
    protected $miPaquete;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MiPaquete $rapigo) {
        $this->miPaquete = $rapigo;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function authenticate() {
        $this->miPaquete->authenticate("https://ecommerce.dev.mipaquete.com/api/auth");
        $this->miPaquete->authenticate("https://ecommerce.mipaquete.com/api/auth");
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
