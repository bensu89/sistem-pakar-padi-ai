<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Email — Sistem Pakar Padi AI</title>
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

    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute -top-20 -right-20 w-96 h-96 bg-green-200/30 rounded-full blur-3xl float-animation"></div>
        <div class="absolute -bottom-32 -left-32 w-[500px] h-[500px] bg-emerald-200/20 rounded-full blur-3xl float-animation"
            style="animation-delay: -3s;"></div>
    </div>

    <div class="w-full max-w-md relative z-10 fade-in">
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl shadow-lg shadow-blue-500/30 mb-4">
                <i class="fa-solid fa-envelope-circle-check text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Verifikasi Email</h1>
            <p class="text-gray-500 text-sm mt-1">Cek email Anda untuk link verifikasi</p>
        </div>

        <div class="glass rounded-2xl shadow-xl shadow-gray-200/50 border border-white/60 p-8">
            @if (session('resent'))
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-5 flex items-start gap-3">
                    <i class="fa-solid fa-circle-check text-green-500 mt-0.5"></i>
                    <p class="text-sm text-green-700">Link verifikasi baru telah dikirim ke email Anda.</p>
                </div>
            @endif

            <div class="text-center space-y-4">
                <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                    <p class="text-sm text-gray-700 leading-relaxed">
                        Sebelum melanjutkan, silakan cek email Anda untuk link verifikasi.
                        Jika tidak menerima email, klik tombol di bawah.
                    </p>
                </div>

                <form method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-3 rounded-xl transition-all duration-300 shadow-lg shadow-green-500/30 hover:shadow-green-500/50 active:scale-[0.98] flex items-center justify-center gap-2">
                        <i class="fa-solid fa-rotate-right"></i> Kirim Ulang Email
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('home') }}"
                class="text-gray-400 text-xs hover:text-green-600 transition inline-flex items-center gap-1">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke halaman utama
            </a>
        </div>
    </div>
</body>

</html>