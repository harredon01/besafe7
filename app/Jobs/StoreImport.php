<?php

namespace App\Jobs;
use App\Services\StoreExport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateGoogleEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    
    protected $user;
    
    protected $clean;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user,$path,$clean)
    {
        $this->path = $path;
        $this->user = $user;
        $this->clean = $clean;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(StoreExport $storeExport)
    {
        $storeExport->importGlobalExcel($this->user,$this->path,$this->clean); 
    }
}
