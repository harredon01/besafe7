<?php

namespace App\Http\Middleware;

use Closure;

class CheckIfAdmin {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null) {
        if ($request->user()->id == 2 || $request->user()->id == 77) {
            return $next($request);
        } else {
            return redirect('/home');
        }
    }

}
