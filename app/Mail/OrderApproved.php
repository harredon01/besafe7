<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\Order;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderApproved extends Mailable {

    use Queueable,
        SerializesModels;

    /**
     * The order instance.
     *
     * @var Order
     */
    public $order;

    /**
     * The order instance.
     *
     * @var Order
     */
    public $shipping;

    /**
     * The order instance.
     *
     * @var Order
     */
    public $user;

    /**
     * The order instance.
     *
     * @var Order
     */
    public $invoice;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, User $user, $shipping, $invoice) {
        $totalCost = 0;
        $totalTax = 0;
        $totalPlatform = 0;
        $totalDiscount = 0;
        $productDiscount = 0;
        $taxDiscount = 0;
        $totalDeposit = 0;
        $payment = $order->payments()->where("user_id", $user->id)->first();
        foreach ($order->orderConditions as $item) {
            $string = $item->value;
            $firstCharacter = $string[0];
            if ($firstCharacter == "-") {
                $totalDiscount += $item->total;
            } else {
                $totalPlatform += $item->total;
            }
        }
        foreach ($order->items as $item) {
            $attributes = json_decode($item->attributes, true);
            if (array_key_exists("is_credit", $attributes)) {
                $totalDeposit+= ($item->quantity * $item->cost);
            } else {
                $totalCost += ($item->quantity * $item->cost);
                $totalTax += ($item->quantity * $item->tax);
                $totalPlatform += ($item->quantity * ($item->price - ($item->cost + $item->tax)));
            }
        }
        if ($totalDiscount > 0) {
            $productDiscount = $totalDiscount / 1.08;
            $taxDiscount = $totalDiscount - $productDiscount;
        }
        $order->payment = $payment;
        $order->tax = $totalTax - $taxDiscount;
        $order->totalCost = $totalCost - $productDiscount;
        $order->totalPlatform = $totalPlatform;
        $order->totalDeposit = $totalDeposit;
        $order->discount = $totalDiscount;
        $this->order = $order;
        $this->user = $user;
        $this->invoice = $invoice;
        $this->shipping = $shipping;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->markdown('emails.orders.approved');
    }

}
