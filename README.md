Nama Project: Aplikasi Deteksi Penyakit Padi (Padi Web)
Overview: Project ini adalah sebuah aplikasi web berbasis Laravel yang dirancang untuk membantu mendeteksi penyakit pada tanaman padi. Aplikasi ini memiliki fitur utama untuk menganalisa kondisi tanaman (diagnosis) dan dashboard monitoring untuk admin atau peneliti.
Fitur Utama:
Diagnosis Penyakit (User):
Halaman Utama (/): Antarmuka bagi pengguna untuk memulai proses deteksi (scan/upload gambar).
Proses Analisa (/analyze): Mengirimkan data gambar padi untuk dianalisa (kemungkinan terintegrasi dengan layanan AI/ML eksternal atau internal).
Hasil Diagnosis: Menampilkan hasil deteksi penyakit kepada pengguna.
Dashboard Admin & Monitoring (/monitoring-penelitian):
Halaman khusus admin untuk memantau data penelitian atau riwayat diagnosis yang masuk.
Fitur untuk melihat detail laporan.
Manajemen Data Laporan:
Hapus Data (DELETE /monitoring-penelitian/{id}): Menghapus data laporan yang valid.
Hapus Sampah (DELETE /hapus-sampah/{id}): Menghapus data yang dianggap tidak valid atau salah upload.
Export Laporan (/export-laporan): Mengunduh data laporan dalam format file (Excel/CSV).
Teknologi (Tech Stack):
Backend Framework: Laravel 8.x (PHP ^7.3|^8.0)
Frontend: Blade Templates (View engine bawaan Laravel)
Database: MySQL (Menggunakan Eloquent ORM)
HTTP Client: Guzzle (kemungkinan digunakan untuk komunikasi dengan service ML/Python)
Autentikasi: Laravel Sanctum (untuk API token management)
Struktur Kode Penting:
Controllers (app/Http/Controllers):
DiagnosisController.php: Mengatur logika diagnosis dan interaksi user di halaman depan.
AdminController.php: Mengelola dashboard admin, monitoring data, dan export laporan.
Views (resources/views):
home.blade.php: Tampilan halaman utama.
admin.blade.php & admin/: Tampilan dashboard admin.
result.blade.php: Tampilan hasil diagnosis.
Routes (routes/web.php): Mendefinisikan alur aplikasi mulai dari halaman depan, proses analisa, hingga fitur administratif.
Catatan: Terlihat ada beberapa file duplikat atau salah penempatan seperti admin.blade.php di dalam folder Controllers yang sebaiknya dibersihkan.
