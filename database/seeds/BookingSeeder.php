<?php

use Illuminate\Database\Seeder;
use App\Services\EditBooking;
use App\Services\PayU;
use App\Services\EditAlerts;
use App\Services\Rapigo;
use App\Jobs\ApprovePayment;
use App\Models\Merchant;
use App\Models\Condition;
use App\Models\User;
use App\Models\Delivery;
use App\Models\Route;
use App\Models\OrderAddress;

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
        $this->createBookingAvailability();
    }

    public function createBookingAvailability() {
        $merchant = Merchant::find(1302);
        $theCondition = Condition::find(1);
        $user = User::find(2);
        $sql = "select * from orders o join order_conditions oc on oc.order_id = o.id where o.status = 'approved' and oc.condition_id = $theCondition->id and o.user_id = $user->id;";
        $orders = DB::select($sql);
        //dd($merchant->availabilities);
//        $serviceBooking = new \App\Models\Availability();
//        $serviceBooking->make(['range' => 'monday', 'from' => '08:00 am', 'to' => '12:30 pm', 'is_bookable' => true])
//                ->bookable()->associate($merchant)
//                ->save();
        $merchant->newBooking($user, '2019-07-15 15:44:12', '2019-07-15 16:30:11');
        dd($merchant->availabilities);
        $merchant->newAvailability('mon', '08:00 am', '12:30 pm');
        $merchant->newAvailability('mon', '02:00 pm', '06:00 pm');
        $merchant->newAvailability('tue', '08:00 am', '12:30 pm');
        $merchant->newAvailability('tue', '02:00 pm', '06:00 pm');
        $merchant->newAvailability('wed', '08:00 am', '12:30 pm');
        $merchant->newAvailability('wed', '02:00 pm', '06:00 pm');
        $merchant->newAvailability('thu', '08:00 am', '12:30 pm');
        $merchant->newAvailability('thu', '02:00 pm', '06:00 pm');
        $merchant->newAvailability('fri', '08:00 am', '12:30 pm');
        $merchant->newAvailability('fri', '02:00 pm', '06:00 pm');
        dd($merchant - availabilities);
    }

    public function createBooking() {
        
    }

}
