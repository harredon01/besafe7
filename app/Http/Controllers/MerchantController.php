<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditMapObject;
use App\Services\MerchantImport;
/*use Excel;
use App\Models\Category;
use App\Models\Product;
use App\Models\Merchant;
use App\Models\OfficeHour;
use App\Models\PaymentMethod;
use DB;*/

class MerchantController extends Controller {

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * The edit profile implementation.
     *
     */
    protected $editMapObject;

    /**
     * The edit profile implementation.
     *
     */
    protected $merchantImport;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth, EditMapObject $editMapObject, MerchantImport $merchantImport) {
        $this->editMapObject = $editMapObject;
        $this->merchantImport = $merchantImport;
        $this->auth = $auth;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        //
    }

    public function importMerchant() {
        $user = $this->auth->user();
        $filename = "merchant test.xlsx";
        return response()->json($this->merchantImport->importNewMerchant($user,$filename));
    }
    public function exportMerchant() {
        $user = $this->auth->user();
        $filename = "merchant test.xlsx";
        $merchantid = "5";
        return response()->json($this->merchantImport->exportMerchant($user,$filename,$merchantid));
    }
    public function exportMerchantOrders() {
        $user = $this->auth->user();
        $filename = "merchant test.xlsx";
        $merchantid = "5";
        return response()->json($this->merchantImport->exportMerchantOrders($user,$filename,$merchantid));
    }
    
    public function importUpdateMerchant() {
        $user = $this->auth->user();
        $filename = "Filename13.xlsx";
        $merchantid = "5";
        return response()->json($this->merchantImport->importUpdateMerchant($user,$filename,$merchantid));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getNearbyMerchants(Request $request) {
        $validator = $this->editMapObject->validatorGetMerchant($request->all());
        if ($validator->fails()) {
            $this->throwValidationException(
                    $request, $validator
            );
        }
        return response()->json($this->editMapObject->getNearbyObjects($request->all()));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getPaymentMethodsMerchant($id) {
        return response()->json($this->editMapObject->getPaymentMethodsMerchant($id));
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getMerchantOrders($id) {
        $user = $this->auth->user();
        return view('merchants.merchant')->with('merchant', $this->editMapObject->getMerchantOrders($user,$id));
    }
    
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getRegisterMerchant() {
        return view('merchant.register');
    }
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function postRegisterMerchant(Request $request)  {
        
        $this->editMapObject->createUserObject( $request->all());
        
        return view('merchant.complete');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request) {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        return $this->editMapObject->getMerchantOrders($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        //
    }

}
