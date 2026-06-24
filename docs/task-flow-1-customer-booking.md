---
title: Flow Brief — Flow 1: Customer Booking
flow: flow-1
units: [5, 6, 7, 8]
---

## Flow 1: Customer Booking

**Actor:** Customer (tamu, tanpa akun)
**Prasyarat:** Pre-Flow selesai — data menu, meja, dan autentikasi admin sudah ada.

### Unit yang Tercakup

| # | Unit Kerja |
|---|------------|
| 5 | Halaman katalog menu |
| 6 | Form booking |
| 7 | Halaman status booking |
| 8 | Integrasi Midtrans & notifikasi WhatsApp |

### Schema yang Terlibat

**`menu_categories`** — untuk menampilkan katalog
Kolom: `id`, `name`, `thumbnail_path`, `sort_order`, `status`
Query: filter `status = 'active'`, ordered by `sort_order`

**`menu_items`** — item menu dalam katalog
Kolom: `id`, `category_id`, `name`, `price`, `thumbnail_path`, `status`
Query: filter `status = 'available'`, group by category

**`tables`** — untuk cek ketersediaan meja
Kolom: `id`, `table_number`, `capacity`, `status`
Query: filter `status = 'active'`, cek overlap di `bookings` by `table_id + booking_date + booking_time + duration_minutes`

**`users`** — customer tamu
Dibuat otomatis saat booking (role = `customer`)
Kolom kunci: `id`, `name`, `phone`, `role`
`password` nullable untuk customer

**`bookings`** — menyimpan data reservasi
Kolom kunci: `id`, `user_id`, `table_id`, `items` (JSONB), `booking_date`, `booking_time`, `duration_minutes`, `guest_count`, `total_amount`, `status`, `booking_reference`
Status awal saat dibuat: `pending_payment`
`booking_reference` format: `RST-YYYYMMDD-XXXX`, UNIQUE
`items` JSONB snapshot: `[{"id": 1, "name": "Nasi Goreng", "price": 45000, "qty": 2}]`

**`transactions`** — data pembayaran Midtrans
Kolom kunci: `id`, `booking_id`, `midtrans_transaction_id`, `amount`, `status`, `expired_at`
Status: `pending`, `paid`, `failed`, `expired`
Dibuat bersamaan saat booking dibuat (relasi 1:1 dengan `bookings`)

### Acceptance Criteria

**Unit 5 — Halaman katalog menu**
- [ ] Halaman menampilkan semua kategori aktif dengan thumbnail, sorted by `sort_order`
- [ ] Setiap kategori menampilkan item dengan `status = 'available'`
- [ ] Item dengan `status = 'sold_out'` ditampilkan tapi tidak bisa dipilih
- [ ] Customer bisa tambah/kurang jumlah setiap item
- [ ] Total harga diupdate real-time saat item dipilih
- [ ] Layout mobile-first

**Unit 6 — Form booking**
- [ ] Customer mengisi: nama, nomor HP, tanggal, jam, durasi, jumlah tamu
- [ ] Sistem menampilkan daftar meja yang tersedia untuk slot yang dipilih
- [ ] Validasi ketersediaan meja mengecek overlap `booking_date + booking_time + duration_minutes + status IN ('paid','confirmed')` di level database
- [ ] Customer memilih meja dari daftar tersedia
- [ ] Setelah submit: booking dibuat dengan `status = 'pending_payment'`, transaksi dibuat di Midtrans, customer diarahkan ke halaman pembayaran Midtrans Snap
- [ ] Jika tidak ada meja tersedia di slot yang dipilih, tampilkan pesan yang jelas

**Unit 7 — Halaman status booking**
- [ ] Halaman publik (tanpa login) diakses via `booking_reference` dari URL
- [ ] Menampilkan ringkasan pesanan: meja, waktu, daftar menu, dan total
- [ ] Menampilkan status terkini booking (pending_payment, paid, confirmed, dll)
- [ ] Jika status `pending_payment`: tampilkan countdown timer dan tombol "Bayar Sekarang"
- [ ] Jika status `cancelled`: tampilkan alasan pembatalan
- [ ] Data sensitif tidak ditampilkan lengkap (misal: nomor HP hanya 4 digit terakhir)
- [ ] Mobile-first, informasi utama above the fold

**Unit 8 — Integrasi Midtrans & notifikasi WhatsApp**
- [ ] Midtrans Snap terintegrasi dan terbuka dari tombol "Bayar Sekarang" di halaman status booking
- [ ] Setelah pembayaran berhasil: `transactions.status` → `paid`, `bookings.status` → `paid`
- [ ] Webhook endpoint menerima notifikasi status (settlement, expire, cancel)
- [ ] Validasi signature key pada setiap webhook callback
- [ ] Webhook handler idempotent — callback duplikat tidak mengubah status dua kali
- [ ] Customer menerima pesan WhatsApp berisi `booking_reference`, ringkasan pesanan, dan detail waktu booking
- [ ] Jika pembayaran gagal: `transactions.status` → `failed`, `bookings.status` tetap `pending_payment`

### Constraints
- Customer tidak perlu membuat akun — booking berbasis sesi anonim dengan `booking_reference`
- Full payment di muka sebelum admin konfirmasi
- Mobile-first
- Validasi ketersediaan meja dilakukan di level database untuk mencegah double booking

### Out of Scope
- Pre-Flow (auth & master data admin)
- Flow 2: Admin approval
- Flow 3: Customer tracking
- Post-Flow: Notifikasi automasi & scheduler auto-cancel
