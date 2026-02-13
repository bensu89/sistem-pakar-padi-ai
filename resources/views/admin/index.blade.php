<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penelitian Padi</title>
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
                <p class="text-xs text-gray-500">Monitoring Diagnosa Daun Padi</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('home') }}"
                class="px-4 py-2 text-sm text-gray-600 hover:text-green-600 bg-gray-50 rounded-lg border border-gray-200 transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Scan
            </a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-6">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-blue-500 flex justify-between items-center">
                <div>
                    <p class="text-gray-500 text-sm">Total Data Valid</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $data->count() }} <span
                            class="text-sm font-normal">Sampel</span></h3>
                </div>
                <i class="fa-solid fa-database text-blue-100 text-4xl"></i>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-red-500 flex justify-between items-center">
                <div>
                    <p class="text-gray-500 text-sm">Ditolak (Bukan Padi)</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $sampah->count() }} <span
                            class="text-sm font-normal">File</span></h3>
                </div>
                <i class="fa-solid fa-trash-can text-red-100 text-4xl"></i>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-green-500 flex flex-col justify-center">
                <p class="text-gray-500 text-sm mb-2">Laporan Penelitian</p>
                <a href="{{ route('admin.export') }}"
                    class="bg-green-600 hover:bg-green-700 text-white text-center py-2 rounded-lg text-sm font-bold transition shadow-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-file-csv"></i> Download CSV / Excel
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 mb-10">
            <div class="p-5 border-b flex justify-between items-center bg-gray-50">
                <h2 class="font-bold text-gray-700">📋 Log Data Valid (Penyakit Terdeteksi)</h2>
                <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-bold">Live Data</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th class="px-6 py-3">Waktu</th>
                            <th class="px-6 py-3">Citra Daun</th>
                            <th class="px-6 py-3">Hasil Diagnosa</th>
                            <th class="px-6 py-3">Akurasi AI</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr class="bg-white border-b hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $item->created_at->format('d M Y') }} <br>
                                    <span class="text-xs text-gray-400">{{ $item->created_at->format('H:i') }} WIB</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="h-16 w-16 rounded-lg overflow-hidden border border-gray-200 shadow-sm group relative">
                                        <img src="{{ asset($item->image_path) }}"
                                            class="object-cover w-full h-full transform group-hover:scale-110 transition duration-300"
                                            alt="Padi">
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-800 text-base block">{{ $item->disease_name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $conf = floatval($item->confidence);
                                        $color = $conf > 80 ? 'bg-green-100 text-green-700' : ($conf > 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                                    @endphp
                                    <span class="{{ $color }} px-2 py-1 rounded-full font-bold text-xs">
                                        {{ number_format($conf, 2) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('admin.destroy', $item->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition"
                                            title="Hapus Data">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 bg-gray-50">
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

            <!-- Pagination Data Valid -->
            <div class="p-4 border-t bg-gray-50">
                {{ $data->links() }}
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-red-200">
            <div class="p-5 border-b flex justify-between items-center bg-red-50">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                    <h2 class="font-bold text-red-800">🗑️ Log Salah Upload (Ditolak AI)</h2>
                </div>
                <span class="text-xs bg-white border border-red-200 text-red-600 px-3 py-1 rounded-full font-bold">
                    Filter Satpam Aktif
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-red-50/50">
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
                                    <form action="{{ route('admin.destroyFailed', $item->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus permanen data sampah ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-500 hover:bg-red-100 p-2 rounded-lg transition border border-red-200"
                                            title="Hapus Permanen">
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

            <!-- Pagination Data Sampah -->
            <div class="p-4 border-t bg-gray-50">
                {{ $sampah->links() }}
            </div>
        </div>

    </div>

</body>

</html>