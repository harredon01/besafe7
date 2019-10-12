<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Merchant;
use App\Services\EditOrder;
use App\Services\EditRating;
use App\Services\EditBooking;
use App\Services\MerchantImport;
use App\Models\CoveragePolygon;
use App\Models\PaymentMethod;
use App\Models\Booking;

class MerchantTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */

    /**
     * The edit profile implementation.
     *
     */
    protected $editOrder;

    /**
     * The edit profile implementation.
     *
     */
    protected $editRating;

    /**
     * The edit profile implementation.
     *
     */
    protected $editBooking;

    /**
     * The edit profile implementation.
     *
     */
    protected $merchantImport;

    public function __construct(EditOrder $editOrder, MerchantImport $merchantImport, EditRating $editRating, EditBooking $editBooking) {
        $this->editOrder = $editOrder;
        $this->merchantImport = $merchantImport;
        $this->editRating = $editRating;
        $this->editBooking = $editBooking;
        /* $this->middleware('location.group', ['only' => 'postGroupLocation']);
          $this->middleware('location.group', ['only' => 'getGroupLocation']); */
    }

    public function run() {
//        $this->cleanPhones();
        //$this->createConditions();
//        $this->createProducts();
        $this->createExcel();
        //$this->createMerchants();
    }

    public function createProducts() {
        for ($x = 0; $x <= 4; $x++) {
            $category1 = Category::create([
                        'name' => "Cat" . $x,
                        'level' => "1",
                        'description' => "Cat desc " . $x,
            ]);
            for ($y = 0; $y <= 4; $y++) {
                $product1 = new Product([
                    'name' => "plan " . $x . "-" . $y,
                    'description' => " plan de seguridad " . $x . "-" . $y,
                    'hash' => "plan-de-seguridad-" . $x . "-" . $y,
                    'isActive' => true,
                ]);
                $category1->products()->save($product1);
                $product1->save();
                if ($y % 2 == 0) {
                    $itemCondition = new Condition(array(
                        'name' => "Product percent discount " . $y,
                        'type' => 'sale',
                        'target' => 'item',
                        'value' => '-' . ($y + 1) . "%",
                        'isActive' => true
                    ));
                    $product1->conditions()->save($itemCondition);
                }
                for ($z = 0; $z <= 4; $z++) {
                    $attributes = ["size" => "tamano-" . $z, "color" => "color-" . $z];
                    $productVariant1 = new ProductVariant([
                        'isActive' => true,
                        'sku' => "sku-" . $x . "-" . $y . "-" . $z,
                        'ref2' => "ref-" . $x . "-" . $y . "-" . $z,
                        'sale' => 800 * ($z + 1) * ($y + 1),
                        'price' => 1000 * ($z + 1) * ($y + 1),
                        'quantity' => 1 * ($z + 1) * ($y + 1),
                        'attributes' => json_encode($attributes)
                    ]);
                    $product1->productVariants()->save($productVariant1);
                    $productVariant1->save();
                    if ($z % 2 == 0) {
                        $amount = ($y + 1) * ($z + 1);
                        $itemCondition = new Condition(array(
                            'name' => "Product variant percent discount " . $y . "-" . $z,
                            'type' => 'sale',
                            'target' => 'item',
                            'value' => '-' . $amount . "%",
                            'isActive' => true
                        ));
                        $productVariant1->conditions()->save($itemCondition);
                    }
                }
            }
        }
    }

    public function createOrders() {
        $users = User::all();

        foreach ($users as $user) {
            $merchants = Merchant::all();
            foreach ($merchants as $merchant) {
                foreach ($merchant->products as $product) {
                    $data["product_id"] = $product->id;
                    $data["quantity"] = 2;
                    $this->editOrder->addCartItem($user, $data);
                }
                $address = $user->addresses->first();
                $data["address_id"] = $address->id;
                $this->editOrder->setShippingAddress($user, $data);


                $pmethod = $merchant->paymentMethods->first();
                $data["payment_method_id"] = $pmethod->id;
                $data["comments"] = "No comment";
                $data["cash_for_change"] = "50";
                $this->editOrder->setOrderDetails($user, $data);
            }
        }
    }

    public function cleanPhones() {
        $merchants = Merchant::all();
        foreach ($merchants as $merchant) {
            $phone = $merchant->telephone;

            $phone = str_replace("Fijo:", "+571", $phone);



            $merchant->telephone = $phone;
            $merchant->save();
        }
    }

    public function createConditions() {
        $paymentMethod = PaymentMethod::create([
                    'id' => '1',
                    'name' => "Efectivo",
        ]);
        $paymentMethod2 = PaymentMethod::create([
                    'id' => '2',
                    'name' => "Sodexo",
        ]);
        $paymentMethod3 = PaymentMethod::create([
                    'id' => '3',
                    'name' => "Credito",
        ]);
        $paymentMethod4 = PaymentMethod::create([
                    'id' => '4',
                    'name' => "Debito",
        ]);
        /* $itemCondition = Condition::create(array(
          'name' => "Iva",
          'type' => 'tax',
          'target' => 'subtotal',
          'value' => '+16%',
          'country_id' => 1,
          'isActive' => true
          ));
          $itemCondition = Condition::create(array(
          'name' => "Envios",
          'type' => 'shipping',
          'target' => 'subtotal',
          'value' => '+5000',
          'isActive' => true,
          'country_id' => 1
          ));
          $itemCondition = Condition::create(array(
          'name' => "Dia de la madre",
          'type' => 'sale',
          'target' => 'subtotal',
          'value' => '-20%',
          'isActive' => true
          ));
          $itemCondition = Condition::create(array(
          'name' => "Cupon",
          'type' => 'coupon',
          'coupon' => 'trevooh',
          'target' => 'subtotal',
          'value' => '-10%',
          'isActive' => true
          )); */
    }

    public function createMerchant($category, $num, $categoryProds) {
        $userId = mt_rand(1, 2);
        $bookerId = 1;
        if ($userId == 1) {
            $bookerId = 2;
        } else {
            $bookerId = 1;
        }
        $booker = User::find($bookerId);
        $owner = User::find($userId);
        $merchant = Merchant::create(array(
                    'name' => "Dr " . $category->name . " " . $num,
                    'type' => 'medical',
                    'email' => 'hoov@hoov.com',
                    'telephone' => '3152562356',
                    'url' => "http://hoovert.com",
                    'address' => "cra 1 # 45-56",
                    'description' => "El mejor " . $category->name . " " . $num,
                    'icon' => 'https://s3.us-east-2.amazonaws.com/gohife/public/product/catering-eventos.jpg',
                    'lat' => 4.656060000,
                    'long' => -74.045932000,
                    'price' => 40000,
                    'unit_cost' => 30000,
                    'base_cost' => 5000,
                    'unit' => "hour",
                    'user_id' => $owner->id,
                    'currency' => "COP",
                    'status' => "active",
                    'attributes' => [
                        "experience" => [["name" => "St judes hospital", "years" => "1.5"]],
                        "services" => [["name" => $category->name . "1", "icon" => "1.5"], ["name" => $category->name . "2", "icon" => "1.5"], ["name" => $category->name . "3", "icon" => "1.5"]],
                        "booking_requires_authorization" => mt_rand(0, 1),
                        "max_per_hour" => 2
                    ]
        ));
        CoveragePolygon::create(array(
            'coverage' => '[{"lat":4.723382111533708,"lng":-74.04241729687499},{"lat":4.729223885835614,"lng":-74.06611658606096},{"lat":4.7049518372167185,"lng":-74.08981691503908},{"lat":4.668639281672278,"lng":-74.11485624947431},{"lat":4.641907363562615,"lng":-74.07912789819335},{"lat":4.636631621857092,"lng":-74.05114528732781},{"lat":4.646754460045917,"lng":-74.04994175097659},{"lat":4.671210887736071,"lng":-74.04257490395742},{"lat":4.685316127393699,"lng":-74.03228927563475},{"lat":4.699259639444434,"lng":-74.0269677729492},{"lat":4.711406520789634,"lng":-74.0269677729492},{"lat":4.723382111533708,"lng":-74.04241729687499},{"lat":4.723382111533708,"lng":-74.04241729687499},{"lat":4.723382111533708,"lng":-74.04241729687499}]',
            'lat' => 4.721717000,
            'long' => -74.069855000,
            'country_id' => 1,
            'region_id' => 11,
            'city_id' => 524,
            'merchant_id' => $merchant->id,
            'address_id' => 14,
            'provider' => 'Basilikum',
        ));
        $merchant->categories()->save($category);
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "range" => 'monday',
            "from" => '08:00 am',
            "to" => '12:30 pm'
        ];
        $this->editBooking->addAvailabilityObject($data, $owner);
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "range" => 'monday',
            "from" => '02:00 pm',
            "to" => '06:00 pm'
        ];
        $this->editBooking->addAvailabilityObject($data, $owner);
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "range" => 'tuesday',
            "from" => '08:00 am',
            "to" => '12:30 pm'
        ];
        $this->editBooking->addAvailabilityObject($data, $owner);

        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "range" => 'tuesday',
            "from" => '02:00 pm',
            "to" => '06:00 pm'
        ];
        $this->editBooking->addAvailabilityObject($data, $owner);
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "range" => 'wednesday',
            "from" => '08:00 am',
            "to" => '12:30 pm'
        ];
        $this->editBooking->addAvailabilityObject($data, $owner);
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "range" => 'wednesday',
            "from" => '02:00 pm',
            "to" => '06:00 pm'
        ];
        $this->editBooking->addAvailabilityObject($data, $owner);
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "range" => 'thursday',
            "from" => '08:00 am',
            "to" => '12:30 pm'
        ];
        $this->editBooking->addAvailabilityObject($data, $owner);
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "range" => 'thursday',
            "from" => '02:00 pm',
            "to" => '06:00 pm'
        ];
        $this->editBooking->addAvailabilityObject($data, $owner);
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "range" => 'friday',
            "from" => '08:00 am',
            "to" => '12:30 pm'
        ];
        $this->editBooking->addAvailabilityObject($data, $owner);
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "range" => 'friday',
            "from" => '02:00 pm',
            "to" => '06:00 pm'
        ];
        $this->editBooking->addAvailabilityObject($data, $owner);
        $date = date_create();
        $dayofweek = date('w', strtotime(date_format($date, "Y-m-d H:i:s")));
        date_add($date, date_interval_create_from_date_string("1 days"));
        $booker2 = User::find(3);
        $booker3 = User::find(6);
        $attributes = [
            "pet" => "cat",
            "weight" => "200lb"
        ];
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "from" => date_format($date, "Y-m-d") . ' 08:00:00',
            "to" => date_format($date, "Y-m-d") . ' 09:00:00',
            "attributes" => $attributes
        ];
        $result1 = $this->editBooking->addBookingObject($data, $booker);
        $result1 = $result1->original;

        if ($result1['status'] == "success") {
            $booking = $result1['booking'];
            $status = [
                "status" => "approved",
                "booking_id" => $booking->id
            ];
            echo "Paid" . $booking->total_paid . PHP_EOL;
            if ($booking->total_paid == -1) {
                $this->editBooking->changeStatusBookingObject($status, $owner);
            }
        }
        $updateData = [
            "total_paid" => $booking->price,
            "updated_at" => date("Y-m-d hh:m:s")
        ];
        Booking::where("id", $booking->id)->update($updateData);
        $array1 = $merchant->bookingsStartsBetween($data['from'], $data['to'])->whereColumn("price", "total_paid")->get();
        //dd($array1);

        $result1 = $this->editBooking->addBookingObject($data, $booker2);
        $result1 = $result1->original;

        if ($result1['status'] == "success") {
            $booking = $result1['booking'];
            $status = [
                "status" => "approved",
                "booking_id" => $booking->id
            ];
            echo "Paid" . $booking->total_paid . PHP_EOL;
            if ($booking->total_paid == -1) {
                $this->editBooking->changeStatusBookingObject($status, $owner);
            }
        }
        $updateData = [
            "total_paid" => $booking->price,
            "updated_at" => date("Y-m-d h:m:s")
        ];
        Booking::where("id", $booking->id)->update($updateData);
        $array1 = $merchant->bookingsStartsBetween($data['from'], $data['to'])->whereColumn("price", "total_paid")->get();
        $result1 = $this->editBooking->addBookingObject($data, $booker3);
        $result1 = $result1->original;

        if ($result1['status'] == "success") {
            $booking = $result1['booking'];
            $status = [
                "status" => "approved",
                "booking_id" => $booking->id
            ];
            echo "Paid" . $booking->total_paid . PHP_EOL;
            if ($booking->total_paid == -1) {
                $this->editBooking->changeStatusBookingObject($status, $owner);
            }
        }
        //$this->editBooking->deleteBooking($booker, $booking->id);
        date_add($date, date_interval_create_from_date_string("1 days"));
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "from" => date_format($date, "Y-m-d") . ' 08:00:00',
            "to" => date_format($date, "Y-m-d") . ' 09:00:00',
            "attributes" => $attributes
        ];
        $result1 = $this->editBooking->addBookingObject($data, $booker);
        $result1 = $result1->original;
        if ($result1['status'] == "success") {
            $booking = $result1['booking'];
            $status = [
                "status" => "approved",
                "booking_id" => $booking->id
            ];
            echo "Paid" . $booking->total_paid . PHP_EOL;
            if ($booking->total_paid == -1) {
                $this->editBooking->changeStatusBookingObject($status, $owner);
            }
        }
        $updateData = [
            "total_paid" => $booking->price,
            "updated_at" => date("Y-m-d h:m:s")
        ];
        Booking::where("id", $booking->id)->update($updateData);
        $result1 = $this->editBooking->addBookingObject($data, $booker2);
        $result1 = $result1->original;
        if ($result1['status'] == "success") {
            $booking = $result1['booking'];
            $status = [
                "status" => "approved",
                "booking_id" => $booking->id
            ];
            echo "Paid" . $booking->total_paid . PHP_EOL;
            if ($booking->total_paid == -1) {
                $this->editBooking->changeStatusBookingObject($status, $owner);
            }
        }
        $updateData = [
            "total_paid" => $booking->price,
            "updated_at" => date("Y-m-d h:m:s")
        ];
        Booking::where("id", $booking->id)->update($updateData);
        $result1 = $this->editBooking->addBookingObject($data, $booker3);
        $result1 = $result1->original;
        if ($result1['status'] == "success") {
            $booking = $result1['booking'];
            $status = [
                "status" => "approved",
                "booking_id" => $booking->id
            ];
            echo "Paid" . $booking->total_paid . PHP_EOL;
            if ($booking->total_paid == -1) {
                $this->editBooking->changeStatusBookingObject($status, $owner);
            }
        }
        $updateData = [
            "total_paid" => $booking->price,
            "updated_at" => date("Y-m-d h:m:s")
        ];
        Booking::where("id", $booking->id)->update($updateData);
        //$this->editBooking->deleteBooking($booker, $booking->id);
        date_add($date, date_interval_create_from_date_string("1 days"));
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "from" => date_format($date, "Y-m-d") . ' 08:00:00',
            "to" => date_format($date, "Y-m-d") . ' 09:00:00',
            "attributes" => $attributes
        ];
        $result1 = $this->editBooking->addBookingObject($data, $booker);
        $result1 = $result1->original;
        if ($result1['status'] == "success") {
            $booking = $result1['booking'];
            $status = [
                "status" => "denied",
                "booking_id" => $booking->id,
                "reason" => "No me queda bien esa hora"
            ];
            echo "Paid" . $booking->total_paid . PHP_EOL;
            if ($booking->total_paid == -1) {
                $this->editBooking->changeStatusBookingObject($status, $owner);
            }
        }
        $result1 = $this->editBooking->addBookingObject($data, $booker2);
        $result1 = $result1->original;
        if ($result1['status'] == "success") {
            $booking = $result1['booking'];
            $status = [
                "status" => "denied",
                "booking_id" => $booking->id,
                "reason" => "No me queda bien esa hora"
            ];
            echo "Paid" . $booking->total_paid . PHP_EOL;
            if ($booking->total_paid == -1) {
                $this->editBooking->changeStatusBookingObject($status, $owner);
            }
        }
        $result1 = $this->editBooking->addBookingObject($data, $booker3);
        $result1 = $result1->original;
        if ($result1['status'] == "success") {
            $booking = $result1['booking'];
            $status = [
                "status" => "denied",
                "booking_id" => $booking->id,
                "reason" => "No me queda bien esa hora"
            ];
            echo "Paid" . $booking->total_paid . PHP_EOL;
            if ($booking->total_paid == -1) {
                $this->editBooking->changeStatusBookingObject($status, $owner);
            }
        }
        //$this->editBooking->deleteBooking($booker, $booking->id);
        for ($i = 1; $i < 4; $i++) {
            $product = Product::create([
                        'name' => "Product " . $i,
                        'description' => "Description " . $i,
                        'isActive' => true,
                        'hash' => "",
            ]);
            for ($j = 1; $j < 4; $j++) {
                $productVariant = ProductVariant::updateOrCreate([
                            'product_id' => $product->id,
                            'merchant_id' => $merchant->id,
                            'sku' => "prod" . $i . "-" . $j,
                            'ref2' => "prod2" . $i . "-" . $j,
                            'isActive' => true,
                            'price' => 10 * $i * $j,
                            'sale' => 9 * $i * $j,
                            'tax' => $i * $j,
                            'cost' => 8 * $i * $j,
                            'quantity' => 10,
                            'min_quantity' => 1,
                            'is_digital' => true,
                            'is_shippable' => true,
                                //'attributes' => $sheet['attributes'],
                ]);
            }
            $merchant->products()->save($product);
            $categoryProds->products()->save($product);
        }
        for ($i = 1; $i < 4; $i++) {
            $user = User::whereNotIn("id", [$owner->id])->first();
            $data = [
                "type" => "Merchant",
                "object_id" => $merchant->id,
                "rating" => $i + 2,
                "comment" => "Comment " . $i
            ];
            $this->editRating->addRatingObject($data, $user);
        }
    }

    public function createExcel() {
        
//        $merchant = Merchant::find(1302);
//        DB::enableQueryLog();
//        $array1 = $merchant->bookingsStartsBetween('2019-10-13 07:59:59', '2019-10-13 09:00:00')->get();
//        dd($array1->toArray());
        $dentists = Category::create(array(
                    'name' => "Dentists",
                    'type' => 'merchants',
                    'level' => '1',
                    'description' => 'tend to peoples teeth',
        ));
        $store = Category::create(array(
                    'name' => "Store",
                    'type' => 'products',
                    'level' => '1',
                    'description' => 'tend to peoples teeth',
        ));
        $this->createMerchant($dentists, 1, $store);
        $this->createMerchant($dentists, 2, $store);
        $this->createMerchant($dentists, 3, $store);
        $dermatologist = Category::create(array(
                    'name' => "Dermatologists",
                    'type' => 'merchants',
                    'level' => '1',
                    'description' => 'tend to peoples skin',
        ));
        $this->createMerchant($dermatologist, 1, $store);
        $this->createMerchant($dermatologist, 2, $store);
        $this->createMerchant($dermatologist, 3, $store);
        $oftalmologists = Category::create(array(
                    'name' => "Oftalmologists",
                    'type' => 'merchants',
                    'level' => '1',
                    'description' => 'tend to peoples eyes',
        ));
        $this->createMerchant($oftalmologists, 1, $store);
        $this->createMerchant($oftalmologists, 2, $store);
        $this->createMerchant($oftalmologists, 3, $store);
    }

    public function createMerchants() {
        //$this->merchantImport->exportMerchantJson("/home/hoovert/hospitales.json");

        $this->merchantImport->importPlans("plans.xlsx");
        $this->command->info('plans seeded!');

        $this->merchantImport->importAttributes("attributes.xlsx");
        $this->merchantImport->importAttributeOptions("attributeOptions.xlsx");
        $this->command->info('Attributes seeded!');
        $this->merchantImport->importProducts("products.xlsx");
        $this->merchantImport->importProductVariants("productvariants.xlsx");
        $this->merchantImport->importProductAttributeOptions("productvariantsattributes.xlsx");
        $this->command->info('Products seeded!');
        $this->merchantImport->importFollowers("followers.xlsx");
        $this->command->info('Followers seeded!');
        $this->merchantImport->importConditions("conditions.xlsx");
        $this->command->info('Conditions seeded!');
        $this->merchantImport->importLocations("locations.xlsx", 1);
        $this->merchantImport->importLocations("locations2.xlsx", 2);
        $this->merchantImport->importLocations("locations3.xlsx", 3);
        $this->merchantImport->importLocations("locations4.xlsx", 4);
        $this->command->info('Locations seeded!');
        sleep(1);
        $this->merchantImport->importFollowers("followers.xlsx");
        $this->command->info('Followers seeded!');
        $this->merchantImport->importLocations("locations.xlsx", 1);
        $this->merchantImport->importLocations("locations2.xlsx", 2);
        $this->merchantImport->importLocations("locations3.xlsx", 3);
        $this->merchantImport->importLocations("locations4.xlsx", 4);
        $this->command->info('Locations seeded!');

        $this->merchantImport->importGroups("groups.xlsx");
        $this->command->info('Groups seeded!');

        $this->merchantImport->inviteGroups("invitegroup.xlsx");
        $this->command->info('Groups shareLocationGroup!');
        $this->merchantImport->importLocations("locations.xlsx", 1);
        $this->merchantImport->importLocations("locations2.xlsx", 2);
        $this->merchantImport->importLocations("locations3.xlsx", 3);
        $this->merchantImport->importLocations("locations4.xlsx", 4);
        $this->merchantImport->importLocations("locations.xlsx", 5);
        $this->merchantImport->importLocations("locations2.xlsx", 6);
        $this->merchantImport->importLocations("locations3.xlsx", 7);
        $this->merchantImport->importLocations("locations4.xlsx", 8);
        $this->merchantImport->importLocations("locations.xlsx", 9);
        $this->merchantImport->importLocations("locations2.xlsx", 10);
        $this->merchantImport->importLocations("locations3.xlsx", 11);
        $this->merchantImport->importLocations("locations4.xlsx", 12);
        $this->merchantImport->importLocations("locations.xlsx", 13);
        $this->merchantImport->importLocations("locations2.xlsx", 14);
        $this->merchantImport->importLocations("locations3.xlsx", 15);
        $this->merchantImport->importLocations("locations4.xlsx", 16);
        $this->merchantImport->importLocations("locations.xlsx", 17);
        $this->merchantImport->importLocations("locations2.xlsx", 18);
        $this->merchantImport->importLocations("locations3.xlsx", 19);
        $this->merchantImport->importLocations("locations4.xlsx", 20);
        $this->merchantImport->importLocations("locations4.xlsx", 21);
        $this->merchantImport->importLocations("locations4.xlsx", 22);
        $this->merchantImport->importLocations("locations4.xlsx", 23);
        $this->merchantImport->importLocations("locations4.xlsx", 24);
        $this->command->info('Groups Locations finished!');
        $this->merchantImport->sendMessageGroup("messageGroup.xlsx");
        $this->command->info('Group messages sent');

        $this->merchantImport->importMerchants("medicos1.xlsx");
        $this->command->info('medicos1.xlsx seeded!');
        $this->merchantImport->importMerchants("medicos2.xlsx");
        $this->command->info('medicos2.xlsx seeded!');
        $this->merchantImport->importMerchants("policias.xlsx");
        $this->command->info('policias.xlsx seeded!');
        $this->merchantImport->importReports("reports.xlsx");
        $this->command->info('reports.xlsx seeded!');
    }

}
