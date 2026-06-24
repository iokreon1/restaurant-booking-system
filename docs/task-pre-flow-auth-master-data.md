---
title: Flow Brief — Pre-Flow: Auth & Master Data Admin
flow: pre-flow
units: [1, 2, 3, 4]
---

## Pre-Flow: Auth & Master Data Admin

**Actor:** Admin (`super_admin`, `manager`, `staff`)
**Prasyarat:** Tidak ada — Pre-Flow adalah fondasi yang harus selesai sebelum semua flow lain bisa berjalan.

### Unit yang Tercakup

| # | Unit Kerja |
|---|------------|
| 1 | Setup autentikasi admin |
| 2 | CRUD menu categories |
| 3 | CRUD menu items |
| 4 | Manajemen meja |

### Schema yang Terlibat

**`users`** — data admin dan customer
Kolom: `id`, `name`, `email`, `phone`, `password`, `role`, `is_active`, `last_login_at`, `created_at`, `updated_at`
Constraint: `email` UNIQUE
Role admin: `super_admin`, `manager`, `staff`
`password` wajib untuk role admin, NULL untuk customer
`is_active = false` → admin tidak bisa login, session langsung di-revoke

**`menu_categories`** — master data kategori menu
Kolom: `id`, `name`, `thumbnail_path`, `sort_order`, `status`, `created_at`, `updated_at`
Constraint: `name` UNIQUE
`sort_order` menentukan urutan tab di halaman menu customer
`status`: `active` / `inactive`
Kategori yang masih punya menu items tidak bisa dihapus (RESTRICT)

**`menu_items`** — master data item menu
Kolom: `id`, `category_id`, `name`, `description`, `price`, `thumbnail_path`, `status`, `sort_order`, `created_at`, `updated_at`
Constraint: `category_id` FK ke `menu_categories` (ON DELETE RESTRICT)
`price` bertipe NUMERIC(10,2) — jangan gunakan floating point
`status`: `available` / `sold_out`
Item yang sudah pernah masuk ke booking tidak bisa dihapus (ditangani di application layer)

**`tables`** — master data meja restoran
Kolom: `id`, `table_number`, `capacity`, `location_description`, `status`, `created_at`, `updated_at`
Constraint: `table_number` UNIQUE
`status`: `active` / `inactive` / `maintenance`
Status `inactive` dan `maintenance` tidak tampil di halaman booking customer
Meja yang masih punya booking aktif tidak bisa dihapus (ditangani di application layer)

### Acceptance Criteria

**Unit 1 — Setup autentikasi admin**
- [ ] Admin bisa login dengan email dan password
- [ ] Middleware auth memproteksi semua route admin
- [ ] Role-based access: `super_admin`, `manager`, `staff` — middleware cek role di controller
- [ ] User dengan `is_active = false` tidak bisa login meskipun password benar
- [ ] Session di-revoke saat admin dinonaktifkan
- [ ] Halaman login menggunakan design system project

**Unit 2 — CRUD menu categories**
- [ ] Halaman index menampilkan tabel: nama, thumbnail, status badge, sort_order, action buttons (edit, hapus)
- [ ] Form create/edit: field `name`, `thumbnail_path` (upload opsional), `sort_order`, `status` toggle
- [ ] `name` harus unique — validasi di backend, tampilkan error yang jelas jika duplikat
- [ ] Kategori yang masih punya menu items tidak bisa dihapus, tampilkan error yang informatif
- [ ] Yang bisa akses: `super_admin` dan `manager`

**Unit 3 — CRUD menu items**
- [ ] Halaman index menampilkan tabel: thumbnail, nama, kategori, harga (format Rp), badge status, action buttons (edit, hapus)
- [ ] Form create/edit: field `name`, `category_id` (dropdown dari kategori aktif), `description`, `price`, `thumbnail_path` (upload dengan preview), `status`, `sort_order`
- [ ] Validasi: `price > 0`, `category_id` harus dari kategori aktif
- [ ] `price` disimpan dan ditampilkan sebagai NUMERIC — tidak boleh ada pembulatan floating point
- [ ] Item yang sudah pernah masuk ke booking tidak bisa dihapus (RESTRICT), tampilkan error yang informatif, tawarkan toggle status sebagai alternatif
- [ ] Badge status: `available` (hijau), `sold_out` (abu-abu) sesuai design system
- [ ] Yang bisa akses: `super_admin` dan `manager`

**Unit 4 — Manajemen meja**
- [ ] Halaman index menampilkan tabel: nomor meja, kapasitas, lokasi, badge status (active/inactive/maintenance), action buttons (edit, hapus)
- [ ] Form create/edit: field `table_number`, `capacity`, `location_description` (opsional), `status`
- [ ] `table_number` harus unique — validasi di backend
- [ ] Status `inactive` dan `maintenance` tidak tampil di halaman booking customer
- [ ] Meja yang masih punya booking aktif tidak bisa dihapus, tampilkan error yang jelas
- [ ] Yang bisa akses: `super_admin` dan `manager`

### Constraints
- Semua route admin harus diproteksi middleware auth
- Role-based access: `super_admin` punya akses penuh, `manager` bisa kelola menu dan meja, `staff` hanya bisa lihat dan operasional terbatas
- Upload thumbnail menggunakan storage lokal (public disk)
- Semua deletion constraint (kategori punya items, meja punya booking aktif) ditangani di application layer dengan error message yang informatif

### Out of Scope
- Flow 1: Customer booking
- Flow 2: Admin approval
- Flow 3: Customer tracking
- Post-Flow: Notifikasi automasi & scheduler auto-cancel
- Customer-facing pages (katalog menu, form booking)
