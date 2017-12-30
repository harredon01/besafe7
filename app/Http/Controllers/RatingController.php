<?php

namespace App\Http\Controllers;

use App\Services\EditRating;
use App\Models\Rating;
use Illuminate\Http\Request;

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
    protected $editRating;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, EditRating $editRating) {
        $this->editRating = $editRating;
        $this->auth = $auth;
        $this->middleware('auth:api');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postAddRatingObject(Request $request) {
        $validator = $this->editRating->validatorRating($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        $user = $request->user();
        return $this->editRating->addRatingObject($request->all(), $user);
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
