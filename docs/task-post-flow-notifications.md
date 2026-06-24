---
title: Flow Brief — Post-Flow: Notifikasi & Automasi
flow: post-flow
units: [10, 11]
---

## Post-Flow: Notifikasi & Automasi

**Actor:** Sistem (background jobs, webhook handler)
**Prasyarat:** Flow 1, Flow 2, dan Flow 3 selesai — semua transisi status booking sudah berjalan.

### Unit yang Tercakup

| # | Unit Kerja |
|---|------------|
| 10 | Notifikasi WhatsApp otomatis & webhook handler Midtrans |
| 11 | Scheduler auto-cancel expired booking |

### Schema yang Terlibat

**`bookings`** — sumber trigger notifikasi dan target auto-cancel
Kolom: `id`, `user_id`, `table_id`, `booking_reference`, `booking_date`, `booking_time`, `status`, `updated_at`
Status yang relevan:
- `pending_payment` → target auto-cancel jika transaksi expired
- `paid` → trigger notifikasi ke customer (pembayaran diterima) dan ke admin (ada booking baru)
- `confirmed` → trigger notifikasi ke customer (booking dikonfirmasi)
- `cancelled` → trigger notifikasi ke customer (booking dibatalkan)

**`transactions`** — sumber data expired & target update status auto-cancel
Kolom: `id`, `booking_id`, `midtrans_transaction_id`, `amount`, `status`, `expired_at`
Query scheduler: `status = 'pending' AND expired_at < NOW()`
Transisi status di unit ini: `pending` → `expired`

**`users`** — nomor HP untuk pengiriman WhatsApp
Kolom: `id`, `name`, `phone`
JOIN ke `bookings` by `user_id`

**`tables`** — nomor meja untuk isi pesan notifikasi
Kolom: `id`, `table_number`
JOIN ke `bookings` by `table_id` — hanya untuk display di pesan WhatsApp

### Acceptance Criteria

**Unit 10 — Notifikasi WhatsApp otomatis & webhook handler Midtrans**
- [ ] Webhook endpoint Midtrans menerima callback dan memvalidasi signature sebelum memproses
- [ ] Jika callback `payment_type: settlement`: `transactions.status` → `paid`, `bookings.status` → `paid` — dilakukan secara atomik dalam satu transaksi database
- [ ] Setelah status booking berubah menjadi `paid`: WhatsApp dikirim ke customer berisi `booking_reference`, ringkasan pesanan, tanggal dan waktu booking
- [ ] Setelah admin konfirmasi (`confirmed`): WhatsApp dikirim ke customer bahwa booking telah dikonfirmasi
- [ ] Setelah admin reject atau sistem cancel (`cancelled`): WhatsApp dikirim ke customer dengan keterangan pembatalan
- [ ] Jika callback Midtrans duplikat (idempotent): tidak ada perubahan status ganda — cek `transactions.status` sebelum update

**Unit 11 — Scheduler auto-cancel expired booking**
- [ ] Scheduler berjalan secara periodik via cron job
- [ ] Query: cari semua `transactions` dengan `status = 'pending' AND expired_at < NOW()`
- [ ] Untuk setiap transaksi yang ditemukan: update `transactions.status` → `expired` dan `bookings.status` → `cancelled` secara atomik dalam satu transaksi database
- [ ] Scheduler bersifat idempotent — jika dijalankan dua kali pada data yang sama, tidak ada double-cancel
- [ ] WhatsApp dikirim ke customer bahwa booking di-cancel karena pembayaran expired

### Constraints
- Webhook Midtrans wajib divalidasi signature-nya sebelum diproses — jangan proses callback tanpa validasi
- Scheduler harus idempotent
- Perubahan status di unit ini harus atomik — tidak boleh ada state di mana `transactions.status = 'expired'` tapi `bookings.status` masih `pending_payment`

### Out of Scope
- Pre-Flow, Flow 1, Flow 2, Flow 3
- Notifikasi manual oleh admin
- Retry logic untuk gagal kirim WhatsApp (Fase 2)
- Refund otomatis setelah auto-cancel (Fase 2)
