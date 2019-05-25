<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*$schedule->call('App\Http\Controllers\LocationController@moveOldLocations')->hourly();
        $schedule->call('App\Http\Controllers\GroupController@updateExpiredGroups')->daily();*/
        $schedule->call('App\Http\Controllers\PayuController@cronPayU')->hourly();
        
        
        $schedule->call('App\Http\Controllers\RapigoController@getActiveRoutesUpdate')->everyFiveMinutes();;
        
        $schedule->call('App\Http\Controllers\FoodController@reprogramDeliveries')->hourly();
        $schedule->call('App\Http\Controllers\FoodApiController@sendReminder')->dailyAt('15:30');
        $schedule->call('App\Http\Controllers\FoodApiController@backups')->dailyAt('18:15');
//        $schedule->call('App\Http\Controllers\FoodApiController@getPurchaseOrder')->dailyAt('18:15');
//        $schedule->call('App\Http\Controllers\FoodApiController@regenerateScenarios')->dailyAt('18:15');
        
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
