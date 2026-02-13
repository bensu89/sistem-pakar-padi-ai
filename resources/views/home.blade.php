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
    </style>
</head>

<body class="bg-gray-100 min-h-screen md:h-screen w-screen md:overflow-hidden flex items-center justify-center p-4">

    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-auto md:h-[90vh] flex flex-col md:flex-row overflow-hidden relative">

        <!-- ========== PANEL KIRI: DIAGNOSA FOTO ========== -->
        <div
            class="w-full md:w-4/12 border-r border-gray-200 bg-white flex flex-col h-auto md:h-full relative z-20 shadow-md">

            <div class="p-5 border-b flex justify-between items-center bg-gray-50">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-leaf text-green-600 text-xl"></i>
                    <h2 class="font-bold text-gray-700">Diagnosa Daun Padi</h2>
                </div>
                <button type="button" onclick="resetApp()"
                    class="text-gray-400 hover:text-red-500 transition text-sm flex items-center gap-1 border border-gray-300 px-2 py-1 rounded bg-white hover:border-red-400"
                    title="Mulai Ulang">
                    <i class="fa-solid fa-arrows-rotate"></i> Reset
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-4">
                <form id="uploadForm" class="space-y-3">
                    <div class="border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-green-50 transition cursor-pointer relative h-48 flex flex-col justify-center items-center group overflow-hidden"
                        id="dropZone">
                        <input type="file" id="imageInput" name="image"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*"
                            required>
                        <div id="previewContainer" class="hidden w-full h-full absolute inset-0 bg-white">
                            <img id="imagePreview" src="" class="w-full h-full object-contain">
                        </div>
                        <div id="uploadPrompt" class="group-hover:scale-110 transition duration-300 text-center p-4">
                            <div class="bg-white p-3 rounded-full shadow-sm inline-block mb-2">
                                <i class="fa-solid fa-camera text-2xl text-green-500"></i>
                            </div>
                            <p class="text-gray-500 font-medium text-sm">Klik atau Tarik Foto</p>
                            <p class="text-gray-400 text-xs mt-1">Format: JPG, PNG</p>
                        </div>
                    </div>
                    <button type="submit" id="btnDiagnosa"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition shadow-md flex justify-center items-center gap-2 active:scale-95">
                        <i class="fa-solid fa-microscope"></i> Analisa Foto
                    </button>
                </form>

                <div id="resultSection"
                    class="hidden bg-green-50 rounded-xl border border-green-200 p-4 animate-fade-in-up">
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

                <div class="text-xs text-gray-400 text-center mt-4">
                    <p>Aplikasi ini Dikembangkan Tim KKN Desa Cikurubuk</p>
                </div>
            </div>
        </div>

        <!-- ========== PANEL KANAN: CHATBOT ========== -->
        <div class="w-full md:w-8/12 bg-white flex flex-col h-[80vh] md:h-full relative z-10">

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
                    <!-- Reset Chat -->
                    <button type="button" onclick="resetChat()"
                        class="text-gray-400 hover:text-red-500 transition text-xs flex items-center gap-1 border border-gray-300 px-2 py-1.5 rounded-full bg-white hover:border-red-400 hover:bg-red-50 tooltip"
                        data-tip="Reset Chat">
                        <i class="fa-solid fa-broom"></i>
                    </button>
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
                        📸 <b>Upload Foto</b> di panel kiri untuk diagnosa penyakit<br>
                        💬 <b>Ketik Pertanyaan</b> di bawah untuk konsultasi<br>
                        📎 <b>Attach File</b> untuk analisa gambar langsung di chat<br>
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
                    <!-- Attach File Button -->
                    <input type="file" id="chatFileInput" class="hidden" accept="image/*">
                    <button type="button" onclick="document.getElementById('chatFileInput').click()"
                        class="w-9 h-9 rounded-full border border-gray-300 bg-gray-50 hover:bg-blue-50 hover:border-blue-400 text-gray-500 hover:text-blue-600 flex items-center justify-center transition tooltip flex-shrink-0"
                        data-tip="Attach Gambar">
                        <i class="fa-solid fa-paperclip text-sm"></i>
                    </button>
                    <!-- Add URL Button -->
                    <button type="button" onclick="toggleUrlInput()"
                        class="w-9 h-9 rounded-full border border-gray-300 bg-gray-50 hover:bg-purple-50 hover:border-purple-400 text-gray-500 hover:text-purple-600 flex items-center justify-center transition tooltip flex-shrink-0"
                        data-tip="Tambah URL">
                        <i class="fa-solid fa-link text-sm"></i>
                    </button>
                    <!-- Text Input -->
                    <div class="flex-1 relative">
                        <input type="text" id="chatInput"
                            class="w-full bg-gray-100 border-transparent focus:bg-white focus:border-blue-500 border rounded-full px-5 py-3 text-sm transition outline-none shadow-inner pr-12"
                            placeholder="Tanyakan sesuatu tentang pertanian padi...">
                        <button type="submit" id="btnSendChat"
                            class="absolute right-2 top-1.5 bg-blue-600 hover:bg-blue-700 text-white w-9 h-9 rounded-full flex items-center justify-center transition shadow-md">
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                        </button>
                    </div>
                </form>
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
        function resetApp() {
            document.getElementById('uploadForm').reset();
            document.getElementById('imagePreview').src = "";
            document.getElementById('previewContainer').classList.add('hidden');
            document.getElementById('uploadPrompt').classList.remove('hidden');
            document.getElementById('resultSection').classList.add('hidden');
            currentDisease = "Konsultasi Umum";
            document.getElementById('chatContextDisease').innerText = currentDisease;
            addBotMessage("🔄 <i>Mode diagnosa di-reset. Kembali ke konsultasi umum.</i>");
        }

        function resetChat() {
            const history = document.getElementById('chatHistory');
            history.innerHTML = `
                <div class="flex gap-3 animate-fade-in-up">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-1">
                        <i class="fa-solid fa-robot text-green-600 text-xs"></i>
                    </div>
                    <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none shadow-sm text-sm text-gray-700 max-w-[90%] border border-gray-100 leading-relaxed">
                        Halo! Saya <b>Pohaci AI</b>, asisten pakar padi Anda. 🌱<br><br>
                        📸 <b>Upload Foto</b> di panel kiri untuk diagnosa penyakit<br>
                        💬 <b>Ketik Pertanyaan</b> di bawah untuk konsultasi<br>
                        📎 <b>Attach File</b> untuk analisa gambar langsung di chat<br>
                        🔗 <b>Paste URL</b> untuk analisa konten halaman web
                    </div>
                </div>`;
            removeAttachedFile();
            removeAttachedUrl();
        }

        // ============================================================
        // PREVIEW IMAGE (Left Panel)
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
        // 1. UPLOAD & DIAGNOSA (Panel Kiri → Groq Vision)
        // ============================================================
        document.getElementById('uploadForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const fileInput = document.getElementById('imageInput');

            if (!fileInput.files[0]) {
                alert("Pilih foto dulu!");
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
                const data = response.data;

                document.getElementById('resultSection').classList.remove('hidden');
                document.getElementById('resDisease').innerText = data.disease_name;
                document.getElementById('resConfidenceBar').style.width = data.confidence + "%";
                document.getElementById('resConfidenceText').innerText = data.confidence + "%";

                currentDisease = data.disease_name;
                document.getElementById('chatContextDisease').innerText = currentDisease;

                const analisaRapi = formatText(data.solution);
                addBotMessage(`💡 <b>Hasil Diagnosa (${data.confidence}%):</b><br><br>${analisaRapi}`);

            } catch (error) {
                console.error(error);
                console.error(error);
                let msg = "Gagal koneksi ke AI. Coba lagi.";
                if (error.response && error.response.data) {
                    msg = error.response.data.error || error.response.data.message || msg;
                }
                alert(msg);
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

            // Minimal harus ada salah satu: text, file, atau URL
            if (!question && !attachedFile && !attachedUrl) return;

            // Tampilkan pesan user
            let userDisplay = '';
            if (attachedFile) {
                userDisplay += `<div class="mb-1"><span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded"><i class="fa-solid fa-image"></i> ${escapeHtml(attachedFile.name)}</span></div>`;
            }
            if (attachedUrl) {
                userDisplay += `<div class="mb-1"><span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded"><i class="fa-solid fa-link"></i> ${escapeHtml(attachedUrl)}</span></div>`;
            }
            if (question) {
                userDisplay += escapeHtml(question);
            }
            addUserMessage(userDisplay);

            input.value = "";
            document.getElementById('chatLoading').classList.remove('hidden');

            // Build FormData
            const formData = new FormData();
            if (question) formData.append('message', question);
            formData.append('disease_context', currentDisease);

            if (attachedFile) {
                formData.append('file', attachedFile);
            }
            if (attachedUrl) {
                formData.append('url', attachedUrl);
            }

            try {
                const response = await axios.post(URL_CHAT, formData);
                const jawabanRapi = formatText(response.data.answer);
                const modelUsed = response.data.model_used || '';
                const typeIcon = response.data.type === 'vision' ? '👁️' : response.data.type === 'url' ? '🔗' : '💬';

                addBotMessage(`${jawabanRapi}<div class="mt-2 text-[10px] text-gray-400">${typeIcon} ${modelUsed}</div>`);

            } catch (error) {
                console.error(error);
                let msg = error.message || "Koneksi AI gagal.";

                if (error.response) {
                    // Jika response ada (bukan network error)
                    if (typeof error.response.data === 'string') {
                        // Response HTML (biasanya 500 error page atau 504 timeout)
                        msg = `Server Error (${error.response.status}): Cek Logs Vercel.`;
                    } else if (error.response.data) {
                        // Response JSON
                        msg = error.response.data.error || error.response.data.message || msg;
                    }
                }

                addBotMessage("⚠️ " + escapeHtml(msg));
            } finally {
                document.getElementById('chatLoading').classList.add('hidden');
                // Clear attachments after send
                removeAttachedFile();
                removeAttachedUrl();
            }
        });

        // ============================================================
        // CHAT BUBBLE HELPERS
        // ============================================================
        function addUserMessage(html) {
            const history = document.getElementById('chatHistory');
            const bubble = `
                <div class="flex gap-3 justify-end animate-fade-in-up">
                    <div class="bg-blue-600 text-white px-4 py-2 rounded-2xl rounded-tr-none shadow-sm text-sm max-w-[85%]">
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
                    <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none shadow-sm text-sm text-gray-700 max-w-[90%] border border-gray-100 leading-relaxed">
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
    </script>
</body>

</html>
