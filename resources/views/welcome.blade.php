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
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.index') }}"
                                class="text-gray-600 hover:text-green-600 font-medium text-sm">Dashboard Admin</a>
                        @endif
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
                <div class="md:hidden flex items-center gap-2">
                    <button id="mobile-menu-toggle"
                        class="p-2 rounded-lg hover:bg-gray-100 transition"
                        aria-label="Toggle menu">
                        <i class="fa-solid fa-bars text-gray-900 text-xl"></i>
                    </button>
                    <a href="{{ route('home') }}" class="bg-green-600 text-white p-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fa-solid fa-play"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Dropdown -->
    <div id="mobile-menu"
        class="hidden md:hidden fixed top-20 left-0 right-0 bg-white border-b border-gray-200 shadow-lg z-40">
        <div class="px-4 py-4 space-y-2 flex flex-col">
            <a href="#fitur"
                class="px-4 py-2 rounded-lg text-gray-700 hover:text-green-600 hover:bg-gray-100 font-medium transition">
                <i class="fa-solid fa-star mr-2"></i>Fitur
            </a>
            <a href="#cara-kerja"
                class="px-4 py-2 rounded-lg text-gray-700 hover:text-green-600 hover:bg-gray-100 font-medium transition">
                <i class="fa-solid fa-book mr-2"></i>Cara Kerja
            </a>
            <a href="#faq"
                class="px-4 py-2 rounded-lg text-gray-700 hover:text-green-600 hover:bg-gray-100 font-medium transition">
                <i class="fa-solid fa-question-circle mr-2"></i>FAQ
            </a>

            <!-- Auth Links -->
            <div class="pt-4 border-t border-gray-200 space-y-2">
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.index') }}"
                            class="px-4 py-2 rounded-lg text-gray-700 hover:text-green-600 hover:bg-gray-100 font-medium transition">
                            <i class="fa-solid fa-gauge-high mr-2"></i>Dashboard Admin
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                        class="px-4 py-2 rounded-lg text-gray-700 hover:text-green-600 hover:bg-gray-100 font-medium transition">
                        <i class="fa-solid fa-lock mr-2"></i>Masuk Admin
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="relative pt-24 pb-12 lg:pt-40 lg:pb-32 overflow-hidden bg-gradient-to-b from-green-50 to-white">
        <!-- Decoration -->
        <div class="absolute inset-0 hero-pattern opacity-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">

            <div
                class="inline-flex items-center gap-2 bg-green-100 border border-green-200 rounded-full px-4 py-1.5 mb-8 shadow-sm">
                <span class="flex h-3 w-3 relative">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-green-800 text-sm font-bold tracking-wide uppercase">Teknologi Tepat Guna</span>
            </div>

            <h1 class="text-4xl md:text-7xl font-extrabold text-gray-900 tracking-tight mb-6 leading-tight">
                Pohaci AI <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-green-600 via-emerald-500 to-teal-500">
                    Ngariksa Pare, Ngajaga Lemah Cai
                </span>
            </h1>

            <p class="mt-6 max-w-3xl mx-auto text-xl md:text-2xl text-gray-600 mb-10 leading-relaxed font-light">
                Pohaci AI adalah aplikasi sistem pakar berbasis web untuk deteksi penyakit tanaman padi dan konsultasi
                pertanian.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-5">
                <a href="{{ route('home') }}"
                    class="group relative inline-flex items-center justify-center px-8 py-5 text-lg font-bold text-white transition-all duration-200 bg-green-600 font-pj rounded-2xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-600 hover:bg-green-700 shadow-xl hover:shadow-2xl hover:-translate-y-1">
                    <span class="mr-3 text-2xl"><i class="fa-solid fa-camera"></i></span>
                    Cek Kesehatan Padi
                    <div
                        class="absolute -top-3 -right-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-md animate-bounce">
                        GRATIS!
                    </div>
                </a>

                <a href="#fitur"
                    class="inline-flex items-center justify-center px-8 py-5 text-lg font-bold text-gray-700 transition-all duration-200 bg-white border-2 border-gray-200 font-pj rounded-2xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 hover:bg-gray-50 hover:text-green-600 hover:border-green-200 shadow-sm hover:shadow-md">
                    <i class="fa-solid fa-circle-play mr-2 text-green-500 text-xl"></i>
                    Cara Pakai
                </a>
            </div>

            <!-- Stats with Icons -->
            <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8 max-w-5xl mx-auto">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="text-green-500 text-3xl mb-2"><i class="fa-solid fa-bullseye"></i></div>
                    <h4 class="text-4xl font-bold text-gray-900">98%</h4>
                    <p class="text-gray-500 font-medium">Akurasi Deteksi</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="text-blue-500 text-3xl mb-2"><i class="fa-solid fa-bolt"></i></div>
                    <h4 class="text-4xl font-bold text-gray-900">&lt; 2dtk</h4>
                    <p class="text-gray-500 font-medium">Proses Cepat</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="text-purple-500 text-3xl mb-2"><i class="fa-solid fa-clock"></i></div>
                    <h4 class="text-4xl font-bold text-gray-900">24 Jam</h4>
                    <p class="text-gray-500 font-medium">Siap Membantu</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="text-orange-500 text-3xl mb-2"><i class="fa-solid fa-rupiah-sign"></i></div>
                    <h4 class="text-4xl font-bold text-gray-900">0 Rp</h4>
                    <p class="text-gray-500 font-medium">Tanpa Biaya</p>
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

    <!-- Cara Kerja Section -->
    <section id="cara-kerja" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Cara Kerja Pohaci AI</h2>
                <p class="text-gray-500 max-w-2xl mx-auto">Proses mudah dan cepat untuk mendapatkan diagnosa akurat tentang kesehatan tanaman padi Anda.</p>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                <!-- Step 1 -->
                <div class="relative flex flex-col items-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-lg mb-4">
                        1
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg text-center mb-2">Upload Foto Daun</h3>
                    <p class="text-gray-600 text-sm text-center">Ambil atau unggah foto daun padi yang terlihat sakit dari perangkat Anda.</p>
                    <div class="hidden md:block absolute top-8 left-[calc(100%)] w-full h-1 bg-gradient-to-r from-green-500 to-emerald-600" style="width: calc(200% + 24px); margin-left: 12px;"></div>
                </div>

                <!-- Step 2 -->
                <div class="relative flex flex-col items-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-lg mb-4">
                        2
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg text-center mb-2">AI Analisis</h3>
                    <p class="text-gray-600 text-sm text-center">Model Vision AI kami menganalisis gambar dalam hitungan detik untuk identifikasi akurat.</p>
                    <div class="hidden md:block absolute top-8 left-[calc(100%)] w-full h-1 bg-gradient-to-r from-emerald-500 to-teal-600" style="width: calc(200% + 24px); margin-left: 12px;"></div>
                </div>

                <!-- Step 3 -->
                <div class="relative flex flex-col items-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-lg mb-4">
                        3
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg text-center mb-2">Terima Hasil</h3>
                    <p class="text-gray-600 text-sm text-center">Dapatkan nama penyakit, tingkat akurasi, dan solusi penanganan yang detail.</p>
                    <div class="hidden md:block absolute top-8 left-[calc(100%)] w-full h-1 bg-gradient-to-r from-teal-500 to-cyan-600" style="width: calc(200% + 24px); margin-left: 12px;"></div>
                </div>

                <!-- Step 4 -->
                <div class="relative flex flex-col items-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-lg mb-4">
                        4
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg text-center mb-2">Konsultasi Lanjutan</h3>
                    <p class="text-gray-600 text-sm text-center">Tanya lebih lanjut ke chatbot AI untuk mendapatkan rekomendasi penanganan optimal.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Pertanyaan Umum (FAQ)</h2>
                <p class="text-gray-500">Temukan jawaban atas pertanyaan yang sering diajukan tentang Pohaci AI.</p>
            </div>

            <div class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-100 transition flex justify-between items-center">
                        <span><i class="fa-solid fa-chart-pie text-green-600 mr-3"></i>Berapa akurasi deteksi penyakit Pohaci AI?</span>
                        <i class="fa-solid fa-chevron-down transition-transform duration-300"></i>
                    </button>
                    <div class="hidden px-6 pb-4 text-gray-600">
                        Pohaci AI menggunakan model Vision AI terbaru (Llama 3.2 Vision) yang dapat mencapai akurasi 90%+ dalam mendeteksi penyakit tanaman padi. Namun, akurasi dapat bervariasi tergantung kualitas foto dan kondisi pencahayaan.
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-100 transition flex justify-between items-center">
                        <span><i class="fa-solid fa-coins text-green-600 mr-3"></i>Apakah aplikasi Pohaci AI gratis?</span>
                        <i class="fa-solid fa-chevron-down transition-transform duration-300"></i>
                    </button>
                    <div class="hidden px-6 pb-4 text-gray-600">
                        Ya! Pohaci AI sepenuhnya gratis untuk digunakan. Tidak ada biaya tersembunyi, langganan, atau iklan. Kami mengembangkan aplikasi ini sebagai dedikasi untuk memajukan teknologi pertanian digital di Indonesia.
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-100 transition flex justify-between items-center">
                        <span><i class="fa-solid fa-comments text-green-600 mr-3"></i>Bagaimana cara menggunakan chatbot AI?</span>
                        <i class="fa-solid fa-chevron-down transition-transform duration-300"></i>
                    </button>
                    <div class="hidden px-6 pb-4 text-gray-600">
                        Chatbot AI kami tersedia di panel kanan aplikasi. Anda bisa mengetik pertanyaan seputar budidaya padi, pupuk, hama, dan penyakit. Anda juga bisa menambahkan gambar atau URL artikel untuk analisa yang lebih mendalam. Tekan Enter untuk mengirim pesan atau Shift+Enter untuk baris baru.
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-100 transition flex justify-between items-center">
                        <span><i class="fa-solid fa-leaf text-green-600 mr-3"></i>Apa saja penyakit padi yang bisa dideteksi?</span>
                        <i class="fa-solid fa-chevron-down transition-transform duration-300"></i>
                    </button>
                    <div class="hidden px-6 pb-4 text-gray-600">
                        Pohaci AI dapat mendeteksi berbagai penyakit tanaman padi seperti Blas (Blast), Kresek, Hawar Daun Bakteri, Busuk Batang, Tungro, dan penyakit lainnya. Selain itu, aplikasi juga bisa mendeteksi serangan hama seperti Wereng Coklat dan Penggerek Batang.
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-100 transition flex justify-between items-center">
                        <span><i class="fa-solid fa-lock text-green-600 mr-3"></i>Apakah foto saya aman dan privasi terjamin?</span>
                        <i class="fa-solid fa-chevron-down transition-transform duration-300"></i>
                    </button>
                    <div class="hidden px-6 pb-4 text-gray-600">
                        Ya, data Anda aman. Foto yang Anda unggah diproses melalui server secure dan hanya digunakan untuk analisis. Kami tidak menyimpan foto Anda lebih lama dari diperlukan untuk proses diagnosa. Privasi pengguna adalah prioritas kami.
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-100 transition flex justify-between items-center">
                        <span><i class="fa-solid fa-question-circle text-green-600 mr-3"></i>Bagaimana jika hasil diagnosa kurang akurat?</span>
                        <i class="fa-solid fa-chevron-down transition-transform duration-300"></i>
                    </button>
                    <div class="hidden px-6 pb-4 text-gray-600">
                        Jika Anda meragukan hasil diagnosa, kami sarankan untuk: 1) Coba upload foto dengan sudut berbeda, pencahayaan lebih terang, atau fokus pada bagian daun yang terserang. 2) Konsultasikan dengan ahli pertanian lokal Anda. 3) Gunakan chatbot untuk mendiskusikan gejala lebih detail dengan AI pakar kami.
                    </div>
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
                <a href="https://github.com/bensu89/sistem-pakar-padi-ai" target="_blank" class="text-gray-400 hover:text-white transition"><i
                        class="fa-brands fa-github text-xl"></i></a>
                <a href="https://instagram.com" target="_blank" class="text-gray-400 hover:text-white transition"><i
                        class="fa-brands fa-instagram text-xl"></i></a>
            </div>
        </div>
    </footer>

    <script>
        // FAQ Accordion Toggle
        function toggleFaq(button) {
            const container = button.parentElement;
            const content = container.querySelector('div:last-child');
            const icon = button.querySelector('i:last-child');

            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }

        // Mobile Menu Toggle
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });

            // Close menu when a link is clicked
            document.querySelectorAll('#mobile-menu a').forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenu.classList.add('hidden');
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('#mobile-menu-toggle') &&
                    !event.target.closest('#mobile-menu')) {
                    mobileMenu.classList.add('hidden');
                }
            });
        }
    </script>

    <style>>
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