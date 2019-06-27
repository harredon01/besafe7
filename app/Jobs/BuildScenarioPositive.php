<?php

namespace App\Jobs;
use Exception;
use App\Services\Routing;
use App\Services\EditAlerts;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BuildScenarioPositive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scenario;
    protected $provider;
    protected $hash;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($scenario,$provider, $hash )
    {
        $this->scenario = $scenario;
        $this->provider = $provider;
        $this->hash = $hash;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Routing $routing, EditAlerts $editAlerts)
    {
        $routes = Route::where("type", $this->scenario)->where("status", "pending")->where("provider", $this->provider)->with(['deliveries.user'])->orderBy('id')->get();
        $checkResult = $routing->checkScenario($routes, $this->hash);
        if ($checkResult) {
            $routes = Route::whereColumn('unit_price', '>', 'unit_cost')->where("status", "pending")->where("provider", $this->provider)->where("type", $this->scenario)->with(['deliveries.user'])->orderBy('id')->get();
            $routing->buildScenarioTransit($routes);
        }
        $payload = ["scenario"=> $this->scenario." positivos" ];
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
    public function failed(Exception $exception)
    {
        $payload = ["scenario"=> $this->scenario." positivos"  ];
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
        $className = "App\\Services\\EditAlerts";
        $editAlerts = new $className;
        $editAlerts->sendMassMessage($data, $followers, null, true, $date, true);
    }
}
