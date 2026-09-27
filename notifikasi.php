<?php
/**
 * notifikasi.php — Halaman Notifikasi Pengguna
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/nav_sidebar.php';
requireLogin();

$user   = currentUser();
$db     = getDB();

// Tandai semua sebagai dibaca jika klik "baca semua"
if (isset($_GET['baca_semua'])) {
    $db->prepare("UPDATE notifikasi SET dibaca = 1 WHERE user_id = ?")
       ->execute([$user['id']]);
    header('Location: notifikasi.php');
    exit;
}

// Tandai satu notifikasi sebagai dibaca
if (isset($_GET['baca']) && is_numeric($_GET['baca'])) {
    $db->prepare("UPDATE notifikasi SET dibaca = 1 WHERE id = ? AND user_id = ?")
       ->execute([(int)$_GET['baca'], $user['id']]);
    $link = $db->prepare("SELECT link FROM notifikasi WHERE id = ? AND user_id = ?");
    $link->execute([(int)$_GET['baca'], $user['id']]);
    $row = $link->fetch();
    if ($row && $row['link']) {
        header('Location: ' . $row['link']);
        exit;
    }
    header('Location: notifikasi.php');
    exit;
}

// Ambil semua notifikasi
$stmt = $db->prepare("SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user['id']]);
$notifs = $stmt->fetchAll();

$unreadCount = countUnreadNotif();

// Icon berdasarkan tipe
function notifIcon(string $tipe): string {
    return match($tipe) {
        'laporan'  => '<span class="material-symbols-outlined text-[20px]">campaign</span>',
        'forum'    => '<span class="material-symbols-outlined text-[20px]">groups</span>',
        'darurat'  => '<span class="material-symbols-outlined text-[20px]">emergency</span>',
        default    => '<span class="material-symbols-outlined text-[20px]">notifications</span>',
    };
}
function notifColor(string $tipe): string {
    return match($tipe) {
        'laporan'  => 'bg-amber-100 text-amber-700',
        'forum'    => 'bg-indigo-100 text-indigo-700',
        'darurat'  => 'bg-red-100 text-red-700',
        default    => 'bg-blue-100 text-blue-700',
    };
}
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Baru saja';
    if ($diff < 3600)   return floor($diff/60) . ' menit lalu';
    if ($diff < 86400)  return floor($diff/3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff/86400) . ' hari lalu';
    return date('d M Y', strtotime($datetime));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifikasi — Teman Warga AI</title>
<link rel="icon" type="image/svg+xml" href="logo.php"/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
tailwind.config={darkMode:"class",theme:{extend:{colors:{"surface-container-low":"#eff4ff","surface-container-lowest":"#ffffff","outline":"#737686","on-background":"#0b1c30","outline-variant":"#c3c6d7","surface-container-highest":"#d3e4fe","surface-dim":"#cbdbf5","on-primary":"#ffffff","error-container":"#ffdad6","surface-container":"#e5eeff","tertiary":"#005b7c","surface":"#f8f9ff","secondary-fixed-dim":"#93ccff","primary-fixed-dim":"#b4c5ff","on-secondary-fixed":"#001d31","on-surface-variant":"#434655","on-primary-fixed-variant":"#003ea8","secondary-fixed":"#cce5ff","surface-variant":"#d3e4fe","on-surface":"#0b1c30","primary":"#004ac6","secondary":"#006398","secondary-container":"#5bb8fe","primary-container":"#2563eb","primary-fixed":"#dbe1ff","on-tertiary":"#ffffff","background":"#f8f9ff","on-tertiary-fixed-variant":"#004c69","on-primary-fixed":"#00174b","tertiary-fixed-dim":"#7bd0ff","on-secondary-fixed-variant":"#004b73","surface-bright":"#f8f9ff","inverse-surface":"#213145","on-error-container":"#93000a","surface-container-high":"#dce9ff","on-tertiary-container":"#e1f2ff","surface-tint":"#0053db","on-primary-container":"#eeefff","on-tertiary-fixed":"#001e2c","tertiary-container":"#00759f","inverse-on-surface":"#eaf1ff","on-secondary":"#ffffff","on-secondary-container":"#00476e","inverse-primary":"#b4c5ff","tertiary-fixed":"#c4e7ff","error":"#ba1a1a","on-error":"#ffffff"},borderRadius:{"DEFAULT":"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px"},spacing:{"space-md":"1rem","space-lg":"1.5rem","space-sm":"0.5rem","space-xs":"0.25rem","gutter":"1.25rem","margin":"2rem","space-xl":"2rem"},fontFamily:{"sans":["Outfit","sans-serif"]}}}}
</script>
<style>
@keyframes fadeInUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
.notif-item { animation: fadeInUp .4s ease both; }
</style>
</head>
<body class="bg-gradient-to-b from-surface-container-lowest to-surface font-sans text-on-surface min-h-screen">

<!-- Header -->
<header class="fixed top-0 left-0 right-0 h-16 z-50 bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] px-gutter flex items-center justify-between gap-4">
  <?php renderHeaderBrand(); ?>
  <div class="flex items-center gap-3 shrink-0">
    <a href="<?= getHomeDashboardUrl(); ?>" class="flex items-center gap-2 text-sm font-semibold text-on-surface-variant hover:text-primary transition-colors">
      <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
    </a>
    <a href="Profile.php" class="flex items-center gap-2 pl-2 py-1 pr-2 rounded-xl hover:bg-surface-container-low cursor-pointer transition-all">
      <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center ring-2 ring-transparent hover:ring-primary/30">
        <span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
      </div>
      <span class="text-sm font-semibold text-on-surface hidden md:inline"><?= htmlspecialchars($user['nama']) ?></span>
    </a>
  </div>
</header>

<!-- Main Content -->
<div class="pt-16">
  <main class="max-w-2xl mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-on-surface">Notifikasi</h1>
        <p class="text-sm text-outline mt-0.5">
          <?php if ($unreadCount > 0): ?>
          <span class="text-primary font-semibold"><?= $unreadCount ?> belum dibaca</span>
          <?php else: ?>
          Semua notifikasi telah dibaca
          <?php endif; ?>
        </p>
      </div>
      <?php if ($unreadCount > 0): ?>
      <a href="notifikasi.php?baca_semua=1" class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-primary bg-surface-container-low hover:bg-surface-container rounded-xl transition-all active:scale-95">
        <span class="material-symbols-outlined text-[18px]">done_all</span>
        Baca Semua
      </a>
      <?php endif; ?>
    </div>

    <!-- Notifikasi List -->
    <?php if (empty($notifs)): ?>
    <div class="text-center py-20 bg-surface-container-lowest rounded-2xl border border-surface-container-high/40 shadow-sm">
      <span class="material-symbols-outlined text-[64px] text-outline/50">notifications_off</span>
      <h3 class="text-lg font-bold text-on-surface mt-3">Tidak Ada Notifikasi</h3>
      <p class="text-sm text-outline mt-1">Notifikasi Anda akan muncul di sini.</p>
    </div>
    <?php else: ?>
    <div class="space-y-2">
      <?php foreach ($notifs as $i => $n): ?>
      <a href="notifikasi.php?baca=<?= $n['id'] ?>"
         class="notif-item flex items-start gap-4 p-4 rounded-2xl border transition-all duration-200 cursor-pointer group <?= $n['dibaca'] ? 'bg-surface-container-lowest border-surface-container-high/40 hover:bg-surface-container-low' : 'bg-blue-50/50 border-brand-200/60 hover:bg-blue-50' ?>"
         style="animation-delay: <?= $i * 0.04 ?>s">
        <!-- Icon -->
        <div class="w-11 h-11 rounded-xl <?= notifColor($n['tipe']) ?> flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
          <?= notifIcon($n['tipe']) ?>
        </div>
        <!-- Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <h3 class="text-sm font-bold text-on-surface truncate <?= !$n['dibaca'] ? 'text-primary' : '' ?>">
              <?= htmlspecialchars($n['judul']) ?>
            </h3>
            <?php if (!$n['dibaca']): ?>
            <span class="w-2 h-2 rounded-full bg-brand-600 shrink-0"></span>
            <?php endif; ?>
          </div>
          <p class="text-sm text-on-surface-variant mt-0.5 leading-relaxed">
            <?= htmlspecialchars($n['pesan']) ?>
          </p>
          <span class="text-xs text-outline mt-1 inline-block"><?= timeAgo($n['created_at']) ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
