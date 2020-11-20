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
use App\Models\Article;
use App\Jobs\PostLocation;
use App\Jobs\InviteUsers;
use App\Models\Block;
use App\Jobs\AddFollower;
use App\Jobs\AddContact;
use App\Models\FileM;
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
use App\Imports\ArrayImport;
use App\Imports\ArrayMultipleSheetImport;

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
        //dd(storage_path('imports') . '/' . $filename);
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $sheet) {
            $headers = $sheet[0];
            foreach ($sheet as $item) {
                $row = null;
                foreach ($item as $key => $value) {
                    $row[$headers[$key]] = $value;
                }
                if ($row['id'] && $row['id'] != 'id') {
                    $country = Country::updateOrCreate(['id' => $row['id']], [
                                'id' => $row['id'],
                                'name' => $row['name'],
                                'area_code' => $row['area_code'],
                                'code' => $row['code']
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

        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $sheet) {
            $headers = $sheet[0];
            foreach ($sheet as $item) {
                $row = null;
                foreach ($item as $key => $value) {
                    $row[$headers[$key]] = $value;
                }
                $country = Country::where('name', 'like', '%' . $row['name'] . '%')->first();
                if ($country) {
                    $country->area_code = $row['area_code'];
                    $country->save();
                }
            }
        }
    }

    public function importRegions($filename) {

        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $headers = $row[0];
            foreach ($row as $item) {
                $sheet = null;
                foreach ($item as $key => $value) {
                    $sheet[$headers[$key]] = $value;
                }
                if ($sheet['id'] && $sheet['id'] != 'id') {
                    //dd($sheet);
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
    }

    public function importCities($filename) {

        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $headers = $row[0];
            foreach ($row as $item) {
                $sheet = null;
                foreach ($item as $key => $value) {
                    $sheet[$headers[$key]] = $value;
                }
                if ($sheet['id'] && $sheet['id'] != 'id') {
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

    public function importUsers($filename) {

        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $sheet) {
            $this->importUsersInternal($sheet);
        }
    }

    public function importUsersInternal(array $sheet) {

        $headers = $sheet[0];
        foreach ($sheet as $item) {
            $row = null;
            foreach ($item as $key => $value) {
                $row[$headers[$key]] = $value;
            }
            if ($row['email'] && $row['email'] != 'email') {
                $this->editUserData->create($row);
            }
        }
    }

    public function importUpdateUsers($filename) {
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $sheet) {
            $headers = $sheet[0];
            foreach ($sheet as $item) {
                $row = null;
                foreach ($item as $key => $value) {
                    $row[$headers[$key]] = $value;
                }
                if ($row['id'] && $row['id'] != 'id') {
                    $user = User::find($row['id']);
                    $this->editUserData->update($user, $row);
                }
            }
        }
    }

    public function importGlobalExcel($filename) {
        echo 'merchants file: ' . $filename;
        $reader = Excel::toArray(new ArrayMultipleSheetImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $key => $value) {
            if ($key == 'merchants') {
                $this->importMerchantsExcelInternal($value);
            } else if ($key == 'categories') {
                $this->importCategoriesExcelInternal($value);
            } else if ($key == 'reports') {
                $this->importReportsExcelInternal($value);
            } else if ($key == 'products') {
                $this->importProductsExcelInternal($value);
            } else if ($key == 'variants') {
                $this->importProductVariantsExcelInternal($value);
            } else if ($key == 'availabilities') {
                $this->importMerchantsAvailabilitiesExcelInternal($value);
            } else if ($key == 'ratings') {
                $this->importMerchantsRatingsExcelInternal($value);
            } else if ($key == 'polygons') {
                $this->importPolygonsInternal($value);
            }
        }
    }

    public function importMerchantsExcel($filename) {
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $this->importMerchantsExcelInternal($row);
        }
    }

    public function importMerchantsExcelInternal(array $row) {

        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }

            if ($sheet['user_id'] && $sheet['user_id'] != 'user_id') {
                $owner = User::find($sheet['user_id']);
                if ($owner) {
                    unset($sheet['user_id']);
                    $categoriesData = explode(",", $sheet['categories']);
                    unset($sheet['categories']);
                    $image = $sheet['icon'];
                    //dd($sheet['id']);
                    unset($sheet['icon']);
                    unset($sheet['id']);
                    $results = $this->editMapObject->saveOrCreateObject($owner, $sheet, "Merchant");
                    if ($results['status'] == "error") {
                        dd($results);
                    }
                    $merchant = $results['object'];
                    if ($categoriesData) {
                        $categories = Category::whereIn('id', $categoriesData)->get();
                        foreach ($categories as $item) {
                            $item->merchants()->save($merchant);
                        }
                    }
                    $merchant->slug = $this->slug_url($merchant->name);
                    $merchant->lat = rand(4527681, 4774930) / 1000000;
                    $merchant->long = rand(-74185612, -74035612) / 1000000;
                    if ($image) {
                        $merchant->icon = 'https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/' . $image;
                    } else {
                        $merchant->icon = 'https://picsum.photos/900/350';
                    }
                    $merchant->save();
                }
            }
        }
    }

    public function importReportsExcel($filename) {
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $this->importReportsExcelInternal($row);
        }
    }

    public function importReportsExcelInternal(array $row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['user_id'] && $sheet['user_id'] != 'user_id') {
                $owner = User::find($sheet['user_id']);
                if ($owner) {
                    unset($sheet['user_id']);
                    $categoriesData = explode(",", $sheet['categories']);
                    unset($sheet['categories']);
                    $image = $sheet['icon'];
                    if (array_key_exists('merchant_id', $sheet)) {
                        $merchant_id = $sheet['merchant_id'];
                        unset($sheet['merchant_id']);
                    }
                    unset($sheet['icon']);
                    unset($sheet['id']);
                    $results = $this->editMapObject->saveOrCreateObject($owner, $sheet, "Report");
                    $report = $results['object'];
                    if ($categoriesData) {
                        $categories = Category::whereIn('id', $categoriesData)->get();
                        foreach ($categories as $item) {
                            $item->reports()->save($report);
                        }
                    }
                    if ($image) {
                        $report->icon = 'https://s3.us-east-2.amazonaws.com/gohife/public/pets-report/' . $image;
                    } else {
                        $report->icon = 'https://picsum.photos/900/350';
                    }
                    $report->slug = $this->slug_url($report->name);
                    $report->lat = rand(4527681, 4774930) / 1000000;
                    $report->long = rand(-74185612, -74035612) / 1000000;
                    $report->save();
                    if ($merchant_id) {
                        $merchant = Merchant::find($merchant_id);
                        if ($merchant) {
                            $merchant->reports()->save($report);
                        }
                    }
                }
            }
        }
    }

    public function importCategoriesExcel($filename) {
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $this->importCategoriesExcelInternal($row);
        }
    }

    public function slug_url($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

    public function importCategoriesExcelInternal(array $row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['id'] && $sheet['id'] != 'id') {
                if ($sheet['icon']) {
                    $sheet['icon'] = 'https://s3.us-east-2.amazonaws.com/gohife/public/pets-categories/' . $sheet['icon'];
                } else {
                    $sheet['icon'] = 'https://picsum.photos/900/300';
                }
                $sheet['url'] = $this->slug_url($sheet['name']);
                $sheet['isActive'] = true;
                Category::firstOrCreate($sheet);
            }
        }
    }

    public function importArticlesExcel($filename) {
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $this->importArticlesExcelInternal($row);
        }
    }

    public function importArticlesExcelInternal(array $row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['id'] && $sheet['id'] != 'id') {
                $categoriesData = explode(",", $sheet['categories']);
                unset($sheet['categories']);
                $article = new Article($sheet);
                $image = $sheet['icon'];
                if ($image) {
                    $article->icon = 'https://s3.us-east-2.amazonaws.com/gohife/public/pets-banners/' . $image;
                } else {
                    $article->icon = 'https://picsum.photos/900/350';
                }
                $article->save();
                if ($categoriesData) {
                    $categories = Category::whereIn('id', $categoriesData)->get();
                    foreach ($categories as $item) {
                        $item->articles()->save($article);
                    }
                }
            }
        }
    }

    public function importMerchantsAvailabilitiesExcel($filename) {
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $this->importMerchantsAvailabilitiesExcelInternal($row);
        }
    }

    public function importMerchantsAvailabilitiesExcelInternal(array $row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['owner_id'] && $sheet['owner_id'] != 'owner_id') {
                $owner = User::find($sheet['owner_id']);
                if ($owner) {
                    unset($sheet['owner_id']);
                    $this->editBooking->addAvailabilityObject($sheet, $owner);
                }
            }
        }
    }

    public function importMerchantsBookingsExcel($filename) {
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $this->importMerchantsBookingsExcelInternal($row);
        }
    }

    public function importMerchantsBookingsExcelInternal(array $row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['owner_id'] && $sheet['owner_id'] != 'owner_id') {
                $owner = User::find($sheet['owner_id']);
                unset($sheet['owner_id']);
                $this->editBooking->addBookingObject($sheet, $owner);
            }
        }
    }

    public function importMerchantsRatingsExcel($filename) {
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $headers = $row[0];
            foreach ($row as $item) {
                $sheet = null;
                foreach ($item as $key => $value) {
                    $sheet[$headers[$key]] = $value;
                }
                if ($sheet['owner_id'] && $sheet['owner_id'] != 'owner_id') {
                    $owner = User::find($sheet['owner_id']);
                    unset($sheet['owner_id']);
                    $this->editRating->addRatingObject($sheet, $owner);
                }
            }
        }
    }

    public function importMerchantsRatingsExcelInternal(array $row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['owner_id'] && $sheet['owner_id'] != 'owner_id') {
                $owner = User::find($sheet['owner_id']);
                unset($sheet['owner_id']);
                $this->editRating->addRatingObject($sheet, $owner);
            }
        }
    }

    public function importProductsExcel($filename) {
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $this->importProductsExcelInternal($row);
        }
    }

    public function importProductsExcelInternal(array $row) {

        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                if ($headers[$key]) {
                    $sheet[$headers[$key]] = $value;
                }
            }
            if ($sheet['id'] && $sheet['id'] != 'id') {
                $product = Product::find($sheet['id']);
                if ($product) {
                    
                } else {
                    $product = new Product();
                }
                $image = $sheet['imagen'];
                $merchantsData = explode(",", $sheet['merchant_id']);
                unset($sheet['merchant_id']);
                unset($sheet['imagen']);
                $categoriesData = explode(",", $sheet['categories']);
                unset($sheet['categories']);

                //dd($sheet);
                $product->fill($sheet);
                $product->isActive = true;
                $product->slug = $this->slug_url($product->name);

                $product->save();

                if ($categoriesData) {
                    $categories = Category::whereIn('id', $categoriesData)->get();
                    foreach ($categories as $item) {
                        $product->categories()->save($item);
                    }
                }
                $merchantFolder = "";
                if ($merchantsData) {
                    $merchants = Merchant::whereIn('id', $merchantsData)->get();
                    foreach ($merchants as $item) {
                        $merchantFolder = $item->id;
                        $product->merchants()->save($item);
                    }
                }
                if ($image) {
                    $files = explode(",", $image);
                    foreach ($files as $value) {
                        $imageData = explode(".", $value);
                        $image = 'https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/' . $merchantFolder . '/' . $value;
                        $ext = $imageData[count($imageData) - 1];
                        $file = FileM::where("trigger_id", $product->id)->where("file", $image)->where("extension", $ext)->first();
                        if (!$file) {
                            FileM::create([
                                'user_id' => 2,
                                'trigger_id' => $product->id,
                                'file' => $image,
                                'extension' => $ext,
                                'type' => 'App\Models\Product'
                            ]);
                        }
                    }
                } else {
                    $image = 'https://picsum.photos/600/350';
                    $ext = 'jpg';
                    $file = FileM::where("trigger_id", $product->id)->where("file", $image)->where("extension", $ext)->first();
                    if (!$file) {
                        FileM::create([
                            'user_id' => 2,
                            'trigger_id' => $product->id,
                            'file' => $image,
                            'extension' => $ext,
                            'type' => 'App\Models\Product'
                        ]);
                    }
                }
            }
        }
    }

    public function importProductsExcel2($filename) {

        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $headers = $row[0];
            foreach ($row as $item) {
                $sheet = null;
                foreach ($item as $key => $value) {
                    $sheet[$headers[$key]] = $value;
                }
                if ($sheet['user_id'] && $sheet['user_id'] != 'user_id') {

                    $owner = User::find($sheet['user_id']);
                    if ($owner) {
                        unset($sheet['user_id']);
                        $sheet['id'] = "";
                        $image = $sheet['imagen'];
                        $ext = $sheet['ext'];
                        unset($sheet['imagen']);
                        unset($sheet['ext']);
                        $categoriesData = explode(",", $sheet['categories']);
                        unset($sheet['categories']);
                        $results = $this->editProduct->createOrUpdateProduct($owner, $sheet);
                        if ($results['status'] == 'error') {
                            dd($results);
                        }
                        $product = $results['product'];
                        if ($categoriesData) {
                            $categories = Category::whereIn('id', $categoriesData)->get();

                            foreach ($categories as $item) {
                                $product->categories()->save($item);
                            }
                        }
                        if ($image) {
                            $image = 'https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/' . $image;
                        } else {
                            $image = 'https://picsum.photos/600/350';
                            $ext = 'jpg';
                        }
                        FileM::create([
                            'user_id' => 2,
                            'trigger_id' => $product->id,
                            'file' => $image,
                            'extension' => $ext,
                            'type' => 'App\Models\Product'
                        ]);
                    }
                }
            }
        }
    }

    public function importProductVariantsExcel($filename) {
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $this->importProductVariantsExcelInternal($row);
        }
    }

    public function importProductVariantsExcelInternal($row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if (array_key_exists('id', $sheet)) {
                if ($sheet['id'] && $sheet['id'] != 'id') {
                    $product = Product::find($sheet['product_id']);
                    if (!$product) {
                        
                    }
                    $merchant = $product->merchants()->first();
                    $owner = $merchant->users()->first();
                    if ($owner) {
                        $variant = ProductVariant::find($sheet['id']);
                        if (!$variant) {
                            $sheet['id'] = "";
                        }
                        $attributes = $sheet['attributes'];
                        unset($sheet['attributes']);
                        $results = $this->editProduct->createOrUpdateVariant($owner, $sheet);
                        if ($results['status'] == 'success') {
                            if ($attributes) {
                                $variant = $results['variant'];
                                $variant->attributes = $attributes;
                                $variant->save();
                            }
                        } else {
                            dd($results);
                        }
                    }
                }
            }
        }
    }

    public function importPolygons($filename) {
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $row) {
            $this->importPolygonsInternal($row);
        }
    }

    public function importPolygonsInternal(array $row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['merchant_id'] && $sheet['merchant_id'] != 'merchant_id') {
                $cpolygon = new CoveragePolygon;
                $cpolygon->fill($sheet);
                $coordPoints = json_decode($cpolygon->coverage, true);
                if (is_array($coordPoints)) {
                    $totalPoints = [];
                    foreach ($coordPoints as $coordPoint) {
                        $pointArray = [$coordPoint['lng'], $coordPoint['lat']];
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
        }
    }

    public function importTranslationsExcel($filename) {
        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $sheet) {
            $headers = $sheet[0];
            foreach ($sheet as $item) {
                $row = null;
                foreach ($item as $key => $value) {
                    $row[$headers[$key]] = $value;
                }
                if ($row['id'] && $row['id'] != 'id') {
                    $cpolygon = new Translation;
                    if (!$row['body']) {
                        $row['body'] = '';
                    }
                    $cpolygon->fill($row);
                    $cpolygon->save();
                }
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

        $reader = Excel::toArray(new ArrayImport, storage_path('imports') . '/' . $filename);
        foreach ($reader as $sheet) {
            $headers = $sheet[0];
            foreach ($sheet as $item) {
                $row = null;
                foreach ($item as $key => $value) {
                    $row[$headers[$key]] = $value;
                }
                if ($row['user_id'] && $row['user_id'] != 'user_id') {
                    $user = User::find($row['user_id']);
                    if ($user) {
                        unset($row['user_id']);
                        $this->editUserData->createOrUpdateAddress($user, $row);
                    }
                }
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
