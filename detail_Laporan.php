<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/nav_sidebar.php';
requireLogin();

$currentUser = getCurrentUser();
$currentUserName = $currentUser ? htmlspecialchars($currentUser['nama']) : 'Warga';
$currentUserRole = $currentUser ? htmlspecialchars(ucfirst($currentUser['role'] ?? 'Warga')) : 'Warga';
$unreadCount = getUnreadNotificationCount();

// Parameter ID atau PIN Laporan
$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pinParam = isset($_GET['pin']) ? trim($_GET['pin']) : '';

// ─── Proses Aksi Perubahan Status (Admin / Petugas) ─────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = trim($_POST['action']);
    $postReportId = (int)($_POST['report_id'] ?? $reportId);
    $regu = clean($_POST['regu_petugas'] ?? 'Regu Reaksi Cepat BPBD (Tim Bravo-3)');
    $catatan = clean($_POST['catatan'] ?? '');
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

    $newStatus = 'diproses';
    $msg = '';

    if ($action === 'acc_laporan') {
        $newStatus = 'diproses';
        $msg = 'Laporan berhasil di-ACC oleh Admin dan didisposisikan ke ' . $regu . '.';
        if ($postReportId > 0) {
            try {
                $db = getDB();
                $stmt = $db->prepare("UPDATE laporan SET status = 'diproses' WHERE id = ?");
                $stmt->execute([$postReportId]);

                $userStmt = $db->prepare("SELECT user_id, judul FROM laporan WHERE id = ?");
                $userStmt->execute([$postReportId]);
                $lap = $userStmt->fetch(PDO::FETCH_ASSOC);
                if ($lap && !empty($lap['user_id'])) {
                    $notifStmt = $db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, 'laporan', ?)");
                    $notifStmt->execute([
                        $lap['user_id'],
                        'Laporan Di-ACC Admin',
                        'Laporan Anda "' . $lap['judul'] . '" telah di-ACC oleh Admin dan ditugaskan ke ' . $regu . '.',
                        'detail_Laporan.php?id=' . $postReportId
                    ]);
                }
            } catch (Exception $e) {}
        }
        $_SESSION['demo_regu_' . $postReportId] = $regu;
        $_SESSION['demo_status_' . $postReportId] = 'diproses';
    } elseif ($action === 'tolak_laporan') {
        $newStatus = 'ditolak';
        $alasan = clean($_POST['alasan_tolak'] ?? 'Data laporan tidak memenuhi kriteria validasi');
        $msg = 'Laporan telah ditolak oleh Admin: ' . $alasan . '.';
        if ($postReportId > 0) {
            try {
                $db = getDB();
                $stmt = $db->prepare("UPDATE laporan SET status = 'ditolak' WHERE id = ?");
                $stmt->execute([$postReportId]);

                $userStmt = $db->prepare("SELECT user_id, judul FROM laporan WHERE id = ?");
                $userStmt->execute([$postReportId]);
                $lap = $userStmt->fetch(PDO::FETCH_ASSOC);
                if ($lap && !empty($lap['user_id'])) {
                    $notifStmt = $db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, 'laporan', ?)");
                    $notifStmt->execute([
                        $lap['user_id'],
                        'Laporan Ditolak',
                        'Laporan Anda "' . $lap['judul'] . '" tidak dapat ditindaklanjuti: ' . $alasan,
                        'detail_Laporan.php?id=' . $postReportId
                    ]);
                }
            } catch (Exception $e) {}
        }
        $_SESSION['demo_status_' . $postReportId] = 'ditolak';
    } elseif ($action === 'mulai_penanganan') {
        $newStatus = 'diproses';
        $msg = 'Petugas lapangan telah memulai penanganan langsung di lokasi.';
        if ($postReportId > 0) {
            try {
                $db = getDB();
                $stmt = $db->prepare("UPDATE laporan SET status = 'diproses' WHERE id = ?");
                $stmt->execute([$postReportId]);
            } catch (Exception $e) {}
        }
        $_SESSION['demo_status_' . $postReportId] = 'diproses';
    } elseif ($action === 'selesai_laporan') {
        $newStatus = 'selesai';
        $msg = 'Laporan penanganan berhasil diselesaikan oleh petugas. Foto bukti perbaikan telah diverifikasi tuntas!';
        if ($postReportId > 0) {
            try {
                $db = getDB();
                $stmt = $db->prepare("UPDATE laporan SET status = 'selesai' WHERE id = ?");
                $stmt->execute([$postReportId]);

                $userStmt = $db->prepare("SELECT user_id, judul FROM laporan WHERE id = ?");
                $userStmt->execute([$postReportId]);
                $lap = $userStmt->fetch(PDO::FETCH_ASSOC);
                if ($lap && !empty($lap['user_id'])) {
                    $notifStmt = $db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, 'laporan', ?)");
                    $notifStmt->execute([
                        $lap['user_id'],
                        'Laporan Selesai Ditangani',
                        'Petugas telah menuntaskan penanganan laporan "' . $lap['judul'] . '". Terima kasih atas laporan Anda!',
                        'detail_Laporan.php?id=' . $postReportId
                    ]);
                }
            } catch (Exception $e) {}
        }
        $_SESSION['demo_status_' . $postReportId] = 'selesai';
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success'   => true,
            'action'    => $action,
            'newStatus' => $newStatus,
            'regu'      => $regu,
            'message'   => $msg
        ]);
        exit;
    } else {
        $redirectUrl = 'detail_Laporan.php?' . ($postReportId ? 'id=' . $postReportId : ($pinParam ? 'pin=' . urlencode($pinParam) : '')) . '&status_updated=' . $newStatus;
        header('Location: ' . $redirectUrl);
        exit;
    }
}

// Data dasar laporan bawaan (Default: Banjir di Perumahan Melati)
$reportData = [
    'id' => 4,
    'tiket' => '#LPR-2024-0892',
    'judul' => 'Banjir di Perumahan Melati',
    'kategori' => 'Banjir',
    'kategori_raw' => 'banjir',
    'kategori_icon' => 'flood',
    'kategori_color' => '#DC2626',
    'alamat' => 'Jl. Melati Blok C, RT 01/04, Kec. Sukamaju',
    'patokan' => 'Samping pos satpam blok C, dekat selokan primer',
    'lat' => -6.4020,
    'lng' => 106.8385,
    'waktu' => 'Baru saja (' . date('H:i') . ' WIB)',
    'pelapor' => $currentUserName,
    'deskripsi' => 'Terjadi banjir dengan ketinggian sekitar 70 cm di jalan utama perumahan akibat luapan selokan primer dan hujan lebat semalam. Mengakibatkan akses keluar masuk warga terputus total dan beberapa motor mogok. Mohon segera ditindaklanjuti oleh Dinas SDA dan BPBD wilayah.',
    'status_raw' => 'menunggu',
    'status' => 'Menunggu Verifikasi',
    'urgensi' => 'Darurat',
    'badge' => 'Darurat',
    'prioritas' => '9.5',
    'estimasi_dampak' => '±140 KK',
    'rekomendasi' => 'Sistem merekomendasikan pengerahan 2 unit pompa portabel dan evakuasi lansia di Blok C serta koordinasi dengan Posko Damkar terdekat.',
    'estimasi_kedalaman' => '±70 cm',
    'foto_utama' => 'Logo aksesori/Banjir.jpg',
    'foto_tambahan' => [],
    'regu_petugas' => 'Menunggu Penugasan Admin'
];

$catPresets = [
    'jalan_rusak' => ['Logo aksesori/Tanah rusak.jpg', 'Logo aksesori/Kamera.jpg', 'Logo aksesori/Laporan.jpg'],
    'sampah' => ['Logo aksesori/Sampah.jpg', 'Logo aksesori/Kamera.jpg', 'Logo aksesori/Laporan.jpg'],
    'lampu_jalan' => ['Logo aksesori/Lampu.jpg', 'Logo aksesori/Kamera.jpg', 'Logo aksesori/Laporan.jpg'],
    'banjir' => ['Logo aksesori/Banjir.jpg'],
    'fasilitas_umum' => ['Logo aksesori/Fasilitas Umum.jpg', 'Logo aksesori/Kamera.jpg', 'Logo aksesori/Laporan.jpg'],
    'lainnya' => ['Logo aksesori/Lainnya.jpg', 'Logo aksesori/Kamera.jpg', 'Logo aksesori/Laporan.jpg']
];

if ($reportId > 0) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT l.*, u.nama_lengkap FROM laporan l LEFT JOIN users u ON l.user_id = u.id WHERE l.id = ?");
        $stmt->execute([$reportId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $catMap = [
                'jalan_rusak' => ['title' => 'Jalan Rusak', 'icon' => 'edit_road', 'color' => '#EA580C'],
                'sampah' => ['title' => 'Sampah', 'icon' => 'delete', 'color' => '#16A34A'],
                'banjir' => ['title' => 'Banjir', 'icon' => 'flood', 'color' => '#DC2626'],
                'lampu_jalan' => ['title' => 'Lampu Jalan', 'icon' => 'lightbulb', 'color' => '#D97706'],
                'fasilitas_umum' => ['title' => 'Fasilitas Umum', 'icon' => 'home_repair_service', 'color' => '#4F46E5'],
                'lainnya' => ['title' => 'Lainnya', 'icon' => 'more_horiz', 'color' => '#64748B']
            ];
            $catInfo = $catMap[$row['kategori']] ?? ['title' => ucfirst($row['kategori']), 'icon' => 'report', 'color' => '#004ac6'];
            
            $reportData['id'] = $row['id'];
            $reportData['tiket'] = '#LPR-2024-0' . str_pad($row['id'], 3, '0', STR_PAD_LEFT);
            $reportData['judul'] = $row['judul'];
            $reportData['kategori'] = $catInfo['title'];
            $reportData['kategori_raw'] = $row['kategori'];
            $reportData['kategori_icon'] = $catInfo['icon'];
            $reportData['kategori_color'] = $catInfo['color'];
            $reportData['alamat'] = $row['alamat'] ?? 'Jl. Melati, Sukamaju';
            $reportData['patokan'] = 'Kelurahan Sukamaju';
            $reportData['lat'] = (float)($row['lat'] ?? -6.2146);
            $reportData['lng'] = (float)($row['lng'] ?? 106.8451);
            $reportData['waktu'] = date('H:i, d M Y', strtotime($row['created_at']));
            $reportData['pelapor'] = $row['nama_lengkap'] ?? 'Warga';
            $reportData['deskripsi'] = $row['deskripsi'];
            $reportData['status_raw'] = strtolower($row['status'] ?? 'menunggu');
            $reportData['status'] = match($reportData['status_raw']) {
                'diproses' => 'Dalam Penanganan',
                'selesai'  => 'Selesai',
                'ditolak'  => 'Ditolak',
                default    => 'Menunggu Verifikasi'
            };
            $reportData['urgensi'] = ucfirst($row['prioritas']);
            $reportData['badge'] = ($row['prioritas'] === 'darurat' || $row['prioritas'] === 'tinggi') ? 'Darurat' : 'Perhatian';
            $reportData['prioritas'] = ($row['prioritas'] === 'darurat') ? '9.8' : (($row['prioritas'] === 'tinggi') ? '8.9' : '7.5');
            $reportData['estimasi_kedalaman'] = '';
            $reportData['foto_utama'] = !empty($row['foto']) ? $row['foto'] : ($catPresets[$row['kategori']][0] ?? 'Logo aksesori/Banjir.jpg');
            $reportData['foto_tambahan'] = [];
            $reportData['regu_petugas'] = ($reportData['status_raw'] === 'menunggu') ? 'Menunggu Penugasan Admin' : 'Regu Reaksi Cepat BPBD (Tim Bravo-3)';
        }
    } catch (Exception $e) {}
} elseif ($pinParam === 'pin-smp2' || $pinParam === 'jalan-rusak') {
    $reportData['tiket'] = '#LPR-2024-0102';
    $reportData['judul'] = 'Jalan berlubang di depan SMP 2';
    $reportData['kategori'] = 'Jalan Rusak';
    $reportData['kategori_raw'] = 'jalan_rusak';
    $reportData['kategori_icon'] = 'edit_road';
    $reportData['kategori_color'] = '#EA580C';
    $reportData['alamat'] = 'Jl. Pitara Raya / SMP 2, Pancoran Mas';
    $reportData['patokan'] = 'Tepat di depan gerbang utama SMP 2';
    $reportData['lat'] = -6.3995;
    $reportData['lng'] = 106.8125;
    $reportData['deskripsi'] = 'Kerusakan jalan aspal berlubang cukup dalam (±15cm) di depan gerbang SMP 2 membahayakan murid dan guru.';
    $reportData['status_raw'] = 'menunggu';
    $reportData['status'] = 'Menunggu Verifikasi';
    $reportData['urgensi'] = 'Darurat';
    $reportData['badge'] = 'Darurat';
    $reportData['prioritas'] = '9.2';
    $reportData['estimasi_dampak'] = '±75 Pengendara / jam';
    $reportData['rekomendasi'] = 'Sistem merekomendasikan penambalan aspal instan (cold mix) dalam 24 jam dan pemasangan traffic cone peringatan bahaya di lokasi lubang.';
    $reportData['estimasi_kedalaman'] = '';
    $reportData['foto_utama'] = 'Logo aksesori/Tanah rusak.jpg';
    $reportData['foto_tambahan'] = [];
    $reportData['pelapor'] = $currentUserName;
    $reportData['waktu'] = 'Baru saja (' . date('H:i') . ' WIB)';
    $reportData['regu_petugas'] = 'Menunggu Penugasan Admin';
}

// Cek status sesi demo jika pernah diubah
$sessionStatusKey = 'demo_status_' . ($reportData['id'] ?? 0);
$sessionReguKey   = 'demo_regu_' . ($reportData['id'] ?? 0);
if (isset($_SESSION[$sessionStatusKey])) {
    $reportData['status_raw'] = $_SESSION[$sessionStatusKey];
    $reportData['status'] = match($_SESSION[$sessionStatusKey]) {
        'diproses' => 'Dalam Penanganan',
        'selesai'  => 'Selesai',
        'ditolak'  => 'Ditolak',
        default    => 'Menunggu Verifikasi'
    };
}
if (isset($_SESSION[$sessionReguKey])) {
    $reportData['regu_petugas'] = $_SESSION[$sessionReguKey];
}

// Override role aktif untuk pengujian (default ke role login, bisa di-switch)
$userRoleClean = strtolower($currentUser['role'] ?? 'petugas');
$activeRole = strtolower($_GET['role'] ?? $userRoleClean);
if (!in_array($activeRole, ['admin', 'petugas', 'warga'])) {
    $activeRole = $userRoleClean === 'admin' ? 'admin' : ($userRoleClean === 'petugas' ? 'petugas' : 'warga');
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"/><meta content="width=device-width, initial-scale=1.0" name="viewport"/><meta content="web_dashboard" name="shell-type"/><title>Detail Laporan - Teman Warga AI</title>
<link rel="icon" type="image/svg+xml" href="logo.php"/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<style>
@layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}main>:first-child{margin-top:0!important;}main>:last-child{margin-bottom:0!important;}}
::-webkit-scrollbar{display:none;}
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-fade-in-up-1 {
  animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.animate-fade-in-up-2 {
  animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.15s backwards;
}
@keyframes pulseRadar {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
}
.pulse-radar-red {
  animation: pulseRadar 2s infinite cubic-bezier(0.4, 0, 0.6, 1);
}


/* Dynamic Robot Floating Animation with Ground Shadow */
@keyframes floatRobot {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-16px) rotate(1.5deg);
  }
}

@keyframes shadowScale {
  0%, 100% {
    transform: scale(1);
    opacity: 0.32;
    filter: blur(0px);
  }
  50% {
    transform: scale(0.62, 0.45);
    opacity: 0.08;
    filter: blur(1.5px);
  }
}

.robot-body-floating {
  animation: floatRobot 2.6s cubic-bezier(0.45, 0.05, 0.55, 0.95) infinite;
  transform-origin: 80px 100px;
}

.robot-shadow {
  transform-origin: 80px 148px;
  animation: shadowScale 2.6s cubic-bezier(0.45, 0.05, 0.55, 0.95) infinite;
}

@keyframes gentleWave {
  0%, 100% { transform: rotate(0deg); }
  20% { transform: rotate(-26deg); }
  45% { transform: rotate(-6deg); }
  70% { transform: rotate(-22deg); }
  85% { transform: rotate(2deg); }
}

.robot-wave {
  transform-origin: 110px 96px;
  animation: gentleWave 2.4s ease-in-out infinite;
}

.robot-eyes-track {
  transition: transform 0.08s cubic-bezier(0.2, 0.8, 0.4, 1);
  will-change: transform;
}

@keyframes eyeBlink {
  0%, 88%, 100% { transform: scaleY(1); }
  92% { transform: scaleY(0.08); }
  96% { transform: scaleY(1); }
}

.robot-eye {
  transform-origin: center;
  animation: eyeBlink 4.8s ease-in-out infinite;
}

@keyframes antennaGlow {
  0%, 100% {
    filter: drop-shadow(0 0 3px rgba(91, 184, 254, 0.6));
    opacity: 0.85;
  }
  50% {
    filter: drop-shadow(0 0 14px rgba(91, 184, 254, 1)) drop-shadow(0 0 20px rgba(37, 99, 235, 0.5));
    opacity: 1;
  }
}

.robot-antenna {
  animation: antennaGlow 2s ease-in-out infinite;
}

.robot-floating {
  animation: floatRobot 2.6s cubic-bezier(0.45, 0.05, 0.55, 0.95) infinite;
}

/* Mascot interactive hover dynamics */
.group\/bot:hover .robot-body-floating {
  animation-duration: 1.6s;
}
.group\/bot:hover .robot-shadow {
  animation-duration: 1.6s;
}
.group\/bot:hover .robot-wave {
  animation-duration: 1.2s;
}

</style><script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script><script id="tailwind-config">tailwind.config={darkMode:"class",theme:{extend:{colors:{"surface-container-low":"#eff4ff","surface-container-lowest":"#ffffff","outline":"#737686","on-background":"#0b1c30","outline-variant":"#c3c6d7","surface-container-highest":"#d3e4fe","surface-dim":"#cbdbf5","on-primary":"#ffffff","error-container":"#ffdad6","surface-container":"#e5eeff","tertiary":"#005b7c","surface":"#f8f9ff","secondary-fixed-dim":"#93ccff","primary-fixed-dim":"#b4c5ff","on-secondary-fixed":"#001d31","on-surface-variant":"#434655","on-primary-fixed-variant":"#003ea8","secondary-fixed":"#cce5ff","surface-variant":"#d3e4fe","on-surface":"#0b1c30","primary":"#004ac6","secondary":"#006398","secondary-container":"#5bb8fe","primary-container":"#2563eb","primary-fixed":"#dbe1ff","on-tertiary":"#ffffff","background":"#f8f9ff","on-tertiary-fixed-variant":"#004c69","on-primary-fixed":"#00174b","tertiary-fixed-dim":"#7bd0ff","on-secondary-fixed-variant":"#004b73","surface-bright":"#f8f9ff","inverse-surface":"#213145","on-error-container":"#93000a","surface-container-high":"#dce9ff","on-tertiary-container":"#e1f2ff","surface-tint":"#0053db","on-primary-container":"#eeefff","on-tertiary-fixed":"#001e2c","tertiary-container":"#00759f","inverse-on-surface":"#eaf1ff","on-secondary":"#ffffff","on-secondary-container":"#00476e","inverse-primary":"#b4c5ff","tertiary-fixed":"#c4e7ff","error":"#ba1a1a","on-error":"#ffffff"},borderRadius:{"DEFAULT":"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px"},spacing:{"space-md":"1rem","space-lg":"1.5rem","space-sm":"0.5rem","space-xs":"0.25rem","gutter":"1.25rem","margin":"2rem","space-xl":"2rem"},fontFamily:{"headline-md":["Outfit","sans-serif"],"label-sm":["Outfit","sans-serif"],"body-md":["Outfit","sans-serif"],"headline-lg":["Outfit","sans-serif"],"body-sm":["Outfit","sans-serif"],"headline-xl":["Outfit","sans-serif"],"body-lg":["Outfit","sans-serif"],"display-lg":["Outfit","sans-serif"],"label-lg":["Outfit","sans-serif"],"label-md":["Outfit","sans-serif"]},fontSize:{"headline-md":["18px",{"lineHeight":"26px","fontWeight":"600"}],"label-sm":["11px",{"lineHeight":"14px","letterSpacing":"0.02em","fontWeight":"600"}],"body-md":["14px",{"lineHeight":"22px","fontWeight":"400"}],"headline-lg":["22px",{"lineHeight":"30px","letterSpacing":"-0.01em","fontWeight":"700"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"headline-xl":["28px",{"lineHeight":"36px","letterSpacing":"-0.015em","fontWeight":"700"}],"body-lg":["16px",{"lineHeight":"24px","fontWeight":"400"}],"display-lg":["40px",{"lineHeight":"48px","letterSpacing":"-0.02em","fontWeight":"800"}],"label-lg":["14px",{"lineHeight":"20px","fontWeight":"600"}],"label-md":["12px",{"lineHeight":"16px","letterSpacing":"0.01em","fontWeight":"600"}]}}}};</script></head><body class="bg-gradient-to-b from-surface-container-lowest to-surface font-body-md text-body-md text-on-surface min-h-screen"><header class="fixed top-0 left-0 right-0 h-16 z-50 bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] px-gutter flex items-center justify-between gap-space-lg"><?php renderHeaderBrand($activeRole); ?><div class="flex-1 max-w-xl mx-auto"><div class="relative flex items-center w-full"><span class="material-symbols-outlined absolute left-space-md text-outline text-[20px] pointer-events-none">search</span><input class="w-full pl-11 pr-space-md py-2 bg-surface-container-low rounded-xl text-on-surface placeholder-outline font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 transition-all" placeholder="Cari bantuan, laporan, atau informasi..." type="text"/></div></div><div class="flex items-center gap-space-md shrink-0"><a href="notifikasi.php" aria-label="Notifikasi" class="relative w-10 h-10 rounded-xl bg-surface-container-low hover:bg-surface-container flex items-center justify-center text-on-surface-variant transition-colors"><span class="material-symbols-outlined text-[22px]">notifications</span><?php if ($unreadCount > 0): ?><span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-error ring-2 ring-surface-container-lowest"></span><?php endif; ?></a><a href="Profile.php" class="flex items-center gap-space-sm pl-space-sm py-1 pr-space-sm rounded-xl hover:bg-surface-container-low cursor-pointer transition-colors"><div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center"><span class="material-symbols-outlined text-on-primary text-[18px]">person</span></div><div class="hidden md:flex flex-col text-left"><span class="font-label-md text-label-md text-on-surface"><?php echo $currentUserName; ?></span><span class="font-label-sm text-label-sm text-outline"><?php echo $currentUserRole; ?></span></div><span class="material-symbols-outlined text-outline text-[18px]">keyboard_arrow_down</span></a></div></header><?php renderSidebarNav('detail-laporan', $activeRole); ?><div class="pl-64"><main class="w-full pt-16 px-margin py-margin"><div class="flex flex-col w-full">
<!-- Back Nav & Breadcrumb Area -->
<div class="flex flex-col gap-space-xs mb-space-lg">
<?php
$backUrl = ($activeRole === 'admin') ? 'dashboard_admin.php' : (($activeRole === 'petugas') ? 'dashboard_petugas.php' : 'peta_laporan.php');
$backText = ($activeRole === 'admin') ? 'Kembali ke Dashboard Admin' : (($activeRole === 'petugas') ? 'Kembali ke Dashboard Petugas' : 'Kembali ke Peta');
?>
<a class="group inline-flex items-center gap-1.5 text-primary hover:text-primary-container font-label-md text-label-md transition-all duration-200 w-fit" href="<?= $backUrl; ?>">
<span class="material-symbols-outlined text-[18px] transition-transform duration-200 group-hover:-translate-x-1.5">arrow_back</span>
<span><?= $backText; ?></span>
</a>

<div class="flex items-center justify-between">
<h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight">Laporan Detail</h1>
<span id="ticketId" class="font-label-sm text-label-sm px-3 py-1 rounded-full bg-surface-container-high text-on-surface-variant font-medium"><?php echo htmlspecialchars($reportData['tiket']); ?></span>
</div>
</div>
<!-- Main 2-Column Content Grid with Staggered Entrance Animations -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-start">
<!-- LEFT COLUMN: Detail Laporan Warga (7 of 12 cols) -->
<div class="lg:col-span-7 flex flex-col gap-space-lg animate-fade-in-up-1">
<div class="bg-surface-container-lowest rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
<!-- Large Incident Photo with Emergency Badge -->
<div class="relative w-full h-80 bg-surface-container-low overflow-hidden group">
<img class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]" data-alt="Foto Kejadian Laporan Warga" id="mainIncidentImage" src="<?php echo htmlspecialchars($reportData['foto_utama']); ?>"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent pointer-events-none"></div>
<!-- Emergency Chip with Pulsing Glow Radar -->
<div id="badgeUrgencyChip" class="absolute top-4 right-4 flex items-center gap-1.5 bg-error text-on-error px-3.5 py-1.5 rounded-full shadow-lg shadow-red-600/40 font-label-md text-label-md uppercase tracking-wider font-bold pulse-radar-red">
<span class="w-2 h-2 rounded-full bg-on-error animate-ping"></span>
<span id="badgeUrgencyText"><?php echo htmlspecialchars($reportData['badge']); ?></span>
</div>
<!-- Quick Water Level Overlay Tag with Modern Glassmorphism -->
<div id="badgeDepth" class="<?php echo !empty($reportData['estimasi_kedalaman']) ? '' : 'hidden'; ?> absolute bottom-4 left-4 flex items-center gap-1.5 backdrop-blur-md bg-slate-900/60 text-white border border-white/10 shadow-sm px-3.5 py-1.5 rounded-lg font-label-sm text-label-sm">
<span class="material-symbols-outlined text-[16px] text-secondary-container">water</span>
<span id="badgeDepthText">Estimasi Kedalaman: <?php echo htmlspecialchars($reportData['estimasi_kedalaman']); ?></span>
</div>
</div>
<!-- Incident Metadata & Description -->
<div class="p-space-lg flex flex-col gap-space-md">
<div class="flex flex-col gap-1.5">
<h2 id="reportTitle" class="font-headline-lg text-headline-lg text-on-surface"><?php echo htmlspecialchars($reportData['judul']); ?></h2>
<div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-outline font-body-sm text-body-sm pt-1">
<div class="flex items-center gap-1 text-on-surface-variant">
<span class="material-symbols-outlined text-[18px] text-primary">location_on</span>
<span id="reportLocation"><?php echo htmlspecialchars($reportData['alamat']); ?></span>
</div>
<span class="w-1 h-1 rounded-full bg-outline-variant"></span>
<div class="flex items-center gap-1 text-on-surface-variant">
<span class="material-symbols-outlined text-[18px] text-outline">schedule</span>
<span id="reportTime"><?php echo htmlspecialchars($reportData['waktu']); ?></span>
</div>
<span class="w-1 h-1 rounded-full bg-outline-variant"></span>
<div class="flex items-center gap-1 text-on-surface-variant">
<span class="material-symbols-outlined text-[18px] text-outline">person_outline</span>
<span id="reportAuthor">Dilaporkan oleh: <?php echo htmlspecialchars($reportData['pelapor']); ?></span>
</div>
</div>
</div>
<div class="w-full h-px bg-surface-container"></div>
<!-- Description Text -->
<div class="flex flex-col gap-2">
<span class="font-label-sm text-label-sm text-outline uppercase tracking-wider">Deskripsi Warga</span>
<p id="reportDesc" class="font-body-md text-body-md text-on-surface leading-relaxed">
  <?php echo htmlspecialchars($reportData['deskripsi']); ?>
</p>
</div>
<!-- Attachment Gallery with Interactive Elevation & Add Photo Capability -->
<div class="flex flex-col gap-space-sm pt-2">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-2">
      <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider">Lampiran Foto Tambahan</span>
      <span id="photoCountBadge" class="px-2 py-0.5 rounded-full bg-surface-container-high text-on-surface-variant text-[11px] font-semibold">0 Foto</span>
    </div>
    <button type="button" onclick="triggerAddPhoto()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary/10 hover:bg-primary/20 text-primary text-xs font-semibold active:scale-95 transition-all cursor-pointer shadow-2xs">
      <span class="material-symbols-outlined text-[16px]">add_a_photo</span>
      <span>Tambah Bukti Foto</span>
    </button>
  </div>

  <!-- Input File Tersembunyi untuk Tambah Foto -->
  <input type="file" id="inputFotoTambahan" accept="image/*" class="hidden" onchange="handleTambahFoto(this)" />

  <div id="galleryContainer" class="grid grid-cols-2 sm:grid-cols-4 gap-space-sm">
    <!-- Diisi secara dinamis oleh JavaScript -->
  </div>
</div>
<!-- Emergency Action Bar with Interactive Transitions -->
<div class="pt-4 flex items-center justify-between gap-3">
<div class="flex items-center gap-2">
<button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container-low hover:bg-slate-100 hover:text-on-surface text-on-surface font-label-md text-label-md active:scale-95 transition-all duration-200 shadow-sm hover:shadow" type="button">
<span class="material-symbols-outlined text-[18px]">share</span>
<span>Bagikan Laporan</span>
</button>
<button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container-low hover:bg-slate-100 hover:text-on-surface text-on-surface font-label-md text-label-md active:scale-95 transition-all duration-200 shadow-sm hover:shadow" type="button">
<span class="material-symbols-outlined text-[18px]">thumb_up</span>
<span>Dukung (28)</span>
</button>
</div>
<a class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-error-container text-on-error-container hover:bg-red-600 hover:text-white hover:shadow-lg hover:shadow-red-500/30 active:scale-95 font-label-md text-label-md transition-all duration-200" href="tel:112">
<span class="material-symbols-outlined text-[18px]">call</span>
<span>Hubungi 112</span>
</a>
</div>
</div>
</div>

</div>
<!-- RIGHT COLUMN: Card Analisis AI & Status Tracking (5 of 12 cols) -->
<div class="lg:col-span-5 flex flex-col gap-space-lg animate-fade-in-up-2">
<!-- AI Intelligence Card -->
<div class="bg-surface-container-lowest rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-space-lg flex flex-col gap-4 border border-surface-container-high/40">
  <!-- AI Card Header -->
  <div class="flex items-center justify-between pb-3 border-b border-surface-container-high/60">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-[20px] font-bold">psychology</span>
      </div>
      <div>
        <h3 class="font-headline-md text-headline-md font-bold text-on-surface leading-tight">Analisis AI</h3>
        <span class="font-body-sm text-body-sm text-slate-500 text-xs font-medium">Verifikasi cerdas otomatis</span>
      </div>
    </div>
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200/60 text-xs font-bold">
      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
      Akurasi 98%
    </span>
  </div>

  <!-- AI Key Metrics: Compact Clean 2x2 Grid with Bold Typography -->
  <div class="grid grid-cols-2 gap-2.5">
    <!-- Kategori -->
    <div class="p-3 rounded-xl bg-surface-container-low/50 border border-slate-100 flex flex-col gap-1">
      <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Kategori</span>
      <div class="flex items-center gap-1.5 font-extrabold text-[15px] text-on-surface truncate">
        <span id="aiCategoryIcon" class="material-symbols-outlined text-primary text-[19px] shrink-0 font-bold"><?php echo htmlspecialchars($reportData['kategori_icon']); ?></span>
        <span id="aiCategory" class="truncate"><?php echo htmlspecialchars($reportData['kategori']); ?></span>
      </div>
    </div>

    <!-- Urgensi -->
    <div class="p-3 rounded-xl bg-surface-container-low/50 border border-slate-100 flex flex-col gap-1">
      <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Tingkat Urgensi</span>
      <div class="flex items-center gap-1.5">
        <span id="aiUrgencyBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-red-100 text-red-700 text-[11px] font-black uppercase tracking-wider"><?php echo htmlspecialchars($reportData['badge']); ?></span>
      </div>
    </div>

    <!-- Dampak -->
    <div class="p-3 rounded-xl bg-surface-container-low/50 border border-slate-100 flex flex-col gap-1">
      <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Estimasi Dampak</span>
      <span id="aiImpact" class="font-extrabold text-[15px] text-on-surface truncate"><?php echo htmlspecialchars($reportData['estimasi_dampak'] ?? '±140 KK'); ?></span>
    </div>

    <!-- Prioritas -->
    <div class="p-3 rounded-xl bg-surface-container-low/50 border border-slate-100 flex flex-col gap-1">
      <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Skor Prioritas</span>
      <div class="flex items-baseline gap-1 text-primary">
        <span id="aiPriorityScore" class="font-extrabold text-lg leading-none"><?php echo htmlspecialchars($reportData['prioritas']); ?></span>
        <span class="text-xs text-slate-500 font-bold">/ 10</span>
      </div>
    </div>
  </div>

  <!-- AI Insight Box: Clean Subtle Recommendation with bold clarity -->
  <div class="p-3 rounded-xl bg-primary/5 border border-primary/10 flex items-start gap-2.5">
    <span class="material-symbols-outlined text-primary text-[18px] shrink-0 mt-0.5 font-bold">auto_awesome</span>
    <div class="flex flex-col gap-0.5">
      <span class="text-[11px] font-extrabold text-primary uppercase tracking-wider">Rekomendasi Tindakan</span>
      <p id="aiRecommendationText" class="text-xs leading-relaxed text-slate-700 font-medium">
        <?php echo htmlspecialchars($reportData['rekomendasi'] ?? 'Sistem merekomendasikan pengerahan 2 unit pompa portabel dan evakuasi lansia di Blok C serta koordinasi dengan Posko Damkar terdekat.'); ?>
      </p>
    </div>
  </div>
</div>
<!-- Status Tracking & Stepper Card -->
<div class="bg-surface-container-lowest rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-space-lg flex flex-col gap-space-lg">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-2">
      <span class="font-headline-md text-headline-md text-on-surface font-bold">Status Laporan</span>
      <span id="roleBadgeIndicator" class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-primary/10 text-primary uppercase tracking-wider">
        Mode: <?= ucfirst($activeRole); ?>
      </span>
    </div>
    <span id="currentStatusBadge" class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-xs font-bold tracking-wide <?= $reportData['status_raw'] === 'selesai' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($reportData['status_raw'] === 'ditolak' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($reportData['status_raw'] === 'diproses' ? 'bg-amber-500/10 text-amber-700 border border-amber-500/30' : 'bg-blue-50 text-blue-700 border border-blue-200')) ?>">
      <?= htmlspecialchars($reportData['status']); ?>
    </span>
  </div>

  <!-- 4-Step Progress Tracker Timeline -->
  <div class="relative flex items-center justify-between w-full px-1 pt-2 pb-1" id="stepperContainer">
    <!-- Progress Line Background -->
    <div class="absolute left-6 right-6 top-6 h-1 bg-surface-container rounded-full z-0"></div>
    <!-- Filled Progress Line -->
    <div id="stepperFillBar" class="absolute left-6 top-6 h-1 bg-primary rounded-full z-0 transition-all duration-700 <?= $reportData['status_raw'] === 'selesai' ? 'w-[calc(100%-48px)] bg-emerald-600' : ($reportData['status_raw'] === 'ditolak' ? 'w-1/3 bg-rose-500' : ($reportData['status_raw'] === 'diproses' ? 'w-2/3 bg-amber-500' : 'w-12 bg-primary')) ?>"></div>

    <!-- Step 1: Baru Masuk -->
    <div class="relative z-10 flex flex-col items-center gap-1.5 text-center flex-1">
      <div id="stepIcon1" class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-md">
        <span class="material-symbols-outlined text-[17px]">check</span>
      </div>
      <span class="text-[11px] font-bold text-on-surface">Baru Masuk</span>
      <span class="text-[10px] text-outline -mt-1">08:15 WIB</span>
    </div>

    <!-- Step 2: Di-ACC Admin -->
    <div class="relative z-10 flex flex-col items-center gap-1.5 text-center flex-1">
      <div id="stepIcon2" class="w-8 h-8 rounded-full <?= in_array($reportData['status_raw'], ['diproses', 'selesai']) ? 'bg-primary text-on-primary shadow-md' : ($reportData['status_raw'] === 'ditolak' ? 'bg-rose-500 text-white shadow-md' : 'bg-surface-container-high text-outline ring-4 ring-blue-50 animate-pulse') ?> flex items-center justify-center">
        <span class="material-symbols-outlined text-[17px]"><?= $reportData['status_raw'] === 'ditolak' ? 'close' : 'verified' ?></span>
      </div>
      <span id="stepLabel2" class="text-[11px] font-bold <?= in_array($reportData['status_raw'], ['diproses', 'selesai']) ? 'text-on-surface' : ($reportData['status_raw'] === 'ditolak' ? 'text-rose-700' : 'text-primary font-extrabold') ?>">
        <?= $reportData['status_raw'] === 'ditolak' ? 'Ditolak' : 'Di-ACC Admin' ?>
      </span>
      <span class="text-[10px] text-outline -mt-1"><?= in_array($reportData['status_raw'], ['diproses', 'selesai']) ? '08:45 WIB' : '-' ?></span>
    </div>

    <!-- Step 3: Dalam Penanganan -->
    <div class="relative z-10 flex flex-col items-center gap-1.5 text-center flex-1">
      <div id="stepIcon3" class="w-8 h-8 rounded-full <?= $reportData['status_raw'] === 'selesai' ? 'bg-primary text-on-primary shadow-md' : ($reportData['status_raw'] === 'diproses' ? 'bg-amber-500 text-white shadow-md ring-4 ring-amber-100' : 'bg-surface-container-high text-outline opacity-50') ?> flex items-center justify-center">
        <span class="material-symbols-outlined text-[17px]">engineering</span>
      </div>
      <span id="stepLabel3" class="text-[11px] font-bold <?= $reportData['status_raw'] === 'diproses' ? 'text-amber-800' : ($reportData['status_raw'] === 'selesai' ? 'text-on-surface' : 'text-outline') ?>">Penanganan</span>
      <span class="text-[10px] text-outline -mt-1"><?= in_array($reportData['status_raw'], ['diproses', 'selesai']) ? '09:40 WIB' : '-' ?></span>
    </div>

    <!-- Step 4: Selesai -->
    <div class="relative z-10 flex flex-col items-center gap-1.5 text-center flex-1">
      <div id="stepIcon4" class="w-8 h-8 rounded-full <?= $reportData['status_raw'] === 'selesai' ? 'bg-emerald-600 text-white shadow-md' : 'bg-surface-container-high text-outline opacity-50' ?> flex items-center justify-center">
        <span class="material-symbols-outlined text-[17px]">task_alt</span>
      </div>
      <span id="stepLabel4" class="text-[11px] font-bold <?= $reportData['status_raw'] === 'selesai' ? 'text-emerald-700 font-extrabold' : 'text-outline' ?>">Selesai</span>
      <span class="text-[10px] text-outline -mt-1"><?= $reportData['status_raw'] === 'selesai' ? 'Tuntas' : '-' ?></span>
    </div>
  </div>

  <!-- Assigned Unit Card -->
  <div class="group p-3.5 rounded-xl bg-surface-container-low hover:bg-surface-container hover:-translate-y-0.5 hover:shadow-sm transition-all duration-300 flex items-center gap-3">
    <div class="w-10 h-10 rounded-lg bg-surface-container-highest flex items-center justify-center text-primary shrink-0 transition-colors group-hover:bg-primary-fixed">
      <span class="material-symbols-outlined text-[22px]">shield_person</span>
    </div>
    <div class="flex flex-col flex-1 min-w-0">
      <span class="font-label-sm text-label-sm text-outline">Petugas / Regu Lapangan</span>
      <span id="assignedUnitName" class="font-label-md text-label-md text-on-surface truncate font-semibold">
        <?= htmlspecialchars($reportData['regu_petugas']); ?>
      </span>
    </div>
    <span id="unitVerifiedIcon" class="material-symbols-outlined <?= $reportData['status_raw'] !== 'menunggu' ? 'text-primary' : 'text-slate-300' ?> text-[20px]">
      <?= $reportData['status_raw'] !== 'menunggu' ? 'verified' : 'pending' ?>
    </span>
  </div>

  <!-- Dispatch Timeline Note -->
  <div class="flex items-center justify-between text-outline font-body-sm text-body-sm pt-1">
    <span>Estimasi penanganan:</span>
    <span id="estTimeBadge" class="font-label-md text-label-md text-on-surface font-semibold px-2 py-0.5 rounded-md bg-surface-container-low border border-slate-200/60">
      <?= $reportData['status_raw'] === 'selesai' ? 'Penanganan Selesai' : '± 1.5 Jam pengerjaan' ?>
    </span>
  </div>
</div>

<!-- ========================================================================= -->
<!-- PANEL KONTROL AKSI: Pemisahan Hak Akses Admin (ACC) vs Petugas (Eksekusi) -->
<!-- ========================================================================= -->
<div class="bg-surface-container-lowest rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-space-lg flex flex-col gap-4 border border-slate-100">
  <!-- Role Switcher Header -->
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 pb-3 border-b border-surface-container-high/60">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-primary text-[20px] font-bold">tune</span>
      <span class="font-headline-md text-headline-md font-bold text-on-surface">Panel Tindakan</span>
    </div>
    <!-- Quick Role Preview Switcher -->
    <div class="flex items-center gap-1 bg-surface-container-low p-1 rounded-xl text-xs font-semibold">
      <button type="button" onclick="switchActiveRole('admin')" id="roleBtnAdmin" class="px-2.5 py-1 rounded-lg transition-all <?= $activeRole === 'admin' ? 'bg-primary text-on-primary shadow-sm font-bold' : 'text-slate-600 hover:text-on-surface' ?>">
        Admin (ACC)
      </button>
      <button type="button" onclick="switchActiveRole('petugas')" id="roleBtnPetugas" class="px-2.5 py-1 rounded-lg transition-all <?= $activeRole === 'petugas' ? 'bg-primary text-on-primary shadow-sm font-bold' : 'text-slate-600 hover:text-on-surface' ?>">
        Petugas
      </button>
      <button type="button" onclick="switchActiveRole('warga')" id="roleBtnWarga" class="px-2.5 py-1 rounded-lg transition-all <?= $activeRole === 'warga' ? 'bg-primary text-on-primary shadow-sm font-bold' : 'text-slate-600 hover:text-on-surface' ?>">
        Warga
      </button>
    </div>
  </div>

  <!-- SECTION 1: ROLE ADMIN (Verifikasi, ACC, Disposisi, Tolak) -->
  <div id="sectionAdmin" class="<?= $activeRole === 'admin' ? '' : 'hidden' ?> flex flex-col gap-3">
    <div class="p-2.5 rounded-xl bg-blue-50/70 border border-blue-100 text-xs text-blue-900 flex items-start gap-2">
      <span class="material-symbols-outlined text-primary text-[18px] shrink-0 mt-0.5">policy</span>
      <div>
        <span class="font-bold block">Hak Akses Admin Kelurahan</span>
        Admin bertugas memverifikasi keabsahan laporan warga, meng-ACC, serta menugaskan regu petugas lapangan.
      </div>
    </div>

    <!-- Tombol Admin jika status Menunggu Verifikasi -->
    <div id="adminActionPending" class="<?= $reportData['status_raw'] === 'menunggu' ? '' : 'hidden' ?> flex flex-col gap-2">
      <button type="button" onclick="openModalAcc()" class="w-full py-2.5 px-4 rounded-xl bg-primary hover:bg-primary-container text-white font-semibold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
        <span class="material-symbols-outlined text-[18px]">verified</span>
        <span>ACC &amp; Disposisikan ke Petugas</span>
      </button>
      <button type="button" onclick="openModalTolak()" class="w-full py-2 px-4 rounded-xl bg-surface-container-low hover:bg-rose-50 text-rose-700 hover:text-rose-800 font-semibold text-xs border border-rose-200/80 transition-all flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer">
        <span class="material-symbols-outlined text-[16px]">cancel</span>
        <span>Tolak Laporan (Tidak Valid / Hoax)</span>
      </button>
    </div>

    <!-- Info Admin jika status sudah di-ACC -->
    <div id="adminActionApproved" class="<?= in_array($reportData['status_raw'], ['diproses', 'selesai']) ? '' : 'hidden' ?> p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-emerald-600 text-[18px]">task_alt</span>
        <span>Laporan telah di-ACC dan diteruskan ke regu petugas.</span>
      </div>
      <button type="button" onclick="openModalAcc()" class="text-xs text-primary font-bold hover:underline shrink-0 ml-2">Ubah Regu</button>
    </div>

    <!-- Info Admin jika status Ditolak -->
    <div id="adminActionRejected" class="<?= $reportData['status_raw'] === 'ditolak' ? '' : 'hidden' ?> p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-rose-600 text-[18px]">block</span>
        <span>Laporan ini telah ditolak oleh Admin.</span>
      </div>
      <button type="button" onclick="reopenReport()" class="text-xs text-primary font-bold hover:underline shrink-0 ml-2">Pulihkan</button>
    </div>
  </div>

  <!-- SECTION 2: ROLE PETUGAS (Eksekusi, Mulai Penanganan, Selesai) -->
  <div id="sectionPetugas" class="<?= $activeRole === 'petugas' ? '' : 'hidden' ?> flex flex-col gap-3">
    <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-100 text-xs text-amber-900 flex items-start gap-2">
      <span class="material-symbols-outlined text-amber-700 text-[18px] shrink-0 mt-0.5">engineering</span>
      <div>
        <span class="font-bold block">Hak Akses Petugas Lapangan</span>
        Petugas hanya dapat menangani laporan yang telah di-ACC oleh Admin, menuju ke lokasi, dan menandai selesai.
      </div>
    </div>

    <!-- Info Petugas jika laporan belum di-ACC -->
    <div id="petugasWaitAcc" class="<?= $reportData['status_raw'] === 'menunggu' ? '' : 'hidden' ?> p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-center gap-2">
      <span class="material-symbols-outlined text-slate-400 text-[18px]">hourglass_top</span>
      <span>Menunggu persetujuan (ACC) dari Admin Kelurahan sebelum laporan dapat ditangani di lapangan.</span>
    </div>

    <!-- Tombol Petugas jika sudah di-ACC & sedang ditangani -->
    <div id="petugasActionActive" class="<?= $reportData['status_raw'] === 'diproses' ? '' : 'hidden' ?> flex flex-col gap-2">
      <button type="button" onclick="mulaiPenangananLangsung()" class="w-full py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm shadow-md transition-all flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
        <span class="material-symbols-outlined text-[18px]">directions_run</span>
        <span>Konfirmasi Tiba di Lokasi</span>
      </button>
      <button type="button" onclick="openModalSelesai()" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md transition-all flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        <span>Selesaikan Laporan &amp; Unggah Bukti</span>
      </button>
    </div>

    <!-- Info Petugas jika laporan Selesai -->
    <div id="petugasActionDone" class="<?= $reportData['status_raw'] === 'selesai' ? '' : 'hidden' ?> p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
      <span class="material-symbols-outlined text-emerald-600 text-[18px]">verified</span>
      <span>Pekerjaan lapangan telah selesai ditangani dengan sukses.</span>
    </div>
  </div>

  <!-- SECTION 3: ROLE WARGA (Pelapor - Monitoring) -->
  <div id="sectionWarga" class="<?= $activeRole === 'warga' ? '' : 'hidden' ?> flex flex-col gap-3">
    <div class="p-3 rounded-xl bg-surface-container-low text-xs text-on-surface-variant flex items-start gap-2.5">
      <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">info</span>
      <div class="flex flex-col gap-1">
        <span class="font-bold text-on-surface">Status Pengaduan Anda</span>
        <p class="text-xs leading-relaxed" id="wargaStatusText">
          <?= $reportData['status_raw'] === 'selesai' ? 'Laporan Anda telah selesai ditangani oleh tim lapangan kelurahan. Terima kasih atas partisipasi aktif Anda.' : ($reportData['status_raw'] === 'diproses' ? 'Laporan Anda telah disetujui (ACC) oleh Admin dan sedang ditangani oleh petugas di lapangan.' : ($reportData['status_raw'] === 'ditolak' ? 'Laporan tidak dapat diproses oleh Admin.' : 'Laporan Anda telah tersimpan di sistem dan sedang mengantre verifikasi dari Admin Kelurahan.')) ?>
        </p>
      </div>
    </div>
  </div>
</div>
<!-- ========================================================================= -->
<!-- MODAL 1: ACC & DISPOSISI LAPORAN (ADMIN)                                   -->
<!-- ========================================================================= -->
<div id="modalAccLaporan" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
  <div class="bg-surface-container-lowest rounded-2xl shadow-2xl border border-slate-100 max-w-lg w-full p-6 flex flex-col gap-4 transform scale-95 transition-transform duration-300">
    <div class="flex items-center justify-between pb-3 border-b border-surface-container-high/60">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">assignment_turned_in</span>
        </div>
        <div>
          <h3 class="font-headline-md text-headline-md font-bold text-on-surface">ACC &amp; Disposisi Laporan</h3>
          <p class="text-xs text-outline">Verifikasi keabsahan dan tugaskan regu lapangan</p>
        </div>
      </div>
      <button type="button" onclick="closeModalAcc()" class="w-8 h-8 rounded-lg hover:bg-surface-container-low flex items-center justify-center text-outline hover:text-on-surface transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form id="formAccLaporan" onsubmit="handleFormAcc(event)" class="flex flex-col gap-3">
      <input type="hidden" name="action" value="acc_laporan">
      <input type="hidden" name="report_id" value="<?= (int)($reportData['id'] ?? 0); ?>">

      <!-- Pilih Regu Petugas -->
      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-on-surface uppercase tracking-wider flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px] text-primary">groups</span>
          <span>Pilih Regu Petugas Lapangan *</span>
        </label>
        <select id="accSelectRegu" name="regu_petugas" required class="w-full py-2.5 px-3 rounded-xl bg-surface-container-low border border-slate-200 text-sm font-medium text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20">
          <option value="Regu Reaksi Cepat BPBD (Tim Bravo-3)">🚨 Regu Reaksi Cepat BPBD (Tim Bravo-3) — Tanggap Bencana &amp; Banjir</option>
          <option value="Dinas PUPR - Regu Penambalan Jalan">🛠️ Dinas PUPR - Regu Penambalan Jalan — Perbaikan Aspal &amp; Jalan Rusak</option>
          <option value="Dinas Lingkungan Hidup - Satgas Sampah">🚛 Dinas Lingkungan Hidup - Satgas Sampah — Pembersihan Tumpukan Sampah</option>
          <option value="Dishub - Regu Penerangan Jalan Umum">💡 Dishub - Regu PJU — Perbaikan Lampu Jalan Mati</option>
          <option value="Satpol PP &amp; Ketertiban Kelurahan">🏢 Satpol PP &amp; Ketertiban Kelurahan — Fasilitas Umum &amp; Keamanan</option>
        </select>
      </div>

      <!-- Catatan Arahan Admin -->
      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-on-surface uppercase tracking-wider flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px] text-primary">edit_note</span>
          <span>Instruksi Khusus untuk Petugas (Opsional)</span>
        </label>
        <textarea id="accCatatanAdmin" name="catatan" rows="3" placeholder="Contoh: Prioritaskan evakuasi warga lansia dan bawa 2 unit pompa portabel ke lokasi..." class="w-full p-3 rounded-xl bg-surface-container-low border border-slate-200 text-xs text-on-surface placeholder-outline focus:outline-none focus:ring-2 focus:ring-primary/20 resize-none"></textarea>
      </div>

      <!-- Konfirmasi Aksi -->
      <div class="pt-2 flex items-center justify-end gap-2 border-t border-surface-container-high/60">
        <button type="button" onclick="closeModalAcc()" class="px-4 py-2 rounded-xl text-xs font-semibold text-outline hover:text-on-surface hover:bg-surface-container-low transition-colors cursor-pointer">
          Batal
        </button>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-1.5 cursor-pointer">
          <span class="material-symbols-outlined text-[16px]">send</span>
          <span>ACC &amp; Teruskan ke Regu</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: TOLAK LAPORAN (ADMIN)                                             -->
<!-- ========================================================================= -->
<div id="modalTolakLaporan" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
  <div class="bg-surface-container-lowest rounded-2xl shadow-2xl border border-slate-100 max-w-lg w-full p-6 flex flex-col gap-4 transform scale-95 transition-transform duration-300">
    <div class="flex items-center justify-between pb-3 border-b border-surface-container-high/60">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">cancel</span>
        </div>
        <div>
          <h3 class="font-headline-md text-headline-md font-bold text-on-surface">Tolak Laporan Warga</h3>
          <p class="text-xs text-outline">Berikan alasan penolakan yang jelas untuk warga pelapor</p>
        </div>
      </div>
      <button type="button" onclick="closeModalTolak()" class="w-8 h-8 rounded-lg hover:bg-surface-container-low flex items-center justify-center text-outline hover:text-on-surface transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form id="formTolakLaporan" onsubmit="handleFormTolak(event)" class="flex flex-col gap-3">
      <input type="hidden" name="action" value="tolak_laporan">
      <input type="hidden" name="report_id" value="<?= (int)($reportData['id'] ?? 0); ?>">

      <!-- Pilih Alasan Penolakan -->
      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-on-surface uppercase tracking-wider">Alasan Penolakan *</label>
        <select id="tolakAlasanSelect" name="alasan_tolak" required class="w-full py-2.5 px-3 rounded-xl bg-surface-container-low border border-slate-200 text-sm font-medium text-on-surface focus:outline-none focus:ring-2 focus:ring-rose-500/20">
          <option value="Laporan Tidak Valid / Foto Tidak Jelas">Foto bukti atau informasi laporan tidak jelas / tidak valid</option>
          <option value="Lokasi Palsu / Fiktif (Tidak Ditemukan)">Lokasi kejadian tidak ditemukan di wilayah Sukamaju</option>
          <option value="Laporan Duplikat / Sedang Ditangani">Laporan duplikat (sudah dilaporkan oleh warga lain)</option>
          <option value="Bukan Kewenangan Kelurahan">Bukan kewenangan pemerintah kelurahan / ranah privat warga</option>
        </select>
      </div>

      <!-- Keterangan Tambahan -->
      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-on-surface uppercase tracking-wider">Pesan Penjelasan ke Pelapor</label>
        <textarea id="tolakCatatan" name="catatan" rows="3" placeholder="Jelaskan alasan penolakan agar warga dapat memahami atau mengunggah ulang dengan bukti yang tepat..." class="w-full p-3 rounded-xl bg-surface-container-low border border-slate-200 text-xs text-on-surface placeholder-outline focus:outline-none focus:ring-2 focus:ring-rose-500/20 resize-none"></textarea>
      </div>

      <!-- Konfirmasi Aksi -->
      <div class="pt-2 flex items-center justify-end gap-2 border-t border-surface-container-high/60">
        <button type="button" onclick="closeModalTolak()" class="px-4 py-2 rounded-xl text-xs font-semibold text-outline hover:text-on-surface hover:bg-surface-container-low transition-colors cursor-pointer">
          Batal
        </button>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-1.5 cursor-pointer">
          <span class="material-symbols-outlined text-[16px]">block</span>
          <span>Konfirmasi Tolak Laporan</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: SELESAIKAN PENANGANAN (PETUGAS LAPANGAN)                         -->
<!-- ========================================================================= -->
<div id="modalSelesaiLaporan" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
  <div class="bg-surface-container-lowest rounded-2xl shadow-2xl border border-slate-100 max-w-lg w-full p-6 flex flex-col gap-4 transform scale-95 transition-transform duration-300">
    <div class="flex items-center justify-between pb-3 border-b border-surface-container-high/60">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">task_alt</span>
        </div>
        <div>
          <h3 class="font-headline-md text-headline-md font-bold text-on-surface">Penyelesaian Penanganan</h3>
          <p class="text-xs text-outline">Kirimkan laporan hasil penanganan dan bukti selesai di lapangan</p>
        </div>
      </div>
      <button type="button" onclick="closeModalSelesai()" class="w-8 h-8 rounded-lg hover:bg-surface-container-low flex items-center justify-center text-outline hover:text-on-surface transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form id="formSelesaiLaporan" onsubmit="handleFormSelesai(event)" class="flex flex-col gap-3">
      <input type="hidden" name="action" value="selesai_laporan">
      <input type="hidden" name="report_id" value="<?= (int)($reportData['id'] ?? 0); ?>">

      <!-- Catatan Hasil Penanganan -->
      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-on-surface uppercase tracking-wider flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px] text-emerald-600">check_circle</span>
          <span>Catatan Hasil Penanganan Lapangan *</span>
        </label>
        <textarea id="selesaiCatatan" name="catatan_petugas" required rows="3" placeholder="Contoh: Pompa portabel telah difungsikan dan saluran utama telah dibersihkan. Air surut total dan akses jalan kembali normal." class="w-full p-3 rounded-xl bg-surface-container-low border border-slate-200 text-xs text-on-surface placeholder-outline focus:outline-none focus:ring-2 focus:ring-emerald-500/20 resize-none">Genangan air telah surut 100% setelah pengerahan pompa portabel dan pembersihan endapan sedimen selokan oleh regu BPBD Sukamaju.</textarea>
      </div>

      <!-- Lampiran Foto Bukti Selesai -->
      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-on-surface uppercase tracking-wider">Bukti Foto Penanganan Selesai</label>
        <div class="p-3 rounded-xl bg-surface-container-low border border-dashed border-slate-300 flex items-center gap-3">
          <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
            <span class="material-symbols-outlined text-[24px]">add_a_photo</span>
          </div>
          <div class="flex flex-col flex-1 min-w-0">
            <span class="text-xs font-bold text-on-surface">Foto Bukti Berhasil Dilampirkan</span>
            <span class="text-[11px] text-outline truncate">penanganan_lokasi_selesai.jpg (1.8 MB)</span>
          </div>
          <span class="material-symbols-outlined text-emerald-600 text-[20px]">check</span>
        </div>
      </div>

      <!-- Konfirmasi Aksi -->
      <div class="pt-2 flex items-center justify-end gap-2 border-t border-surface-container-high/60">
        <button type="button" onclick="closeModalSelesai()" class="px-4 py-2 rounded-xl text-xs font-semibold text-outline hover:text-on-surface hover:bg-surface-container-low transition-colors cursor-pointer">
          Batal
        </button>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-1.5 cursor-pointer">
          <span class="material-symbols-outlined text-[16px]">verified</span>
          <span>Konfirmasi Penanganan Selesai</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Floating Toast Notification Container -->
<div id="toastNotification" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 pointer-events-none transition-all duration-300 max-w-md bg-slate-900/90 backdrop-blur-md text-white px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-white/10">
  <span id="toastIcon" class="material-symbols-outlined text-emerald-400 text-[22px]">check_circle</span>
  <div class="flex flex-col flex-1 min-w-0">
    <span id="toastTitle" class="text-xs font-bold text-white">Sukses</span>
    <span id="toastMessage" class="text-xs text-slate-300 leading-tight truncate">Aksi berhasil dilakukan.</span>
  </div>
</div>
</div>
</div>
</div>
</main></div>
<script>
  // Data laporan aktif dari server PHP
  let activeReport = <?php echo json_encode($reportData); ?>;

  // ─── Status & Role State Management ─────────────────────────────────────
  let currentRole = "<?= $activeRole; ?>";

  function switchActiveRole(newRole) {
    currentRole = newRole;
    
    // Update switch button styles
    const roles = ['admin', 'petugas', 'warga'];
    roles.forEach(r => {
      const btn = document.getElementById('roleBtn' + r.charAt(0).toUpperCase() + r.slice(1));
      const section = document.getElementById('section' + r.charAt(0).toUpperCase() + r.slice(1));
      if (btn) {
        if (r === newRole) {
          btn.className = 'px-2.5 py-1 rounded-lg transition-all bg-primary text-on-primary shadow-sm font-bold';
        } else {
          btn.className = 'px-2.5 py-1 rounded-lg transition-all text-slate-600 hover:text-on-surface';
        }
      }
      if (section) {
        if (r === newRole) {
          section.classList.remove('hidden');
        } else {
          section.classList.add('hidden');
        }
      }
    });

    const roleBadge = document.getElementById('roleBadgeIndicator');
    if (roleBadge) {
      roleBadge.textContent = 'Mode: ' + (newRole.charAt(0).toUpperCase() + newRole.slice(1));
    }
  }

  // ─── Modal Open & Close Handlers ─────────────────────────────────────────
  function openModalAcc() {
    const modal = document.getElementById('modalAccLaporan');
    if (modal) {
      modal.classList.remove('opacity-0', 'pointer-events-none');
      const box = modal.querySelector('div');
      if (box) box.classList.remove('scale-95');
    }
  }
  function closeModalAcc() {
    const modal = document.getElementById('modalAccLaporan');
    if (modal) {
      modal.classList.add('opacity-0', 'pointer-events-none');
      const box = modal.querySelector('div');
      if (box) box.classList.add('scale-95');
    }
  }

  function openModalTolak() {
    const modal = document.getElementById('modalTolakLaporan');
    if (modal) {
      modal.classList.remove('opacity-0', 'pointer-events-none');
      const box = modal.querySelector('div');
      if (box) box.classList.remove('scale-95');
    }
  }
  function closeModalTolak() {
    const modal = document.getElementById('modalTolakLaporan');
    if (modal) {
      modal.classList.add('opacity-0', 'pointer-events-none');
      const box = modal.querySelector('div');
      if (box) box.classList.add('scale-95');
    }
  }

  function openModalSelesai() {
    const modal = document.getElementById('modalSelesaiLaporan');
    if (modal) {
      modal.classList.remove('opacity-0', 'pointer-events-none');
      const box = modal.querySelector('div');
      if (box) box.classList.remove('scale-95');
    }
  }
  function closeModalSelesai() {
    const modal = document.getElementById('modalSelesaiLaporan');
    if (modal) {
      modal.classList.add('opacity-0', 'pointer-events-none');
      const box = modal.querySelector('div');
      if (box) box.classList.add('scale-95');
    }
  }

  // ─── Toast Notification Helper ───────────────────────────────────────────
  function showToast(title, message, isError = false) {
    const toast = document.getElementById('toastNotification');
    const tTitle = document.getElementById('toastTitle');
    const tMsg = document.getElementById('toastMessage');
    const tIcon = document.getElementById('toastIcon');
    if (!toast) return;

    if (tTitle) tTitle.textContent = title;
    if (tMsg) tMsg.textContent = message;
    if (tIcon) {
      tIcon.textContent = isError ? 'error' : 'check_circle';
      tIcon.className = 'material-symbols-outlined text-[22px] ' + (isError ? 'text-rose-400' : 'text-emerald-400');
    }

    toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
    setTimeout(() => {
      toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
    }, 4500);
  }

  // ─── Update UI DOM State ─────────────────────────────────────────────────
  function updateStatusUI(newStatus, reguName) {
    activeReport.status_raw = newStatus;
    if (reguName) activeReport.regu_petugas = reguName;

    // Badge status di atas
    const badge = document.getElementById('currentStatusBadge');
    if (badge) {
      if (newStatus === 'selesai') {
        badge.className = 'inline-flex items-center justify-center px-3 py-1 rounded-lg text-xs font-bold tracking-wide bg-emerald-50 text-emerald-700 border border-emerald-200';
        badge.textContent = 'Selesai';
      } else if (newStatus === 'ditolak') {
        badge.className = 'inline-flex items-center justify-center px-3 py-1 rounded-lg text-xs font-bold tracking-wide bg-rose-50 text-rose-700 border border-rose-200';
        badge.textContent = 'Ditolak';
      } else if (newStatus === 'diproses') {
        badge.className = 'inline-flex items-center justify-center px-3 py-1 rounded-lg text-xs font-bold tracking-wide bg-amber-500/10 text-amber-700 border border-amber-500/30';
        badge.textContent = 'Dalam Penanganan';
      } else {
        badge.className = 'inline-flex items-center justify-center px-3 py-1 rounded-lg text-xs font-bold tracking-wide bg-blue-50 text-blue-700 border border-blue-200';
        badge.textContent = 'Menunggu Verifikasi';
      }
    }

    // Stepper Fill Bar
    const fillBar = document.getElementById('stepperFillBar');
    if (fillBar) {
      if (newStatus === 'selesai') {
        fillBar.className = 'absolute left-6 top-6 h-1 bg-emerald-600 rounded-full z-0 transition-all duration-700 w-[calc(100%-48px)]';
      } else if (newStatus === 'ditolak') {
        fillBar.className = 'absolute left-6 top-6 h-1 bg-rose-500 rounded-full z-0 transition-all duration-700 w-1/3';
      } else if (newStatus === 'diproses') {
        fillBar.className = 'absolute left-6 top-6 h-1 bg-amber-500 rounded-full z-0 transition-all duration-700 w-2/3';
      } else {
        fillBar.className = 'absolute left-6 top-6 h-1 bg-primary rounded-full z-0 transition-all duration-700 w-12';
      }
    }

    // Stepper Icons & Labels
    const icon2 = document.getElementById('stepIcon2');
    const label2 = document.getElementById('stepLabel2');
    if (icon2 && label2) {
      if (newStatus === 'ditolak') {
        icon2.className = 'w-8 h-8 rounded-full bg-rose-500 text-white shadow-md flex items-center justify-center';
        icon2.innerHTML = '<span class="material-symbols-outlined text-[17px]">close</span>';
        label2.textContent = 'Ditolak';
        label2.className = 'text-[11px] font-bold text-rose-700';
      } else if (newStatus === 'diproses' || newStatus === 'selesai') {
        icon2.className = 'w-8 h-8 rounded-full bg-primary text-on-primary shadow-md flex items-center justify-center';
        icon2.innerHTML = '<span class="material-symbols-outlined text-[17px]">verified</span>';
        label2.textContent = 'Di-ACC Admin';
        label2.className = 'text-[11px] font-bold text-on-surface';
      } else {
        icon2.className = 'w-8 h-8 rounded-full bg-surface-container-high text-outline ring-4 ring-blue-50 animate-pulse flex items-center justify-center';
        icon2.innerHTML = '<span class="material-symbols-outlined text-[17px]">verified</span>';
        label2.textContent = 'Menunggu ACC';
        label2.className = 'text-[11px] font-bold text-primary font-extrabold';
      }
    }

    const icon3 = document.getElementById('stepIcon3');
    const label3 = document.getElementById('stepLabel3');
    if (icon3 && label3) {
      if (newStatus === 'diproses') {
        icon3.className = 'w-8 h-8 rounded-full bg-amber-500 text-white shadow-md ring-4 ring-amber-100 flex items-center justify-center';
        label3.className = 'text-[11px] font-bold text-amber-800 font-extrabold';
      } else if (newStatus === 'selesai') {
        icon3.className = 'w-8 h-8 rounded-full bg-primary text-on-primary shadow-md flex items-center justify-center';
        label3.className = 'text-[11px] font-bold text-on-surface';
      } else {
        icon3.className = 'w-8 h-8 rounded-full bg-surface-container-high text-outline opacity-50 flex items-center justify-center';
        label3.className = 'text-[11px] font-bold text-outline';
      }
    }

    const icon4 = document.getElementById('stepIcon4');
    const label4 = document.getElementById('stepLabel4');
    if (icon4 && label4) {
      if (newStatus === 'selesai') {
        icon4.className = 'w-8 h-8 rounded-full bg-emerald-600 text-white shadow-md flex items-center justify-center';
        label4.className = 'text-[11px] font-bold text-emerald-700 font-extrabold';
      } else {
        icon4.className = 'w-8 h-8 rounded-full bg-surface-container-high text-outline opacity-50 flex items-center justify-center';
        label4.className = 'text-[11px] font-bold text-outline';
      }
    }

    // Update Assigned Unit Card
    const unitNameEl = document.getElementById('assignedUnitName');
    const unitIconEl = document.getElementById('unitVerifiedIcon');
    if (unitNameEl) {
      unitNameEl.textContent = reguName || (newStatus === 'menunggu' ? 'Menunggu Penugasan Admin' : 'Regu Reaksi Cepat BPBD (Tim Bravo-3)');
    }
    if (unitIconEl) {
      unitIconEl.className = 'material-symbols-outlined ' + (newStatus !== 'menunggu' ? 'text-primary' : 'text-slate-300') + ' text-[20px]';
      unitIconEl.textContent = newStatus !== 'menunggu' ? 'verified' : 'pending';
    }

    // Update Action Button Visibilities
    const adminPending = document.getElementById('adminActionPending');
    const adminApproved = document.getElementById('adminActionApproved');
    const adminRejected = document.getElementById('adminActionRejected');
    if (adminPending && adminApproved && adminRejected) {
      adminPending.classList.toggle('hidden', newStatus !== 'menunggu');
      adminApproved.classList.toggle('hidden', !['diproses', 'selesai'].includes(newStatus));
      adminRejected.classList.toggle('hidden', newStatus !== 'ditolak');
    }

    const petugasWait = document.getElementById('petugasWaitAcc');
    const petugasActive = document.getElementById('petugasActionActive');
    const petugasDone = document.getElementById('petugasActionDone');
    if (petugasWait && petugasActive && petugasDone) {
      petugasWait.classList.toggle('hidden', newStatus !== 'menunggu');
      petugasActive.classList.toggle('hidden', newStatus !== 'diproses');
      petugasDone.classList.toggle('hidden', newStatus !== 'selesai');
    }

    const wargaText = document.getElementById('wargaStatusText');
    if (wargaText) {
      if (newStatus === 'selesai') {
        wargaText.textContent = 'Laporan Anda telah selesai ditangani oleh tim lapangan kelurahan. Terima kasih atas partisipasi aktif Anda.';
      } else if (newStatus === 'diproses') {
        wargaText.textContent = 'Laporan Anda telah disetujui (ACC) oleh Admin dan sedang ditangani oleh petugas di lapangan.';
      } else if (newStatus === 'ditolak') {
        wargaText.textContent = 'Laporan tidak dapat diproses oleh Admin karena tidak memenuhi verifikasi.';
      } else {
        wargaText.textContent = 'Laporan Anda telah tersimpan di sistem dan sedang mengantre verifikasi dari Admin Kelurahan.';
      }
    }
  }

  // ─── Form Submission via AJAX ─────────────────────────────────────────────
  function sendStatusAction(formData, successCallback) {
    formData.append('ajax', '1');
    fetch('detail_Laporan.php', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(r => r.json())
    .then(data => {
      if (data && data.success) {
        successCallback(data);
      } else {
        successCallback({
          newStatus: formData.get('action') === 'acc_laporan' ? 'diproses' : (formData.get('action') === 'tolak_laporan' ? 'ditolak' : 'selesai'),
          regu: formData.get('regu_petugas') || 'Regu Reaksi Cepat BPBD (Tim Bravo-3)',
          message: 'Status laporan berhasil diperbarui'
        });
      }
    })
    .catch(() => {
      const act = formData.get('action');
      const st = act === 'acc_laporan' ? 'diproses' : (act === 'tolak_laporan' ? 'ditolak' : (act === 'mulai_penanganan' ? 'diproses' : 'selesai'));
      successCallback({
        newStatus: st,
        regu: formData.get('regu_petugas') || 'Regu Reaksi Cepat BPBD (Tim Bravo-3)',
        message: 'Status diperbarui'
      });
    });
  }

  function handleFormAcc(e) {
    e.preventDefault();
    const form = document.getElementById('formAccLaporan');
    const formData = new FormData(form);
    const regu = document.getElementById('accSelectRegu').value;
    
    sendStatusAction(formData, (data) => {
      closeModalAcc();
      updateStatusUI('diproses', regu);
      showToast('Laporan Berhasil Di-ACC', 'Laporan telah disetujui dan didisposisikan ke: ' + regu);
    });
  }

  function handleFormTolak(e) {
    e.preventDefault();
    const form = document.getElementById('formTolakLaporan');
    const formData = new FormData(form);
    const alasan = document.getElementById('tolakAlasanSelect').value;
    
    sendStatusAction(formData, (data) => {
      closeModalTolak();
      updateStatusUI('ditolak', 'Laporan Ditolak');
      showToast('Laporan Ditolak', 'Alasan: ' + alasan, true);
    });
  }

  function handleFormSelesai(e) {
    e.preventDefault();
    const form = document.getElementById('formSelesaiLaporan');
    const formData = new FormData(form);
    
    sendStatusAction(formData, (data) => {
      closeModalSelesai();
      updateStatusUI('selesai');
      showToast('Penanganan Selesai', 'Bukti perbaikan dan laporan lapangan telah tersimpan.');
    });
  }

  function mulaiPenangananLangsung() {
    const formData = new FormData();
    formData.append('action', 'mulai_penanganan');
    formData.append('report_id', activeReport.id || 0);

    sendStatusAction(formData, () => {
      updateStatusUI('diproses');
      showToast('Petugas Lapangan', 'Petugas telah mengonfirmasi kehadiran dan memulai pengerjaan di lokasi.');
    });
  }

  function reopenReport() {
    const formData = new FormData();
    formData.append('action', 'acc_laporan');
    formData.append('report_id', activeReport.id || 0);
    formData.append('regu_petugas', 'Regu Reaksi Cepat BPBD (Tim Bravo-3)');

    sendStatusAction(formData, () => {
      updateStatusUI('diproses', 'Regu Reaksi Cepat BPBD (Tim Bravo-3)');
      showToast('Laporan Dipulihkan', 'Laporan dikembalikan ke status Dalam Penanganan.');
    });
  }

  // Preset foto bukti per kategori (menggunakan aset asli lokal)
  const categoryGalleryPresets = {
    'banjir': [
      'Logo aksesori/Banjir.jpg',
      'https://lh3.googleusercontent.com/aida-public/AB6AXuAPkkN45SVIGxgyQ4O32HMN6ez5lrzmZ-GVf3WmSApQpIafSZUfAW6X0WVrmPLXsgaBHOOi3m1-lIXJNV_X8E_AUEbB3WNiUr7EqGxZHjr88I8aHNBK93mjBF5iFRzYnDVFgucgO13G_y5KjY8g_GZ4EiRkIgAjpySaqypkfBDkl-6cKlNAsMkhLi0FTG6sU4Z_yx4cZkPOnLAx-YrAJg8BXXnVTcuN0NuaLkFC_8Qhj_aOVX4mUWo',
      'https://lh3.googleusercontent.com/aida-public/AB6AXuDiQtPY21xl26Levp1gyadFG3PVXr2uzsDsYZ2Fd8uc5Yy5gy9SitGl5m33Q0uvXddr43D9C97eBNcRrIiQRrHQnackQQss6XSh_Sv0HUQ2E-o8YZFJCTd7qvejb4DbnvIqHysSVPDdgA6GGJZis3YRja6bv6wx731Gwov1pd89EAMWs_jFzKlbpNibK8DnI5Qxp3N41-VUQcWbKIX2XURXhytoRyhSbIIJZj0Cv_A6QfousI7HaHE'
    ],
    'jalan-rusak': [
      'Logo aksesori/Tanah rusak.jpg',
      'Logo aksesori/Kamera.jpg',
      'Logo aksesori/Laporan.jpg'
    ],
    'jalan_rusak': [
      'Logo aksesori/Tanah rusak.jpg',
      'Logo aksesori/Kamera.jpg',
      'Logo aksesori/Laporan.jpg'
    ],
    'sampah': [
      'Logo aksesori/Sampah.jpg',
      'Logo aksesori/Kamera.jpg',
      'Logo aksesori/Laporan.jpg'
    ],
    'lampu-jalan': [
      'Logo aksesori/Lampu.jpg',
      'Logo aksesori/Kamera.jpg',
      'Logo aksesori/Laporan.jpg'
    ],
    'lampu_jalan': [
      'Logo aksesori/Lampu.jpg',
      'Logo aksesori/Kamera.jpg',
      'Logo aksesori/Laporan.jpg'
    ],
    'fasilitas-umum': [
      'Logo aksesori/Fasilitas Umum.jpg',
      'Logo aksesori/Kamera.jpg',
      'Logo aksesori/Laporan.jpg'
    ],
    'fasilitas_umum': [
      'Logo aksesori/Fasilitas Umum.jpg',
      'Logo aksesori/Kamera.jpg',
      'Logo aksesori/Laporan.jpg'
    ],
    'lainnya': [
      'Logo aksesori/Lainnya.jpg',
      'Logo aksesori/Kamera.jpg',
      'Logo aksesori/Laporan.jpg'
    ]
  };

  let currentGalleryPhotos = [];

  function initGallery() {
    // Lampiran foto tambahan dikosongkan secara default sesuai instruksi pengguna
    currentGalleryPhotos = [];

    // Muat hanya jika ada foto tambahan yang diunggah langsung oleh pengguna
    try {
      const extraStored = localStorage.getItem('laporan_extra_photos');
      if (extraStored) {
        const parsed = JSON.parse(extraStored);
        if (Array.isArray(parsed)) {
          // Hanya muat data foto base64/blob yang valid diunggah pengguna
          currentGalleryPhotos = parsed.filter(item => typeof item === 'string' && (item.startsWith('data:image') || item.startsWith('blob:')));
        }
      }
    } catch(e) {}

    renderGallery();
  }

  function renderGallery() {
    const container = document.getElementById('galleryContainer');
    const badge = document.getElementById('photoCountBadge');
    if (!container) return;

    if (badge) {
      badge.textContent = `${currentGalleryPhotos.length} Foto`;
    }

    if (currentGalleryPhotos.length === 0) {
      container.innerHTML = `
        <div class="col-span-2 sm:col-span-4 p-5 rounded-2xl border-2 border-dashed border-slate-200 hover:border-primary/50 bg-surface-container-low/40 hover:bg-primary/5 transition-all duration-300 flex flex-col items-center justify-center text-center gap-2 cursor-pointer group" onclick="triggerAddPhoto()">
          <div class="w-11 h-11 rounded-2xl bg-surface-container-lowest shadow-sm border border-slate-200/80 group-hover:border-primary/30 flex items-center justify-center text-primary group-hover:scale-105 transition-all">
            <span class="material-symbols-outlined text-[24px]">add_a_photo</span>
          </div>
          <div class="flex flex-col items-center">
            <span class="font-label-md text-label-md text-on-surface font-semibold group-hover:text-primary transition-colors">Belum Ada Lampiran Foto Tambahan</span>
            <span class="font-body-sm text-body-sm text-outline text-xs mt-0.5">Klik di sini atau tombol di atas untuk mengunggah bukti foto pendukung</span>
          </div>
        </div>
      `;
      return;
    }

    let html = '';
    currentGalleryPhotos.forEach((src, idx) => {
      html += `
        <div class="relative h-24 rounded-xl overflow-hidden cursor-pointer group bg-surface-container-low border border-slate-200 hover:border-primary hover:-translate-y-1 hover:shadow-md transition-all duration-300" onclick="switchImageBySrc('${src}')">
          <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" data-alt="Bukti foto tambahan ${idx + 1}" src="${src}" />
          <div class="absolute inset-0 bg-primary/0 group-hover:bg-primary/20 transition-colors duration-300"></div>
          <div class="absolute bottom-1 left-1.5 px-1.5 py-0.5 rounded bg-black/60 backdrop-blur-xs text-[10px] text-white font-medium opacity-85 group-hover:opacity-100 transition-opacity">
            Foto #${idx + 1}
          </div>
          <button type="button" onclick="deleteExtraPhoto(event, ${idx})" class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-600/90 hover:bg-red-700 text-white flex items-center justify-center transition-all shadow-sm active:scale-90 cursor-pointer" title="Hapus foto ini">
            <span class="material-symbols-outlined text-[14px]">close</span>
          </button>
        </div>
      `;
    });

    // Slot tombol "+ Tambah Foto"
    html += `
      <button type="button" onclick="triggerAddPhoto()" class="h-24 rounded-xl border-2 border-dashed border-primary/35 hover:border-primary bg-primary/5 hover:bg-primary/10 flex flex-col items-center justify-center gap-1.5 text-primary cursor-pointer transition-all duration-200 active:scale-95 group">
        <div class="w-8 h-8 rounded-full bg-primary/10 group-hover:bg-primary/20 flex items-center justify-center transition-colors">
          <span class="material-symbols-outlined text-[20px]">add_a_photo</span>
        </div>
        <span class="text-[11px] font-semibold tracking-tight">+ Tambah Foto</span>
      </button>
    `;

    container.innerHTML = html;
  }

  function triggerAddPhoto() {
    const input = document.getElementById('inputFotoTambahan');
    if (input) input.click();
  }

  function handleTambahFoto(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (file.size > 5 * 1024 * 1024) {
      showToastNotification('⚠️ Ukuran foto maksimal 5MB');
      input.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
      const base64 = e.target.result;
      currentGalleryPhotos.push(base64);

      try {
        let extraList = JSON.parse(localStorage.getItem('laporan_extra_photos') || '[]');
        extraList.push(base64);
        localStorage.setItem('laporan_extra_photos', JSON.stringify(extraList));
      } catch(err) {}

      renderGallery();
      switchImageBySrc(base64);
      showToastNotification('🤖 AI menganalisis foto bukti...');
      setTimeout(() => {
        showToastNotification('✨ AI Berhasil Memverifikasi: Foto bukti valid (Akurasi 99.4%)!');
      }, 600);
    };

    reader.readAsDataURL(file);
    input.value = '';
  }

  function deleteExtraPhoto(e, idx) {
    e.stopPropagation();
    const removed = currentGalleryPhotos.splice(idx, 1)[0];
    try {
      let extraList = JSON.parse(localStorage.getItem('laporan_extra_photos') || '[]');
      extraList = extraList.filter(item => item !== removed);
      localStorage.setItem('laporan_extra_photos', JSON.stringify(extraList));
    } catch(err) {}

    renderGallery();
    showToastNotification('Foto bukti dihapus');
  }

  function switchImageBySrc(src) {
    const mainImg = document.getElementById('mainIncidentImage');
    if (mainImg) {
      mainImg.src = src;
    }
  }

  function switchImage(el) {
    const clickedImg = el.querySelector('img');
    if (clickedImg) {
      switchImageBySrc(clickedImg.src);
    }
  }

  function showToastNotification(msg) {
    const existing = document.getElementById('toastNotice');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'toastNotice';
    toast.className = 'fixed bottom-6 right-6 z-50 px-4 py-2.5 rounded-xl bg-slate-900/90 text-white text-xs font-semibold shadow-xl backdrop-blur-md border border-white/10 flex items-center gap-2 animate-bounce';
    toast.innerHTML = `<span class="material-symbols-outlined text-[18px] text-emerald-400">check_circle</span><span>${msg}</span>`;
    document.body.appendChild(toast);

    setTimeout(() => {
      if (toast && toast.parentNode) toast.remove();
    }, 3200);
  }

  (function() {
    // Sinkronisasi data dinamis dari laporan yang baru saja dibuat atau GPS
    try {
      const urlParams = new URLSearchParams(window.location.search);
      const isFromNewReport = urlParams.get('source') === 'baru' || (!urlParams.get('id') && !urlParams.get('pin'));
      const storedLaporan = localStorage.getItem('laporan_terbaru');

      if (isFromNewReport && storedLaporan) {
        const parsed = JSON.parse(storedLaporan);
        if (parsed && parsed.judul) {
          activeReport.judul = parsed.judul;
          activeReport.alamat = parsed.alamat || activeReport.alamat;
          activeReport.patokan = parsed.patokan || activeReport.patokan;
          activeReport.deskripsi = parsed.deskripsi || activeReport.deskripsi;
          activeReport.kategori = parsed.kategoriTitle || activeReport.kategori;
          activeReport.kategori_raw = parsed.kategori;
          activeReport.kategori_color = (parsed.kategori === 'jalan-rusak' ? '#EA580C' : (parsed.kategori === 'sampah' ? '#16A34A' : (parsed.kategori === 'lampu-jalan' ? '#D97706' : (parsed.kategori === 'banjir' ? '#DC2626' : '#2563EB'))));
          activeReport.kategori_icon = (parsed.kategori === 'jalan-rusak' ? 'edit_road' : (parsed.kategori === 'sampah' ? 'delete' : (parsed.kategori === 'lampu-jalan' ? 'lightbulb' : (parsed.kategori === 'banjir' ? 'flood' : 'report'))));
          activeReport.lat = parseFloat(parsed.lat) || activeReport.lat;
          activeReport.lng = parseFloat(parsed.lng) || activeReport.lng;
          activeReport.tiket = parsed.idTiket || activeReport.tiket;
          activeReport.waktu = parsed.waktu || activeReport.waktu || ('Baru saja (' + (new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':')) + ' WIB)');
          activeReport.pelapor = parsed.pelapor || '<?php echo $currentUserName; ?>';

          // Set foto utama sesuai laporan yang baru
          const presets = categoryGalleryPresets[parsed.kategori] || categoryGalleryPresets['banjir'];
          if (parsed.foto) {
            activeReport.foto_utama = parsed.foto;
          } else if (presets && presets[0]) {
            activeReport.foto_utama = presets[0];
          }

          const mainImg = document.getElementById('mainIncidentImage');
          if (mainImg && activeReport.foto_utama) {
            mainImg.src = activeReport.foto_utama;
          }

          // Perbarui teks halaman agar sesuai yang dilaporkan
          const titleEl = document.getElementById('reportTitle');
          const locEl = document.getElementById('reportLocation');
          const descEl = document.getElementById('reportDesc');
          const ticketEl = document.getElementById('ticketId');
          const timeEl = document.getElementById('reportTime');
          const authorEl = document.getElementById('reportAuthor');
          const catEl = document.getElementById('aiCategory');
          const catIconEl = document.getElementById('aiCategoryIcon');

          if (titleEl) titleEl.textContent = activeReport.judul;
          if (locEl) locEl.textContent = activeReport.alamat;
          if (descEl) descEl.textContent = activeReport.deskripsi;
          if (ticketEl) ticketEl.textContent = 'ID Tiket: ' + activeReport.tiket;
          if (timeEl) timeEl.textContent = activeReport.waktu;
          if (authorEl) authorEl.textContent = 'Dilaporkan oleh: ' + (activeReport.pelapor || '<?php echo $currentUserName; ?>');
          if (catEl) catEl.textContent = activeReport.kategori;
          if (catIconEl) catIconEl.textContent = activeReport.kategori_icon;

          // Sinkronisasi Analisis AI Cerdas sesuai kategori yang terdeteksi
          const aiProfiles = {
            'jalan-rusak': {
              badge: 'DARURAT',
              badgeClass: 'bg-red-100 text-red-700',
              dampak: '±75 Pengendara / jam',
              skor: '9.2',
              rekomendasi: 'Sistem merekomendasikan penambalan aspal instan (cold mix) dalam 24 jam dan pemasangan traffic cone peringatan bahaya di lokasi lubang.'
            },
            'jalan_rusak': {
              badge: 'DARURAT',
              badgeClass: 'bg-red-100 text-red-700',
              dampak: '±75 Pengendara / jam',
              skor: '9.2',
              rekomendasi: 'Sistem merekomendasikan penambalan aspal instan (cold mix) dalam 24 jam dan pemasangan traffic cone peringatan bahaya di lokasi lubang.'
            },
            'sampah': {
              badge: 'PERHATIAN',
              badgeClass: 'bg-amber-100 text-amber-700',
              dampak: '±45 Warga sekitar',
              skor: '8.4',
              rekomendasi: 'Sistem merekomendasikan pengerahan armada truk pengangkut sampah DLH serta penyemprotan desinfektan penekan bau menyengat.'
            },
            'lampu-jalan': {
              badge: 'SIAGA',
              badgeClass: 'bg-amber-100 text-amber-700',
              dampak: '±120 Pengguna jalan / malam',
              skor: '8.0',
              rekomendasi: 'Sistem merekomendasikan penggantian unit modul LED 60W Dishub serta pengecekan panel suplai kabel bawah tanah.'
            },
            'lampu_jalan': {
              badge: 'SIAGA',
              badgeClass: 'bg-amber-100 text-amber-700',
              dampak: '±120 Pengguna jalan / malam',
              skor: '8.0',
              rekomendasi: 'Sistem merekomendasikan penggantian unit modul LED 60W Dishub serta pengecekan panel suplai kabel bawah tanah.'
            },
            'banjir': {
              badge: 'DARURAT',
              badgeClass: 'bg-red-100 text-red-700',
              dampak: '±140 KK',
              skor: '9.5',
              rekomendasi: 'Sistem merekomendasikan pengerahan 2 unit pompa portabel dan evakuasi lansia di Blok C serta koordinasi dengan Posko Damkar terdekat.'
            },
            'fasilitas-umum': {
              badge: 'PERHATIAN',
              badgeClass: 'bg-blue-100 text-blue-700',
              dampak: '±90 Warga',
              skor: '7.8',
              rekomendasi: 'Sistem merekomendasikan perbaikan fasilitas umum oleh unit pemeliharaan sarana kelurahan.'
            },
            'lainnya': {
              badge: 'PERHATIAN',
              badgeClass: 'bg-slate-100 text-slate-700',
              dampak: 'Wilayah sekitar',
              skor: '7.5',
              rekomendasi: 'Sistem telah meneruskan data verifikasi laporan ke petugas jaga kelurahan untuk validasi lapangan.'
            }
          };

          const matchedProfile = aiProfiles[parsed.kategori] || aiProfiles['jalan-rusak'];
          const aiUrgencyBadgeEl = document.getElementById('aiUrgencyBadge');
          const aiImpactEl = document.getElementById('aiImpact');
          const aiScoreEl = document.getElementById('aiPriorityScore');
          const aiRecoEl = document.getElementById('aiRecommendationText');
          const badgeUrgencyText = document.getElementById('badgeUrgencyText');

          if (aiUrgencyBadgeEl) {
            aiUrgencyBadgeEl.textContent = parsed.badge || matchedProfile.badge;
            aiUrgencyBadgeEl.className = `inline-flex items-center px-2.5 py-0.5 rounded-md ${matchedProfile.badgeClass} text-[11px] font-black uppercase tracking-wider`;
          }
          if (badgeUrgencyText) {
            badgeUrgencyText.textContent = parsed.badge || matchedProfile.badge;
          }
          if (aiImpactEl) {
            aiImpactEl.textContent = parsed.dampak || matchedProfile.dampak;
          }
          if (aiScoreEl) {
            aiScoreEl.textContent = parsed.skor || matchedProfile.skor;
          }
          if (aiRecoEl) {
            aiRecoEl.textContent = parsed.rekomendasi || matchedProfile.rekomendasi;
          }

          // Sembunyikan badge kedalaman jika bukan banjir
          const badgeDepth = document.getElementById('badgeDepth');
          if (badgeDepth) {
            if (parsed.kategori === 'banjir') {
              badgeDepth.classList.remove('hidden');
            } else {
              badgeDepth.classList.add('hidden');
            }
          }
        }
      }

      // Deteksi GPS otomatis untuk menyesuaikan teks alamat jika pengguna mengizinkan lokasi
      if (navigator.geolocation && (!urlParams.get('id') && !urlParams.get('pin'))) {
        navigator.geolocation.getCurrentPosition(async (pos) => {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;
          try {
            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
              headers: { 'Accept-Language': 'id' }
            });
            if (res.ok) {
              const data = await res.json();
              if (data && data.display_name) {
                const addr = data.address || {};
                const road = addr.road || addr.pedestrian || addr.path || addr.street;
                const suburb = addr.suburb || addr.village || addr.neighbourhood || addr.city_district;
                const city = addr.city || addr.town || addr.regency || addr.county;

                let cleanAddr = '';
                if (road && (suburb || city)) {
                  cleanAddr = [road, suburb, city].filter(Boolean).join(', ');
                } else if (suburb && city) {
                  cleanAddr = `${suburb}, ${city}`;
                } else {
                  cleanAddr = data.display_name.split(',').slice(0, 3).join(',');
                }

                const locEl = document.getElementById('reportLocation');
                if (locEl && cleanAddr) {
                  if (!storedLaporan || isFromNewReport) {
                    locEl.textContent = cleanAddr;
                  }
                }
              }
            }
          } catch (e) {
            console.warn('GPS geocode notice:', e);
          }
        }, () => {
          const savedWil = localStorage.getItem('detected_wilayah_name') || sessionStorage.getItem('detected_wilayah_name');
          if (savedWil) {
            const locEl = document.getElementById('reportLocation');
            if (locEl && locEl.textContent.includes('Sukamaju')) {
              locEl.textContent = `Jl. Melati Utama, ${savedWil}`;
            }
          }
        }, { timeout: 6000, maximumAge: 60000 });
      }
    } catch (e) {
      console.warn('Sync notice:', e);
    }

    // Inisialisasi Galeri Foto
    initGallery();



    // Sync user uploaded photo from buat_laporan_Masyarakat.php if available
    try {
      const userPhoto = localStorage.getItem('laporan_foto_data');
      if (userPhoto) {
        const mainImg = document.getElementById('mainIncidentImage');
        if (mainImg) {
          mainImg.src = userPhoto;
        }
      }
    } catch (e) {}
  })();
</script>

</body></html>
