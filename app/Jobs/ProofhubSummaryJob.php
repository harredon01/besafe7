<?php

namespace App\Jobs;

use Exception;
use App\Services\Proofhub;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProofhubSummaryJob implements ShouldQueue {

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 15000;
    protected $labels;
    protected $people;
    protected $projects;
    protected $full;
    protected $ignoreDate;
    protected $name;

    public function __construct($labels,$people,$projects,$full,$ignoreDate,$name) {
        $this->labels = $labels;
        $this->people = $people;
        $this->projects = $projects;
        $this->full = $full;
        $this->ignoreDate = $ignoreDate;
        $this->name = $name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Proofhub $proofhub) {
        $proofhub->getSummary($this->labels, $this->people, $this->projects, $this->full, $this->ignoreDate, $this->name);
    }

}
