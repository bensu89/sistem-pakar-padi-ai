<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Penelitian Padi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-100 min-h-screen">

    <nav class="bg-white shadow-sm border-b px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <div class="bg-green-600 text-white w-10 h-10 flex items-center justify-center rounded-lg shadow">
                <i class="fa-solid fa-seedling text-xl"></i>
            </div>
            <div>
                <h1 class="font-bold text-gray-800 text-lg">Pohaci AI: Ngariksa Pare, Ngajaga Lemah Cai</h1>
                <p class="text-xs text-gray-500">Monitoring Penelitian Padi</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('home') }}"
                class="px-4 py-2 text-sm text-gray-600 hover:text-green-600 bg-gray-50 rounded-lg border border-gray-200 transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Cek Kesehatan
            </a>
        </div>
    </nav>

        <div class="max-w-7xl mx-auto p-6">

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-blue-500 flex justify-between items-center">
                <div>
                    <p class="text-gray-500 text-sm">Total Monitoring Masuk</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $data->total() }} <span
                            class="text-sm font-normal">Sampel</span></h3>
                </div>
                <i class="fa-solid fa-database text-blue-100 text-4xl"></i>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-red-500 flex justify-between items-center">
                <div>
                    <p class="text-gray-500 text-sm">Data Ditolak / Bukan Padi</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $sampah->total() }} <span
                            class="text-sm font-normal">File</span></h3>
                </div>
                <i class="fa-solid fa-trash-can text-red-100 text-4xl"></i>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-green-500 flex flex-col justify-center">
                <p class="text-gray-500 text-sm mb-2">Laporan Monitoring</p>
                <a href="{{ route('admin.export') }}"
                    class="bg-green-600 hover:bg-green-700 text-white text-center py-2 rounded-lg text-sm font-bold transition shadow-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-file-csv"></i> Download CSV / Excel
                </a>
            </div>
        </div>

        <!-- Filter UI -->
        <div class="bg-white rounded-xl shadow-sm p-5 mb-8 border border-gray-200">
                <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-funnel text-green-600"></i> Filter Monitoring
            </h3>

            <form method="GET" action="{{ route('admin.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">Cari (Petani / Penyakit / Lokasi)</label>
                        <div class="relative">
                            <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                            <input type="text" name="search" value="{{ $search ?? '' }}"
                                placeholder="Cari di sini..."
                                class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none">
                        </div>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ $dateTo ?? '' }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none">
                    </div>

                    <!-- Confidence Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">Filter Akurasi AI</label>
                        <select name="confidence_min"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none">
                            <option value="">Semua Akurasi</option>
                            <option value="0">Rendah (0-50%)</option>
                            <option value="50">Sedang (50-80%)</option>
                            <option value="80">Tinggi (>80%)</option>
                        </select>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="submit"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-sm transition flex items-center gap-2">
                            <i class="fa-solid fa-check"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.index') }}"
                        class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium text-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-redo"></i> Reset
                    </a>
                </div>

                <!-- Active Filters -->
                @if (count($activeFilters) > 0)
                    <div class="pt-4 border-t border-gray-200 flex flex-wrap gap-2">
                        @foreach ($activeFilters as $key => $value)
                            <span
                                class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium flex items-center gap-2">
                                @switch($key)
                                    @case('search')
                                        <i class="fa-solid fa-search"></i> {{ $value }}
                                    @break
                                    @case('date_from')
                                        <i class="fa-solid fa-calendar"></i> Dari: {{ $value }}
                                    @break
                                    @case('date_to')
                                        <i class="fa-solid fa-calendar"></i> Sampai: {{ $value }}
                                    @break
                                    @case('confidence_min')
                                        <i class="fa-solid fa-percentage"></i> Akurasi: ≥ {{ $value }}%
                                    @break
                                @endswitch
                            </span>
                        @endforeach
                    </div>
                @endif
            </form>
        </div>

        <!-- Data Valid Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 mb-10">
            <div class="p-5 border-b flex justify-between items-center bg-gray-50">
                <h2 class="font-bold text-gray-700">📋 Log Monitoring Penelitian</h2>
                <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-bold">Live Data</span>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b-2 border-gray-300 shadow-sm">
                        <tr>
                            <th class="px-6 py-3">Petani / Akun</th>
                            <th class="px-6 py-3">Waktu</th>
                            <th class="px-6 py-3">Hasil Diagnosa</th>
                            <th class="px-6 py-3">Akurasi / NDVI</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr class="bg-white border-b hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $item->reporter_name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->reporter_email ?? '-' }}</div>
                                    <div class="text-xs text-gray-400 mt-1">{{ $item->location_label ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $item->created_at->format('d M Y') }} <br>
                                    <span class="text-xs text-gray-400">{{ $item->created_at->format('H:i') }} WIB</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700">
                                        <div class="font-semibold">{{ $item->disease_name ?? '-' }}</div>
                                        <div class="text-xs text-gray-400 mt-1 line-clamp-2">
                                            {{ $item->recommendation ?? $item->solution ?? '-' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $confidence = $item->confidence !== null ? rtrim(rtrim(number_format((float) $item->confidence, 1), '0'), '.') . '%' : '-';
                                        $ndvi = $item->ndvi_value !== null ? number_format((float) $item->ndvi_value, 5) : '-';
                                    @endphp
                                    <div class="flex flex-col gap-1">
                                        <span class="px-2 py-1 rounded-full font-bold text-xs bg-blue-100 text-blue-700 inline-block">
                                            AK {{ $confidence }}
                                        </span>
                                        <span class="px-2 py-1 rounded-full font-bold text-xs bg-emerald-100 text-emerald-700 inline-block">
                                            NDVI {{ $ndvi }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold {{ $item->followup_status === 'done' ? 'bg-green-100 text-green-700' : ($item->followup_status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') }}">
                                        {{ $item->followup_status ?? 'pending' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button"
                                            class="text-gray-500 hover:text-green-600 hover:bg-green-50 p-2 rounded-lg transition"
                                            onclick="toggleMonitoringDetail('detail-row-{{ $item->id }}')"
                                            title="Lihat Detail">
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </button>
                                        <form action="{{ route('admin.destroy', $item->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition"
                                                title="Hapus Data"
                                                data-confirm="Hapus data diagnosa ini permanen?">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr id="detail-row-{{ $item->id }}" class="hidden bg-gray-50/70 border-b">
                                <td colspan="6" class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs text-gray-600">
                                        <div>
                                            <p class="font-semibold text-gray-500 uppercase">Koordinat</p>
                                            <p class="mt-1">{{ $item->latitude ?? '-' }}, {{ $item->longitude ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-500 uppercase">Sumber Koordinat</p>
                                            <p class="mt-1">{{ $item->coordinate_source ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-500 uppercase">Mode Analisa</p>
                                            <p class="mt-1">{{ $item->analysis_mode ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-500 uppercase">Foto</p>
                                            <p class="mt-1">
                                                @if($item->image_path)
                                                    <a href="{{ asset($item->image_path) }}" target="_blank" class="text-green-600 hover:underline">Buka gambar</a>
                                                @else
                                                    -
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400 bg-gray-50">
                                    <div class="flex flex-col items-center">
                                        <i class="fa-solid fa-folder-open text-3xl mb-2 text-gray-300"></i>
                                        <p>Belum ada data penelitian.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden">
                @forelse($data as $item)
                    <div class="border-b p-4 space-y-3 hover:bg-gray-50 transition">
                        <!-- Content -->
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs text-gray-500 font-semibold">Petani / Akun</p>
                                <p class="text-sm font-medium text-gray-800">{{ $item->reporter_name ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ $item->location_label ?? '-' }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 font-semibold">Waktu</p>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $item->created_at->format('d M Y H:i') }} WIB
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 font-semibold">Diagnosa / Akurasi</p>
                                <p class="text-base font-bold text-gray-800">{{ $item->disease_name ?? '-' }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $item->confidence !== null ? rtrim(rtrim(number_format((float) $item->confidence, 1), '0'), '.') . '%' : '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 font-semibold">NDVI / Status</p>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $item->ndvi_value !== null ? number_format((float) $item->ndvi_value, 5) : '-' }}
                                    · {{ $item->followup_status ?? 'pending' }}
                                </p>
                            </div>

                            <div>
                                <details class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <summary class="cursor-pointer text-sm font-semibold text-gray-700">Lihat detail</summary>
                                    <div class="mt-3 space-y-2 text-sm text-gray-600">
                                        <p><span class="font-semibold">Lokasi:</span> {{ $item->location_label ?? '-' }}</p>
                                        <p><span class="font-semibold">Koordinat:</span> {{ $item->latitude ?? '-' }}, {{ $item->longitude ?? '-' }}</p>
                                        <p><span class="font-semibold">Mode:</span> {{ $item->analysis_mode ?? '-' }}</p>
                                        <p><span class="font-semibold">Rekomendasi:</span> {{ $item->recommendation ?? $item->solution ?? '-' }}</p>
                                        @if($item->image_path)
                                            <a href="{{ asset($item->image_path) }}" target="_blank" class="inline-block text-green-600 hover:underline">Buka foto</a>
                                        @endif
                                    </div>
                                </details>
                            </div>
                        </div>

                        <!-- Action -->
                        <form action="{{ route('admin.destroy', $item->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full bg-red-50 hover:bg-red-100 text-red-600 py-2 rounded-lg font-medium text-sm transition flex items-center justify-center gap-2"
                                data-confirm="Hapus data diagnosa ini permanen?">
                                <i class="fa-solid fa-trash"></i> Hapus Data
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="p-10 text-center text-gray-400">
                        <i class="fa-solid fa-folder-open text-3xl mb-2 text-gray-300"></i>
                        <p>Belum ada data penelitian.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination Data Valid -->
            <div class="p-4 border-t bg-gray-50">
                {{ $data->links() }}
            </div>
        </div>

        <!-- Data Sampah Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-red-200">
            <div class="p-5 border-b flex justify-between items-center bg-red-50">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                    <h2 class="font-bold text-red-800">🗑️ Log Salah Upload (Ditolak AI)</h2>
                </div>
                <span class="text-xs bg-white border border-red-200 text-red-600 px-3 py-1 rounded-full font-bold">
                    Filter Aktif
                </span>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-red-50/50 border-b-2 border-red-300 shadow-sm">
                        <tr>
                            <th class="px-6 py-3">Waktu</th>
                            <th class="px-6 py-3">Citra Ditolak</th>
                            <th class="px-6 py-3">Alasan Penolakan</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sampah as $item)
                            <tr class="bg-white border-b hover:bg-red-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $item->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-12 w-12 rounded bg-gray-100 border border-gray-200 overflow-hidden">
                                        <img src="{{ asset($item->image_path) }}"
                                            class="object-cover w-full h-full opacity-60 grayscale hover:grayscale-0 transition">
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-ban text-red-500"></i>
                                        <span class="text-red-600 font-semibold">{{ $item->reason }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('admin.destroyFailed', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-500 hover:bg-red-100 p-2 rounded-lg transition border border-red-200"
                                            title="Hapus Permanen"
                                            data-confirm="Hapus data sampah ini permanen?">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-gray-400 italic bg-gray-50">
                                    Bersih! Belum ada user yang upload sembarangan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden">
                @forelse($sampah as $item)
                    <div class="border-b p-4 space-y-3 hover:bg-red-50 transition">
                        <!-- Image -->
                        <div class="h-24 w-full rounded-lg bg-gray-100 border border-gray-200 overflow-hidden">
                            <img src="{{ asset($item->image_path) }}"
                                class="object-cover w-full h-full opacity-60 grayscale hover:grayscale-0 transition"
                                alt="Ditolak">
                        </div>

                        <!-- Content -->
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs text-gray-500 font-semibold">Waktu</p>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $item->created_at->format('d M Y H:i') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 font-semibold">Alasan Penolakan</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <i class="fa-solid fa-ban text-red-500 text-sm"></i>
                                    <span class="text-red-600 font-semibold">{{ $item->reason }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action -->
                        <form action="{{ route('admin.destroyFailed', $item->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full bg-red-50 hover:bg-red-100 text-red-600 py-2 rounded-lg font-medium text-sm transition flex items-center justify-center gap-2"
                                data-confirm="Hapus data sampah ini permanen?">
                                <i class="fa-solid fa-trash"></i> Hapus Permanen
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="p-10 text-center text-gray-400">
                        <i class="fa-solid fa-check-circle text-3xl mb-2 text-green-300"></i>
                        <p>Bersih! Belum ada user yang upload sembarangan.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination Data Sampah -->
            <div class="p-4 border-t bg-gray-50">
                {{ $sampah->links() }}
            </div>
        </div>

    </div>

    <script>
        // ============================================================
        // TOAST NOTIFICATION SYSTEM
        // ============================================================
        function showToast(type, message, duration = 4000) {
            const id = 'toast-' + Date.now();

            const bgColor = {
                'success': 'bg-green-500',
                'error': 'bg-red-500',
                'info': 'bg-blue-500',
                'warning': 'bg-yellow-500'
            }[type] || 'bg-gray-500';

            const icon = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'info': 'fa-info-circle',
                'warning': 'fa-triangle-exclamation'
            }[type] || 'fa-bell';

            const toastHTML = `
                <div id="${id}" class="fixed bottom-4 right-4 ${bgColor} text-white px-5 py-3 rounded-lg shadow-lg flex items-center gap-3 z-50 max-w-sm" style="animation: slideIn 0.3s ease-out;">
                    <i class="fa-solid ${icon}"></i>
                    <span>${escapeHtml(message)}</span>
                    <button onclick="document.getElementById('${id}').remove()" class="ml-2 text-white/70 hover:text-white transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>`;

            document.body.insertAdjacentHTML('beforeend', toastHTML);

            setTimeout(() => {
                const el = document.getElementById(id);
                if (el) el.remove();
            }, duration);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }

        function toggleMonitoringDetail(id) {
            const row = document.getElementById(id);
            if (!row) return;

            row.classList.toggle('hidden');
        }

        // ============================================================
        // CONFIRM DIALOG SYSTEM
        // ============================================================
        function showConfirmDialog(title, message, confirmText = 'Hapus', cancelText = 'Batal') {
            return new Promise((resolve) => {
                const id = 'dialog-' + Date.now();

                const dialogHTML = `
                    <div id="${id}-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40"></div>
                    <div id="${id}" class="fixed inset-0 flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-bold text-gray-900">${escapeHtml(title)}</h3>
                            </div>
                            <div class="p-6">
                                <p class="text-gray-600">${escapeHtml(message)}</p>
                            </div>
                            <div class="p-6 border-t border-gray-200 flex gap-3 justify-end">
                                <button type="button" onclick="document.getElementById('${id}').remove(); document.getElementById('${id}-overlay').remove(); window.confirmDialogResolve(false);" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition">
                                    ${escapeHtml(cancelText)}
                                </button>
                                <button type="button" onclick="document.getElementById('${id}').remove(); document.getElementById('${id}-overlay').remove(); window.confirmDialogResolve(true);" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition">
                                    ${escapeHtml(confirmText)}
                                </button>
                            </div>
                        </div>
                    </div>`;

                window.confirmDialogResolve = (result) => resolve(result);

                document.body.insertAdjacentHTML('beforeend', dialogHTML);
            });
        }

        // Handle form submissions with data-confirm attribute
        document.addEventListener('submit', async function(e) {
            const submitBtn = e.submitter;
            if (!submitBtn) return;

            const confirmMsg = submitBtn.getAttribute('data-confirm');
            if (confirmMsg) {
                e.preventDefault();

                const confirmed = await showConfirmDialog('Konfirmasi', confirmMsg, 'Hapus', 'Batal');
                if (confirmed) {
                    e.target.submit();
                }
            }
        });

        // Auto-dismiss success message from Laravel (if any)
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.search.includes('success') || document.querySelector('[role="alert"]')) {
                setTimeout(() => {
                    showToast('success', 'Data berhasil dihapus!');
                }, 100);
            }
        });

        @if ($errors->any())
            showToast('error', "{{ $errors->first() }}");
        @endif
    </script>

    <style>
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>

</body>

</html>
