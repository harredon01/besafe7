<?php

namespace App\Services;

use App\Models\User;
use Excel;
use App\Models\Category;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use App\Models\Group;
use App\Models\Plan;
use App\Jobs\PostLocation;
use App\Jobs\InviteUsers;
use App\Models\Block;
use App\Jobs\AddFollower;
use App\Jobs\AddContact;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Translation;
use App\Models\Merchant;
use App\Models\OfficeHour;
use App\Models\Condition;
use App\Models\PaymentMethod;
use App\Services\EditUserData;
use App\Services\EditLocation;
use App\Services\EditAlerts;
use App\Services\EditGroup;
use App\Services\EditMerchant;
use DB;

class MerchantImport {

    protected $delimiter = ',';
    protected $enclosure = '"';
    protected $lineEnding = '\r\n';
    protected $editUserData;
    protected $editLocation;
    protected $editAlerts;
    protected $editMerchant;
    protected $editGroup;

    const OBJECT_LOCATION = 'Location';
    const OBJECT_REPORT = 'Report';

    public function __construct(EditUserData $editUserData, EditLocation $editLocation, EditAlerts $editAlerts, EditMerchant $editMerchant, EditGroup $editGroup) {
        $this->editUserData = $editUserData;
        $this->editLocation = $editLocation;
        $this->editAlerts = $editAlerts;
        $this->editMerchant = $editMerchant;
        $this->editGroup = $editGroup;
    }

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
            if (array_key_exists('user_id', $row)) {
                $user = User::find($row['user_id']);
                if ($user) {
                    $coords = ["lat" => $row['lat'], "long" => $row['long']];
                    $results = $this->editMerchant->saveOrCreateObject($user, $coords, "Merchant");
                    $merchant = $results['object'];
                    $row['telephone'] = $row['phone_number'];
                    if(!$row['telephone']){
                        $row['telephone'] = 111111;
                    }

                    
                    $row['description'] = $row['location'];
                    unset($row['minimum']);
                    unset($row['location']);
                    unset($row['name_html']);
                    unset($row['category']);
                    unset($row['category_slug']);
                    unset($row['delivery']);
                    unset($row['phone_number']);
                    unset($row['schedule']);
                    unset($row['schedule_search']);
                    unset($row[0]);
                    $row['id'] = $merchant->id;
                    $this->editMerchant->saveOrCreateObject($user, $row, "Merchant");
                }
            } else {
                if ($row['id']) {
                    $merchant1 = Merchant::updateOrCreate(['merchant_id' => $row['id']], [
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
    }

    public function importCountries($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            foreach ($sheet as $row) {
                if ($row['id']) {
                    $code;
                    if ($row['facebook_id'] == "NULL") {
                        $code = null;
                    } else {
                        $code = $sheet['facebook_id'];
                    }
                    $country = Country::updateOrCreate(['id' => $row['id']], [
                                'id' => $row['id'],
                                'name' => $row['name'],
                                'area_code' => $row['area_code'],
                                'code' => $row['code'],
                                'facebook_id' => $code
                    ]);
                }
            }
        }
    }

    public function importCountries2($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $row) {
            //foreach ($sheet as $row) {
            if ($row['geoname_id']) {
                $code;
                if (array_key_exists('facebook_id', $row)) {
                    if ($row['facebook_id'] == "NULL") {
                        $row['code'] = null;
                    } else {
                        $row['code'] = $sheet['facebook_id'];
                    }
                }

                $row['id'] = $row['geoname_id'];
                $row['name'] = $row['country_name'];
                $row['code'] = $row['country_iso_code'];
                unset($row['geoname_id']);
                unset($row['locale_code']);
                unset($row['continent_code']);
                unset($row['continent_name']);
                unset($row['country_iso_code']);
                unset($row['country_name']);
                if ($row['name']) {
                    $country = Country::updateOrCreate($row);
                }
            }
            //}
        }
    }

    public function importCountriesBlocks($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $row) {
            //foreach ($sheet as $row) {
            $country = Country::find($row['registered_country_geoname_id']);
            if ($country) {
                unset($row['registered_country_geoname_id']);
                unset($row['represented_country_geoname_id']);
                unset($row['is_anonymous_proxy']);
                unset($row['is_satellite_provider']);
                unset($row['postal_code']);
                unset($row['accuracy_radius']);
                unset($row['geoname_id']);
                $block = Block::updateOrCreate($row);
                $country->blocks()->save($block);
            }
            //}
        }
    }

    public function importCountriesAreas($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            foreach ($sheet as $row) {
                $country = Country::where('name', 'like', '%' . $row['name'] . '%')->first();
                if ($country) {
                    $country->area_code = $row['area_code'];
                    $country->save();
                }
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
                $region = Region::updateOrCreate([
                            'id' => $sheet['id']], [
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
                $city = City::updateOrCreate([
                            'id' => $sheet['id']], [
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

    public function importTranslations($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            unset($sheet['']);
            $translation = Translation::updateOrCreate([
                        'code' => $sheet['code'],
                        'language' => $sheet['language'],
                        'value' => $sheet['value']
            ]);
        }
    }

    public function importCities2($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            if ($sheet['geoname_id']) {
                $sheet['id'] = $sheet['geoname_id'];
                unset($sheet['geoname_id']);
                $city = City::updateOrCreate($sheet);
            }
        }
    }

    public function importCitiesBlocks($filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $city = City::find('name', 'like', '%' . $sheet['name'] . '%');
            if ($city) {
                $city->lat = $sheet['latitude'];
                $city->long = $sheet['longitude'];
                $city->country_id = $sheet['registered_country_geoname_id'];
                $city->network = $sheet['network'];
                $city->save();
            }
        }
    }

    public function importCitiesData($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            foreach ($sheet as $row) {
                $city = City::where('name', 'like', '%' . $row['name'] . '%')->first();
                if ($city) {
                    $city->lat = $sheet['lat'];
                    $city->long = $sheet['long'];
                    $city->save();
                }
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
                $product = Product::updateOrCreate(['id' => $sheet['id']], [
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

    public function importPlans($filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $plan = Plan::updateOrCreate($sheet);
        }
    }

    public function importProductVariants($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            if ($sheet['id']) {
                $productVariant = ProductVariant::updateOrCreate(['id' => $sheet['id']], [
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
                $attribute = Attribute::updateOrCreate(['id' => $sheet['id']], [
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
                $sheet['password'] = bcrypt(intval($sheet['password']) . "");
                $sheet['id'] = intval($sheet['id']) . "";
                $sheet['cellphone'] = intval($sheet['cellphone']) . "";
                $sheet['area_code'] = intval($sheet['area_code']) . "";
                $sheet['docnum'] = intval($sheet['docnum']) . "";
                if ($sheet['language'] == "En-us") {
                    $sheet['language'] = "en-us";
                } else if ($sheet['language'] == "Es-co") {
                    $sheet['language'] = "en-us";
                }

                unset($sheet[0]);
                $user = User::updateOrCreate(["id" => $sheet['id']], $sheet);
            }
        }
    }

    public function importConditions($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            if ($sheet["product_id"] == "NULL") {
                unset($sheet["product_id"]);
            }
            if ($sheet["product_variant_id"] == "NULL") {
                unset($sheet["product_variant_id"]);
            }
            if ($sheet["city_id"] == "NULL") {
                unset($sheet["city_id"]);
            }
            if ($sheet["region_id"] == "NULL") {
                unset($sheet["region_id"]);
            }
            if ($sheet["country_id"] == "NULL") {
                unset($sheet["country_id"]);
            }
            Condition::updateOrCreate(["id" => $sheet['id']], $sheet);
        }
    }

    public function importAddresses($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $user = User::find($sheet['user_id']);
            if ($user) {
                $this->editUserData->createOrUpdateAddress($user, $sheet);
            }
        }
    }

    public function importLocations($filename, $userId) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();

        foreach ($reader as $sheet) {
            $user = User::find($userId);
            if ($user) {
                unset($sheet["user_id"]);
                $coords = array();
                $location = array();
                $extras = array();
                $activity = array();
                $battery = array();
                $coords["latitude"] = $sheet["lat"];
                $coords["longitude"] = $sheet["long"];
                $coords["accuracy"] = $sheet["accuracy"];
                $coords["speed"] = $sheet["speed"];
                $coords["heading"] = $sheet["heading"];
                $coords["altitude"] = $sheet["altitude"];
                $dalast = false;
                if ($sheet["islast"]) {
                    $extras["islast"] = $sheet["islast"];
                    $dalast = true;
                } else {
                    unset($sheet["islast"]);
                }
                $extras["code"] = $sheet["code"];
                $activity["type"] = $sheet["activity"];
                $activity["confidence"] = $sheet["confidence"];
                $battery["level"] = $sheet["battery"];
                $battery["is_charging"] = $sheet["is_charging"];
                $location["timestamp"] = $sheet["created_at"];
                $location["is_moving"] = $sheet["is_moving"];
                $location["coords"] = $coords;
                $location["extras"] = $extras;

                $location["activity"] = $activity;
                $location["battery"] = $battery;
                $data["location"] = $location;
                dispatch(new PostLocation($user, $data));
                if ($dalast) {
                    sleep(1);
                }
            }
        }
    }

    public function importFollowers($filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $user = User::find($sheet['user_id']);
            if ($user) {
                $sheet['follower'] = str_replace("|", ",", $sheet['follower']);
                dispatch(new AddFollower($user, $sheet));
            }
        }
    }

    public function importReports($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $user = User::find($sheet['user_id']);
            if ($user) {
                $coords = ["lat" => $sheet['lat'], "long" => $sheet['long']];
                $results = $this->editMerchant->saveOrCreateObject($user, $coords, self::OBJECT_REPORT);
                $report = $results['object'];
                $sheet['id'] = $report->id;
                $sheet['report_time'] = date("Y-m-d h:i:sa");
                $this->editMerchant->saveOrCreateObject($user, $sheet, self::OBJECT_REPORT);
            }
        }
    }

    public function importContacts($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $user = User::find($sheet['user_id']);
            if ($user) {
                dispatch(new AddContact($user, $sheet['contact_id']));
            }
            $contact = User::find($sheet['contact_id']);
            if ($contact) {
                dispatch(new AddContact($contact, $sheet['user_id']));
            }
        }
    }

    public function importGroups($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $user = User::find($sheet['user_id']);
            if ($user) {
                unset($sheet['user_id']);
                unset($sheet['admin_id']);
                unset($sheet['']);
                $contacts = explode(",", $sheet['contacts']);
                $sheet['contacts'] = $contacts;
                $this->editGroup->saveOrCreateGroup($sheet, $user);
            }
        }
    }

    public function inviteGroups($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $user = User::find($sheet['user_id']);
            if ($user) {
                unset($sheet['user_id']);
                $contacts = explode(",", $sheet['contacts']);
                $sheet['contacts'] = $contacts;
                $group = Group::find($sheet['group_id']);
                //$this->editGroup->inviteUsers($user, $sheet, false);
                dispatch(new InviteUsers($user, $sheet, false, $group));
            }
        }
    }

    public function shareLocationGroup($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $user = User::find($sheet['user_id']);
            if ($user) {
                unset($sheet['user_id']);
                $sheet['type'] = 'group';
                $sheet['object'] = 'Location';
                $sheet['follower'] = $sheet['group_id'];
                unset($sheet['group_id']);
                $this->editAlerts->addFollower($sheet, $user);
                //dispatch(new AddFollower($user, $sheet));
            }
        }
    }

    public function sendMessageGroup($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $user = User::find($sheet['user_id']);
            if ($user) {
                unset($sheet['user_id']);
                $this->editAlerts->postMessage($user, $sheet);
                //dispatch(new PostMessage($user, $sheet));
            }
        }
    }

}
