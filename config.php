<?php
/**
 * config.php — Konfigurasi Database & Konstanta Teman Warga AI
 */

// ─── Pengaturan Database ───────────────────────────────────────────────────
define('DB_HOST',     'localhost');
define('DB_NAME',     'teman_warga_ai');
define('DB_USER',     'root');
define('DB_PASS',     '');
define('DB_CHARSET',  'utf8mb4');

// ─── Pengaturan Aplikasi ──────────────────────────────────────────────────
define('APP_NAME',    'Teman Warga AI');
define('APP_URL',     'http://localhost:8080');
define('APP_VERSION', '1.0.0');

// ─── Pengaturan AI (Opsional: Google Gemini API) ───────────────────────────
// Masukkan Gemini API Key di sini jika ingin menggunakan model AI Generatif Gemini.
// Jika dikosongkan, sistem otomatis memakai Smart Civic AI Engine bawaan.
define('GEMINI_API_KEY', 'AQ.Ab8RN6JfBwi2YScQeTFqHreCKPN5SFtdX8ztYWqZgx8tlSbVEw');
define('GEMINI_MODEL',   'gemini-flash-lite-latest');


// ─── Koneksi PDO ──────────────────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST, DB_NAME, DB_CHARSET
    );
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Tampilkan pesan error yang user-friendly
        die(json_encode([
            'error' => true,
            'message' => 'Koneksi database gagal. Pastikan MySQL sudah berjalan dan database "teman_warga_ai" sudah dibuat.',
            'detail' => $e->getMessage()
        ]));
    }
    return $pdo;
}

// ─── Timezone ─────────────────────────────────────────────────────────────
date_default_timezone_set('Asia/Jakarta');
