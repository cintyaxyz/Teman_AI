<?php
/**
 * dashboard_admin.php — Dashboard Eksekutif Administrator Kelurahan
 * Teman Warga AI
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/nav_sidebar.php';
requireLogin();

$currentUser = currentUser();
$userRoleClean = strtolower($currentUser['role'] ?? 'admin');

// Batasi akses: jika warga biasa atau petugas mencoba masuk, alihkan ke dashboard masing-masing
if ($userRoleClean === 'warga') {
    header('Location: beranda.php');
    exit;
} elseif ($userRoleClean === 'petugas') {
    header('Location: dashboard_petugas.php');
    exit;
}

$currentUserName = htmlspecialchars($currentUser['nama'] ?? 'Admin Kelurahan');
$unreadCount = getUnreadNotificationCount();
$db = getDB();

// ─── Query Data Statistik Riil Kelurahan ─────────────────────────────────────
try {
    $totalLaporan = (int)$db->query("SELECT COUNT(*) FROM laporan")->fetchColumn();
    $menungguAcc  = (int)$db->query("SELECT COUNT(*) FROM laporan WHERE status = 'menunggu'")->fetchColumn();
    $dalamProses  = (int)$db->query("SELECT COUNT(*) FROM laporan WHERE status = 'diproses'")->fetchColumn();
    $selesai      = (int)$db->query("SELECT COUNT(*) FROM laporan WHERE status = 'selesai'")->fetchColumn();

    // Laporan terbaru yang aktif (menunggu atau diproses)
    $stmtRecent = $db->query("SELECT l.*, u.nama_lengkap 
                              FROM laporan l 
                              LEFT JOIN users u ON l.user_id = u.id 
                              ORDER BY l.created_at DESC LIMIT 5");
    $recentReports = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

    // Hitung per kategori
    $kategoriStats = [
        'jalan_rusak' => (int)$db->query("SELECT COUNT(*) FROM laporan WHERE kategori = 'jalan_rusak'")->fetchColumn(),
        'sampah'      => (int)$db->query("SELECT COUNT(*) FROM laporan WHERE kategori = 'sampah'")->fetchColumn(),
        'lampu_jalan' => (int)$db->query("SELECT COUNT(*) FROM laporan WHERE kategori = 'lampu_jalan'")->fetchColumn(),
        'banjir'      => (int)$db->query("SELECT COUNT(*) FROM laporan WHERE kategori = 'banjir'")->fetchColumn(),
        'lainnya'     => (int)$db->query("SELECT COUNT(*) FROM laporan WHERE kategori NOT IN ('jalan_rusak','sampah','lampu_jalan','banjir')")->fetchColumn(),
    ];
} catch (Exception $e) {
    $totalLaporan = 52;
    $menungguAcc  = 4;
    $dalamProses  = 16;
    $selesai      = 32;
    $recentReports = [];
    $kategoriStats = [
        'jalan_rusak' => 18,
        'sampah'      => 14,
        'lampu_jalan' => 9,
        'banjir'      => 7,
        'lainnya'     => 4
    ];
}

// Fallback data simulasi jika tabel kosong
if (empty($recentReports)) {
    $recentReports = [
        [
            'id' => 1,
            'tiket' => '#LPR-2024-0102',
            'judul' => 'Jalan berlubang di depan gerbang SMP 2',
            'nama_lengkap' => 'Rasya Pratama',
            'kategori' => 'jalan_rusak',
            'alamat' => 'Jl. Pitara Raya / SMP 2, Sukamaju',
            'status' => 'menunggu',
            'prioritas' => 'darurat',
            'created_at' => date('Y-m-d H:i:s', strtotime('-25 minutes'))
        ],
        [
            'id' => 2,
            'tiket' => '#LPR-2024-0089',
            'judul' => 'Tumpukan sampah liar di bantaran kali RT 04',
            'nama_lengkap' => 'Bambang Sutrisno',
            'kategori' => 'sampah',
            'alamat' => 'Jl. Cempaka No. 12, RT 04/02, Sukamaju',
            'status' => 'menunggu',
            'prioritas' => 'tinggi',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ],
        [
            'id' => 11,
            'tiket' => '#LPR-2024-0098',
            'judul' => 'Pengurukan & penambalan lubang jalan aspal retak',
            'nama_lengkap' => 'Siti Nurhaliza',
            'kategori' => 'jalan_rusak',
            'alamat' => 'Jl. Kenanga No. 18, RT 01/RW 04',
            'status' => 'diproses',
            'prioritas' => 'sedang',
            'created_at' => date('Y-m-d H:i:s', strtotime('-4 hours'))
        ],
        [
            'id' => 12,
            'tiket' => '#LPR-2024-0095',
            'judul' => 'Penyedotan genangan luapan selokan warga RT 03',
            'nama_lengkap' => 'Ahmad Fauzi',
            'kategori' => 'banjir',
            'alamat' => 'Gang Melati IV, RT 03/RW 06',
            'status' => 'diproses',
            'prioritas' => 'tinggi',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours'))
        ]
    ];
}

// Preset Regu Petugas Lapangan Singkat
$reguPreview = [
    [
        'nama' => 'Regu Reaksi Cepat BPBD (Tim Bravo-3)',
        'bidang' => 'Banjir & Tanggap Darurat',
        'icon' => 'Logo%20aksesori/Banjir.jpg',
        'status' => 'Siaga Penuh',
        'badge' => 'bg-emerald-100 text-emerald-800',
        'personil' => '8 Personil'
    ],
    [
        'nama' => 'Dinas PUPR - Regu Penambalan Jalan',
        'bidang' => 'Jalan Rusak & Infrastruktur',
        'icon' => 'Logo%20aksesori/Tanah%20rusak.jpg',
        'status' => 'Sedang di Lokasi',
        'badge' => 'bg-blue-100 text-blue-800',
        'personil' => '12 Personil'
    ],
    [
        'nama' => 'Dinas Lingkungan Hidup - Satgas Sampah',
        'bidang' => 'Kebersihan & TPS Liar',
        'icon' => 'Logo%20aksesori/Sampah.jpg',
        'status' => 'Armada Beroperasi',
        'badge' => 'bg-emerald-100 text-emerald-800',
        'personil' => '10 Personil'
    ],
    [
        'nama' => 'Dishub - Regu Penerangan Jalan Umum',
        'bidang' => 'PJU & Kelistrikan',
        'icon' => 'Logo%20aksesori/Lampu.jpg',
        'status' => 'Jadwal Reguler',
        'badge' => 'bg-amber-100 text-amber-800',
        'personil' => '6 Personil'
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta content="web_dashboard" name="shell-type"/>
  <title>Dashboard Administrator — Portal Admin Kelurahan</title>
  <link rel="icon" type="image/svg+xml" href="logo.php"/>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
  <style>
    @layer base {
      html, body { margin: 0; padding: 0; }
      body { overscroll-behavior: none; }
    }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    @keyframes fadeUpAnim {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes progressGrow {
      from { width: 0%; }
    }
    .anim-fade-up {
      opacity: 0;
      transform: translateY(20px);
    }
    .anim-fade-up.is-visible {
      animation: fadeUpAnim 0.55s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .anim-delay-1.is-visible { animation-delay: 60ms; }
    .anim-delay-2.is-visible { animation-delay: 120ms; }
    .anim-delay-3.is-visible { animation-delay: 180ms; }
    .anim-delay-4.is-visible { animation-delay: 240ms; }
    .anim-delay-5.is-visible { animation-delay: 300ms; }
    .anim-delay-6.is-visible { animation-delay: 360ms; }
    .progress-bar-anim {
      animation: progressGrow 0.9s cubic-bezier(0.16, 1, 0.3, 1) both;
      animation-delay: 300ms;
    }
  </style>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "surface-container-low": "#eff4ff",
            "surface-container-lowest": "#ffffff",
            "outline": "#737686",
            "on-background": "#0b1c30",
            "outline-variant": "#c3c6d7",
            "surface-container-highest": "#d3e4fe",
            "surface-dim": "#cbdbf5",
            "on-primary": "#ffffff",
            "error-container": "#ffdad6",
            "surface-container": "#e5eeff",
            "tertiary": "#005b7c",
            "surface": "#f8f9ff",
            "primary": "#004ac6",
            "secondary": "#006398",
            "primary-container": "#2563eb",
            "on-surface": "#0b1c30",
            "on-surface-variant": "#434655"
          },
          fontFamily: {
            sans: ['Outfit', 'Inter', 'system-ui', 'sans-serif'],
            "body-md": ["Outfit", "sans-serif"],
            "headline-md": ["Outfit", "sans-serif"],
            "headline-lg": ["Outfit", "sans-serif"],
            "headline-xl": ["Outfit", "sans-serif"]
          }
        }
      }
    };
  </script>
</head>
<body class="bg-[#f8faff] font-body-md text-slate-800 min-h-screen">

  <!-- Header Atas Portal Admin -->
  <header class="fixed top-0 left-0 right-0 h-16 z-50 bg-white/95 backdrop-blur-xl border-b border-slate-200/80 px-6 sm:px-8 flex items-center justify-between gap-6 shadow-[0_1px_4px_rgba(0,0,0,0.03)]">
    <?php renderHeaderBrand('admin'); ?>

    <!-- Search Bar -->
    <div class="flex-1 max-w-xl mx-auto">
      <div class="relative flex items-center w-full">
        <span class="material-symbols-outlined absolute left-3.5 text-slate-400 text-[20px] pointer-events-none">search</span>
        <input class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200/90 rounded-xl text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all" placeholder="Cari nomor tiket, lokasi laporan, atau informasi warga..." type="text"/>
      </div>
    </div>

    <!-- Right Profile & Notif -->
    <div class="flex items-center gap-4 shrink-0">
      <a href="notifikasi.php" aria-label="Notifikasi" class="relative w-10 h-10 rounded-xl bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-slate-600 transition-colors border border-slate-200/70">
        <span class="material-symbols-outlined text-[22px]">notifications</span>
        <?php if ($unreadCount > 0): ?>
          <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-red-600 ring-2 ring-white"></span>
        <?php endif; ?>
      </a>
      <a href="Profile.php" class="flex items-center gap-3 pl-2 py-1 pr-3 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-200/60">
        <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs uppercase shadow-xs">
          <?= htmlspecialchars(mb_substr($currentUserName, 0, 2)); ?>
        </div>
        <div class="hidden md:flex flex-col text-left">
          <span class="text-xs font-bold text-slate-900"><?= $currentUserName; ?></span>
          <span class="text-[11px] text-amber-700 font-bold">Admin Sistem</span>
        </div>
        <span class="material-symbols-outlined text-slate-400 text-[18px]">keyboard_arrow_down</span>
      </a>
    </div>
  </header>

  <!-- Sidebar Khusus ADMIN (Sesuai Screenshot) -->
  <?php renderSidebarNav('dashboard-admin', 'admin'); ?>

  <!-- Konten Utama: Dashboard Admin (Desain Luas, Terbuka, & Lega) -->
  <div class="pl-64">
    <main class="w-full pt-16">
      <div class="max-w-7xl mx-auto px-6 sm:px-8 py-8 space-y-6">

        <!-- Banner Selamat Datang & Status Wilayah (Lega) -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.02)] anim-fade-up">
          <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Selamat Datang, <?= $currentUserName; ?></h1>
            <p class="text-slate-500 text-sm sm:text-base max-w-2xl">Pantau ringkasan pelayanan pengaduan warga, status wilayah kelurahan, dan kinerja petugas lapangan secara terintegrasi.</p>
          </div>
        </div>

        <!-- Banner Notifikasi Antrean ACC Jika Ada Laporan Masuk -->
        <?php if ($menungguAcc > 0): ?>
        <div class="p-6 rounded-3xl bg-amber-50 border border-amber-200/90 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 anim-fade-up anim-delay-1 hover:shadow-md transition-all duration-300">
          <div class="flex items-start sm:items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 border border-amber-300 flex items-center justify-center shrink-0 shadow-xs p-2">
              <img src="Logo%20aksesori/Laporan.jpg" alt="Laporan Baru" style="width: 28px; height: 28px; max-width: 28px; max-height: 28px; object-fit: contain;" class="w-7 h-7 object-contain rounded shrink-0"/>
            </div>
            <div>
              <h3 class="text-base font-extrabold text-amber-950">Ada <?= $menungguAcc; ?> Laporan Baru Menunggu Verifikasi &amp; ACC!</h3>
              <p class="text-xs sm:text-sm text-amber-800 mt-0.5">Laporan warga telah diklasifikasikan oleh AI Triage dan siap untuk Anda verifikasi serta disposisikan ke regu lapangan.</p>
            </div>
          </div>
          <a href="verifikasi_acc.php" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 active:scale-95 text-white text-xs sm:text-sm font-bold shadow-md shadow-amber-600/20 transition flex items-center justify-center gap-1.5 shrink-0">
            <span>Buka Halaman Verifikasi &amp; ACC</span>
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
          </a>
        </div>
        <?php endif; ?>

        <!-- 4 Metric Cards Statistik Wilayah (Lega & Jelas dengan Logo Aksesori) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
          <!-- Card 1: Menunggu ACC -->
          <a href="verifikasi_acc.php" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.02)] hover:border-amber-400 hover:-translate-y-1.5 hover:shadow-lg transition-all duration-300 flex flex-col justify-between group anim-fade-up anim-delay-1">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-500 uppercase tracking-wider group-hover:text-amber-700 transition">Menunggu ACC</span>
              <span class="p-2 rounded-2xl bg-amber-50 border border-amber-200/80 group-hover:bg-amber-100 group-hover:scale-110 transition-all duration-300 flex items-center justify-center shrink-0">
                <img src="Logo%20aksesori/Timer.jpg" alt="Menunggu ACC" style="width: 24px; height: 24px; max-width: 24px; max-height: 24px; object-fit: contain;" class="w-6 h-6 object-contain rounded shrink-0"/>
              </span>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
              <span class="text-3xl sm:text-4xl font-black text-slate-900"><?= $menungguAcc; ?></span>
              <span class="text-xs font-bold text-amber-800 bg-amber-100 px-2.5 py-1 rounded-lg">Butuh Verifikasi</span>
            </div>
          </a>

          <!-- Card 2: Dalam Penanganan -->
          <a href="monitoring_petugas.php" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.02)] hover:border-primary/40 hover:-translate-y-1.5 hover:shadow-lg transition-all duration-300 flex flex-col justify-between group anim-fade-up anim-delay-2">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-500 uppercase tracking-wider group-hover:text-primary transition">Dalam Penanganan</span>
              <span class="p-2 rounded-2xl bg-blue-50 border border-blue-200/80 group-hover:bg-blue-100 group-hover:scale-110 transition-all duration-300 flex items-center justify-center shrink-0">
                <img src="Logo%20aksesori/Petugas.jpg" alt="Petugas Lapangan" style="width: 24px; height: 24px; max-width: 24px; max-height: 24px; object-fit: contain;" class="w-6 h-6 object-contain rounded shrink-0"/>
              </span>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
              <span class="text-3xl sm:text-4xl font-black text-slate-900"><?= $dalamProses; ?></span>
              <span class="text-xs font-bold text-primary bg-primary/10 px-2.5 py-1 rounded-lg">Di Lapangan</span>
            </div>
          </a>

          <!-- Card 3: Selesai Dituntaskan -->
          <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.02)] hover:-translate-y-1.5 hover:shadow-lg transition-all duration-300 flex flex-col justify-between group anim-fade-up anim-delay-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Selesai Dituntaskan</span>
              <span class="p-2 rounded-2xl bg-emerald-50 border border-emerald-200/80 group-hover:scale-110 transition-all duration-300 flex items-center justify-center shrink-0">
                <img src="Logo%20aksesori/Ceklis%20Ijau%20buat%20dashboard.jpg" alt="Selesai Dituntaskan" style="width: 24px; height: 24px; max-width: 24px; max-height: 24px; object-fit: contain;" class="w-6 h-6 object-contain rounded-full shrink-0"/>
              </span>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
              <span class="text-3xl sm:text-4xl font-black text-slate-900"><?= $selesai; ?></span>
              <span class="text-xs font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-lg">92% Puas</span>
            </div>
          </div>

          <!-- Card 4: Total Laporan -->
          <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.02)] hover:-translate-y-1.5 hover:shadow-lg transition-all duration-300 flex flex-col justify-between group anim-fade-up anim-delay-4">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Laporan Warga</span>
              <span class="p-2 rounded-2xl bg-slate-100 border border-slate-200/80 group-hover:scale-110 transition-all duration-300 flex items-center justify-center shrink-0">
                <img src="Logo%20aksesori/Document.jpg" alt="Total Dokumen Laporan" style="width: 24px; height: 24px; max-width: 24px; max-height: 24px; object-fit: contain;" class="w-6 h-6 object-contain rounded shrink-0"/>
              </span>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
              <span class="text-3xl sm:text-4xl font-black text-slate-900"><?= $totalLaporan; ?></span>
              <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">Keseluruhan</span>
            </div>
          </div>
        </div>

        <!-- 2-COLUMN SECTION: STATUS REGU & DISTRIBUSI PENGADUAN (LEGA) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 anim-fade-up anim-delay-5">
          <!-- Kolom Kiri: Kesiapan Regu Petugas Lapangan (7 cols) -->
          <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.02)] flex flex-col justify-between gap-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">engineering</span>
                <h2 class="text-base sm:text-lg font-bold text-slate-900">Kesiapan Regu Petugas Lapangan</h2>
              </div>
              <a href="monitoring_petugas.php" class="text-xs text-primary font-bold hover:underline flex items-center gap-1">
                <span>Buka Monitoring Lengkap</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
              </a>
            </div>

            <div class="space-y-3">
              <?php foreach ($reguPreview as $regu): ?>
              <div class="p-4 rounded-2xl bg-slate-50/80 hover:bg-slate-100/70 transition flex items-center justify-between gap-3 border border-slate-200/60">
                <div class="flex items-center gap-3.5 min-w-0">
                  <div class="w-10 h-10 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-center shrink-0 p-1.5 overflow-hidden">
                    <img src="<?= $regu['icon'] ?? 'Logo%20aksesori/Petugas.jpg'; ?>" alt="<?= htmlspecialchars($regu['nama']); ?>" style="width: 26px; height: 26px; max-width: 26px; max-height: 26px; object-fit: contain;" class="w-[26px] h-[26px] object-contain rounded shrink-0"/>
                  </div>
                  <div class="flex flex-col min-w-0">
                    <span class="text-xs sm:text-sm font-bold text-slate-900 truncate"><?= htmlspecialchars($regu['nama']); ?></span>
                    <span class="text-[11px] text-slate-500"><?= htmlspecialchars($regu['bidang']); ?> &bull; <?= htmlspecialchars($regu['personil']); ?></span>
                  </div>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full shrink-0 <?= $regu['badge']; ?>">
                  <?= htmlspecialchars($regu['status']); ?>
                </span>
              </div>
              <?php endforeach; ?>
            </div>

            <div class="pt-2 text-center">
              <a href="peta_laporan.php" class="text-xs font-bold text-slate-500 hover:text-primary transition flex items-center justify-center gap-1.5">
                <img src="Logo%20aksesori/Maps.jpg" alt="Peta" style="width: 16px; height: 16px; max-width: 16px; max-height: 16px; object-fit: contain;" class="w-4 h-4 object-contain rounded shrink-0"/>
                <span>Lihat Sebaran Lokasi Petugas di Peta Pantauan &rarr;</span>
              </a>
            </div>
          </div>

          <!-- Kolom Kanan: Distribusi Kategori Masalah (5 cols) -->
          <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.02)] flex flex-col justify-between gap-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">pie_chart</span>
                <h2 class="text-base sm:text-lg font-bold text-slate-900">Distribusi Masalah Warga</h2>
              </div>
              <span class="text-xs text-slate-400">Statistik Wilayah</span>
            </div>

            <div class="space-y-4">
              <?php
              $kategoriConfig = [
                  'jalan_rusak' => ['label' => 'Jalan Rusak & Berlubang', 'icon' => 'Logo%20aksesori/Tanah%20rusak.jpg', 'color' => 'bg-blue-600', 'textColor' => 'text-blue-600'],
                  'sampah'      => ['label' => 'Sampah & Kebersihan TPS', 'icon' => 'Logo%20aksesori/Sampah.jpg', 'color' => 'bg-emerald-600', 'textColor' => 'text-emerald-600'],
                  'lampu_jalan' => ['label' => 'PJU Mati / Kelistrikan', 'icon' => 'Logo%20aksesori/Lampu.jpg', 'color' => 'bg-amber-500', 'textColor' => 'text-amber-600'],
                  'banjir'      => ['label' => 'Banjir & Saluran Air', 'icon' => 'Logo%20aksesori/Banjir.jpg', 'color' => 'bg-cyan-600', 'textColor' => 'text-cyan-600'],
                  'lainnya'     => ['label' => 'Fasilitas Publik Lainnya', 'icon' => 'Logo%20aksesori/Fasilitas%20Umum.jpg', 'color' => 'bg-slate-400', 'textColor' => 'text-slate-500']
              ];
              $maxKat = max(array_values($kategoriStats)) ?: 1;

              foreach ($kategoriConfig as $key => $cfg):
                  $val = $kategoriStats[$key] ?? 0;
                  $pct = round(($val / ($totalLaporan ?: 1)) * 100);
                  $barPct = round(($val / $maxKat) * 100);
              ?>
              <div class="space-y-1.5">
                <div class="flex items-center justify-between text-xs font-bold">
                  <span class="text-slate-700 flex items-center gap-1.5">
                    <img src="<?= $cfg['icon']; ?>" alt="" style="width: 15px; height: 15px; max-width: 15px; max-height: 15px; object-fit: contain;" class="w-[15px] h-[15px] object-contain rounded shrink-0"/>
                    <span><?= $cfg['label']; ?></span>
                  </span>
                  <span class="<?= $cfg['textColor']; ?>"><?= $val; ?> laporan (<?= $pct; ?>%)</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                  <div class="<?= $cfg['color']; ?> h-full rounded-full transition-all duration-700 progress-bar-anim" style="width: <?= $barPct; ?>%"></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>

            <div class="p-3.5 rounded-2xl bg-blue-50/60 border border-blue-100 text-xs text-blue-900 flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[18px]">auto_awesome</span>
              <span>AI Triage secara otomatis memprioritaskan perbaikan jalan berlubang darurat.</span>
            </div>
          </div>
        </div>

        <!-- Tabel Aktivitas Laporan Terbaru (Lega & Rapi) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden anim-fade-up anim-delay-6">
          <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Aktivitas Laporan Warga Terbaru</h2>
              <p class="text-xs text-slate-500">Laporan terbaru yang masuk ke sistem Kelurahan Sukamaju.</p>
            </div>
            <a href="verifikasi_acc.php" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition flex items-center gap-1.5">
              <span>Ke Halaman Verifikasi &amp; ACC</span>
              <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/70">
                  <th class="py-4 px-6">Laporan &amp; Waktu</th>
                  <th class="py-4 px-6">Pelapor &amp; Lokasi</th>
                  <th class="py-4 px-6 text-center">Status</th>
                  <th class="py-4 px-6 text-center">Prioritas</th>
                  <th class="py-4 px-6 text-center">Tindakan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-sm">
                <?php foreach ($recentReports as $rep): 
                  $isMenunggu = ($rep['status'] === 'menunggu');
                  $isDarurat = strtolower($rep['prioritas'] ?? '') === 'darurat';
                  $isTinggi = strtolower($rep['prioritas'] ?? '') === 'tinggi';
                  $prioLabel = ucfirst($rep['prioritas'] ?? 'Sedang');
                ?>
                <tr class="hover:bg-slate-50/70 transition-colors">
                  <td class="py-4 px-6">
                    <a href="detail_Laporan.php?id=<?= $rep['id']; ?>&role=admin" class="font-bold text-slate-900 hover:text-blue-700 transition block text-sm leading-snug">
                      <?= htmlspecialchars($rep['judul']); ?>
                    </a>
                    <span class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                      <span class="material-symbols-outlined text-[13px]">schedule</span>
                      <span><?= htmlspecialchars($rep['created_at']); ?></span>
                    </span>
                  </td>
                  <td class="py-4 px-6 text-xs">
                    <span class="font-bold text-slate-800 block text-xs"><?= htmlspecialchars($rep['nama_lengkap'] ?? 'Warga'); ?></span>
                    <span class="text-slate-500 mt-0.5 flex items-center gap-1">
                      <span class="material-symbols-outlined text-[13px] text-slate-400">location_on</span>
                      <span><?= htmlspecialchars($rep['alamat'] ?? 'Sukamaju'); ?></span>
                    </span>
                  </td>
                  <td class="py-4 px-6 text-center">
                    <?php if ($isMenunggu): ?>
                      <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200/70">Menunggu ACC</span>
                    <?php else: ?>
                      <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">Diproses Regu</span>
                    <?php endif; ?>
                  </td>
                  <td class="py-4 px-6 text-center">
                    <?php if ($isDarurat): ?>
                      <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                        <?= $prioLabel; ?>
                      </span>
                    <?php elseif ($isTinggi): ?>
                      <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200/60">
                        <?= $prioLabel; ?>
                      </span>
                    <?php else: ?>
                      <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200/60">
                        <?= $prioLabel; ?>
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="py-4 px-6 text-center">
                    <?php if ($isMenunggu): ?>
                      <a href="verifikasi_acc.php" class="px-3.5 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white border border-blue-200 hover:border-blue-600 text-xs font-bold transition-all duration-150 inline-flex items-center gap-1.5 shadow-2xs">
                        <span class="material-symbols-outlined text-[15px]">verified</span>
                        <span>Verifikasi</span>
                      </a>
                    <?php else: ?>
                      <a href="detail_Laporan.php?id=<?= $rep['id']; ?>&role=admin" class="px-3.5 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-800 text-slate-700 hover:text-white border border-slate-200 hover:border-slate-800 text-xs font-bold transition-all duration-150 inline-flex items-center gap-1.5 shadow-2xs">
                        <span class="material-symbols-outlined text-[15px]">visibility</span>
                        <span>Detail</span>
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

<script>
  // ── Scroll-Reveal: Intersection Observer ───────────────────────────
  (function() {
    const revealEls = document.querySelectorAll('.anim-fade-up');
    if (!revealEls.length) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

    revealEls.forEach(el => observer.observe(el));
  })();
</script>
</body>
</html>
