<?php

namespace App\Jobs;
use Exception;
use App\Services\Food;
use App\Services\Notifications;
use App\Models\CoveragePolygon;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RegenerateDeliveriesAndScenarios implements ShouldQueue {

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food, Notifications $notifications) {
        $food->deleteRandomDeliveriesData();
        $user = User::find(2);
        $polygons = CoveragePolygon::where('merchant_id', 1299)->where("provider","Rapigo")->get();
        foreach ($polygons as $value) {
            $food->generateRandomDeliveries($value);
        }
        $food->prepareRoutingSimulation($polygons);
        $food->getShippingCosts($user, "pending");
        $payload = [ ];
        
        $followers = [$user];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => "Lonchis",
            "sign" => true,
            "payload" => $payload,
            "type" => "food_regeneration_complete",
            "user_status" => "normal"
        ];
        $date = date("Y-m-d H:i:s");
        $notifications->sendMassMessage($data, $followers, null, true, $date, true);
    }
    
    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    /*public function failed(Exception $exception)
    {
        $payload = [ ];
        $user = User::find(2);
        $followers = [$user];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => "Lonchis",
            "sign" => true,
            "payload" => $payload,
            "type" => "food_regeneration_failed",
            "user_status" => "normal"
        ];
        $date = date("Y-m-d H:i:s");
        $className = "App\\Services\\Notifications";
        $notifications = new $className;
        $notifications->sendMassMessage($data, $followers, null, true, $date, true);
    }*/

}
