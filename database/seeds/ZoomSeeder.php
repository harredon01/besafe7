<?php

use Illuminate\Database\Seeder;
use App\Services\ZoomMeetings;
use App\Services\EditBooking;
use App\Models\Merchant;
use App\Models\Booking;
use App\Models\User;
use App\Models\Push;

class ZoomSeeder extends Seeder {


    /**
     * The edit profile implementation.
     *
     */
    protected $zoom;
    /**
     * The edit profile implementation.
     *
     */
    protected $booking;

    public function __construct(ZoomMeetings $zoom,EditBooking $editBooking ) {
        $this->zoom = $zoom;
        $this->booking = $editBooking;
    }
 
    public function run() {
        $user = User::find(2);
        $results = $this->booking->createChatroom(145);
        dd($results);
        $results = $this->zoom->endMeeting("746462367");
        dd($results);
        return null;
        $token = "eyJhbGciOiJSUzI1NiIsImtpZCI6Ijc5YzgwOWRkMTE4NmNjMjI4YzRiYWY5MzU4NTk5NTMwY2U5MmI0YzgiLCJ0eXAiOiJKV1QifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLmdvb2dsZS5jb20iLCJhenAiOiI2NTAwNjUzMTI3NzctZzV0NjNqMjI3bmNhMjV1ZThhMm5pMGN2ZWpnazlmZTMuYXBwcy5nb29nbGV1c2VyY29udGVudC5jb20iLCJhdWQiOiI2NTAwNjUzMTI3NzctaDZzcTlsZWVoY3FvNzczMm0wcjhvdDNnZWsxYnRpZzkuYXBwcy5nb29nbGV1c2VyY29udGVudC5jb20iLCJzdWIiOiIxMTA3ODM0MzgxNTEwMTYxMjcxOTgiLCJlbWFpbCI6ImhhcnJlZG9uMDFAZ21haWwuY29tIiwiZW1haWxfdmVyaWZpZWQiOnRydWUsIm5hbWUiOiJIb292ZXJ0IEFycmVkb25kbyIsInBpY3R1cmUiOiJodHRwczovL2xoMy5nb29nbGV1c2VyY29udGVudC5jb20vLWtlTHhqU21MMkNvL0FBQUFBQUFBQUFJL0FBQUFBQUFBQUFBL0FDSGkzcmVmd3JUcC1xeDcxaTdleGw3WHR3YjNkenFLQlEvczk2LWMvcGhvdG8uanBnIiwiZ2l2ZW5fbmFtZSI6Ikhvb3ZlcnQiLCJmYW1pbHlfbmFtZSI6IkFycmVkb25kbyIsImxvY2FsZSI6ImVuIiwiaWF0IjoxNTgyMjU0ODg4LCJleHAiOjE1ODIyNTg0ODh9.Itmow9IGXYatz8YSxiqd-KmrIUWW-ItNvni3oMHo5A4bISoTz7R7w-0rHbh2jklmCuWuyqXLALGvvhOFdU2969fpDxYFOx0BztJGygx_CSw9oOmuWdUyEjfA_GtIXyVpXN5jrpd_0Ajg_rbSH08-2A2MINtxQ6zFgRTFvXIqKMAPuuvrT241pHKEHflo1jMnLDrK04HmMee5NLab4y3AkEWt71C_ADQ69EURWD34Td-LMlv-algYwt_RTekwCB89prYgX6dXszA81yaIEwswtKvYqOFiOUtCW-4Tmw6ks1qMdUu4c2yXSMSDet-G7Nx0BHShkvJb8Cz07NGqnlTing";
        $user = Socialite::driver('google')->userFromToken($token);
        dd($user);
        $user = User::find(1);
        $source = $user->sources()->where("gateway","MercadoPago")->first();
        $client = $this->mercadoPago->getClient($source);
        dd($client->cards);
        $payment = Payment::find(630);
        $data = ["payment_id"=>630];
        $user = User::find(2);
        $source = "MercadoPago";
        //dispatch(new ApprovePayment($payment, "Food"));
        //$this->billing->payCreditCard($user, $source, $data);
        
//        $user = User::find(1);
//        $user2 = User::find(2);
//        $users = [$user2];
//        $data = [
//                        "trigger_id" => $user->id,
//                        "message" => "test",
//                        "payload" => ['order_id'=>1,"order_total"=>12,"order_status"=>"test"],
//                        "object" => "test",
//                        "sign" => true,
//                        "type" => self::ORDER_PAYMENT,
//                        "user_status" => "test"
//                    ];
//        $this->food->sendMassMessage($data, $users, $user, true, null,"food");
//        $this->food->runCompleteSimulation();
//        $this->food->importDishes();
//        $this->deleteOldData();
//        $this->food->generateRandomDeliveries(4.670129, -74.051013);
//        $this->food->prepareRoutingSimulation(4.670129, -74.051013);
    }

}
