{{--
    Modal overlay untuk halaman dashboard / Livewire.

    Props:
      show (bool) — tampilkan overlay.
      closeAction (string) — nama method Livewire untuk tutup, default closeModal.
      maxWidth (string) — kelas lebar panel, default max-w-lg.
      panelClass (string) — kelas tambahan pada panel (mis. max-h-[92vh]).

    Slot opsional:
      heading — judul (HTML bebas, mis. <h3>).

    Slot default:
      konten (form, dll.).

    Contoh:
    <x-dashboard.modal :show="$showModal" closeAction="closeModal">
        <x-slot name="heading">
            <h3 class="text-lg font-bold text-[#0A1628]">{{ $editingId ? 'Ubah' : 'Baru' }}</h3>
        </x-slot>
        <form wire:submit="save">...</form>
    </x-dashboard.modal>
--}}

@props([
    'show' => false,
    'closeAction' => 'closeModal',
    'maxWidth' => 'max-w-lg',
    'panelClass' => 'max-h-[90vh]',
])

@if ($show)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        wire:click="{{ $closeAction }}"
        wire:keydown.escape.window="{{ $closeAction }}"
    >
        <div
            @class([
                'w-full overflow-y-auto rounded-[16px] border border-[#D1D9D9] bg-white p-6 shadow-xl',
                $maxWidth,
                $panelClass,
            ])
            wire:click.stop
        >
            @isset($heading)
                {{ $heading }}
            @endisset
            {{ $slot }}
        </div>
    </div>
@endif
