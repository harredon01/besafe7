<?php
namespace App\Querybuilders;
use Unlu\Laravel\Api\QueryBuilder;

class MerchantQueryBuilder extends QueryBuilder 
{
   public function filterBySharedId($query, $id)
   {
      return $query->join('userables', 'merchants.id', '=', 'userables.object_id')
           ->where('userables.user_id', '=', $id)
           ->where('userable_type', '=', "Merchant");
   }
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

