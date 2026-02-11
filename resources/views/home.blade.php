<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pakar Padi AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.3s ease-out forwards;
        }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #bbb; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #888; }
    </style>
</head>
<body class="bg-gray-100 h-screen w-screen overflow-hidden flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[90vh] flex flex-col md:flex-row overflow-hidden relative">
        
        <div class="w-full md:w-4/12 border-r border-gray-200 bg-white flex flex-col h-full relative z-20 shadow-md">
            
            <div class="p-5 border-b flex justify-between items-center bg-gray-50">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-leaf text-green-600 text-xl"></i>
                    <h2 class="font-bold text-gray-700">Diagnosa Padi</h2>
                </div>
                <button type="button" onclick="resetApp()" class="text-gray-400 hover:text-red-500 transition text-sm flex items-center gap-1 border border-gray-300 px-2 py-1 rounded bg-white hover:border-red-400" title="Mulai Ulang">
                    <i class="fa-solid fa-arrows-rotate"></i> Reset
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-4">
                
                <form id="uploadForm" class="space-y-3">
                    <div class="border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-green-50 transition cursor-pointer relative h-48 flex flex-col justify-center items-center group overflow-hidden" id="dropZone">
                        <input type="file" id="imageInput" name="image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" required>
                        
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

                    <button type="submit" id="btnDiagnosa" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition shadow-md flex justify-center items-center gap-2 active:scale-95">
                        <i class="fa-solid fa-microscope"></i> Analisa Foto
                    </button>
                </form>

                <div id="resultSection" class="hidden bg-green-50 rounded-xl border border-green-200 p-4 animate-fade-in-up">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Terdeteksi:</p>
                            <h3 id="resDisease" class="text-lg font-bold text-gray-800 capitalize leading-tight">Nama Penyakit</h3>
                        </div>
                        <span id="resConfidenceText" class="text-xs bg-green-600 text-white px-2 py-1 rounded font-bold shadow-sm">0%</span>
                    </div>
                    
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                        <div id="resConfidenceBar" class="bg-green-500 h-1.5 rounded-full transition-all duration-1000" style="width: 0%"></div>
                    </div>
                </div>

                <div class="text-xs text-gray-400 text-center mt-4">
                    <p>Gunakan fitur chat di samping untuk konsultasi umum tanpa upload foto.</p>
                </div>
            </div>
        </div>

        <div class="w-full md:w-8/12 bg-white flex flex-col h-full relative z-10">
            
            <div class="p-4 border-b flex items-center justify-between bg-white z-10 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-100 w-10 h-10 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-user-doctor text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm">Dokter Padi AI</h3>
                        <p class="text-xs text-green-600 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Online
                        </p>
                    </div>
                </div>
                <div class="text-xs text-gray-500 border px-3 py-1 rounded-full bg-gray-50 max-w-[200px] truncate flex items-center gap-1">
                    Topik: <span id="chatContextDisease" class="font-bold text-gray-700">Konsultasi Umum</span>
                </div>
            </div>

            <div id="chatHistory" class="flex-1 p-5 overflow-y-auto space-y-4 bg-gray-50/30 scroll-smooth">
                <div class="flex gap-3 animate-fade-in-up">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-1">
                        <i class="fa-solid fa-robot text-green-600 text-xs"></i>
                    </div>
                    <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none shadow-sm text-sm text-gray-700 max-w-[90%] border border-gray-100 leading-relaxed">
                        Halo! Saya asisten pakar padi Anda. 🌱<br><br>
                        Silakan <b>Upload Foto Daun</b> di panel kiri untuk diagnosa penyakit, atau langsung <b>Ketik Pertanyaan</b> di bawah untuk konsultasi umum (misal: pupuk, cuaca, hama).
                    </div>
                </div>
            </div>

            <div id="chatLoading" class="hidden absolute bottom-20 left-1/2 transform -translate-x-1/2 bg-white px-4 py-2 rounded-full shadow-lg border flex items-center gap-2 z-20 animate-bounce">
                <span class="text-xs text-gray-500 font-medium">Sedang mengetik...</span>
            </div>

            <div class="p-4 border-t bg-white">
                <form id="chatForm" class="flex gap-2 relative">
                    <input type="text" id="chatInput" class="w-full bg-gray-100 border-transparent focus:bg-white focus:border-blue-500 border rounded-full px-5 py-3 text-sm transition outline-none shadow-inner" placeholder="Tanyakan sesuatu tentang pertanian padi..." required>
                    <button type="submit" id="btnSendChat" class="absolute right-2 top-1.5 bg-blue-600 hover:bg-blue-700 text-white w-9 h-9 rounded-full flex items-center justify-center transition shadow-md">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // --- KONFIGURASI JALUR ---
        const URL_UPLOAD = "{{ route('analyze') }}"; 
        const URL_CHAT = "http://127.0.0.1:5000/chat"; 
        
        // Default Context: "Konsultasi Umum" jika belum ada foto
        let currentDisease = "Konsultasi Umum"; 

        function formatText(text) {
            if (!text) return "";
            let formatted = text;
            formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<b class="font-bold text-gray-900">$1</b>');
            formatted = formatted.replace(/^\* /gm, '• ');
            formatted = formatted.replace(/\n/g, '<br>');
            return formatted;
        }

        // --- FUNGSI RESET (KEMBALI KE MODE UMUM) ---
        function resetApp() {
            // 1. Reset Upload Form
            document.getElementById('uploadForm').reset();
            document.getElementById('imagePreview').src = "";
            document.getElementById('previewContainer').classList.add('hidden');
            document.getElementById('uploadPrompt').classList.remove('hidden');
            document.getElementById('resultSection').classList.add('hidden');
            
            // 2. Kembalikan Chat ke Mode Umum
            currentDisease = "Konsultasi Umum";
            document.getElementById('chatContextDisease').innerText = currentDisease;
            
            // 3. Beri Info di Chat (Opsional)
            addBotMessage("🔄 <i>Mode diagnosa di-reset. Kembali ke konsultasi umum.</i>");
        }

        // Preview Gambar
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('previewContainer').classList.remove('hidden');
                    document.getElementById('uploadPrompt').classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        // --- 1. UPLOAD & DIAGNOSA ---
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fileInput = document.getElementById('imageInput');
            
            if(!fileInput.files[0]) {
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
                // Kirim ke Laravel
                const response = await axios.post(URL_UPLOAD, formData);
                const data = response.data;

                // Tampilkan Hasil di Kiri
                document.getElementById('resultSection').classList.remove('hidden');
                document.getElementById('resDisease').innerText = data.disease_name;
                document.getElementById('resConfidenceBar').style.width = data.confidence + "%";
                document.getElementById('resConfidenceText').innerText = data.confidence + "%";

                // UBAH KONTEKS CHAT JADI PENYAKIT SPESIFIK
                currentDisease = data.disease_name;
                document.getElementById('chatContextDisease').innerText = currentDisease;
                
                // Masukkan Analisa Awal ke Chat
                const analisaRapi = formatText(data.solution);
                addBotMessage(`💡 <b>Hasil Diagnosa (${data.confidence}%):</b><br><br>${analisaRapi}`);

            } catch (error) {
                console.error(error);
                alert("Gagal koneksi. Pastikan server berjalan!");
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });

        // --- 2. CHATBOT (BISA DIPAKAI KAPAN SAJA) ---
        document.getElementById('chatForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const input = document.getElementById('chatInput');
            const question = input.value.trim();
            if(!question) return;

            addUserMessage(question);
            input.value = "";
            document.getElementById('chatLoading').classList.remove('hidden');

            try {
                const response = await axios.post(URL_CHAT, {
                    question: question,
                    disease_context: currentDisease // Bisa 'Konsultasi Umum' atau Nama Penyakit
                });
                
                const jawabanRapi = formatText(response.data.answer);
                addBotMessage(jawabanRapi);

            } catch (error) {
                console.error(error);
                addBotMessage("⚠️ Maaf, koneksi terputus. Pastikan `python api.py` jalan.");
            } finally {
                document.getElementById('chatLoading').classList.add('hidden');
            }
        });

        function addUserMessage(text) {
            const history = document.getElementById('chatHistory');
            const html = `
                <div class="flex gap-3 justify-end animate-fade-in-up">
                    <div class="bg-blue-600 text-white px-4 py-2 rounded-2xl rounded-tr-none shadow-sm text-sm max-w-[85%]">
                        ${text}
                    </div>
                </div>`;
            history.insertAdjacentHTML('beforeend', html);
            history.scrollTop = history.scrollHeight;
        }

        function addBotMessage(htmlContent) {
            const history = document.getElementById('chatHistory');
            const html = `
                <div class="flex gap-3 animate-fade-in-up">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-1">
                        <i class="fa-solid fa-robot text-green-600 text-xs"></i>
                    </div>
                    <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none shadow-sm text-sm text-gray-700 max-w-[90%] border border-gray-100 leading-relaxed">
                        ${htmlContent}
                    </div>
                </div>`;
            history.insertAdjacentHTML('beforeend', html);
            history.scrollTop = history.scrollHeight;
        }
    </script>
</body>
</html>