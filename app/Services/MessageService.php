<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Number;

class MessageService
{
    /** @var array<string, string> */
    private array $templates = [];

    public function __construct()
    {
        $this->loadTemplates();
    }

    /**
     * Compose a message for the given event using booking data.
     *
     * @param  array<string, string>  $extraData  Additional placeholders to merge
     */
    public function compose(string $event, Booking $booking, array $extraData = []): string
    {
        $template = $this->getTemplate($event);

        $placeholders = $this->buildPlaceholders($booking, $extraData);

        return str_replace(
            array_map(fn (string $key): string => "{{$key}}", array_keys($placeholders)),
            array_values($placeholders),
            $template,
        );
    }

    /**
     * Get the raw template for an event.
     */
    public function getTemplate(string $event): string
    {
        if (! isset($this->templates[$event])) {
            throw new \InvalidArgumentException("Message template for event [{$event}] not found.");
        }

        return $this->templates[$event];
    }

    /**
     * Get all available event names.
     *
     * @return array<int, string>
     */
    public function availableEvents(): array
    {
        return array_keys($this->templates);
    }

    /**
     * @return array<string, string>
     */
    private function buildPlaceholders(Booking $booking, array $extraData = []): array
    {
        $booking->loadMissing(['user', 'table']);

        return array_merge([
            'customer_name' => $booking->user?->name ?? '-',
            'booking_reference' => $booking->booking_reference,
            'booking_date' => $booking->booking_date?->format('d M Y') ?? '-',
            'booking_time' => $booking->booking_time ?? '-',
            'guest_count' => (string) $booking->guest_count,
            'table_number' => (string) ($booking->table?->table_number ?? '-'),
            'total_amount' => Number::format((float) $booking->total_amount, locale: 'id'),
            'ordered_menu' => $this->formatOrderedMenu($booking->items ?? []),
            'payment_url' => '-',
            'cancellation_reason' => $booking->cancellation_reason ?? '-',
        ], $extraData);
    }

    /**
     * @param  array<int, array{name?: string, quantity?: int, subtotal?: float|int|string}>  $items
     */
    private function formatOrderedMenu(array $items): string
    {
        if ($items === []) {
            return '-';
        }

        return collect($items)
            ->map(function (array $item): string {
                $name = (string) ($item['name'] ?? 'Menu');
                $quantity = (int) ($item['quantity'] ?? 0);
                $subtotal = Number::format((float) ($item['subtotal'] ?? 0), locale: 'id');

                return sprintf('• %s x%d (Rp %s)', $name, $quantity, $subtotal);
            })
            ->implode("\n");
    }

    private function loadTemplates(): void
    {
        $path = resource_path('data/message.json');

        if (! File::exists($path)) {
            return;
        }

        /** @var array<int, array{event: string, message: string}> $entries */
        $entries = json_decode(File::get($path), true);

        foreach ($entries as $entry) {
            $this->templates[$entry['event']] = $entry['message'];
        }
    }
}
