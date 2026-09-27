<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/nav_sidebar.php';
requireLogin();

// Ambil data user yang sedang login langsung dari database
$currentUser = getFullUserProfile();
if (!$currentUser) {
    header('Location: logout.php');
    exit;
}

// Tangani aksi update profil (POST)
$flash = getFlash();
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    $nama    = clean($_POST['nama_lengkap'] ?? '');
    $no_hp   = clean($_POST['no_hp'] ?? '');
    $nik     = clean($_POST['nik'] ?? '');
    $wilayah = clean($_POST['wilayah'] ?? '');

    if (empty($nama)) {
        setFlash('error', 'Nama lengkap wajib diisi.');
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE users SET nama_lengkap = ?, no_hp = ?, nik = ?, wilayah = ? WHERE id = ?");
            $stmt->execute([$nama, $no_hp ?: null, $nik ?: null, $wilayah ?: 'Sukamaju', $currentUser['id']]);
            $_SESSION['user_nama'] = $nama;
            setFlash('success', 'Profil kependudukan berhasil diperbarui di database!');
            header('Location: Profile.php');
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Gagal menyimpan perubahan: ' . $e->getMessage());
            header('Location: Profile.php');
            exit;
        }
    }
    header('Location: Profile.php');
    exit;
}

// Tangani aksi update lokasi via GPS (AJAX POST)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'update_gps') {
    header('Content-Type: application/json');
    $newWilayah = clean($_POST['wilayah'] ?? '');
    if (!empty($newWilayah)) {
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE users SET wilayah = ? WHERE id = ?");
            $stmt->execute([$newWilayah, $currentUser['id']]);
            echo json_encode(['success' => true, 'wilayah' => $newWilayah]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Data wilayah tidak boleh kosong.']);
    exit;
}

$userName = $currentUser['nama_lengkap'];
$userRole = ucfirst($currentUser['role'] ?? 'warga');
$email = $currentUser['email'] ?? '';
$phone = $currentUser['no_hp'] ?: '';
$wilayah = $currentUser['wilayah'] ?: 'Kelurahan Sukamaju';
$rawNik = $currentUser['nik'] ?: '';

// Masking NIK untuk privasi
if (!empty($rawNik)) {
    $nikMasked = strlen($rawNik) > 4 ? substr($rawNik, 0, 4) . str_repeat('*', max(0, strlen($rawNik) - 4)) : $rawNik;
} else {
    $nikMasked = 'Belum diisi';
}

$isVerified = (bool)($currentUser['is_verified'] ?? 0);
$badgeStatus = $isVerified ? 'Terverifikasi Dukcapil' : 'Warga Terdaftar';

// Format tanggal bergabung dalam Bahasa Indonesia
$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$createdTimestamp = strtotime($currentUser['created_at'] ?? 'now');
$bln = $bulanIndo[(int)date('n', $createdTimestamp)] ?? date('F', $createdTimestamp);
$thn = date('Y', $createdTimestamp);
$bergabungText = "Bergabung: {$bln} {$thn}";

// Hitung statistik laporan nyata dari database
$db = getDB();
$stmtTotal = $db->prepare("SELECT COUNT(*) FROM laporan WHERE user_id = ?");
$stmtTotal->execute([$currentUser['id']]);
$laporanTotal = (int)$stmtTotal->fetchColumn();

$stmtSelesai = $db->prepare("SELECT COUNT(*) FROM laporan WHERE user_id = ? AND status = 'selesai'");
$stmtSelesai->execute([$currentUser['id']]);
$laporanSelesai = (int)$stmtSelesai->fetchColumn();

$stmtProses = $db->prepare("SELECT COUNT(*) FROM laporan WHERE user_id = ? AND status IN ('diproses', 'menunggu')");
$stmtProses->execute([$currentUser['id']]);
$laporanProses = (int)$stmtProses->fetchColumn();

// Ambil riwayat laporan milik pengguna ini
$stmtLaporan = $db->prepare("SELECT * FROM laporan WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmtLaporan->execute([$currentUser['id']]);
$userReports = $stmtLaporan->fetchAll();

$unreadCount = getUnreadNotificationCount();

function getCategoryIcon(string $kat): string {
    return match($kat) {
        'jalan_rusak'    => 'construction',
        'sampah'         => 'delete_outline',
        'banjir'         => 'water_damage',
        'lampu_jalan'    => 'lightbulb',
        'fasilitas_umum' => 'location_city',
        default          => 'report_problem'
    };
}

function getCategoryBg(string $kat): string {
    return match($kat) {
        'jalan_rusak'    => 'bg-amber-100 text-amber-700',
        'sampah'         => 'bg-emerald-100 text-emerald-700',
        'banjir'         => 'bg-blue-100 text-blue-700',
        'lampu_jalan'    => 'bg-yellow-100 text-yellow-700',
        'fasilitas_umum' => 'bg-indigo-100 text-indigo-700',
        default          => 'bg-primary/10 text-primary'
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta content="web_dashboard" name="shell-type"/>
  <title>Profil Saya - Teman Warga AI</title>
  <link rel="icon" type="image/svg+xml" href="logo.php"/>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
  <style>
    @layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}main>:first-child{margin-top:0!important;}main>:last-child{margin-bottom:0!important;}}
    ::-webkit-scrollbar{display:none;}
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(16px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
      animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
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

</style>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script id="tailwind-config">
    tailwind.config={
      darkMode:"class",
      theme:{
        extend:{
          colors:{
            "surface-container-low":"#eff4ff",
            "surface-container-lowest":"#ffffff",
            "outline":"#737686",
            "on-background":"#0b1c30",
            "outline-variant":"#c3c6d7",
            "surface-container-highest":"#d3e4fe",
            "surface-dim":"#cbdbf5",
            "on-primary":"#ffffff",
            "error-container":"#ffdad6",
            "surface-container":"#e5eeff",
            "tertiary":"#005b7c",
            "surface":"#f8f9ff",
            "secondary-fixed-dim":"#93ccff",
            "primary-fixed-dim":"#b4c5ff",
            "on-secondary-fixed":"#001d31",
            "on-surface-variant":"#434655",
            "on-primary-fixed-variant":"#003ea8",
            "secondary-fixed":"#cce5ff",
            "surface-variant":"#d3e4fe",
            "on-surface":"#0b1c30",
            "primary":"#004ac6",
            "secondary":"#006398",
            "secondary-container":"#5bb8fe",
            "primary-container":"#2563eb",
            "primary-fixed":"#dbe1ff",
            "on-tertiary":"#ffffff",
            "background":"#f8f9ff",
            "on-tertiary-fixed-variant":"#004c69",
            "on-primary-fixed":"#00174b",
            "tertiary-fixed-dim":"#7bd0ff",
            "on-secondary-fixed-variant":"#004b73",
            "surface-bright":"#f8f9ff",
            "inverse-surface":"#213145",
            "on-error-container":"#93000a",
            "surface-container-high":"#dce9ff",
            "on-tertiary-container":"#e1f2ff",
            "surface-tint":"#0053db",
            "on-primary-container":"#eeefff",
            "on-tertiary-fixed":"#001e2c",
            "tertiary-container":"#00759f",
            "inverse-on-surface":"#eaf1ff",
            "on-secondary":"#ffffff",
            "on-secondary-container":"#00476e",
            "inverse-primary":"#b4c5ff",
            "tertiary-fixed":"#c4e7ff",
            "error":"#ba1a1a",
            "on-error":"#ffffff"
          },
          borderRadius:{"DEFAULT":"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px"},
          spacing:{"space-md":"1rem","space-lg":"1.5rem","space-sm":"0.5rem","space-xs":"0.25rem","gutter":"1.25rem","margin":"2rem","space-xl":"2rem"},
          fontFamily:{
            "headline-md":["Outfit","sans-serif"],"label-sm":["Outfit","sans-serif"],"body-md":["Outfit","sans-serif"],
            "headline-lg":["Outfit","sans-serif"],"body-sm":["Outfit","sans-serif"],"headline-xl":["Outfit","sans-serif"],
            "body-lg":["Outfit","sans-serif"],"display-lg":["Outfit","sans-serif"],"label-lg":["Outfit","sans-serif"],
            "label-md":["Outfit","sans-serif"]
          },
          fontSize:{
            "headline-md":["18px",{"lineHeight":"26px","fontWeight":"600"}],
            "label-sm":["11px",{"lineHeight":"14px","letterSpacing":"0.02em","fontWeight":"600"}],
            "body-md":["14px",{"lineHeight":"22px","fontWeight":"400"}],
            "headline-lg":["22px",{"lineHeight":"30px","letterSpacing":"-0.01em","fontWeight":"700"}],
            "body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],
            "headline-xl":["28px",{"lineHeight":"36px","letterSpacing":"-0.015em","fontWeight":"700"}],
            "body-lg":["16px",{"lineHeight":"24px","fontWeight":"400"}],
            "display-lg":["40px",{"lineHeight":"48px","letterSpacing":"-0.02em","fontWeight":"800"}],
            "label-lg":["14px",{"lineHeight":"20px","fontWeight":"600"}],
            "label-md":["12px",{"lineHeight":"16px","letterSpacing":"0.01em","fontWeight":"600"}]
          }
        }
      }
    };
  </script>
</head>
<body class="bg-gradient-to-b from-surface-container-lowest to-surface font-body-md text-body-md text-on-surface min-h-screen">
  <!-- Top Navigation Header -->
  <header class="fixed top-0 left-0 right-0 h-16 z-50 bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] px-gutter flex items-center justify-between gap-space-lg">
    <?php renderHeaderBrand(); ?>
    <div class="flex-1 max-w-xl mx-auto">
      <div class="relative flex items-center w-full">
        <span class="material-symbols-outlined absolute left-space-md text-outline text-[20px] pointer-events-none">search</span>
        <input class="w-full pl-11 pr-space-md py-2 bg-surface-container-low rounded-xl text-on-surface placeholder-outline font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/30 transition-all shadow-none" placeholder="Cari bantuan, laporan, atau informasi..." type="text"/>
      </div>
    </div>
    <div class="flex items-center gap-space-md shrink-0">
      <a href="notifikasi.php" aria-label="Notifikasi" class="relative w-10 h-10 rounded-xl bg-surface-container-low hover:bg-surface-container active:scale-95 flex items-center justify-center text-on-surface-variant transition-all">
        <span class="material-symbols-outlined text-[22px]">notifications</span>
        <?php if ($unreadCount > 0): ?>
        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-error ring-2 ring-surface-container-lowest"></span>
        <?php endif; ?>
      </a>
      <a href="Profile.php" class="flex items-center gap-space-sm pl-space-sm py-1 pr-space-sm rounded-xl bg-surface-container-low transition-all">
        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center font-bold text-white text-xs uppercase">
          <?php echo htmlspecialchars(mb_substr($userName, 0, 1)); ?>
        </div>
        <div class="hidden md:flex flex-col text-left">
          <span class="font-label-md text-label-md text-on-surface"><?php echo htmlspecialchars($userName); ?></span>
          <span class="font-label-sm text-label-sm text-outline"><?php echo htmlspecialchars($userRole); ?></span>
        </div>
      </a>
    </div>
  </header>

  <!-- Left Sidebar Navigation Sesuai Peran -->
  <?php renderSidebarNav('profil'); ?>

  <!-- Main Content -->
  <div class="pl-64">
    <main class="w-full pt-16 px-margin py-margin">
      <div class="flex flex-col w-full gap-space-lg max-w-5xl pb-10">
        
        <?php if ($flash): ?>
        <div class="p-4 rounded-xl flex items-center gap-3 border <?php echo ($flash['type'] === 'success') ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200'; ?> animate-fade-in-up">
          <span class="material-symbols-outlined text-[20px]"><?php echo ($flash['type'] === 'success') ? 'check_circle' : 'error'; ?></span>
          <span class="font-body-md"><?php echo htmlspecialchars($flash['message']); ?></span>
        </div>
        <?php endif; ?>

        <!-- Profile Banner Card -->
        <div class="bg-surface-container-lowest rounded-2xl p-space-lg shadow-sm border border-outline-variant/20 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 animate-fade-in-up">
          <div class="flex items-center gap-5">
            <div class="relative">
              <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-primary to-secondary-container flex items-center justify-center text-on-primary shadow-lg shadow-primary/20 font-bold text-3xl uppercase">
                <?php echo htmlspecialchars(mb_substr($userName, 0, 1)); ?>
              </div>
              <?php if ($isVerified): ?>
              <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-white" title="Akun Terverifikasi">
                <span class="material-symbols-outlined text-[14px]">check</span>
              </span>
              <?php endif; ?>
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-headline-xl font-headline-xl text-on-surface font-bold"><?php echo htmlspecialchars($userName); ?></h1>
                <span class="<?php echo $isVerified ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-primary/10 text-primary border-primary/20'; ?> font-label-sm text-label-sm px-2.5 py-0.5 rounded-full font-semibold border">
                  <?php echo htmlspecialchars($badgeStatus); ?>
                </span>
              </div>
              <div class="mt-1 flex items-center gap-2 flex-wrap">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-gradient-to-r from-sky-50 via-blue-50 to-indigo-50 border border-blue-200/70 shadow-2xs">
                  <div class="w-6 h-6 rounded-lg bg-white/90 p-0.5 shadow-2xs ring-1 ring-blue-500/20 flex items-center justify-center shrink-0 overflow-hidden">
                    <img src="Logo%20aksesori/Maps.jpg" alt="Logo GPS" class="w-full h-full object-contain mix-blend-multiply drop-shadow-2xs" />
                  </div>
                  <span id="profileWilayahDisplay" class="font-body-md text-body-md text-on-surface font-semibold"><?php echo htmlspecialchars($wilayah); ?></span>
                </div>
                <button id="btnSyncGPS" onclick="syncGPSProfile()" type="button" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-primary/10 hover:bg-primary/20 text-primary text-xs font-semibold border border-primary/25 transition-all active:scale-95 cursor-pointer shadow-2xs group" title="Sesuaikan lokasi profil Anda otomatis dengan GPS">
                  <span id="gpsSyncIcon" class="material-symbols-outlined text-[16px] group-hover:rotate-45 transition-transform text-primary">my_location</span>
                  <span id="gpsSyncText">Sesuaikan dengan GPS</span>
                </button>
              </div>
              <div class="flex items-center gap-3 mt-2 text-label-sm font-label-sm text-outline flex-wrap">
                <span>NIK: <?php echo htmlspecialchars($nikMasked); ?></span>
                <span>&bull;</span>
                <span><?php echo htmlspecialchars($bergabungText); ?></span>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-2.5 flex-wrap">
            <button onclick="openEditProfileModal()" type="button" class="px-4 py-2 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface font-label-lg text-label-lg transition-all flex items-center gap-1.5 active:scale-95 border border-outline-variant/30 cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">edit</span>
              <span>Edit Profil</span>
            </button>
            <a href="logout.php" class="px-4 py-2 rounded-xl border border-error/30 text-error hover:bg-red-50 text-label-lg font-label-lg transition-all flex items-center gap-1.5 active:scale-95 shadow-2xs">
              <span class="material-symbols-outlined text-[18px]">logout</span>
              <span>Keluar</span>
            </a>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="bg-surface-container-lowest rounded-xl p-5 shadow-sm border border-outline-variant/10 flex items-center gap-4 group hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="w-12 h-12 rounded-xl bg-blue-50/80 p-2 shadow-xs border border-blue-100 flex items-center justify-center shrink-0 overflow-hidden group-hover:scale-105 transition-transform">
              <img src="Logo%20aksesori/Document.jpg" alt="Total Laporan Dikirim" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs" />
            </div>
            <div>
              <span class="text-headline-lg font-bold text-on-surface"><?php echo $laporanTotal; ?></span>
              <p class="text-label-sm text-outline">Total Laporan Dikirim</p>
            </div>
          </div>
          <div class="bg-surface-container-lowest rounded-xl p-5 shadow-sm border border-outline-variant/10 flex items-center gap-4 group hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="w-12 h-12 rounded-xl bg-emerald-50/80 p-2 shadow-xs border border-emerald-100 flex items-center justify-center shrink-0 overflow-hidden group-hover:scale-105 transition-transform">
              <img src="Logo%20aksesori/Ceklis%20(2).jpg" alt="Laporan Selesai" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs" />
            </div>
            <div>
              <span class="text-headline-lg font-bold text-on-surface"><?php echo $laporanSelesai; ?></span>
              <p class="text-label-sm text-outline">Laporan Selesai</p>
            </div>
          </div>
          <div class="bg-surface-container-lowest rounded-xl p-5 shadow-sm border border-outline-variant/10 flex items-center gap-4 group hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="w-12 h-12 rounded-xl bg-amber-50/80 p-2 shadow-xs border border-amber-100 flex items-center justify-center shrink-0 overflow-hidden group-hover:scale-105 transition-transform">
              <img src="Logo%20aksesori/Timer.jpg" alt="Sedang Ditangani" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs" />
            </div>
            <div>
              <span class="text-headline-lg font-bold text-on-surface"><?php echo $laporanProses; ?></span>
              <p class="text-label-sm text-outline">Sedang Ditangani</p>
            </div>
          </div>
        </div>

        <!-- Detail Form & Settings -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-space-lg">
          <!-- Information Card -->
          <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm border border-outline-variant/10 flex flex-col gap-4">
            <div class="flex items-center justify-between">
              <h2 class="text-headline-md font-headline-md text-on-surface font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">badge</span>
                <span>Informasi Kependudukan</span>
              </h2>
              <button onclick="openEditProfileModal()" type="button" class="text-primary hover:text-primary-container text-label-md font-semibold inline-flex items-center gap-1 hover:underline cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">edit</span>
                <span>Ubah</span>
              </button>
            </div>
            <div class="space-y-3">
              <div>
                <label class="text-label-sm text-outline block mb-1">Nama Lengkap</label>
                <input type="text" readonly value="<?php echo htmlspecialchars($userName); ?>" class="w-full bg-surface-container-low rounded-xl px-4 py-2.5 text-on-surface font-body-md border-0 focus:ring-0">
              </div>
              <div>
                <label class="text-label-sm text-outline block mb-1">Nomor Telepon / WhatsApp</label>
                <input type="text" readonly value="<?php echo htmlspecialchars($phone ?: 'Belum diatur'); ?>" class="w-full bg-surface-container-low rounded-xl px-4 py-2.5 text-on-surface font-body-md border-0 focus:ring-0">
              </div>
              <div>
                <label class="text-label-sm text-outline block mb-1">Email</label>
                <input type="email" readonly value="<?php echo htmlspecialchars($email); ?>" class="w-full bg-surface-container-low rounded-xl px-4 py-2.5 text-on-surface font-body-md border-0 focus:ring-0">
              </div>
              <div>
                <label class="text-label-sm text-outline block mb-1">Alamat Domisili / Wilayah</label>
                <textarea readonly rows="2" class="w-full bg-surface-container-low rounded-xl px-4 py-2.5 text-on-surface font-body-md border-0 focus:ring-0 resize-none"><?php echo htmlspecialchars($wilayah); ?></textarea>
              </div>
            </div>
          </div>

          <!-- History & Quick Actions -->
          <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm border border-outline-variant/10 flex flex-col gap-4">
            <h2 class="text-headline-md font-headline-md text-on-surface font-semibold flex items-center gap-2">
              <span class="material-symbols-outlined text-primary">history</span>
              <span>Riwayat Terkini Laporan Anda</span>
            </h2>

            <?php if (empty($userReports)): ?>
            <div class="py-8 px-4 rounded-xl bg-surface-container-low/50 border border-outline-variant/20 flex flex-col items-center justify-center text-center gap-2 my-auto">
              <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-1">
                <span class="material-symbols-outlined text-[24px]">assignment_turned_in</span>
              </div>
              <h4 class="font-label-md font-semibold text-on-surface">Belum Ada Riwayat Laporan</h4>
              <p class="text-body-sm text-outline max-w-xs">Anda belum pernah membuat laporan aduan publik. Laporkan jika ada permasalahan lingkungan di sekitar Anda.</p>
              <a href="buat_laporan_Masyarakat.php" class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-primary text-on-primary rounded-xl text-label-sm font-semibold hover:bg-primary/95 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[16px]">add</span>
                <span>Buat Laporan Baru</span>
              </a>
            </div>
            <?php else: ?>
            <div class="flex flex-col gap-3">
              <?php foreach ($userReports as $rep): 
                $kat = $rep['kategori'] ?? 'lainnya';
                $status = strtolower($rep['status'] ?? 'menunggu');
                $badgeClass = match($status) {
                  'selesai'  => 'text-emerald-600 bg-emerald-50 border border-emerald-200',
                  'diproses' => 'text-blue-600 bg-blue-50 border border-blue-200',
                  'menunggu' => 'text-amber-600 bg-amber-50 border border-amber-200',
                  'ditolak'  => 'text-rose-600 bg-rose-50 border border-rose-200',
                  default    => 'text-outline bg-surface-container'
                };
                $statusLabel = match($status) {
                  'selesai'  => 'Selesai',
                  'diproses' => 'Diproses',
                  'menunggu' => 'Menunggu',
                  'ditolak'  => 'Ditolak',
                  default    => ucfirst($status)
                };
                $icon = getCategoryIcon($kat);
                $catColor = getCategoryBg($kat);
                $tglLapor = date('d F Y', strtotime($rep['created_at']));
                foreach ($bulanIndo as $mNum => $mName) {
                  $tglLapor = str_replace(date('F', mktime(0, 0, 0, $mNum, 10)), $mName, $tglLapor);
                }
              ?>
              <a href="detail_Laporan.php?id=<?php echo (int)$rep['id']; ?>" class="p-3 rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex items-center justify-between group">
                <div class="flex items-center gap-3 min-w-0">
                  <div class="w-8 h-8 rounded-lg <?php echo $catColor; ?> flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]"><?php echo $icon; ?></span>
                  </div>
                  <div class="min-w-0">
                    <h4 class="font-label-md font-semibold text-on-surface group-hover:text-primary transition-colors truncate"><?php echo htmlspecialchars($rep['judul']); ?></h4>
                    <p class="text-label-sm text-outline truncate"><?php echo $tglLapor; ?> &bull; <?php echo htmlspecialchars($rep['alamat'] ?? $wilayah); ?></p>
                  </div>
                </div>
                <span class="text-label-sm font-semibold px-2.5 py-1 rounded-full shrink-0 <?php echo $badgeClass; ?>"><?php echo $statusLabel; ?></span>
              </a>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="mt-auto pt-4 border-t border-surface-container flex items-center justify-end">
              <a href="peta_laporan.php" class="text-primary hover:underline font-label-md font-semibold">
                Lihat di Peta &rarr;
              </a>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>

  <!-- Modal Edit Profil Interaktif -->
  <div id="modalEditProfile" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-surface-container-lowest rounded-2xl max-w-md w-full p-6 shadow-xl border border-outline-variant/30 animate-fade-in-up">
      <div class="flex items-center justify-between pb-3 border-b border-surface-container mb-4">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[22px]">manage_accounts</span>
          <h3 class="font-headline-md text-headline-md text-on-surface font-bold">Ubah Profil Kependudukan</h3>
        </div>
        <button onclick="closeEditProfileModal()" type="button" class="w-8 h-8 rounded-lg text-outline hover:bg-surface-container flex items-center justify-center cursor-pointer">
          <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
      </div>

      <form action="Profile.php" method="POST" class="space-y-4">
        <input type="hidden" name="action" value="update_profile">
        <div>
          <label class="text-label-sm text-outline block mb-1 font-semibold">Nama Lengkap</label>
          <input type="text" name="nama_lengkap" required value="<?php echo htmlspecialchars($userName); ?>" class="w-full bg-surface-container-low rounded-xl px-4 py-2.5 text-on-surface font-body-md border border-outline-variant/30 focus:border-primary focus:bg-surface-container-lowest focus:outline-none transition-all">
        </div>
        <div>
          <label class="text-label-sm text-outline block mb-1 font-semibold">Nomor WhatsApp / HP</label>
          <input type="text" name="no_hp" placeholder="Contoh: 081234567890" value="<?php echo htmlspecialchars($phone); ?>" class="w-full bg-surface-container-low rounded-xl px-4 py-2.5 text-on-surface font-body-md border border-outline-variant/30 focus:border-primary focus:bg-surface-container-lowest focus:outline-none transition-all">
        </div>
        <div>
          <label class="text-label-sm text-outline block mb-1 font-semibold">Nomor Induk Kependudukan (NIK)</label>
          <input type="text" name="nik" maxlength="16" placeholder="16 digit NIK sesuai KTP" value="<?php echo htmlspecialchars($rawNik); ?>" class="w-full bg-surface-container-low rounded-xl px-4 py-2.5 text-on-surface font-body-md border border-outline-variant/30 focus:border-primary focus:bg-surface-container-lowest focus:outline-none transition-all">
        </div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="text-label-sm text-outline font-semibold">Wilayah / Alamat Domisili</label>
            <button type="button" onclick="detectGPSForModal()" class="text-xs text-primary hover:text-primary-container font-semibold inline-flex items-center gap-1 cursor-pointer">
              <span class="material-symbols-outlined text-[15px]">my_location</span>
              <span id="modalGpsBtnText">Deteksi GPS</span>
            </button>
          </div>
          <div class="relative flex items-center">
            <div class="absolute left-3 w-6 h-6 rounded-lg bg-white p-0.5 shadow-2xs ring-1 ring-blue-500/20 flex items-center justify-center overflow-hidden shrink-0 pointer-events-none z-10">
              <img src="Logo%20aksesori/Maps.jpg" alt="GPS" class="w-full h-full object-contain mix-blend-multiply" />
            </div>
            <input type="text" id="modalWilayahInput" name="wilayah" placeholder="Contoh: RT 04 / RW 07, Kelurahan Sukamaju" value="<?php echo htmlspecialchars($wilayah); ?>" class="w-full pl-11 bg-surface-container-low rounded-xl px-4 py-2.5 text-on-surface font-body-md border border-outline-variant/30 focus:border-primary focus:bg-surface-container-lowest focus:outline-none transition-all">
          </div>
        </div>

        <div class="pt-2 flex items-center justify-end gap-2.5">
          <button type="button" onclick="closeEditProfileModal()" class="px-4 py-2 rounded-xl text-on-surface-variant hover:bg-surface-container text-label-md font-semibold transition-all cursor-pointer">Batal</button>
          <button type="submit" class="px-5 py-2 rounded-xl bg-primary text-on-primary hover:bg-primary/95 text-label-md font-semibold shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">save</span>
            <span>Simpan Perubahan</span>
          </button>
        </div>
      </form>
    </div>
  </div>


<script>
  function openEditProfileModal() {
    const modal = document.getElementById('modalEditProfile');
    if (modal) {
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeEditProfileModal() {
    const modal = document.getElementById('modalEditProfile');
    if (modal) {
      modal.classList.add('hidden');
      document.body.style.overflow = '';
    }
  }

  // Tutup modal jika klik di luar area modal atau tekan Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeEditProfileModal();
  });
  const editModal = document.getElementById('modalEditProfile');
  if (editModal) {
    editModal.addEventListener('click', (e) => {
      if (e.target === editModal) closeEditProfileModal();
    });
  }

  // Geolocation & Reverse Geocode Helper for Profile
  async function reverseGeocodeGPS(lat, lng) {
    try {
      const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
        headers: { 'Accept-Language': 'id' }
      });
      if (res.ok) {
        const data = await res.json();
        if (data && data.address) {
          const addr = data.address;
          const village = addr.village || addr.suburb || addr.neighbourhood || addr.city_district || "";
          const city = addr.city || addr.regency || addr.town || addr.municipality || "";
          if (village && city) {
            return `Kelurahan ${village}, ${city}`;
          } else if (village || city) {
            return village || city;
          }
          return data.display_name.split(',').slice(0, 3).join(',').trim();
        }
      }
    } catch (err) {
      console.warn("Geocoding notice:", err);
    }
    return null;
  }

  function syncGPSProfile() {
    const btn = document.getElementById('btnSyncGPS');
    const icon = document.getElementById('gpsSyncIcon');
    const text = document.getElementById('gpsSyncText');
    const display = document.getElementById('profileWilayahDisplay');

    if (!navigator.geolocation) {
      alert("Browser Anda tidak mendukung deteksi lokasi GPS.");
      return;
    }

    if (text) text.textContent = "Mendeteksi GPS...";
    if (icon) icon.className = "material-symbols-outlined text-[16px] animate-spin text-primary";
    if (btn) btn.disabled = true;

    navigator.geolocation.getCurrentPosition(
      async (pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        const detectedName = await reverseGeocodeGPS(lat, lng) || `Titik GPS (${lat.toFixed(4)}, ${lng.toFixed(4)})`;

        // Update database via AJAX
        try {
          const formData = new FormData();
          formData.append('action', 'update_gps');
          formData.append('wilayah', detectedName);

          const res = await fetch('Profile.php', {
            method: 'POST',
            body: formData
          });
          const json = await res.json();

          if (json.success) {
            if (display) display.textContent = detectedName;
            const modalInput = document.getElementById('modalWilayahInput');
            if (modalInput) modalInput.value = detectedName;

            if (text) text.textContent = "GPS Sesuai!";
            if (icon) icon.className = "material-symbols-outlined text-[16px] text-emerald-600";
            setTimeout(() => {
              if (text) text.textContent = "Sesuaikan dengan GPS";
              if (icon) icon.className = "material-symbols-outlined text-[16px] text-primary";
              if (btn) btn.disabled = false;
            }, 3000);
          } else {
            throw new Error(json.message || "Gagal menyimpan");
          }
        } catch (err) {
          alert("Gagal memperbarui wilayah profil: " + err.message);
          if (text) text.textContent = "Coba Lagi";
          if (icon) icon.className = "material-symbols-outlined text-[16px] text-primary";
          if (btn) btn.disabled = false;
        }
      },
      (err) => {
        let msg = "Gagal mengambil koordinat GPS.";
        if (err.code === 1) msg = "Izin akses lokasi GPS ditolak oleh browser. Silakan aktifkan izin lokasi di browser Anda.";
        else if (err.code === 2) msg = "Sinyal GPS tidak tersedia saat ini.";
        else if (err.code === 3) msg = "Waktu pencarian GPS habis.";
        alert(msg);
        if (text) text.textContent = "Sesuaikan dengan GPS";
        if (icon) icon.className = "material-symbols-outlined text-[16px] text-primary";
        if (btn) btn.disabled = false;
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
  }

  function detectGPSForModal() {
    const btnText = document.getElementById('modalGpsBtnText');
    const input = document.getElementById('modalWilayahInput');

    if (!navigator.geolocation) {
      alert("Browser Anda tidak mendukung deteksi lokasi GPS.");
      return;
    }

    if (btnText) btnText.textContent = "Mencari...";

    navigator.geolocation.getCurrentPosition(
      async (pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        const detectedName = await reverseGeocodeGPS(lat, lng) || `Titik GPS (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
        if (input) input.value = detectedName;
        if (btnText) btnText.textContent = "Terdeteksi!";
        setTimeout(() => {
          if (btnText) btnText.textContent = "Deteksi GPS";
        }, 2500);
      },
      (err) => {
        alert("Gagal mendeteksi lokasi GPS: " + err.message);
        if (btnText) btnText.textContent = "Deteksi GPS";
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
  }
</script>

</body>
</html>
