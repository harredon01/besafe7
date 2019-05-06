<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\Order;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderApproved extends Mailable
{
    use Queueable, SerializesModels;

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
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order,User $user,$shipping)
    {
        $totalCost = 0 ;
        $totalTax = 0 ;
        $totalPlatform = 0 ;
        $totalDiscount = 0 ;
        $payment = $order->payments()->where("user_id",$user->id)->first();
        foreach($order->items as $item){
            $totalCost += ($item->quantity*$item->cost) ;
            $totalTax += ($item->quantity*$item->tax) ;
            $totalPlatform += ($item->quantity*($item->price-($item->cost+$item->tax))) ;
        }
        foreach ($order->orderConditions as $item){
            
        }
        $order->payment = $payment;
        $order->totalTax = $totalTax;
        $order->totalCost = $totalCost;
        $order->totalPlatform = $totalPlatform;
        $order->totalDiscount = $totalDiscount;
        $this->order = $order;
        $this->user = $user;
        $this->shipping = $shipping;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.orders.approved');
    }
}
