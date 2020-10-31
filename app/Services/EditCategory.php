<?php

namespace App\Services;
use App\Models\Category;
use DB;
use Cache;
class EditCategory {

    /**
     * Categorias con parent 0 y hasta 2 niveles se devuelven para los hijos
     */

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getCategories(array $data) {
        
        //DB::enableQueryLog();
        //dd(DB::getQueryLog());
        if(array_key_exists("level", $data)){
            $categories = Category::where('level',1)->get();
            return ['status' => "success", "data" => $categories];
        }
        if(!array_key_exists("type", $data)){
            return ['status' => "error", "message" => "needs type"];
        }
        if(array_key_exists("name", $data)){
            $categories = DB::select(" "
                        . "SELECT * FROM categories WHERE id IN ( SELECT DISTINCT(category_id)"
                . " from categorizables where categorizable_type=:type) and name like :name limit 15"
                        . "", ['name'=>"%".$data['name']."%",'type'=>$data['type']]);
        } else {
            $categories = DB::select(" "
                        . "SELECT * FROM categories WHERE id IN ( SELECT DISTINCT(category_id)"
                . " from categorizables where categorizable_type=:type) limit 15"
                        . "", ['type'=>$data['type']]);
        }
        
        return ['status' => "success", "data" => $categories];
    }
    public function getCategoriesMenu() {
        if (false) {
            $results = Cache::remember('products_merchant_' . $data['merchant_id'] . "_" . $data['page'], 100, function ()use ($data) {
                        return $this->productsQuery($data);
                    });
        } else {
            return Category::where('level',1)->with("children.children")->get()->toArray();
        }

        return [];
    }

}
