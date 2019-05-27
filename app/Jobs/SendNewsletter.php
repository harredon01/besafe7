<?php

namespace App\Jobs;
use Exception;
use App\Services\Food;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNewsletter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( )
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food)
    {
        $food->sendNewsletter();
    }
    
    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
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
        $className = "App\\Services\\EditAlerts";
        $editAlerts = new $className;
        $editAlerts->sendMassMessage($data, $followers, null, true, $date, true);
    }
}
