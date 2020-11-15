<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Product;
use App\Models\Item;
use App\Models\ProductVariant;
use App\Models\Merchant;
use App\Services\EditOrder;
use App\Services\EditRating;
use App\Services\EditBooking;
use App\Services\MerchantImport;
use App\Services\MiPaquete;
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
    protected $miPaquete;

    /**
     * The edit profile implementation.
     *
     */
    protected $merchantImport;

    public function __construct(EditOrder $editOrder, MerchantImport $merchantImport, EditRating $editRating, EditBooking $editBooking, MiPaquete $miPaquete) {
        $this->editOrder = $editOrder;
        $this->merchantImport = $merchantImport;
        $this->editRating = $editRating;
        $this->editBooking = $editBooking;
        $this->miPaquete = $miPaquete;
        /* $this->middleware('location.group', ['only' => 'postGroupLocation']);
          $this->middleware('location.group', ['only' => 'getGroupLocation']); */
    }

    public function run() {
//        $this->cleanPhones();
        //$this->createConditions();
//        $this->createProducts();
        $this->createMerchantsExcel();
        //$this->createMerchants();
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

    public function addBooking($data, $booker, $owner, $merchant) {
        $result1 = $this->editBooking->addBookingObject($data, $booker);
        //$result1 = $result1->original;
        if ($result1['status'] == "success") {
            $booking = $result1['booking'];
            $status = [
                "status" => "approved",
                "booking_id" => $booking->id
            ];
            echo "Paid" . $booking->total_paid . PHP_EOL;
            if ($booking->notes == 'in_confirmation') {
                $this->editBooking->changeStatusBookingObject($status, $owner);
            }
            $shouldPay = mt_rand(0, 1);
            if ($shouldPay) {
                $updateData = [
                    "total_paid" => $booking->price,
                    "updated_at" => date("Y-m-d hh:m:s")
                ];
                Booking::where("id", $booking->id)->update($updateData);
//                $itemData = [
//                    "name" => "Reserva para " . $merchant->name,
//                    "price" => $booking->price,
//                    "priceSum" => $booking->price,
//                    "priceConditions" => $booking->price,
//                    "priceSumConditions" => $booking->price,
//                    "quantity" => $booking->quantity,
//                    "paid_status" => "paid",
//                    "fulfillment" => "unfulfilled",
//                    "requires_authorization" => 0,
//                    "user_id" => $booker->id,
//                    "merchant_id" => $merchant->id,
//                    "created_at" => date("Y-m-d H:i:s"),
//                    "updated_at" => date("Y-m-d H:i:s")
//                ];
//                Item::create($itemData);
            }
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

    public function createMerchantBookings() {
        $userId = mt_rand(1, 2);
        $bookerId = 1;
        $merchant_id = 0;
        if ($userId == 1) {
            $bookerId = 2;
            $merchant_id = mt_rand(1, 3);
        } else {
            $bookerId = 1;
            $merchant_id = mt_rand(4, 6);
        }
        $merchant = Merchant::find($merchant_id);
        $booker = User::find($bookerId);
        $owner = User::find($userId);

        $date = date_create();
        date_add($date, date_interval_create_from_date_string("1 days"));
        $booker2 = User::find(3);
        $attributes = [
            "pet" => "cat",
            "weight" => "200lb",
            "location" => "zoom"
        ];
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "from" => date_format($date, "Y-m-d") . ' 08:00:00',
            "to" => date_format($date, "Y-m-d") . ' 09:00:00',
            "attributes" => $attributes
        ];
        $this->addBooking($data, $booker, $owner, $merchant);
        $this->addBooking($data, $booker2, $owner, $merchant);

        date_add($date, date_interval_create_from_date_string("1 days"));
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "from" => date_format($date, "Y-m-d") . ' 08:00:00',
            "to" => date_format($date, "Y-m-d") . ' 09:00:00',
            "attributes" => $attributes
        ];
        $this->addBooking($data, $booker, $owner, $merchant);
        $this->addBooking($data, $booker2, $owner, $merchant);
        date_add($date, date_interval_create_from_date_string("1 days"));
        $data = [
            "type" => "Merchant",
            "object_id" => $merchant->id,
            "from" => date_format($date, "Y-m-d") . ' 08:00:00',
            "to" => date_format($date, "Y-m-d") . ' 09:00:00',
            "attributes" => $attributes
        ];
        $this->addBooking($data, $booker, $owner, $merchant);
        $this->addBooking($data, $booker2, $owner, $merchant);
    }

    public function createMerchantsExcel() {
        $this->miPaquete->authenticate("https://ecommerce.mipaquete.com/api/auth");
        $this->miPaquete->getCitiesAndRegions();
        //$this->merchantImport->exportMerchantJson("/home/hoovert/hospitales.json");
        $this->merchantImport->importGlobalExcel("Global3.xlsx");
//        return true;
//        $this->merchantImport->importMerchantsExcel("merchants.xlsx");
//        $this->command->info('merchants seeded!');
//        $this->merchantImport->importReportsExcel("reports.xlsx");
//        $this->command->info('merchants seeded!');
//
//        $this->merchantImport->importPolygons("polygons.xlsx");
//        $this->command->info('polygons seeded!');
//        $this->merchantImport->importProductsExcel("products.xlsx");
//        $this->command->info('products seeded!');
//        $this->merchantImport->importProductVariantsExcel("variants.xlsx");
//        $this->command->info('variants seeded!');
//        $this->merchantImport->importMerchantsAvailabilitiesExcel("availabilities.xlsx");
//        $this->command->info('availabilities seeded!');
        $this->createMerchantBookings();
        $this->createMerchantBookings();
        $this->command->info('Bookings created!');
        $this->merchantImport->importArticlesExcel("articles.xlsx");
        $this->command->info('articles seeded!');
//        $this->merchantImport->importMerchantsRatingsExcel("ratings.xlsx");
//        $this->command->info('ratings seeded!');
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
