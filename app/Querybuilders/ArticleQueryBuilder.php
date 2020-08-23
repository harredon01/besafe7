<?php

namespace App\Querybuilders;

use Unlu\Laravel\Api\QueryBuilder;

class ArticleQueryBuilder extends QueryBuilder {

    public function filterBySharedId($query, $id) {
        return $query->join('userables', 'merchants.id', '=', 'userables.object_id')
                        ->where('userables.user_id', '=', $id)
                        ->where('userable_type', '=', "Merchant");
    }

    public function filterByGroupId($query, $id) {
        return $query->join('group_merchant', 'merchants.id', '=', 'group_merchant.merchant_id')
                        ->where('group_merchant.group_id', '=', $id);
    }

    public function filterByCategoryId($query, $id) {
        if (strpos($id, ',') !== false) {
            return $query->join('categorizables', 'articles.id', '=', 'categorizables.categorizable_id')
                            ->whereIn('categorizables.category_id', explode(",", $id))
                            ->where('categorizables.categorizable_type', 'App\Models\Article');
        } else {
            return $query->join('categorizables', 'articles.id', '=', 'categorizables.categorizable_id')
                            ->where('categorizables.category_id', '=', $id)
                            ->where('categorizables.categorizable_type', 'App\Models\Article');
        }
    }

    public function filterByOwnerId($query, $id) {
        return $query->join('merchant_user', 'merchants.id', '=', 'merchant_user.merchant_id')
                        ->where('merchant_user.user_id', '=', $id);
    }

    public function filterByFavoritesId($query, $id) {
        return $query->join('favorites', 'merchants.id', '=', 'favorites.object_id')
                        ->where('favorites.favorite_type', '=', "Merchant");
    }

}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

