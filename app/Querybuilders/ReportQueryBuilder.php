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
                        ->where('favorites.favorite_type', '=', "Report");
    }
    
    public function filterByGroupId($query, $id) {
        return $query->join('group_report', 'reports.id', '=', 'group_report.report_id')
                        ->where('group_report.group_id', '=', $id);
    }
    
    public function filterByGroupStatus($query, $id) {
        return $query->where('group_report.status', '=', $id);
    }
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

