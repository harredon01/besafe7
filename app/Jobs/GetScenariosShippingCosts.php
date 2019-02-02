<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\Food;
use App\Services\EditAlerts;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BuildCompleteScenario implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food,EditAlerts $editAlerts)
    {
        $results = $food->getShippingCosts(); 
    }
    
    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception, EditAlerts $editAlerts)
    {
        $payload = ["scenario"=> $this->scenario." completo"  ];
        $user = User::find(2);
        $followers = [$user];
        $data = [
            "trigger_id" => $user->id,
            "message" => "",
            "subject" => "",
            "object" => "Lonchis",
            "sign" => true,
            "payload" => $payload,
            "type" => "shipping_costs_failed",
            "user_status" => "normal"
        ];
        $date = date("Y-m-d H:i:s");
        $editAlerts->sendMassMessage($data, $followers, null, true, $date, true);
    }
}
