# Integrasi Groq API — Walkthrough

## Ringkasan
Mengganti seluruh backend AI dari **Flask/Python API lokal** (`127.0.0.1:5000`) ke **Groq Cloud API**. Menambahkan fitur chatbot: **file attachment**, **URL analysis**, dan **dynamic model switching**.

## File yang Dibuat

| File | Fungsi |
|------|--------|
| [GroqService.php](file:///d:/Aplikasi/padi-web/app/Services/GroqService.php) | Service layer untuk komunikasi Groq API (chat, vision, URL, diagnosa) |
| [ChatController.php](file:///d:/Aplikasi/padi-web/app/Http/Controllers/ChatController.php) | Controller chat — handle text, file, dan URL |

## File yang Dimodifikasi

| File | Perubahan |
|------|-----------|
| [.env](file:///d:/Aplikasi/padi-web/.env) | Tambah `GROQ_API_KEY` dan `GROQ_DEFAULT_MODEL` |
| [services.php](file:///d:/Aplikasi/padi-web/config/services.php) | Tambah konfigurasi `groq` (api_key, model, base_url) |
| [DiagnosisController.php](file:///d:/Aplikasi/padi-web/app/Http/Controllers/DiagnosisController.php) | Ganti Flask API → Groq Vision untuk diagnosa daun |
| [web.php](file:///d:/Aplikasi/padi-web/routes/web.php) | Tambah route `POST /chat` → `ChatController` |
| [home.blade.php](file:///d:/Aplikasi/padi-web/resources/views/home.blade.php) | Rebuilt UI: model selector, file attach, URL input |

## Fitur Baru di Chatbot

1. **🧠 Model Selector** — Dropdown untuk pilih model:
   - `Llama 3.3 70B` (text, default)
   - `Mixtral 8x7B` (fast)
   - `Llama 3.2 Vision` (analisa gambar)

2. **📎 Attach File** — Tombol paperclip, auto-switch ke Vision model, preview thumbnail

3. **🔗 Add URL** — Tombol link → input bar muncul → paste URL → konten di-fetch & dianalisa AI

4. **Diagnosa Foto** — Panel kiri sekarang pakai Groq Vision (tidak lagi butuh Python API)

## Verifikasi

- ✅ Route `POST /chat` terdaftar (`php artisan route:list`)
- ✅ Config cache cleared (`php artisan config:clear`)
- ✅ Groq API berhasil merespons: *"Penyakit blas pada padi adalah..."*
- ✅ Python/Flask API **tidak lagi dibutuhkan**

## 🚀 Panduan Deployment ke Vercel

Project ini sudah dikonfigurasi untuk deploy ke Vercel dengan database Supabase.

### 1. Persiapan Database (Supabase)
- Pastikan migration sudah dijalankan: `php artisan migrate`
- Gunakan connection string **Transaction Pooler** (Port 6543) atau **Session Pooler** (Port 5432).

### 2. Import Project ke Vercel
1. Buka [Vercel Dashboard](https://vercel.com/dashboard)
2. Klik **Add New...** → **Project**
3. Pilih repository GitHub `padi-web`
4. Framework Preset: **Other** (biarkan default)

### 3. Konfigurasi `vercel.json`
Pastikan file `vercel.json` ada di root project dengan isi:
```json
{
    "functions": {
        "api/index.php": { "runtime": "vercel-php@0.9.0" }
    }
}
```

### 4. Pilihan Model Groq (Opsional)
Kamu bisa mengganti model di `.env` (lokal) atau Environment Variables (Vercel):

- `llama-3.3-70b-versatile` (Default, cerdas)
- `llama-3.1-8b-instant` (Cepat & hemat)
- `qwen/qwen3-32b` (Alternatif seimbang)
- `meta-llama/llama-4-scout-17b-16e-instruct` (Vision & reasoning kuat)

### 5. Environment Variables
Di halaman konfigurasi Vercel, masukkan variable berikut:

| Key | Value |
|-----|-------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `true` |
| `APP_KEY` | (Copy dari .env) |
| `APP_URL` | `https://padi-web.vercel.app` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `aws-1-ap-southeast-1.pooler.supabase.com` |
| `DB_PORT` | `6543` |
| `DB_DATABASE` | `postgres` |
| `DB_USERNAME` | `postgres.zhphdbdhzadgvtbtjykr` |
| `DB_PASSWORD` | `Gtqt8TqRVxJ0QQJD` |
| `GROQ_API_KEY` | (Copy dari .env) |
| `GROQ_DEFAULT_MODEL` | `llama-3.1-8b-instant` |
| `GROQ_VISION_MODEL` | `meta-llama/llama-4-scout-17b-16e-instruct` |
| `LOG_CHANNEL` | `stderr` |

### 6. Deploy
- Klik **Deploy**
- Aplikasi siap diakses! 🎉

### 🚑 Troubleshooting (Jika Error di Vercel)

Jika muncul error saat Chatbot digunakan:

1.  **"Gateway Timeout" (504)**
    - Penyebab: Model `70b` terlalu lama menjawab (>10 detik).
    - Solusi: Ganti `GROQ_DEFAULT_MODEL` ke `llama-3.1-8b-instant`.

2.  **"Server Error" (500)**
    - Penyebab: Config salah atau API Key salah.
    - Solusi: Cek `GROQ_API_KEY` di Vercel Settings. Pastikan tidak ada spasi tambahan.

3.  **"Mixed Content" / "Network Error"**
    - Penyebab: Akses `http://` bukan `https://`.
    - Solusi: Pastikan akses website pakai `https://padi-web.vercel.app`.

4.  **Error Masih Sama Setelah Update?**
    - Solusi: Lakukan **Redeploy** di Vercel Dashboard agar perubahan kode terbaru aktif.
