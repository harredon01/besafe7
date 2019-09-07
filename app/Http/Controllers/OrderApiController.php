<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditOrder;
use App\Models\Order;
use App\Models\User;
use Unlu\Laravel\Api\QueryBuilder;
use App\Services\CleanSearch;

class OrderApiController extends Controller {

    /**
     * The edit profile implementation.
     *
     */
    protected $editOrder;

    /**
     * The edit profile implementation.
     *
     */
    protected $cleanSearch;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EditOrder $editOrder, CleanSearch $cleanSearch) {
        $this->editOrder = $editOrder;
        $this->cleanSearch = $cleanSearch;
        $this->middleware('auth:api', ['except' => ['confirmOrder', 'denyOrder']]);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loadOrder(Request $request, $order) {
        $user = $request->user();
        return response()->json($this->editOrder->loadOrder($user, $order));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function confirmOrder($code) {
        return response()->json($this->editOrder->confirmOrder($code));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function prepareOrder(Request $request, $platform) {
        $user = $request->user();
        return response()->json($this->editOrder->prepareOrder($user, $platform, $request->all()));
    }
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setOrderRecurringType(Request $request, $order) {
        $user = $request->user();
        return $this->editOrder->setOrderRecurringType($user, $order, $request->all());
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addDiscounts(Request $request, $platform, $order) {
        $user = $request->user();

        $className = "App\\Services\\EditOrder" . ucfirst($platform);
        $platFormService = new $className; //// <--- this thing will be autoloaded
        if ($platFormService) {
            $orderContainer = Order::find($order);
            if ($orderContainer) {
                if ($orderContainer->user_id == $user->id) {
                    $orderContainer = $this->editOrder->addItemsToOrder($user, $orderContainer);
                    return response()->json($platFormService->addDiscounts($user, $orderContainer));
                }
                return response()->json(["status" => "error", "message" => "Order is not users"]);
            }
            return response()->json(["status" => "error", "message" => "Order not found"]);
        }
        return response()->json(["status" => "error", "message" => "no service"]);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function payOrder(Request $request) {
        $user = $request->user();
        return response()->json($this->editOrder->payOrder($user, $request->all()));
    }
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getOrder(Request $request) {
        $user = $request->user();
        return response()->json(["status"=>"success","data"=>$this->editOrder->getOrder($user)]);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkOrder(Request $request, $platform) {
        $order = Order::find($platform);
        $user = $request->user();
        return response()->json($this->editOrder->checkOrder($user, $order, $request->all())); 
    }
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkUserCredits(Request $request) {
        $data = $request->all();
        $user = User::where("email",$data['email'])->first();
        if($user){
            return response()->json(["status"=>"success","credits"=>$this->editOrder->checkUsersCredits([$user],$data['platform']),"user_id"=>$user->id]);
        }
        return response()->json(["status"=>"error"]);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function denyOrder($code) {
        return response()->json($this->editOrder->denyOrder($code));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendProposal(Request $request) {
        $user = $request->user();
        $data = $request->all([
            "order_id",
            "proposed_time",
            "proposed_discount",
            "reason"]);
        return response()->json($this->editOrder->sendProposal($user, $data));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function acceptProposal(Request $request) {
        $user = $request->user();
        $data = $request->all([
            "order_id",
            "proposed_time",
            "proposed_discount",
            "reason"]);
        return response()->json($this->editOrder->sendProposal($user, $data));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setShippingAddress(Request $request) {
        $user = $request->user();
        return response()->json($this->editOrder->setShippingAddress($user, $request->all()));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setShippingCondition(Request $request) {
        $user = $request->user();
        return response()->json($this->editOrder->setShippingCondition($user, $request->only("condition_id")));
    }
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setPlatformShippingCondition(Request $request,$order,$platform) {
        $user = $request->user();
        return response()->json($this->editOrder->setPlatformShippingCondition($user, $order,$platform));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setBillingAddress(Request $request) {
        $user = $request->user();
        return response()->json($this->editOrder->setBillingAddress($user, $request->only("address_id")));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setCouponCondition(Request $request) {
        $user = $request->user();
        return response()->json($this->editOrder->setCouponCondition($user, $request->only("coupon")));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setTaxesCondition(Request $request) {
        $user = $request->user();
        $data = $request->all("country_id", "region_id");
        return response()->json($this->editOrder->setTaxesCondition($user, $data));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request) {
        $user = $request->user();
        $request2 = $this->cleanSearch->handleOrder($user,$request);
        if ($request2) {
            $queryBuilder = new QueryBuilder(new Order, $request2);
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

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        //
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
        //
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
    public function destroy($id, Request $request) {
        $user = $request->user();
        return response()->json($this->editOrder->deleteOrder($user));
    }

}
