<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diagnosis;      // Model Data Valid
use App\Models\FailedUpload;   // Model Data Sampah
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminController extends Controller
{
    // Lindungi semua method dengan auth middleware
    public function __construct()
    {
        $this->middleware('auth');
    }

    // 1. HALAMAN UTAMA DASHBOARD
    public function index(Request $request)
    {
        // Search parameter
        $search = $request->query('search', '');

        // Filter parameters untuk Diagnosis
        $diagnosisConfidenceMin = $request->query('confidence_min');
        $diagnosisConfidenceMax = $request->query('confidence_max');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        // Build Diagnosis query with filters
        $diagnosisQuery = Diagnosis::query();

        if ($search) {
            $diagnosisQuery->where('disease_name', 'LIKE', "%{$search}%");
        }

        if ($diagnosisConfidenceMin !== null) {
            $diagnosisQuery->where('confidence', '>=', (float)$diagnosisConfidenceMin);
        }

        if ($diagnosisConfidenceMax !== null) {
            $diagnosisQuery->where('confidence', '<=', (float)$diagnosisConfidenceMax);
        }

        if ($dateFrom) {
            $diagnosisQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $diagnosisQuery->whereDate('created_at', '<=', $dateTo);
        }

        $data = $diagnosisQuery->latest()->paginate(15)->appends($request->query());

        // Build FailedUpload query with search filter
        $failedQuery = FailedUpload::query();

        if ($search) {
            $failedQuery->where('reason', 'LIKE', "%{$search}%");
        }

        if ($dateFrom) {
            $failedQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $failedQuery->whereDate('created_at', '<=', $dateTo);
        }

        $sampah = $failedQuery->latest()->paginate(10)->appends($request->query());

        // Build active filters info for UI
        $activeFilters = [];
        if ($search) $activeFilters['search'] = $search;
        if ($diagnosisConfidenceMin !== null) $activeFilters['confidence_min'] = $diagnosisConfidenceMin;
        if ($diagnosisConfidenceMax !== null) $activeFilters['confidence_max'] = $diagnosisConfidenceMax;
        if ($dateFrom) $activeFilters['date_from'] = $dateFrom;
        if ($dateTo) $activeFilters['date_to'] = $dateTo;

        return view('admin.index', compact('data', 'sampah', 'search', 'activeFilters',
            'diagnosisConfidenceMin', 'diagnosisConfidenceMax', 'dateFrom', 'dateTo'));
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

    // 3. EXPORT DATA KE EXCEL (.XLSX)
    public function export()
    {
        $fileName = 'Laporan_Padi_' . date('Y-m-d_H-i') . '.xlsx';

        // Get all diagnosis data
        $data = Diagnosis::latest()->get()->map(function($item, $index) {
            return [
                'No' => $index + 1,
                'Tanggal Scan' => $item->created_at->format('d-m-Y H:i'),
                'Nama Penyakit' => $item->disease_name,
                'Akurasi (%)' => $item->confidence,
                'Solusi AI' => $item->solution,
                'Lokasi Gambar' => asset($item->image_path)
            ];
        });

        // Create Excel collection
        return Excel::download(
            new class implements FromCollection, WithHeadings {
                public function headings(): array
                {
                    return ['No', 'Tanggal Scan', 'Nama Penyakit', 'Akurasi (%)', 'Solusi AI', 'Lokasi Gambar'];
                }

                public function collection()
                {
                    $data = Diagnosis::latest()->get()->map(function($item, $index) {
                        return [
                            'No' => $index + 1,
                            'Tanggal Scan' => $item->created_at->format('d-m-Y H:i'),
                            'Nama Penyakit' => $item->disease_name,
                            'Akurasi (%)' => $item->confidence,
                            'Solusi AI' => $item->solution,
                            'Lokasi Gambar' => asset($item->image_path)
                        ];
                    });
                    return collect($data);
                }
            },
            $fileName
        );
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