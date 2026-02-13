<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password — Sistem Pakar Padi AI</title>
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
    </div>

    <div class="w-full max-w-md relative z-10 fade-in">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg shadow-green-500/30 mb-4">
                <i class="fa-solid fa-shield-halved text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Reset Password</h1>
            <p class="text-gray-500 text-sm mt-1">Buat password baru untuk akun Anda</p>
        </div>

        <!-- Card -->
        <div class="glass rounded-2xl shadow-xl shadow-gray-200/50 border border-white/60 p-8">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        <i class="fa-solid fa-envelope text-green-500 mr-1"></i> Email
                    </label>
                    <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required
                        autocomplete="email" autofocus
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
                        <i class="fa-solid fa-lock text-green-500 mr-1"></i> Password Baru
                    </label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/70 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition outline-none text-sm @error('password') border-red-400 @enderror"
                        placeholder="Minimal 8 karakter">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password-confirm" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        <i class="fa-solid fa-shield-halved text-green-500 mr-1"></i> Konfirmasi Password
                    </label>
                    <input id="password-confirm" type="password" name="password_confirmation" required
                        autocomplete="new-password"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/70 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition outline-none text-sm"
                        placeholder="Ketik ulang password baru">
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-3 rounded-xl transition-all duration-300 shadow-lg shadow-green-500/30 hover:shadow-green-500/50 active:scale-[0.98] flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrows-rotate"></i> Reset Password
                </button>
            </form>
        </div>
    </div>
</body>

</html>