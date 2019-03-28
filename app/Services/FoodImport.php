<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Address;
use App\Models\Translation;
use App\Models\CoveragePolygon;
use App\Models\ProductVariant;
use Excel;

class FoodImport {

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

    public function importProducts() {
        $excel = Excel::load(storage_path('imports') . '/productsfood.xlsx');
        $reader = $excel->toArray();
        foreach ($reader as $row) {
            $merchants = explode(",", $row['merchant_id']);
            $categories = explode(",", $row['categories']);
            unset($row['merchant_id']);
            unset($row['categories']);
            if ($row['id']) {
                $product = Product::create($row);
                foreach ($merchants as $merchantId) {
                    $merchant = Merchant::find($merchantId);
                    if ($merchant) {
                        $merchant->products()->save($product);
                    }
                }
                foreach ($categories as $categoryId) {
                    $category = Category::find($categoryId);
                    if ($category) {
                        $category->products()->save($product);
                    }
                }
            }
        }
    }

    public function importMerchants() {

        $excel = Excel::load(storage_path('imports') . '/merchantsfood.xlsx');
        $reader = $excel->toArray();
        foreach ($reader as $row) {

            if (array_key_exists("id", $row)) {
                $merchant = Merchant::find(intval($row['id']));

                if ($merchant) {
                    $products = $merchant->products;
                    $merchant->products()->detach();
                    foreach ($products as $value) {
                        $value->productVariants()->delete();
                        $value->delete();
                    }
                    $polygons = $merchant->polygons;
                    foreach ($polygons as $item) {
                        $address = $item->address;
                        $item->delete();
                        $address->delete();
                    }
                    $merchant->items()->delete();
//                    $merchant->orders()->delete();
//                    $merchant->delete();
                }
                unset($row[0]);
                Merchant::create($row);
            }
        }
        $excel = Excel::load(storage_path('imports') . '/productsfood.xlsx');
        $reader = $excel->toArray();
        foreach ($reader as $row) {
            $merchants = explode(",", $row['merchant_id']);
            unset($row['merchant_id']);
            if ($row['id']) {
                $product = Product::create($row);
                foreach ($merchants as $merchantId) {
                    $merchant = Merchant::find($merchantId);
                    if ($merchant) {
                        $merchant->products()->save($product);
                    }
                }
            }
        }

        $excel = Excel::load(storage_path('imports') . '/productvariantsfood.xlsx');
        $reader = $excel->toArray();
        foreach ($reader as $row) {
            if ($row['sku']) {
                $row['ref2'] = $row['sku'];
                $product = ProductVariant::create($row);
            }
        }
        $excel = Excel::load(storage_path('imports') . '/merchantAddress.xlsx');
        $reader = $excel->toArray();
        foreach ($reader as $row) {
            if ($row['id']) {
                Address::create($row);
            }
        }
        $this->importPolygons(storage_path('imports') . '/merchantPolygons.xlsx');
        $this->importDishes(storage_path('imports') . '/TemplateAlmuerzo.xlsx');
    }

    private function importDish($entradas, $principales, $postres, $activeRow) {
        $dishes = [
            "entradas" => $entradas,
            "plato" => $principales,
            "postre" => $postres
        ];
        $saveDate = "";
        if (gettype($activeRow['fecha']) == "string") {
            $date = explode("/", $activeRow['fecha']);
            $saveDate = $date[2] . "-" . $date[1] . "-" . $date[0];
        } else {
            $saveDate = $activeRow['fecha'];
        }
        //dd($date);
        $article = Article::create([
                    "type" => "lunch",
                    "name" => $activeRow['almuerzo'],
                    "description" => "Almuerzo " . $activeRow['almuerzo'],
                    "start_date" => $saveDate,
                    "attributes" => json_encode($dishes)
        ]);
    }

    public function importPolygons($path) {
        $excel = Excel::load($path);
        $reader = $excel->toArray();
        foreach ($reader as $row) {
            $coverage = $row['coverage'];
            $coverage = str_replace("new google.maps.LatLng(", '{"lat":', $coverage);
            $coverage = str_replace("-74", '"lng":-74', $coverage);
            $coverage = str_replace("),", '},', $coverage);
            $coverage = str_replace(")", '}', $coverage);
            $coverage = "[" . $coverage . "]";
            $resultset = json_decode($coverage, true);
            $firstItem = $resultset[0];
            array_push($resultset, $firstItem);
            $row['coverage'] = json_encode($resultset);
            $find["id"] = $row["id"];
            CoveragePolygon::updateOrCreate($find, $row);
        }
    }

    public function importDishes($path) {
        $excel = Excel::load($path);
        $reader = $excel->toArray();
        $i = 0;
        $activeRow = $reader[0];
        $activeLunch = $activeRow['almuerzo'];
        $entradas = [];
        $principales = [];
        $postres = [];
        foreach ($reader as $row) {
            if ($row['fecha']) {
                if ($row['almuerzo'] != $activeLunch) {
                    $activeLunch = $row['almuerzo'];
                    $this->importDish($entradas, $principales, $postres, $activeRow);
                    $entradas = [];
                    $principales = [];
                    $postres = [];
                }
                $plato = [
                    "valor" => $row['plato'],
                    "codigo" => $row['codigo'],
                    "descripcion" => $row['descripcion']
                ];
                if ($row['tipo'] == "Entrada") {
                    array_push($entradas, $plato);
                } else if ($row['tipo'] == "Principal") {
                    array_push($principales, $plato);
                } else if ($row['tipo'] == "Postre") {
                    array_push($postres, $plato);
                }
                $activeRow = $row;
            }
        }
        $this->importDish($entradas, $principales, $postres, $activeRow);
    }

    public function importTranslations($path) {
        Translation::where("id", ">", '0')->delete();
        $excel = Excel::load($path);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            unset($sheet['']);
            if ($sheet['code']) {
                if (!$sheet['body']) {
                    $sheet['body'] = "";
                }
                $translation = Translation::updateOrCreate([
                            'code' => $sheet['code'],
                            'language' => $sheet['language'],
                            'value' => $sheet['value'],
                            'body' => $sheet['body']
                ]);
            } else {
                break;
            }
        }
    }

    public function importContent($path) {
        $excel = Excel::load($path);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            unset($sheet['']);
            $article = Article::create([
                        "type" => $sheet['type'],
                        "name" => $sheet['name'],
                        "description" => $sheet['description'],
                        "body" => $sheet['body']
            ]);
        }
    }

}
