# Pohaci AI: Sistem Pakar Padi & Deteksi Penyakit 🌾🤖

**Pohaci AI** adalah aplikasi berbasis web yang dirancang untuk membantu petani dalam mendeteksi penyakit pada tanaman padi secara dini menggunakan teknologi Kecerdasan Buatan (AI). Aplikasi ini menyediakan fitur diagnosa melalui analisis citra daun padi dan asisten chatbot interaktif.

## 🚀 Fitur Utama

- **Diagnosa Penyakit Padi**: Unggah foto daun padi untuk mendapatkan analisa penyakit secara instan menggunakan AI.
- **AI Chatbot (Llama-3 Vision)**: Konsultasi interaktif seputar pertanian dengan kemampuan analisa gambar dan teks.
- **Kompresi Gambar Otomatis**: Fitur optimasi sisi klien untuk memastikan upload cepat dan hemat bandwidth (mengatasi limit serverless).
- **Manajemen Pengguna**: Mendukung peran Admin, Teknisi, dan Pelapor untuk pengelolaan data lapangan.
- **Dashboard Admin**: Tokenisasi API, manajemen laporan, dan monitoring sistem.

## 🛠️ Teknologi yang Digunakan

- **Backend**: Laravel 10 (PHP 8.0)
- **Frontend**: Blade Templates, Tailwind CSS, JavaScript (Vanilla + Axios)
- **Database**: MySQL / MariaDB
- **Storage**: Supabase Storage (S3 Protocol)
- **AI Engine**: Groq API (Llama-3 Vision Model)
- **Libraries**:
    - `intervention/image` (Manipulasi Gambar)
    - `browser-image-compression` (Kompresi Client-side)
    - `league/flysystem-aws-s3-v3` (Storage Driver)

## 📦 Instalasi & Konfigurasi

Ikuti langkah-langkah berikut untuk menjalankan proyek di komputer lokal Anda:

### 1. Clone Repository
```bash
git clone https://github.com/bensu89/sistem-pakar-padi-ai.git
cd sistem-pakar-padi-ai
```

### 2. Install Dependencies
Pastikan Anda memiliki PHP 8.0 dan Composer terinstal.
```bash
composer install
npm install && npm run dev
```

### 3. Konfigurasi Environment (.env)
Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasinya:
```bash
cp .env.example .env
```

**Konfigurasi Database:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pohaci_db
DB_USERNAME=root
DB_PASSWORD=
```

**Konfigurasi Supabase Storage (Wajib untuk Upload Gambar):**
```env
SUPABASE_ACCESS_KEY_ID=your_access_key
SUPABASE_SECRET_ACCESS_KEY=your_secret_key
SUPABASE_REGION=ap-southeast-1
SUPABASE_BUCKET=padi-uploads
SUPABASE_ENDPOINT=https://<project_ref>.supabase.co/storage/v1/s3
SUPABASE_URL=https://<project_ref>.supabase.co/storage/v1/object/public/padi-uploads
```

**Konfigurasi Groq AI:**
```env
GROQ_API_KEY=gsk_your_groq_api_key
```

### 4. Generate Key & Migrasi Database
```bash
php artisan key:generate
php artisan migrate
```

### 5. Jalankan Aplikasi
```bash
php artisan serve
```
Akses aplikasi di `http://127.0.0.1:8000`.

## 📝 Catatan Penting
- **Limit Upload**: Aplikasi ini memiliki validasi sisi klien 20MB, namun secara otomatis mengompres gambar menjadi di bawah 1MB sebelum dikirim ke server untuk kompatibilitas dengan serverless environment (seperti Vercel).
- **PHP Version**: Pastikan menggunakan PHP 8.0 sesuai dengan `composer.json`.

## 📄 Lisensi
[MIT License](LICENSE)

---
*Dikembangkan oleh Tim KKN Desa Cikurubuk untuk Pertanian Indonesia yang Lebih Maju.* 🌱
