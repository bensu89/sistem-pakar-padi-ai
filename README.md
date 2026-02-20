
# 🌾 Pohaci AI: Sistem Pakar & Deteksi Penyakit Padi

**Pohaci AI** adalah platform *Smart Farming* berbasis web yang memanfaatkan kecerdasan buatan (AI) untuk membantu petani mendiagnosa penyakit tanaman padi secara dini. Aplikasi ini menggabungkan visi komputer (*Computer Vision*) dan pemrosesan bahasa alami (*NLP*) untuk memberikan konsultasi pertanian yang akurat, cepat, dan hemat data.

🔗 **Live Demo:** [https://pohaci-ai.vercel.app/](https://pohaci-ai.vercel.app/)

---

## 🚀 Fitur Unggulan

### 1. 🤖 Diagnosa Penyakit Berbasis AI (Gemini & Llama-3 Vision)
* **Analisa Visual:** Unggah foto daun padi, dan AI akan mendeteksi penyakit (seperti Blas, Hawar Daun, Tungro) beserta tingkat keparahannya.
* **Auto-Fallback System:** Menggunakan Google Gemini sebagai AI utama (`gemini-2.5-flash` / `gemini-2.5-pro`). Jika Limit/Quota habis, sistem otomatis berpindah (*fallback*) ke Groq API (`llama-3.3-70b-versatile`) memastikan aplikasi tetap berjalan 24/7 tanpa downtime.
* **Anti-Halusinasi (*Safety Gate*):** Sistem dilengkapi validasi ketat. AI akan menolak menjawab jika objek bukan tanaman padi.

### 2. 💬 Asisten Chatbot Pertanian Cerdas
* **Konteks Percakapan:** Chatbot mengingat riwayat pertanyaan sebelumnya untuk konsultasi yang mengalir natural.
* **Deep Reading:** Mampu membaca dan mengekstrak informasi detail (nama latin, bahan aktif obat) dari artikel referensi atau URL yang diberikan pengguna.

### 3. ⚡ Smart Compression (Hemat Kuota & Server)

* **Optimasi Dua Lapis:**
* **Client-Side:** Kompresi awal di browser sebelum upload.
* **Server-Side:** *Resize* dan konversi otomatis (lebar max 1024px) menggunakan `intervention/image` sebelum dikirim ke AI.


* **Dukungan Resolusi Tinggi:** Menerima foto kamera HP modern (hingga 20MB) namun tetap ringan diproses server.

### 4. 📊 Data Lake & Dual-Bucket Architecture
* **Logging Otomatis:** Setiap interaksi tersimpan otomatis di database sebagai aset dataset.
* **Smart Routing Storage:** Terintegrasi dengan Supabase REST API untuk memisahkan gambar valid (bucket `diagnosa`) dan gambar cacat/bukan padi (bucket `salah-upload`) guna menjaga kebersihan dataset.

---

## 🛠️ Teknologi & Arsitektur

### Backend & Infrastructure

* **Framework:** Laravel 9/10 (Support PHP 8.0)
* **Language:** PHP 8.0.30
* **Database:** MySQL / MariaDB
* **Storage:** Supabase Storage (via S3 Protocol Adapter)
* **Hosting:** Vercel (Frontend/Serverless logic) / Shared Hosting

### Frontend

* **UI Framework:** Tailwind CSS
* **Templating:** Blade Templates
* **Scripting:** Vanilla JS + Axios
* **Library:** `browser-image-compression` (Untuk optimasi sisi klien)

### AI Engine
* **Architecure:** Interface-based Dual AI Provider (`AIServiceInterface`)
* **Primary:** Google Gemini API (`gemini-2.5-flash` / `gemini-2.5-pro`)
* **Fallback:** Groq API (`llama-3.3-70b-versatile` / `llama-4-scout-17b`)

---

## ⚙️ Instalasi Lokal

Ikuti langkah ini untuk menjalankan proyek di komputer lokal:

1. **Clone Repository**
```bash
git clone https://github.com/username/pohaci-ai.git
cd pohaci-ai

```


2. **Install Dependencies**
*Catatan: Pastikan menggunakan PHP 8.0+*
```bash
composer install
npm install && npm run build

```


3. **Konfigurasi Environment**
Duplikasi file `.env.example` menjadi `.env` dan sesuaikan kuncinya:
```ini
DB_CONNECTION=pgsql
DB_HOST=aws-1-ap-southeast-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.your_project_ref
DB_PASSWORD=your_db_password

# Konfigurasi AI
AI_PROVIDER=gemini
GEMINI_API_KEY=your_gemini_key
GEMINI_DEFAULT_MODEL=gemini-2.5-flash
GROQ_API_KEY=your_groq_key

# Konfigurasi Supabase Storage (REST)
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your_service_role_key
SUPABASE_BUCKET=diagnosa
SUPABASE_FAILED_BUCKET=salah-upload
```


4. **Setup Database & Migrations**
```bash
php artisan migrate

```


5. **Konfigurasi PHP (Penting!)**
Pastikan `php.ini` di server lokal Anda mengizinkan upload file besar:
```ini
upload_max_filesize = 25M
post_max_size = 30M

```


6. **Jalankan Aplikasi**
```bash
php artisan serve

```



---

## 🛡️ Keamanan & Lisensi

Proyek ini menerapkan batasan keamanan (*Safety Gate*) untuk mencegah penyalahgunaan AI dalam memberikan saran medis manusia atau topik di luar pertanian.

Lisensi: **MIT License**
Dikembangkan oleh: **Beben Sutara (bensu89)**

---

Dibuat dengan ❤️ untuk kemajuan petani Indonesia. Hidup Jokowi! 🇮🇩
