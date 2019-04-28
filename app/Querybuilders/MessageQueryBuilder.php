<?php

namespace App\Querybuilders;

use Unlu\Laravel\Api\QueryBuilder;

class MessageQueryBuilder extends QueryBuilder {

    public function filterByUserChat($query, $id) {
        $vars = (explode(",", $id));
        return $query->where('messageable_type', '=', 'user_message')
                ->where(function ($query) use ($vars) {
                    $query->where(function ($query) use ($vars) {
                        $query->where('user_id', $vars[0])
                                ->where('messageable_id', $vars[1]);
                    })->orWhere(function ($query) use ($vars) {
                        $query->where('user_id', $vars[1])
                                ->where('messageable_id', $vars[0]);
                    });
                });
    }

    public function filterByIdAfter($query, $id) {
        return $query->where('messages.id', '>', $id);
    }

}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

