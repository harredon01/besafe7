<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Notifications;
use App\Services\Geolocation;
use OpenTok\OpenTok;
class AppServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        $this->app->alias('bugsnag.logger', \Illuminate\Contracts\Logging\Log::class);
        $this->app->alias('bugsnag.logger', \Psr\Log\LoggerInterface::class);
        $this->app->singleton('Notifications', function () {
            return new Notifications();
        });
        $this->app->singleton('Geolocation', function () {
            return new Geolocation();
        });
        $this->app->singleton('OpenTok', function () {
            return new OpenTok(env('OPENTOK_API_KEY'), env('OPENTOK_API_SECRET'));
        });
    }
}
