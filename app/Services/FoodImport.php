<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Merchant;
use App\Models\Product;
use App\Imports\ArrayImport;
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

    private function importDish($entradas, $principales, $postres, $activeRow,$message) {
        $dishes = [
            "entradas" => $entradas,
            "plato" => $principales,
            "postre" => $postres
        ];
        $saveDate = "";
        if (gettype($activeRow[0]) == "string") {
            $date = explode("/", $activeRow[0]);
            $saveDate = $date[2] . "-" . $date[0] . "-" . $date[1];
        } else {
            $saveDate = $activeRow[0];
        }
//        dd($saveDate);
        //dd($date);
        $article = Article::create([
                    "type" => "lunch",
                    "name" => $activeRow[1],
                    "pagetitle" => $message,
                    "description" => "Almuerzo " . $activeRow[1],
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
        //$excel = Excel::load($path);
        $reader = Excel::toArray(new ArrayImport, $path);
        $reader = $reader[0];
        
        array_shift($reader);
        //array_shift($reader);
        $i = 0;
        //dd($reader[0][0]);
        $activeRow = $reader[0];
        $activeLunch = $activeRow[1];
        $entradas = [];
        $principales = [];
        $postres = [];
        $message = null;
        foreach ($reader as $row) {
            
            if ($row[0]) {
                foreach ($row as &$item) {
                    if(!$item){
                        $item = "";
                    }
                }
                if ($row[1] != $activeLunch) {
                    $activeLunch = $row[1];
                    $this->importDish($entradas, $principales, $postres, $activeRow,$message);
                    $entradas = [];
                    $principales = [];
                    $postres = [];
                    $message = null;
                }
                $imagen ="";
                if($row[7]){
                    $imagen ="https://gohife.s3.us-east-2.amazonaws.com/public/dishes/".$row[7];
                }
                $pesos = [];
                if($row[8]){
                    $wght = [
                        "name"=>"Cal.",
                        "value" => $row[8]
                    ];
                    array_push($pesos, $wght);
                }
                if($row[9]){
                    $wght = [
                        "name"=>"Carb.",
                        "value" => $row[9]
                    ];
                    array_push($pesos, $wght);
                }
                if($row[10]){
                    $wght = [
                        "name"=>"Prot.",
                        "value" => $row[10]
                    ];
                    array_push($pesos, $wght);
                }
                if($row[11]){
                    $wght = [
                        "name"=>"Grasas.",
                        "value" => $row[11]
                    ];
                    array_push($pesos, $wght);
                }
                if($row[12]){
                    $wght = [
                        "name"=>"Fibra.",
                        "value" => $row[12]
                    ];
                    array_push($pesos, $wght);
                }
                $plato = [
                    "valor" => $row[3],
                    "codigo" => $row[6],
                    "descripcion" => $row[5],
                    "imagen" => $imagen,
                    "p_principal" => "",
                    "p_harinas" => "",
                    "p_verduras" => "",
                    "p_otro" => "",
                    "pesos" => $pesos
                ];
                if($row[13]){
                    $plato['message']=$row[13];
                }
                if ($row[2] == "Entrada") {
                    array_push($entradas, $plato);
                } else if ($row[2] == "Principal") {
                    array_push($principales, $plato);
                } else if ($row[2] == "Postre") {
                    array_push($postres, $plato);
                }
                $activeRow = $row;
            }
        }
        $this->importDish($entradas, $principales, $postres, $activeRow,$message);
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
