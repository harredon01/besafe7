<?php

namespace App\Jobs;

use Exception;
use App\Services\Routing;
use App\Models\User;
use App\Models\Route;
use App\Services\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BuildScenarioRouteId implements ShouldQueue {

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    protected $id;
    protected $hash;
    protected $check;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $hash,$check) {
        $this->id = $id;
        $this->hash = $hash;
        $this->check = $check;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Routing $routing, Notifications $notifications) {
        $routes = Route::where("id", $this->id)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
        if ($this->check) {
            $checkResult = $routing->checkScenario($routes, $this->hash);
            if ($checkResult) {
                $routing->buildScenarioTransit($routes);
            }
        } else {
            $routing->buildScenarioTransit($routes);
        }

        $payload = ["route" => $this->id];
        $user = User::find(2);
        $followers = [$user];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => "Lonchis",
            "sign" => true,
            "payload" => $payload,
            "type" => "food_route_transit",
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
    public function failed(Exception $exception) {
        $payload = ["route" => $this->id];
        $user = User::find(2);
        $followers = [$user];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => "Lonchis",
            "sign" => true,
            "payload" => $payload,
            "type" => "food_route_transit_failed",
            "user_status" => "normal"
        ];
        $date = date("Y-m-d H:i:s");
        $className = "App\\Services\\Notifications";
        $notifications = new $className;
        $notifications->sendMassMessage($data, $followers, null, true, $date, true);
    }

}
