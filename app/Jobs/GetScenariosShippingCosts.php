<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\Food;
use App\Services\EditAlerts;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\ScenarioSelect;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetScenariosShippingCosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    
    protected $user;
    
    protected $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user,$type)
    {
        $this->user = $user;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food)
    {
        $data = $food->getShippingCosts($this->type); 
        Mail::to($this->user)->send(new ScenarioSelect($data['resultsPre'], $data['resultsSimple'], $data['winner']));
    }
    
    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
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
        $className = "App\\Services\\EditAlerts";
        $editAlerts = new $className;
        $editAlerts->sendMassMessage($data, $followers, null, true, $date, true);
    }
}
