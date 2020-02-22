<?php

namespace App\Http\Controllers;

use Unlu\Laravel\Api\QueryBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CleanSearch;
use App\Services\EditItem;
use App\Models\Item;

class ItemApiController extends Controller {

    /**
     * The edit profile implementation.
     *
     */
    protected $cleanSearch;
    
    /**
     * The edit profile implementation.
     *
     */
    protected $editItem;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CleanSearch $cleanSearch, EditItem $editItem) {
        $this->cleanSearch = $cleanSearch;
        $this->editItem = $editItem;
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $user = $request->user();
        $request2 = $this->cleanSearch->handleOrder($user,$request);
        if ($request2) {
            $queryBuilder = new QueryBuilder(new Item, $request2);
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
    public function destroy($id) {
        //
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fulfillItem(Request $request) {
        $user = $request->user();
        return response()->json($this->editItem->changeStatusItems($user, $request->all()));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function acceptItem(Request $request, $item) {
        $user = $request->user();
        return response()->json($this->editOrder->prepareOrder($user, $platform, $request->all()));
    }

}
