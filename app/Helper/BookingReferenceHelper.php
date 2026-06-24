<?php

namespace App\Helper;

use App\Models\Booking;
use Illuminate\Support\Str;

class BookingReferenceHelper
{
    public function generate(): string
    {
        do {
            $reference = 'BK-'.Str::upper(Str::random(8));
        } while (Booking::query()->where('booking_reference', $reference)->exists());

        return $reference;
    }
}
