<?php

namespace App\Querybuilders;

use Unlu\Laravel\Api\QueryBuilder;

class ReportQueryBuilder extends QueryBuilder {

    public function filterBySharedId($query, $id) {
        return $query->join('userables', 'reports.id', '=', 'userables.object_id')
                        ->where('userables.user_id', '=', $id)
                        ->where('userable_type', '=', "Report");
    }
    
    public function filterByFavoritesId($query, $id) {
        return $query->join('favorites', 'reports.id', '=', 'favorites.object_id')
                        ->where('favorites.type', '=', "Report");
    }

}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

