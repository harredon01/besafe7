<?php

namespace App\Services;

use App\Models\Merchant;
use App\Models\Category;
use App\Models\Availability;
use App\Models\Report;
use App\Imports\ArrayMultipleSheetImport;
use App\Models\Delivery;
use App\Models\FileM;
use Illuminate\Support\Facades\Mail;
use App\Mail\StoreReports;
use App\Services\EditMapObject;
use App\Services\EditProduct;
use App\Services\EditBooking;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Exports\ArrayMultipleSheetExport;
use App\Models\ProductVariant;
use Excel;
use DB;

class StoreExport {

    const COMMISION_RATE = 0.1;
    const TAX_RATE = 0.08;
    const PACKING_COST = 1400;
    const ORDER_APPORVED_STATUS = 'approved';
    const ORDER_EXTERNAL_PAYMENT_STATUS = 'payment_in_bank';
    const COMMISION_WO_DISCOUNT = 'Comision antes descuentos';
    const DICOUNT_COMMISION = 'Descuento Comision';
    const COMMISION_W_DISCOUNT = 'Comision despues descuentos';
    const COST_WO_DISCOUNT = 'Costo antes de descuentos';
    const LUNCHES = 'Almuerzos';
    const DICOUNT_COST = 'Descuento Productos';
    const COST_W_DISCOUNT = 'Costo despues de descuentos';
    const TAX_WO_DISCOUNT = 'Impuestos antes de descuentos';
    const DICOUNT_TAX = 'Descuento Impuestos';
    const TAX_W_DISCOUNT = 'Impuestos despues de descuentos';
    const DEPOSIT = 'Deposito';
    const SUBTOTAL_ORDER = 'Subtotal Orden';
    const SHIPPING = 'Envio';
    const SHIPPINGDISCOUNT = 'Descuento Envio';
    const SHIPPINGPRE = 'Envio sin descuento';
    const PACKING = 'Desechable';
    const PACKING_AMOUNT = 'Desechables usados';
    const DISCOUNT = 'Descuento';
    const TOTAL_ORDER = 'Total Orden';
    const SUBTOTAL_PAYMENTS = 'Subtotal Pagos';
    const TRANSACTION_COST = 'Costo Transaccion';
    const TOTAL_PAYMENTS = 'Total Pagos';
    const DISCOUNT_MARKETING = 'Descuento Mercadeo';
    const DISCOUNT_VOLUME = 'Descuento Volumen';
    const DISCOUNT_PROVIDER = 'Descuento Proveedor';
    const DISCOUNT_PROVIDER_OPERATION = 'Descuento Operacion Proveedor';
    const DISCOUNT_PROVIDER_MARKETING = 'Descuento Mercadeo Proveedor';
    const DISCOUNT_HOOV = 'Descuento Hoov';
    const DISCOUNT_HOOV_OPERATION = 'Descuento Hoov Operacion';
    const DISCOUNT_HOOV_MARKETING = 'Descuento Hoov Mercadeo';
    const PAYMENTS_AMOUNT = 'Cantidad Pagos';
    const ORDERS_AMOUNT = 'Cantidad Ordenes';

    public function __construct(EditMapObject $editMapObject, EditProduct $editProduct, EditBooking $editBooking) {
        $this->editMapObject = $editMapObject;
        $this->editProduct = $editProduct;
        $this->editBooking = $editBooking;
    }

    public function exportEverything($startDate, $endDate) {
//        $startDate = "2019-05-05";
//        $endDate = "2021-05-19";
        $shipping = 0;
        $tax = 0;
        $discount = 0;
        $commision = 0;
        $deposits = 0;
        $packing = 0;
        $packingNum = 0;
        $transactionCost = 0;
        $amountOrders = 0;
        $cost = 0;
        $lunches = 0;
        $productDiscount = 0;
        $providerDiscount = 0;
        $providerOperationDiscount = 0;
        $providerMarketingDiscount = 0;
        $meDiscount = 0;
        $meOperationDiscount = 0;
        $meMarketingDiscount = 0;
        $marketingDiscount = 0;
        $operationDiscount = 0;
        $costSub = 0;
        $taxSub = 0;
        $comissionSub = 0;
        $taxDiscount = 0;
        $subtotal = 0;
        $total = 0;
        $paymentsSubtotal = 0;
        $paymentsTotal = 0;
        $paymentsCount = 0;
        $depositData = [['orden', 'depositos']];
        $providerData = [['orden', 'proveedor', 'productos', 'impo']];
        $results = [];
        $merchantsObserved = [["name",
        self::LUNCHES,
        self::COST_WO_DISCOUNT,
        self::DICOUNT_COST,
        self::COST_W_DISCOUNT,
        self::TAX_WO_DISCOUNT,
        self::DICOUNT_TAX,
        self::TAX_W_DISCOUNT,
        self::COMMISION_WO_DISCOUNT,
        self::DICOUNT_COMMISION,
        self::COMMISION_W_DISCOUNT,
        self::DEPOSIT,
        self::PACKING,
        self::PACKING_AMOUNT,
        self::DISCOUNT_VOLUME,
        self::SUBTOTAL_ORDER,
        self::DISCOUNT_MARKETING,
        self::SHIPPING,
        self::TOTAL_ORDER,
        self::SUBTOTAL_PAYMENTS,
        self::TRANSACTION_COST,
        self::TOTAL_PAYMENTS,
        self::DISCOUNT,
        self::DISCOUNT_VOLUME,
        self::DISCOUNT_MARKETING,
        self::DISCOUNT_PROVIDER,
        self::DISCOUNT_PROVIDER_OPERATION,
        self::DISCOUNT_PROVIDER_MARKETING,
        self::DISCOUNT_HOOV,
        self::DISCOUNT_HOOV_OPERATION,
        self::DISCOUNT_HOOV_MARKETING,
        self::PAYMENTS_AMOUNT,
        self::ORDERS_AMOUNT]];
        $merchants = Merchant::whereIn("id", [1299, 1300, 1301])->get();
        foreach ($merchants as $merchant) {
            $orders = Order::where('status', self::ORDER_APPORVED_STATUS)
                            ->where('merchant_id', $merchant->id)
                            ->whereBetween('updated_at', [$startDate, $endDate])
                            ->whereNotIn('user_id', [1, 77])
                            ->with(['payments.user', 'items', 'orderConditions'])->get();

            $merchantResults = $this->exportOrderInvoices($orders, $merchant->name);
            $meDiscount += $merchantResults[self::DISCOUNT_HOOV];
            $comissionSub += $merchantResults[self::COMMISION_W_DISCOUNT];

            $costSub += $merchantResults[self::COST_W_DISCOUNT];
            $taxSub += $merchantResults[self::TAX_W_DISCOUNT];
            $deposits += $merchantResults[self::DEPOSIT];
            $lunches += $merchantResults[self::LUNCHES];
            $packing += $merchantResults[self::PACKING];
            $packingNum += $merchantResults[self::PACKING_AMOUNT];
            $subtotal += $merchantResults[self::SUBTOTAL_ORDER];
            $discount += $merchantResults[self::DISCOUNT];
            $total += $merchantResults[self::TOTAL_ORDER];
            $paymentsSubtotal += $merchantResults[self::SUBTOTAL_PAYMENTS];
            $transactionCost += $merchantResults[self::TRANSACTION_COST];
            $paymentsTotal += $merchantResults[self::TOTAL_PAYMENTS];
            $providerDiscount += $merchantResults[self::DISCOUNT_PROVIDER];
            $meDiscount += $merchantResults[self::DISCOUNT_HOOV];
            $paymentsCount += $merchantResults[self::PAYMENTS_AMOUNT];
            $amountOrders += $merchantResults[self::ORDERS_AMOUNT];
            $page = [
                "name" => $merchant->name,
                "rows" => $merchantResults['operationData']
            ];
            unset($merchantResults['operationData']);

            array_push($results, $page);

            array_push($merchantsObserved, $merchantResults);
            $depositData = array_merge($depositData, $merchantResults['depositsData']);
            $providerData = array_merge($providerData, $merchantResults['providerData']);
        }
        $summary = [
            "name" => "totals",
            self::LUNCHES => $lunches,
            self::COST_W_DISCOUNT => $costSub,
            self::TAX_W_DISCOUNT => $taxSub,
            self::DEPOSIT => $deposits,
            self::COMMISION_W_DISCOUNT => $comissionSub,
            self::PACKING => $packing,
            self::PACKING_AMOUNT => $packingNum,
            self::SUBTOTAL_ORDER => $subtotal,
            self::TOTAL_ORDER => $total,
            self::SUBTOTAL_PAYMENTS => $paymentsSubtotal,
            self::TRANSACTION_COST => $transactionCost,
            self::TOTAL_PAYMENTS => $paymentsTotal,
            self::DISCOUNT => $discount,
            self::DISCOUNT_PROVIDER => $providerDiscount,
            self::DISCOUNT_HOOV => $meDiscount,
            self::PAYMENTS_AMOUNT => $paymentsCount,
            self::ORDERS_AMOUNT => $amountOrders
        ];
        array_push($merchantsObserved, $summary);
        $page = [
            "name" => "Totals",
            "rows" => $merchantsObserved
        ];
        array_unshift($results, $page);
        $page = [
            "name" => "Proveedor",
            "rows" => $providerData
        ];
        array_push($results, $page);
        $page = [
            "name" => "Depositos",
            "rows" => $depositData
        ];
        array_push($results, $page);
        $results = $this->deliveriesData($startDate, $endDate, $results);
        $this->writeFile($results, "Total operacion_" . time(), true);
    }

    public function exportOrdersAdmin($merchant_id, $startDate, $endDate) {
        $pages = [
            ["name" => "Summary", "rows" => []],
            ["name" => "Detail", "rows" => []],
        ];

        $processed = 0;
        $count = Order::whereIn('status', [self::ORDER_APPORVED_STATUS, self::ORDER_EXTERNAL_PAYMENT_STATUS])
                        ->where('merchant_id', $merchant_id)
                        ->whereBetween('updated_at', [$startDate, $endDate])
                        ->whereNotIn('user_id', [1, 2, 3])->count();
        Order::whereIn('status', [self::ORDER_APPORVED_STATUS, self::ORDER_EXTERNAL_PAYMENT_STATUS])
                ->where('merchant_id', $merchant_id)
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->whereNotIn('user_id', [1, 2, 3])
                ->with(['payments.user', 'items', 'orderConditions'])->chunk(50, function ($orders) use(&$pages, $count, &$processed) {
            $merchantResults = $this->exportOrderMaster($orders);
            $pages[0]['rows'] = array_merge($pages[0]['rows'], $merchantResults['orders_fast']);
            $pages[1]['rows'] = array_merge($pages[1]['rows'], $merchantResults['orders_full']);
            $processed += 100;
            echo $processed . PHP_EOL;
            if ($processed == $count) {
                array_unshift($pages[0]['rows'], array_keys($merchantResults['orders_fast'][0]));
                array_unshift($pages[1]['rows'], array_keys($merchantResults['orders_full'][0]));
                $this->writeFile($pages, "Productos-Descarga-Completa" . time(), true);
            }
        });
    }

    public function exportOrdersClient(User $user, $merchant_id, $startDate, $endDate) {
        $pages = [
            ["name" => "Summary", "rows" => []],
            ["name" => "Detail", "rows" => []],
        ];

        $processed = 0;
        $count = Order::whereIn('status', [self::ORDER_APPORVED_STATUS, self::ORDER_EXTERNAL_PAYMENT_STATUS])
                        ->where('merchant_id', $merchant_id)
                        ->whereBetween('updated_at', [$startDate, $endDate])
                        ->whereNotIn('user_id', [1, 2, 3])->count();
        Order::whereIn('status', [self::ORDER_APPORVED_STATUS, self::ORDER_EXTERNAL_PAYMENT_STATUS])
                ->where('merchant_id', $merchant_id)
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->whereNotIn('user_id', [1, 2, 3])
                ->with(['items', 'orderConditions'])->chunk(50, function ($orders) use(&$pages, $count, &$processed, &$user) {
            $merchantResults = $this->exportOrderData($orders);
            $pages[0]['rows'] = array_merge($pages[0]['rows'], $merchantResults['orders_fast']);
            $pages[1]['rows'] = array_merge($pages[1]['rows'], $merchantResults['orders_full']);
            $processed += 100;
            echo $processed . PHP_EOL;
            if ($processed == $count) {
                array_unshift($pages[0]['rows'], array_keys($merchantResults['orders_fast'][0]));
                array_unshift($pages[1]['rows'], array_keys($merchantResults['orders_full'][0]));
                $this->writeFile2($user, $pages, "Productos-Descarga-Completa" . time(), true);
            }
        });
    }

    public function exportProducts(User $user, $merchant_id) {
        $pages = [
            ["name" => "products", "rows" => []],
            ["name" => "variants", "rows" => []],
        ];

        $processed = 0;
        $variantsHead = [];
        $filesHead = [];
        $count = Product::whereIn('id', function($query) use ($merchant_id) {
                    $query->select('product_id')
                            ->from('merchant_product')
                            ->where('merchant_id', $merchant_id);
                })->count();
        Product::whereIn('id', function($query) use ($merchant_id) {
            $query->select('product_id')
                    ->from('merchant_product')
                    ->where('merchant_id', $merchant_id);
        })->with(['productVariants', 'merchants', 'files', 'categories'])->chunk(100, function ($products) use(&$pages, $count, &$processed, &$variantsHead, &$filesHead, &$user) {
            foreach ($products as $product) {
                $variants = $product->productVariants;
                if (count($variants) > 0) {
                    unset($variants[0]->created_at);
                    unset($variants[0]->updated_at);
                    $variantsHead = array_keys($variants[0]->toArray());
                }
                $merchantId = "";
                foreach ($product->merchants as $merch) {
                    $merchantId .= $merch->id . ",";
                }
                $merchantId = substr($merchantId, 0, -1);
                $product->merchant_id = $merchantId;
                $categories = "";

                foreach ($product->categories as $p) {
                    $categories .= $p->id . ",";
                }
                $categories = substr($categories, 0, -1);
                $product->categories = $categories;
                $files = "";

                foreach ($product->files as $p) {
                    $resfile = explode("/", $p->file);
                    $files .= $resfile[count($resfile) - 1] . ",";
                }
                $files = substr($files, 0, -1);
                $product->imagen = $files;
                unset($product->productVariants);
                unset($product->files);
                unset($product->merchants);
                unset($product->user_id);
                unset($product->high);
                unset($product->created_at);
                unset($product->updated_at);
                unset($product->low);
                unset($product->rating);
                unset($product->ends_at);
                unset($product->rating_count);
                array_push($pages[0]['rows'], $product);
                if ($variants) {
                    foreach ($variants as $value) {
                        unset($value->created_at);
                        unset($value->updated_at);
                    }
                    $pages[1]['rows'] = array_merge($pages[1]['rows'], $variants->toArray());
                }

                $processed++;
                //echo $processed . PHP_EOL;
                if ($processed == $count) {
                    array_unshift($pages[0]['rows'], array_keys($product->toArray()));
                    array_unshift($pages[1]['rows'], $variantsHead);
                    $this->writeFile2($user, $pages, "Productos-Descarga-Completass" . time(), true);
                }
            }
        });
    }

    public function exportProductsFast(User $user, $merchant_id) {
        $pages = [
            ["name" => "quick", "rows" => []],
        ];

        $processed = 0;
        $tableHeader = [];
        $count = Product::whereIn('id', function($query) use ($merchant_id) {
                    $query->select('product_id')
                            ->from('merchant_product')
                            ->where('merchant_id', $merchant_id);
                })->count();
        Product::whereIn('id', function($query) use ($merchant_id) {
            $query->select('product_id')
                    ->from('merchant_product')
                    ->where('merchant_id', $merchant_id);
        })->with('productVariants')->chunk(100, function ($products) use(&$pages, $count, &$processed, &$tableHeader, &$user) {
            foreach ($products as $product) {
                $row = null;
                foreach ($product->productVariants as $variant) {
                    $row = [
                        "name" => $product->name,
                        "id" => $variant->id,
                        "sku" => $variant->sku,
                        "quantity" => $variant->quantity,
                        "min_quantity" => $variant->min_quantity,
                        "price" => $variant->price,
                        "sale" => $variant->sale,
                        "tax" => $variant->tax,
                        "cost" => $variant->cost,
                        "description" => $variant->description,
                    ];
                    $tableHeader = array_keys($row);
                    array_push($pages[0]['rows'], $row);
                }
                $processed++;
                echo $processed . PHP_EOL;
                if ($processed == $count) {
                    array_unshift($pages[0]['rows'], $tableHeader);
                    $this->writeFile2($user, $pages, "Productos-Descarga-rapida-" . time(), true);
                }
            }
        });
    }

    public function exportMerchants(User $user) {
        $pages = [
            ["name" => "merchants", "rows" => []],
        ];
        $tablehead = [];
        $processed = 0;
        $count = Merchant::whereIn('id', function($query) use ($user) {
                    $query->select('merchant_id')
                            ->from('merchant_user')
                            ->where('user_id', $user->id);
                })->count();
        Merchant::whereIn('id', function($query) use ($user) {
            $query->select('merchant_id')
                    ->from('merchant_user')
                    ->where('user_id', $user->id);
        })->with("categories")->chunk(100, function ($merchants) use(&$pages, $count, &$processed, &$tablehead) {
            foreach ($merchants as $merchant) {
                unset($merchant->merchant_id);
                $attributes = $merchant->attributes;
                $categories = "";

                foreach ($merchant->categories as $p) {
                    $categories .= $p->id . ",";
                }
                unset($merchant->categories);
                $categories = substr($categories, 0, -1);
                $data = $merchant->toArray();
                $data['categories'] = $categories;
                $data['experience1'] = "";
                $data['experience2'] = "";
                $data['experience3'] = "";
                $data['experience4'] = "";
                $data['experience5'] = "";
                $data['specialty1'] = "";
                $data['specialty2'] = "";
                $data['specialty3'] = "";
                $data['specialty4'] = "";
                $data['specialty5'] = "";
                $data['service1'] = "";
                $data['service2'] = "";
                $data['service3'] = "";
                $data['service4'] = "";
                $data['service5'] = "";
                unset($data['attributes']);
                unset($data['created_at']);
                unset($data['updated_at']);
                unset($data['ends_at']);
                unset($data['rating']);
                unset($data['rating_count']);
                unset($data['position']);
                foreach ($attributes as $key => $value) {
                    if (is_array($value)) {
                        $attrA = $value;
                        $counter = 0;
                        foreach ($attrA as $key2 => $item) {
                            $counter++;
                            $data[$key . $counter] = $item['name'];
                        }
                    } else {
                        $data[$key] = $value;
                    }
                }
                if (count(array_keys($data)) > count($tablehead)) {
                    $tablehead = array_keys($data);
                }
                array_push($pages[0]['rows'], $data);
                $processed++;
                echo $processed . PHP_EOL;
                if ($processed == $count) {
                    array_unshift($pages[0]['rows'], $tablehead);
                    $this->writeFile($pages, "Listado-Negocios-Descarga-Completa-" . time(), true);
                }
            }
        });
    }

    public function exportAvailabilitiesFast(User $user, $merchant_id) {
        $pages = [
            ["name" => "availabilities", "rows" => []],
        ];
        $processed = 0;
        $count = Availability::where('bookable_id', $merchant_id)->where('bookable_type', "App\\Models\\Merchant")->count();
        Availability::where('bookable_id', $merchant_id)->where('bookable_type', "App\\Models\\Merchant")->chunk(100, function ($availabilities) use(&$pages, $count, &$processed, &$user) {
            foreach ($availabilities as $availability) {
                array_push($pages[0]['rows'], $availability);
                $processed++;
                $availability->type = $availability->bookable_type;
                $availability->object_id = $availability->bookable_id;
                unset($availability->bookable_type);
                unset($availability->bookable_id);
                unset($availability->is_bookable);
                unset($availability->priority);
                unset($availability->created_at);
                unset($availability->updated_at);
                unset($availability->deleted_at);
                echo $processed . PHP_EOL;
                if ($processed == $count) {
                    //dd($pages);
                    array_unshift($pages[0]['rows'], array_keys($availability->toArray()));
                    $this->writeFile2($user, $pages, "Disponibilidad-" . time(), true);
                }
            }
        });
    }

    public function importGlobalExcel(User $user, $filename, $clean) {
        $reader = Excel::toArray(new ArrayMultipleSheetImport, $filename);
        $productsMap = [];
        foreach ($reader as $key => $value) {
            if ($key == 'merchants') {
                $this->importMerchantsExcelInternal($user, $value);
            } else if ($key == 'categories') {
                $this->importCategoriesExcelInternal($user, $value);
            } else if ($key == 'reports') {
                $this->importReportsExcelInternal($user, $value);
            } else if ($key == 'products') {
                $this->importProductsExcelInternal($user, $value);
            } else if ($key == 'variants') {
                $this->importProductVariantsExcelInternal($user, $value);
            } else if ($key == 'availabilities') {
                $this->importMerchantsAvailabilitiesExcelInternal($user, $value);
            } else if ($key == 'ratings') {
                $this->importMerchantsRatingsExcelInternal($user, $value);
            } else if ($key == 'polygons') {
                $this->importPolygonsInternal($user, $value);
            } else if ($key == 'quick') {
                $this->importProductsQuickExcelInternal($user, $value);
            } else if ($key == 'new-merchants') {
                $productsMap = $this->importMerchantsExcelInternal($user, $value, $productsMap);
            } else if ($key == 'new-variants') {
                $this->importMerchantsAvailabilitiesExcelInternal($user, $value, $productsMap);
            } else if ($key == 'new-products') {
                $productsMap = $this->importProductsExcelInternal($user, $value, $productsMap);
            } else if ($key == 'new-variants') {
                $this->importProductVariantsExcelInternal($user, $value, $productsMap);
            }
        }
        if ($clean) {
            Storage::delete($filename);
        }
    }

    public function writeFile($data, $title, $sendMail) {
        //dd($data);
        $file = Excel::store(new ArrayMultipleSheetExport($data), "exports/" . $title . ".xls", "local");
        $path = 'exports/' . $title . ".xls";
        $path = 'exports/' . $title . ".xls";
        if ($sendMail) {
            $users = User::whereIn('id', [1, 77, 2])->get();
            Mail::to($users)->send(new StoreReports($path));
        } else {
            return $path;
        }
    }

    public function writeFile2($user, $data, $title, $sendMail) {
        //dd($data);
        $file = Excel::store(new ArrayMultipleSheetExport($data), "exports/" . $title . ".xls", "local");
        $path = 'exports/' . $title . ".xls";
        $path = 'exports/' . $title . ".xls";
        if ($sendMail) {
            Mail::to($user)->send(new StoreReports($path));
        } else {
            return $path;
        }
    }

    public function deliveriesData($startDate, $endDate, $results) {
        $deliveries = Delivery::whereNotIn("status", ["deposit"])->whereNotIn("user_id", [2, 77, 3, 82, 161])->whereBetween('created_at', [$startDate, $endDate])->get();
        $attributes = array_keys($deliveries[0]->toArray());
        $rows = [$attributes];
        $rows = array_merge($rows, $deliveries->toArray());
        $page = [
            "name" => "Entregas Vendidas",
            "rows" => $rows
        ];
        array_push($results, $page);
        $deliveries = Delivery::whereIn("status", ["completed", "preparing"])->whereNotIn("user_id", [2, 77, 3, 82, 161])->whereBetween('delivery', [$startDate, $endDate])->get();
        $attributes = array_keys($deliveries[0]->toArray());
        $rows = [$attributes];
        $rows = array_merge($rows, $deliveries->toArray());
        $page = [
            "name" => "Entregas Ejecutadas",
            "rows" => $rows
        ];
        array_push($results, $page);
        $deliveries = Delivery::whereIn("status", ["completed", "preparing"])->whereIn("user_id", [77, 82])->whereBetween('delivery', [$startDate, $endDate])->get();
        $attributes = array_keys($deliveries[0]->toArray());
        $rows = [$attributes];
        $rows = array_merge($rows, $deliveries->toArray());
        $page = [
            "name" => "Entregas Mluisa y Camila",
            "rows" => $rows
        ];
        array_push($results, $page);
        return $results;
    }

    public function dailyInvoices($ordersArray) {
        $orders = Order::whereIn('id', $ordersArray)
                        ->with(['payments.user', 'items', 'orderConditions'])->get();
        $results = $this->exportOrderInvoices($orders, "Facturas-diarias");
        $page = [
            "name" => "Facturas diarias",
            "rows" => $results['operationData']
        ];
        return $this->writeFile([$page], "Facturas-diarias" . time(), false);
    }

    private function exportOrderInvoices($orders, $name) {

        $discount = 0;
        $deposits = 0;
        $comissionSub = 0;
        $taxSub = 0;
        $packing = 0;
        $packingNum = 0;
        $transactionCost = 0;
        $lunches = 0;
        $costSub = 0;
        $providerDiscount = 0;
        $meDiscount = 0;
        $subtotal = 0;
        $total = 0;
        $paymentsSubtotal = 0;
        $paymentsTotal = 0;
        $paymentsCount = 0;
        $title = array([]);
        $titleO = array([]);
        $title2 = array([]);
        $title3 = array([]);
        $ordersData = [$orderItem = [
        "Orden",
        self::LUNCHES,
        self::COST_W_DISCOUNT,
        self::TAX_W_DISCOUNT,
        "Proveedor",
        self::DEPOSIT,
        self::COMMISION_W_DISCOUNT,
        "Usuario",
        self::PACKING,
        self::PACKING_AMOUNT,
        self::TOTAL_ORDER,
        self::SUBTOTAL_PAYMENTS,
        self::TRANSACTION_COST,
        self::TOTAL_PAYMENTS,
        self::DISCOUNT,
        self::DISCOUNT_PROVIDER,
        self::DISCOUNT_HOOV,
        self::PAYMENTS_AMOUNT
        ]];
        $ordersData2 = [];
        $conditionsData = [];
        $providerData = [];
        $depositsData = [];
        $itemsData = [];
        $paymentsData = [];
        foreach ($orders as $order) {
            $oTax = 0;
            $oCost = 0;
            $oShipping = 0;
            $oDeposit = 0;
            $oPacking = 0;
            $oPackingNum = 0;
            $oCommision = 0;
            $oLunches = 0;
            $oPaymentsSubtotal = 0;
            $oPaymentsTotal = 0;
            $oTransactionCost = 0;
            $oMeDiscount = 0;
            $OordersData = [];
            $OconditionsData = [];
            $OitemsData = [];
            $OpaymentsData = [];
            $oProviderDiscount = 0;
            $orderArray = $order->toArray();
            foreach ($order->orderConditions as $condition) {
                $conditionArray = $condition->toArray();
                $string = $condition->value;
                $firstCharacter = $string[0];
                if ($firstCharacter == "-") {
                    if ($condition->condition_id) {
                        $oProviderDiscount += ($condition->total * 0.7);
                        $oMeDiscount += ($condition->total * 0.3);
                    } else {
                        $oProviderDiscount += $condition->total;
                    }
                } else {
                    $oShipping += $condition->total;
                }
                $conditionArray["Condicion"] = $conditionArray["name"];
                $finalItem = [
                    "Condicion" => $conditionArray['name'],
                    "Tipo" => $conditionArray['value'],
                    "Total" => $conditionArray['total'],
                ];
                //array_push($conditionsData, $finalItem);
                array_push($OconditionsData, $finalItem);
                $title2 = array(array_keys($finalItem));
            }
            foreach ($order->items as $item) {
                $buyers = 1;
                $attributes = json_decode($item->attributes, true);
                if (array_key_exists('buyers', $attributes)) {
                    $buyers = (double) $attributes['buyers'];
                }

                if (array_key_exists("is_credit", $attributes)) {
                    $oDeposit += $item->priceSumConditions - (2500 * $item->quantity);
                    $theDeposit = [
                        "fecha" => $order->updated_at,
                        "Cuenta" => 2805,
                        "Tercero" => "Cliente",
                        "NIT" => "830109723-8",
                        "Cliente" => $order->user_id,
                        "Concepto" => "Deposito orden: " . $order->id,
                        "Debito" => "",
                        "Credito" => $item->priceSumConditions - (2500 * $item->quantity)
                    ];
                    array_push($depositsData, $theDeposit);
                    $theDeposit = [
                        "fecha" => $order->updated_at,
                        "Cuenta" => 1110,
                        "Tercero" => "Bancos",
                        "NIT" => "",
                        "Cliente" => "",
                        "Concepto" => "",
                        "Debito" => $item->priceSumConditions - (2500 * $item->quantity),
                        "Credito" => ""
                    ];
                    array_push($depositsData, $theDeposit);
                } else {
                    $oLunches += ($buyers * $item->quantity);
                    $oCost += $item->quantity * $item->cost;
                    $oTax += $item->quantity * $item->tax;
                    if (array_key_exists('shipping', $attributes)) {
                        $oShipping += $item->quantity * $buyers * ((double) $attributes['shipping']);
                        if (!array_key_exists('requires_credits', $attributes)) {
                            $oPacking += $item->quantity * self::PACKING_COST * $buyers;
                            $oPackingNum += $item->quantity * $buyers;
                        }
                    }
                }
                $itemArray = $item->toArray();
                $finalItem = [
                    "Producto" => $itemArray['name'],
                    "Precio" => $itemArray['price'],
                    "Almuerzos" => ($buyers * $item->quantity),
                    "Cantidad" => $itemArray['quantity'],
                    "Costo" => $itemArray['cost'],
                    "Impuesto" => $itemArray['tax'],
                ];
                if (!array_key_exists('requires_credits', $attributes)) {
                    $finalItem['Empaque'] = $item->quantity * self::PACKING_COST * $buyers;
                    $finalItem['Empaques usados'] = $item->quantity * $buyers;
                }
                $title = array(array_keys($finalItem));
                //array_push($itemsData, $finalItem);
                array_push($OitemsData, $finalItem);
            }

            foreach ($order->payments as $payment) {
                $oPaymentsSubtotal += $payment->subtotal;
                $oPaymentsTotal += $payment->total;
                $oTransactionCost += ($payment->total - $payment->subtotal);
                $paymentArray = $payment->toArray();
                $user = $paymentArray['user'];
                $finalPayment = [
                    "Pago" => $paymentArray['id'],
                    "Usuario" => $user['firstName'] . " " . $user['lastName'],
                    "Subtotal" => $paymentArray['subtotal'],
                    "Costo Transaccion" => $paymentArray['transaction_cost'],
                    "Total" => $paymentArray['total'],
                    "Referencia" => $paymentArray['referenceCode'],
                    "Fecha Actualizacion" => $paymentArray['updated_at'],
                ];
                $title3 = array(array_keys($finalPayment));
                //array_push($paymentsData, $finalPayment);
                array_push($OpaymentsData, $finalPayment);
            }
            $paymentsCount += count($order->payments);
            $subtotal += $order->subtotal;
            $total += $order->total;
            $oProvider = $oCost + $oTax - $oProviderDiscount;
            $oProducts = $oProvider / (1.08);
            $oTax = $oProvider - $oProducts;
            $theProvider = [
                "fecha" => $order->updated_at,
                "Cuenta" => 4155,
                "Tercero" => "Basilikum",
                "NIT" => "830109723-8",
                "Cliente" => $order->user_id,
                "Concepto" => "Costo orden: " . $order->id,
                "Debito" => "",
                "Credito" => $oProvider
            ];
            array_push($providerData, $theProvider);
            $theProvider = [
                "fecha" => $order->updated_at,
                "Cuenta" => 111001,
                "Tercero" => "Pagar a Basilikum",
                "NIT" => "",
                "Cliente" => "",
                "Concepto" => "",
                "Debito" => $oProvider,
                "Credito" => ""
            ];
            array_push($providerData, $theProvider);
            $oComissionSub = $oPaymentsTotal - $oProvider - $oDeposit;
            $costSub = $oProducts;
            $taxSub = $oTax;
            $comissionSub = $oComissionSub;

            $orderItem = [
                "Orden" => $orderArray['id'],
                self::LUNCHES => $oLunches,
                self::COST_W_DISCOUNT => $oProducts,
                self::TAX_W_DISCOUNT => $oTax,
                "Proveedor" => $oProvider,
                self::DEPOSIT => $oDeposit,
                self::COMMISION_W_DISCOUNT => $oComissionSub,
                "Usuario" => $orderArray['user_id'],
                self::PACKING => $oPacking,
                self::PACKING_AMOUNT => $oPackingNum,
                self::TOTAL_ORDER => $orderArray['total'],
                self::SUBTOTAL_PAYMENTS => $oPaymentsSubtotal,
                self::TRANSACTION_COST => $oTransactionCost,
                self::TOTAL_PAYMENTS => $oPaymentsTotal,
                self::DISCOUNT => $oProviderDiscount + $oMeDiscount,
                self::DISCOUNT_PROVIDER => $oProviderDiscount,
                self::DISCOUNT_HOOV => $oMeDiscount,
                self::PAYMENTS_AMOUNT => count($order->payments)
            ];
            $discount += ($oProviderDiscount + $oMeDiscount);
            $titleO = array(array_keys($orderItem));
            array_push($ordersData, $orderItem);
            $ordersData2 = array_merge($ordersData2, $titleO, [$orderItem], $title, $OitemsData, $title2, $OconditionsData, $title3, $OpaymentsData);
        }

        $operationData = array_merge($ordersData, $ordersData2);
        $summary = [
            "name" => $name,
            self::LUNCHES => $lunches,
            self::COST_W_DISCOUNT => $costSub,
            self::TAX_W_DISCOUNT => $taxSub,
            self::DEPOSIT => $deposits,
            self::COMMISION_W_DISCOUNT => $comissionSub,
            self::PACKING => $packing,
            self::PACKING_AMOUNT => $packingNum,
            self::SUBTOTAL_ORDER => $subtotal,
            self::TOTAL_ORDER => $total,
            self::SUBTOTAL_PAYMENTS => $paymentsSubtotal,
            self::TRANSACTION_COST => $transactionCost,
            self::TOTAL_PAYMENTS => $paymentsTotal,
            self::DISCOUNT => $discount,
            self::DISCOUNT_PROVIDER => $providerDiscount,
            self::DISCOUNT_HOOV => $meDiscount,
            self::PAYMENTS_AMOUNT => $paymentsCount,
            self::ORDERS_AMOUNT => count($orders),
            "operationData" => $operationData,
            "depositsData" => $depositsData,
            "providerData" => $providerData
        ];
        return $summary;
    }

    private function exportOrderMaster($orders) {

        $discount = 0;
        $taxSub = 0;
        $transactionCost = 0;
        $costSub = 0;
        $subtotal = 0;
        $total = 0;
        $paymentsSubtotal = 0;
        $paymentsTotal = 0;
        $paymentsCount = 0;
        $title = array([]);
        $titleO = array([]);
        $title2 = array([]);
        $title3 = array([]);
        $ordersData = [$orderItem = [
        "Orden",
        self::COST_W_DISCOUNT,
        self::TAX_W_DISCOUNT,
        "Usuario",
        self::TOTAL_ORDER,
        self::SUBTOTAL_PAYMENTS,
        self::TRANSACTION_COST,
        self::TOTAL_PAYMENTS,
        self::DISCOUNT,
        self::PAYMENTS_AMOUNT
        ]];
        $ordersData2 = [];
        foreach ($orders as $order) {
            $oTax = 0;
            $oCost = 0;
            $oPrice = 0;
            $oShipping = 0;
            $oPaymentsSubtotal = 0;
            $oPaymentsTotal = 0;
            $oTransactionCost = 0;
            $OconditionsData = [];
            $OitemsData = [];
            $OpaymentsData = [];
            $oDiscount = 0;
            $orderArray = $order->toArray();
            foreach ($order->orderConditions as $condition) {
                $conditionArray = $condition->toArray();
                $string = $condition->value;
                $firstCharacter = $string[0];
                if ($firstCharacter == "-") {
                    $oDiscount += $condition->total;
                } else {
                    $oShipping += $condition->total;
                }
                $conditionArray["Condicion"] = $conditionArray["name"];
                $finalItem = [
                    "Condicion" => $conditionArray['name'],
                    "Tipo" => $conditionArray['value'],
                    "Total" => $conditionArray['total'],
                ];
                //array_push($conditionsData, $finalItem);
                array_push($OconditionsData, $finalItem);
                $title2 = array(array_keys($finalItem));
            }
            foreach ($order->items as $item) {
                $buyers = 1;
                $attributes = json_decode($item->attributes, true);
                if (array_key_exists('buyers', $attributes)) {
                    $buyers = (double) $attributes['buyers'];
                }
                $oPrice += $item->quantity * $item->price;
                $oCost += $item->quantity * $item->cost;
                $oTax += $item->quantity * $item->tax;
                if (array_key_exists('shipping', $attributes)) {
                    $oShipping += $item->quantity * ((double) $attributes['shipping']);
                }
                $itemArray = $item->toArray();
                $finalItem = [
                    "Producto" => $itemArray['name'],
                    "Precio" => $itemArray['price'],
                    "Cantidad" => $itemArray['quantity'],
                    "Costo" => $itemArray['cost'],
                    "Impuesto" => $itemArray['tax'],
                ];
                $title = array(array_keys($finalItem));
                //array_push($itemsData, $finalItem);
                array_push($OitemsData, $finalItem);
            }

            foreach ($order->payments as $payment) {
                $oPaymentsSubtotal += $payment->subtotal;
                $oPaymentsTotal += $payment->total;
                $oTransactionCost += ($payment->total - $payment->subtotal);
                $paymentArray = $payment->toArray();
                $user = $paymentArray['user'];
                $finalPayment = [
                    "Pago" => $paymentArray['id'],
                    "Usuario" => $user['firstName'] . " " . $user['lastName'],
                    "Subtotal" => $paymentArray['subtotal'],
                    "Costo Transaccion" => $paymentArray['transaction_cost'],
                    "Total" => $paymentArray['total'],
                    "Referencia" => $paymentArray['referenceCode'],
                    "Fecha Actualizacion" => $paymentArray['updated_at'],
                ];
                $title3 = array(array_keys($finalPayment));
                //array_push($paymentsData, $finalPayment);
                array_push($OpaymentsData, $finalPayment);
            }
            $paymentsCount += count($order->payments);
            $subtotal += $order->subtotal;
            $total += $order->total;
            $costSub = $oPrice;
            $taxSub = $oTax;

            $orderItem = [
                "Orden" => $orderArray['id'],
                self::COST_W_DISCOUNT => $oPrice,
                self::TAX_W_DISCOUNT => $oTax,
                "Usuario" => $orderArray['user_id'],
                self::TOTAL_ORDER => $orderArray['total'],
                self::SUBTOTAL_PAYMENTS => $oPaymentsSubtotal,
                self::TRANSACTION_COST => $oTransactionCost,
                self::TOTAL_PAYMENTS => $oPaymentsTotal,
                self::DISCOUNT => $oDiscount,
                self::PAYMENTS_AMOUNT => count($order->payments)
            ];
            $discount += $oDiscount;
            $titleO = array(array_keys($orderItem));
            array_push($ordersData, $orderItem);
            $ordersData2 = array_merge($ordersData2, $titleO, [$orderItem], $title, $OitemsData, $title2, $OconditionsData, $title3, $OpaymentsData);
        }
        $summary = [
            self::COST_W_DISCOUNT => $costSub,
            self::TAX_W_DISCOUNT => $taxSub,
            self::SUBTOTAL_ORDER => $subtotal,
            self::TOTAL_ORDER => $total,
            self::SUBTOTAL_PAYMENTS => $paymentsSubtotal,
            self::TRANSACTION_COST => $transactionCost,
            self::TOTAL_PAYMENTS => $paymentsTotal,
            self::DISCOUNT => $discount,
            self::PAYMENTS_AMOUNT => $paymentsCount,
            self::ORDERS_AMOUNT => count($orders),
            "orders_fast" => $ordersData,
            "orders_full" => $ordersData2
        ];
        return $summary;
    }

    private function exportOrderData($orders) {

        $discount = 0;
        $taxSub = 0;

        $costSub = 0;
        $subtotal = 0;
        $total = 0;
        $title = array([]);
        $titleO = array([]);
        $title2 = array([]);
        $title3 = array([]);
        $ordersData = [$orderItem = [
        "Orden",
        "Usuario",
        self::TOTAL_ORDER,
        self::DISCOUNT,
        ]];
        $ordersData2 = [];
        foreach ($orders as $order) {
            $oTax = 0;
            $oPrice = 0;
            $oCost = 0;
            $oDiscount = 0;
            $oShipping = 0;
            $OordersData = [];
            $OconditionsData = [];
            $OitemsData = [];
            $OpaymentsData = [];
            $orderArray = $order->toArray();
            foreach ($order->orderConditions as $condition) {
                $conditionArray = $condition->toArray();
                $string = $condition->value;
                $firstCharacter = $string[0];
                if ($firstCharacter == "-") {
                    $oDiscount += $condition->total;
                } else {
                    $oShipping += $condition->total;
                }
                $conditionArray["Condicion"] = $conditionArray["name"];
                $finalItem = [
                    "Condicion" => $conditionArray['name'],
                    "Tipo" => $conditionArray['value'],
                    "Total" => $conditionArray['total'],
                ];
                //array_push($conditionsData, $finalItem);
                array_push($OconditionsData, $finalItem);
                $title2 = array(array_keys($finalItem));
            }
            foreach ($order->items as $item) {
                $oPrice += $item->quantity * $item->price;
                $oCost += $item->quantity * $item->cost;
                $oTax += $item->quantity * $item->tax;
                $itemArray = $item->toArray();
                $finalItem = [
                    "Nombre" => $itemArray['name'],
                    "Precio" => $itemArray['price'],
                    "Cantidad" => $itemArray['quantity'],
                    "Costo" => $itemArray['cost'],
                    "Impuesto" => $itemArray['tax'],
                    "Producto" => $itemArray['product_variant_id'],
                    "fulfillment" => $itemArray['fulfillment'],
                    "attributes" => $itemArray['attributes'],
                ];
                $title = array(array_keys($finalItem));
                //array_push($itemsData, $finalItem);
                array_push($OitemsData, $finalItem);
            }

            $subtotal += $order->subtotal;
            $total += $order->total;
            $costSub = $oPrice;
            $taxSub = $oTax;
            $discount += $oDiscount;

            $orderItem = [
                "Orden" => $orderArray['id'],
                self::COST_W_DISCOUNT => $oPrice,
                self::TAX_W_DISCOUNT => $oTax,
                "Usuario" => $orderArray['user_id'],
                self::TOTAL_ORDER => $orderArray['total'],
                self::DISCOUNT => $oDiscount,
            ];

            $titleO = array(array_keys($orderItem));
            array_push($ordersData, $orderItem);
            $ordersData2 = array_merge($ordersData2, $titleO, [$orderItem], $title, $OitemsData, $title2, $OconditionsData);
        }
        $summary = [
            self::COST_W_DISCOUNT => $costSub,
            self::TAX_W_DISCOUNT => $taxSub,
            self::SUBTOTAL_ORDER => $subtotal,
            self::TOTAL_ORDER => $total,
            self::DISCOUNT => $discount,
            self::ORDERS_AMOUNT => count($orders),
            "orders_fast" => $ordersData,
            "orders_full" => $ordersData2
        ];
        return $summary;
    }

    public function importMerchantsExcelInternal(User $user, array $row, $objectsMap = null) {

        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }

            if ($sheet['name']) {
                $id = null;
                if ($objectsMap) {
                    $id = $sheet['id'];
                    unset($sheet['id']);
                } else {
                    $test = Merchant::find($sheet['id']);

                    if (!$test) {
                        $id = $sheet['id'];
                        unset($sheet['id']);
                    }
                }

                $categoriesData = explode(",", $sheet['categories']);
                unset($sheet['categories']);
                $image = $sheet['icon'];
                //dd($sheet['id']);
                unset($sheet['icon']);
                $shipping = [];
                if (isset($sheet['envio1'])) {
                    $envio1 = $sheet['envio1'];
                    unset($sheet['envio1']);
                    if ($envio1) {
                        array_push($shipping, ['id' => -1, "price" => $envio1]);
                    }
                } else {
                    dd($sheet);
                }
                if (isset($sheet['envio2'])) {
                    $envio1 = $sheet['envio2'];
                    unset($sheet['envio2']);
                    if ($envio1) {
                        array_push($shipping, ['id' => -2, "price" => $envio1]);
                    }
                }
                $results = $this->editMapObject->saveOrCreateObject($user, $sheet, "Merchant");
                $merchant = $results['object'];
                if ($categoriesData) {
                    DB::table('categorizables')->where('categorizable_id', $merchant->id)->where('categorizable_type', "App\\Models\\Merchant")->delete();
                    $categories = Category::whereIn('id', $categoriesData)->get();
                    foreach ($categories as $item) {
                        $item->merchants()->save($merchant);
                    }
                }
                $merchant->lat = $sheet['lat'];
                $merchant->long = $sheet['long'];
                $merchant->slug = $this->slug_url($merchant->name);
                if (count($shipping) > 0) {
                    $attrs = $merchant->attributes;
                    $attrs['shipping'] = $shipping;
                    $merchant->attributes = $attrs;
                }
                if ($image) {
                    if (strpos($image, "https://") !== false) {
                        
                    } else {
                        $image = 'https://s3.us-east-2.amazonaws.com/gohife/public/pets-merchants/' . $image;
                    }
                    $merchant->icon = $image;
                } else {
                    $merchant->icon = 'https://picsum.photos/900/350';
                }
                $merchant->save();
                if ($id) {
                    array_push($objectsMap['products'], ["sheet_id" => $id, "created_id" => $merchant->id]);
                }
            }
        }
    }

    public function importReportsExcelInternal(User $user, array $row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['name'] && $sheet['name'] != 'name') {
                $categoriesData = explode(",", $sheet['categories']);
                unset($sheet['categories']);
                $merchantData = [];
                $image = $sheet['icon'];
                if (array_key_exists('merchant_id', $sheet)) {
                    $merchantData = explode(",", $sheet['merchant_id']);
                    unset($sheet['merchant_id']);
                }
                unset($sheet['icon']);
                $report = Report::find($sheet['id']);
                if (!$report) {
                    unset($sheet['id']);
                }

                $results = $this->editMapObject->saveOrCreateObject($user, $sheet, "Report");
                $report = $results['object'];
                if ($categoriesData) {
                    DB::table('categorizables')->where('categorizable_id', $report->id)->where('categorizable_type', "App\\Models\\Report")->delete();
                    $categories = Category::whereIn('id', $categoriesData)->get();
                    foreach ($categories as $item) {
                        $item->reports()->save($report);
                    }
                }
                if ($merchantData) {
                    DB::table('merchant_report')->where('report_id', $report->id)->delete();
                    $merchants = Merchant::whereIn('id', $merchantData)->get();
                    foreach ($merchants as $item) {
                        $item->reports()->save($report);
                    }
                }
                if ($image) {
                    if (strpos($image, "https://") !== false) {
                        
                    } else {
                        $image = 'https://s3.us-east-2.amazonaws.com/gohife/public/pets-report/' . $image;
                    }
                    $report->icon = $image;
                } else {
                    $report->icon = 'https://picsum.photos/900/350';
                }
                $report->lat = rand(4527681, 4774930) / 1000000;
                $report->long = rand(-74185612, -74035612) / 1000000;
                $report->slug = $this->slug_url($report->name);
                $report->save();
            }
        }
    }

    public function importFilesExcelInternal(User $user, array $row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['file'] && $sheet['file'] != 'file') {
                if (strpos($sheet['file'], "https://") !== false) {
                    
                } else {
                    $sheet['file'] = 'https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/' . $sheet['file'];
                }
                $file = FileM::find($sheet['id']);
                if (!$file) {
                    $file = new FileM();
                }
                $file->fill($sheet);
                $file->save();
            }
        }
    }

    public function importCategoriesExcelInternal(User $user, array $row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['name'] && $sheet['name'] != 'name') {
                if ($sheet['icon']) {
                    if (strpos($sheet['icon'], "https://") !== false) {
                        
                    } else {
                        $sheet['icon'] = 'https://s3.us-east-2.amazonaws.com/gohife/public/pets-categories/' . $sheet['icon'];
                    }
                } else {
                    $sheet['icon'] = 'https://picsum.photos/900/300';
                }
                if ((!isset($sheet['id'])) || !$sheet['id']) {
                    $sheet['id'] = -1;
                }
                $category = Category::find($sheet['id']);
                if (!$category) {
                    $category = new Category;
                }
                $sheet['url'] = $this->slug_url($sheet['name']);
                $sheet['isActive'] = true;
                if ($sheet['id'] == -1) {
                    unset($sheet['id']);
                }
                $category->fill($sheet);
                $category->save();
            }
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

    public function importArticlesExcelInternal(User $user, array $row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['id'] && $sheet['id'] != 'id') {
                $categoriesData = explode(",", $sheet['categories']);
                unset($sheet['categories']);
                $article = Article::find($sheet['id']);
                if (!$article) {
                    $article = new Article($sheet);
                }
                $image = $sheet['icon'];
                if ($image) {
                    if (strpos($image, "https://") !== false) {
                        
                    } else {
                        $image = 'https://s3.us-east-2.amazonaws.com/gohife/public/pets-banners/' . $image;
                    }
                    $article->icon = $image;
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

    public function importMerchantsAvailabilitiesExcelInternal(User $user, array $row,$objectsMap = null) {
        $headers = $row[0];

        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['from'] && $sheet['from'] != 'from') {
                if ($objectsMap) {
                    $result = $this->getIdFromMap($objectsMap, 'merchants', $sheet['object_id']);
                    if ($result) {
                        $sheet['object_id'] = $result;
                    }
                }
                $availability = Availability::find($sheet['id']);
                if (!$availability) {
                    unset($sheet['id']);
                }
                $this->editBooking->addAvailabilityObject($sheet, $user);
            }
        }
    }

    public function importProductsExcelInternal(User $user, array $row, $objectsMap = null) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                if ($headers[$key]) {
                    $sheet[$headers[$key]] = $value;
                }
            }
            if ($sheet['name'] && $sheet['name'] != 'name') {
                $product = null;
                $product_id = null;
                $isnew = true;
                if ($objectsMap) {
                    $product_id = $sheet['id'];
                    unset($sheet['id']);
                }
                if (isset($sheet['id'])) {
                    $product = Product::find($sheet['id']);
                }
                $image = $sheet['imagen'];

                $merchantsData = explode(",", $sheet['merchant_id']);
                $result = [];
                if ($product) {
                    $isnew = false;
                    $result = $this->editProduct->checkAccessProduct($user, $sheet["id"]);
                } else {
                    unset($sheet['id']);
                    $product = new Product();
                    foreach ($merchantsData as $k => $item) {
                        if ($objectsMap) {
                            $result = $this->getIdFromMap($objectsMap, 'merchants', $item);
                            if ($result) {
                                $merchantsData[$k] = $result;
                                $item = $result;
                            }
                        }
                        $result = $this->editProduct->checkAccessAdminMerchant($user, $item);
                        if ($result['access'] == false) {
                            unset($merchantsData[$k]);
                        }
                    }
                }
                unset($sheet['merchant_id']);
                unset($sheet['imagen']);
                $categoriesData = explode(",", $sheet['categories']);
                unset($sheet['categories']);
                $product->fill($sheet);
                $product->slug = $this->slug_url($product->name);
                $product->save();
                if ($product_id) {
                    array_push($objectsMap['products'], ["sheet_id" => $product_id, "created_id" => $product->id]);
                }

                if ($categoriesData) {
                    DB::table('categorizables')->where('categorizable_id', $product->id)->where('categorizable_type', "App\\Models\\Product")->delete();
                    $categories = Category::whereIn('id', $categoriesData)->get();
                    foreach ($categories as $item) {
                        $product->categories()->save($item);
                    }
                }
                if ($merchantsData) {
                    DB::table('merchant_product')->where('product_id', $product->id)->delete();
                    $merchants = Merchant::whereIn('id', $merchantsData)->get();
                    foreach ($merchants as $item) {
                        $product->merchants()->save($item);
                    }
                }
                FileM::where('trigger_id', $product->id)->where('type', 'App\Models\Product')->delete();
                if ($image) {
                    $files = explode(",", $image);
                    foreach ($files as $value) {
                        $imageData = explode(".", $value);
                        $image = 'https://s3.us-east-2.amazonaws.com/gohife/public/pets-products/' . $merchantsData[0] . '/' . $value;
                        $ext = $imageData[count($imageData) - 1];
                        $file = FileM::where("trigger_id", $product->id)->where("file", $image)->first();
                        if (!$file) {
                            FileM::create([
                                'user_id' => $user->id,
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
                            'user_id' => $user->id,
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

    public function importProductVariantsExcelInternal(User $user, $row, $newObjects = null) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['sku'] && $sheet['sku'] != 'sku') {
                $variant = null;
                if ($newObjects) {
                    $sheet['product_id'] = $this->getIdFromMap($newObjects, "products", $sheet['product_id']);
                } else {
                    $variant = ProductVariant::find($sheet['id']);
                }

                if (!$variant) {
                    unset($sheet['id']);
                }
                $attributes = $sheet['attributes'];
                unset($sheet['attributes']);
                $results = $this->editProduct->createOrUpdateVariant($user, $sheet);
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

    private function getIdFromMap($objectsMap, $type, $product_id) {
        if (isset($objectsMap[$type])) {
            foreach ($objectsMap[$type] as $value) {
                if ($value['sheet_id'] == $product_id) {
                    return $value['created_id'];
                }
            }
        }
        return null;
    }

    public function importProductsQuickExcelInternal(User $user, array $row) {
        $headers = $row[0];
        foreach ($row as $item) {
            $sheet = null;
            foreach ($item as $key => $value) {
                $sheet[$headers[$key]] = $value;
            }
            if ($sheet['id'] && $sheet['id'] != 'id') {
                unset($sheet['name']);
                $this->editProduct->createOrUpdateVariant($user, $sheet);
            }
        }
    }

}
