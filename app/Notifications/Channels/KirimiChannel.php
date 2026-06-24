<?php

namespace App\Notifications\Channels;

use App\External\KirimiService;
use App\Notifications\Messages\KirimiMessage;
use Illuminate\Notifications\Notification;

class KirimiChannel
{
    public function __construct(
        protected KirimiService $kirimiService,
    ) {}

    /**
     * Send the given notification.
     *
     * @return array{success: bool, message: string, data?: array}|null
     */
    public function send(object $notifiable, Notification $notification): ?array
    {
        /** @var KirimiMessage $message */
        $message = $notification->toKirimi($notifiable);

        $phone = $notifiable->routeNotificationFor('kirimi', $notification);

        if (empty($phone)) {
            return null;
        }

        return $this->kirimiService->sendMessage(
            $phone,
            $message->content,
            $message->mediaUrl,
        );
    }
}
