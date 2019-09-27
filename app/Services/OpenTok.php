<?php

namespace App\Services;

use OpenTok\OpenTok;
use OpenTok\MediaMode;
use App\Services\Notifications;
use Validator;

class OpenTok {

    const MODEL_PATH = 'App\\Models\\';
    const OBJECT_BOOKING = 'Booking';
    const BOOKING_APPROVED = 'booking_approved';
    const BOOKING_CREATED = 'booking_created';
    const BOOKING_DENIED = 'booking_denied';
    const BOOKING_CANCELED = 'booking_cancelled';
    const BOOKING_RESCHEDULE = 'booking_reschedule';
    const BOOKING_STARTING = 'booking_starting';
    const BOOKING_COMPLETED = 'booking_completed';

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $openTok;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $notifications;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(Notifications $notifications) {
        $this->notifications = $notifications;
        $this->openTok = new OpenTok(env('OPENTOK_API_KEY'), env('OPENTOK_API_SECRET'));
    }

    public function createSession() {
        // Create a session that attempts to use peer-to-peer streaming:
        $session = $opentok->createSession();
        return $session;
    }

    public function createChatroom($booking) {
        $bookable = $booking->bookable_type::find($booking->bookable_id);
        $user = $bookable->users()->first();
        $client = $booking->customer_type::find($booking->customer_id);
        $session = $this->openTok->createSession();
        $bookableToken = $session->generateToken();
        $payload = [
            "booking_id" => $booking->id,
            "sessionId" => $session->getSessionId(),
            "token" => $bookableToken
        ];
        $followers = [$user];
        $data = [
            "trigger_id" => $booking->id,
            "message" => "",
            "subject" => "",
            "object" => self::OBJECT_BOOKING,
            "sign" => true,
            "payload" => $payload,
            "type" => self::BOOKING_STARTING,
            "user_status" => "normal"
        ];
        $date = date("Y-m-d H:i:s");
        $this->notifications->sendMassMessage($data, $followers, null, true, $date, true);
        $clientToken = $session->generateToken();
        $payload = [
            "booking_id" => $booking->id,
            "token" => $clientToken
        ];
        $followers = [$client];
        $data = [
            "trigger_id" => $booking->id,
            "message" => "",
            "subject" => "",
            "object" => self::OBJECT_BOOKING,
            "sign" => true,
            "payload" => $payload,
            "type" => self::BOOKING_STARTING,
            "user_status" => "normal"
        ];
        $date = date("Y-m-d H:i:s");
        $this->notifications->sendMassMessage($data, $followers, null, true, $date, true);
    }
    
    public function endChatroom($booking) {
        
    }
    

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorGetBookings(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'query' => 'required|max:255',
                    'object_id' => 'required|max:255',
                    'from' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorStatusBookings(array $data) {
        return Validator::make($data, [
                    'object_id' => 'required|max:255',
                    'status' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorScheduleBooking(array $data) {
        return Validator::make($data, [
                    'object_id' => 'required|max:255',
                    'status' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorCreateBooking(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'object_id' => 'required|integer|min:1',
                    'from' => 'required|max:255',
                    'to' => 'required|max:255',
        ]);
    }

    /**
     * Get a validator for an incoming edit profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validatorCreateAvailability(array $data) {
        return Validator::make($data, [
                    'type' => 'required|max:255',
                    'object_id' => 'required|integer|min:1',
        ]);
    }

}
