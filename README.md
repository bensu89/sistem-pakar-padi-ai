
# 🌾 Pohaci AI: Sistem Pakar & Deteksi Penyakit Padi

**Pohaci AI** adalah platform *Smart Farming* berbasis web yang memanfaatkan kecerdasan buatan (AI) untuk membantu petani mendiagnosa penyakit tanaman padi secara dini. Aplikasi ini menggabungkan visi komputer (*Computer Vision*) dan pemrosesan bahasa alami (*NLP*) untuk memberikan konsultasi pertanian yang akurat, cepat, dan hemat data.

🔗 **Live Demo:** [https://pohaci-ai.vercel.app/](https://pohaci-ai.vercel.app/)

---

## 🚀 Fitur Unggulan

### 1. 🤖 Diagnosa Penyakit Berbasis AI (Llama-3 Vision)

* **Analisa Visual:** Unggah foto daun padi, dan AI akan mendeteksi penyakit (seperti Blas, Hawar Daun, Tungro) beserta tingkat keparahannya.
* **Anti-Halusinasi (*Safety Gate*):** Sistem dilengkapi validasi ketat. AI akan menolak menjawab jika objek bukan tanaman padi atau jika informasi tidak tersedia di basis pengetahuan.

### 2. 💬 Asisten Chatbot Pertanian Cerdas

* **Konteks Percakapan:** Chatbot mengingat riwayat pertanyaan sebelumnya untuk konsultasi yang mengalir natural.
* **Deep Reading:** Mampu membaca dan mengekstrak informasi detail (nama latin, bahan aktif obat) dari artikel referensi yang diberikan.

### 3. ⚡ Smart Compression (Hemat Kuota & Server)

* **Optimasi Dua Lapis:**
* **Client-Side:** Kompresi awal di browser sebelum upload.
* **Server-Side:** *Resize* dan konversi otomatis (lebar max 1024px) menggunakan `intervention/image` sebelum dikirim ke AI.


* **Dukungan Resolusi Tinggi:** Menerima foto kamera HP modern (hingga 20MB) namun tetap ringan diproses server.

### 4. 📊 Data Lake & Dataset Collector

* **Logging Otomatis:** Setiap interaksi (Tanya-Jawab & Gambar) tersimpan otomatis di database sebagai aset dataset.
* **Supabase Storage:** Penyimpanan gambar bukti fisik penyakit yang terintegrasi dengan log konsultasi.

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

* **Provider:** Groq API
* **Model:** Llama-3.2-11b-vision-preview (Multimodal) / Llama-3.1-8b-instant (Text)

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
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=pohaci_db

# Konfigurasi AI Groq
GROQ_API_KEY=gsk_your_key_here

# Konfigurasi Supabase Storage
AWS_ACCESS_KEY_ID=your_supabase_s3_access_key
AWS_SECRET_ACCESS_KEY=your_supabase_s3_secret_key
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=your_bucket_name
AWS_ENDPOINT=https://your-project.supabase.co/storage/v1/s3

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

*Dibuat dengan ❤️ untuk kemajuan petani Indonesia.* 🇮🇩
