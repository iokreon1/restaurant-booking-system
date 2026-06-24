<div>
    <button
        class="inline-flex items-center justify-center rounded-full border border-[#D1D9D9] bg-white px-5 py-2.5 text-sm font-bold text-[#025864] shadow-sm hover:bg-[#eff5f5]"
        type="button"
        wire:click="openModal"
    >
        Ubah status pembayaran
    </button>

    <x-dashboard.modal :show="$showModal" closeAction="closeModal" maxWidth="max-w-md">
        <x-slot name="heading">
            <h3 class="text-lg font-bold text-[#0A1628]">Ubah status pembayaran</h3>
            <p class="mt-1 text-sm text-slate-500">Pilih status pembayaran terbaru untuk booking ini.</p>
        </x-slot>

        <form class="mt-6 flex flex-col gap-4" wire:submit="save">
            <div>
                <label class="text-xs font-bold uppercase text-slate-500" for="payment-status-modal">Status pembayaran</label>
                <select
                    class="mt-1 w-full rounded-lg border-0 bg-[#eff5f5] px-4 py-2.5 text-sm outline-none ring-1 ring-transparent focus:ring-2 focus:ring-[#025864]/25"
                    id="payment-status-modal"
                    wire:model="payment_status"
                >
                    @foreach ($paymentStatuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('payment_status')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    class="rounded-full border border-[#D1D9D9] bg-white px-5 py-2.5 text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50"
                    type="button"
                    wire:click="closeModal"
                >
                    Batal
                </button>
                <button
                    class="rounded-full bg-[#025864] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    Simpan perubahan
                </button>
            </div>
        </form>
    </x-dashboard.modal>
</div>
