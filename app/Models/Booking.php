<?php

namespace App\Models;

use Rinvex\Bookings\Models\BookableBooking;

class Booking extends BookableBooking {

    protected $table = 'bookable_bookings';

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
