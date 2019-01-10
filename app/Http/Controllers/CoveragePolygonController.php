<?php

namespace App\Http\Controllers;

use App\Models\CoveragePolygon;
use Unlu\Laravel\Api\QueryBuilder;
use Illuminate\Http\Request;

class CoveragePolygonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //$request2 = $this->cleanSearch->handle($request);
        if (true) {
            $queryBuilder = new QueryBuilder(new CoveragePolygon, $request);
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
        //
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
     * @param  \App\CoveragePolygon  $coveragePolygon
     * @return \Illuminate\Http\Response
     */
    public function show(CoveragePolygon $coveragePolygon)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CoveragePolygon  $coveragePolygon
     * @return \Illuminate\Http\Response
     */
    public function edit(CoveragePolygon $coveragePolygon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CoveragePolygon  $coveragePolygon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CoveragePolygon $coveragePolygon)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CoveragePolygon  $coveragePolygon
     * @return \Illuminate\Http\Response
     */
    public function destroy(CoveragePolygon $coveragePolygon)
    {
        //
    }
}
