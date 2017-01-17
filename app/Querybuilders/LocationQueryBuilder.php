<?php
namespace App\Querybuilders;
use Unlu\Laravel\Api\QueryBuilder;

class LocationQueryBuilder extends QueryBuilder 
{
   public function filterByUserId($query, $id)
   {
      return $query->join('locations', 'locations.user_id', '=', 'userables.userable_id')
           ->where('userables.user_id', '=', $id)
           ->where('userable_type', '=', "Location");
   }
   public function filterByHashId($query, $id)
   {
      return $query->join('locations', 'locations.user_id', '=', 'users.id')
           ->where('users.hash', '=', $id)
           ->where('users.is_tracking', '=', "1");
   }
   public function filterByIdAfter($query, $id)
   {
      return $query->where('locations.id', '>=', $id);
   }
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

