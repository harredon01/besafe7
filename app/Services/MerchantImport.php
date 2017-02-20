<?php

namespace App\Services;

use App\Models\User;
use Excel;
use App\Models\Category;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Merchant;
use App\Models\OfficeHour;
use App\Models\PaymentMethod;
use DB;

class MerchantImport {

    protected $delimiter = ',';
    protected $enclosure = '"';
    protected $lineEnding = '\r\n';

    public function getFile() {
        return storage_path('imports') . '/merchant test.xlsx';
    }

    public function getFilters() {
        return [
            'chunk'
        ];
    }

    public function importNewMerchant(User $user, $filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $merchant;
        $reader = $excel->toArray();
        $results = DB::table('categories')->orderBy('id', 'desc')->first();
        $idconstant = $results->id;
        $count = 1;
        foreach ($reader as $sheet) {
            if ($count == 1) {
                foreach ($sheet as $row) {
                    if ($row['name'] && $row['type'] && $row['email'] && $row['telephone'] && $row['address'] && $row['description'] && $row['icon'] && $row['lat'] && $row['long'] && $row['minimum'] && $row['delivery_time'] && $row['delivery_price']) {
                        $merchant1 = Merchant::create([
                                    'name' => $row['name'],
                                    'type' => $row['type'],
                                    'email' => $row['email'],
                                    'telephone' => $row['telephone'] . "",
                                    'address' => $row['address'],
                                    'description' => $row['description'],
                                    'icon' => $row['icon'],
                                    'lat' => $row['lat'],
                                    'long' => $row['long'],
                                    'minimum' => $row['minimum'],
                                    'delivery_time' => $row['delivery_time'],
                                    'delivery_price' => $row['delivery_price'],
                                    'status' => "active",
                        ]);
                        $merchant1->users()->save($user);
                    }
                }
                $count++;
            } elseif ($count == 2) {
                foreach ($sheet as $row) {
                    if ($row['day'] && $row['open'] && $row['close']) {
                        $intday;
                        if ($row['day'] == "lunes") {
                            $intday = 2;
                        } elseif ($row['day'] == "martes") {
                            $intday = 3;
                        } elseif ($row['day'] == "miercoles") {
                            $intday = 4;
                        } elseif ($row['day'] == "jueves") {
                            $intday = 5;
                        } elseif ($row['day'] == "viernes") {
                            $intday = 6;
                        } elseif ($row['day'] == "sabado") {
                            $intday = 7;
                        } elseif ($row['day'] == "domingo") {
                            $intday = 1;
                        }
                        $horario = OfficeHour::create([
                                    "day" => $intday,
                                    "open" => $row['open'],
                                    "close" => $row['close'],
                        ]);
                        $merchant1->hours()->save($horario);
                    }
                }
                $count++;
            } elseif ($count == 3) {
                foreach ($sheet as $row) {
                    if ($row['name'] && $row['level'] && $row['description']) {
                        $category1 = Category::create([
                                    'name' => $row['name'],
                                    'level' => $row['level'],
                                    'description' => $row['description'],
                        ]);
                        $merchant1->categories()->save($category1);
                    }
                }
                $count++;
            } elseif ($count == 4) {
                foreach ($sheet as $row) {
                    if ($row['name'] && $row['description'] && $row['price'] && $row['tax'] && $row['sku'] && $row['ref2'] && $row['total'] && $row['quantity'] && $row['category_id']) {
                        $rere = intval($idconstant) + intval($row['category_id']);
                        $product1 = new Product([
                            'name' => $row['name'],
                            'description' => $row['description'],
                            'price' => $row['price'],
                            'sku' => $row['sku'],
                            'ref2' => $row['ref2'],
                            'tax' => $row['tax'],
                            'total' => $row['total'],
                            'quantity' => $row['quantity'],
                            'category_id' => $rere
                        ]);
                        $merchant1->products()->save($product1);
                        $product1->save();
                    }
                }
                $count++;
            } elseif ($count == 5) {
                foreach ($sheet as $row) {
                    if ($row['id'] && $row['name'] && $row['active']) {
                        if ($row['active'] == "1") {
                            $payment = PaymentMethod::find(intval($row['id']));
                            $merchant1->paymentMethods()->save($payment);
                        }
                    }
                }
                $count++;
            }
        }
    }

    public function importUpdateMerchant(User $user, $filename, $merchantid) {
        $found = false;
        $merchant1;
        foreach ($user->merchants as $merchant) {
            if ($merchant->id == $merchantid) {
                $found = true;
                $merchant1 = $merchant;
            }
        }
        if ($found) {
            $excel = Excel::load(storage_path('imports') . '/' . $filename);
            $reader = $excel->toArray();
            $count = 1;
            DB::table('merchant_payment_methods')->where('merchant_id', '=', $merchant1->id)->delete();
            foreach ($reader as $sheet) {
                if ($count == 1) {
                    foreach ($sheet as $row) {

                        if ($row['name'] && $row['type'] && $row['email'] && $row['telephone'] && $row['address'] && $row['description'] && $row['icon'] && $row['lat'] && $row['long'] && $row['minimum'] && $row['delivery_time'] && $row['delivery_price']) {

                            $merchant1->name = $row['name'];
                            $merchant1->type = $row['type'];
                            $merchant1->email = $row['email'];
                            $merchant1->telephone = $row['telephone'] . "";
                            $merchant1->address = $row['address'];
                            $merchant1->description = $row['description'];
                            $merchant1->icon = $row['icon'];
                            $merchant1->lat = $row['lat'];
                            $merchant1->long = $row['long'];
                            $merchant1->minimum = $row['minimum'];
                            $merchant1->delivery_time = $row['delivery_time'];
                            $merchant1->delivery_price = $row['delivery_price'];
                            $merchant1->status = "active";
                            $merchant1->save();
                        }
                    }
                    $count++;
                } elseif ($count == 2) {
                    foreach ($sheet as $row) {
                        if ($row['day'] && $row['open'] && $row['close']) {
                            $intday;
                            if ($row['day'] == "lunes") {
                                $intday = 2;
                            } elseif ($row['day'] == "martes") {
                                $intday = 3;
                            } elseif ($row['day'] == "miercoles") {
                                $intday = 4;
                            } elseif ($row['day'] == "jueves") {
                                $intday = 5;
                            } elseif ($row['day'] == "viernes") {
                                $intday = 6;
                            } elseif ($row['day'] == "sabado") {
                                $intday = 7;
                            } elseif ($row['day'] == "domingo") {
                                $intday = 1;
                            }
                            $horario = OfficeHour::find(intval($row['id']));

                            $horario->day = $intday;
                            $horario->open = $row['open'];
                            $horario->close = $row['close'];
                            $horario->save();
                        }
                    }
                    $count++;
                } elseif ($count == 3) {
                    foreach ($sheet as $row) {
                        if ($row['name'] && $row['level'] && $row['description']) {
                            $category1 = Category::find(intval($row['id']));
                            $category1->name = $row['name'];
                            $category1->level = $row['level'];
                            $category1->description = $row['description'];
                            $category1->save();
                        }
                    }
                    $count++;
                } elseif ($count == 4) {
                    foreach ($sheet as $row) {
                        if ($row['name'] && $row['description'] && $row['price'] && $row['tax'] && $row['total'] && $row['sku'] && $row['ref2'] && $row['quantity'] && $row['category_id']) {
                            $product1 = Product::find(intval($row['id']));
                            $product1->name = $row['name'];
                            $product1->description = $row['description'];
                            $product1->sku = $row['sku'];
                            $product1->ref2 = $row['ref2'];
                            $product1->price = $row['price'];
                            $product1->tax = $row['tax'];
                            $product1->total = $row['total'];
                            $product1->quantity = $row['quantity'];
                            $product1->category_id = intval($row['category_id']);
                            $product1->save();
                        }
                    }
                    $count++;
                } elseif ($count == 5) {
                    foreach ($sheet as $row) {
                        if ($row['id'] && $row['name'] && $row['active']) {
                            if ($row['active'] == "1") {
                                $payment = PaymentMethod::find(intval($row['id']));
                                $merchant1->paymentMethods()->save($payment);
                            }
                        }
                    }
                    $count++;
                }
            }
        }
    }

    public function exportMerchant(User $user, $filename, $merchantid) {
        $found = false;
        $merchant1;
        foreach ($user->merchants as $merchant) {
            if ($merchant->id == $merchantid) {
                $found = true;
                $merchant1 = $merchant;
            }
        }
        $rand = rand(1, 100);

        Excel::create('Filename' . $rand, function($excel) use($merchant1) {

            $excel->setTitle('Our new awesome title');

            // Chain the setters
            $excel->setCreator('Maatwebsite')
                    ->setCompany('Maatwebsite');

            // Call them separately
            $excel->setDescription('A demonstration to change the file properties');
            $excel->sheet('merchants', function($sheet) use($merchant1) {
                $data = array(
                    $merchant1->toArray()
                );
                $sheet->fromArray($data, null, 'A1', true);
            });
            $excel->sheet('office_hours', function($sheet) use($merchant1) {
                $data = array();
                foreach ($merchant1->hours as $hour) {
                    if ($hour->day == 1) {
                        $hour->day = "domingo";
                    }
                    if ($hour->day == 2) {
                        $hour->day = "lunes";
                    }
                    if ($hour->day == 3) {
                        $hour->day = "martes";
                    }
                    if ($hour->day == 4) {
                        $hour->day = "miercoles";
                    }
                    if ($hour->day == 5) {
                        $hour->day = "jueves";
                    }
                    if ($hour->day == 6) {
                        $hour->day = "viernes";
                    }
                    if ($hour->day == 7) {
                        $hour->day = "sabado";
                    }
                    array_push($data, $hour->toArray());
                }
                $sheet->fromArray($data, null, 'A1', true);
            });
            $excel->sheet('categories', function($sheet) use($merchant1) {
                $data = array();
                $counter = 1;
                foreach ($merchant1->categories as $category) {
                    array_push($data, $category->toArray());
                    $counter++;
                }
                $sheet->fromArray($data, null, 'A1', true);
            });
            $excel->sheet('products', function($sheet) use($merchant1) {
                $data = array();
                $counter = 1;
                foreach ($merchant1->products as $product) {
                    array_push($data, $product->toArray());
                }
                $sheet->fromArray($data, null, 'A1', true);
            });
            $excel->sheet('payment_method', function($sheet) use($merchant1) {
                $data = array();
                $counter = 1;
                foreach (PaymentMethod::all() as $method) {
                    $esta = "0";
                    foreach ($merchant1->paymentMethods as $pmethod) {
                        if ($pmethod->id == $method->id) {
                            $esta = "1";
                        }
                    }
                    array_push($data, array(
                        "id" => $method->id,
                        "name" => $method->name,
                        "active" => $esta
                    ));
                }
                $sheet->fromArray($data, null, 'A1', true);
            });
        })->store('xlsx');
    }

    public function exportMerchantOrders(User $user, $filename, $merchantid) {
        $found = false;
        $merchant1;
        foreach ($user->merchants as $merchant) {
            if ($merchant->id == $merchantid) {
                $found = true;
                $merchant1 = $merchant;
            }
        }
        $rand = rand(1, 100);
        Excel::create('Filename' . $rand, function($excel) use($merchant1) {

            $excel->setTitle('Our new awesome title');

            // Chain the setters
            $excel->setCreator('Maatwebsite')
                    ->setCompany('Maatwebsite');

            // Call them separately
            $excel->setDescription('A demonstration to change the file properties');
            $excel->sheet('office_hours', function($sheet) use($merchant1) {
                $data = array();
                foreach ($merchant1->orders as $order) {

                    array_push($data, $order->toArray());
                }
                $sheet->fromArray($data, null, 'A1', true);
            });
        })->store('xlsx');
    }

    public function exportMerchantJson($filename) {
        $string = file_get_contents($filename);
        $thepolice = json_decode($string, true);
        $rand = rand(1, 100);
        Excel::create('Filename' . $rand, function($excel) use($thepolice) {

            $excel->setTitle('Police bogota');

            // Chain the setters
            $excel->setCreator('Besafe')
                    ->setCompany('Besafe');

            // Call them separately
            $excel->setDescription('A demonstration to change the file properties');
            $excel->sheet('police', function($sheet) use($thepolice) {
                $data = array();
                $count = 0;
                foreach ($thepolice as $police) {
                    $count++;
                    if ($count > 538) {

                        $place = [
                            "id" => $police['id'],
                            "name" => $police['name'],
                            "description" => "",
                            "email" => "",
                            "minimum" => "",
                            "delivery_price" => "",
                            "city_id" => "",
                            "region_id" => "",
                            "country_id" => "",
                            "name_html" => $police['name_html'],
                            "type" => "medical",
                            "category" => $police['category'],
                            "category_slug" => $police['category_slug'],
                            "location" => $police['location'],
                            "address" => $police['address'],
                            "delivery" => $police['delivery'],
                            "phone_number" => $police['phone_number'],
                            "lat" => $police['coordinates']['lat'],
                            "long" => $police['coordinates']['long'],
                            "schedule" => $police['schedule'],
                            "schedule_search" => $police['schedule_search'],
                        ];
                        array_push($data, $place);
                    }
                }
                $sheet->fromArray($data, null, 'A1', true);
            });
        })->store('xlsx');
    }

    public function importMerchants($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $row) {
            if ($row['id']) {
                $merchant1 = Merchant::create([
                            'merchant_id' => $row['id'],
                            'name' => $row['name'],
                            'type' => $row['type'],
                            'email' => $row['email'],
                            'telephone' => $row['phone_number'],
                            'address' => $row['address'] . ", " . $row['location'],
                            'description' => $row['description'],
                            'icon' => $row['category'],
                            'lat' => $row['lat'],
                            'long' => $row['long'],
                            'minimum' => $row['minimum'],
                            'city_id' => $row['city_id'],
                            'region_id' => $row['region_id'],
                            'country_id' => $row['country_id'],
                            'delivery_time' => $row['schedule_search'],
                            'delivery_price' => $row['delivery_price'],
                            'status' => "active",
                ]);
            }
        }
    }

    public function importCountries($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            if ($sheet['id']) {
                $code;
                if ($sheet['facebook_id'] == "NULL") {
                    $code = null;
                } else {
                    $code = $sheet['facebook_id'];
                }
                $country = Country::create([
                            'id' => $sheet['id'],
                            'name' => $sheet['name'],
                            'area_code' => $sheet['area_code'],
                            'code' => $sheet['code'],
                            'facebook_id' => $code
                ]);
            }
        }
    }

    public function importRegions($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            if ($sheet['id']) {
                $code;
                if ($sheet['facebook_id'] == "NULL") {
                    $code = null;
                } else {

                    $code = $sheet['facebook_id'];
                }
                $code2;
                if ($sheet['facebook_country_id'] == "NULL") {
                    $code2 = null;
                } else {
                    $code2 = $sheet['facebook_country_id'];
                }
                $region = Region::create([
                            'id' => $sheet['id'],
                            'name' => $sheet['name'],
                            'country_id' => $sheet['country_id'],
                            'code' => $sheet['code'],
                            'facebook_id' => $code,
                            'facebook_country_id' => $code2
                ]);
            }
        }
    }

    public function importCities($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            if ($sheet['id']) {
                $code;
                if ($sheet['facebook_id'] == "NULL") {
                    $code = null;
                } else {
                    $code = $sheet['facebook_id'];
                }
                $code2;
                if ($sheet['facebook_country_id'] == "NULL") {
                    $code2 = null;
                } else {
                    $code2 = $sheet['facebook_country_id'];
                }
//                if( $sheet['name'] =="BELMIRA"){
//                    dd($sheet);
//                }
                $city = City::create([
                            'id' => $sheet['id'],
                            'name' => $sheet['name'],
                            'code' => $sheet['code'],
                            'facebook_id' => $code,
                            'country_id' => $sheet['country_id'],
                            'facebook_country_id' => $code2,
                            'region_id' => $sheet['region_id'],
                            'lat' => $sheet['lat'],
                            'long' => $sheet['long'],
                ]);
            }
        }
    }

    public function importProducts($filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            if ($sheet['id']) {
                $code;
                if ($sheet['merchant_id'] == "NULL") {
                    $code = null;
                } else {
                    $code = $sheet['merchant_id'];
                }
                $product = Product::create([
                            'id' => $sheet['id'],
                            'name' => $sheet['name'],
                            'description' => $sheet['slug'],
                            'merchant_id' => $code,
                            'isActive' => $sheet['isactive'],
                            'slug' => $sheet['slug'],
                ]);
            }
        }
    }

    public function importProductVariants($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            if ($sheet['id']) {
                $productVariant = ProductVariant::create([
                            'id' => $sheet['id'],
                            'product_id' => $sheet['product_id'],
                            'sku' => $sheet['sku'],
                            'ref2' => $sheet['ref2'],
                            'isActive' => $sheet['isactive'],
                            'price' => $sheet['price'],
                            'sale' => $sheet['sale'],
                            'quantity' => $sheet['quantity'],
                            'is_digital' => $sheet['is_digital'],
                            'is_shippable' => $sheet['is_shippable'],
                                //'attributes' => $sheet['attributes'],
                ]);
            }
        }
    }

    public function importAttributes($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            if ($sheet['id']) {
                $attribute = Attribute::create([
                            'id' => $sheet['id'],
                            'name' => $sheet['name'],
                            'description' => $sheet['description'],
                            'type' => $sheet['type']
                ]);
            }
        }
    }

    public function importAttributeOptions($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            if ($sheet['id']) {
                $attribute = Attribute::find($sheet['attribute_id']);
                $attributeOption = new AttributeOption([
                    'id' => $sheet['id'],
                    'valueS' => $sheet['values'],
                    'valueI' => $sheet['valuei']
                ]);
                $attribute->attributeOptions()->save($attributeOption);
            }
        }
    }

    public function importProductAttributeOptions($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        $i = 0;
        foreach ($reader as $sheet) {
            if ($sheet['product_variant_id']) {
                $attribute = Attribute::find($sheet['attribute_id']);
                $attributeOption = AttributeOption::find($sheet['attribute_option_id']);
                $product = Product::find($sheet['product_id']);
                $productVariant = ProductVariant::find($sheet['product_variant_id']);
                if (fmod($i, 4) == 0) {
                    $productVariant->attributeOptions()->save($attributeOption, ['product_id' => $sheet['product_id'], 'attribute_id' => $sheet['attribute_id'], 'type' => 'option']);
                } else if (fmod($i, 4) == 1) {
                    $product->attributes()->save($attribute, ['product_variant_id' => $sheet['product_variant_id'], 'attribute_option_id' => $sheet['attribute_option_id'], 'type' => 'option']);
                } else if (fmod($i, 4) == 2) {
                    $attribute->products()->save($product, ['product_variant_id' => $sheet['product_variant_id'], 'attribute_option_id' => $sheet['attribute_option_id'], 'type' => 'option']);
                } else if (fmod($i, 4) == 3) {
                    $attributeOption->productVariants()->save($productVariant, ['product_id' => $sheet['product_id'], 'attribute_id' => $sheet['attribute_id'], 'type' => 'option']);
                }
                $losAttributes = json_decode($productVariant->attributes, true);
                if (!$losAttributes) {
                    $losAttributes = array();
                }
                $losAttributes[$attribute->name] = $attributeOption->valueS;
                $productVariant->attributes = json_encode($losAttributes);
                $productVariant->save();
                $i++;
            }
        }
    }

    public function importUsers($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            if ($sheet['id']) {
                $user = User::create([
                            'id' => $sheet['id'],
                            'firstName' => $sheet['firstName'],
                            'emailNotifications' => $sheet['emailNotifications'],
                            'pushNotifications' => $sheet['pushNotifications'],
                            'green' => $sheet['green'],
                            'red' => $sheet['red'],
                            'is_alerting' => $sheet['is_alerting'],
                            'is_tracking' => $sheet['is_tracking'],
                            'alert_type' => $sheet['alert_type'],
                            'notify_location' => $sheet['notify_location'],
                            'lastName' => $sheet['lastName'],
                            'cellphone' => $sheet['cellphone'],
                            'area_code' => $sheet['area_code'],
                            'hash' => $sheet['hash'],
                            'trip' => $sheet['trip'],
                            'token' => $sheet['token'],
                            'platform' => $sheet['platform'],
                            'name' => $sheet['name'],
                            'docType' => $sheet['docType'],
                            'docNum' => $sheet['docNum'],
                            'email' => $sheet['email'],
                            'username' => $sheet['username'],
                            'avatar' => $sheet['avatar'],
                            'gender' => $sheet['gender'],
                            'birth' => $sheet['birth'],
                            'weight' => $sheet['weight'],
                            'blood_type' => $sheet['blood_type'],
                ]);
            }
        }
    }

}
