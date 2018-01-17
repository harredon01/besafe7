<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EditRating;
class FavoriteController extends Controller
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
    public function postAddFavoriteObject(Request $request) {
        $validator = $this->editRating->validatorFavorite($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        $user = $request->user();
        return $this->editRating->addFavoriteObject($request->all(), $user);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postDeleteFavoriteObject(Request $request) {
        $user = $request->user();
        return $this->editRating->deleteFavoriteObject($request->all(), $user);
    }
}
