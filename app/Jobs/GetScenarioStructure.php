<?php

namespace App\Jobs;
use App\Models\User;
use Exception;
use App\Services\Routing;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\RouteChoose;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetScenarioStructure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    
    protected $user;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user,$data)
    {
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Routing $routing)
    {
        $data = $routing->getTotalEstimatedShipping($this->data); 
        if(!$this->user){
            $this->user = User::find(2);
        }
        Mail::to($this->user)->send(new RouteChoose($data['routes']));
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
