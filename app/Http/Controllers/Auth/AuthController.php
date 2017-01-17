<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use App\Services\EditOrder;
use App\Models\User;
use Validator;

class AuthController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Registration & Login Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles the registration of new users, as well as the
      | authentication of existing users. By default, this controller uses
      | a simple trait to add these behaviors. Why don't you explore it?
      |
     */

    protected $editOrder;

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Create a new authentication controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard  $auth
     * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
     * @return void
     */
    public function __construct(EditOrder $editOrder) {
        $this->editOrder = $editOrder;
        $this->middleware('guest', ['except' => 'getLogout']);
    }
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data) {
        return Validator::make($data, [
                    'firstName' => 'required|max:255',
                    'lastName' => 'required|max:255',
                    'cellphone' => 'required|max:255',
                    'email' => 'required|email|max:255',
                    'password' => 'required|confirmed|min:6',
                    'green' => 'required|min:4',
                    'red' => 'required|min:4',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    public function create(array $data) {
        $data['password'] = bcrypt($data['password']);
        $data['name'] = $data['firstName'] . " " . $data['lastName'];
        $data['salt'] = str_random(40);
        return User::create($data);
    }

}
