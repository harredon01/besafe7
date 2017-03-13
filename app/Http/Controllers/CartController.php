<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use App\Services\EditOrder;
use App\Services\PayU;
use App\Services\EditUserData;
use Illuminate\Http\RedirectResponse;

class CartController extends Controller {

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * The edit order implementation.
     *
     */
    protected $editOrder;

    /**
     * The edit order implementation.
     *
     */
    protected $editUserData;

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
    public function __construct(Guard $auth, EditOrder $editOrder, EditUserData $editUserData, PayU $payU) {
        $this->auth = $auth;
        $this->editOrder = $editOrder;
        $this->editUserData = $editUserData;
        $this->payU = $payU;
        $this->middleware('auth', ['except' => 'cartTest']);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postAddCartItem(Request $request) {
        $user = $this->auth->user();
        $status = $this->editOrder->addCartItem($user, $request->all());
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postSetShippingAddress($address) {
        $user = $this->auth->user();
        $status = $this->editOrder->setShippingAddress($user, $address);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postSetBillingAddress($address) {
        $user = $this->auth->user();
        $status = $this->editOrder->setBillingAddress($user, $address);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getShippingConditions($address) {
        $user = $this->auth->user();
        $status = $this->editOrder->getShippingConditions($user, $address);
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

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postSetCoupon($coupon) {
        $user = $this->auth->user();
        $status = $this->editOrder->setCouponCondition($user, $coupon);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postSetShippingCondition($condition) {
        $user = $this->auth->user();
        $status = $this->editOrder->setShippingCondition($user, $condition);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postUpdateCartItem(Request $request) {
        $user = $this->auth->user();
        $status = $this->editOrder->updateCartItem($user, $request->all());
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postClearCart(Request $request) {
        $user = $this->auth->user();
        $status = $this->editOrder->clearCart($user);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loadActiveCart(Request $request) {
        $user = $this->auth->user();
        $items = $this->editOrder->loadActiveCart($user);
        return response()->json($items);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCart(Request $request) {
        $items = $this->editOrder->getCart();
        return response()->json($items);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCheckoutCart(Request $request) {
        $data = $this->editOrder->getCheckoutCart();
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getCheckout() {
        $user = $this->auth->user();
        return view('products.checkout')
               ->with('user', $user);
    }

}
