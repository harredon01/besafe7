<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Notifications;
use App\Services\ZoomMeetings;
use App\Services\Geolocation;
use App\Models\Category;
use OpenTok\OpenTok;
use Illuminate\Support\Facades\View;
use Cache;

class AppServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        View::composer(config("app.views") . '.layouts.app', function ($view) {

            //dd($menu);
            $menu = Cache::remember('main_menu', 100, function () {
                        $categories = Category::where('level', 0)->with("children.children")->get();
                        $menu = view(config("app.views") . '.menu', compact('categories'))->render();
                        return $menu;
                    });
            $sticky = Cache::remember('main_menu', 100, function () {
                        $categories = Category::where('level', 0)->with("children.children")->get();
                        $sticky = view(config("app.views") . '.sticky-menu', compact('categories'))->render();
                        return $sticky;
                    });
                    //dd($menu);
            $view->with('menu', $menu)->with('sticky', $sticky);
        });
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
        $this->app->singleton('ZoomMeetings', function () {
            return new ZoomMeetings();
        });
    }

}
