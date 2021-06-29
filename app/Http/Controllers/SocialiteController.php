<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditCart;
use App\Models\User;
use App\Jobs\PostRegistration;
use Socialite;

class SocialiteController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Login Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles authenticating users for the application and
      | redirecting them to your home screen. The controller uses a trait
      | to conveniently provide its functionality to your applications.
      |
     */


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    protected $editCart;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditCart $editCart) {
        $this->editCart = $editCart;
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected function authenticated(Request $request, $user) {
        //$this->editCart->loadActiveCart($user);
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToFacebook() {
        return Socialite::driver('facebook')->redirect();
    }
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle() {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleFacebookCallback() {
        $user = Socialite::driver('facebook')->stateless()->user();
        $user = json_decode(json_encode($user), true);
        $authUser = User::where("email", $user['email'])->first();

        if (!$authUser) {
            $postRegistration = true;
            $str = rand();
            $result = md5($str);
            $theName = explode(" ", $user['name']);
            $authUser = User::create([
                        'firstName' => $theName[0],
                        'lastName' => $theName[1],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'cellphone' => '11',
                        'language' => 'es',
                        'area_code' => '11',
                        'password' => bcrypt($result),
                        'emailNotifications' => 1,
                        'optinMarketing' => 1
            ]);
            if ($postRegistration) {
                dispatch(new PostRegistration($authUser));
            }
        }
        auth()->login($authUser);
        return redirect()->intended('/home');

        // $user->token;
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback() {
        $user = Socialite::driver('google')->stateless()->user();
        $user = json_decode(json_encode($user), true);
        $authUser = User::where("email", $user['email'])->first();

        if (!$authUser) {
            $postRegistration = true;
            $str = rand();
            $result = md5($str);
            $authUser = User::create([
                        'firstName' => $user['user']['given_name'],
                        'lastName' => $user['user']['family_name'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'cellphone' => '11',
                        'language' => 'es',
                        'area_code' => '11',
                        'password' => bcrypt($result),
                        'emailNotifications' => 1,
                        'optinMarketing' => 1
            ]);
            if ($postRegistration) {
                dispatch(new PostRegistration($authUser));
            }
        }
        //dd($authUser);
        auth()->login($authUser);
        return redirect()->intended('/home');

        // $user->token;
    }

}
