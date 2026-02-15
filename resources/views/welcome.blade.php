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

        .hero-bg {
            background-image: url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=2832&auto=format&fit=crop');
            /* Rice Field Image */
            background-size: cover;
            background-position: center;
        }

        .text-shadow {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased overflow-x-hidden">

    <!-- Navbar -->
    <nav id="navbar" class="fixed w-full z-50 transition-all duration-300 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 rounded-2xl px-6 transition-all duration-300"
                id="nav-container">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-leaf text-white text-lg"></i>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-white" id="nav-logo-text">Pohaci<span
                            class="text-green-400">AI</span></span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#fitur"
                        class="text-white/90 hover:text-white font-medium transition hover:underline decoration-green-400 underline-offset-4 nav-link">Fitur</a>
                    <a href="#cara-kerja"
                        class="text-white/90 hover:text-white font-medium transition hover:underline decoration-green-400 underline-offset-4 nav-link">Cara
                        Kerja</a>
                </div>

                <!-- CTA Button -->
                <div class="hidden md:flex items-center gap-4">
                    @auth
                        <a href="{{ route('admin.index') }}"
                            class="text-white/90 hover:text-white font-medium text-sm nav-link">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-white/90 hover:text-white font-medium text-sm nav-link">Masuk</a>
                    @endauth
                    <a href="{{ route('home') }}"
                        class="bg-white text-green-700 px-6 py-2.5 rounded-full font-bold transition shadow-lg hover:bg-green-50 hover:scale-105 transform">
                        Mulai Sekarang <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex item-center">
                    <a href="{{ route('home') }}" class="bg-white text-green-700 p-2 rounded-lg shadow-lg">
                        <i class="fa-solid fa-play"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative h-screen flex items-center justify-center hero-bg">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/60 to-black/30"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center pt-20">

            <div
                class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-4 py-1.5 mb-8 shadow-sm animate-fade-in-up">
                <span class="flex h-3 w-3 relative">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-green-300 text-sm font-bold tracking-wide uppercase">AI Powered Agriculture</span>
            </div>

            <h1 class="text-5xl md:text-7xl font-extrabold text-white tracking-tight mb-6 leading-tight text-shadow">
                Pohaci AI<br>
                <span class="text-green-400">Ngariksa Pare<br>
                    <span class="text-green-400"></span>Ngajaga Lemah Cai</span>
            </h1>

            <p class="mt-6 max-w-2xl mx-auto text-xl text-gray-200 mb-10 leading-relaxed font-light text-shadow">
                Bantu tingkatkan hasil panen dengan diagnosa penyakit tanaman padi yang akurat menggunakan teknologi
                Artificial Intelligence terbaru.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('home') }}"
                    class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white transition-all duration-200 bg-green-600 font-pj rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-600 hover:bg-green-700 shadow-xl hover:shadow-green-900/50 hover:-translate-y-1">
                    <span class="mr-3 text-xl"><i class="fa-solid fa-camera"></i></span>
                    Cek Kondisi Padi
                </a>

                <a href="#fitur"
                    class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white transition-all duration-200 bg-transparent border-2 border-white/30 backdrop-blur-sm font-pj rounded-full hover:bg-white hover:text-green-800 hover:border-white shadow-sm">
                    Pelajari Lebih Lanjut
                </a>
            </div>
        </div>

        <!-- Wave Divider -->
        <div class="absolute bottom-0 w-full overflow-hidden leading-none z-20">
            <svg class="relative block w-[calc(100%+1.3px)] h-[80px] text-white" data-name="Layer 1"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path
                    d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"
                    fill="currentColor"></path>
            </svg>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="relative -mt-20 z-30 pb-20">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div
                    class="bg-white p-6 rounded-2xl shadow-xl text-center transform hover:-translate-y-1 transition duration-300">
                    <div class="text-green-500 text-3xl mb-2"><i class="fa-solid fa-check-circle"></i></div>
                    <h4 class="text-3xl font-bold text-gray-800">98%</h4>
                    <p class="text-gray-500 text-sm font-medium">Akurasi</p>
                </div>
                <div
                    class="bg-white p-6 rounded-2xl shadow-xl text-center transform hover:-translate-y-1 transition duration-300">
                    <div class="text-blue-500 text-3xl mb-2"><i class="fa-solid fa-bolt"></i></div>
                    <h4 class="text-3xl font-bold text-gray-800">&lt;2dtk</h4>
                    <p class="text-gray-500 text-sm font-medium">Kecepatan</p>
                </div>
                <div
                    class="bg-white p-6 rounded-2xl shadow-xl text-center transform hover:-translate-y-1 transition duration-300">
                    <div class="text-purple-500 text-3xl mb-2"><i class="fa-solid fa-robot"></i></div>
                    <h4 class="text-3xl font-bold text-gray-800">24/7</h4>
                    <p class="text-gray-500 text-sm font-medium">Asisten AI</p>
                </div>
                <div
                    class="bg-white p-6 rounded-2xl shadow-xl text-center transform hover:-translate-y-1 transition duration-300">
                    <div class="text-orange-500 text-3xl mb-2"><i class="fa-solid fa-users"></i></div>
                    <h4 class="text-3xl font-bold text-gray-800">Gratis</h4>
                    <p class="text-gray-500 text-sm font-medium">Untuk Petani</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-green-600 font-bold tracking-wider uppercase text-sm">Fitur Unggulan</span>
                <h2 class="text-4xl font-extrabold text-gray-900 mt-2">Teknologi Untuk Petani</h2>
            </div>

            <div class="grid md:grid-cols-3 gap-10">
                <!-- Feature 1 -->
                <div
                    class="bg-gray-50 rounded-3xl p-8 hover:bg-green-50 transition border border-gray-100 hover:border-green-200 group">
                    <div
                        class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mb-6 shadow-sm group-hover:scale-110 transition duration-300 text-green-600 text-3xl">
                        <i class="fa-solid fa-mobile-screen"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Mudah Digunakan</h3>
                    <p class="text-gray-600 leading-relaxed">Antarmuka yang sederhana dan ramah pengguna. Cukup ambil
                        foto langsung dari HP Anda, tidak perlu mendaftar ribet.</p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="bg-gray-50 rounded-3xl p-8 hover:bg-blue-50 transition border border-gray-100 hover:border-blue-200 group">
                    <div
                        class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mb-6 shadow-sm group-hover:scale-110 transition duration-300 text-blue-600 text-3xl">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Diagnosa Cerdas</h3>
                    <p class="text-gray-600 leading-relaxed">Menggunakan model AI terlatih yang mampu mengenali berbagai
                        penyakit padi seperti Blas, Hawar Daun, dan Tungro.</p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="bg-gray-50 rounded-3xl p-8 hover:bg-purple-50 transition border border-gray-100 hover:border-purple-200 group">
                    <div
                        class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mb-6 shadow-sm group-hover:scale-110 transition duration-300 text-purple-600 text-3xl">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Konsultasi Pakar</h3>
                    <p class="text-gray-600 leading-relaxed">Diskusikan hasil diagnosa dengan asisten AI kami. Tanyakan
                        solusi, dosis pupuk, atau cara penanganan yang tepat.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-leaf text-white text-sm"></i>
                        </div>
                        <span class="font-bold text-2xl">Pohaci AI</span>
                    </div>
                    <p class="text-gray-400 leading-relaxed max-w-md">
                        Pohaci AI adalah aplikasi sistem pakar berbasis web untuk deteksi penyakit tanaman padi dan
                        konsultasi pertanian.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-6 text-white">Navigasi</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li><a href="#" class="hover:text-green-400 transition">Beranda</a></li>
                        <li><a href="#fitur" class="hover:text-green-400 transition">Fitur</a></li>
                        <li><a href="#cara-kerja" class="hover:text-green-400 transition">Cara Kerja</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-6 text-white">Hubungi Kami</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li><i class="fa-solid fa-envelope mr-2 text-green-500"></i> halo@pohaci.id</li>
                        <li><i class="fa-brands fa-whatsapp mr-2 text-green-500"></i> +62 812 3456</li>
                    </ul>
                </div>
            </div>
            <div
                class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center text-gray-500 text-sm">
                <p>&copy; 2026 Pohaci AI. KKN Desa Cikurubuk.</p>
                <div class="flex gap-4 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white transition">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        const navContainer = document.getElementById('nav-container');
        const navLogoText = document.getElementById('nav-logo-text');
        const navLinks = document.querySelectorAll('.nav-link');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                // Scrolled style
                navContainer.classList.add('bg-white/90', 'backdrop-blur-md', 'shadow-lg', 'mt-4');
                navLogoText.classList.replace('text-white', 'text-gray-900');

                navLinks.forEach(link => {
                    link.classList.remove('text-white/90', 'hover:text-white');
                    link.classList.add('text-gray-600', 'hover:text-green-600');
                });
            } else {
                // Top style
                navContainer.classList.remove('bg-white/90', 'backdrop-blur-md', 'shadow-lg', 'mt-4');
                navLogoText.classList.replace('text-gray-900', 'text-white');

                navLinks.forEach(link => {
                    link.classList.add('text-white/90', 'hover:text-white');
                    link.classList.remove('text-gray-600', 'hover:text-green-600');
                });
            }
        });
    </script>
</body>

</html>