<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Services\CleanSearch;
use App\Services\EditDelivery;
use Unlu\Laravel\Api\QueryBuilder;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * The edit profile implementation.
     *
     */
    protected $cleanSearch;
    
    /**
     * The edit profile implementation.
     *
     */
    protected $editDelivery;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CleanSearch $cleanSearch, EditDelivery $editDelivery) {
        $this->cleanSearch = $cleanSearch;
        $this->editDelivery = $editDelivery;
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $request2 = $this->cleanSearch->handle($user,$request);
        if ($request2) {
            $queryBuilder = new QueryBuilder(new Delivery, $request2);
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
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
 
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function postDeliveryOptions(Request $request)
    {
        $user = $request->user();
        $data = $request->all([
            'day',
            'month',
            'year',
            'type_id',
            'delivery_id',
            'starter_id',
            'main_id',
            'dessert_id',
            'observation',
            'details'
        ]);
        return response()->json($this->editDelivery->postDeliveryOptions($user, $data));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Delivery  $delivery
     * @return \Illuminate\Http\Response
     */
    public function show(Delivery $delivery)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Delivery  $delivery
     * @return \Illuminate\Http\Response
     */
    public function edit(Delivery $delivery)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Delivery  $delivery
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Delivery $delivery)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Delivery  $delivery
     * @return \Illuminate\Http\Response
     */
    public function destroy(Delivery $delivery)
    {
        //
    }
}
