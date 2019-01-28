<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditOrder;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller {

    /**
     * The edit order implementation.
     *
     */
    protected $editOrder;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditOrder $editOrder) {
        $this->editOrder = $editOrder;
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
    public function getSources(Request $request, $source) {
        $user = $request->user();
        if ($source == "PayU") {
            $data = $this->cleanPayu($data, $request);
        }
        $status = $this->editOrder->getSources($user, $source);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function createSource(Request $request, $source) {
        $user = $request->user();
        $data = $request->all();
        if ($source == "PayU") {
            $data = $this->cleanPayu($data, $request);
        }
        $status = $this->editOrder->createSource($user, $data, $source);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function deleteSource(Request $request, $source, $source_id) {
        $user = $request->user();
        $status = $this->editOrder->deleteSource($user, $source_id, $source);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function useSource(Request $request, $source) {
        $user = $request->user();
        $data = $request->all();
        if ($source == "PayU") {
            $data = $this->cleanPayu($data, $request);
        }
        $status = $this->editOrder->useSource($user, $data, $source);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function getSubscriptions(Request $request, $source) {
        $user = $request->user();
        if ($source == "PayU") {
            $data = $this->cleanPayu($data, $request);
        }
        $status = $this->editOrder->getSubscriptions($user, $source);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function createSubscription(Request $request, $source) {
        $user = $request->user();
        $data = $request->all();
        if ($source == "PayU") {
            $data = $this->cleanPayu($data, $request);
        }
        $status = $this->editOrder->createSubscription($user, $data, $source);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function deleteSubscription(Request $request, $source, $subscription) {
        $user = $request->user();
        $data = $request->all();
        if ($source == "PayU") {
            $data = $this->cleanPayu($data, $request);
        }
        $status = $this->editOrder->deleteSubscription($user, $subscription, $source);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\ResponsepostPayCreditCard
     */
    public function payOrder(Request $request, $source) {
        $user = $request->user();
        $data = $request->all();
        if ($source == "PayU") {
            $data = $this->cleanPayu($data, $request);
        }
        $status = $this->editOrder->payOrder($user, $data, $source);
        return response()->json($status);
    }

    private function cleanPayu(array $data, Request $request) {
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');
        $data['cookie'] = $request->cookie('name');
        return $data;
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
        $this->payU->checkOrders($request->all());
        //dispatch(new PayUCron());
    }

    public function returnPayU(Request $request) {
        $this->payU->webhookPayU($request->all());
        //dispatch(new PayUCron());
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request) {
        $user = $request->user();
        //$request2 = $this->cleanSearch->handleOrder($user, $request);
        if ($request) {
            $queryBuilder = new QueryBuilder(new Payment, $request);
            $result = $queryBuilder->build()->paginate();
            return response()->json([
                        'data' => $result->items(),
                        "total" => $result->total(),
                        "per_page" => $result->perPage(),
                        "page" => $result->currentPage(),
                        "last_page" => $result->lastPage(),
            ]);
        }
        return response()->json([
                    'status' => "error",
                    'message' => "illegal parameter"
                        ], 403);
    }

}
