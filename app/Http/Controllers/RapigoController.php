<?php

namespace App\Http\Controllers;

use App\Services\Rapigo;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Unlu\Laravel\Api\QueryBuilder;

class RatingController extends Controller
{
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
    protected $rapigo;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, Rapigo $rapigo) {
        $this->rapigo = $rapigo;
        $this->auth = $auth;
        $this->middleware('auth:api');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getActiveRoutesUpdate() {
        return $this->rapigo->getActiveRoutesUpdate();
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getRatingsObject(Request $request) {
        $queryBuilder = new QueryBuilder(new Rating, $request);
        $result = $queryBuilder->build()->paginate();
        return response()->json([
                    'data' => $result->items(),
                    "total" => $result->total(),
                    "per_page" => $result->perPage(),
                    "page" => $result->currentPage(),
                    "last_page" => $result->lastPage(),
                    "last_page" => $result->lastPage(),
        ]);
    }
}
