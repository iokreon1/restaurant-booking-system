{{--
    Tabel data dashboard: kartu putih + scroll horizontal.

    Slot wajib:
      thead — satu atau lebih <tr> header (dengan <th>).

    Slot default:
      isi <tbody> (biasanya @forelse … @empty … @endforelse).

    Slot opsional:
      footer — mis. pagination di bawah tabel.

    Contoh:
    <x-dashboard.data-table>
        <x-slot name="thead">
            <tr class="border-b border-slate-100 bg-[#eff5f5] text-[11px] font-bold uppercase tracking-wider text-slate-500">
                <th class="px-6 py-4">Kolom</th>
            </tr>
        </x-slot>
        @forelse ($rows as $row)
            <tr wire:key="row-{{ $row->id }}">...</tr>
        @empty
            <tr><td class="px-6 py-12 text-center text-slate-500" colspan="3">Kosong</td></tr>
        @endforelse
        <x-slot name="footer">
            {{ $paginator->links() }}
        </x-slot>
    </x-dashboard.data-table>
--}}

@props([
    'wrapperClass' => '',
])

<div @class(['overflow-hidden rounded-[12px] border border-[#D1D9D9] bg-white card-shadow', $wrapperClass])>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left">
            <thead>
                {{ $thead }}
            </thead>
            <tbody class="divide-y divide-slate-100">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @isset($footer)
        {{ $footer }}
    @endisset
</div>
