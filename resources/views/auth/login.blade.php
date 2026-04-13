<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — Sistem Pakar Padi AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
    </style>
</head>

<body
    class="min-h-screen bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50 flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Background Decorations -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute -top-20 -right-20 w-96 h-96 bg-green-200/30 rounded-full blur-3xl float-animation"></div>
        <div class="absolute -bottom-32 -left-32 w-[500px] h-[500px] bg-emerald-200/20 rounded-full blur-3xl float-animation"
            style="animation-delay: -3s;"></div>
        <div class="absolute top-1/3 right-1/4 w-64 h-64 bg-teal-100/20 rounded-full blur-2xl float-animation"
            style="animation-delay: -1.5s;"></div>
    </div>

    <div class="w-full max-w-md relative z-10 fade-in">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg shadow-green-500/30 mb-4">
                <i class="fa-solid fa-seedling text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Pohaci AI </h1>
            <p class="text-gray-500 text-sm mt-1">Masuk ke panel monitoring penelitian</p>
        </div>

        <!-- Login Card -->
        <div class="glass rounded-2xl shadow-xl shadow-gray-200/50 border border-white/60 p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        <i class="fa-solid fa-envelope text-green-500 mr-1"></i> Email
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                        autofocus
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/70 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition outline-none text-sm @error('email') border-red-400 @enderror"
                        placeholder="nama@email.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        <i class="fa-solid fa-lock text-green-500 mr-1"></i> Password
                    </label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/70 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition outline-none text-sm pr-12 @error('password') border-red-400 @enderror"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-600 transition">
                            <i id="eyeIcon" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer">
                        <span class="text-sm text-gray-600">Ingat Saya</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-sm text-green-600 hover:text-green-700 font-medium hover:underline transition">
                            Lupa Password?
                        </a>
                    @endif
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-3 rounded-xl transition-all duration-300 shadow-lg shadow-green-500/30 hover:shadow-green-500/50 active:scale-[0.98] flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk
                </button>
            </form>

            <div class="mt-6 flex items-center justify-center space-x-2">
                <span class="h-px w-1/3 bg-gray-200"></span>
                <span class="text-xs text-gray-400 font-medium uppercase">Atau</span>
                <span class="h-px w-1/3 bg-gray-200"></span>
            </div>

            <!-- Login with Google Button -->
            <a href="{{ route('google.login') }}" class="mt-4 w-full bg-white hover:bg-gray-50 border border-gray-200 text-gray-600 font-medium text-sm py-3 px-4 rounded-xl flex items-center justify-center gap-3 transition-colors shadow-sm cursor-pointer">
                <svg width="20" height="20" viewBox="0 0 48 48" aria-hidden="true" class="w-5 h-5"><path fill="#4285F4" d="M45.12 24.5c0-1.56-.14-3.06-.4-4.5H24v8.51h11.84c-.51 2.75-2.06 5.08-4.39 6.64v5.52h7.11c4.16-3.83 6.56-9.47 6.56-16.17z"></path><path fill="#34A853" d="M24 46c5.94 0 10.92-1.97 14.56-5.33l-7.11-5.52c-1.97 1.32-4.49 2.1-7.45 2.1-5.73 0-10.58-3.87-12.31-9.07H4.34v5.7C7.96 41.07 15.4 46 24 46z"></path><path fill="#FBBC05" d="M11.69 28.18A12.944 12.944 0 0 1 11 24c0-1.47.25-2.9.72-4.18v-5.7H4.34A23.978 23.978 0 0 0 0 24c0 3.86 1.19 7.42 3.16 10.37l8.53-6.19z"></path><path fill="#EA4335" d="M24 10.75c3.23 0 6.13 1.11 8.41 3.29l6.31-6.31C34.91 4.18 29.93 2 24 2 15.4 2 7.96 6.93 4.34 14.12l7.38 5.7c1.73-5.2 6.58-9.07 12.28-9.07z"></path></svg>
                Masuk dengan Google
            </a>
        </div>

        <!-- Register Link -->
        <div class="text-center mt-6">
            <p class="text-gray-500 text-sm">
                Belum punya akun?
                <a href="{{ route('register') }}"
                    class="text-green-600 font-semibold hover:text-green-700 hover:underline transition">
                    Daftar Sekarang
                </a>
            </p>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-3">
            <a href="{{ route('home') }}"
                class="text-gray-400 text-xs hover:text-green-600 transition inline-flex items-center gap-1">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke halaman utama
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>

</html>
