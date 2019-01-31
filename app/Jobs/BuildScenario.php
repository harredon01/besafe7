<?php

namespace App\Jobs;
use App\Services\Food;
use App\Services\EditAlerts;
use App\Models\Route;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BuildScenario implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scenario;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($scenario  )
    {
        $this->scenario = $scenario;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food, EditAlerts $editAlerts)
    {
        $routes = Route::where("type", $this->scenario)->where("status", "pending")->with(['deliveries.user'])->orderBy('id')->get();
        $food->buildScenarioTransit($routes);
        $payload = ["scenario"=> $this->scenario ];
        $user = User::find(2);
        $followers = [$user];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => "Lonchis",
            "sign" => true,
            "payload" => $payload,
            "type" => "food_scenario_transit",
            "user_status" => "normal"
        ];
        $date = date("Y-m-d H:i:s");
        $editAlerts->sendMassMessage($data, $followers, null, true, $date, true);
    }
    
    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception, EditAlerts $editAlerts)
    {
        $payload = ["scenario"=> $this->scenario ];
        $user = User::find(2);
        $followers = [$user];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => "Lonchis",
            "sign" => true,
            "payload" => $payload,
            "type" => "food_scenario_transit_failed",
            "user_status" => "normal"
        ];
        $date = date("Y-m-d H:i:s");
        $editAlerts->sendMassMessage($data, $followers, null, true, $date, true);
    }
}
