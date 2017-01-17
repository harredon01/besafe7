<?php

namespace App\Services;
use App\Models\Category;

class EditCategory {


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getCategories($type) {
        $categories = Category::where('type','=',$type)->get();
        return $categories;
    }

}
