<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    const STATUS_PENDING = 'pending';

    const STATUS_SUCCESS = 'success';

    const STATUS_FAILED = 'failed';

    const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'booking_id',
        'midtrans_transaction_id',
        'snap_token',
        'payment_method',
        'payment_channel',
        'amount',
        'status',
        'raw_response',
        'expired_at',
        'paid_at',
        'webhook_received_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expired_at' => 'datetime',
            'paid_at' => 'datetime',
            'webhook_received_at' => 'datetime',
            'raw_response' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
