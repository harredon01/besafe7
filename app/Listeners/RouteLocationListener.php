<?php

namespace App\Listeners;

use App\Events\LocationEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Route;

class RouteLocationListener {

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LocationEvent  $event
     * @return void
     */
    public function handle(LocationEvent $event) {
        $location = $event->location;
        $routes = Route::where("status", "transit")->where("user_id", $location->user_id)->get();
        if (count($routes)) {
            foreach ($routes as $route) {
                $coverage = json_decode($route->coverage, true);
                $location = $coverage["location"];
                $location["lat"] = $location->lat;
                $location["long"] = $location->long;
                $coverage["location"] = $location;
                $route->coverage = json_encode($coverage);
                $route->save();
            }
        }
    }

}
