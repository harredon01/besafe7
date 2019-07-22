<?php

namespace App\Http\Controllers;
use App\Services\EditBooking;
use Illuminate\Http\Request;

class BookingApiController extends Controller
{

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
    public function __construct(EditBooking $editBooking) {
        $this->editBooking = $editBooking;
        $this->middleware('auth:api');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postAddBookingObject(Request $request) {
        $user = $request->user();
        return $this->editBooking->addBookingObject($request->all(), $user);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postAddAvailabilitiesObject(Request $request) {
        $user = $request->user();
        return $this->editBooking->addAvailabilityObject($request->all(), $user);
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
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getAvailabilitiesObject(Request $request) {
        $user = $request->user();
        return $this->editBooking->getAvailabilitiesObject($request->all());
    }
}
