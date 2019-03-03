<?php

namespace App\Jobs;
use App\Models\User;
use App\Models\Route;
use App\Services\Food;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\RouteOrganize;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetScenarioOrganizationStructure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    
    protected $user;
    protected $provider;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user,$provider)
    {
        $this->user = $user;
        $this->provider = $provider;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Food $food)
    {
        if(!$this->user){
            $this->user = User::find(2);
        }
        $routes = Route::where("status", "enqueue")->where("provider", $this->provider)->with(['deliveries.user'])->get();
        $results = $food->buildScenario($routes);
        Mail::to($this->user)->send(new RouteOrganize($results));
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
        if(!$this->user){
            $this->user = User::find(2);
        }
        $followers = [$this->user];
        $data = [
            "trigger_id" => $this->user->id,
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
