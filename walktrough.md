🔍 Audit Lengkap — Proyek Padi Web (Sistem Pakar Padi AI)
Ringkasan Arsitektur
Komponen	Detail
Framework	Laravel 8.x (PHP ^7.3|^8.0)
Database	MySQL (padi_db) via Eloquent ORM
Frontend	Blade Templates + Tailwind CDN + Font Awesome + Axios
AI Service	Flask/Python API di http://127.0.0.1:5000 (external)
Auth	Laravel Sanctum (terpasang, belum dipakai di web routes)
Asset Bundler	Laravel Mix (Webpack)
📂 Peta File Utama
padi-web/
├── app/
│   ├── Http/Controllers/
│   │   ├── DiagnosisController.php    ← Upload & analisa gambar
│   │   ├── AdminController.php        ← Dashboard monitoring
│   │   ├── admin.blade.php            ⚠️ FILE SALAH TEMPAT
│   │   └── New Text Document.txt      ⚠️ FILE SAMPAH
│   └── Models/
│       ├── Diagnosis.php              ← Data diagnosa valid
│       ├── FailedUpload.php           ← Data upload ditolak
│       └── User.php                   ← Default Laravel user
├── database/migrations/
│   ├── create_diagnoses_table         ← id, image_path, disease_name, confidence, solution
│   └── create_failed_uploads_table    ← id, image_path, reason
├── resources/views/
│   ├── home.blade.php                 ← Halaman utama (scan + chatbot)
│   ├── result.blade.php               ← Halaman hasil (TIDAK DIPAKAI)
│   ├── admin.blade.php                ← ⚠️ View lama/orphan (BROKEN)
│   ├── admin/index.blade.php          ← Dashboard aktif
│   ├── welcome.blade.php              ← Default Laravel welcome
│   └── New Text Document.txt          ⚠️ FILE SAMPAH
├── routes/
│   ├── web.php                        ← 6 route (GET/POST/DELETE)
│   └── api.php                        ← Default Sanctum route only
└── .env                               ← Konfigurasi lokal
🛤️ Route Map
Method	URI	Controller	Fungsi
GET	/	DiagnosisController@index	Halaman scan + chat
POST	/analyze	DiagnosisController@analyze	Upload gambar → AI → simpan
GET	/monitoring-penelitian	AdminController@index	Dashboard admin
DELETE	/monitoring-penelitian/{id}	AdminController@destroy	Hapus diagnosa valid
GET	/export-laporan	AdminController@export	Download CSV
DELETE	/hapus-sampah/{id}	AdminController@destroyFailed	Hapus data sampah
🔬 Analisis Controller
DiagnosisController
index()
 → render 
home.blade.php
analyze()
 → upload gambar ke storage/app/public/uploads/, kirim ke Flask API (POST http://127.0.0.1:5000/predict), simpan hasil ke tabel diagnoses atau failed_uploads
Return JSON response (diproses oleh Axios di frontend)
AdminController
index() → ambil semua Diagnosis dan FailedUpload, kirim ke view admin.index
destroy($id) → hapus record + file gambar dari diagnosa valid
export() → generate dan download file CSV
destroyFailed($id) → hapus record + file gambar dari data sampah
🗄️ Analisis Model & Migrasi
Model	Tabel	Kolom	Guard
Diagnosis	diagnoses	id, image_path, disease_name, confidence (float), solution (text), timestamps	$guarded = []
FailedUpload	failed_uploads	id, image_path, reason (default: 'Bukan Daun Padi'), timestamps	$guarded = []
User	users	Default Laravel	$fillable
🖼️ Analisis View
home.blade.php (Aktif ✅)
Layout 2 panel: kiri upload foto, kanan chatbot AI
Upload via Axios ke /analyze, result ditampilkan inline (tanpa redirect)
Chat mengirim ke http://127.0.0.1:5000/chat (Flask)
Menggunakan Tailwind CDN + Font Awesome
admin/index.blade.php (Aktif ✅)
Dashboard dengan 3 stat card + 2 tabel (data valid & data sampah)
Form delete menggunakan CSRF + method DELETE
Tidak ada pagination (semua data di-load sekaligus)
result.blade.php (❌ Tidak terpakai)
View standalone untuk menampilkan hasil diagnosa
Referensi $image dan $data — tidak ada controller yang menggunakannya
admin.blade.php (❌ Orphan / Broken)
View lama versi dashboard dengan Alpine.js
Referensi $stats dan $reports — variabel ini TIDAK dikirim oleh AdminController@index
Jika dipakai, akan menyebabkan error "Undefined variable"
🚨 Temuan & Masalah
🔴 KRITIS (Harus Diperbaiki)
#	Masalah	File	Detail
1	Missing CSRF Token di AJAX	
home.blade.php
Form upload via Axios tidak mengirim X-CSRF-TOKEN header. POST ke /analyze akan gagal dengan error 419 (CSRF Token Mismatch) kecuali middleware di-disable.
2	Broken View admin.blade.php	
admin.blade.php
Mereferensikan $stats dan $reports yang tidak di-pass oleh controller. View ini orphan dan akan crash jika dipanggil.
3	No Pagination	
AdminController.php
Diagnosis::latest()->get() dan FailedUpload::latest()->get() me-load semua record. Jika data banyak (ribuan), halaman akan sangat lambat atau crash.
4	HTML injection pada chat	
home.blade.php
addUserMessage(text) langsung memasukkan input user sebagai HTML via insertAdjacentHTML tanpa sanitasi. Bisa dieksploitasi untuk XSS.
🟡 SECURITY
#	Masalah	File	Detail
5	$guarded = [] di Models	
Diagnosis.php
, 
FailedUpload.php
Semua kolom bisa di-mass-assign. Sebaiknya gunakan $fillable secara eksplisit.
6	Admin tanpa autentikasi	
web.php
Route /monitoring-penelitian, delete, dan export tidak dilindungi middleware auth. Siapa saja bisa mengakses dan menghapus data.
7	APP_DEBUG=true di .env	
.env
Jika deployed ke production, error detail (termasuk query SQL dan path) akan terekspos ke publik.
🟢 HOUSEKEEPING
#	Masalah	File	Detail
8	File salah tempat	app/Http/Controllers/admin.blade.php	Blade template ada di folder Controllers, bukan di resources/views/.
9	File sampah	app/Http/Controllers/New Text Document.txt, resources/views/New Text Document.txt	File teks kosong/draft yang tidak perlu.
10	View result.blade.php tidak terpakai	
result.blade.php
Tidak ada route atau controller yang me-render view ini. Dead code.
11	View welcome.blade.php default	
welcome.blade.php
Blade default bawaan instalasi Laravel yang tidak dipakai.
12	admin/index.blade.php tidak tertutup	
admin/index.blade.php
Tag </table>, </div>, </body>, </html> tidak lengkap/hilang di akhir file (terpotong di baris 183).
13	APP_NAME masih "Laravel"	.env	Sebaiknya diubah ke "Sistem Pakar Padi AI" atau sejenisnya.
✅ Hal yang Sudah Baik
✅ Validasi file upload (tipe gambar, maks 5MB)
✅ Logika filtering AI (padi vs bukan padi) sudah benar
✅ CSV export berfungsi dengan format rapi
✅ UI modern dan responsif (Tailwind)
✅ Chatbot dengan context switching (umum ↔ penyakit spesifik)
✅ Delete data juga menghapus file fisik (hemat storage)
✅ Reset function di frontend berfungsi baik
✅ Migration schema sudah sesuai kebutuhan
