<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ZoomMeetings;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateMerchant implements ShouldQueue {

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ZoomMeetings $zoom) {
        $user = $this->user;
        
        $source = $user->sources()->where('gateway','MercadoPago')->first();
        $exists = false;
        if ($source) {
            $extra = json_decode($source->extra, true); 
            if (array_key_exists("access_token",$extra)) {
                $exists = true;
            }
        }
        if (!$exists) {
            $payload = [
                "url" => "https://auth.mercadopago.com.co/authorization?client_id=7257598100783047&response_type=code&platform_id=mp&redirect_uri=https%3A%2F%2Fdev.lonchis.com.co%2Fmercado%2Freturn?user_id=".$user->id,
            ];
            $followers = [$user];
            $data = [
                "trigger_id" => $user->id,
                "message" => "",
                "subject" => "",
                "object" => "User",
                "sign" => true,
                "payload" => $payload,
                "type" => "mercadopago",
                "user_status" => "normal"
            ];
            $date = date("Y-m-d H:i:s");
            $notifications = app('Notifications');
            $notifications->sendMassMessage($data, $followers, null, true, $date, true);
        }
        $zoom->createUser($user);
    }

}
