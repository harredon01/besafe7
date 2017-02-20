<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditOrder;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller {
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

use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    protected $editOrder;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditOrder $editOrder) {
        $this->editOrder = $editOrder;
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected function authenticated(Request $request, $user) {
        $this->editOrder->loadActiveCart($user);
    }

}
