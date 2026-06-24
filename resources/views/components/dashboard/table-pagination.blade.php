@props([
    /**
     * @var \Illuminate\Contracts\Pagination\Paginator|\Illuminate\Pagination\AbstractPaginator $paginator
     */
    'paginator',
])

<div class="flex flex-col gap-4 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-slate-600">
        @if ($paginator->total() === 0)
            Tidak ada data.
        @else
            Menampilkan
            <span class="font-semibold text-[#0A1628]">{{ $paginator->firstItem() }}</span>
            –
            <span class="font-semibold text-[#0A1628]">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-[#0A1628]">{{ $paginator->total() }}</span>
            entri
        @endif
    </p>
    @if ($paginator->hasPages())
        <div
            class="[&_button]:rounded-md [&_button]:border-slate-200 [&_button]:text-slate-700 [&_button]:transition-colors [&_button:hover]:border-[#025864]/40 [&_button:hover]:text-[#025864] [&_span[aria-current]]:border-[#025864] [&_span[aria-current]]:bg-[#025864] [&_span[aria-current]]:text-white"
        >
            {{ $paginator->links() }}
        </div>
    @endif
</div>
