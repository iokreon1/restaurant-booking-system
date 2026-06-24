# PRD: Restaurant Booking & Ordering System

## Problem Statement

Restoran saat ini mengandalkan proses reservasi dan pemesanan secara manual —
melalui telepon, WhatsApp langsung, atau walk-in — yang rentan terhadap
kesalahan pencatatan, double booking, dan keterlambatan konfirmasi. Tidak ada
sistem yang memvalidasi ketersediaan meja secara real-time, sehingga staf
harus melakukan pengecekan manual yang rawan human error.

Di sisi lain, tamu yang sudah melakukan reservasi sering tidak datang (no-show)
tanpa konfirmasi pembatalan, menyebabkan slot meja terbuang percuma. Tidak ada
mekanisme untuk meminimalisir no-show karena tidak ada komitmen finansial di
awal dari tamu.

## Target User

**Primary: Customer (Tamu Restoran)**
Tamu yang ingin merencanakan kunjungan ke restoran. Mayoritas mengakses melalui
HP (mobile-first). Tidak terbiasa dengan aplikasi yang rumit — proses harus
bisa diselesaikan dalam < 5 menit tanpa perlu membuat akun.

**Secondary: Admin — Manager**
Mengelola operasional harian: approval booking, kelola menu dan meja, lihat
transaksi, dan pantau laporan. Membutuhkan visibilitas real-time atas status
semua meja dan booking yang masuk.

**Secondary: Admin — Staff**
Operasional di lantai restoran: melihat daftar booking hari ini, mengubah
status meja, dan menandai pesanan selesai. Akses terbatas — tidak perlu
mengubah data master.

**Profil teknis pengguna:**
- Customer: literasi teknologi beragam, mayoritas terbiasa dengan aplikasi
  e-commerce dan WhatsApp, mengakses via browser HP
- Admin: menggunakan laptop untuk pekerjaan administratif, HP untuk operasional
  di lantai

## Current Workaround

**Proses Booking (Reservasi Meja):**
1. Tamu menelepon atau WhatsApp restoran untuk menanyakan ketersediaan meja
2. Staf mengecek buku reservasi manual lalu membalas tamu
3. Tamu mengkonfirmasi secara lisan — tanpa komitmen finansial
4. Staf mencatat booking di buku manual atau spreadsheet

Kelemahan: tidak ada validasi real-time, rentan double booking saat dua staf
menerima permintaan bersamaan, dan tidak ada mekanisme pengurang no-show karena
tamu bisa batal kapan saja tanpa konsekuensi.

**Proses Pemesanan Menu (Order):**
1. Tamu datang dan menerima buku menu fisik
2. Tamu menyebutkan pesanan ke staf secara lisan
3. Staf mencatat pesanan di kertas nota
4. Staf menyerahkan nota ke dapur secara manual

Kelemahan: pesanan rawan salah catat, tidak ada catatan digital pesanan per
tamu, dan tidak ada data historis pemesanan yang bisa digunakan untuk analisis
menu populer atau preferensi customer.

**Dampak Keseluruhan:**
Kedua proses berjalan sepenuhnya manual dan terpisah — tidak ada satu sistem
yang menghubungkan data booking dengan data pesanan, sehingga manager tidak
punya visibilitas real-time atas operasional restoran.

## Proposed Solution

Platform web (mobile-first) yang memungkinkan tamu melakukan reservasi meja
secara mandiri — mulai dari memilih menu, mengisi data diri, memilih meja
yang tersedia, hingga melakukan pembayaran penuh di muka — dalam satu alur
yang seamless tanpa perlu membuat akun.

Sistem memvalidasi ketersediaan meja secara real-time, mengharuskan full
payment di muka untuk mengurangi no-show, dan mengirimkan konfirmasi otomatis
via WhatsApp. Admin mendapatkan panel untuk mengelola seluruh operasional.

## Assumptions

- Tamu memiliki akses internet dan nomor WhatsApp yang aktif
- Restoran hanya memiliki satu cabang (single-branch)
- Satu sesi booking hanya untuk satu meja
- Pembayaran adalah full payment di muka — tidak ada sistem DP
- Durasi standar makan dikonfigurasi oleh admin dan berlaku sama untuk
  semua booking

## User Flow

### Flow 1: Customer melakukan booking

Buka halaman menu → Pilih item menu yang diinginkan → Isi data diri
(nama, email, nomor HP) → Pilih tanggal dan jam kunjungan → Pilih meja
yang tersedia → Review ringkasan pesanan → Bayar via Midtrans →
Terima konfirmasi via WhatsApp

### Flow 2: Admin approval dan operasional

Admin terima notifikasi booking baru → Approve booking →
Sistem kirim konfirmasi WhatsApp ke tamu → Tamu datang →
Staff tandai booking selesai → Slot meja dibebaskan

### Flow 3: Customer tracking status pesanan

Customer terima link tracking di WhatsApp → Buka link →
Lihat status pesanan secara kronologis (paid → confirmed → completed)
tanpa perlu login

## Core Features (MVP)

1. **Customer booking flow** — alur pilih menu → isi data diri → pilih meja
   → bayar, dapat diselesaikan dalam < 5 menit tanpa membuat akun.

2. **Real-time validasi ketersediaan meja** — sistem mencegah double booking
   dengan database-level locking saat pemilihan dan konfirmasi meja.

3. **Payment gateway via Midtrans** — full payment di muka menggunakan
   Midtrans Snap (transfer bank, virtual account, e-wallet, QRIS).

4. **Notifikasi WhatsApp otomatis** — konfirmasi dikirim ke tamu setelah
   pembayaran berhasil, setelah approval admin, dan saat pembatalan.

5. **Auto-cancel expired payment** — booking yang tidak dibayar dalam batas
   waktu otomatis dibatalkan dan slot meja dibebaskan.

6. **Admin panel** — kelola menu, kategori, meja, booking (approval/cancel/
   complete), transaksi, dan pengaturan restoran.

7. **Order tracking page** — halaman publik berbasis booking reference
   untuk tamu memantau status pesanan tanpa perlu login.

## Out of Scope (Fase 2+)

- **Loyalty program / membership** — membutuhkan sistem akun customer yang
  lebih kompleks, belum prioritas untuk MVP.
- **Mobile native app (iOS / Android)** — web mobile-first sudah cukup
  untuk validasi awal, native app bisa dipertimbangkan setelah ada traction.
- **Sistem delivery atau take-away** — fokus MVP adalah dine-in. Delivery
  membutuhkan integrasi kurir dan logistik yang berbeda scope-nya.
- **Multi-branch / multi-restoran** — fokus satu restoran dulu, arsitektur
  multi-tenant bisa dibangun di fase selanjutnya.
- **Integrasi POS (Point of Sale)** — setiap restoran punya sistem POS
  berbeda, terlalu kompleks untuk fase awal.
- **Review & rating sistem** — bukan kebutuhan mendesak untuk operasional,
  bisa ditambahkan setelah sistem inti stabil.

## Success Metrics

| Metrik | Kondisi Saat Ini | Target MVP |
|--------|-----------------|------------|
| Kasus double booking | Sering terjadi | 0 kasus setelah sistem live |
| Waktu reservasi customer | 5-15 menit via telepon | < 5 menit mandiri |
| Angka no-show | Tinggi, tanpa konsekuensi | Berkurang signifikan (full payment di muka) |
| Visibilitas transaksi | Rekap manual mingguan | Real-time di dashboard |
| Input manual staf | Setiap booking | 0 (booking dari customer langsung) |

## Non-Functional Requirements

**Performa:**
- Halaman customer harus bisa dimuat dalam < 3 detik
- API response < 500ms untuk operasi normal
- Sistem harus bisa handle concurrent booking tanpa race condition

**Keamanan:**
- HTTPS wajib di seluruh platform
- Validasi signature webhook Midtrans untuk mencegah pemalsuan
- Role-based access control di admin panel
- Server key Midtrans disimpan terenkripsi, tidak pernah ditampilkan ulang

**Reliabilitas:**
- Notifikasi WhatsApp menggunakan background job dengan retry mechanism
- Scheduler berjalan setiap menit untuk auto-cancel booking expired
- Jika webhook Midtrans terlambat, admin bisa trigger manual reconciliation

## Constraints

- Aplikasi berbasis web (bukan native app) — menghindari barrier instalasi
- Hanya mendukung satu restoran (single-branch) di V1
- Pembayaran menggunakan Midtrans Snap — metode lain tidak disupport di V1
- Bahasa antarmuka Bahasa Indonesia
- Konfirmasi email tidak diimplementasikan di V1 — hanya via WhatsApp

## Tech Consideration

- **Stack**: Laravel (backend) + Livewire 
- **Database**: PostgreSQL
- **Queue**: Laravel Queue + Redis (untuk notifikasi dan auto-cancel)
- **Payment**: Midtrans Snap
- **WhatsApp**: Fonnte / Wablas / WhatsApp Cloud API
- **Hosting**: Single VPS di fase awal