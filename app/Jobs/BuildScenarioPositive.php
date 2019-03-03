<?php

namespace App\Jobs;
use Exception;
use App\Services\Food;
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
    protected $hash;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($scenario, $hash )
    {
        $this->scenario = $scenario;
        $this->hash = $hash;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food, EditAlerts $editAlerts)
    {
        $food->buildScenarioPositive($this->scenario, $this->hash); 
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
