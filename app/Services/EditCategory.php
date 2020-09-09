<?php

namespace App\Services;
use App\Models\Category;
use DB;
class EditCategory {


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getCategories(array $data) {
        
        //DB::enableQueryLog();
        //dd(DB::getQueryLog());
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

}
