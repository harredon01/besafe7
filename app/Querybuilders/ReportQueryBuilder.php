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
    
    public function filterByCategoryId($query, $id) {
        if (strpos($id, ',') !== false) {
            return $query->join('categorizables', 'reports.id', '=', 'categorizables.categorizable_id')
                            ->whereIn('categorizables.category_id', explode(",", $id))
                            ->where('categorizables.categorizable_type', 'App\Models\Report');
        } else {
            return $query->join('categorizables', 'reports.id', '=', 'categorizables.categorizable_id')
                            ->where('categorizables.category_id', '=', $id)
                            ->where('categorizables.categorizable_type', 'App\Models\Report')->select('reports.id as ids');
        }
    }
    
    public function filterByGroupId($query, $id) {
        return $query->join('group_report', 'reports.id', '=', 'group_report.report_id')
                        ->where('group_report.group_id', '=', $id);
    }
    
    public function filterByGroupStatus($query, $id) {
        return $query->where('group_report.status', '=', $id);
    }
    public function filterByOwnerId($query, $id) {
        return $query->join('reportables', 'reports.id', '=', 'reportables.report_id')
                        ->where('reportables.reportable_type', 'App\Models\User')
                        ->where('reportables.reportable_id', $id);
    }
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

