<?php

namespace App\Services;

use App\Models\Merchant;
use App\Models\Payment;
use App\Models\Delivery;
use App\Models\Item;
use Illuminate\Support\Facades\Mail;
use App\Mail\StoreReports;
use App\Models\OrderCondition;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Exports\ArrayMultipleSheetExport;
use App\Models\ProductVariant;
use Excel;

class StoreExport {

    const COMMISION_RATE = 0.1;
    const TAX_RATE = 0.08;
    const PACKING_COST = 1400;
    const ORDER_APPORVED_STATUS = 'approved';
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
                            ->whereNotIn('user_id', [1, 77 ])
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

    public function writeFile($data, $title, $sendMail) {
        //dd($data);
        $file = Excel::store(new ArrayMultipleSheetExport($data), "exports/" . $title . ".xls", "local");
        $path = 'exports/' . $title . ".xls";
        //dd($file);
//        $file = Excel::store($title, function($excel) use($data, $title) {
//
//                    $excel->setTitle($title);
//                    // Chain the setters
//                    $excel->setCreator('Hoovert Arredondo')
//                            ->setCompany('Hoovert Arredondo SAS');
//                    // Call them separately
//                    $excel->setDescription('This report is clasified');
//                    foreach ($data as $page) {
//                        $excel->sheet(substr($page["name"], 0, 30), function($sheet) use($page) {
//                            $sheet->fromArray($page["rows"], null, 'A1', true);
//                        });
//                    }
//                });
        $path = 'exports/' . $title . ".xls";
        if ($sendMail) {
            $users = User::whereIn('id', [2, 77])->get();
            Mail::to($users)->send(new StoreReports($path));
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

}
