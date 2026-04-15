# 🌾 Pohaci AI — Ngariksa Pare, Ngajaga Lemah Cai

Pohaci AI adalah aplikasi sistem pakar berbasis web untuk deteksi penyakit tanaman padi dan konsultasi pertanian. Aplikasi ini mengintegrasikan Laravel sebagai backend dengan Groq Cloud API untuk kecerdasan buatan yang cepat dan akurat.

> "Pohaci" diambil dari Dewi Sri (Nyi Pohaci) dalam mitologi Sunda — dewi padi dan kesuburan.

## ✨ Fitur Unggulan

### 🔬 Diagnosa Penyakit (Vision AI)
- Upload foto daun: analisa otomatis menggunakan model Vision AI.
- Hasil instan: menampilkan nama penyakit, tingkat kepercayaan, dan solusi penanganan.
- Riwayat diagnosa: data tersimpan untuk monitoring dan pelaporan.

### 💬 Chatbot AI Pertanian (Smart Assistant)
- Konsultasi real-time seputar pertanian padi.
- Multi-modal:
  - Text: pertanyaan umum.
  - Gambar (attach): analisa foto hama/penyakit langsung di chat.
  - URL: analisa konten artikel/berita pertanian dari link eksternal.
- Dynamic model: otomatis memilih model bahasa yang tepat.

### 🛰️ Analisa Spasial NDVI
- Jika koordinat tersedia, sistem mengambil data satelit Sentinel-2.
- NDVI dipakai untuk melihat indikasi kesehatan tanaman dan potensi kekurangan hara.
- Jika koordinat tidak tersedia, sistem otomatis masuk ke jalur analisa biasa agar tetap cepat.

### 📱 Antarmuka Ramah Petani (Farmer-Friendly UI)
- Desain mobile-first: tombol besar, kontras tinggi, mudah digunakan di HP saat di sawah.
- Aksi cepat: chip pertanyaan instan tanpa perlu mengetik panjang.
- Responsif dan ringan.

### 🚜 Dashboard Admin Terintegrasi
- Statistik penggunaan AI real-time.
- Manajemen data diagnosa dan riwayat chat.
- Export laporan untuk dinas/kelompok tani.

## 🏗️ Arsitektur & Teknologi

| Komponen | Teknologi | Keterangan |
|---|---|---|
| Framework | Laravel 8.83 | Backend PHP robust & stabil |
| Database | PostgreSQL / MySQL | Cloud atau lokal |
| AI Engine | Groq API | Inference cepat untuk chat dan vision |
| Spasial | Google Earth Engine | NDVI Sentinel-2 |
| Frontend | Blade + Tailwind CSS | UI responsif & mobile-first |
| Proses satelit | Symfony Process + Node.js | Menjalankan script GEE |

## 🚀 Panduan Instalasi (Lokal)

### Clone Repository

```bash
git clone https://github.com/bensu89/sistem-pakar-padi-ai.git
cd sistem-pakar-padi-ai
```

### Install Dependensi

```bash
composer install
npm install && npm run dev
```

### Konfigurasi Environment

Salin `.env.example` ke `.env` dan sesuaikan:

```env
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=padi_db
DB_USERNAME=root
DB_PASSWORD=

GROQ_API_KEY=gsk_...
GROQ_DEFAULT_MODEL=llama-3.1-8b-instant
GROQ_VISION_MODEL=llama-4-scout-17b-16e-instruct

GEE_CLIENT_EMAIL=...
GEE_PRIVATE_KEY=...
```

### Generate Key & Migrasi

```bash
php artisan key:generate
php artisan migrate
php artisan storage:link
```

### Jalankan Server

```bash
php artisan serve
```

Buka `http://localhost:8000`

## ☁️ Panduan Deployment (Vercel)

1. Push ke GitHub pastikan kode terbaru ada di repo.
2. Import di Vercel Dashboard.
3. Set environment variables:
   - `APP_KEY`
   - `APP_DEBUG=false`
   - `APP_URL`
   - `GROQ_API_KEY`
   - `GROQ_DEFAULT_MODEL`
   - `GROQ_VISION_MODEL`
   - `GEE_CLIENT_EMAIL`
   - `GEE_PRIVATE_KEY`
4. Redeploy jika ada perubahan kode.

## 🤝 Kontribusi & Credits

Aplikasi ini dikembangkan untuk memajukan teknologi pertanian digital di Indonesia.

---

<p align="center">
  Dibuat dengan ❤️ untuk Pertanian Indonesia.
</p>
