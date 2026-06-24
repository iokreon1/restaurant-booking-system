---
title: Flow Brief — Flow 2: Admin Approval
flow: flow-2
units: [8]
---

## Flow 2: Admin Approval

**Actor:** Admin (`manager`, `staff`)
**Prasyarat:** Flow 1 selesai — booking dengan `status = 'paid'` sudah masuk ke sistem.

### Unit yang Tercakup

| # | Unit Kerja |
|---|------------|
| 8 | Approval/reject booking & update status booking |

### Schema yang Terlibat

**`bookings`** — data booking yang perlu di-approve
Kolom: `id`, `user_id`, `table_id`, `items` (JSONB), `booking_date`, `booking_time`, `duration_minutes`, `guest_count`, `total_amount`, `status`, `booking_reference`, `updated_at`
Transisi status di flow ini:
- `paid` → `confirmed` (approve)
- `paid` → `cancelled` (reject)
- `confirmed` → `completed` (mark selesai)

**`users`** — data customer untuk ditampilkan ke admin
Kolom: `id`, `name`, `phone`
Hanya untuk display, tidak dimodifikasi di flow ini

**`tables`** — informasi meja yang di-booking
Kolom: `id`, `table_number`, `capacity`
Hanya untuk display, tidak dimodifikasi di flow ini

**`transactions`** — bukti pembayaran
Kolom: `id`, `booking_id`, `amount`, `status`
Hanya untuk display sebagai verifikasi bahwa pembayaran sudah masuk, tidak dimodifikasi di flow ini

### Acceptance Criteria

**Unit 8 — Approval/reject booking & update status booking**
- [ ] Admin melihat daftar booking dengan `status = 'paid'`, sorted by `booking_date` ascending
- [ ] Detail booking menampilkan: nama customer, nomor HP, nomor meja, waktu booking, item pesanan (render dari JSONB `items`), total pembayaran, dan status transaksi
- [ ] Admin bisa approve booking: `bookings.status` → `confirmed`
- [ ] Admin bisa reject booking: `bookings.status` → `cancelled`, disertai alasan yang dicatat
- [ ] Admin bisa mark booking sebagai selesai: `bookings.status` → `completed` — hanya tersedia untuk booking `confirmed` yang `booking_date + booking_time` sudah lewat
- [ ] Daftar booking bisa difilter berdasarkan `status` dan `booking_date`
- [ ] Aksi approve/reject/complete hanya bisa dilakukan oleh role yang sesuai (sesuai PRD admin)
- [ ] Setiap perubahan status tercatat di `updated_at`

### Constraints
- `manager` dan `staff` bisa approve/reject; batas akses spesifik sesuai PRD admin
- Setiap perubahan status booking yang relevan akan menjadi trigger notifikasi WhatsApp ke customer — pengiriman notifikasinya dihandle di Post-Flow, bukan di sini
- Tidak ada refund logic di flow ini

### Out of Scope
- Pre-Flow (auth & master data)
- Flow 1 (customer booking)
- Flow 3 (customer tracking)
- Post-Flow (pengiriman notifikasi WhatsApp)
- Refund setelah cancel
