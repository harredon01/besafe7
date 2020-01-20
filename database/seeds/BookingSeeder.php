<?php

use Illuminate\Database\Seeder;
use App\Services\EditBooking;
use App\Services\OpenTokService;
use App\Services\EditAlerts;
use App\Services\EditOrder;
use App\Jobs\ApprovePayment;
use App\Models\Merchant;
use App\Models\Condition;
use App\Models\User;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Payment;

class BookingSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    const ORDER_PAYMENT = 'order_payment';

    /**
     * The edit profile implementation.
     *
     */
    protected $editBooking;

    public function __construct(EditBooking $editBooking) {
        $this->editBooking = $editBooking;
    }

    public function run() {
//        $this->editBooking->startMeeting();
//        $this->editBooking->remindLates();
        $booking = Booking::find(74);
        $this->editBooking->endChatroom($booking);
    }
}
