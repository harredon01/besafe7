<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\Location;
use App\Services\EditGroup;
use App\Services\EditLocation;

class GroupTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */

    /**
     * The edit profile implementation.
     *
     */
    protected $editGroup;
    
    /**
     * The edit profile implementation.
     *
     */
    protected $editLocation;

    public function __construct(EditGroup $editGroup, EditLocation $editLocation) {
        $this->editGroup = $editGroup;
        $this->editLocation = $editLocation;
    }

    public function run() {
        DB::table('products')->delete();
        DB::table('categories')->delete();
        DB::table('office_hours')->delete();
        DB::table('merchant_payment_methods')->delete();
        DB::table('merchants')->delete();
        DB::table('payment_methods')->delete();
        
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
        $merchant1 = Merchant::create([
                    'name' => "Drogueria Hoov",
                    'email' => "harredon007@hotmail.com",
                    'telephone' => "321123321",
                    'address' => "Calle prueba carrera prueba",
                    'description' => "Drogueria",
                    'icon' => "default",
                    'lat' => 4.654141,
                    'long' => -74.053525,
                    'minimum' => 10000,
                    'delivery_time' => "15 a 20 mins",
                    'delivery_price' => 2000,
                    'status' => "active",
        ]);
        $dias = array(2, 3, 4, 5, 6, 7, 1);

        $category1 = Category::create([
                    'name' => "Cat1",
                    'level' => "1",
                    'description' => "Cat desc 1",
        ]);
        $merchant1->categories()->save($category1);

        $merchant1->paymentMethods()->save($paymentMethod);
        $merchant1->paymentMethods()->save($paymentMethod2);
        $product1 = new Product([
            'name' => "Droga 1",
            'description' => "Droga para tomar 1",
            'sku' => "SDFWERW2334D3",
            'ref2' => "SDFWEsdfgW2334Ddfg3",
            'price' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'quantity' => 20,
        ]);
        $category1->products()->save($product1);
        $merchant1->products()->save($product1);
        $product1->save();
        $product1 = new Product([
            'name' => "Droga 2",
            'description' => "Droga para tomar 2",
            'sku' => "SDFWERW2334D3",
            'ref2' => "SDFWEsdfgW2334Ddfg3",
            'price' => 20000,
            'tax' => 2000,
            'total' => 22000,
            'quantity' => 10,
        ]);
        $category1->products()->save($product1);
        $merchant1->products()->save($product1);
        $product1->save();
        $product1 = new Product([
            'name' => "Droga 3",
            'description' => "Droga para tomar 3",
            'sku' => "SDFWERW2334D3",
            'ref2' => "SDFWEsdfgW2334Ddfg3",
            'price' => 30000,
            'tax' => 3000,
            'total' => 33000,
            'quantity' => 30,
        ]);
        $merchant1->products()->save($product1);

        $category1 = Category::create([
                    'name' => "Cat2",
                    'level' => "1",
                    'description' => "Cat desc 2",
        ]);
        $merchant1->categories()->save($category1);
        $category1->products()->save($product1);
        $product1->save();
        $merchant1 = Merchant::create([
                    'name' => "Pasteleria Hoov",
                    'email' => "harredon01@gmail.com",
                    'telephone' => "321123321",
                    'address' => "Calle prueba carrera prueba",
                    'description' => "Pasteleria",
                    'icon' => "default",
                    'lat' => 4.651938,
                    'long' => -74.054834,
                    'minimum' => 10000,
                    'delivery_time' => "15 a 20 mins",
                    'delivery_price' => 3000,
                    'status' => "active",
        ]);
        $merchant1->paymentMethods()->save($paymentMethod3);
        $merchant1->paymentMethods()->save($paymentMethod4);
        $category1 = Category::create([
                    'name' => "Cat2",
                    'level' => "1",
                    'description' => "Cat desc 2",
        ]);
        $merchant1->categories()->save($category1);
        $product1 = new Product([
            'name' => "Pastel 1",
            'description' => "Pastel para tomar 1",
            'sku' => "SDFWERW2334D3",
            'ref2' => "SDFWEsdfgW2334Ddfg3",
            'price' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'quantity' => 20,
        ]);
        $category1->products()->save($product1);
        $merchant1->products()->save($product1);
        $product1->save();
        $product1 = new Product([
            'name' => "Pastel 2",
            'description' => "Pastel para tomar 2",
            'sku' => "SDFWERW2334D3",
            'ref2' => "SDFWEsdfgW2334Ddfg3",
            'price' => 20000,
            'tax' => 2000,
            'total' => 22000,
            'quantity' => 10,
        ]);
        $merchant1->products()->save($product1);
        $category1->products()->save($product1);
        $product1->save();
        $category1 = Category::create([
                    'name' => "Cat1",
                    'level' => "1",
                    'description' => "Cat desc 1",
        ]);
        $merchant1->categories()->save($category1);
        $product1 = new Product([
            'name' => "Pastel 3",
            'description' => "Pastel para tomar 3",
            'sku' => "SDFWERW2334D3",
            'ref2' => "SDFWEsdfgW2334Ddfg3",
            'price' => 30000,
            'tax' => 3000,
            'total' => 33000,
            'quantity' => 30,
        ]);
        $merchant1->products()->save($product1);
        $category1->products()->save($product1);
        $product1->save();
        $merchant1 = Merchant::create([
                    'name' => "Cigarreria Hoov",
                    'email' => "hoovert@backbonetechnology.com",
                    'telephone' => "321123321",
                    'address' => "Calle prueba carrera prueba",
                    'description' => "Cigarreria",
                    'icon' => "default",
                    'lat' => 4.652751,
                    'long' => -74.059083,
                    'minimum' => 10000,
                    'delivery_time' => "15 a 20 mins",
                    'delivery_price' => 3000,
                    'status' => "active",
        ]);
        $merchant1->paymentMethods()->save($paymentMethod3);
        $merchant1->paymentMethods()->save($paymentMethod);
        $category1 = Category::create([
                    'name' => "Cat1",
                    'level' => "1",
                    'description' => "Cat desc 1",
        ]);
        $merchant1->categories()->save($category1);
        $product1 = new Product([
            'name' => "Cigarro 1",
            'description' => "Cigarro para tomar 1",
            'sku' => "SDFWERW2334D3",
            'ref2' => "SDFWEsdfgW2334Ddfg3",
            'price' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'quantity' => 20,
        ]);
        $merchant1->products()->save($product1);
        $category1->products()->save($product1);
        $product1->save();
        $product1 = new Product([
            'name' => "Cigarro 2",
            'description' => "Cigarro para tomar 2",
            'sku' => "SDFWERW2334D3",
            'ref2' => "SDFWEsdfgW2334Ddfg3",
            'price' => 20000,
            'tax' => 2000,
            'total' => 22000,
            'quantity' => 10,
        ]);
        $merchant1->products()->save($product1);
        $category1->products()->save($product1);
        $product1->save();
        $category1 = Category::create([
                    'name' => "Cat2",
                    'level' => "1",
                    'description' => "Cat desc 2",
        ]);
        $merchant1->categories()->save($category1);
        $product1 = new Product([
            'name' => "Cigarro 3",
            'description' => "Cigarro para tomar 3",
            'sku' => "SDFWERW2334D3",
            'ref2' => "SDFWEsdfgW2334Ddfg3",
            'price' => 30000,
            'tax' => 3000,
            'total' => 33000,
            'quantity' => 30,
        ]);
        $merchant1->products()->save($product1);
        $category1->products()->save($product1);
        $product1->save();

        $merchant1 = Merchant::create([
                    'name' => "Veterinaria Hoov",
                    'email' => "hoovert@impulsarme.com",
                    'telephone' => "321123321",
                    'address' => "Calle prueba carrera prueba",
                    'description' => "Veterinaria",
                    'icon' => "default",
                    'lat' => 4.661198,
                    'long' => -74.050049,
                    'minimum' => 10000,
                    'delivery_time' => "15 a 20 mins",
                    'delivery_price' => 2000,
                    'status' => "active",
        ]);
        $merchant1->paymentMethods()->save($paymentMethod2);
        $merchant1->paymentMethods()->save($paymentMethod4);
        $category1 = Category::create([
                    'name' => "Cat1",
                    'level' => "1",
                    'description' => "Cat desc 1",
        ]);
        $merchant1->categories()->save($category1);
        $product1 = new Product([
            'name' => "Comida 1",
            'description' => "Comida para tomar 1",
            'sku' => "SDFWERW2334D3",
            'ref2' => "SDFWEsdfgW2334Ddfg3",
            'price' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'quantity' => 20,
        ]);
        $category1->products()->save($product1);
        $merchant1->products()->save($product1);
        $product1->save();
        $product1 = new Product([
            'name' => "Comida 2",
            'description' => "Comida para tomar 2",
            'sku' => "SDFWERW2334D3",
            'ref2' => "SDFWEsdfgW2334Ddfg3",
            'price' => 20000,
            'tax' => 2000,
            'total' => 22000,
            'quantity' => 10,
        ]);
        $category1 = Category::create([
                    'name' => "Cat2",
                    'level' => "1",
                    'description' => "Cat desc 2",
        ]);

        $merchant1->categories()->save($category1);
        $category1->products()->save($product1);
        $merchant1->products()->save($product1);
        $product1->save();
        $product1 = new Product([
            'name' => "Comida 3",
            'description' => "Comida para tomar 3",
            'sku' => "SDFWERW2334D3",
            'ref2' => "SDFWEsdfgW2334Ddfg3",
            'price' => 30000,
            'tax' => 3000,
            'total' => 33000,
            'quantity' => 30,
        ]);
        $category1->products()->save($product1);
        $merchant1->products()->save($product1);
        $user = User::where('id', '>', 0)->orderBy('id', 'asc')->take(1)->get()->first();

        $user->merchants()->save($merchant1);
        $product1->save();
        foreach ($dias as $dia) {

            foreach (Merchant::all() as $merchant) {
                $hora = rand(6, 11);
                $close = rand(17, 22);
                //$da = $hora+"L"+$close;
                $horario = OfficeHour::create([
                            "day" => $dia,
                            "open" => $hora . ":00:00",
                            "close" => $close . ":00:00",
                ]);
                $merchant->hours()->save($horario);
            }
        }
        $this->createOrders();
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
                $data["payment_method_id"] =$pmethod->id;
                $data["comments"] = "No comment";
                $data["cash_for_change"] = "50";
                $this->editOrder->setOrderDetails($user, $data);
            }
        }
    }

}
