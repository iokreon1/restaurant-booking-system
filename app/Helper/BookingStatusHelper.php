<?php

namespace App\Helper;

use App\Models\Booking;

class BookingStatusHelper
{
    public const BADGE_FALLBACK_CLASSES = 'rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-700';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            Booking::BOOKING_STATUS_PENDING => 'Menunggu',
            Booking::BOOKING_STATUS_CONFIRMED => 'Dikonfirmasi',
            Booking::BOOKING_STATUS_SEATED => 'Duduk',
            Booking::BOOKING_STATUS_PREPARING => 'Menyiapkan',
            Booking::BOOKING_STATUS_COMPLETED => 'Selesai',
            Booking::BOOKING_STATUS_CANCELLED => 'Dibatalkan',
            Booking::BOOKING_STATUS_NO_SHOW => 'Tidak hadir',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function badgeClasses(): array
    {
        return [
            Booking::BOOKING_STATUS_PENDING => 'rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase text-amber-900',
            Booking::BOOKING_STATUS_CONFIRMED => 'rounded-full bg-sky-100 px-2.5 py-1 text-[10px] font-bold uppercase text-sky-900',
            Booking::BOOKING_STATUS_SEATED => 'rounded-full bg-cyan-100 px-2.5 py-1 text-[10px] font-bold uppercase text-cyan-900',
            Booking::BOOKING_STATUS_PREPARING => 'rounded-full bg-violet-100 px-2.5 py-1 text-[10px] font-bold uppercase text-violet-900',
            Booking::BOOKING_STATUS_COMPLETED => 'rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase text-emerald-900',
            Booking::BOOKING_STATUS_CANCELLED => 'rounded-full bg-red-100 px-2.5 py-1 text-[10px] font-bold uppercase text-red-900',
            Booking::BOOKING_STATUS_NO_SHOW => 'rounded-full bg-slate-200 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-800',
        ];
    }

    public static function label(string $status): string
    {
        return self::labels()[$status] ?? $status;
    }

    public static function badgeClassesFor(string $status): string
    {
        return self::badgeClasses()[$status] ?? self::BADGE_FALLBACK_CLASSES;
    }
}
