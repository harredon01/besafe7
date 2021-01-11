<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Country;
use App\Jobs\PostRegistration;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'firstName' => 'required|max:255',
            'lastName' => 'required|max:255',
            'cellphone' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User 
     */
    protected function create(array $data)
    {
        if(!isset($data['optinMarketing'])){
            $data['optinMarketing'] = false;
        }
        $user = User::create([
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'name' => $data['firstName']." ".$data['lastName'],
            'email' => $data['email'],
            'cellphone' => $data['cellphone'],
            'optinMarketing' => $data['optinMarketing'],
            'emailNotifications' => 1,
            'password' => bcrypt($data['password']),
        ]);
        dispatch(new PostRegistration($user));
        return $user;
    }
}
