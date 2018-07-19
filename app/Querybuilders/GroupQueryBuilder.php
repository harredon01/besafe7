<?php
namespace App\Querybuilders;
use Unlu\Laravel\Api\QueryBuilder;

class GroupQueryBuilder extends QueryBuilder 
{
   public function filterByDateAfter($query, $id)
   {
      return $query->where(function ($query) use ($id) {
                $query->where('group_user.last_significant', '>', $id)
                      ->orWhere('groups.updated_at', '>', $id);
            });
   }
   public function filterByUserId($query, $id)
   {
      return $query->join('group_user', 'groups.id', '=', 'group_user.group_id')
           ->where('group_user.user_id',$id);
   }
   public function filterByGroupId($query, $id)
   {
      return $query->where('groups.id',$id);
   }
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

