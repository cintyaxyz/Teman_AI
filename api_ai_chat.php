<?php
/**
 * api_ai_chat.php — API Endpoint untuk Asisten Teman Warga AI
 * Mendukung Google Gemini API (jika API key diset) & Smart Civic AI Knowledge Engine bawaan
 */
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Hanya proses request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Metode request tidak diizinkan. Gunakan POST.']);
    exit;
}

// Ambil input JSON atau form-urlencoded
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// Fitur Pengujian Koneksi API Key Gemini secara instan
if (!empty($data['testKey'])) {
    $testKey = trim((string)($data['geminiKey'] ?? ''));
    if ($testKey === '') {
        echo json_encode(['success' => false, 'message' => 'API Key tidak boleh kosong.']);
        exit;
    }
    $selectedModel = !empty($data['model']) ? trim((string)$data['model']) : (defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-flash-lite-latest');
    $testRes = callGeminiAPI("Halo, perkenalkan dirimu sebagai Teman Warga AI dalam 1 kalimat ramah.", $testKey, $_SESSION['user_nama'] ?? 'Warga', [], $selectedModel);
    if ($testRes && !empty($testRes['reply'])) {
        $_SESSION['gemini_api_key'] = $testKey;
        $_SESSION['gemini_model'] = $testRes['modelName'] ?? $selectedModel;
        echo json_encode([
            'success' => true,
            'message' => "Google Gemini API (" . ($testRes['modelName'] ?? $selectedModel) . ") berhasil terhubung!",
            'reply' => $testRes['reply']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal terhubung ke Google Gemini. Pastikan API Key valid dan kuota akun Google AI Studio Anda aktif.'
        ]);
    }
    exit;
}

$message = '';
if (is_array($data) && isset($data['message'])) {
    $message = trim((string)$data['message']);
} elseif (isset($_POST['message'])) {
    $message = trim((string)$_POST['message']);
}

if ($message === '') {
    echo json_encode([
        'error' => true,
        'message' => 'Pesan tidak boleh kosong.'
    ]);
    exit;
}

// Catat waktu respon
$timeNow = date('H.i');
$userName = $_SESSION['user_nama'] ?? 'Warga';

// Cek apakah ada Gemini API Key dari request client, session, config, atau env
$geminiKey = !empty($data['geminiKey']) ? trim((string)$data['geminiKey']) : 
             (!empty($_SESSION['gemini_api_key']) ? $_SESSION['gemini_api_key'] : 
             (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== '' ? GEMINI_API_KEY : 
             (getenv('GEMINI_API_KEY') ?: '')));

// Utamakan model dari config.php, atau model dari client
$configModel = defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-flash-lite-latest';
$clientModel = !empty($data['model']) ? trim((string)$data['model']) : '';
$initialModel = !empty($clientModel) ? $clientModel : $configModel;

$history = (isset($data['history']) && is_array($data['history'])) ? $data['history'] : [];

$aiResponse = null;

if (!empty($geminiKey)) {
    // callGeminiAPI memiliki sistem auto-fallback jika model utama terkena rate limit (429)
    $aiResponse = callGeminiAPI($message, $geminiKey, $userName, $history, $initialModel);
}

// Jika Gemini tidak aktif atau gagal, gunakan Smart Civic AI Engine bawaan
if (!$aiResponse) {
    $aiResponse = getCivicAIResponse($message, $userName);
    $aiResponse['isGemini'] = false;
    $aiResponse['model'] = 'Smart Civic Engine';
}

echo json_encode([
    'success' => true,
    'time' => $timeNow,
    'isGemini' => $aiResponse['isGemini'] ?? false,
    'model' => $aiResponse['model'] ?? 'Smart Civic Engine',
    'reply' => $aiResponse['reply'],
    'suggestions' => $aiResponse['suggestions'] ?? [],
    'action' => $aiResponse['action'] ?? null
]);
exit;

// ─── FUNGSI CALL GEMINI API ──────────────────────────────────────────────────
function callGeminiAPI(string $prompt, string $apiKey, string $userName, array $history = [], string $model = 'gemini-flash-lite-latest'): ?array {
    // Daftar model untuk dicoba berurutan jika model pertama terkena kuota / rate limit (429)
    $modelsToTry = array_unique([
        $model,
        'gemini-flash-lite-latest',
        'gemini-3.1-flash-lite',
        'gemini-flash-latest'
    ]);

    $systemContext = "Kamu adalah 'Teman Warga AI', asisten cerdas pelayanan publik & administrasi lingkungan di Indonesia. "
        . "Warga yang sedang bertanya bernama '{$userName}'. "
        . "Pedoman: Jawab ramah, sopan, terstruktur dengan bullet list. Sebutkan berkas yang dibutuhkan (KTP, KK, dll). "
        . "Jam pelayanan kelurahan Senin-Jumat 08.00-15.00 WIB. "
        . "Jika darurat (banjir bahaya, kecelakaan), sebutkan Call Center 112. "
        . "Format pakai markdown (bold untuk hal penting). "
        . "Pertanyaan warga: {$prompt}";

    $contents = [];

    // Sertakan riwayat obrolan terdahulu
    if (!empty($history)) {
        foreach ($history as $h) {
            $role = (isset($h['role']) && $h['role'] === 'user') ? 'user' : 'model';
            $t = trim((string)($h['text'] ?? ''));
            if ($t !== '') {
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $t]]
                ];
            }
        }
        // Tambahkan prompt terkini
        $contents[] = ['role' => 'user', 'parts' => [['text' => $prompt]]];
    } else {
        // Pertama kali: sertakan konteks sistem sebagai bagian pesan
        $contents[] = ['role' => 'user', 'parts' => [['text' => $systemContext]]];
    }

    $payload = [
        'contents' => $contents,
        'generationConfig' => [
            'temperature' => 0.4,
            'maxOutputTokens' => 1000,
        ]
    ];

    foreach ($modelsToTry as $currentModel) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$currentModel}:generateContent?key=" . urlencode($apiKey);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $result   = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $result) {
            $json    = json_decode($result, true);
            $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if ($rawText !== '') {
                return [
                    'reply'       => formatGeminiMarkdownToHtml($rawText),
                    'rawText'     => $rawText,
                    'isGemini'    => true,
                    'model'       => 'Google Gemini (' . $currentModel . ')',
                    'modelName'   => $currentModel,
                    'suggestions' => [
                        'Lapor kendala lingkungan',
                        'Syarat pengurusan dokumen',
                        'Jam operasional kelurahan'
                    ],
                    'action'      => null
                ];
            }
        }
        // Jika 429 (kuota habis) atau 404/503, loop berlanjut ke model berikutnya
    }

    // Log error untuk debugging
    error_log("[Gemini] HTTP={$httpCode} CURL_ERR={$curlErr} RAW=" . substr((string)$result, 0, 300));
    return null;
}

// ─── HELPER FORMATTER MARKDOWN GEMINI KE HTML ELEGAN ─────────────────────────
function formatGeminiMarkdownToHtml(string $md): string {
    $html = htmlspecialchars($md, ENT_QUOTES, 'UTF-8');

    // Headers
    $html = preg_replace('/^###\s+(.+)$/m', '<h4 class="font-bold text-primary text-sm mt-3 mb-1">$1</h4>', $html);
    $html = preg_replace('/^##\s+(.+)$/m', '<h3 class="font-bold text-primary text-base mt-3 mb-1 pb-1 border-b border-surface-container">$1</h3>', $html);
    $html = preg_replace('/^#\s+(.+)$/m', '<h2 class="font-extrabold text-primary text-lg mt-4 mb-2 pb-1 border-b border-surface-container">$1</h2>', $html);

    // Bold (**text**)
    $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong class="font-bold text-on-surface">$1</strong>', $html);

    // Italic (*text* or _text_)
    $html = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em class="italic text-on-surface-variant">$1</em>', $html);

    // Inline code (`code`)
    $html = preg_replace('/`([^`]+)`/', '<code class="px-1.5 py-0.5 rounded bg-surface-container text-primary font-mono text-xs">$1</code>', $html);

    // Bullet lists (- item or * item)
    $html = preg_replace('/^\s*[\-\*]\s+(.+)$/m', '<li class="flex items-start gap-2 py-0.5"><span class="w-1.5 h-1.5 rounded-full bg-primary mt-1.5 shrink-0"></span><span>$1</span></li>', $html);
    $html = preg_replace('/((?:<li class="flex items-start gap-2 py-0.5">.*?<\/li>\s*)+)/s', '<ul class="my-2 space-y-0.5 pl-1">$1</ul>', $html);

    // Numbered lists (1. item)
    $html = preg_replace('/^\s*(\d+)\.\s+(.+)$/m', '<li class="flex items-start gap-2 py-0.5"><span class="w-4 h-4 rounded-full bg-primary/10 text-primary font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">$1</span><span>$2</span></li>', $html);
    $html = preg_replace('/((?:<li class="flex items-start gap-2 py-0.5"><span class="w-4 h-4.*?<\/li>\s*)+)/s', '<ol class="my-2 space-y-0.5 pl-1">$1</ol>', $html);

    // Line breaks
    $html = nl2br($html);

    // Clean extra breaks around lists
    $html = preg_replace('/<br\s*\/?>\s*<\/(ul|ol)>/', '</$1>', $html);
    $html = preg_replace('/<(ul|ol)>\s*<br\s*\/?>/', '<$1>', $html);

    return $html;
}

// ─── SMART CIVIC AI KNOWLEDGE ENGINE BAWAAN ───────────────────────────────────
function getCivicAIResponse(string $query, string $userName): array {
    $q = mb_strtolower($query, 'UTF-8');

    // 1. KTP / E-KTP / Identitas Kependudukan Digital
    if (preg_match('/(ktp|e-ktp|identitas|nik|rekam|ikd)/i', $q)) {
        return [
            'reply' => '
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                        <span class="material-symbols-outlined text-primary text-[20px]">badge</span>
                        <strong class="text-primary font-headline-md">Panduan Pembuatan & Pembaruan KTP Elektronik</strong>
                    </div>
                    <p>Halo <strong>' . htmlspecialchars($userName) . '</strong>, untuk pelayanan e-KTP (baru, hilang, rusak, atau pindah domisili), berikut panduan lengkapnya:</p>
                    
                    <div class="bg-surface-container-low/70 p-3 rounded-xl border border-outline-variant/30 space-y-2">
                        <span class="font-label-sm uppercase tracking-wider text-outline block font-bold">Syarat Berkas yang Diperlukan:</span>
                        <ul class="list-disc list-inside space-y-1 text-body-sm">
                            <li>Fotokopi <strong>Kartu Keluarga (KK)</strong> terbaru</li>
                            <li>KTP lama (jika rusak atau perpanjangan/ganti status)</li>
                            <li><strong>Surat Keterangan Kehilangan dari Polsek</strong> (khusus jika KTP hilang)</li>
                            <li>Surat Pengantar dari RT/RW setempat (opsional tergantung kelurahan)</li>
                        </ul>
                    </div>

                    <div class="p-3 bg-emerald-50 text-emerald-800 rounded-xl text-body-sm border border-emerald-200">
                        <strong>Catatan:</strong> Seluruh proses perekaman biometrik (foto, sidik jari, dan iris mata) dilakukan langsung di <strong>Kantor Kecamatan</strong> atau <strong>Kelurahan</strong> dan <strong>GRATIS (tanpa pungutan biaya)</strong>.
                    </div>
                </div>
            ',
            'suggestions' => [
                'Berapa lama proses cetak KTP?',
                'Bagaimana aktivasi IKD digital?',
                'Cara mengurus Kartu Keluarga (KK)'
            ],
            'action' => [
                'label' => 'Cek Lokasi Kantor Kecamatan di Peta',
                'url' => 'peta_laporan.php'
            ]
        ];
    }

    // 2. Kartu Keluarga (KK)
    if (preg_match('/(kartu keluarga|\bkk\b|tambah anak|pisah kk|pecah kk)/i', $q)) {
        return [
            'reply' => '
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                        <span class="material-symbols-outlined text-primary text-[20px]">group</span>
                        <strong class="text-primary font-headline-md">Prosedur Pengurusan Kartu Keluarga (KK)</strong>
                    </div>
                    <p>Halo <strong>' . htmlspecialchars($userName) . '</strong>, pengurusan KK meliputi penambahan anggota baru, pemecahan KK (keluarga baru), atau penggantian KK hilang/rusak:</p>
                    
                    <div class="bg-surface-container-low/70 p-3 rounded-xl border border-outline-variant/30 space-y-2">
                        <span class="font-label-sm uppercase tracking-wider text-outline block font-bold">Persyaratan Berkas:</span>
                        <ul class="list-disc list-inside space-y-1 text-body-sm">
                            <li>KK Asli lama (bawa juga salinan fotokopi)</li>
                            <li>Surat Pengantar RT/RW setempat</li>
                            <li><strong>Buku Nikah / Akta Perkawinan</strong> (untuk keluarga baru)</li>
                            <li><strong>Surat Keterangan Lahir</strong> (jika menambah kelahiran anak)</li>
                            <li>Surat Keterangan Kematian (jika pengurangan anggota keluarga)</li>
                        </ul>
                    </div>
                    <p class="text-body-sm text-outline">Proses pencetakan sekarang dapat menggunakan kertas putih HVS A4 80 gram bersertifikat Tanda Tangan Elektronik (TTE) dari Disdukcapil.</p>
                </div>
            ',
            'suggestions' => [
                'Syarat Akta Kelahiran',
                'Cara buat Surat Pindah Domisili',
                'Jam Pelayanan Kelurahan'
            ]
        ];
    }

    // 3. Surat Domisili / Surat Keterangan Tinggal
    if (preg_match('/(domisili|surat tinggal|keterangan domisili|skd)/i', $q)) {
        return [
            'reply' => '
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                        <span class="material-symbols-outlined text-primary text-[20px]">verified</span>
                        <strong class="text-primary font-headline-md">Panduan Resmi Surat Keterangan Domisili</strong>
                    </div>
                    <p>Untuk mengurus <strong>Surat Keterangan Domisili</strong> tinggal, Anda bisa mendatangi loket kelurahan dengan membawa dokumen berikut:</p>
                    
                    <div class="space-y-2">
                        <div class="flex items-start gap-2.5 p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/30">
                            <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-[12px] font-bold shrink-0">1</span>
                            <div class="text-body-sm"><strong>Surat Pengantar RT/RW:</strong> Minta surat pengantar dari pengurus RT/RW tempat tinggal Anda saat ini.</div>
                        </div>
                        <div class="flex items-start gap-2.5 p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/30">
                            <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-[12px] font-bold shrink-0">2</span>
                            <div class="text-body-sm"><strong>Fotokopi KTP & KK Pemohon:</strong> 1 lembar fotokopi identitas asli yang masih berlaku.</div>
                        </div>
                        <div class="flex items-start gap-2.5 p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/30">
                            <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-[12px] font-bold shrink-0">3</span>
                            <div class="text-body-sm"><strong>Pas Foto 3x4:</strong> 1 lembar (latar belakang biru/merah) jika diperlukan arsip kelurahan.</div>
                        </div>
                        <div class="flex items-start gap-2.5 p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/30">
                            <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-[12px] font-bold shrink-0">4</span>
                            <div class="text-body-sm"><strong>Mengisi Formulir di Loket:</strong> Petugas akan mencetak dan menandatangani surat domisili resmi dalam 15-30 menit.</div>
                        </div>
                    </div>
                </div>
            ',
            'suggestions' => [
                'Jam buka loket kelurahan?',
                'Syarat membuat KTP baru',
                'Nomor kontak darurat kelurahan'
            ]
        ];
    }

    // 4. Lapor Masalah Lingkungan (Jalan Rusak, Sampah, Banjir, Lampu Mati)
    if (preg_match('/(lapor|jalan rusak|lubang|sampah|banjir|lampu padam|lampu mati|pohon tumbang|got|selokan|pengaduan)/i', $q)) {
        return [
            'reply' => '
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                        <span class="material-symbols-outlined text-primary text-[20px]">campaign</span>
                        <strong class="text-primary font-headline-md">Cara Melaporkan Masalah Lingkungan di Teman Warga</strong>
                    </div>
                    <p>Halo <strong>' . htmlspecialchars($userName) . '</strong>, Anda dapat melaporkan langsung segala gangguan fasilitas umum melalui portal Teman Warga AI:</p>
                    
                    <ol class="list-decimal list-inside space-y-1.5 text-body-sm bg-surface-container-low/70 p-3.5 rounded-xl border border-outline-variant/30">
                        <li>Buka menu <strong>Lapor Masalah</strong> di menu navigasi sebelah kiri.</li>
                        <li>Pilih kategori (misal: <em>Jalan Rusak, Sampah Liar, Lampu Jalan Padam, Genangan Air</em>).</li>
                        <li>Unggah foto bukti langsung dari ponsel atau kamera.</li>
                        <li>Tentukan titik GPS di peta agar petugas dinas mudah menemukan lokasi.</li>
                        <li>Kirim laporan! Anda akan menerima <strong>ID Tiket</strong> dan notifikasi pembaruan secara real-time.</li>
                    </ol>
                </div>
            ',
            'suggestions' => [
                'Berapa lama laporan ditindaklanjuti?',
                'Buka Peta Laporan',
                'Kontak Darurat 112'
            ],
            'action' => [
                'label' => 'Buat Laporan Sekarang',
                'url' => 'buat_laporan_Masyarakat.php'
            ]
        ];
    }

    // 5. Bantuan Sosial (Bansos, PKH, BLT, BPNT, KIS, PBI)
    if (preg_match('/(bansos|bantuan sosial|pkh|blt|bpnt|kis|dtks|sktm|tidak mampu)/i', $q)) {
        return [
            'reply' => '
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                        <span class="material-symbols-outlined text-primary text-[20px]">volunteer_activism</span>
                        <strong class="text-primary font-headline-md">Informasi Bantuan Sosial & DTKS</strong>
                    </div>
                    <p>Halo <strong>' . htmlspecialchars($userName) . '</strong>, bantuan sosial pemerintah (PKH, BPNT, BLT, KIS PBI) disalurkan berdasarkan <strong>Data Terpadu Kesejahteraan Sosial (DTKS)</strong> Kemensos:</p>
                    
                    <div class="bg-surface-container-low/70 p-3 rounded-xl border border-outline-variant/30 space-y-2 text-body-sm">
                        <strong>Langkah Pendaftaran / Usulan Bansos:</strong>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Membawa Fotokopi KTP & KK ke Kantor Kelurahan bagian Kesejahteraan Rakyat (Kesra).</li>
                            <li>Membuat <strong>Surat Keterangan Tidak Mampu (SKTM)</strong> dari RT/RW.</li>
                            <li>Petugas kelurahan akan memasukkan data keluarga ke dalam usulan <strong>SIKS-NG</strong> untuk Musyawarah Kelurahan (Muskel).</li>
                            <li>Anda juga bisa mendaftar mandiri via aplikasi <em>Cek Bansos</em> resmi Kemensos di Playstore.</li>
                        </ul>
                    </div>
                </div>
            ',
            'suggestions' => [
                'Syarat Surat Keterangan Tidak Mampu (SKTM)',
                'Jadwal loket pelayanan kelurahan',
                'Bantuan darurat call center 112'
            ]
        ];
    }

    // 6. Kesehatan, Puskesmas, Posyandu, Vaksinasi
    if (preg_match('/(puskesmas|posyandu|vaksin|kesehatan|imunisasi|dokter|obat|ambulans)/i', $q)) {
        return [
            'reply' => '
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                        <span class="material-symbols-outlined text-primary text-[20px]">vaccines</span>
                        <strong class="text-primary font-headline-md">Jadwal Puskesmas & Layanan Kesehatan Warga</strong>
                    </div>
                    <p>Berikut informasi pelayanan kesehatan terdekat untuk warga:</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-body-sm">
                        <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/30">
                            <strong class="text-primary block mb-1">Puskesmas Kecamatan</strong>
                            <p><strong>Hari:</strong> Senin – Sabtu</p>
                            <p><strong>Jam:</strong> 07.30 – 14.00 WIB</p>
                            <p><strong>IGD 24 Jam:</strong> Selalu siaga</p>
                            <p class="text-xs text-outline mt-1">Layanan BPJS Kesehatan / KIS Gratis</p>
                        </div>
                        <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/30">
                            <strong class="text-primary block mb-1">Posyandu Balita & Lansia</strong>
                            <p><strong>Jadwal:</strong> Minggu ke-2 setiap bulan</p>
                            <p><strong>Tempat:</strong> Balai RW Sukamaju</p>
                            <p><strong>Layanan:</strong> Timbang balita, imunisasi dasar, cek gula darah & tensi lansia.</p>
                        </div>
                    </div>
                </div>
            ',
            'suggestions' => [
                'Nomor ambulans gawat darurat',
                'Syarat BPJS PBI / KIS',
                'Jam pelayanan kelurahan'
            ],
            'action' => [
                'label' => 'Hubungi Ambulans Darurat 119',
                'url' => 'tel:119'
            ]
        ];
    }

    // 7. Jam Buka & Lokasi Kantor Kelurahan
    if (preg_match('/(jam buka|operasional|jam kerja|loket|kantor buka|tutup|alamat kelurahan)/i', $q)) {
        return [
            'reply' => '
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                        <span class="material-symbols-outlined text-primary text-[20px]">schedule</span>
                        <strong class="text-primary font-headline-md">Jam Pelayanan Kantor Kelurahan</strong>
                    </div>
                    <div class="bg-surface-container-low/70 p-3.5 rounded-xl border border-outline-variant/30 space-y-2 text-body-sm">
                        <div class="flex items-center justify-between pb-1 border-b border-outline-variant/20">
                            <span><strong>Senin – Kamis:</strong></span>
                            <span class="font-bold text-primary">08.00 – 15.30 WIB</span>
                        </div>
                        <div class="flex items-center justify-between pb-1 border-b border-outline-variant/20">
                            <span><strong>Jumat:</strong></span>
                            <span class="font-bold text-primary">08.00 – 15.00 WIB</span>
                        </div>
                        <div class="flex items-center justify-between text-error font-medium">
                            <span><strong>Sabtu, Minggu & Libur Nasional:</strong></span>
                            <span>Tutup</span>
                        </div>
                        <div class="text-xs text-outline pt-1">
                            <em>*Istirahat: 12.00 – 13.00 WIB (Jumat: 11.30 – 13.00 WIB)</em>
                        </div>
                    </div>
                    <p class="text-body-sm">Untuk permohonan darurat di luar jam kerja, Anda dapat menghubungi <strong>Call Center 112</strong> yang siaga 24 jam.</p>
                </div>
            ',
            'suggestions' => [
                'Syarat Surat Domisili',
                'Syarat Pembuatan KTP',
                'Lapor Masalah Lingkungan'
            ]
        ];
    }

    // 8. Kontak Darurat
    if (preg_match('/(darurat|emergency|112|polisi|pemadam|damkar|kebakaran|kejahatan|ambulans)/i', $q)) {
        return [
            'reply' => '
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                        <span class="material-symbols-outlined text-error text-[20px]">emergency</span>
                        <strong class="text-error font-headline-md">Nomor Panggilan Darurat Siaga 24 Jam</strong>
                    </div>
                    <p>Jika terjadi situasi bahaya yang memerlukan tindakan segera, hubungi nomor bebas pulsa berikut:</p>
                    
                    <div class="grid grid-cols-2 gap-2 text-body-sm font-semibold">
                        <a href="tel:112" class="p-3 rounded-xl bg-red-50 text-red-700 border border-red-200 flex items-center justify-between hover:bg-red-100 transition-colors">
                            <span>Call Center Terpadu</span>
                            <span class="text-headline-md font-bold">112</span>
                        </a>
                        <a href="tel:113" class="p-3 rounded-xl bg-orange-50 text-orange-700 border border-orange-200 flex items-center justify-between hover:bg-orange-100 transition-colors">
                            <span>Pemadam Kebakaran</span>
                            <span class="text-headline-md font-bold">113</span>
                        </a>
                        <a href="tel:110" class="p-3 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-between hover:bg-blue-100 transition-colors">
                            <span>Kepolisian</span>
                            <span class="text-headline-md font-bold">110</span>
                        </a>
                        <a href="tel:119" class="p-3 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-between hover:bg-emerald-100 transition-colors">
                            <span>Ambulans Medis</span>
                            <span class="text-headline-md font-bold">119</span>
                        </a>
                    </div>
                </div>
            ',
            'suggestions' => [
                'Lapor masalah lingkungan',
                'Jam pelayanan kelurahan',
                'Kembali ke Beranda'
            ],
            'action' => [
                'label' => 'Hubungi Call Center 112',
                'url' => 'tel:112'
            ]
        ];
    }

    // 9. Sapaan & Obrolan Ramah
    if (preg_match('/(halo|hai|apa kabar|kabar|gimana|pagi|siang|sore|malam|assalamualaikum|tes|test|hai bot)/i', $q)) {
        $isApaKabar = preg_match('/(apa kabar|kabar|gimana kabar)/i', $q);
        $salamPembuka = $isApaKabar 
            ? "Halo <strong>" . htmlspecialchars($userName) . "</strong>! Kabar saya sangat baik, senang dan siap membantu Anda hari ini. 😊 Bagaimana dengan kabar Anda?"
            : "Halo <strong>" . htmlspecialchars($userName) . "</strong>! Senang menyapa Anda. 😊";

        return [
            'reply' => '
                <div class="space-y-2">
                    <p>' . $salamPembuka . '</p>
                    <p class="text-body-sm">Saya adalah <strong>Teman Warga AI</strong>, asisten pintar siap sedia membantu Anda seputar administrasi kelurahan, pembuatan berkas (KTP, KK, Surat Domisili), jadwal kesehatan, maupun bantuan pelaporan masalah lingkungan warga.</p>
                    <p class="text-body-sm text-outline">Ketik hal yang ingin Anda tanyakan atau pilih salah satu topik cepat di bawah ini:</p>
                </div>
            ',
            'suggestions' => [
                'Syarat Pembuatan e-KTP',
                'Cara buat Surat Domisili',
                'Lapor jalan rusak atau sampah'
            ]
        ];
    }

    // 10. Ucapan Terima Kasih
    if (preg_match('/(terima kasih|makasih|thanks|thank you|ok|oke|siap)/i', $q)) {
        return [
            'reply' => '
                <div class="space-y-2">
                    <p>Sama-sama <strong>' . htmlspecialchars($userName) . '</strong>! Senang sekali bisa membantu Anda. 🙌</p>
                    <p class="text-body-sm">Jangan ragu bertanya kembali jika membutuhkan bantuan seputar pelayanan lingkungan warga. Semoga hari Anda menyenangkan!</p>
                </div>
            ',
            'suggestions' => [
                'Jam operasional kelurahan',
                'Buat laporan masalah baru',
                'Lihat peta laporan'
            ]
        ];
    }

    // Default Fallback dengan navigasi panduan lengkap
    return [
        'reply' => '
            <div class="space-y-3">
                <p>Terima kasih atas pertanyaannya, <strong>' . htmlspecialchars($userName) . '</strong>.</p>
                <p class="text-body-sm">Untuk memastikan Anda mendapatkan informasi yang paling akurat, topik pertanyaan Anda dapat ditemukan pada salah satu layanan berikut:</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-body-sm">
                    <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/30">
                        <strong>Administrasi & Kependudukan:</strong><br/>
                        Ketik <em>"syarat KTP"</em>, <em>"buat KK"</em>, atau <em>"surat domisili"</em>.
                    </div>
                    <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/30">
                        <strong>Pengaduan Lingkungan:</strong><br/>
                        Ketik <em>"lapor jalan rusak"</em>, <em>"tumpukan sampah"</em>, atau <em>"lampu mati"</em>.
                    </div>
                    <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/30">
                        <strong>Bantuan Sosial & Kesejahteraan:</strong><br/>
                        Ketik <em>"daftar bansos"</em>, <em>"syarat PKH"</em>, atau <em>"SKTM"</em>.
                    </div>
                    <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/30">
                        <strong>Kesehatan & Jam Layanan:</strong><br/>
                        Ketik <em>"jadwal puskesmas"</em>, <em>"posyandu"</em>, atau <em>"jam kelurahan"</em>.
                    </div>
                </div>
            </div>
        ',
        'suggestions' => [
            'Pembuatan KTP Elektronik',
            'Cara buat Surat Domisili',
            'Lapor Fasilitas Rusak'
        ]
    ];
}
