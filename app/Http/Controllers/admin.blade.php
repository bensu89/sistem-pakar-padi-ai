<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penelitian Padi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 p-8 min-h-screen">

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Monitoring Data Penelitian</h1>
                <p class="text-gray-500">Instrumen Deteksi Penyakit Tanaman Padi Berbasis CNN</p>
            </div>
            <a href="{{ route('home') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 shadow transition">
                <i class="fa-solid fa-microscope mr-2"></i> Uji Sampel Baru
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Total Sampel Uji</p>
                <h2 class="text-4xl font-bold text-gray-800 mt-2">{{ $stats['total'] }} <span class="text-sm font-normal text-gray-400">Data</span></h2>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Terdeteksi Penyakit</p>
                <h2 class="text-4xl font-bold text-gray-800 mt-2">{{ $stats['sakit'] }} <span class="text-sm font-normal text-gray-400">Kejadian</span></h2>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Rata-rata Akurasi AI</p>
                <h2 class="text-4xl font-bold text-gray-800 mt-2">{{ $stats['akurasi'] }}%</h2>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-700">Log Data Harian</h3>
                <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">Real-time Update</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-100 text-xs font-bold text-gray-600 uppercase border-b">Waktu Input</th>
                            <th class="px-6 py-3 bg-gray-100 text-xs font-bold text-gray-600 uppercase border-b">Sampel Citra</th>
                            <th class="px-6 py-3 bg-gray-100 text-xs font-bold text-gray-600 uppercase border-b">Hasil Klasifikasi</th>
                            <th class="px-6 py-3 bg-gray-100 text-xs font-bold text-gray-600 uppercase border-b">Confidence Score</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach($reports as $data)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 border-b text-gray-600">{{ $data->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-6 py-4 border-b">
                                <img src="{{ asset($data->image_path) }}" class="h-16 w-16 object-cover rounded border shadow-sm cursor-pointer hover:scale-150 transition" title="Lihat Detail">
                            </td>
                            <td class="px-6 py-4 border-b">
                                <span class="px-3 py-1 rounded-full text-xs font-bold 
                                    {{ $data->disease_name == 'Healthy' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $data->disease_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 border-b">
                                <div class="flex items-center">
                                    <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $data->confidence }}%"></div>
                                    </div>
                                    <span class="font-medium">{{ number_format($data->confidence, 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                @if($reports->isEmpty())
                <div class="p-8 text-center text-gray-400">
                    <i class="fa-solid fa-database text-4xl mb-3"></i>
                    <p>Belum ada data penelitian yang masuk.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
<div class="my-10 border-t border-gray-300"></div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-red-200">
            <div class="p-5 border-b flex justify-between items-center bg-red-50">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                    <h2 class="font-bold text-red-800">Log Salah Upload (Ditolak AI)</h2>
                </div>
                <span class="text-xs bg-red-200 text-red-800 px-3 py-1 rounded-full font-bold">
                    {{ $sampah->count() }} File
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-3">Waktu</th>
                            <th class="px-6 py-3">Citra Ditolak</th>
                            <th class="px-6 py-3">Alasan</th>
                            <th class="px-6 py-3 text-center">Status</th>
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
                                    <img src="{{ asset($item->image_path) }}" class="object-cover w-full h-full opacity-60 grayscale hover:grayscale-0 transition">
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-red-600 font-semibold">{{ $item->reason }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-xs">
                                    Tidak Disimpan
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-6 text-center text-gray-400 italic">
                                Bersih! Belum ada user yang upload sembarangan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="h-20"></div>
</body>
</html>