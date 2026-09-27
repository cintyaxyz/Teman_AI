<?php
/**
 * auth.php — Helper autentikasi & session Teman Warga AI
 */

require_once __DIR__ . '/config.php';

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400, // 1 hari
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ─── Fungsi Cek Login ──────────────────────────────────────────────────────
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ─── Paksa Login (redirect jika belum login) ──────────────────────────────
function requireLogin(string $redirect = 'Halaman_daftar.php?tab=masuk'): void {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect);
        exit;
    }
}

// ─── Paksa Role (redirect jika bukan petugas) ─────────────────────────────
function requireRole(string $role, string $redirect = 'beranda.php'): void {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== $role) {
        header('Location: ' . $redirect);
        exit;
    }
}

// ─── Ambil Data User yang Sedang Login ────────────────────────────────────
function currentUser(): array {
    if (!isLoggedIn()) return [];
    return [
        'id'     => $_SESSION['user_id']   ?? 0,
        'nama'   => $_SESSION['user_nama']  ?? 'Pengguna',
        'email'  => $_SESSION['user_email'] ?? '',
        'role'   => $_SESSION['user_role']  ?? 'warga',
        'avatar' => $_SESSION['user_avatar'] ?? '',
    ];
}

function getCurrentUser(): array {
    return currentUser();
}

/**
 * Mengambil profil lengkap user langsung dari tabel `users` di database
 */
function getFullUserProfile(?int $userId = null): ?array {
    $id = $userId ?? ($_SESSION['user_id'] ?? 0);
    if (!$id) return null;
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    } catch (Exception $e) {
        return null;
    }
}

// ─── Set Session setelah Login ────────────────────────────────────────────
function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']     = $user['id'];
    $_SESSION['user_nama']   = $user['nama_lengkap'];
    $_SESSION['user_email']  = $user['email'];
    $_SESSION['user_role']   = $user['role'];
    $_SESSION['user_avatar'] = $user['avatar'] ?? '';
    $_SESSION['login_time']  = time();
}

// ─── Hapus Session (Logout) ───────────────────────────────────────────────
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

// ─── Hitung Notifikasi Belum Dibaca ───────────────────────────────────────
function countUnreadNotif(): int {
    if (!isLoggedIn()) return 0;
    try {
        $db   = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifikasi WHERE user_id = ? AND dibaca = 0");
        $stmt->execute([$_SESSION['user_id']]);
        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function getUnreadNotificationCount(): int {
    return countUnreadNotif();
}

// ─── Sanitasi Input ───────────────────────────────────────────────────────
function clean(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// ─── Flash Message ────────────────────────────────────────────────────────
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (!isset($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
