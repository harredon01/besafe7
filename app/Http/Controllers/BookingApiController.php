<?php

namespace App\Http\Controllers;
use App\Services\EditBooking;
use Illuminate\Http\Request;

class BookingApiController extends Controller
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
    protected $editBooking;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, EditBooking $editBooking) {
        $this->editBooking = $editBooking;
        $this->auth = $auth;
        $this->middleware('auth:api');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postAddBookingObject(Request $request) {
        $validator = $this->editBooking->validatorBooking($request->all());
        if ($validator->fails()) {
            return response()->json(array("status" => "error", "message" => $validator->getMessageBag()), 400);
        }
        $user = $request->user();
        return $this->editBooking->addBookingObject($request->all(), $user);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getBookingsObject(Request $request) {
        $user = $request->user();
        return $this->editBooking->getBookingsObject($request->all(), $user);
    }
}
