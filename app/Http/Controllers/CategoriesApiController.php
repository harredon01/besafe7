<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EditCategory;

class CategoriesApiController extends Controller {

    /**
     * The edit alerts implementation.
     *
     */
    protected $editCategories;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function __construct(EditCategory $editCategories) {
        $this->editCategories = $editCategories;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getCategoriesType(Request $request) {
        return response()->json($this->editCategories->getCategories($request->all()));
    }

}
