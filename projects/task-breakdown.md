# SaaS Manajemen Lembaga Bimbingan Belajar (LBB)

Platform multi-tenant untuk banyak LBB: website publik, pendaftaran siswa, jadwal, absensi, tagihan/SPP, dan portal siswa/ortu/tutor. Satu aplikasi dipakai banyak lembaga, data tiap lembaga terpisah.

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel |
| Frontend portal | Inertia.js + React/Vue |
| Panel admin | Filament (Livewire) |
| Database | PostgreSQL (Supabase, via pooler) |
| Multi-tenancy | Single database, kolom `tenant_id` + global scope |
| Billing | Midtrans / Xendit |
| Notifikasi | WhatsApp API / email |

## Prinsip Arsitektur

- **Multi-tenant dari hari pertama.** Semua tabel tenant-scoped punya `tenant_id`, difilter otomatis lewat global scope.
- **Filament** untuk panel super admin (platform) dan admin LBB. **Inertia** untuk portal siswa/ortu/tutor dan halaman publik interaktif.
- **Supabase dipakai sebagai Postgres biasa.** Auth dan storage tetap pakai Laravel. Jika memakai pooler mode transaction, perhatikan pengaturan prepared statements.
- **Setiap fitur wajib punya test isolasi tenant.**
- Satu fitur per iterasi: bangun, tes, commit.

## Role

| Role | Cakupan |
|---|---|
| Super admin | Level platform: kelola semua tenant dan paket |
| Owner | Pemilik LBB, akses penuh dalam tenant |
| Admin | Operasional harian LBB |
| Tutor | Absensi, nilai, materi |
| Siswa / Ortu | Lihat jadwal, absensi, tagihan, rapor |

---

## Roadmap

### Tahap 0: Fondasi

- [x] Setup Laravel + Inertia + koneksi Supabase Postgres
- [x] Tabel `tenants`, relasi user-tenant, `tenant_id` di semua tabel
- [x] Global scope + resolusi tenant dari subdomain
- [x] Auth: login, register, reset password
- [x] Role dan permission per tenant
- [x] Panel super admin: daftar tenant, aktif/nonaktif
- [x] Test otomatis: data antar-tenant tidak bocor

### Tahap 1: MVP untuk LBB pilot

- [ ] Onboarding LBB: daftar, buat lembaga, pilih subdomain, isi profil dan logo
- [ ] CRUD siswa, tutor, program, kelas
- [ ] Jadwal kelas (tutor, ruang, hari/jam)
- [ ] Form pendaftaran online + daftar pendaftar (baru, dihubungi, terdaftar)
- [ ] Landing page publik per tenant: profil, program, tutor, kontak, tombol WhatsApp
- [ ] Branding dasar: logo dan warna

### Tahap 2: Operasional harian

- [ ] Absensi siswa dan tutor per sesi
- [ ] Tagihan/SPP bulanan: status lunas/belum, upload bukti transfer
- [ ] Laporan: siswa aktif, pemasukan, kehadiran
- [ ] Notifikasi WhatsApp/email: pengingat jadwal dan tagihan
- [ ] Import siswa dari Excel
- [ ] Gaji tutor dari sesi yang diajar

### Tahap 3: Portal dan monetisasi

- [ ] Portal siswa/ortu: jadwal, absensi, tagihan, rapor
- [ ] Portal tutor: input absensi, nilai, upload materi
- [ ] Paket langganan (Free/Basic/Pro) dengan batas siswa/tutor/fitur
- [ ] Billing otomatis (Midtrans/Xendit)
- [ ] Payment gateway untuk SPP online
- [ ] Custom domain per tenant (paket berbayar)
- [ ] Pilihan template landing page

### Tahap 4: Pertumbuhan

- [ ] Blog/artikel untuk SEO, testimoni, galeri
- [ ] Bank soal / tryout online
- [ ] Pengumuman atau chat ke ortu
- [ ] PWA/aplikasi mobile untuk ortu
- [ ] Audit log, backup, ekspor data

---

## Cara Kerja (Vibe Coding)

1. Taruh spesifikasi di `PRD.md` dan aturan proyek di `CLAUDE.md`.
2. Kerjakan satu fitur per prompt, mulai dari migration, model, lalu Filament resource atau halaman Inertia.
3. Tes (termasuk isolasi tenant), lalu commit ke Git sebelum lanjut.
4. Jangan commit `.env`. Review keamanan sebelum rilis: hashing password, otorisasi per role, validasi input, CORS.
5. Validasi dengan 2-3 LBB pilot sebelum membangun billing otomatis. Penagihan manual dulu tidak masalah.

## Setup Lokal

```bash
git clone <repo-url> && cd <folder>
composer install
npm install
cp .env.example .env
php artisan key:generate
# isi DB_* di .env dengan kredensial Supabase Postgres
php artisan migrate
npm run dev
php artisan serve
```

## Lisensi

Belum ditentukan.