<p align="center">
  <img src="https://img.shields.io/badge/Laravel-8.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Groq_AI-Cloud_API-F55036?style=for-the-badge&logo=groq&logoColor=white" alt="Groq">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind">
  <img src="https://img.shields.io/badge/Vercel-Deployed-000000?style=for-the-badge&logo=vercel&logoColor=white" alt="Vercel">
  <img src="https://img.shields.io/badge/Supabase-PostgreSQL-3ECF8E?style=for-the-badge&logo=supabase&logoColor=white" alt="Supabase">
</p>

# 🌾 Pohaci AI — Ngariksa Pare, Ngajaga Lemah Cai

**Pohaci AI** adalah aplikasi sistem pakar berbasis web untuk **deteksi penyakit tanaman padi** dan **konsultasi pertanian**. Aplikasi ini mengintegrasikan **Laravel** sebagai backend dengan **Groq Cloud API** untuk kecerdasan buatan yang cepat dan akurat.

> *"Pohaci"* diambil dari Dewi Sri (Nyi Pohaci) dalam mitologi Sunda — dewi padi dan kesuburan.

---

## ✨ Fitur Unggulan

### 🔬 Diagnosa Penyakit (Vision AI)
- **Upload Foto Daun**: Analisa otomatis menggunakan model Vision AI (Llama 3.2 Vision / Llama 4 Scout).
- **Hasil Instan**: Menampilkan nama penyakit, tingkat kepercayaan (confidence), dan solusi penanganan.
- **Riwayat Diagnosa**: Data tersimpan untuk monitoring dan pelaporan.

### 💬 Chatbot AI Pertanian (Smart Assistant)
- **Konsultasi Real-time**: Tanya jawab seputar pertanian padi.
- **Multi-Modal**:
  - **Text**: Pertanyaan umum.
  - **Gambar (📎 Attach)**: Analisa foto hama/penyakit langsung di chat.
  - **URL (🔗 Link)**: Analisa konten artikel/berita pertanian dari link eksternal.
- **Dynamic Model**: Otomatis memilih model bahasa yang tepat (Llama 3.3 70B untuk chat kompleks, Mixtral untuk kecepatan).

### 📱 Antarmuka Ramah Petani (Farmer-Friendly UI)
- **Desain Mobile-First**: Tombol besar, kontras tinggi, mudah digunakan di HP saat di sawah.
- **Aksi Cepat (Quick Actions)**: Chip pertanyaan instan ("Hama Wereng", "Pupuk") tanpa perlu mengetik panjang.
- **Responsif & Ringan**: Tampilan bersih tanpa scrollbar mengganggu, optimal untuk sinyal desa.

### 🚜 Dashboard Admin Terintegrasi
- Statistik penggunaan AI real-time.
- Manajemen data diagnosa & riwayat chat.
- Export laporan (Excel/CSV) untuk dinas/kelompok tani.

---

## 🏗️ Arsitektur & Teknologi

| Komponen | Teknologi | Keterangan |
|----------|-----------|------------|
| **Framework** | Laravel 8.83 | Backend PHP robust & stabil (PHP 8.4 Support) |
| **Database** | PostgreSQL (Supabase) | Cloud database scalable |
| **AI Engine** | Groq API | Inference super cepat (Llama 3.3 70B & Vision) |
| **Frontend** | Blade + Tailwind CSS | UI responsif & mobile-first |
| **Hosting** | Vercel (Serverless) | Deployment otomatis & performa tinggi |

---

## 🚀 Panduan Instalasi (Lokal)

1.  **Clone Repository**
    ```bash
    git clone https://github.com/bensu89/sistem-pakar-padi-ai.git
    cd sistem-pakar-padi-ai
    ```

2.  **Install Dependensi**
    ```bash
    composer install
    npm install && npm run dev
    ```

3.  **Konfigurasi Environment**
    Salin `.env.example` ke `.env` dan sesuaikan:
    ```env
    APP_URL=http://localhost:8000
    
    # Database (Bisa pakai MySQL lokal atau Supabase)
    DB_CONNECTION=mysql 
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=padi_db
    DB_USERNAME=root
    DB_PASSWORD=

    # Groq API (Dapatkan di console.groq.com)
    GROQ_API_KEY=gsk_...
    GROQ_DEFAULT_MODEL=llama-3.1-8b-instant
    GROQ_VISION_MODEL=llama-3.2-11b-vision-preview
    ```

4.  **Generate Key & Migrasi**
    ```bash
    php artisan key:generate
    php artisan migrate
    php artisan storage:link
    ```

5.  **Jalankan Server**
    ```bash
    php artisan serve
    ```
    Buka `http://localhost:8000`

---

## ☁️ Panduan Deployment (Vercel)

Aplikasi ini sudah dikonfigurasi untuk **Vercel** serverless environment.

1.  **Push ke GitHub** pastikan kode terbaru ada di repo.
2.  **Import di Vercel Dashboard**.
3.  **Set Environment Variables** di Vercel:
    - `APP_KEY`: (Sama seperti lokal)
    - `APP_DEBUG`: `false` (untuk production)
    - `APP_URL`: `https://namaproject.vercel.app`
    - `GROQ_API_KEY`: API Key Groq Anda
    - `DB_CONNECTION`: `pgsql`
    - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Detail koneksi Supabase Anda.
    - `GROQ_DEFAULT_MODEL`: `llama-3.1-8b-instant` (Rekomendasi)
    - `GROQ_VISION_MODEL`: `llama-3.2-11b-vision-preview`
4.  **Redeploy** jika ada perubahan kode.

> **Catatan:** Untuk Vercel, pastikan menggunakan `vercel-php@0.9.0` (sudah diatur di `vercel.json`).

---

## ⚠️ Troubleshooting Vercel & Supabase

### 1. Error "CSRF Token Mismatch" (419 Page Expired)
- **Problem**: Login atau upload gagal di Vercel.
- **Solusi**: Tambahkan Environment Variables di Vercel Dashboard:
  - `SESSION_DRIVER`: `cookie`
  - `SESSION_SECURE_COOKIE`: `true`
  - `SANCTUM_STATEFUL_DOMAINS`: `nama-app-anda.vercel.app`

### 2. Gambar Upload Tidak Muncul (Broken Image)
- **Problem**: Gambar hilang setelah beberapa saat.
- **Solusi**: Pastikan Supabase Storage dikonfigurasi.
  - Tambahkan `SUPABASE_URL`, `SUPABASE_KEY` (Service Role), `SUPABASE_BUCKET` di Vercel ENV.
  - Buat bucket `padi-uploads` (Public) di Supabase Storage.

### 3. Login Gagal "Credentials do not match" (Database Kosong)
- **Solusi**: Buka route `/setup-admin` di browser (`https://.../setup-admin`) untuk membuat user admin default (`admin@padi.com` / `password`).

### 4. PHP 8.4 Deprecation Warnings
- **Problem**: Muncul warning `Implicitly marking parameter...` di log Vercel.
- **Solusi**: Aplikasi ini sudah dipatch (Feb 2026) menggunakan `voku/portable-ascii` v2.0.3+ dan `symfony/translation` v6+. Pastikan redeploy dengan "Clear Cache".

---


## 🤝 Kontribusi & Credits

Aplikasi ini dikembangkan oleh **Tim KKN Desa Cikurubuk** sebagai dedikasi untuk memajukan teknologi pertanian digital di Indonesia.

---

<p align="center">
  Dibuat dengan ❤️ untuk Pertanian Indonesia 🇮🇩
</p>
