<?php
namespace App\Querybuilders;
use Unlu\Laravel\Api\QueryBuilder;

class GroupQueryBuilder extends QueryBuilder 
{
   public function filterByUserId($query, $id)
   {
      return $query->whereHas('users', function($q) use ($id) {
         return $q->where('users.id', $id);
      });
   }
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

