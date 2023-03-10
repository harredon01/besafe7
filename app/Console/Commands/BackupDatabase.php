<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class BackupDatabase extends Command {

    protected $signature = 'db:backup';
    protected $description = 'Backup the database';
    protected $backupName = '';
    protected $process;

    public function __construct() {
        parent::__construct();
        $date = date_create();
        $this->backupName = date_format($date, "Y-m-d") . "-".config("app.name").".sql";
        $file = storage_path('app/backups/' . $this->backupName);
        $this->process = new Process([
            'mysqldump',
            '--user=' . config('database.connections.mysql.username'),
            '--password=' . config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            '--result-file=' . $file
        ]);
    }

    public function handle() {
        $this->info('Running backup.');
        try {
            $this->process->mustRun();
            $this->info('Backup ran.');
            // executes after the command finishes
            if ($this->process->isSuccessful()) {
                $this->info('Backup successful.');
                $contents = new File(storage_path('app/backups/' . $this->backupName));
                $path = Storage::putFileAs('backups', $contents, $this->backupName, 'private');
                //Storage::delete('backups/' . $this->backupName);
            }
            //
            $this->info('The backup has been stored successfully.');
        } catch (ProcessFailedException $exception) {
            $this->error('The backup process has failed.' . $exception->getMessage());
        }
    }

}
