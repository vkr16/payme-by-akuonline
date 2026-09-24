# PayMe Project Knowledge Base & AI Agent Context

> Dokumen ini adalah single source of truth untuk AI Agent maupun developer berikutnya mengenai arsitektur, filosofi desain, aturan bisnis, status pengerjaan, dan catatan teknis proyek **PayMe**.

---

## 1. Identitas & Visi Produk

- **Nama Aplikasi:** PayMe (PayMe by AkuOnline)
- **Tagline:** *"Split Bill Lebih Adil, Bayar Pakai QRIS Lebih Praktis"*
- **Tujuan Utama:** Menghilangkan kecanggungan menagih patungan makanan/belanja bareng dengan transparansi perhitungan murni (proporsional berbasis produk) dan kemudahan transfer via QRIS dinamis ber-nominal terkunci.
- **Repository Remote:** `https://github.com/vkr16/payme-by-akuonline.git` (Branch utama: `main`)

---

## 2. Tech Stack & Environment

- **Backend:** Laravel 12 (PHP 8.4)
- **Database:** MySQL / SQLite support (default DB diatur via `.env`, host remote `akuonline.my.id`)
- **Frontend / Styling:** Blade Views + Tailwind CSS v4 (`@tailwindcss/vite`) + Vite 8
- **Icon Library:** **FontAwesome Pro 7.1.0** (Self-hosted lokal di `public/vendor/fontawesome/css/all.css`, bukan CDN).
  - *Aturan Icon:* Utamakan style `fa-light` (atau `fal`) untuk tampilan clean, refined, dan elegan. `fa-solid` hanya digunakan jika sangat diperlukan untuk aksen/stempel kontras tinggi.
- **AI Vision Engine (Roadmap OCR):** Direncanakan menggunakan NineRouter API (`https://9router.akuonline.my.id/v1`) dengan model vision (Gemini).

---

## 3. Filosofi Desain (UI/UX Guidelines)

1. **Fintech Utility Aesthetic (Modern Sage & Clean Monochrome):**
   - Latar belakang: Off-white / sage subtle (`bg-[#F9FAF8]`), kartu putih berbatas halus (`border-zinc-200/80` atau `border-zinc-300`).
   - Warna Utama (Primary): Emerald dalam (`text-emerald-800`, `bg-emerald-800 hover:bg-emerald-700`, `bg-emerald-50` untuk badges/tints).
   - Teks & Struktur: High contrast zinc palette (`text-zinc-900`, `text-zinc-600`, `text-zinc-400`).
   - Angka & Finansial: Wajib menggunakan utilitas `tabular-nums` agar digit angka rapi tegak sejajar.
2. **Desain Mobile First & Responsif:**
   - Touch target minimal 36px–44px untuk kenyamanan jempol.
   - Tata letak stepper tombol kuantitas (`- / +`) tactile dan terstruktur.
   - Penempatan badge status (`X/Y terbayar`) seragam di baris kedua (sub-line) di sebelah harga satuan (`@ Rp XX.XXX • X/Y terbayar`), guna menjamin judul menu panjang tidak mengalami teks terpotong atau wrap layout berantakan.

---

## 4. Aturan Bisnis & Logika Perhitungan (Core Business Logic)

### A. Alur Partisipasi (Host vs Teman)
- **Hanya Host (Penagih) yang Wajib Punya Akun / Login:**
  - Host menalangi tagihan, scan struk dengan AI, menginput nomor rekening/QRIS statis merchant, dan membuat link patungan.
  - Rekening dan QRIS host tersimpan rapi di profil akunnya sehingga tidak perlu upload ulang setiap kali membuat tagihan baru.
- **Teman Tidak Perlu Akun & Tidak Perlu Instal Aplikasi:**
  - Teman membuka link patungan langsung di browser HP.
  - Memilih item yang mereka konsumsi menggunakan stepper kuantitas.
  - Sistem otomatis menghasilkan nominal pas dan QRIS Dinamis.
- **Alur Verifikasi Pembayaran (Anti-Fake Claim):**
  1. Teman scan QRIS dan mentransfer via bank/e-wallet.
  2. Teman menekan tombol *"Klaim Sudah Bayar"*.
  3. Host menerima klaim, memeriksa riwayat mutasi di rekeningnya.
  4. Host menekan *"Konfirmasi Dana Masuk"* (Approval). Status tagihan resmi berubah menjadi **LUNAS** (Stempel LUNAS terpicu). Tidak ada auto-approval tanpa verifikasi host.

### B. Rumus Alokasi Biaya & Diskon (Proporsional Murni Berdasarkan Nilai Produk)
Semua biaya tambahan selain produk (ongkir, PB1 pajak restoran, biaya kemasan/layanan) serta diskon/voucher dialokasikan berdasarkan **persentase nilai belanja produk**:

$$\text{Rasio Porsi Produk} = \frac{\text{Subtotal Item Pesanan Pengguna}}{\text{Total Seluruh Produk di Struk (Excl. Biaya & Diskon)}}$$

$$\text{Biaya Tambahan Pengguna} = \text{Total Biaya Tambahan Struk} \times \text{Rasio Porsi Produk}$$

$$\text{Diskon Pengguna} = \text{Total Diskon Struk} \times \text{Rasio Porsi Produk}$$

$$\text{Total Akhir Dibayar} = \text{Subtotal Item Pengguna} + \text{Biaya Tambahan Pengguna} - \text{Diskon Pengguna}$$

*Keunggulan:* Meniadakan ketidakadilan "ongkir bagi rata" (misal kawan yang hanya memesan minuman tidak terbebani ongkir sama besarnya dengan yang memesan banyak porsi makanan).

### C. Fleksibilitas Pembayaran & QRIS
- **QRIS yang Didukung:** Semua QRIS statis merchant (GoPay Merchant, ShopeePay Merchant, QRIS Interaktif, BCA Merchant, dll) yang mendukung payload EMVCo standard (bukan QR transfer antar-rekening perseorangan biasa).
- **Alternatif Non-QRIS:** Host dapat mencantumkan nomor rekening bank (BCA, Mandiri, BRI, dll) serta nomor e-wallet (Dana, GoPay, OVO, ShopeePay) dengan fitur 1-click copy clipboard.
- **Biaya Layanan:** 100% gratis tanpa potongan transaksi sepeserpun (tersedia opsi tip/donasi sukarela untuk developer).

---

## 5. Struktur Halaman & Rute yang Ada

| Rute | Name | File View | Deskripsi |
|---|---|---|---|
| `GET /` | `home` | `resources/views/landing.blade.php` | Landing page lengkap: Hero Section, Interactive Real-Life Split Bill Simulator, 3 Value Pillars, Cara Kerja (3 Steps), Ownership Advantages, 7 FAQ Points, Bottom CTA. Menggunakan QR SVG riil (`public/images/qris-saya.svg`). |
| `GET /design-guide` | `design.guide` | `resources/views/design-guide.blade.php` | Living Design System showcase: Color palette, typography, interactive split card, dynamic QR generator preview, multi-bank copy cards, semantic badges, and accessible modal dialogs. |
| `GET /login` | `login` | `resources/views/auth/login.blade.php` | Halaman Masuk: Form email & password bersih, toggle intip password, remember me, link lupa password. |
| `POST /login` | `login.attempt` | Controller Action | Validasi, rate limiter (5 req/menit), session regeneration, remember me flag. |
| `GET /register` | `register` | `resources/views/auth/register.blade.php` | Halaman Daftar: Form pendaftaran email instan (tanpa verifikasi email rumit, langsung aktif), toggle intip password. |
| `POST /register` | `register.store` | Controller Action | Pembuatan user instan, auto-login, redirect ke dashboard. |
| `POST /logout` | `logout` | Controller Action | Logout user terautentikasi, invalidasi session & CSRF regenerate. |
| `GET /dashboard` | `dashboard` | `resources/views/dashboard.blade.php` | Dashboard Host (Penagih): Ringkasan tagihan, klaim menunggu, profil rekening/QRIS, dan onboarding panduan host. |
| `GET /bills/create` | `bills.create` | `resources/views/bills/create.blade.php` | Form Buat Patungan: Header ringkas (nama acara), AI receipt OCR dropzone, input manual tactile stepper, manajemen QRIS & rekening DB, live fee & discount calculations, sticky floating bottom bar. |
| `POST /bills` | `bills.store` | Controller Action | Validasi data patungan, parsing & pembuatan bill, penyimpanan item, snapshot bank & QRIS, auto-save profile rekening. |
| `POST /bills/parse-receipt` | `bills.parse_receipt` | Controller Action | Endpoint OCR NineRouter AI Vision untuk ekstraksi item struk belanja secara otomatis. |
| `GET /b/{slug}` | `bills.show` | `resources/views/bills/show.blade.php` | Halaman Rincian Patungan: Ringkasan total tagihan, metode pembayaran QRIS & Bank dengan 1-click copy, daftar menu pesanan, share ke WhatsApp & copy tautan. |

---

## 6. Status Pengerjaan & Roadmap Selanjutnya

### ✅ Yang Sudah Selesai:
1. Setup arsitektur frontend dengan Tailwind CSS v4, FontAwesome Pro 7.1.0 lokal (`fa-light` standard).
2. Desain landing page responsif dengan copy yang akurat:
   - Rima headline: *"Split Bill Lebih Adil, Bayar Pakai QRIS Lebih Praktis"*.
   - Call to action penagih bebas jargon.
   - Live simulator dengan input stepper kuantitas dan label status terbayar universal (`X/Y terbayar`).
   - Alur 2 langkah anti-fake claim (Teman Klaim $\rightarrow$ Host Konfirmasi).
   - Rincian proporsional murni berbasis nilai produk.
   - Integrasi file QR real (`qris-saya.svg`).
   - FAQ komprehensif mencakup koreksi AI, fee, bank transfer, dan QRIS merchant.
3. Git hygiene & security: `.gitignore` bersih dari `.env`, credential, build cache, dan log runtime.
4. Git repo inisialisasi di branch `main` dengan remote terpasang ke GitHub user.
5. **Host Authentication & Full PWA Implementation:**
   - [AuthController.php](file:///home/fikri/Development/payme/app/Http/Controllers/Auth/AuthController.php) (Register instan, Login dengan RateLimiter 5 req/menit, Remember me, Logout aman).
   - [DashboardController.php](file:///home/fikri/Development/payme/app/Http/Controllers/DashboardController.php) & [dashboard.blade.php](file:///home/fikri/Development/payme/resources/views/dashboard.blade.php).
   - Dynamic Auth Header (`@auth` / `@guest`) di [app.blade.php](file:///home/fikri/Development/payme/resources/views/layouts/app.blade.php).
   - Full automated test coverage di [AuthTest.php](file:///home/fikri/Development/payme/tests/Feature/AuthTest.php) (8 passed tests).
   - **Full Progressive Web App (PWA) Support:**
     - Web App Manifest: [manifest.webmanifest](file:///home/fikri/Development/payme/public/manifest.webmanifest) (Nama, icon maskable/any 192px & 512px, start_url: `/dashboard`, display: `standalone`, app shortcuts).
     - Service Worker: [sw.js](file:///home/fikri/Development/payme/public/sw.js) (Pre-cache shell assets, FontAwesome lokal, icons; Network-first untuk halaman dinamis).
     - Auto-registration di [app.js](file:///home/fikri/Development/payme/resources/js/app.js) dan link manifest di [app.blade.php](file:///home/fikri/Development/payme/resources/views/layouts/app.blade.php).
6. **Halaman "Buat Patungan" (Tactile, Focused & Database-Backed):**
   - **Database Models & Migrations:** `UserQris`, `UserBank`, `Bill`, `BillItem`, `BillBank`.
   - **Services:** [QrisService.php](file:///home/fikri/Development/payme/app/Services/QrisService.php) (EMVCo parser & dynamic QRIS generator) dan [ReceiptParserService.php](file:///home/fikri/Development/payme/app/Services/ReceiptParserService.php) (NineRouter Gemini Vision parser).
   - **Controller:** [BillController.php](file:///home/fikri/Development/payme/app/Http/Controllers/BillController.php) (`create`, `store`, `parseReceipt`, `show`).
   - **Views:** [create.blade.php](file:///home/fikri/Development/payme/resources/views/bills/create.blade.php) & [show.blade.php](file:///home/fikri/Development/payme/resources/views/bills/show.blade.php).
   - **Automated Tests:** [BillTest.php](file:///home/fikri/Development/payme/tests/Feature/BillTest.php) (8 passed tests, total suite: 16 passed tests).

### 🚀 Roadmap Selanjutnya (Upcoming Work):
1. **Public Bill Participant Page (Interactive Selection & Claims):**
   - Halaman publik akses via `/b/{slug}` untuk teman memilih pesanan via stepper kuantitas.
   - Generate Dynamic QRIS dengan nominal terkunci per klaim pesanan partisipan.
   - Form klaim pembayaran (Teman Klaim $\rightarrow$ Host Konfirmasi di Dashboard).
   - **Post-Payment "Buy Me a Coffee" Prompt:** Menampilkan card/tombol donasi traktir kopi untuk developer (`fa-light fa-mug-hot`) setelah pembayaran terverifikasi LUNAS.
2. **Dashboard Host Realtime Claims Management:**
   - Realtime tracking klaim pembayaran teman (Menunggu Verifikasi $\rightarrow$ Host Approve / Reject).
   - Halaman kelola profil rekening & QRIS mandiri (`/profile/accounts`).
