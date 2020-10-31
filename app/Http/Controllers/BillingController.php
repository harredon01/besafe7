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
        return view(config("app.views").'.billing.sources')->with('user', $user);
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getSubscriptions() {
        $user = $this->auth->user();
        return view(config("app.views").'.billing.subscriptions')->with('user',$user );
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
        return view(config("app.views").'.billing.plans')->with('user',$user )->with('plans',$plans );
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getOrders() {
        $user = $this->auth->user();

        return view(config("app.views").'.billing.orders.ordersDashboard')->with('user',$user );
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getPayments() {
        $user = $this->auth->user();

        return view(config("app.views").'.billing.payments.paymentsDashboard')->with('user',$user );
    }

}
