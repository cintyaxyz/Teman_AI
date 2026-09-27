<?php
/**
 * lupa_password.php — Halaman Lupa Password
 */
require_once __DIR__ . '/auth.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    header('Location: beranda.php');
    exit;
}

$step    = $_GET['step'] ?? 'request'; // request | sent | reset | success
$error   = '';
$success = '';

// ─── Step 1: Request Email ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_request'])) {
    $email = clean($_POST['email'] ?? '');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Masukkan alamat email yang valid.';
    } else {
        try {
            $db   = getDB();
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                // Buat token
                $token     = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 jam
                $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)")
                   ->execute([$email, $token, $expiresAt]);
                // Simpan email di session untuk halaman konfirmasi
                $_SESSION['reset_email'] = $email;
            }
            // Selalu redirect ke sent (jangan bocorkan apakah email ada)
            header('Location: lupa_password.php?step=sent');
            exit;
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}

// ─── Step 3: Reset Password ────────────────────────────────────
$resetToken = $_GET['token'] ?? '';
$validToken = null;
if ($step === 'reset' && $resetToken) {
    try {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
        $stmt->execute([$resetToken]);
        $validToken = $stmt->fetch();
        if (!$validToken) {
            $error = 'Link reset tidak valid atau sudah kadaluarsa.';
            $step  = 'request';
        }
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan. Silakan minta link baru.';
        $step  = 'request';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_reset'])) {
    $token   = clean($_POST['token'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirmation'] ?? '';
    if (strlen($pass) < 8) {
        $error = 'Password minimal 8 karakter.';
        $step  = 'reset';
    } elseif ($pass !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
        $step  = 'reset';
    } else {
        try {
            $db   = getDB();
            $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
            $stmt->execute([$token]);
            $row  = $stmt->fetch();
            if ($row) {
                $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
                $db->prepare("UPDATE users SET password = ? WHERE email = ?")->execute([$hash, $row['email']]);
                $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")->execute([$token]);
                header('Location: lupa_password.php?step=success');
                exit;
            } else {
                $error = 'Link tidak valid atau sudah digunakan.';
                $step  = 'request';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan. Silakan coba lagi.';
            $step  = 'reset';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lupa Password — Teman Warga AI</title>
<link rel="icon" type="image/svg+xml" href="logo.php"/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
tailwind.config = {
  theme: { extend: { fontFamily: { sans: ['"Outfit"', 'sans-serif'] },
    colors: { brand: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' } } } }
}
</script>
<style>
body { font-family: 'Outfit', sans-serif; }
.input-aura:focus { background-color:#fff; border-color:#3b82f6; box-shadow:0 0 0 4px rgba(59,130,246,.2); outline:none; }
@keyframes fadeUp { from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)} }
.fade-up { animation: fadeUp .55s cubic-bezier(.16,1,.3,1) both; }
</style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 antialiased">
<div class="w-full max-w-md fade-up">
  <!-- Header Branding -->
  <div class="text-center mb-8">
    <a href="landing-page.php" class="inline-flex items-center gap-2 mb-4 group">
      <div class="w-10 h-10 rounded-xl overflow-hidden shadow-md group-hover:scale-105 transition-transform">
        <img src="logo.php" class="w-full h-full object-cover" alt="Logo">
      </div>
      <span class="text-xl font-bold text-slate-900">Teman Warga <span class="text-brand-600">AI</span></span>
    </a>
  </div>

  <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
    <?php if ($step === 'request'): ?>
    <!-- Step 1: Masukkan Email -->
    <div class="text-center mb-6">
      <div class="w-16 h-16 rounded-2xl bg-blue-50 text-brand-600 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Lupa Password?</h1>
      <p class="text-sm text-slate-500 mt-1">Masukkan email akun Anda dan kami akan kirim instruksi reset password.</p>
    </div>
    <?php if ($error): ?>
    <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
      <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="email">Alamat Email <span class="text-red-500">*</span></label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
          <input type="email" id="email" name="email" class="input-aura block w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 transition-all" placeholder="email@contoh.com" required>
        </div>
      </div>
      <button type="submit" name="action_request" class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg">
        Kirim Link Reset Password
      </button>
    </form>

    <?php elseif ($step === 'sent'): ?>
    <!-- Step 2: Email Terkirim -->
    <div class="text-center">
      <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"/></svg>
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Email Terkirim!</h1>
      <p class="text-sm text-slate-500 mt-2 leading-relaxed">
        Jika email <strong><?= htmlspecialchars($_SESSION['reset_email'] ?? 'Anda') ?></strong> terdaftar, 
        kami telah mengirim link reset password. Cek kotak masuk dan folder spam Anda.
      </p>
      <div class="mt-6 p-4 rounded-xl bg-amber-50 border border-amber-100 text-xs text-amber-700 text-left">
        <strong>Catatan:</strong> Link valid selama 1 jam. Jika tidak menerima email, periksa folder spam atau minta link baru.
      </div>
      <a href="lupa_password.php" class="mt-4 inline-flex items-center gap-1 text-sm text-brand-600 hover:text-brand-700 font-semibold">
        ← Minta Link Baru
      </a>
    </div>

    <?php elseif ($step === 'reset' && $validToken): ?>
    <!-- Step 3: Isi Password Baru -->
    <div class="text-center mb-6">
      <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Buat Password Baru</h1>
      <p class="text-sm text-slate-500 mt-1">Pastikan password baru Anda kuat dan mudah diingat.</p>
    </div>
    <?php if ($error): ?>
    <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" class="space-y-4" id="resetForm">
      <input type="hidden" name="token" value="<?= htmlspecialchars($resetToken) ?>">
      <div>
        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Password Baru <span class="text-red-500">*</span></label>
        <input type="password" name="password" id="new_pass" class="input-aura block w-full px-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg text-slate-900 transition-all" placeholder="Min. 8 karakter" required>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Password <span class="text-red-500">*</span></label>
        <input type="password" name="password_confirmation" id="confirm_pass" class="input-aura block w-full px-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg text-slate-900 transition-all" placeholder="Ulangi password baru" required>
      </div>
      <div id="passMatchMsg" class="hidden text-xs font-medium"></div>
      <button type="submit" name="action_reset" class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-md transition-all hover:-translate-y-0.5">
        Simpan Password Baru
      </button>
    </form>
    <script>
    document.getElementById('confirm_pass').addEventListener('input', function() {
      const msg = document.getElementById('passMatchMsg');
      if (this.value === document.getElementById('new_pass').value) {
        msg.className = 'text-xs font-medium text-emerald-600';
        msg.textContent = '✓ Password cocok';
      } else {
        msg.className = 'text-xs font-medium text-red-500';
        msg.textContent = '✗ Password tidak cocok';
      }
      msg.classList.remove('hidden');
    });
    </script>

    <?php elseif ($step === 'success'): ?>
    <!-- Step 4: Sukses -->
    <div class="text-center">
      <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Password Berhasil Diubah!</h1>
      <p class="text-sm text-slate-500 mt-2">Password akun Anda telah berhasil diperbarui. Silakan login dengan password baru Anda.</p>
      <a href="Halaman_daftar.php?tab=masuk" class="mt-6 inline-flex items-center justify-center gap-2 px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-md transition-all hover:-translate-y-0.5">
        Masuk ke Akun
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
      </a>
    </div>

    <?php else: ?>
    <div class="text-center text-slate-500 text-sm py-4">Link tidak valid. <a href="lupa_password.php" class="text-brand-600 font-semibold">Minta link baru</a>.</div>
    <?php endif; ?>

    <!-- Back to Login -->
    <?php if (!in_array($step, ['sent','success'])): ?>
    <div class="mt-6 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
      Ingat password Anda? <a href="Halaman_daftar.php?tab=masuk" class="text-brand-600 font-bold hover:underline">Kembali Masuk</a>
    </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
