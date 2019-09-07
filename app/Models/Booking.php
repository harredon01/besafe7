<?php

namespace App\Models;

use Rinvex\Bookings\Models\BookableBooking;

class Booking extends BookableBooking {

    protected $table = 'bookable_bookings';
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'id', 
        'bookable_id',
        'bookable_type',
        'customer_id',
        'customer_type',
        'starts_at',
        'ends_at',
        'price',
        'quantity',
        'total_paid',
        'currency',
        'formula',
        'canceled_at',
        'options',
        'notes',
    ];
    protected $rules = [
        'bookable_id' => 'required|integer',
        'bookable_type' => 'required|string',
        'customer_id' => 'required|integer',
        'customer_type' => 'required|string',
        'starts_at' => 'required|date',
        'ends_at' => 'required|date',
        'price' => 'required|numeric',
        'quantity' => 'required|integer',
        'total_paid' => 'required',
        'currency' => 'required|alpha|size:3',
        'formula' => 'nullable|array',
        'canceled_at' => 'nullable|date',
        'options' => 'nullable',
        'notes' => 'nullable|string|max:10000',
    ];

    protected static function boot() {
        parent::bootTraits();

        static::validating(function (self $bookableAvailability) {
            if (!$bookableAvailability->price) {
                $formula = $bookableAvailability->calculatePrice(
                        $bookableAvailability->bookable, $bookableAvailability->starts_at, $bookableAvailability->ends_at
                );
                $price = $formula['total_price'];
                $currency = $formula['currency'];
            } else {
                $price = $bookableAvailability->price;
                $formula = $bookableAvailability->formula;
                $currency = $bookableAvailability->currency;
            }
            $bookableAvailability->currency = $currency;
            $bookableAvailability->formula = $formula;
            $bookableAvailability->price = $price;
            $bookableAvailability->quantity = 1;
            $bookableAvailability->total_paid = 0;
        });
    }

}
