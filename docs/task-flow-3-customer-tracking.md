---
title: Flow Brief — Flow 3: Customer Tracking
flow: flow-3
units: [9]
---

## Flow 3: Customer Tracking

**Actor:** Customer (tamu, tanpa akun)
**Prasyarat:** Flow 1 dan Flow 2 selesai — booking sudah ada dan bisa berganti status.

### Unit yang Tercakup

| # | Unit Kerja |
|---|------------|
| 9 | Halaman tracking status booking |

### Schema yang Terlibat

**`bookings`** — sumber data tracking
Kolom: `id`, `booking_reference`, `booking_date`, `booking_time`, `duration_minutes`, `guest_count`, `items` (JSONB), `total_amount`, `status`, `updated_at`
Query: cari by `booking_reference` — ada UNIQUE index di kolom ini

**`transactions`** — status pembayaran
Kolom: `id`, `booking_id`, `amount`, `status`, `expired_at`
JOIN ke `bookings` by `booking_id`

**`tables`** — info meja yang di-booking
Kolom: `id`, `table_number`, `capacity`
JOIN ke `bookings` by `table_id` — hanya untuk display

### Acceptance Criteria

**Unit 9 — Halaman tracking status booking**
- [ ] Customer bisa input `booking_reference` untuk melihat status booking-nya
- [ ] Halaman menampilkan: status booking, detail item pesanan (render dari JSONB `items`), tanggal dan waktu booking, nomor meja, jumlah tamu, total pembayaran, dan status transaksi
- [ ] Status booking ditampilkan dalam bahasa yang mudah dipahami — bukan raw enum (`pending_payment` → "Menunggu Pembayaran", `confirmed` → "Booking Dikonfirmasi", dst.)
- [ ] Jika `booking_reference` tidak ditemukan, tampilkan pesan error yang jelas
- [ ] Halaman bisa diakses tanpa login (public route)
- [ ] Mobile-first

### Constraints
- Tracking berbasis `booking_reference` saja — tidak perlu akun atau verifikasi tambahan
- Semua data di halaman ini read-only; customer tidak bisa mengubah apapun

### Out of Scope
- Pre-Flow, Flow 1, Flow 2, Post-Flow
- Pembatalan booking oleh customer
- Riwayat semua booking customer (fitur ini butuh akun — Out of Scope MVP)
