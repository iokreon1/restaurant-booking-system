<div>
    <x-microsite.header :title="'Dapur Nabilah'"
                        :show-cart="false"
                        :center-title="true"/>

    <div class="space-y-4 p-4">
        <section class="flex flex-col gap-4">
            <div class="w-full h-48 rounded-2xl overflow-hidden shadow">
                <img alt="Authentic Indonesian Rendang" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCwTiLMNT1GtsDIsjwK0M7oBmugkgXbBMj-_9luR43wr_LLQ0Q-_pqTe7mXujOyOtSBuK9mL3XB7tberopps05_K0gltPhLZoxmZc-TvirbWSXNhYkvanJVrAhmQm6m3R2yTWL6IinzT3yf2vfrMulcQxbweLOsbf3MRo8D1JIzfQjgLd8PJok0ChSmgDIeMxcmKbRd6jWR7WwiJ--FfqN5gwEL8i9j-_15eFHwr7lCGSUlboo8E-ycWH1dD9iY1Gm7i1SyCg-e7yQ"/>
            </div>
            <div class="bg-surface-container-lowest p-6 rounded-2xl shadow border border-outline-variant/20">
                <h1 class="text-2xl font-extrabold text-primary leading-tight mb-2">
                    Cita Rasa Otentik Nabilah
                </h1>
                <p class="text-on-surface-variant text-sm mb-6 leading-relaxed">
                    Nikmati hidangan warisan bumbu rempah pilihan langsung dari dapur kami.
                </p>
                <a href="{{ route('microsite.menu') }}" class="block w-full bg-primary text-white py-3.5 rounded-xl font-bold text-base text-center hover:brightness-105 transition-all active:scale-95">
                    Pesan Sekarang
                </a>
            </div>
        </section>
        <section class="grid grid-cols-1 gap-4">
            <div class="grid grid-cols-1 gap-3">
                <div class="bg-surface-container-lowest p-5 rounded-2xl border border-outline-variant/10 shadow flex items-start gap-4">
                    <div class="bg-surface-container w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-primary text-2xl" data-icon="eco">eco</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-on-surface mb-1">Bahan Segar Setiap Hari</h3>
                        <p class="text-xs text-on-surface-variant leading-normal">Kualitas terbaik yang dipetik langsung dari petani lokal.</p>
                    </div>
                </div>
                <div class="bg-surface-container-lowest p-5 rounded-2xl border border-outline-variant/10 shadow flex items-start gap-4">
                    <div class="bg-surface-container w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-primary text-2xl" data-icon="history_edu">history_edu</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-on-surface mb-1">Resep Warisan Keluarga</h3>
                        <p class="text-xs text-on-surface-variant leading-normal">Rahasia bumbu turun-temurun menjaga keaslian rasa.</p>
                    </div>
                </div>
                <div class="bg-surface-container-lowest p-5 rounded-2xl border border-outline-variant/10 shadow flex items-start gap-4">
                    <div class="bg-surface-container w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-primary text-2xl" data-icon="volunteer_activism">volunteer_activism</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-on-surface mb-1">Pelayanan Ramah &amp; Hangat</h3>
                        <p class="text-xs text-on-surface-variant leading-normal">Menyambut Anda layaknya tamu istimewa di rumah sendiri.</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="space-y-3">
            <div class="flex justify-between items-center px-1">
                <h2 class="text-lg font-bold text-primary">Menu Pilihan Favorit</h2>
                <a href="{{ route('microsite.menu') }}" class="text-xs font-bold text-secondary flex items-center gap-1">
                    Lihat Semua <span class="material-symbols-outlined text-xs" data-icon="arrow_forward">arrow_forward</span>
                </a>
            </div>
            @if (count($this->featuredItems) > 0)
                <div class="grid grid-cols-1 gap-3">
                    @foreach ($this->featuredItems as $item)
                        <div wire:key="landing-menu-item-{{ $item['id'] }}" class="bg-surface-container-lowest overflow-hidden rounded-2xl border border-outline-variant/10 shadow flex">
                            <div class="w-32 h-32 flex-shrink-0">
                                <img alt="{{ $item['image_alt'] }}" class="w-full h-full object-cover" src="{{ $item['image'] }}"/>
                            </div>
                            <div class="p-4 flex flex-col justify-between flex-grow min-w-0">
                                <div>
                                    <div class="flex items-start justify-between gap-2">
                                        <h4 class="font-bold text-sm">{{ $item['name'] }}</h4>
                                        @if ($item['is_sold_out'])
                                            <span class="shrink-0 rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700">Sold Out</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-on-surface-variant line-clamp-2 mt-1">{{ $item['description'] }}</p>
                                </div>
                                <span class="text-primary font-bold text-sm">{{ $item['price_label'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-surface-container-lowest overflow-hidden rounded-2xl border border-outline-variant/10 shadow px-4 py-6 text-center">
                    <p class="text-sm font-bold text-on-surface">Menu belum tersedia.</p>
                    <p class="text-xs text-on-surface-variant mt-1">Tambahkan data menu untuk menampilkan favorit di landing page.</p>
                </div>
            @endif
        </section>
        <section class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/10 shadow">
            <span class="text-secondary font-bold tracking-widest uppercase text-[10px] mb-2 block">Cerita Kami</span>
            <h2 class="text-xl font-bold text-primary mb-3">Melestarikan Warisan Kuliner Melalui Setiap Sajian</h2>
            <div class="w-full h-40 rounded-xl overflow-hidden mb-4">
                <img alt="Interior Dapur Nabilah" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCV4mmdMieOrDNGabh_T9_A2OfcpNGr4r-LfL1ckHiQdAID-z2BNFvo-nalE9VRl9OTlLDvD-tNifxIa6ObqTa19HyOv55ol-fhdF2hG9zlergr1GDwaW9AlLnIAMVo9ISZRrZGWI38HpTTAEjfVXV7ZJiG5v-SUj1IgS6BCe8A6muOzd6Y4SCrM57miRmOrRqjhpF1L8xMpMPcfonH9Al2LqA9apDsxhHWaJhZEyCHVxZalUJAUnSD9VauG2qr2QFBPeueKHrisPs"/>
            </div>
            <p class="text-on-surface-variant text-sm leading-relaxed mb-6">
                Dapur Nabilah lahir dari kerinduan akan masakan rumah yang kaya rempah. Kami berkomitmen untuk menjaga tradisi kuliner Indonesia.
            </p>
            <div class="flex gap-8 pt-4 border-t border-outline-variant/20">
                <div>
                    <span class="text-xl font-bold text-primary block">15+</span>
                    <span class="text-[10px] text-on-surface-variant font-medium">Resep Rahasia</span>
                </div>
                <div>
                    <span class="text-xl font-bold text-primary block">10th</span>
                    <span class="text-[10px] text-on-surface-variant font-medium">Dedikasi Rasa</span>
                </div>
            </div>
        </section>
        <section class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/10 shadow space-y-5">
            <h2 class="text-lg font-bold text-primary">Kunjungi Kami</h2>
            <div class="space-y-4">
                <div class="flex gap-3">
                    <span class="material-symbols-outlined text-secondary text-xl" data-icon="location_on">location_on</span>
                    <div>
                        <h4 class="font-bold text-on-surface text-sm">Alamat</h4>
                        <p class="text-xs text-on-surface-variant">Jl. Raya Jakarta No. 123, Jakarta Selatan</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="material-symbols-outlined text-secondary text-xl" data-icon="schedule">schedule</span>
                    <div>
                        <h4 class="font-bold text-on-surface text-sm">Jam Operasional</h4>
                        <p class="text-xs text-on-surface-variant">Senin - Jumat: 10:00 - 22:00</p>
                        <p class="text-xs text-on-surface-variant">Sabtu - Minggu: 09:00 - 23:00</p>
                    </div>
                </div>
            </div>
            <div class="w-full h-40 rounded-xl overflow-hidden border border-outline-variant/20">
                <img alt="Map location" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAkr8fuqD_vrg4WFjWun7Dy4cgeaY81LP5r1LRxTpaI14vsJV5ItJvHh04H3l5Tt2leF81FE-2XHgWmEmMxko5Yn59is_BM-ycmpxGgb5iZ0lyylwcHPdorr2zwf142FEPG1UWxy9p-JmbW8ZEaPAcXwKP09leuktG6r3GJEG6SO45mcHb-joITDUaKf7y3RSp_zXHtm-1StygruAODHcCkLEsFUfZqYKVIh7X8HWpxq4YJSRh0KJ7maqXwmozzxzhrBo2wFmedEUA"/>
            </div>
        </section>
    </div>

    <footer class="mt-4 w-full bg-tertiary text-on-tertiary p-6 pb-24">
        <div class="space-y-6 text-center">
            <span class="text-lg font-bold text-tertiary-fixed">Dapur Nabilah</span>
            <p class="text-xs font-light opacity-80 leading-relaxed">
                Menghadirkan kehangatan masakan Indonesia ke meja makan Anda.
            </p>
            <div class="flex justify-center gap-6">
                <span class="material-symbols-outlined text-lg" data-icon="share">share</span>
                <span class="material-symbols-outlined text-lg" data-icon="chat">chat</span>
                <span class="material-symbols-outlined text-lg" data-icon="camera">camera</span>
            </div>
            <div class="grid grid-cols-2 gap-4 pt-6 border-t border-on-tertiary/10 text-left">
                <div class="space-y-2">
                    <h5 class="font-bold text-tertiary-fixed uppercase text-[10px] tracking-widest">Navigation</h5>
                    <ul class="space-y-1 text-[11px] opacity-70">
                        <li>Menu</li>
                        <li>About Us</li>
                        <li>Reservations</li>
                    </ul>
                </div>
                <div class="space-y-2">
                    <h5 class="font-bold text-tertiary-fixed uppercase text-[10px] tracking-widest">Legal</h5>
                    <ul class="space-y-1 text-[11px] opacity-70">
                        <li>Privacy Policy</li>
                        <li>Terms of Service</li>
                    </ul>
                </div>
            </div>
            <p class="text-[10px] opacity-40 pt-4">© 2024 Dapur Nabilah. All rights reserved.</p>
        </div>
    </footer>


</div>
