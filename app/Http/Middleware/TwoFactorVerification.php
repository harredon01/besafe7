<?php

namespace App\Http\Middleware;

use Closure;

class TwoFactorVerification {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $user = $request->user();
        if ($user->two_factor_expiry > \Carbon\Carbon::now()) {
            return $next($request);
        }
        $user->two_factor_token = str_random(8);
        $user->save();
        \Mail::to($user)->send(new TwoFactorAuthMail($user,$user->two_factor_token));
        return response()->json(['message' => 'Requires two factor authentication'], 403);
    }

}
