<?php

namespace App\Jobs;
use App\Services\Proofhub;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProofhubTotalsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 15000;
    protected $projects;
    protected $data;
    protected $type;
    public function __construct($projects,$data,$type) 
    {
        $this->projects = $projects;
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Proofhub $proofhub)
    {
        if($this->type=="people"){
            $proofhub->calculateTotalsPeople($this->projects, $this->data);
        } else {
            $proofhub->calculateTotalsLabels($this->projects, $this->data);
        }
        
    }
}
