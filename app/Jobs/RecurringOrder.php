<?php

namespace App\Jobs;
use App\Services\OrderJobs;
use App\Services\EditAlerts;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RecurringOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ip;
    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, $ip )
    {
        $this->ip = $ip;
        $this->order = $order;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OrderJobs $orderJobs,EditAlerts $editAlerts)
    {
        $orderJobs->RecurringOrder($this->order,$this->ip); 
        /*$payload = ["route"=> $this->id ];
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
        $editAlerts->sendMassMessage($data, $followers, null, true, $date, true);*/
    }
    
    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        $payload = ["route"=> $this->id ];
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
        $className = "App\\Services\\EditAlerts";
        $editAlerts = new $className;
        $editAlerts->sendMassMessage($data, $followers, null, true, $date, true);
    }
}
