<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MercadoPagoService;
use App\Jobs\PayUCron;
use Illuminate\Http\RedirectResponse;

class MercadoPagoController extends Controller {

    /**
     * The edit order implementation.
     *
     */
    protected $mercadoPago;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MercadoPagoService $mercadoPago) {
        $this->mercadoPago = $mercadoPago;
        $this->middleware('auth:api', ['except' => ['cartTest', 'cronPayU', 'webhookPayU', 'returnPayU']]);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function postPayCreditCard(Request $request) {
        $user = $request->user();
        $data = $request->all();
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');
        $data['cookie'] = $request->cookie('name');
        $status = $this->mercadoPago->payCreditCardT($user, $data);
        return response()->json($status);
    }
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function getPaymentMethods(Request $request) {
        $user = $request->user();
        $data = $request->all();
        return response()->json($this->mercadoPago->getPaymentMethods($user, $data));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function postPayDebitCard(Request $request) {
        $user = $request->user();
        $data = $request->all();
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');
        ;
        $data['cookie'] = $request->cookie('name');
        $status = $this->payU->payDebitCard($user, $data);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function postPayCash(Request $request) {
        $user = $request->user();
        $data = $request->all();
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');
        ;
        $data['cookie'] = $request->cookie('name');
        $status = $this->payU->payCash($user, $data);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getBanks(Request $request) {
        $user = $request->user();
        $status = $this->payU->getBanks($user);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cartTest() {
        $status = $this->payU->ping();
        return response()->json($status);
    }

    public function cronPayU() {
        $debug = env('APP_DEBUG');
        if($debug == 'true'){
            return response()->json(array("status" => "success", "message" => "Debug mode doing nothing"));
        }
        $this->payU->checkOrders();
        //dispatch(new PayUCron());
    }

    public function webhook(Request $request) {
        return response()->json($this->payU->webhook($request->all()));
        //dispatch(new PayUCron());
    }
    public function postcreateAll(Request $request) {
        $user = $request->user();
        return $this->payU->createAll($user,$request->all());
        //dispatch(new PayUCron());
    }

    public function returnPayU(Request $request) {
        $data = $this->payU->returnPayu($request->all());
        return view('billing.PayU.return', $data);
    }

}