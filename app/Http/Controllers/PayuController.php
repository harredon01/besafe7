<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PayU;
use App\Jobs\PayUCron;
use Illuminate\Http\RedirectResponse;

class PayuController extends Controller {

    /**
     * The edit order implementation.
     *
     */
    protected $payU;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PayU $payU) {
        $this->payU = $payU;
        $this->middleware('auth', ['except' => ['cartTest', 'cronPayU', 'webhookPayU', 'returnPayU']]);
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
        $status = $this->payU->payCreditCard($user, $data);
        return response()->json($status);
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
    public function getBanks() {
        $status = $this->payU->getBanks();
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
        $this->payU->checkOrders();
        //dispatch(new PayUCron());
    }

    public function webhookPayU(Request $request) {
        $this->payU->webhook($request->all());
        //dispatch(new PayUCron());
    }

    public function returnPayU(Request $request) {
        $data = $request->all();
        $ApiKey = env('PAYU_KEY');
        $merchant_id = $data['merchantId'];
        $referenceCode = $data['referenceCode'];
        $TX_VALUE = $data['TX_VALUE'];
        $New_value = number_format($TX_VALUE, 1, '.', '');
        $currency = $data['currency'];
        $transactionState = $data['transactionState'];
        $firma_cadena = "$ApiKey~$merchant_id~$referenceCode~$New_value~$currency~$transactionState";
        $firmacreada = md5($firma_cadena);
        $firma = $data['signature'];
        $estadoTx = "";
        if (true) {//if (strtoupper($firma) == strtoupper($firmacreada)) {
            if ($data['transactionState'] == 4) {
                $estadoTx = "Transaction approved";
            } else if ($data['transactionState'] == 6) {
                $estadoTx = "Transaction rejected";
            } else if ($data['transactionState'] == 104) {
                $estadoTx = "Error";
            } else if ($data['transactionState'] == 7) {
                $estadoTx = "Pending payment";
            } else {
                $estadoTx = $data['mensaje'];
            }
            $data['estadoTx'] = $estadoTx;
            return view('billing.PayU.return', $data);
        }
    }

}
