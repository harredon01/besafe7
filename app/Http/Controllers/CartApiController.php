<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use App\Services\EditCart;
use App\Services\PayU;
use App\Services\EditUserData;
use Illuminate\Http\RedirectResponse;

class CartApiController extends Controller {


    /**
     * The edit order implementation.
     *
     */
    protected $editCart;

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
    public function __construct(EditCart $editCart, EditUserData $editUserData, PayU $payU) {
        $this->editCart = $editCart;
        $this->editUserData = $editUserData;
        $this->payU = $payU;
        $this->middleware('auth:api');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postAddCartItem(Request $request) {
        $user = $request->user();
        $status = $this->editCart->addCartItem($user, $request->all());
        return response()->json($status);
    }
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postAddCartItemStateless(Request $request) {
        $user = $request->user();
        $status = $this->editCart->addCartItemStateless($user, $request->all());
        return response()->json($status);
    }


    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postUpdateCartItem(Request $request) {
        $user = $request->user();
        $status = $this->editCart->updateCartItem($user, $request->all());
        return response()->json($status);
    }
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postUpdateCartItemStateless(Request $request) {
        $user = $request->user();
        $status = $this->editCart->updateCartItemStateless($user, $request->all());
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postClearCart(Request $request) {
        $user = $request->user();
        $status = $this->editCart->clearCart($user);
        return response()->json($status);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loadActiveCart(Request $request) {
        $user = $request->user();
        $items = $this->editCart->loadActiveCart($user);
        return response()->json($items);
    }
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loadCartOrder($id, Request $request) {
        $user = $request->user();
        $items = $this->editCart->loadCartOrder($user,$id);
        return response()->json($items);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCart() {
        $items = $this->editCart->getCart();
        return response()->json($items);
    }
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCartStateless(Request $request) {
        $user = $request->user();
        $items = $this->editCart->getCartStateless($user);
        return response()->json($items);
    }
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCheckoutCartStateless(Request $request) {
        $user = $request->user();
        $items = $this->editCart->getCheckoutCartStateless($user);
        return response()->json($items);
    }


    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCheckoutCart() {
        $data = $this->editCart->getCheckoutCart();
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
