<?php
/**
 * monitoring_petugas.php — Halaman Khusus Monitoring Tim Petugas Lapangan
 * Khusus Administrator Kelurahan — Teman Warga AI
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/nav_sidebar.php';
requireLogin();

$currentUser = currentUser();
$userRoleClean = strtolower($currentUser['role'] ?? 'admin');

// Batasi akses: Hanya untuk admin
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

// Preset Data Regu Petugas Lapangan Kelurahan Sukamaju
$reguList = [
    [
        'id' => 'bpbd',
        'nama' => 'Regu Reaksi Cepat BPBD (Tim Bravo-3)',
        'bidang' => 'Banjir, Longsor & Tanggap Bencana',
        'icon' => 'Logo%20aksesori/Banjir.jpg',
        'koordinator' => 'Komandan Arief Wicaksono',
        'kontak' => '+62 812-4455-8890',
        'status' => 'Siaga Penuh',
        'status_badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'personil' => '8 Personil',
        'armada' => '2 Perahu Karet, 1 Truk Rescue',
        'tugas_aktif' => 3,
        'wilayah_fokus' => 'RW 06 & RW 08 (Bantaran Kali)'
    ],
    [
        'id' => 'pupr',
        'nama' => 'Dinas PUPR - Regu Penambalan Jalan & Saluran',
        'bidang' => 'Infrastruktur Jalan Rusak & Drainase',
        'icon' => 'Logo%20aksesori/Tanah%20rusak.jpg',
        'koordinator' => 'Ir. Hendra Gunawan',
        'kontak' => '+62 813-7788-9901',
        'status' => 'Sedang di Lokasi',
        'status_badge' => 'bg-blue-100 text-blue-800 border-blue-200',
        'personil' => '12 Personil',
        'armada' => '1 Aspal Finisher Mini, 2 Pickup',
        'tugas_aktif' => 5,
        'wilayah_fokus' => 'Jl. Pitara Raya & Jl. Flamboyan'
    ],
    [
        'id' => 'dlh',
        'nama' => 'Dinas Lingkungan Hidup - Satgas Sampah',
        'bidang' => 'Pengangkutan Sampah & Pembersihan TPS Liar',
        'icon' => 'Logo%20aksesori/Sampah.jpg',
        'koordinator' => 'Bpk. Supriyadi',
        'kontak' => '+62 821-3322-1144',
        'status' => 'Armada Beroperasi',
        'status_badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'personil' => '10 Personil',
        'armada' => '3 Truk Compactor, 4 Gerobak Motor',
        'tugas_aktif' => 4,
        'wilayah_fokus' => 'Pasar Kemiri & RT 04 Sukamaju'
    ],
    [
        'id' => 'dishub',
        'nama' => 'Dishub - Regu Penerangan Jalan Umum (PJU)',
        'bidang' => 'PJU, Kelistrikan & Rambu Lalu Lintas',
        'icon' => 'Logo%20aksesori/Lampu.jpg',
        'koordinator' => 'Agus Priyono, S.T.',
        'kontak' => '+62 857-9900-2211',
        'status' => 'Jadwal Reguler',
        'status_badge' => 'bg-amber-100 text-amber-800 border-amber-200',
        'personil' => '6 Personil',
        'armada' => '1 Mobil Tangga Skylift',
        'tugas_aktif' => 2,
        'wilayah_fokus' => 'Area Permukiman RW 02 - RW 05'
    ]
];

// Query Laporan yang Sedang Dikerjakan Petugas
try {
    $stmtTugas = $db->query("SELECT l.*, u.nama_lengkap 
                            FROM laporan l 
                            LEFT JOIN users u ON l.user_id = u.id 
                            WHERE l.status = 'diproses' 
                            ORDER BY l.updated_at DESC LIMIT 8");
    $tugasBerjalan = $stmtTugas->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $tugasBerjalan = [];
}

if (empty($tugasBerjalan)) {
    $tugasBerjalan = [
        [
            'id' => 11,
            'tiket' => '#LPR-2024-0098',
            'judul' => 'Pengurukan & penambalan lubang jalan aspal retak',
            'alamat' => 'Jl. Kenanga No. 18, RT 01/RW 04',
            'regu' => 'Dinas PUPR - Regu Penambalan Jalan',
            'progres' => '65%',
            'mulai' => 'Tadi pagi, 08:30 WIB'
        ],
        [
            'id' => 12,
            'tiket' => '#LPR-2024-0095',
            'judul' => 'Penyedotan genangan luapan selokan warga RT 03',
            'alamat' => 'Gang Melati IV, RT 03/RW 06',
            'regu' => 'Regu Reaksi Cepat BPBD (Tim Bravo-3)',
            'progres' => '80%',
            'mulai' => 'Tadi pagi, 09:15 WIB'
        ],
        [
            'id' => 13,
            'tiket' => '#LPR-2024-0091',
            'judul' => 'Pengangkutan tumpukan dahan pohon tumbang pasca angin kencang',
            'alamat' => 'Dekat Lapangan Sukamaju Indah',
            'regu' => 'Dinas Lingkungan Hidup - Satgas Sampah',
            'progres' => '40%',
            'mulai' => 'Pukul 10:00 WIB'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta content="web_dashboard" name="shell-type"/>
  <title>Monitoring Petugas — Portal Admin Kelurahan</title>
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
      from { opacity: 0; transform: translateY(18px); }
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
      animation-delay: 400ms;
    }
    .card-hover {
      transition: transform 0.2s cubic-bezier(0.16,1,0.3,1), box-shadow 0.2s ease;
    }
    .card-hover:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(0,0,0,0.07);
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
        <input class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200/90 rounded-xl text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all" placeholder="Cari nama regu, koordinator, atau armada..." type="text"/>
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
          <span class="text-[11px] text-amber-700 font-bold">Admin Verifikator</span>
        </div>
        <span class="material-symbols-outlined text-slate-400 text-[18px]">keyboard_arrow_down</span>
      </a>
    </div>
  </header>

  <!-- Sidebar Khusus ADMIN (Sesuai Screenshot) -->
  <?php renderSidebarNav('monitoring', 'admin'); ?>

  <!-- Konten Utama: Monitoring Petugas (Desain Luas) -->
  <div class="pl-64">
    <main class="w-full pt-16">
      <div class="max-w-7xl mx-auto px-6 sm:px-8 py-8 space-y-6">

        <!-- Header Halaman Luas -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.02)] anim-fade-up">
          <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Monitoring Regu &amp; Petugas Lapangan</h1>
            <p class="text-slate-500 text-sm sm:text-base max-w-2xl">Pantau kesiapan personil, armada kelurahan, status tugas di lapangan, serta koordinasi tanggap cepat terpadu.</p>
          </div>

          <div class="flex items-center gap-3">
            <a href="peta_laporan.php" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-primary/20 transition flex items-center gap-2">
              <img src="Logo%20aksesori/Maps.jpg" alt="Peta" style="width: 18px; height: 18px; max-width: 18px; max-height: 18px; object-fit: contain;" class="w-[18px] h-[18px] object-contain rounded shrink-0"/>
              <span>Pantau di Peta Wilayah</span>
            </a>
          </div>
        </div>

        <!-- 4 Metric Cards Status Petugas (Lega & Rapi dengan Logo Aksesori) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between anim-fade-up anim-delay-1">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Regu Aktif</span>
              <span class="p-1.5 rounded-xl bg-blue-50/80 border border-blue-100 flex items-center justify-center shrink-0">
                <img src="Logo%20aksesori/Regu%20aktif.jpg" alt="Regu Aktif" style="width: 24px; height: 24px; max-width: 24px; max-height: 24px; object-fit: contain;" class="w-6 h-6 object-contain rounded shrink-0"/>
              </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
              <span class="text-3xl font-extrabold text-slate-900">4 Regu</span>
              <span class="text-xs font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md">100% Siaga</span>
            </div>
            <span class="text-[11px] text-slate-400 mt-1">Regu lintas dinas &amp; kelurahan</span>
          </div>

          <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between anim-fade-up anim-delay-2">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Personil Lapangan</span>
              <span class="p-1.5 rounded-xl bg-emerald-50/80 border border-emerald-100 flex items-center justify-center shrink-0">
                <img src="Logo%20aksesori/Name%20Tag.jpg" alt="Personil" style="width: 24px; height: 24px; max-width: 24px; max-height: 24px; object-fit: contain;" class="w-6 h-6 object-contain rounded shrink-0"/>
              </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
              <span class="text-3xl font-extrabold text-slate-900">36 Orang</span>
              <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">Tersebar</span>
            </div>
            <span class="text-[11px] text-slate-400 mt-1">Kesiapan personil shift pagi &amp; sore</span>
          </div>

          <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between anim-fade-up anim-delay-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tugas Sedang Dikerjakan</span>
              <span class="p-1.5 rounded-xl bg-amber-50/80 border border-amber-100 flex items-center justify-center shrink-0">
                <img src="Logo%20aksesori/Timer.jpg" alt="Tugas Berjalan" style="width: 24px; height: 24px; max-width: 24px; max-height: 24px; object-fit: contain;" class="w-6 h-6 object-contain rounded shrink-0"/>
              </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
              <span class="text-3xl font-extrabold text-slate-900">14 Titik</span>
              <span class="text-xs font-bold text-amber-800 bg-amber-100/70 px-2 py-0.5 rounded-md">Aktif</span>
            </div>
            <span class="text-[11px] text-slate-400 mt-1">Laporan dalam penanganan hari ini</span>
          </div>

          <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between anim-fade-up anim-delay-4">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tuntas Bulan Ini</span>
              <span class="p-1.5 rounded-xl bg-purple-50/80 border border-purple-100 flex items-center justify-center shrink-0">
                <img src="Logo%20aksesori/Ceklis%20Ijau%20buat%20dashboard.jpg" alt="Tuntas" style="width: 24px; height: 24px; max-width: 24px; max-height: 24px; object-fit: contain;" class="w-6 h-6 object-contain rounded-full shrink-0"/>
              </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
              <span class="text-3xl font-extrabold text-slate-900">32 Titik</span>
              <span class="text-xs font-bold text-purple-700 bg-purple-100/70 px-2 py-0.5 rounded-md">94% Rata-rata</span>
            </div>
            <span class="text-[11px] text-slate-400 mt-1">Penyelesaian di Sukamaju</span>
          </div>
        </div>

        <!-- Grid Regu Petugas Lapangan (Cards Luas) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 anim-fade-up anim-delay-5">
          <?php foreach ($reguList as $regu): ?>
          <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.02)] flex flex-col justify-between gap-5 hover:border-primary/40 card-hover">
            <div class="space-y-3">
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-center shrink-0 shadow-xs p-2 overflow-hidden">
                    <img src="<?= $regu['icon'] ?? 'Logo%20aksesori/Petugas.jpg'; ?>" alt="<?= htmlspecialchars($regu['nama']); ?>" style="width: 32px; height: 32px; max-width: 32px; max-height: 32px; object-fit: contain;" class="w-8 h-8 object-contain rounded shrink-0"/>
                  </div>
                  <div>
                    <h3 class="font-bold text-slate-900 text-base sm:text-lg leading-tight"><?= htmlspecialchars($regu['nama']); ?></h3>
                    <span class="text-xs font-medium text-slate-500"><?= htmlspecialchars($regu['bidang']); ?></span>
                  </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold border shrink-0 <?= $regu['status_badge']; ?>">
                  <?= htmlspecialchars($regu['status']); ?>
                </span>
              </div>

              <!-- Rincian Regu -->
              <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 space-y-0.5">
                  <span class="text-slate-400 block text-[11px]">Koordinator</span>
                  <span class="font-bold text-slate-800"><?= htmlspecialchars($regu['koordinator']); ?></span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 space-y-0.5">
                  <span class="text-slate-400 block text-[11px]">Kapasitas Personil</span>
                  <span class="font-bold text-slate-800"><?= htmlspecialchars($regu['personil']); ?></span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 space-y-0.5">
                  <span class="text-slate-400 block text-[11px]">Dukungan Armada</span>
                  <span class="font-bold text-slate-800 truncate block"><?= htmlspecialchars($regu['armada']); ?></span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 space-y-0.5">
                  <span class="text-slate-400 block text-[11px]">Wilayah Pantauan</span>
                  <span class="font-bold text-slate-800 truncate block"><?= htmlspecialchars($regu['wilayah_fokus']); ?></span>
                </div>
              </div>
            </div>

            <!-- Footer Card: Kontak & Penugasan -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
              <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span><?= $regu['tugas_aktif']; ?> Tugas Lapangan Aktif</span>
              </div>
              <a href="tel:<?= preg_replace('/[^0-9+]/', '', $regu['kontak']); ?>" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-emerald-600">call</span>
                <span>Hubungi Regu</span>
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Tabel Tugas Lapangan Berjalan (Lega) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden anim-fade-up anim-delay-6">
          <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Daftar Titik Penanganan Lapangan Saat Ini</h2>
              <p class="text-xs text-slate-500">Laporan warga yang telah di-ACC dan sedang dalam proses perbaikan di lapangan.</p>
            </div>
            <a href="verifikasi_acc.php" class="text-xs text-primary font-bold hover:underline flex items-center gap-1">
              <span>Ke Halaman Verifikasi &amp; ACC</span>
              <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/70">
                  <th class="py-4 px-6">Laporan &amp; Masalah</th>
                  <th class="py-4 px-6">Lokasi Penanganan</th>
                  <th class="py-4 px-6">Regu yang Menangani</th>
                  <th class="py-4 px-6">Waktu Mulai</th>
                  <th class="py-4 px-6 text-center">Status Pengerjaan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-sm">
                <?php foreach ($tugasBerjalan as $t): ?>
                <tr class="hover:bg-slate-50/70 transition-colors">
                  <td class="py-4 px-6">
                    <span class="font-bold text-slate-900 block"><?= htmlspecialchars($t['judul']); ?></span>
                  </td>
                  <td class="py-4 px-6 text-xs text-slate-600">
                    <div class="flex items-center gap-1.5">
                      <span class="material-symbols-outlined text-slate-400 text-[16px]">location_on</span>
                      <span><?= htmlspecialchars($t['alamat'] ?? 'Wilayah Sukamaju'); ?></span>
                    </div>
                  </td>
                  <td class="py-4 px-6 text-xs font-semibold text-slate-800">
                    <div class="flex items-center gap-2">
                      <img src="Logo%20aksesori/Petugas.jpg" alt="Petugas" style="width: 20px; height: 20px; max-width: 20px; max-height: 20px; object-fit: contain;" class="w-5 h-5 rounded-full object-contain shrink-0 border border-slate-200"/>
                      <span><?= htmlspecialchars($t['regu'] ?? 'Regu Lapangan Terpadu'); ?></span>
                    </div>
                  </td>
                  <td class="py-4 px-6 text-xs text-slate-500">
                    <?= htmlspecialchars($t['mulai'] ?? 'Sedang berlangsung'); ?>
                  </td>
                  <td class="py-4 px-6 text-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                      <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                      <span><?= htmlspecialchars($t['progres'] ?? 'Sedang Ditangani'); ?></span>
                    </span>
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
