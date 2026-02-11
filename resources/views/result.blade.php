<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Diagnosa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen py-10 px-4">

    <div class="max-w-lg mx-auto bg-white rounded-3xl shadow-2xl overflow-hidden">
        
        <div class="relative h-64 bg-gray-200">
            <img src="{{ $image }}" alt="Uploaded Leaf" class="w-full h-full object-cover">
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6">
                <h2 class="text-white text-xl font-bold">Hasil Analisa AI</h2>
            </div>
        </div>

        <div class="p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-sm text-gray-500 uppercase tracking-wider font-semibold">Diagnosa Penyakit</p>
                    <h1 class="text-3xl font-extrabold text-gray-800 mt-1">{{ $data['disease_name'] }}</h1>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Akurasi</p>
                    <span class="inline-block bg-green-100 text-green-800 text-lg font-bold px-3 py-1 rounded-lg">
                        {{ $data['confidence'] }}%
                    </span>
                </div>
            </div>

            <hr class="border-gray-100 my-6">

            <div class="bg-blue-50 rounded-xl p-5 border border-blue-100">
                <h3 class="flex items-center text-blue-800 font-bold mb-3">
                    <i class="fa-solid fa-user-doctor mr-2"></i> Rekomendasi Solusi
                </h3>
                <p class="text-gray-700 leading-relaxed text-sm">
                    {{ $data['solution'] }}
                </p>
            </div>

            <a href="{{ route('home') }}" class="mt-8 block w-full bg-gray-800 hover:bg-gray-900 text-white text-center font-bold py-3 rounded-xl transition duration-300">
                <i class="fa-solid fa-rotate-right mr-2"></i> Scan Ulang
            </a>
        </div>
    </div>

</body>
</html>