<?php
namespace App\Querybuilders;
use Unlu\Laravel\Api\QueryBuilder;
class ContactQueryBuilder extends QueryBuilder 
{
   public function filterByUserId($query, $id)
   {
      return $query->join('contacts', 'users.id', '=', 'contacts.contact_id')
           ->where('contacts.user_id', '=', $id)
              ->where('contacts.level', '<>', "contact_deleted");
   }
   public function filterByDateAfter($query, $id)
   {
      return $query->where('contacts.level', '<>', "contact_deleted")
              ->where(function ($query) use ($id) {
                $query->where('contacts.last_significant', '>', $id)
                      ->orWhere('users.updated_at', '>', $id);
            });
   }
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

