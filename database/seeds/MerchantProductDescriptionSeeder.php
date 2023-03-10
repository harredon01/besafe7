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
use App\Services\Rapigo;
use App\Models\CoveragePolygon;
use App\Models\PaymentMethod;
use App\Models\Booking;

class MerchantProductDescriptionSeeder extends Seeder {
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
    protected $rapigo;

    /**
     * The edit profile implementation.
     *
     */
    protected $merchantImport;

    public function __construct(EditOrder $editOrder, MerchantImport $merchantImport, EditRating $editRating, EditBooking $editBooking, MiPaquete $miPaquete,Rapigo $rapigo) {
        $this->editOrder = $editOrder;
        $this->merchantImport = $merchantImport;
        $this->editRating = $editRating;
        $this->editBooking = $editBooking;
        $this->miPaquete = $miPaquete;
        $this->rapigo = $rapigo;
        /* $this->middleware('location.group', ['only' => 'postGroupLocation']);
          $this->middleware('location.group', ['only' => 'getGroupLocation']); */
    }

    public function run() {
        $products = Product::all();
        foreach ($products as $product) {
            $product->description = "<p>".$product->description."</p>";
            $product->save();
        }
    }


}
