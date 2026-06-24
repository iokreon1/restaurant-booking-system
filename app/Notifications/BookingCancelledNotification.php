<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\KirimiChannel;
use App\Notifications\Messages\KirimiMessage;
use App\Services\MessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
    ) {}

    /**
     * @return array<int, class-string>
     */
    public function via(object $notifiable): array
    {
        return [KirimiChannel::class];
    }

    public function toKirimi(object $notifiable): KirimiMessage
    {
        $content = app(MessageService::class)->compose('booking_cancelled', $this->booking);

        return (new KirimiMessage)->content($content);
    }
}
