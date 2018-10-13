<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditBilling;

class BillingApiController extends Controller {


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
    private $editBilling;
    
    private $payU;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditBilling $editBilling) {
        $this->editBilling = $editBilling;
        $this->middleware('auth:api');
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
    public function getRawSources(Request $request,$source) {
        $user = $request->user();
        $status = $this->editBilling->getRawSources($user,$source );
        return response()->json($status);
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
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function postPayCreditCard(Request $request,$source) {
        $user = $request->user();
        $data = $request->all();
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');
        $data['cookie'] = $request->cookie('name');
        $status = $this->editBilling->payCreditCard($user,$source, $data);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function postPayDebitCard(Request $request,$source) {
        $user = $request->user();
        $data = $request->all();
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');
        ;
        $data['cookie'] = $request->cookie('name');
        $status = $this->editBilling->payDebitCard($user,$source, $data);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function postPayCash(Request $request,$source) {
        $user = $request->user();
        $data = $request->all();
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');
        ;
        $data['cookie'] = $request->cookie('name');
        $status = $this->editBilling->payCash($user,$source, $data);
        return response()->json($status);
    }

}
