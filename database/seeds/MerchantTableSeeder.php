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
        $this->createConditions();
//        $this->createProducts();
//        $this->createExcel();
        $this->createMerchants();
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
                    'slug' => "plan-de-seguridad-" . $x . "-" . $y,
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
        
    }

    public function createMerchants() {
        //$this->merchantImport->exportMerchantJson("/home/hoovert/hospitales.json");
        
        $this->merchantImport->importMerchants("policias.xlsx");
        $this->merchantImport->importMerchants("medicos1.xlsx");
        $this->merchantImport->importMerchants("medicos2.xlsx");
        $this->command->info('Merchants seeded!');
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
        $this->merchantImport->importLocations("locations.xlsx");
        $this->merchantImport->importLocations("locations2.xlsx");
        $this->merchantImport->importLocations("locations3.xlsx");
        $this->merchantImport->importLocations("locations4.xlsx");
        $this->command->info('Locations seeded!');
        sleep(1);
        $this->merchantImport->importFollowers("followers.xlsx");
        $this->command->info('Followers seeded!');
        $this->merchantImport->importLocations("locations.xlsx");
        $this->merchantImport->importLocations("locations2.xlsx");
        $this->merchantImport->importLocations("locations3.xlsx");
        $this->merchantImport->importLocations("locations4.xlsx");
        $this->command->info('Locations seeded!');
        $this->merchantImport->importReports("reports.xlsx");
        $this->command->info('Reports seeded!');
    }

}
