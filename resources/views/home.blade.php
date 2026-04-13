<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pohaci AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.3s ease-out forwards;
        }

        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #bbb;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #888;
        }

        .tooltip {
            position: relative;
        }

        .tooltip::after {
            content: attr(data-tip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2937;
            color: #fff;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        .tooltip:hover::after {
            opacity: 1;
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }
    </style>
</head>

<body
    class="bg-gray-100 min-h-screen md:h-screen w-screen md:overflow-hidden flex items-center justify-center p-4 relative">

    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-auto md:h-[90vh] flex flex-col overflow-hidden relative">

        <!-- ========== PANEL CHATBOT ========== -->
        <div class="w-full bg-white flex flex-col h-[85vh] md:h-full relative z-10">

            <!-- Header Chat -->
            <div class="p-4 border-b flex items-center justify-between bg-white z-10 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-100 w-10 h-10 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-user-doctor text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm">Pohaci AI: Ngariksa Pare, Ngajaga Lemah Cai</h3>
                        <p class="text-xs text-green-600 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Online
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Topic Badge -->
                    <div
                        class="text-xs text-gray-500 border px-3 py-1 rounded-full bg-gray-50 max-w-[150px] truncate flex items-center gap-1 hidden md:flex">
                        Topik: <span id="chatContextDisease" class="font-bold text-gray-700">Umum</span>
                    </div>
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.index') }}"
                                class="bg-white text-gray-700 hover:text-green-600 px-3 py-1.5 rounded-lg shadow-sm font-bold text-xs flex items-center gap-2 transition hover:shadow-md border border-gray-200">
                                <i class="fa-solid fa-gauge-high"></i> Dashboard Admin
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}"
                            class="bg-white/80 backdrop-blur text-gray-500 hover:text-blue-600 px-3 py-1.5 rounded-lg text-xs font-medium flex items-center gap-1 transition hover:bg-white hover:shadow-sm border border-transparent hover:border-blue-100">
                            <i class="fa-solid fa-lock"></i> Admin Login
                        </a>
                    @endauth
                    <button type="button" onclick="resetApp()"
                        class="text-gray-400 hover:text-red-500 transition text-xs flex items-center gap-1 border border-gray-300 px-2 py-1.5 rounded-full bg-white hover:border-red-400 hover:bg-red-50 tooltip"
                        data-tip="Reset Semua">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                </div>
            </div>

            <!-- ========== DIAGNOSA FOTO DI DALAM CHAT ========== -->
            <div class="p-4 border-b bg-gradient-to-br from-green-50 via-white to-emerald-50">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-green-600 font-semibold">Diagnosa Foto</p>
                        <h2 class="text-lg font-bold text-gray-800 mt-1">Ambil / Upload Foto langsung dari chat</h2>
                        <p class="text-sm text-gray-500">Pilih dari kamera atau galeri, lalu cek penyakitnya di sini.</p>
                    </div>
                </div>

                <form id="uploadForm" class="space-y-3">
                    <label for="imageInput"
                        class="border-2 border-dashed border-green-300 rounded-2xl bg-white/90 hover:bg-green-50 transition cursor-pointer relative min-h-[12rem] flex flex-col justify-center items-center group overflow-hidden shadow-inner">
                        <input type="file" id="imageInput" name="image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            accept="image/*" capture="environment" required>
                        <div id="previewContainer" class="hidden w-full h-full absolute inset-0 bg-white">
                            <img id="imagePreview" src="" class="w-full h-full object-contain p-2">
                        </div>
                        <div id="uploadPrompt" class="group-hover:scale-105 transition duration-300 text-center p-6">
                            <div class="bg-white p-5 rounded-full shadow-md inline-block mb-4">
                                <i class="fa-solid fa-camera text-4xl text-green-600"></i>
                            </div>
                            <p class="text-gray-700 font-bold text-lg">Ambil / Upload Foto</p>
                            <p class="text-gray-500 text-sm mt-1">Pastikan bagian daun terlihat jelas</p>
                        </div>
                    </label>
                    <button type="submit" id="btnDiagnosa"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold px-5 py-4 rounded-2xl transition shadow-lg hover:shadow-green-500/30 flex justify-center items-center gap-3 active:scale-95 text-lg">
                        <i class="fa-solid fa-magnifying-glass-chart"></i>
                        <span>Mulai Cek Penyakit</span>
                    </button>
                </form>

                <div id="resultSection"
                    class="hidden mt-3 bg-green-50 rounded-xl border border-green-200 p-4 animate-fade-in-up">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Terdeteksi:</p>
                            <h3 id="resDisease" class="text-lg font-bold text-gray-800 capitalize leading-tight">Nama
                                Penyakit</h3>
                        </div>
                        <span id="resConfidenceText"
                            class="text-xs bg-green-600 text-white px-2 py-1 rounded font-bold shadow-sm">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                        <div id="resConfidenceBar" class="bg-green-500 h-1.5 rounded-full transition-all duration-1000"
                            style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- Chat History -->
            <div id="chatHistory" class="flex-1 p-5 overflow-y-auto space-y-4 bg-gray-50/30 scroll-smooth">
                <div class="flex gap-3 animate-fade-in-up">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-1">
                        <i class="fa-solid fa-robot text-green-600 text-xs"></i>
                    </div>
                    <div
                        class="bg-white px-4 py-3 rounded-2xl rounded-tl-none shadow-sm text-sm text-gray-700 max-w-[90%] border border-gray-100 leading-relaxed">
                        Halo! Saya <b>Pohaci AI</b>, asisten pakar padi Anda. 🌱<br><br>
                        📸 <b>Upload Foto</b> di kartu atas untuk diagnosa penyakit<br>
                        💬 <b>Ketik Pertanyaan</b> di bawah untuk konsultasi<br>
                        🔗 <b>Paste URL</b> untuk analisa konten halaman web
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="chatLoading"
                class="hidden absolute bottom-28 left-1/2 transform -translate-x-1/2 bg-white px-4 py-2 rounded-full shadow-lg border flex items-center gap-2 z-20">
                <div class="flex gap-1">
                    <span class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay:0s"></span>
                    <span class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay:0.15s"></span>
                    <span class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay:0.3s"></span>
                </div>
                <span class="text-xs text-gray-500 font-medium">AI sedang berpikir...</span>
            </div>

            <!-- Attachment Preview (File/URL) -->
            <div id="attachmentPreview" class="hidden border-t bg-gray-50 px-4 py-2 flex items-center gap-3">
                <!-- File Preview -->
                <div id="filePreviewBadge"
                    class="hidden flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-lg px-3 py-1.5">
                    <img id="chatFileThumb" src="" class="w-8 h-8 rounded object-cover">
                    <span id="chatFileName" class="text-xs text-blue-700 font-medium max-w-[120px] truncate"></span>
                    <button type="button" onclick="removeAttachedFile()"
                        class="text-blue-400 hover:text-red-500 transition">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
                <!-- URL Preview -->
                <div id="urlPreviewBadge"
                    class="hidden flex items-center gap-2 bg-purple-50 border border-purple-200 rounded-lg px-3 py-1.5">
                    <i class="fa-solid fa-link text-purple-500 text-xs"></i>
                    <span id="chatUrlText" class="text-xs text-purple-700 font-medium max-w-[200px] truncate"></span>
                    <button type="button" onclick="removeAttachedUrl()"
                        class="text-purple-400 hover:text-red-500 transition">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- Quick Actions Chips -->
            <div id="quickActions" class="px-4 pb-2 flex gap-2 overflow-x-auto no-scrollbar mask-gradient">
                <button onclick="fillChat('Cara mengatasi hama wereng coklat?')"
                    class="flex-shrink-0 bg-white border border-green-200 text-green-700 px-3 py-1.5 rounded-full text-xs font-medium hover:bg-green-50 transition shadow-sm whitespace-nowrap">
                    🦠 Hama Wereng
                </button>
                <button onclick="fillChat('Rekomendasi pupuk untuk padi usia 30 hari?')"
                    class="flex-shrink-0 bg-white border border-blue-200 text-blue-700 px-3 py-1.5 rounded-full text-xs font-medium hover:bg-blue-50 transition shadow-sm whitespace-nowrap">
                    💊 Rekomendasi Pupuk
                </button>
                <button onclick="fillChat('Penyakit apa yang membuat daun padi menguning?')"
                    class="flex-shrink-0 bg-white border border-yellow-200 text-yellow-700 px-3 py-1.5 rounded-full text-xs font-medium hover:bg-yellow-50 transition shadow-sm whitespace-nowrap">
                    🍂 Daun Menguning
                </button>
                <button onclick="fillChat('Cara mencegah penyakit blas pada padi?')"
                    class="flex-shrink-0 bg-white border border-red-200 text-red-700 px-3 py-1.5 rounded-full text-xs font-medium hover:bg-red-50 transition shadow-sm whitespace-nowrap">
                    🍄 Pencegahan Blas
                </button>
            </div>

            <!-- URL Input Bar (hidden by default) -->
            <div id="urlInputBar" class="hidden border-t bg-purple-50 px-4 py-2 flex items-center gap-2">
                <i class="fa-solid fa-link text-purple-500 text-sm"></i>
                <input type="url" id="urlInput"
                    class="flex-1 bg-white border border-purple-200 rounded-lg px-3 py-1.5 text-sm outline-none focus:border-purple-500"
                    placeholder="Paste URL halaman web...">
                <button type="button" onclick="confirmUrl()"
                    class="bg-purple-500 hover:bg-purple-600 text-white text-xs px-3 py-1.5 rounded-lg transition">
                    <i class="fa-solid fa-check"></i>
                </button>
                <button type="button" onclick="cancelUrl()"
                    class="text-gray-400 hover:text-red-500 text-xs px-2 py-1.5 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Chat Input -->
            <div class="p-4 border-t bg-white">
                <form id="chatForm" class="flex gap-2 items-center">
                    <input type="file" id="chatFileInput" class="hidden" accept="image/*">
                    <!-- Add URL Button -->
                    <button type="button" onclick="toggleUrlInput()"
                        class="w-9 h-9 rounded-full border border-gray-300 bg-gray-50 hover:bg-purple-50 hover:border-purple-400 text-gray-500 hover:text-purple-600 flex items-center justify-center transition tooltip flex-shrink-0"
                        data-tip="Tambah URL">
                        <i class="fa-solid fa-link text-sm"></i>
                    </button>
                    <!-- Text Input -->
                    <div class="flex-1 relative">
                        <textarea id="chatInput" rows="1"
                            class="w-full bg-gray-100 border-2 border-transparent focus:bg-white focus:border-green-500 rounded-2xl px-5 py-4 text-base transition outline-none shadow-inner pr-14 resize-none overflow-hidden placeholder-gray-500"
                            placeholder="Tulis pertanyaanmu di sini..."></textarea>
                        <button type="submit" id="btnSendChat"
                            class="absolute right-2 bottom-2 bg-green-600 hover:bg-green-700 text-white w-10 h-10 rounded-full flex items-center justify-center transition shadow-md p-2">
                            <i class="fa-solid fa-paper-plane text-sm"></i>
                        </button>
                    </div>
                </form>
                <div class="text-center text-xs text-gray-400 mt-2">
                    Pohaci AI is AI and can make mistakes.
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- KONFIGURASI ---
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const URL_UPLOAD = "{{ route('analyze') }}";
        const URL_CHAT = "{{ route('chat.send') }}";

        let currentDisease = "Konsultasi Umum";
        let attachedFile = null;
        let attachedUrl = null;

        // --- ESCAPE HTML (XSS prevention) ---
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }

        function formatText(text) {
            if (!text) return "";
            let formatted = text;
            formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<b class="font-bold text-gray-900">$1</b>');
            formatted = formatted.replace(/^\* /gm, '• ');
            formatted = formatted.replace(/\n/g, '<br>');
            return formatted;
        }

        function renderDiagnosisResult(data) {
            document.getElementById('resultSection').classList.remove('hidden');
            document.getElementById('resDisease').innerText = data.disease_name;
            document.getElementById('resConfidenceBar').style.width = data.confidence + "%";
            document.getElementById('resConfidenceText').innerText = data.confidence + "%";

            currentDisease = data.disease_name;
            document.getElementById('chatContextDisease').innerText = currentDisease;

            const analisaRapi = formatText(data.solution);
            addBotMessage(`💡 <b>Hasil Diagnosa (${data.confidence}%):</b><br><br>${analisaRapi}`);
        }

        // ============================================================
        // TEXTAREA AUTO-RESIZE & SUBMIT HANDLER
        // ============================================================
        const chatInput = document.getElementById('chatInput');

        // Auto-resize textarea
        chatInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            if (this.value === '') this.style.height = 'auto'; // Reset if empty
        });

        // Handle Enter vs Shift+Enter
        chatInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault(); // Prevent default new line
                if (this.value.trim() !== "" || attachedFile || attachedUrl) {
                    document.getElementById('chatForm').requestSubmit();
                }
            }
        });

        // ============================================================
        // ATTACHMENT: FILE
        // ============================================================
        document.getElementById('chatFileInput').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            attachedFile = file;

            // Show preview badge
            const reader = new FileReader();
            reader.onload = function (ev) {
                document.getElementById('chatFileThumb').src = ev.target.result;
            };
            reader.readAsDataURL(file);
            document.getElementById('chatFileName').textContent = file.name;
            document.getElementById('filePreviewBadge').classList.remove('hidden');
            document.getElementById('attachmentPreview').classList.remove('hidden');
        });

        function removeAttachedFile() {
            attachedFile = null;
            document.getElementById('chatFileInput').value = '';
            document.getElementById('filePreviewBadge').classList.add('hidden');
            if (!attachedUrl) {
                document.getElementById('attachmentPreview').classList.add('hidden');
            }
        }

        // ============================================================
        // ATTACHMENT: URL
        // ============================================================
        function toggleUrlInput() {
            const bar = document.getElementById('urlInputBar');
            bar.classList.toggle('hidden');
            if (!bar.classList.contains('hidden')) {
                document.getElementById('urlInput').focus();
            }
        }

        function confirmUrl() {
            const urlValue = document.getElementById('urlInput').value.trim();
            if (!urlValue) return;

            attachedUrl = urlValue;
            document.getElementById('chatUrlText').textContent = urlValue;
            document.getElementById('urlPreviewBadge').classList.remove('hidden');
            document.getElementById('attachmentPreview').classList.remove('hidden');
            document.getElementById('urlInputBar').classList.add('hidden');
            document.getElementById('urlInput').value = '';
        }

        function cancelUrl() {
            document.getElementById('urlInputBar').classList.add('hidden');
            document.getElementById('urlInput').value = '';
        }

        function removeAttachedUrl() {
            attachedUrl = null;
            document.getElementById('urlPreviewBadge').classList.add('hidden');
            if (!attachedFile) {
                document.getElementById('attachmentPreview').classList.add('hidden');
            }
        }

        // ============================================================
        // RESET FUNCTIONS
        // ============================================================
        async function resetApp() {
            const confirmed = await showConfirmDialog(
                'Reset Semua?',
                'Semua diagnosa, attachment, dan riwayat chat akan dihapus. Lanjutkan?',
                'Reset',
                'Batal'
            );
            if (confirmed) {
                document.getElementById('uploadForm').reset();
                document.getElementById('imagePreview').src = "";
                document.getElementById('previewContainer').classList.add('hidden');
                document.getElementById('uploadPrompt').classList.remove('hidden');
                document.getElementById('resultSection').classList.add('hidden');
                currentDisease = "Konsultasi Umum";
                document.getElementById('chatContextDisease').innerText = currentDisease;
                const history = document.getElementById('chatHistory');
                history.innerHTML = `
                    <div class="flex gap-3 animate-fade-in-up">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-1">
                            <i class="fa-solid fa-robot text-green-600 text-xs"></i>
                        </div>
                        <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none shadow-sm text-sm text-gray-700 max-w-[90%] border border-gray-100 leading-relaxed">
                            Halo! Saya <b>Pohaci AI</b>, asisten pakar padi Anda. 🌱<br><br>
                            📸 <b>Upload Foto</b> di kartu atas untuk diagnosa penyakit<br>
                            💬 <b>Ketik Pertanyaan</b> di bawah untuk konsultasi<br>
                            🔗 <b>Paste URL</b> untuk analisa konten halaman web
                        </div>
                    </div>`;
                removeAttachedFile();
                removeAttachedUrl();
                addBotMessage("🔄 <i>Semua data sudah di-reset. Silakan mulai ulang.</i>");
                showToast('success', 'Semua data direset');
            }
        }

        // ============================================================
        // PREVIEW IMAGE (Chat Photo Card)
        // ============================================================
        document.getElementById('imageInput').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('previewContainer').classList.remove('hidden');
                    document.getElementById('uploadPrompt').classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        // ============================================================
        // 1. UPLOAD & DIAGNOSA (Chat Photo Card → Groq Vision)
        // ============================================================
        document.getElementById('uploadForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const fileInput = document.getElementById('imageInput');

            if (!fileInput.files[0]) {
                showToast('warning', 'Pilih foto dulu!');
                return;
            }

            const btn = document.getElementById('btnDiagnosa');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            try {
                const response = await axios.post(URL_UPLOAD, formData);
                renderDiagnosisResult(response.data);

            } catch (error) {
                console.error(error);
                let msg = "Gagal koneksi ke AI. Coba lagi.";
                if (error.response && error.response.data) {
                    msg = error.response.data.error || error.response.data.message || msg;
                }
                showToast('error', msg);
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });

        // ============================================================
        // 2. CHATBOT (Text / File / URL → Laravel → Groq)
        // ============================================================
        document.getElementById('chatForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const input = document.getElementById('chatInput');
            const question = input.value.trim();
            const hasDiagnosisImage = !!attachedFile;

            // Minimal harus ada salah satu: text, file, atau URL
            if (!question && !attachedFile && !attachedUrl) return;

            // Tampilkan pesan user
            let userDisplay = '';
            if (attachedFile) {
                userDisplay += `<div class="mb-1"><span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded"><i class="fa-solid fa-image"></i> ${escapeHtml(attachedFile.name)}</span></div>`;
            }
            if (attachedUrl && !hasDiagnosisImage) {
                userDisplay += `<div class="mb-1"><span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded"><i class="fa-solid fa-link"></i> ${escapeHtml(attachedUrl)}</span></div>`;
            }
            if (question && !hasDiagnosisImage) {
                // Replace newlines with <br> for display
                userDisplay += escapeHtml(question).replace(/\n/g, '<br>');
            } else if (hasDiagnosisImage) {
                userDisplay += '<div class="text-xs opacity-80">Foto dikirim untuk diagnosa</div>';
                if (question) {
                    userDisplay += `<div class="text-xs opacity-80 mt-1">Catatan: ${escapeHtml(question)}</div>`;
                }
            }
            addUserMessage(userDisplay);

            input.value = "";
            input.style.height = 'auto'; // Reset height
            document.getElementById('chatLoading').classList.remove('hidden');
            addSkeletonMessage();

            // Build FormData
            const formData = new FormData();

            try {
                if (hasDiagnosisImage) {
                    formData.append('file', attachedFile);
                    const response = await axios.post(URL_UPLOAD, formData);
                    renderDiagnosisResult(response.data);
                } else {
                    if (question) formData.append('message', question);
                    formData.append('disease_context', currentDisease);

                    if (attachedFile) {
                        formData.append('file', attachedFile);
                    }
                    if (attachedUrl) {
                        formData.append('url', attachedUrl);
                    }

                    const response = await axios.post(URL_CHAT, formData);
                    const jawabanRapi = formatText(response.data.answer);
                    const modelUsed = response.data.model_used || '';
                    const typeIcon = response.data.type === 'vision' ? '👁️' : response.data.type === 'url' ? '🔗' : '💬';

                    addBotMessage(`${jawabanRapi}<div class="mt-2 text-[10px] text-gray-400">${typeIcon} ${modelUsed}</div>`);
                }

            } catch (error) {
                console.error(error);
                let msg = error.message || "Koneksi AI gagal.";

                if (error.response) {
                    if (typeof error.response.data === 'string') {
                        msg = `Server Error (${error.response.status}): Cek Logs Vercel.`;
                    } else if (error.response.data) {
                        msg = error.response.data.error || error.response.data.message || msg;
                    }
                }

                addBotMessage("⚠️ " + escapeHtml(msg));
            } finally {
                document.getElementById('chatLoading').classList.add('hidden');
                document.getElementById('skeleton-msg')?.remove();
                if (hasDiagnosisImage) {
                    removeAttachedFile();
                    removeAttachedUrl();
                } else {
                    removeAttachedFile();
                }
                // removeAttachedUrl(); // Jangan hapus URL agar konteks tetap terjaga untuk chat berikutnya
            }
        });

        // ============================================================
        // CHAT BUBBLE HELPERS
        // ============================================================
        function addUserMessage(html) {
            const history = document.getElementById('chatHistory');
            const bubble = `
                <div class="flex gap-3 justify-end animate-fade-in-up">
                    <div class="bg-blue-600 text-white px-4 py-2 rounded-2xl rounded-tr-none shadow-sm text-sm max-w-[85%] text-left">
                        ${html}
                    </div>
                </div>`;
            history.insertAdjacentHTML('beforeend', bubble);
            history.scrollTop = history.scrollHeight;
        }

        function addBotMessage(htmlContent) {
            const history = document.getElementById('chatHistory');
            const bubble = `
                <div class="flex gap-3 animate-fade-in-up">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-1">
                        <i class="fa-solid fa-robot text-green-600 text-xs"></i>
                    </div>
                    <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none shadow-sm text-sm text-gray-700 max-w-[90%] border border-gray-100 leading-relaxed text-left">
                        ${htmlContent}
                    </div>
                </div>`;
            history.insertAdjacentHTML('beforeend', bubble);
            history.scrollTop = history.scrollHeight;
        }

        // Allow Enter key in URL input to confirm
        document.getElementById('urlInput').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmUrl();
            }
        });

        // Quick Action Chip Handler
        function fillChat(text) {
            const input = document.getElementById('chatInput');
            input.value = text;
            input.focus();
            // Optional: Auto submit? Maybe better to let user confirm.
            // document.getElementById('chatForm').requestSubmit();
        }

        // ============================================================
        // CONFIRM DIALOG SYSTEM
        // ============================================================
        function showConfirmDialog(title, message, confirmText = 'Ya', cancelText = 'Batal') {
            return new Promise((resolve) => {
                const id = 'dialog-' + Date.now();

                const dialogHTML = `
                    <div id="${id}-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40"></div>
                    <div id="${id}" class="fixed inset-0 flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-bold text-gray-900">${escapeHtml(title)}</h3>
                            </div>
                            <div class="p-6">
                                <p class="text-gray-600">${escapeHtml(message)}</p>
                            </div>
                            <div class="p-6 border-t border-gray-200 flex gap-3 justify-end">
                                <button onclick="document.getElementById('${id}').remove(); document.getElementById('${id}-overlay').remove();" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition">
                                    ${escapeHtml(cancelText)}
                                </button>
                                <button onclick="document.getElementById('${id}').remove(); document.getElementById('${id}-overlay').remove(); window.confirmDialogResolve('${id}', true);" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition">
                                    ${escapeHtml(confirmText)}
                                </button>
                            </div>
                        </div>
                    </div>`;

                window.confirmDialogResolve = (dialogId, result) => resolve(result);

                document.body.insertAdjacentHTML('beforeend', dialogHTML);
            });
        }

        // ============================================================
        // SKELETON LOADER FOR CHAT
        // ============================================================
        function addSkeletonMessage() {
            const history = document.getElementById('chatHistory');
            const skeletonHTML = `
                <div id="skeleton-msg" class="flex gap-3 animate-fade-in-up">
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex-shrink-0 mt-1 animate-pulse"></div>
                    <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none shadow-sm text-sm text-gray-700 max-w-[90%] border border-gray-100 leading-relaxed space-y-2">
                        <div class="h-4 bg-gray-200 rounded animate-pulse"></div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-5/6"></div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-4/5"></div>
                    </div>
                </div>`;
            history.insertAdjacentHTML('beforeend', skeletonHTML);
            history.scrollTop = history.scrollHeight;
        }

        // ============================================================
        // TOAST NOTIFICATION SYSTEM
        // ============================================================
        function showToast(type, message, duration = 4000) {
            const id = 'toast-' + Date.now();

            const bgColor = {
                'success': 'bg-green-500',
                'error': 'bg-red-500',
                'info': 'bg-blue-500',
                'warning': 'bg-yellow-500'
            }[type] || 'bg-gray-500';

            const icon = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'info': 'fa-info-circle',
                'warning': 'fa-triangle-exclamation'
            }[type] || 'fa-bell';

            const toastHTML = `
                <div id="${id}" class="fixed bottom-4 right-4 ${bgColor} text-white px-5 py-3 rounded-lg shadow-lg flex items-center gap-3 animate-fade-in-up z-50 max-w-sm">
                    <i class="fa-solid ${icon}"></i>
                    <span>${escapeHtml(message)}</span>
                    <button onclick="document.getElementById('${id}').remove()" class="ml-2 text-white/70 hover:text-white transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>`;

            document.body.insertAdjacentHTML('beforeend', toastHTML);

            // Auto-dismiss
            setTimeout(() => {
                const el = document.getElementById(id);
                if (el) el.remove();
            }, duration);
        }
    </script>
</body>

</html>
