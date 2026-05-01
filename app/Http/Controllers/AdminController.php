<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PohaciMonitoring;
use App\Models\FailedUpload;
use App\Services\SupabaseStorageService;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminController extends Controller
{
    protected SupabaseStorageService $supabase;

    public function __construct(SupabaseStorageService $supabase)
    {
        $this->middleware('auth');
        $this->supabase = $supabase;
    }

    // 1. HALAMAN UTAMA DASHBOARD
    public function index(Request $request)
    {
        // Search parameter
        $search = $request->query('search', '');

        // Filter parameters untuk Monitoring
        $diagnosisConfidenceMin = $request->query('confidence_min');
        $diagnosisConfidenceMax = $request->query('confidence_max');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $analysisMode = $request->query('analysis_mode', '');
        $followupStatus = $request->query('followup_status', '');

        // Build Monitoring query with filters
        $diagnosisQuery = PohaciMonitoring::query();

        if ($search) {
            $diagnosisQuery->where(function ($query) use ($search) {
                $query->where('disease_name', 'LIKE', "%{$search}%")
                    ->orWhere('reporter_name', 'LIKE', "%{$search}%")
                    ->orWhere('reporter_email', 'LIKE', "%{$search}%")
                    ->orWhere('location_label', 'LIKE', "%{$search}%")
                    ->orWhere('coordinate_source', 'LIKE', "%{$search}%")
                    ->orWhere('analysis_mode', 'LIKE', "%{$search}%")
                    ->orWhere('followup_status', 'LIKE', "%{$search}%");
            });
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

        if ($analysisMode) {
            $diagnosisQuery->where('analysis_mode', $analysisMode);
        }

        if ($followupStatus) {
            $diagnosisQuery->where('followup_status', $followupStatus);
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
        if ($analysisMode) $activeFilters['analysis_mode'] = $analysisMode;
        if ($followupStatus) $activeFilters['followup_status'] = $followupStatus;

        return view('admin.index', compact('data', 'sampah', 'search', 'activeFilters',
            'diagnosisConfidenceMin', 'diagnosisConfidenceMax', 'dateFrom', 'dateTo', 'analysisMode', 'followupStatus'));
    }

    // 2. HAPUS DATA MONITORING
    public function destroy($id)
    {
        $item = PohaciMonitoring::find($id);
        if ($item) {
            $this->deleteImage($item->image_path);
            $item->delete();
        }
        return back()->with('success', 'Data monitoring berhasil dihapus');
    }

    // 3. EXPORT DATA KE EXCEL (.XLSX)
    public function export()
    {
        $fileName = 'Laporan_Pohaci_Monitoring_' . date('Y-m-d_H-i') . '.xlsx';

        return Excel::download(
            new class implements FromCollection, WithHeadings {
                public function headings(): array
                {
                    return ['No', 'Petani / Akun', 'Waktu Laporan', 'Lokasi', 'Sumber Koordinat', 'Hasil Diagnosa', 'Akurasi (%)', 'Model AI', 'NDVI', 'Mode Analisa', 'Rekomendasi', 'Status Tindak Lanjut', 'Lokasi Gambar'];
                }

                public function collection()
                {
                    return PohaciMonitoring::latest()->get()->map(function ($item, $index) {
                        return [
                            'No' => $index + 1,
                            'Petani / Akun' => $item->reporter_name ?? '-',
                            'Waktu Laporan' => $item->created_at->format('d-m-Y H:i'),
                            'Lokasi' => trim(($item->latitude ?? '-') . ', ' . ($item->longitude ?? '-')),
                            'Sumber Koordinat' => $item->coordinate_source ?? '-',
                            'Hasil Diagnosa' => $item->disease_name ?? '-',
                            'Akurasi (%)' => $item->confidence,
                            'Model AI' => $item->model_used ?? data_get($item->raw_payload, 'diagnosis.model_used') ?? data_get($item->raw_payload, 'diagnosis.model') ?? '-',
                            'NDVI' => $item->ndvi_value,
                            'Mode Analisa' => $item->analysis_mode,
                            'Rekomendasi' => $item->recommendation ?? $item->solution,
                            'Status Tindak Lanjut' => $item->followup_status ?? '-',
                            'Lokasi Gambar' => $item->image_path ? asset($item->image_path) : '-',
                        ];
                    });
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
            $this->deleteImage($item->image_path);
            $item->delete();
        }

        return back()->with('success', 'Data sampah berhasil dihapus permanen.');
    }

    protected function deleteImage(?string $imagePath): void
    {
        if (!$imagePath) {
            return;
        }

        if ($this->supabase->isSupabaseUrl($imagePath)) {
            $this->supabase->delete($imagePath);
        } elseif (file_exists(public_path($imagePath))) {
            unlink(public_path($imagePath));
        }
    }
}
