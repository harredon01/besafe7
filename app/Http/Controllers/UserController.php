<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use App\Traits\EditProfileUsers;
use App\Services\EditUserData;

class UserController extends Controller {

    use EditProfileUsers;
    /*
      |--------------------------------------------------------------------------
      | Home Controller
      |--------------------------------------------------------------------------
      |
      | This controller renders your application's "dashboard" for users that
      | are authenticated. Of course, you are free to change or remove the
      | controller as you wish. It is just here to get your app started!
      |
     */
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, EditUserData $editUserData) {
        $this->auth = $auth;
        $this->editUserData = $editUserData;
        $this->middleware('auth');
    }
    
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index() {
        return view('user.editProfile');
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function access() {
        $user = $this->auth->user();
        return view('user.editAccess')->with('user', $user);
    }

}
