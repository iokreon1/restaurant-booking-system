<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\KirimiChannel;
use App\Notifications\Messages\KirimiMessage;
use App\Services\MessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $paymentUrl = '',
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
        $messageService = app(MessageService::class);

        $content = $messageService->compose('booking_created', $this->booking, [
            'payment_url' => $this->paymentUrl,
        ]);

        return (new KirimiMessage)->content($content);
    }
}
