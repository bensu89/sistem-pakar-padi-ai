<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pohaci AI — Sistem Pakar Padi Cerdas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .hero-pattern {
            background-color: #f0fdf4;
            background-image: radial-gradient(#16a34a 0.5px, transparent 0.5px), radial-gradient(#16a34a 0.5px, #f0fdf4 0.5px);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
            opacity: 0.5;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased overflow-x-hidden">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-green-500/30">
                        <i class="fa-solid fa-leaf text-white text-lg"></i>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-gray-900">Pohaci<span
                            class="text-green-600">AI</span></span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#fitur" class="text-gray-600 hover:text-green-600 font-medium transition">Fitur</a>
                    <a href="#cara-kerja" class="text-gray-600 hover:text-green-600 font-medium transition">Cara
                        Kerja</a>
                    <a href="#faq" class="text-gray-600 hover:text-green-600 font-medium transition">FAQ</a>
                </div>

                <!-- CTA Button -->
                <div class="hidden md:flex items-center gap-4">
                    @auth
                        <a href="{{ route('admin.index') }}"
                            class="text-gray-600 hover:text-green-600 font-medium text-sm">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-green-600 font-medium text-sm">Masuk
                            Admin</a>
                    @endauth
                    <a href="{{ route('home') }}"
                        class="bg-gray-900 hover:bg-black text-white px-6 py-2.5 rounded-full font-semibold transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Mulai Sekarang <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex item-center">
                    <a href="{{ route('home') }}" class="bg-green-600 text-white p-2 rounded-lg">
                        <i class="fa-solid fa-play"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute inset-0 hero-pattern z-0"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-white/50 to-white z-0"></div>

        <!-- Decoration Blobs -->
        <div
            class="absolute top-20 left-10 w-72 h-72 bg-green-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob">
        </div>
        <div
            class="absolute top-20 right-10 w-72 h-72 bg-emerald-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000">
        </div>
        <div
            class="absolute -bottom-32 left-1/2 w-96 h-96 bg-teal-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">

            <span
                class="inline-block py-1 px-3 rounded-full bg-green-100 text-green-700 text-xs font-bold tracking-wide mb-6 border border-green-200 shadow-sm">
                ✨ TEKNOLOGI PERTANIAN 4.0
            </span>

            <h1 class="text-5xl md:text-7xl font-extrabold text-gray-900 tracking-tight mb-8 leading-tight">
                Pohaci AI, <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-green-600 to-emerald-500">Ngariksa
                    Pare, Ngajaga Lemah Cai</span>
            </h1>

            <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-600 mb-12 leading-relaxed">
                Asisten cerdas untuk mendeteksi penyakit padi secara instan menggunakan AI Vision. Konsultasi masalah
                pertanian kapan saja, gratis.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('home') }}"
                    class="px-8 py-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-2xl shadow-xl shadow-green-600/30 transition transform hover:-translate-y-1 hover:scale-105 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-camera"></i> Scan Tanaman Sekarang
                </a>
                <a href="#demo-video"
                    class="px-8 py-4 bg-white hover:bg-gray-50 text-gray-700 font-bold rounded-2xl shadow-md border border-gray-200 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-play text-green-600"></i> Lihat Demo
                </a>
            </div>

            <!-- Stats -->
            <div class="mt-16 pt-8 border-t border-gray-200/60 max-w-4xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8">
                <div>
                    <h4 class="text-3xl font-bold text-gray-900">98%</h4>
                    <p class="text-sm text-gray-500 font-medium">Akurasi AI</p>
                </div>
                <div>
                    <h4 class="text-3xl font-bold text-gray-900">&lt; 2dtk</h4>
                    <p class="text-sm text-gray-500 font-medium">Kecepatan Diagnosa</p>
                </div>
                <div>
                    <h4 class="text-3xl font-bold text-gray-900">24/7</h4>
                    <p class="text-sm text-gray-500 font-medium">Siap Melayani</p>
                </div>
                <div>
                    <h4 class="text-3xl font-bold text-gray-900">Gratis</h4>
                    <p class="text-sm text-gray-500 font-medium">Untuk Petani</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-20 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Fitur Canggih Pohaci AI</h2>
                <p class="text-gray-500 max-w-2xl mx-auto">Gabungan teknologi Computer Vision dan Large Language Model
                    (LLM) untuk membantu petani.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div
                    class="bg-gray-50 rounded-2xl p-8 transition hover:shadow-xl hover:bg-white border border-transparent hover:border-gray-100 group">
                    <div
                        class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition duration-300">
                        <i class="fa-solid fa-eye text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Deteksi Penyakit Visual</h3>
                    <p class="text-gray-500 leading-relaxed">Cukup foto daun padi yang sakit, AI akan menganalisa jenis
                        penyakit dan memberikan solusi penanganan yang tepat.</p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="bg-gray-50 rounded-2xl p-8 transition hover:shadow-xl hover:bg-white border border-transparent hover:border-gray-100 group">
                    <div
                        class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition duration-300">
                        <i class="fa-solid fa-comments text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Chatbot Pertanian</h3>
                    <p class="text-gray-500 leading-relaxed">Tanya jawab seputar budidaya padi, pupuk, dan hama. Chatbot
                        pintar yang mengerti konteks pertanyaan Anda.</p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="bg-gray-50 rounded-2xl p-8 transition hover:shadow-xl hover:bg-white border border-transparent hover:border-gray-100 group">
                    <div
                        class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition duration-300">
                        <i class="fa-solid fa-chart-line text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Rekomendasi Pintar</h3>
                    <p class="text-gray-500 leading-relaxed">Dapatkan saran pemupukan dan perawatan berdasarkan kondisi
                        tanaman real-time.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 border-t border-gray-800">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-center md:text-left">
                <div class="flex items-center gap-2 justify-center md:justify-start mb-2">
                    <i class="fa-solid fa-leaf text-green-500"></i>
                    <span class="font-bold text-xl">Pohaci AI</span>
                </div>
                <p class="text-gray-400 text-sm">KKN Desa Cikurubuk 2026</p>
            </div>

            <div class="flex gap-6">
                <a href="#" class="text-gray-400 hover:text-white transition"><i
                        class="fa-brands fa-github text-xl"></i></a>
                <a href="#" class="text-gray-400 hover:text-white transition"><i
                        class="fa-brands fa-instagram text-xl"></i></a>
            </div>
        </div>
    </footer>

    <style>
        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }

        @keyframes blob {
            0% {
                transform: translate(0px, 0px) scale(1);
            }

            33% {
                transform: translate(30px, -50px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }

            100% {
                transform: translate(0px, 0px) scale(1);
            }
        }
    </style>
</body>

</html>