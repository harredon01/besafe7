<?php

namespace App\Services;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\ProductVariant;
use Excel;

class ProductImport {

    const OBJECT_ORDER = 'Order';
    const CREDIT_PRICE = 10000;
    const LUNCH_ROUTE = 15;
    const LUNCH_PROFIT = 1100;
    const ROUTE_HOUR_COST = 11000;
    const ROUTE_HOURS_EST = 3;
    const UNIT_LOYALTY_DISCOUNT = 11000;
    const OBJECT_ORDER_REQUEST = 'OrderRequest';
    const ORDER_PAYMENT = 'order_payment';
    const PAYMENT_APPROVED = 'payment_approved';
    const PAYMENT_DENIED = 'payment_denied';
    const PLATFORM_NAME = 'food';
    const ORDER_PAYMENT_REQUEST = 'order_payment_request';

    public function importProducts($path) {
        $excel = Excel::load($path);
        $reader = $excel->toArray();
        foreach ($reader as $row) {
            $merchants = explode(",", $row['merchant_id']);
            $categories = [];
            if ($row['categories']) {
                $categories = explode(",", $row['categories']);
            }
            unset($row['merchant_id']);
            unset($row['categories']);
            if ($row['id']) {
                $find["id"] = $row["id"];
                $product = Product::updateOrCreate($find, $row);
                $product->categories()->detach();
                $product->merchants()->detach();
                foreach ($merchants as $merchantId) {
                    $merchant = Merchant::find($merchantId);
                    if ($merchant) {
                        $merchant->products()->save($product);
                    }
                }
                if ($categories) {
                    foreach ($categories as $categoryId) {
                        $category = Category::find($categoryId);
                        if ($category) {
                            $category->products()->save($product);
                        }
                    }
                }
            }
        }
    }

    public function checkUser(User $user) {
        if ($user->id == 1 || $user->id == 2 || $user->id == 3) {
            return true;
        }
        return false;
    }

    public function importVariants($path) {
        $excel = Excel::load($path);
        $reader = $excel->toArray();
        foreach ($reader as $row) {
            if ($row['sku']) {
                $row['ref2'] = $row['sku'];
                $find["id"] = $row["id"];
                $product = ProductVariant::updateOrCreate($find, $row);
            }
        }
    }

    public function importMerchants($path) {

        $excel = Excel::load($path);
        $reader = $excel->toArray();
        foreach ($reader as $row) {

            if (array_key_exists("id", $row)) {
                //$merchant = Merchant::find(intval($row['id']));

//                if ($merchant) {
//                    $products = $merchant->products;
//                    $merchant->products()->detach();
//                    foreach ($products as $value) {
//                        $value->productVariants()->delete();
//                        $value->delete();
//                    }
//                    $polygons = $merchant->polygons;
//                    foreach ($polygons as $item) {
//                        $address = $item->address;
//                        $item->delete();
//                        $address->delete();
//                    }
//                    $merchant->items()->delete();
////                    $merchant->orders()->delete();
////                    $merchant->delete();
//                }
                unset($row[0]);
                $find["id"] = $row["id"];
                Merchant::updateOrCreate($find, $row);
            }
        }
    }

    public function importCategories($path) {

        $excel = Excel::load($path);
        $reader = $excel->toArray();
        foreach ($reader as $row) {

            if (array_key_exists("id", $row)) {
                unset($row[0]);
                $find["id"] = $row["id"];
                Category::updateOrCreate($find, $row);
            }
        }
    }

}
