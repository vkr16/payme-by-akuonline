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
| `GET /b/{slug}` | `bills.show` | `resources/views/bills/show.blade.php` | Halaman Pembayaran Patungan: Pemilihan menu pesanan interaktif (stepper kuantitas), kalkulasi proporsional real-time, generate Dynamic QRIS nominal terkunci, modal klaim "Saya Sudah Bayar", dan khusus Host: antarmuka verifikasi & approval klaim kawan (Anti-Fake Claim). |
| `POST /b/{slug}/calculate` | `bills.calculate` | Controller Action | AJAX endpoint kalkulasi proporsional subtotal item, alokasi ongkir/layanan/diskon, dan generate payload dynamic QRIS. |
| `POST /b/{slug}/claim` | `bills.claim` | Controller Action | Pengajuan klaim pembayaran partisipan dengan status 'pending'. |
| `POST /b/{slug}/claims/{claimId}/confirm` | `bills.claims.confirm` | Controller Action | Approval host: konfirmasi dana masuk dan mengubah status klaim menjadi 'confirmed'. |
| `DELETE /b/{slug}/claims/{claimId}/reject` | `bills.claims.reject` | Controller Action | Penolakan / pembatalan klaim pembayaran oleh host. |

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
   - **Views:** [create.blade.php](file:///home/fikri/Development/payme/resources/views/bills/create.blade.php).
7. **Halaman Pembayaran & Mekanisme Approval Host (Anti-Fake Claim):**
   - **Database Models & Migrations:** `BillClaim` & `BillClaimItem` dengan tracking status `pending` dan `confirmed`.
   - **Interaktif Pemilihan Menu:** Stepper kuantitas porsi pesanan dengan live calculation AJAX proporsionalitas ongkir/layanan/diskon.
   - **Dynamic QRIS Modal:** Menghasilkan QR code SVG/canvas ber-nominal terkunci pas sesuai porsi yang dipilih partisipan.
   - **Klaim Pembayaran ("Saya Sudah Bayar"):** Form modal input nama & metode bayar yang mencatat klaim ke database.
   - **Host Claims Management:** Khusus user dengan sesi Host, muncul card approval untuk memverifikasi dana masuk ("Konfirmasi Dana Masuk" / "Tolak Klaim").
   - **Automated Tests:** [BillTest.php](file:///home/fikri/Development/payme/tests/Feature/BillTest.php) (11 passed tests, total suite: 19 passed tests).

### 🚀 Roadmap Selanjutnya (Upcoming Work):
1. **Post-Payment "Buy Me a Coffee" Prompt:**
   - Menampilkan card/tombol manis apresiasi donasi/traktir kopi untuk developer (`fa-light fa-mug-hot`) setelah status pembayaran teman terverifikasi **LUNAS** (momen conversion donasi terbaik).
2. **Dashboard Host Realtime Notification & Management:**
   - Halaman kelola profil rekening & QRIS mandiri (`/profile/accounts`).
   - Polling / auto-refresh status klaim pada halaman bill.
