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
        $this->middleware('auth:api')->except("getBookingsObject");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postAddBookingObject(Request $request) {
        $user = $request->user();
        $data = $request->all();
        $data['call'] = false;
        return response()->json($this->editBooking->addBookingObject($data, $user));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postEditBookingObject(Request $request) {
        $user = $request->user();
        $data = $request->all();
        return response()->json($this->editBooking->editBookingObject($data, $user));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postImmediateBookingObject(Request $request) {
        $user = $request->user();
        $data = $request->all();
        $date = date_create();
        $data["from"] =  date_format($date, "Y-m-d H:i:s"); 
        date_add($date, date_interval_create_from_date_string("1 hour"));
        $data['to'] = date_format($date, "Y-m-d H:i:s");
        $data['call'] = true;
        return response()->json($this->editBooking->addBookingObject($data, $user));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postAddAvailabilitiesObject(Request $request) {
        $user = $request->user();
        return response()->json($this->editBooking->addAvailabilityObject($request->all(), $user));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteAvailabilityObject(Request $request) {
        $user = $request->user();
        return response()->json($this->editBooking->deleteAvailabilityObject($request->all(), $user));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postChangeStatusBookingObject(Request $request) {
        $user = $request->user();
        return response()->json($this->editBooking->changeStatusBookingObject($request->all(), $user));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteBookingObject(Request $request,$booking) {
        $user = $request->user();
        return response()->json($this->editBooking->deleteBooking( $user,$booking));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postRescheduleBookingObject(Request $request) {
        $user = $request->user();
        return response()->json($this->editBooking->rescheduleBookingObject($request->all(), $user));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function startMeeting() {
        return $this->editBooking->startMeeting();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function terminateOpenChatRooms() {
        return $this->editBooking->terminateOpenChatRooms();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function sendStartReminder() {
        return $this->editBooking->sendStartReminder();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function remindLates() {
        return $this->editBooking->remindLates();
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getBookingsObject(Request $request) {
        $user = auth('api')->user();
        $data = $request->all();
        if($data['query']!="day"){
            if(!$user){
                response()->json(["status"=>"error","message"=>"access denied"],401);
            }
        }
        return response()->json($this->editBooking->getBookingsObject($data, $user));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getBooking(Request $request,$code) {
        $user = $request->user();
        return response()->json($this->editBooking->getBooking($user, $code));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getObjectsWithBookingUser(Request $request) {
        $user = $request->user();
        return response()->json($this->editBooking->getObjectsWithBookingUser($user));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getAvailabilitiesObject(Request $request) {
        $user = $request->user();
        return response()->json($this->editBooking->getAvailabilitiesObject($request->all()));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postleaveChatroom(Request $request) {
        $user = $request->user();
        return response()->json($this->editBooking->leaveChatroom($user,$request->all()));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postRegisterConnection(Request $request) {
        $user = $request->user();
        return response()->json($this->editBooking->registerConnection($user,$request->all()));
    }
}
