<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pakar Padi AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gradient-to-br from-green-50 to-green-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col md:flex-row">
        
        <div class="w-full md:w-1/2 p-8 border-r border-gray-100">
            <div class="text-center mb-6">
                <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-leaf text-2xl text-green-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Diagnosa Penyakit</h2>
                <p class="text-gray-500 text-sm">Upload foto daun padi untuk dianalisa AI</p>
            </div>

            <form id="uploadForm" class="space-y-4">
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition cursor-pointer relative" id="dropZone">
                    <input type="file" id="imageInput" name="image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" required>
                    <div id="previewContainer" class="hidden">
                        <img id="imagePreview" src="" class="max-h-48 mx-auto rounded shadow-sm">
                        <p class="text-xs text-gray-400 mt-2">Klik untuk ganti foto</p>
                    </div>
                    <div id="uploadPrompt">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500 font-medium">Klik atau Tarik Foto ke Sini</p>
                    </div>
                </div>
                <button type="submit" id="btnDiagnosa" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition shadow-lg flex justify-center items-center gap-2">
                    <i class="fa-solid fa-microscope"></i> Analisa Sekarang
                </button>
            </form>

            <div id="resultSection" class="hidden mt-6 p-4 bg-green-50 rounded-xl border border-green-200 animate-fade-in-up">
                <h3 class="font-bold text-green-800 text-lg flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check"></i> Hasil Diagnosa
                </h3>
                <div class="mt-3 space-y-2">
                    <p class="text-sm text-gray-600">Penyakit Terdeteksi:</p>
                    <p id="resDisease" class="text-xl font-bold text-gray-800">Bacterial Leaf Blight</p>
                    
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="resConfidenceBar" class="bg-green-600 h-2.5 rounded-full" style="width: 0%"></div>
                    </div>
                    <p class="text-xs text-right text-gray-500" id="resConfidenceText">Akurasi: 0%</p>
                </div>
            </div>
        </div>

        <div id="chatSection" class="w-full md:w-1/2 bg-gray-50 flex flex-col hidden relative">
            <div class="p-4 bg-white border-b shadow-sm flex items-center gap-3">
                <div class="bg-blue-100 p-2 rounded-full">
                    <i class="fa-solid fa-robot text-blue-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Dokter Padi AI</h3>
                    <p class="text-xs text-green-600 flex items-center gap-1">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Online
                    </p>
                </div>
            </div>

            <div id="chatHistory" class="flex-1 p-4 overflow-y-auto space-y-4 h-80 md:h-auto scroll-smooth">
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-robot text-blue-600 text-xs"></i>
                    </div>
                    <div class="bg-white p-3 rounded-r-xl rounded-bl-xl shadow-sm text-sm text-gray-700 max-w-[85%] border">
                        Halo! Saya asisten AI. Berdasarkan hasil scan, tanaman Anda terindikasi <b id="chatContextDisease">...</b>. Ada yang ingin ditanyakan tentang pengobatannya?
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white border-t">
                <form id="chatForm" class="flex gap-2">
                    <input type="text" id="chatInput" class="flex-1 border rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Tanya obat, dosis, dll..." required disabled>
                    <button type="submit" id="btnSendChat" class="bg-blue-600 text-white w-10 h-10 rounded-full hover:bg-blue-700 transition flex items-center justify-center disabled:opacity-50" disabled>
                        <i class="fa-solid fa-paper-plane text-sm"></i>
                    </button>
                </form>
            </div>
            
            <div id="chatLoading" class="hidden absolute inset-0 bg-white/50 backdrop-blur-sm flex items-center justify-center z-10">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
        </div>

    </div>

    <script>
        const API_URL = "http://127.0.0.1:5000"; // URL Python Flask
        let currentDisease = ""; // Menyimpan konteks penyakit saat ini

        // 1. Logic Preview Gambar
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

        // 2. Logic Diagnosa (Upload ke Python)
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fileInput = document.getElementById('imageInput');
            
            if(!fileInput.files[0]) {
                alert("Pilih foto dulu!");
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            // Tampilkan Loading & Disable Tombol
            const btn = document.getElementById('btnDiagnosa');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menganalisa...';
            btn.disabled = true;

            try {
                // Kirim ke API /predict
                const response = await axios.post(`${API_URL}/predict`, formData);
                const data = response.data;

                // Tampilkan Hasil
                document.getElementById('resultSection').classList.remove('hidden');
                document.getElementById('resDisease').innerText = data.disease_name;
                document.getElementById('resConfidenceBar').style.width = data.confidence + "%";
                document.getElementById('resConfidenceText').innerText = "Akurasi AI: " + data.confidence + "%";

                // Simpan Konteks Penyakit & Buka Chat
                currentDisease = data.disease_name;
                document.getElementById('chatContextDisease').innerText = currentDisease;
                
                // Buka Bagian Chat
                document.getElementById('chatSection').classList.remove('hidden');
                document.getElementById('chatSection').classList.add('flex'); // Supaya layout flex jalan
                document.getElementById('chatInput').disabled = false;
                document.getElementById('btnSendChat').disabled = false;
                
                // Tambahkan Solusi Awal ke Chat History
                addBotMessage("💡 <b>Analisa Awal:</b><br>" + data.solution.replace(/\n/g, "<br>"));

            } catch (error) {
                console.error(error);
                alert("Gagal menganalisa gambar. Pastikan server Python (api.py) jalan!");
            } finally {
                btn.innerHTML = '<i class="fa-solid fa-microscope"></i> Analisa Sekarang';
                btn.disabled = false;
            }
        });

        // 3. Logic Chatting (Kirim ke Python)
        document.getElementById('chatForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const input = document.getElementById('chatInput');
            const question = input.value.trim();
            if(!question) return;

            // Tampilkan Pesan User
            addUserMessage(question);
            input.value = "";
            
            // Loading Chat
            document.getElementById('chatLoading').classList.remove('hidden');

            try {
                // Kirim ke API /chat
                const response = await axios.post(`${API_URL}/chat`, {
                    question: question,
                    disease_context: currentDisease
                });
                
                // Tampilkan Jawaban Bot
                addBotMessage(response.data.answer.replace(/\n/g, "<br>"));

            } catch (error) {
                addBotMessage("⚠️ Maaf, terjadi kesalahan koneksi.");
            } finally {
                document.getElementById('chatLoading').classList.add('hidden');
            }
        });

        // Helper: Tambah Pesan User ke UI
        function addUserMessage(text) {
            const history = document.getElementById('chatHistory');
            const html = `
                <div class="flex gap-3 justify-end animate-fade-in-up">
                    <div class="bg-green-600 text-white p-3 rounded-l-xl rounded-br-xl shadow-sm text-sm max-w-[85%]">
                        ${text}
                    </div>
                </div>`;
            history.insertAdjacentHTML('beforeend', html);
            history.scrollTop = history.scrollHeight;
        }

        // Helper: Tambah Pesan Bot ke UI
        function addBotMessage(text) {
            const history = document.getElementById('chatHistory');
            const html = `
                <div class="flex gap-3 animate-fade-in-up">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-robot text-blue-600 text-xs"></i>
                    </div>
                    <div class="bg-white p-3 rounded-r-xl rounded-bl-xl shadow-sm text-sm text-gray-700 max-w-[85%] border">
                        ${text}
                    </div>
                </div>`;
            history.insertAdjacentHTML('beforeend', html);
            history.scrollTop = history.scrollHeight;
        }
    </script>
    
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.3s ease-out forwards;
        }
    </style>
</body>
</html>