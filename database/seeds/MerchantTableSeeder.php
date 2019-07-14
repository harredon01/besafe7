<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Merchant;
use App\Services\EditOrder;
use App\Services\MerchantImport;
use App\Models\OfficeHour;
use App\Models\PaymentMethod;

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
    protected $merchantImport;

    public function __construct(EditOrder $editOrder, MerchantImport $merchantImport) {
        $this->editOrder = $editOrder;
        $this->merchantImport = $merchantImport;
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
                
                $phone = str_replace("Fijo:","+571",$phone);
                
                
                
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
        /*$itemCondition = Condition::create(array(
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
        ));*/
    }

    public function createExcel() {
        $dentists = Category::create(array(
                    'name' => "Dentists",
                    'type' => 'merchants',
                    'level' => '1', 
                    'description' => 'tend to peoples teeth',
        ));
        $dermatologist = Category::create(array(
                    'name' => "Dermatologists",
                    'type' => 'merchants',
                    'level' => '1',
                    'description' => 'tend to peoples skin',
        ));
        $oftalmologists = Category::create(array(
                    'name' => "Oftalmologists",
                    'type' => 'merchants',
                    'level' => '1',
                    'description' => 'tend to peoples eyes',
        ));
        $drdentist = Merchant::create(array(
                    'name' => "Dr dentist",
                    'type' => 'medical',
                    'email' => 'hoov@hoov.com',
                    'telephone' => '3152562356',
                    'url' => "http://hoovert.com",
                    'address' => "cra 1 # 45-56",
                    'description' => "El mejor dentista",
                    'icon' => 'https://s3.us-east-2.amazonaws.com/gohife/public/product/catering-eventos.jpg',
                    'lat' => 4.656060000,
                    'long' => -74.045932000,
                    'price' => 40000,
                    'unit_cost' => 30000,
                    'base_cost' => 5000,
                    'unit' => "hour",
                    'status' => "active"
        ));
        $drdentist->categories()->save($dentists);
        $drdentist->newAvailability('mon', '08:00 am', '12:30 pm');
        $drdentist->newAvailability('mon', '02:00 pm', '06:00 pm');
        $drdentist->newAvailability('tue', '08:00 am', '12:30 pm');
        $drdentist->newAvailability('tue', '02:00 pm', '06:00 pm');
        $drdentist->newAvailability('wed', '08:00 am', '12:30 pm');
        $drdentist->newAvailability('wed', '02:00 pm', '06:00 pm');
        $drdentist->newAvailability('thu', '08:00 am', '12:30 pm');
        $drdentist->newAvailability('thu', '02:00 pm', '06:00 pm');
        $drdentist->newAvailability('fri', '08:00 am', '12:30 pm');
        $drdentist->newAvailability('fri', '02:00 pm', '06:00 pm');
        
        $drdentist2 = Merchant::create(array(
                    'name' => "Dr dentist2",
                    'type' => 'medical',
                    'email' => 'hoov@hoov.com',
                    'telephone' => '3152562356',
                    'url' => "http://hoovert.com",
                    'address' => "cra 1 # 45-56",
                    'description' => "El mejor dentista",
                    'icon' => 'https://s3.us-east-2.amazonaws.com/gohife/public/product/catering-eventos.jpg',
                    'lat' => 4.656060000,
                    'long' => -74.045932000,
                    'price' => 40000,
                    'unit_cost' => 30000,
                    'base_cost' => 5000,
                    'unit' => "hour",
                    'status' => "active"
        ));
        $drdentist2->categories()->save($dentists);
        $drdentist2->newAvailability('mon', '08:00 am', '12:30 pm');
        $drdentist2->newAvailability('mon', '02:00 pm', '06:00 pm');
        $drdentist2->newAvailability('tue', '08:00 am', '12:30 pm');
        $drdentist2->newAvailability('tue', '02:00 pm', '06:00 pm');
        $drdentist2->newAvailability('wed', '08:00 am', '12:30 pm');
        $drdentist2->newAvailability('wed', '02:00 pm', '06:00 pm');
        $drdentist2->newAvailability('thu', '08:00 am', '12:30 pm');
        $drdentist2->newAvailability('thu', '02:00 pm', '06:00 pm');
        $drdentist2->newAvailability('fri', '08:00 am', '12:30 pm');
        $drdentist2->newAvailability('fri', '02:00 pm', '06:00 pm');
        
        $drdentist3 = Merchant::create(array(
                    'name' => "Dr dentist3",
                    'type' => 'medical',
                    'email' => 'hoov@hoov.com',
                    'telephone' => '3152562356',
                    'url' => "http://hoovert.com",
                    'address' => "cra 1 # 45-56",
                    'description' => "El mejor dentista",
                    'icon' => 'https://s3.us-east-2.amazonaws.com/gohife/public/product/catering-eventos.jpg',
                    'lat' => 4.656060000,
                    'long' => -74.045932000,
                    'price' => 40000,
                    'unit_cost' => 30000,
                    'base_cost' => 5000,
                    'unit' => "hour",
                    'status' => "active"
        ));
        $drdentist3->categories()->save($dentists);
        $drdentist3->newAvailability('mon', '08:00 am', '12:30 pm');
        $drdentist3->newAvailability('mon', '02:00 pm', '06:00 pm');
        $drdentist3->newAvailability('tue', '08:00 am', '12:30 pm');
        $drdentist3->newAvailability('tue', '02:00 pm', '06:00 pm');
        $drdentist3->newAvailability('wed', '08:00 am', '12:30 pm');
        $drdentist3->newAvailability('wed', '02:00 pm', '06:00 pm');
        $drdentist3->newAvailability('thu', '08:00 am', '12:30 pm');
        $drdentist3->newAvailability('thu', '02:00 pm', '06:00 pm');
        $drdentist3->newAvailability('fri', '08:00 am', '12:30 pm');
        $drdentist3->newAvailability('fri', '02:00 pm', '06:00 pm');
        $drderma = Merchant::create(array(
                    'name' => "Dr derma",
                    'type' => 'medical',
                    'email' => 'hoov@hoov.com',
                    'telephone' => '3152562356',
                    'url' => "http://hoovert.com",
                    'address' => "cra 1 # 45-56",
                    'description' => "El mejor dentista",
                    'icon' => 'https://s3.us-east-2.amazonaws.com/gohife/public/product/catering-eventos.jpg',
                    'lat' => 4.656060000,
                    'long' => -74.045932000,
                    'price' => 40000,
                    'unit_cost' => 30000,
                    'base_cost' => 5000,
                    'unit' => "hour",
                    'status' => "active"
        ));
        $drderma->categories()->save($dermatologist);
        $drderma->newAvailability('mon', '08:00 am', '12:30 pm');
        $drderma->newAvailability('mon', '02:00 pm', '06:00 pm');
        $drderma->newAvailability('tue', '08:00 am', '12:30 pm');
        $drderma->newAvailability('tue', '02:00 pm', '06:00 pm');
        $drderma->newAvailability('wed', '08:00 am', '12:30 pm');
        $drderma->newAvailability('wed', '02:00 pm', '06:00 pm');
        $drderma->newAvailability('thu', '08:00 am', '12:30 pm');
        $drderma->newAvailability('thu', '02:00 pm', '06:00 pm');
        $drderma->newAvailability('fri', '08:00 am', '12:30 pm');
        $drderma->newAvailability('fri', '02:00 pm', '06:00 pm');
        $drderma2 = Merchant::create(array(
                    'name' => "Dr derma2",
                    'type' => 'medical',
                    'email' => 'hoov@hoov.com',
                    'telephone' => '3152562356',
                    'url' => "http://hoovert.com",
                    'address' => "cra 1 # 45-56",
                    'description' => "El mejor dentista",
                    'icon' => 'https://s3.us-east-2.amazonaws.com/gohife/public/product/catering-eventos.jpg',
                    'lat' => 4.656060000,
                    'long' => -74.045932000,
                    'price' => 40000,
                    'unit_cost' => 30000,
                    'base_cost' => 5000,
                    'unit' => "hour",
                    'status' => "active"
        ));
        $drderma2->categories()->save($dermatologist);
        $drderma2->newAvailability('mon', '08:00 am', '12:30 pm');
        $drderma2->newAvailability('mon', '02:00 pm', '06:00 pm');
        $drderma2->newAvailability('tue', '08:00 am', '12:30 pm');
        $drderma2->newAvailability('tue', '02:00 pm', '06:00 pm');
        $drderma2->newAvailability('wed', '08:00 am', '12:30 pm');
        $drderma2->newAvailability('wed', '02:00 pm', '06:00 pm');
        $drderma2->newAvailability('thu', '08:00 am', '12:30 pm');
        $drderma2->newAvailability('thu', '02:00 pm', '06:00 pm');
        $drderma2->newAvailability('fri', '08:00 am', '12:30 pm');
        $drderma2->newAvailability('fri', '02:00 pm', '06:00 pm');
        
        $drderma3 = Merchant::create(array(
                    'name' => "Dr derma3",
                    'type' => 'medical',
                    'email' => 'hoov@hoov.com',
                    'telephone' => '3152562356',
                    'url' => "http://hoovert.com",
                    'address' => "cra 1 # 45-56",
                    'description' => "El mejor dentista",
                    'icon' => 'https://s3.us-east-2.amazonaws.com/gohife/public/product/catering-eventos.jpg',
                    'lat' => 4.656060000,
                    'long' => -74.045932000,
                    'price' => 40000,
                    'unit_cost' => 30000,
                    'base_cost' => 5000,
                    'unit' => "hour",
                    'status' => "active"
        ));
        $drderma3->categories()->save($dermatologist);
        $drderma3->newAvailability('mon', '08:00 am', '12:30 pm');
        $drderma3->newAvailability('mon', '02:00 pm', '06:00 pm');
        $drderma3->newAvailability('tue', '08:00 am', '12:30 pm');
        $drderma3->newAvailability('tue', '02:00 pm', '06:00 pm');
        $drderma3->newAvailability('wed', '08:00 am', '12:30 pm');
        $drderma3->newAvailability('wed', '02:00 pm', '06:00 pm');
        $drderma3->newAvailability('thu', '08:00 am', '12:30 pm');
        $drderma3->newAvailability('thu', '02:00 pm', '06:00 pm');
        $drderma3->newAvailability('fri', '08:00 am', '12:30 pm');
        $drderma3->newAvailability('fri', '02:00 pm', '06:00 pm');
        
        $drofta = Merchant::create(array(
                    'name' => "Dr ofta",
                    'type' => 'medical',
                    'email' => 'hoov@hoov.com',
                    'telephone' => '3152562356',
                    'url' => "http://hoovert.com",
                    'address' => "cra 1 # 45-56",
                    'description' => "El mejor dentista",
                    'icon' => 'https://s3.us-east-2.amazonaws.com/gohife/public/product/catering-eventos.jpg',
                    'lat' => 4.656060000,
                    'long' => -74.045932000,
                    'price' => 40000,
                    'unit_cost' => 30000,
                    'base_cost' => 5000,
                    'unit' => "hour",
                    'status' => "active"
        ));
        $drofta->categories()->save($oftalmologists);
        $drofta->newAvailability('mon', '08:00 am', '12:30 pm');
        $drofta->newAvailability('mon', '02:00 pm', '06:00 pm');
        $drofta->newAvailability('tue', '08:00 am', '12:30 pm');
        $drofta->newAvailability('tue', '02:00 pm', '06:00 pm');
        $drofta->newAvailability('wed', '08:00 am', '12:30 pm');
        $drofta->newAvailability('wed', '02:00 pm', '06:00 pm');
        $drofta->newAvailability('thu', '08:00 am', '12:30 pm');
        $drofta->newAvailability('thu', '02:00 pm', '06:00 pm');
        $drofta->newAvailability('fri', '08:00 am', '12:30 pm');
        $drofta->newAvailability('fri', '02:00 pm', '06:00 pm');
        $drofta2 = Merchant::create(array(
                    'name' => "Dr ofta2",
                    'type' => 'medical',
                    'email' => 'hoov@hoov.com',
                    'telephone' => '3152562356',
                    'url' => "http://hoovert.com",
                    'address' => "cra 1 # 45-56",
                    'description' => "El mejor dentista",
                    'icon' => 'https://s3.us-east-2.amazonaws.com/gohife/public/product/catering-eventos.jpg',
                    'lat' => 4.656060000,
                    'long' => -74.045932000,
                    'price' => 40000,
                    'unit_cost' => 30000,
                    'base_cost' => 5000,
                    'unit' => "hour",
                    'status' => "active"
        ));
        $drofta2->categories()->save($oftalmologists);
        $drofta2->newAvailability('mon', '08:00 am', '12:30 pm');
        $drofta2->newAvailability('mon', '02:00 pm', '06:00 pm');
        $drofta2->newAvailability('tue', '08:00 am', '12:30 pm');
        $drofta2->newAvailability('tue', '02:00 pm', '06:00 pm');
        $drofta2->newAvailability('wed', '08:00 am', '12:30 pm');
        $drofta2->newAvailability('wed', '02:00 pm', '06:00 pm');
        $drofta2->newAvailability('thu', '08:00 am', '12:30 pm');
        $drofta2->newAvailability('thu', '02:00 pm', '06:00 pm');
        $drofta2->newAvailability('fri', '08:00 am', '12:30 pm');
        $drofta2->newAvailability('fri', '02:00 pm', '06:00 pm');
        $drofta3 = Merchant::create(array(
                    'name' => "Dr ofta3",
                    'type' => 'medical',
                    'email' => 'hoov@hoov.com',
                    'telephone' => '3152562356',
                    'url' => "http://hoovert.com",
                    'address' => "cra 1 # 45-56",
                    'description' => "El mejor dentista",
                    'icon' => 'https://s3.us-east-2.amazonaws.com/gohife/public/product/catering-eventos.jpg',
                    'lat' => 4.656060000,
                    'long' => -74.045932000,
                    'price' => 40000,
                    'unit_cost' => 30000,
                    'base_cost' => 5000,
                    'unit' => "hour",
                    'status' => "active"
        ));
        $drofta3->categories()->save($oftalmologists);
        $drofta3->newAvailability('mon', '08:00 am', '12:30 pm');
        $drofta3->newAvailability('mon', '02:00 pm', '06:00 pm');
        $drofta3->newAvailability('tue', '08:00 am', '12:30 pm');
        $drofta3->newAvailability('tue', '02:00 pm', '06:00 pm');
        $drofta3->newAvailability('wed', '08:00 am', '12:30 pm');
        $drofta3->newAvailability('wed', '02:00 pm', '06:00 pm');
        $drofta3->newAvailability('thu', '08:00 am', '12:30 pm');
        $drofta3->newAvailability('thu', '02:00 pm', '06:00 pm');
        $drofta3->newAvailability('fri', '08:00 am', '12:30 pm');
        $drofta3->newAvailability('fri', '02:00 pm', '06:00 pm');
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
        $this->merchantImport->importLocations("locations.xlsx",1);
        $this->merchantImport->importLocations("locations2.xlsx",2);
        $this->merchantImport->importLocations("locations3.xlsx",3);
        $this->merchantImport->importLocations("locations4.xlsx",4);
        $this->command->info('Locations seeded!');
        sleep(1);
        $this->merchantImport->importFollowers("followers.xlsx");
        $this->command->info('Followers seeded!');
        $this->merchantImport->importLocations("locations.xlsx",1);
        $this->merchantImport->importLocations("locations2.xlsx",2);
        $this->merchantImport->importLocations("locations3.xlsx",3);
        $this->merchantImport->importLocations("locations4.xlsx",4);
        $this->command->info('Locations seeded!');
        
        $this->merchantImport->importGroups("groups.xlsx");
        $this->command->info('Groups seeded!');
        
        $this->merchantImport->inviteGroups("invitegroup.xlsx");
        $this->command->info('Groups shareLocationGroup!');
        $this->merchantImport->importLocations("locations.xlsx",1);
        $this->merchantImport->importLocations("locations2.xlsx",2);
        $this->merchantImport->importLocations("locations3.xlsx",3);
        $this->merchantImport->importLocations("locations4.xlsx",4);
        $this->merchantImport->importLocations("locations.xlsx",5);
        $this->merchantImport->importLocations("locations2.xlsx",6);
        $this->merchantImport->importLocations("locations3.xlsx",7);
        $this->merchantImport->importLocations("locations4.xlsx",8);
        $this->merchantImport->importLocations("locations.xlsx",9);
        $this->merchantImport->importLocations("locations2.xlsx",10);
        $this->merchantImport->importLocations("locations3.xlsx",11);
        $this->merchantImport->importLocations("locations4.xlsx",12);
        $this->merchantImport->importLocations("locations.xlsx",13);
        $this->merchantImport->importLocations("locations2.xlsx",14);
        $this->merchantImport->importLocations("locations3.xlsx",15);
        $this->merchantImport->importLocations("locations4.xlsx",16);
        $this->merchantImport->importLocations("locations.xlsx",17);
        $this->merchantImport->importLocations("locations2.xlsx",18);
        $this->merchantImport->importLocations("locations3.xlsx",19);
        $this->merchantImport->importLocations("locations4.xlsx",20);
        $this->merchantImport->importLocations("locations4.xlsx",21);
        $this->merchantImport->importLocations("locations4.xlsx",22);
        $this->merchantImport->importLocations("locations4.xlsx",23);
        $this->merchantImport->importLocations("locations4.xlsx",24);
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
