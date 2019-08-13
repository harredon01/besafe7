<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Booking;

class EditOrderBooking {

    const MODEL_PATH = 'App\\Models\\';
    const OBJECT_ORDER = 'Order';
    const OBJECT_ORDER_REQUEST = 'OrderRequest';
    const ORDER_PAYMENT = 'order_payment';
    const PAYMENT_APPROVED = 'payment_approved';
    const PAYMENT_DENIED = 'payment_denied';
    const PLATFORM_NAME = 'food';
    const TRANSACTION_CONDITION = 'transaction';
    const ORDER_PAYMENT_REQUEST = 'order_payment_request';

    public function addDiscounts(User $user, Order $order) {
//        Cart::session($user->id)->removeConditionsByType(self::PLATFORM_NAME);
//        $order->orderConditions()->where("type", self::PLATFORM_NAME)->delete();
//        Cart::session($user->id)->removeConditionsByType(self::TRANSACTION_CONDITION);
//        $order->orderConditions()->where("type", self::TRANSACTION_CONDITION)->delete();
//        $items = $order->items;
//        $conditions = [];
//        $requiresShipping = 0;
//        if ($order->merchant_id == 1299) {
//            foreach ($items as $value) {
//                $attributes = json_decode($value->attributes, true);
//                if ($value->quantity > 10) {
//                    $control2 = floor($value->quantity / 11);
//                    $buyers = 1;
//                    if (array_key_exists("multiple_buyers", $attributes)) {
//                        if ($attributes['multiple_buyers']) {
//                            $buyers = $attributes['buyers'];
//                        }
//                    }
//                    $discount = ($control2 * $buyers * self::UNIT_LOYALTY_DISCOUNT);
//                    $condition = new OrderCondition(array(
//                        'name' => "Por cada 11 días recibe un descuento de 11 mil pesos",
//                        'target' => "subtotal",
//                        'type' => self::PLATFORM_NAME,
//                        'value' => "-" . $discount,
//                        'total' => $discount,
//                    ));
//                    array_push($conditions, $condition);
//                    $order->orderConditions()->save($condition);
//                    $condition2 = new CartCondition(array(
//                        'name' => $condition->name,
//                        'type' => $condition->type,
//                        'target' => $condition->target, // this condition will be applied to cart's subtotal when getSubTotal() is called.
//                        'value' => $condition->value,
//                        'order' => 1
//                    ));
//                    Cart::session($user->id)->condition($condition2);
//                }
//            }
//        }

        return array("status" => "success", "message" => "Conditions added", "order" => $order);
    }

    public function approvePayment(Payment $payment) {
        
    }

    public function denyPayment(Payment $payment) {
        
    }

    public function pendingPayment(Payment $payment) {
        
    }

    public function getTransactionTotal($total) {
        return ($total * 0.0349 + 900);
    }

    public function approveOrder(Order $order) {
        $data = array();
        $items = $order->items;
        foreach ($items as $item) {
            $data = json_decode($item->attributes, true);
            $item->attributes = $data;

            if (array_key_exists("type", $data)) {
                if ($data['type'] == "Booking") {
                    $id = $data['id'];
                    $booking = Booking::find($id);
                    if ($booking) {
                        $attributes = $booking->options;
                        $attributes['order_id'] = $order->id;
                        $attributes['item_id'] = $item->id;
                        $attributes['payer'] = $order->user_id;
                        $attributes['paid'] = date("Y-m-d h:m:s");
                        $updateData = [
                            "options" => $attributes,
                            "total_paid" => $item->priceSumConditions,
                            "updated_at" => date_create()
                        ];
                        Booking::where("id",$id)->update($updateData);
                    }
                }
            }
        }
        $order->status = "approved";
        $order->save();
        return array("status" => "success", "message" => "Order approved, subtasks completed", "order" => $order);
    }

}
