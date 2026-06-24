<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    const BOOKING_STATUS_PENDING = 'pending';

    const BOOKING_STATUS_CONFIRMED = 'confirmed';

    const BOOKING_STATUS_SEATED = 'seated';

    const BOOKING_STATUS_PREPARING = 'preparing';

    const BOOKING_STATUS_COMPLETED = 'completed';

    const BOOKING_STATUS_CANCELLED = 'cancelled';

    const BOOKING_STATUS_NO_SHOW = 'no_show';

    const PAYMENT_STATUS_PENDING = 'pending';

    const PAYMENT_STATUS_PAID = 'paid';

    const PAYMENT_STATUS_EXPIRED = 'expired';

    const PAYMENT_STATUS_REFUNDED = 'refunded';

    const TYPE_MANUAL = 'manual';

    const TYPE_MICROSITE = 'microsite';

    protected $fillable = [
        'booking_reference',
        'type',
        'user_id',
        'table_id',
        'items',
        'total_amount',
        'booking_date',
        'booking_time',
        'guest_count',
        'booking_status',
        'payment_status',
        'cancellation_reason',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'booking_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Booking $booking): void {
            $booking->transaction()->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }
}
