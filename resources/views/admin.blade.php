<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penelitian Padi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 p-8 min-h-screen" x-data="{ showModal: false, modalTitle: '', modalContent: '', copied: false }">

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Monitoring Data Penelitian</h1>
                <p class="text-gray-500">Instrumen Deteksi Penyakit Tanaman Padi Berbasis CNN & GenAI</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.export') }}" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700">
    <i class="fa-solid fa-file-excel"></i> Download CSV
</a>
                
                <a href="{{ route('home') }}" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 shadow transition flex items-center">
                    <i class="fa-solid fa-microscope mr-2"></i> Uji Sampel Baru
                </a>
            </div>
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
                <div class="flex items-center space-x-2 bg-green-100 px-3 py-1 rounded-full">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <span class="text-xs font-bold text-green-700">Live Update (30s)</span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-100 text-xs font-bold text-gray-600 uppercase border-b">Waktu</th>
                            <th class="px-6 py-3 bg-gray-100 text-xs font-bold text-gray-600 uppercase border-b">Citra</th>
                            <th class="px-6 py-3 bg-gray-100 text-xs font-bold text-gray-600 uppercase border-b">Hasil AI</th>
                            <th class="px-6 py-3 bg-gray-100 text-xs font-bold text-gray-600 uppercase border-b">Akurasi</th>
                            <th class="px-6 py-3 bg-gray-100 text-xs font-bold text-gray-600 uppercase border-b">Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach($reports as $data)
                        <tr class="hover:bg-gray-50 transition border-b border-gray-100">
                            <td class="px-6 py-4 text-gray-600">{{ $data->created_at->format('d/m H:i') }}</td>
                            <td class="px-6 py-4">
                                <img src="{{ asset($data->image_path) }}" class="h-12 w-12 object-cover rounded border shadow-sm cursor-pointer hover:scale-150 transition">
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold 
                                    {{ $data->disease_name == 'Healthy' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $data->disease_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-1.5 mr-2">
                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $data->confidence }}%"></div>
                                    </div>
                                    <span class="font-medium text-xs">{{ number_format($data->confidence, 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <button 
                                    @click="showModal = true; copied = false; modalTitle = '{{ $data->disease_name }}'; modalContent = `{{ str_replace('`', '\`', $data->solution) }}`"
                                    class="text-blue-600 hover:text-blue-800 text-xs font-bold border border-blue-200 px-3 py-1 rounded-md bg-blue-50 hover:bg-blue-100 transition">
                                    <i class="fa-solid fa-file-lines mr-1"></i> Baca Solusi
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
            <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2 p-6 animate-fade-in-down" @click.away="showModal = false">
                
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h3 class="text-xl font-bold text-gray-800" x-text="'Solusi Penanganan: ' + modalTitle"></h3>
                    <button @click="showModal = false" class="text-gray-500 hover:text-red-500">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>

                <div class="prose max-w-none text-gray-700 text-sm whitespace-pre-wrap leading-relaxed h-96 overflow-y-auto p-4 bg-gray-50 rounded border" x-text="modalContent">
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    
                    <button 
                        @click="navigator.clipboard.writeText(modalContent); copied = true; setTimeout(() => copied = false, 2000)" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition flex items-center shadow-sm"
                        :class="copied ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'"
                    >
                        <i class="fa-solid" :class="copied ? 'fa-check' : 'fa-copy'"></i>
                        <span class="ml-2 font-semibold" x-text="copied ? 'Tersalin!' : 'Salin Solusi'"></span>
                    </button>

                    <button @click="showModal = false" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        Tutup
                    </button>
                </div>

            </div>
        </div>

    </div>

    <script>
        setTimeout(function(){
           if(!document.querySelector('[x-data]').__x.$data.showModal) {
               window.location.reload();
           }
        }, 30000); 
    </script>

</body>
</html>