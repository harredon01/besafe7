<?php

namespace App\Services;

use App\Models\User;
use Excel;
use App\Models\Category;
use App\Models\CoveragePolygon;
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
use App\Services\EditMessages;
use App\Services\EditMapObject;
use App\Services\EditProduct;
use App\Services\EditBooking;
use App\Services\EditRating;
use Grimzy\LaravelMysqlSpatial\Types\MultiPolygon;
use DB;

class MerchantImport {

    protected $delimiter = ',';
    protected $enclosure = '"';
    protected $lineEnding = '\r\n';
    protected $editUserData;
    protected $editLocation;
    protected $editAlerts;
    protected $editMapObject;
    protected $editGroup;
    protected $editMessages;
    protected $editProduct;
    protected $editBooking;
    protected $editRating;

    const OBJECT_LOCATION = 'Location';
    const OBJECT_REPORT = 'Report';

    public function __construct(EditUserData $editUserData, EditLocation $editLocation, EditAlerts $editAlerts, EditMapObject $editMapObject, EditGroup $editGroup, EditMessages $editMessages, EditProduct $editProduct, EditBooking $editBooking, EditRating $editRating) {
        $this->editUserData = $editUserData;
        $this->editLocation = $editLocation;
        $this->editAlerts = $editAlerts;
        $this->editMapObject = $editMapObject;
        $this->editGroup = $editGroup;
        $this->editMessages = $editMessages;
        $this->editProduct = $editProduct;
        $this->editBooking = $editBooking;
        $this->editRating = $editRating;
    }

    public function getFile() {
        return storage_path('imports') . '/merchant test.xlsx';
    }

    public function getFilters() {
        return [
            'chunk'
        ];
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
                            'description' => $sheet['description'],
                            'isActive' => $sheet['isactive'],
                            'hash' => $sheet['slug'],
                ]);
            }
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

    /*
     * [
      'firstName',
      'lastName',
      'docNum',
      'docType',
      'area_code',
      'cellphone',
      'email',
      'optinMarketing',
      'password',
      'password_confirmation',
      'language',
      'city_id',
      'region_id',
      'country_id',
      ]
     */

    public function importUsers($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $this->editUserData->create($sheet);
        }
    }

    public function importUpdateUsers($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $user = User::find($sheet['id']);
            $this->editUserData->update($user, $sheet);
        }
    }

    public function importMerchantsExcel($filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $owner = User::find($sheet['owner_id']);
            unset($sheet['owner_id']);
            $categoriesData = explode(",", $sheet['categories']);
            unset($sheet['categories']);
            $results = $this->editMapObject->saveOrCreateObject($owner, $sheet, "Merchant");
            $categories = Category::whereIn('id', $categoriesData)->get();
            $merchant = $results['object'];
            foreach ($categories as $item) {
                $item->merchants()->save($merchant);
            }
        }
    }

    public function importCategoriesExcel($filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            Category::create($sheet);
        }
    }

    public function importMerchantsAvailabilitiesExcel($filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $owner = User::find($sheet['owner_id']);
            unset($sheet['owner_id']);
            $this->editBooking->addAvailabilityObject($sheet, $owner);
        }
    }

    public function importMerchantsBookingsExcel($filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $owner = User::find($sheet['owner_id']);
            unset($sheet['owner_id']);
            $this->editBooking->addBookingObject($sheet, $owner);
        }
    }

    public function importMerchantsRatingsExcel($filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $owner = User::find($sheet['owner_id']);
            unset($sheet['owner_id']);
            $this->editRating->addRatingObject($sheet, $owner);
        }
    }

    public function importProductsExcel($filename) {

        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $owner = User::find($sheet['user_id']);
            unset($sheet['user_id']);
            $categoriesData = explode(",", $sheet['categories']);
            unset($sheet['categories']);
            $results = $this->editProduct->createOrUpdateProduct($owner, $sheet);
            $categories = Category::whereIn('id', $categoriesData)->get();
            $product = $results['product'];
            foreach ($categories as $item) {
                $item->products()->save($product);
            }
        }
    }

    public function importProductVariantsExcel($filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $owner = User::find($sheet['user_id']);
            unset($sheet['user_id']);
            $attributes = $sheet['attributes'];
            unset($sheet['attributes']);
            $results = $this->editProduct->createOrUpdateVariant($owner, $sheet);
            if ($attributes) {
                $variant = $results['variant'];
                $variant->attributes = $attributes;
                $variant->save();
            }
        }
    }
    
    public function importPolygons($filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $cpolygon = new CoveragePolygon;
            $cpolygon->fill($sheet);
            $coordPoints = json_decode($cpolygon->coverage, true);
            $totalPoints = [];
            foreach ($coordPoints as $coordPoint) {
                $pointArray = [ $coordPoint['lng'],$coordPoint['lat']];
                array_push($totalPoints, $pointArray);
            }
            $result = [
                "type" => "MultiPolygon",
                "coordinates" => 
                    [[$totalPoints]]
                
            ];
            $mp = MultiPolygon::fromJson(json_encode($result));
            $cpolygon->geometry = $mp;
            $cpolygon->save();
        }
    }
    public function importTranslations($filename) {
        $excel = Excel::load(storage_path('imports') . '/' . $filename);
        $reader = $excel->toArray();
        foreach ($reader as $sheet) {
            $cpolygon = new Translation;
            $cpolygon->fill($sheet);
            $cpolygon->save();
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
        $date = date("Y-m-d");
        foreach ($reader as $sheet) {
            $user = User::find($sheet['user_id']);
            if ($user) {
                $coords = ["lat" => $sheet['lat'], "long" => $sheet['long'], "name" => "test", "type" => "burglary", "address" => "address", "report_time" => $date];
                $results = $this->editMapObject->saveOrCreateObject($user, $coords, self::OBJECT_REPORT);
                $report = $results['object'];
                $sheet['id'] = $report->id;
                $sheet['report_time'] = date("Y-m-d h:i:sa");
                $this->editMapObject->saveOrCreateObject($user, $sheet, self::OBJECT_REPORT);
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
                $group->updated_at = date("Y-m-d H:i:s");
                $group->save();
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
                $this->editMessages->postMessage($user, $sheet);
                //dispatch(new PostMessage($user, $sheet));
            }
        }
    }

}
