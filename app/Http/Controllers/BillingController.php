<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use App\Services\EditBilling;

class BillingController extends Controller {


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
    public function __construct(Guard $auth, EditBilling $editBilling) {
        $this->auth = $auth;
        $this->editBilling = $editBilling;
        $this->middleware('auth');
    }
    
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getSources() {
        $user = $this->auth->user();
        return view('billing.sources')->with('user', $user);
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getSubscriptions() {
        $user = $this->auth->user();
        return view('billing.subscriptions')->with('user',$user );
    }


    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getPlans() {
        $user = $this->auth->user();
	$user->sources;
	$plans = $this->editBilling->getPlans();
        return view('billing.plans')->with('user',$user )->with('plans',$plans );
    }

}
