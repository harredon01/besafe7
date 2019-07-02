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
use App\Models\ProductVariant;
use Excel;

class StoreExport {

    const COMMISION_RATE = 0.1;
    const TAX_RATE = 0.08;
    const PACKING_COST = 1150;
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
        $results = [];
        $merchantsObserved = [];
        $merchants = Merchant::whereIn("id", [1299, 1300, 1301])->get();
        foreach ($merchants as $merchant) {

            $orders = Order::where('status', self::ORDER_APPORVED_STATUS)
                            ->where('merchant_id', $merchant->id)
                            ->whereBetween('updated_at', [$startDate, $endDate])
                            ->whereNotIn('user_id', [2, 77, 3,82,161])
                            ->with(['payments.user', 'items', 'orderConditions'])->get();

            $merchantResults = $this->exportOrderInvoices($orders, $merchant->name);
            $commision += $merchantResults[self::COMMISION_WO_DISCOUNT];
            $meDiscount += $merchantResults[self::DICOUNT_COMMISION];
            $comissionSub += $merchantResults[self::COMMISION_W_DISCOUNT];

            $cost += $merchantResults[self::COST_WO_DISCOUNT];
            $productDiscount += $merchantResults[self::DICOUNT_COST];
            $costSub += $merchantResults[self::COST_W_DISCOUNT];
            $tax += $merchantResults[self::TAX_WO_DISCOUNT];
            $taxDiscount += $merchantResults[self::DICOUNT_TAX];
            $taxSub += $merchantResults[self::TAX_W_DISCOUNT];
            $deposits += $merchantResults[self::DEPOSIT];
            $lunches += $merchantResults[self::LUNCHES];
            $packing += $merchantResults[self::PACKING];
            $packingNum += $merchantResults[self::PACKING_AMOUNT];
            $subtotal += $merchantResults[self::SUBTOTAL_ORDER];
            $shipping += $merchantResults[self::SHIPPING];
            $discount += $merchantResults[self::DISCOUNT];
            $total += $merchantResults[self::TOTAL_ORDER];
            $paymentsSubtotal += $merchantResults[self::SUBTOTAL_PAYMENTS];
            $transactionCost += $merchantResults[self::TRANSACTION_COST];
            $paymentsTotal += $merchantResults[self::TOTAL_PAYMENTS];

            $marketingDiscount += $merchantResults[self::DISCOUNT_MARKETING];
            $operationDiscount += $merchantResults[self::DISCOUNT_VOLUME];
            $providerDiscount += $merchantResults[self::DISCOUNT_PROVIDER];
            $providerOperationDiscount += $merchantResults[self::DISCOUNT_PROVIDER_OPERATION];
            $providerMarketingDiscount += $merchantResults[self::DISCOUNT_PROVIDER_MARKETING];
            $meDiscount += $merchantResults[self::DISCOUNT_HOOV];
            $meOperationDiscount += $merchantResults[self::DISCOUNT_HOOV_OPERATION];
            $meMarketingDiscount += $merchantResults[self::DISCOUNT_HOOV_MARKETING];
            $paymentsCount += $merchantResults[self::PAYMENTS_AMOUNT];
            $amountOrders += $merchantResults[self::ORDERS_AMOUNT];
            $page = [
                "name" => $merchant->name,
                "rows" => $merchantResults['operationData']
            ];
            unset($merchantResults['operationData']);

            array_push($results, $page);

            array_push($merchantsObserved, $merchantResults);
        }
        $summary = [
            "name" => "totals",
            self::LUNCHES => $lunches,
            self::COST_WO_DISCOUNT => $cost,
            self::DICOUNT_COST => $productDiscount,
            self::COST_W_DISCOUNT => $costSub,
            self::TAX_WO_DISCOUNT => $tax,
            self::DICOUNT_TAX => $taxDiscount,
            self::TAX_W_DISCOUNT => $taxSub,
            self::COMMISION_WO_DISCOUNT => $commision,
            self::DICOUNT_COMMISION => $meDiscount,
            self::COMMISION_W_DISCOUNT => $comissionSub,
            self::DEPOSIT => $deposits,
            self::PACKING => $packing,
            self::PACKING_AMOUNT => $packingNum,
            self::DISCOUNT_VOLUME => $operationDiscount,
            self::SUBTOTAL_ORDER => $subtotal,
            self::DISCOUNT_MARKETING => $marketingDiscount,
            self::SHIPPING => $shipping,
            self::TOTAL_ORDER => $total,
            self::SUBTOTAL_PAYMENTS => $paymentsSubtotal,
            self::TRANSACTION_COST => $transactionCost,
            self::TOTAL_PAYMENTS => $paymentsTotal,
            self::DISCOUNT => $discount,
            self::DISCOUNT_VOLUME => $operationDiscount,
            self::DISCOUNT_MARKETING => $marketingDiscount,
            self::DISCOUNT_PROVIDER => $providerDiscount,
            self::DISCOUNT_PROVIDER_OPERATION => $providerOperationDiscount,
            self::DISCOUNT_PROVIDER_MARKETING => $providerMarketingDiscount,
            self::DISCOUNT_HOOV => $meDiscount,
            self::DISCOUNT_HOOV_OPERATION => $meOperationDiscount,
            self::DISCOUNT_HOOV_MARKETING => $meMarketingDiscount,
            self::PAYMENTS_AMOUNT => $paymentsCount,
            self::ORDERS_AMOUNT => $amountOrders
        ];
        array_push($merchantsObserved, $summary);
        $page = [
            "name" => "Totals",
            "rows" => $merchantsObserved
        ];
        array_unshift($results, $page);
        $this->writeFile($results, "Total operacion_" . time(), true);
    }

    public function writeFile($data, $title, $sendMail) {
        //dd($data);
        $file = Excel::create($title, function($excel) use($data, $title) {

                    $excel->setTitle($title);
                    // Chain the setters
                    $excel->setCreator('Hoovert Arredondo')
                            ->setCompany('Hoovert Arredondo SAS');
                    // Call them separately
                    $excel->setDescription('This report is clasified');
                    foreach ($data as $page) {
                        $excel->sheet(substr($page["name"], 0, 30), function($sheet) use($page) {
                            $sheet->fromArray($page["rows"], null, 'A1', true);
                        });
                    }
                })->store('xlsx', storage_path('app/exports'));
        $path = 'exports/' . $file->filename . "." . $file->ext;
        if ($sendMail) {
            $users = User::whereIn('id', [2, 77])->get();
            Mail::to($users)->send(new StoreReports($path));
        } else {
            return $path;
        }
    }

    public function dailyInvoices($ordersArray) {
        $orders = Order::whereIn('id', $ordersArray)
                        ->with(['payments.user', 'items', 'orderConditions'])->get();
        $results = $this->exportOrderInvoices($orders, "Facturas-diarias");
        $page = [
            "name" => "Facturas diarias",
            "rows" => $results['operationData']
        ];
        return $this->writeFile([$page], "Facturas-diarias" . time(),false);
    }

    private function exportOrderInvoices($orders, $name) {
        $shipping = 0;
        $tax = 0;
        $discount = 0;
        $commision = 0;
        $deposits = 0;
        $packing = 0;
        $packingNum = 0;
        $transactionCost = 0;
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
        $taxDiscount = 0;
        $subtotal = 0;
        $total = 0;
        $paymentsSubtotal = 0;
        $paymentsTotal = 0;
        $paymentsCount = 0;
        $title = array([]);
        $titleO = array([]);
        $title2 = array([]);
        $title3 = array([]);
        $ordersData = [];
        $ordersData2 = [];
        $conditionsData = [];
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
            $oMarketingDiscount = 0;
            $OordersData = [];
            $OconditionsData = [];
            $OitemsData = [];
            $OpaymentsData = [];
            $oOperationDiscount = 0;
            $orderArray = $order->toArray();
            foreach ($order->orderConditions as $condition) {
                $conditionArray = $condition->toArray();
                $string = $condition->value;
                $firstCharacter = $string[0];
                if ($firstCharacter == "-") {
                    if ($condition->condition_id) {
                        $marketingDiscount += $condition->total;
                        $oMarketingDiscount += $condition->total;
                    } else {
                        $operationDiscount += $condition->total;
                        $oOperationDiscount += $condition->total;
                    }
                } else {
                    $shipping += $condition->total;
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
                $tTax = 0;
                $tCost = 0;
                $tShipping = 0;
                $tDeposit = 0;
                $tComision = 0;
                $buyers = 1;
                $attributes = json_decode($item->attributes, true);
                if (array_key_exists('buyers', $attributes)) {
                    $buyers = (double) $attributes['buyers'];
                }

                if (array_key_exists("is_credit", $attributes)) {
                    $tDeposit = $item->priceSumConditions;
                    $deposits += $tDeposit;
                    $oDeposit += $tDeposit;
                } else {
                    $tCost = $item->quantity * $item->cost;
                    $lunches += ($buyers * $item->quantity);
                    $oLunches += ($buyers * $item->quantity);
                    $cost += $tCost;
                    $oCost += $tCost;
                    if (array_key_exists('shipping', $attributes)) {
                        $tShipping = $item->quantity * $buyers * ((double) $attributes['shipping']);

                        $shipping += $tShipping;
                        $oShipping += $tShipping;
                    }
                    $tTax = $item->quantity * $item->tax;
                    $tax += $tTax;
                    $oTax += $tTax;
                    if (array_key_exists('shipping', $attributes)) {
                        if (array_key_exists('requires_credits', $attributes)) {
                            $tComision = $item->quantity * ($item->price - ($item->cost + $item->tax + ((double) $attributes['shipping'])));
                        } else {

                            $packing += $item->quantity * self::PACKING_COST * $buyers;
                            $oPacking += $item->quantity * self::PACKING_COST * $buyers;
                            $packingNum += $item->quantity * $buyers;
                            $oPackingNum += $item->quantity * $buyers;
                            $tComision = $item->quantity * ($item->price - ($item->cost + (self::PACKING_COST * $buyers) + $item->tax + ((double) $attributes['shipping'])));
                        }
                    } else {
                        $tComision = $item->quantity * ($item->price - ($item->cost + $item->tax));
                    }
                    $commision += $tComision;
                    $oCommision += $tComision;
                }
                $itemArray = $item->toArray();
                $finalItem = [
                    "Producto" => $itemArray['name'],
                    "Precio" => $itemArray['price'],
                    "Almuerzos" => ($buyers * $item->quantity),
                    "Cantidad" => $itemArray['quantity'],
                    "Costo" => $itemArray['cost'],
                    "Impuesto" => $itemArray['tax'],
                    "CostoTotal" => $tCost,
                    "ImpuestoTotal" => $tTax,
                    "envioTotal" => $tShipping,
                    "comisionTotal" => $tComision,
                    "Total" => $itemArray['priceSumConditions'],
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
                $paymentsSubtotal += $payment->subtotal;
                $oPaymentsSubtotal += $payment->subtotal;
                $paymentsTotal += $payment->total;
                $oPaymentsTotal += $payment->total;
                $transactionCost += ($payment->total - $payment->subtotal);
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
            $oMeMarketingDiscount = 0;
            $oMeOperationDiscount = 0;
            $oMeDiscount = 0;
            $oProviderDiscount = 0;
            $oProductDiscount = 0;
            $oTaxDiscount = 0;
            $oProviderOperationDiscount = 0;
            $oProviderMarketingDiscount = 0;
            if ($oOperationDiscount > 0 || $oMarketingDiscount > 0) {

                if (true) {
                    $oProviderOperationDiscount = $oOperationDiscount / (1 + self::COMMISION_RATE);
                } else {
                    $oProviderOperationDiscount = $oOperationDiscount;
                }
                $oMeOperationDiscount = $oOperationDiscount - $oProviderOperationDiscount;
                if (false) {
                    $oProviderMarketingDiscount += $oMarketingDiscount / 2;
                } else {
                    $oProviderMarketingDiscount += $oMarketingDiscount;
                }
                $oMeMarketingDiscount = $oMarketingDiscount - $oProviderMarketingDiscount;
                $oMeDiscount = $oMeOperationDiscount + $oMeMarketingDiscount;
                $oProviderDiscount = $oProviderMarketingDiscount + $oProviderOperationDiscount;
                $oProductDiscount = $oProviderDiscount / (1 + self::TAX_RATE);
                $oTaxDiscount = $oProviderDiscount - $oProductDiscount;
            }
            $oCostSub = $oCost - $oProductDiscount;
            $oTaxSub = $oTax - $oTaxDiscount;
            $oComissionSub = $oCommision - $oMeDiscount;

            $orderItem = [
                "Orden" => $orderArray['id'],
                self::LUNCHES => $oLunches,
                self::COST_WO_DISCOUNT => $oCost,
                self::DICOUNT_COST => $oProductDiscount,
                self::COST_W_DISCOUNT => $oCostSub,
                self::TAX_WO_DISCOUNT => $oTax,
                self::DICOUNT_TAX => $oTaxDiscount,
                self::TAX_W_DISCOUNT => $oTaxSub,
                self::COMMISION_WO_DISCOUNT => $oCommision,
                self::DICOUNT_COMMISION => $oMeDiscount,
                self::COMMISION_W_DISCOUNT => $oComissionSub,
                self::DEPOSIT => $oDeposit,
                self::PACKING => $oPacking,
                self::PACKING_AMOUNT => $oPackingNum,
                self::DISCOUNT_VOLUME => $oOperationDiscount,
                self::SUBTOTAL_ORDER => $orderArray['subtotal'],
                self::SHIPPING => $oShipping,
                self::DISCOUNT_MARKETING => $oMarketingDiscount,
                self::TOTAL_ORDER => $orderArray['total'],
                self::SUBTOTAL_PAYMENTS => $oPaymentsSubtotal,
                self::TRANSACTION_COST => $oTransactionCost,
                self::TOTAL_PAYMENTS => $oPaymentsTotal,
                self::DISCOUNT => $oOperationDiscount + $oMarketingDiscount,
                self::DISCOUNT_VOLUME => $oOperationDiscount,
                self::DISCOUNT_MARKETING => $oMarketingDiscount,
                self::DISCOUNT_PROVIDER => $oProviderDiscount,
                self::DISCOUNT_PROVIDER_OPERATION => $oProviderOperationDiscount,
                self::DISCOUNT_PROVIDER_MARKETING => $oProviderMarketingDiscount,
                self::DISCOUNT_HOOV => $oMeDiscount,
                self::DISCOUNT_HOOV_OPERATION => $oMeOperationDiscount,
                self::DISCOUNT_HOOV_MARKETING => $oMeMarketingDiscount,
                self::PAYMENTS_AMOUNT => count($order->payments)
            ];
            $discount += ($oOperationDiscount + $oMarketingDiscount);
            $titleO = array(array_keys($orderItem));
            array_push($ordersData, $orderItem);
            $ordersData2 = array_merge($ordersData2, $titleO, [$orderItem], $title, $OitemsData, $title2, $OconditionsData, $title3, $OpaymentsData);
        }
        if ($operationDiscount > 0 || $marketingDiscount > 0) {
            if (true) {
                $providerOperationDiscount = $operationDiscount / (1 + self::COMMISION_RATE);
            } else {
                $providerOperationDiscount = $operationDiscount;
            }
            $meOperationDiscount = $operationDiscount - $providerOperationDiscount;
            if (false) {
                $providerMarketingDiscount += $marketingDiscount / 2;
            } else {
                $providerMarketingDiscount += $marketingDiscount;
            }
            $meMarketingDiscount = $marketingDiscount - $providerMarketingDiscount;
            $meDiscount = $meOperationDiscount + $meMarketingDiscount;
            $providerDiscount = $providerMarketingDiscount + $providerOperationDiscount;
            $productDiscount = $providerDiscount / (1 + self::TAX_RATE);
            $taxDiscount = $providerDiscount - $productDiscount;
        }
        $costSub = $cost - $productDiscount;
        $taxSub = $tax - $taxDiscount;
        $comissionSub = $commision - $meDiscount;
        $operationData = array_merge($ordersData, $ordersData2);
        $summary = [
            "name" => $name,
            self::COST_WO_DISCOUNT => $cost,
            self::LUNCHES => $lunches,
            self::DICOUNT_COST => $productDiscount,
            self::COST_W_DISCOUNT => $costSub,
            self::TAX_WO_DISCOUNT => $tax,
            self::DICOUNT_TAX => $taxDiscount,
            self::TAX_W_DISCOUNT => $taxSub,
            self::COMMISION_WO_DISCOUNT => $commision,
            self::DICOUNT_COMMISION => $meDiscount,
            self::COMMISION_W_DISCOUNT => $comissionSub,
            self::DEPOSIT => $deposits,
            self::PACKING => $packing,
            self::PACKING_AMOUNT => $packingNum,
            self::DISCOUNT_VOLUME => $operationDiscount,
            self::SUBTOTAL_ORDER => $subtotal,
            self::DISCOUNT_MARKETING => $marketingDiscount,
            self::SHIPPING => $shipping,
            self::TOTAL_ORDER => $total,
            self::SUBTOTAL_PAYMENTS => $paymentsSubtotal,
            self::TRANSACTION_COST => $transactionCost,
            self::TOTAL_PAYMENTS => $paymentsTotal,
            self::DISCOUNT_VOLUME => $operationDiscount,
            self::DISCOUNT => $discount,
            self::DISCOUNT_MARKETING => $marketingDiscount,
            self::DISCOUNT_PROVIDER => $providerDiscount,
            self::DISCOUNT_PROVIDER_OPERATION => $providerOperationDiscount,
            self::DISCOUNT_PROVIDER_MARKETING => $providerMarketingDiscount,
            self::DISCOUNT_HOOV => $meDiscount,
            self::DISCOUNT_HOOV_OPERATION => $meOperationDiscount,
            self::DISCOUNT_HOOV_MARKETING => $meMarketingDiscount,
            self::PAYMENTS_AMOUNT => $paymentsCount,
            self::ORDERS_AMOUNT => count($orders),
            "operationData" => $operationData
        ];
        return $summary;
    }

}
