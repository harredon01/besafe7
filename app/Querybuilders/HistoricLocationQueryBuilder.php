<?php
namespace App\Querybuilders;
use Unlu\Laravel\Api\QueryBuilder;

class HistoricLocationQueryBuilder extends QueryBuilder 
{
   public function filterByUserId($query, $id)
   {
      return $query->join('historic_location', 'historic_location.user_id', '=', 'userables_historic.userable_id')
           ->where('userables_historic.user_id', '=', $id)
           ->where('userables_historic.userable_type', '=', "Location");
   }
   public function filterByTripId($query, $id)
   {
      return $query->where('historic_location.trip', '=', $id)
              ->where('userables_historic.object_id', '=', $id);
   }
   public function filterByTargetId($query, $id)
   {
      return $query->where('historic_location.user_id', '=', $id);
   }
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

