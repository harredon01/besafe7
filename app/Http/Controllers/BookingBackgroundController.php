<?php

namespace App\Http\Controllers;
use App\Services\BookingBackground;
use Illuminate\Http\Request;

class BookingBackgroundController extends Controller
{

    /**
     * The edit alerts implementation.
     *
     */
    protected $bookingBackground;

    /**
     * Create a new controller instance.
     * 
     * @return void
     */
    public function __construct(BookingBackground $bookingBackground) {
        $this->bookingBackground = $bookingBackground;
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function startMeeting() {
        return $this->bookingBackground->startMeeting();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function terminateOpenChatRooms() {
        return $this->bookingBackground->terminateOpenChatRooms();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function sendStartReminder() {
        return $this->bookingBackground->sendStartReminder();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteOldBookings() {
        return $this->bookingBackground->deleteOldBookings();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function remindLates() {
        return $this->bookingBackground->remindLates();
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postleaveChatroom(Request $request) {
        $user = $request->user();
        return response()->json($this->bookingBackground->leaveChatroom($user,$request->all()));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postRegisterConnection(Request $request) {
        $user = $request->user();
        return response()->json($this->bookingBackground->registerConnection($user,$request->all()));
    }
}
