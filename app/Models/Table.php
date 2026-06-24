<?php

namespace App\Models;

use Database\Factories\TableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Table extends Model
{
    /** @use HasFactory<TableFactory> */
    use HasFactory;

    protected $fillable = ['table_number', 'capacity', 'location_description', 'status'];

    const STATUS_AVAILABLE = 'available';

    const STATUS_BOOKED = 'booked';

    const STATUS_MAINTENANCE = 'maintenance';

    const STATUS_INACTIVE = 'inactive';

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function latestBooking(): HasOne
    {
        return $this->hasOne(Booking::class)->latestOfMany('booking_date');
    }
}
