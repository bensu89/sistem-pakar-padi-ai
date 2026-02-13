<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diagnosis;      // Model Data Valid
use App\Models\FailedUpload;   // Model Data Sampah

class AdminController extends Controller
{
    // Lindungi semua method dengan auth middleware
    public function __construct()
    {
        $this->middleware('auth');
    }

    // 1. HALAMAN UTAMA DASHBOARD
    public function index()
    {
        // Data Valid (Padi) - Urutkan dari terbaru, dengan pagination
        $data = Diagnosis::latest()->paginate(15);

        // Data Sampah (Salah Upload) - Urutkan dari terbaru, dengan pagination
        $sampah = FailedUpload::latest()->paginate(10);

        return view('admin.index', compact('data', 'sampah'));
    }

    // 2. HAPUS DATA DIAGNOSA
    public function destroy($id)
    {
        $item = Diagnosis::find($id);
        if ($item) {
            // Hapus file fisik gambar agar hemat penyimpanan
            if (file_exists(public_path($item->image_path))) {
                unlink(public_path($item->image_path));
            }
            $item->delete();
        }
        return back()->with('success', 'Data berhasil dihapus');
    }

    // 3. EXPORT DATA KE CSV (EXCEL)
    public function export()
    {
        $fileName = 'Laporan_Padi_' . date('Y-m-d_H-i') . '.csv';
        $data = Diagnosis::latest()->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Header Kolom Excel
            fputcsv($file, array('No', 'Tanggal Scan', 'Nama Penyakit', 'Akurasi (%)', 'Solusi AI', 'Lokasi Gambar'));

            // Isi Data
            foreach ($data as $key => $row) {
                fputcsv($file, array(
                    $key + 1,
                    $row->created_at->format('d-m-Y H:i'),
                    $row->disease_name,
                    $row->confidence . '%',
                    $row->solution,
                    asset($row->image_path)
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    // 4. HAPUS DATA SAMPAH (FAILED UPLOADS)
    public function destroyFailed($id)
    {
        $item = FailedUpload::find($id);

        if ($item) {
            // Hapus file gambarnya juga biar storage lega
            // Cek path, kadang tersimpan relative atau full path
            // Kita coba public_path() standar
            if (file_exists(public_path($item->image_path))) {
                unlink(public_path($item->image_path));
            }

            $item->delete();
        }

        return back()->with('success', 'Data sampah berhasil dihapus permanen.');
    }
}