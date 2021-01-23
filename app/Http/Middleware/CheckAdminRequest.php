<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class CheckAdminRequest {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $user = $request->user();
        $mystring = $request->getRequestUri();
        $findme = '?';
        $pos = strpos($mystring, $findme);
        if ($user->id < 4 || $user->id == 77) {
            return $next($request);
        }
        return response()->json(['message' => 'Access denied'], 402);
    }

}
