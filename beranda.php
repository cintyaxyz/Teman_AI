<?php
/**
 * beranda.php — Dashboard Warga Teman Warga AI
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/nav_sidebar.php';
requireLogin();

$user  = currentUser();
$userRoleClean = strtolower($user['role'] ?? 'warga');

// Arahkan Admin dan Petugas ke dashboard khusus masing-masing
if ($userRoleClean === 'admin') {
    header('Location: dashboard_admin.php');
    exit;
} elseif ($userRoleClean === 'petugas') {
    header('Location: dashboard_petugas.php');
    exit;
}

$db    = getDB();

// Ambil statistik laporan user
$stmtTotal = $db->prepare("SELECT COUNT(*) FROM laporan WHERE user_id = ?");
$stmtTotal->execute([$user['id']]);
$totalLaporan = (int) $stmtTotal->fetchColumn();

$stmtProses = $db->prepare("SELECT COUNT(*) FROM laporan WHERE user_id = ? AND status = 'diproses'");
$stmtProses->execute([$user['id']]);
$dalamPenanganan = (int) $stmtProses->fetchColumn();

$stmtSelesai = $db->prepare("SELECT COUNT(*) FROM laporan WHERE user_id = ? AND status = 'selesai'");
$stmtSelesai->execute([$user['id']]);
$selesai = (int) $stmtSelesai->fetchColumn();

// Ambil laporan terbaru
$stmtRecent = $db->prepare("SELECT * FROM laporan WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmtRecent->execute([$user['id']]);
$recentLaporan = $stmtRecent->fetchAll();

// Jumlah notifikasi belum dibaca
$unreadNotif = countUnreadNotif();

// Flash message
$flash = getFlash();
?>
<!DOCTYPE html>

<html lang="id" style=""><head><meta charset="utf-8"/><meta content="width=device-width, initial-scale=1.0" name="viewport"/><meta content="web_dashboard" name="shell-type"/><title>Beranda - Teman Warga AI</title><link rel="icon" type="image/svg+xml" href="logo.php"/><link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/><style>
@layer base{
  html,body{margin:0;padding:0;}
  body{overscroll-behavior:none;}
  main>:first-child{margin-top:0!important;}
  main>:last-child{margin-bottom:0!important;}
}
::-webkit-scrollbar{display:none;}

/* Staggered entrance animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(18px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-entrance {
  animation: fadeInUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.delay-1 { animation-delay: 0.05s; }
.delay-2 { animation-delay: 0.15s; }
.delay-3 { animation-delay: 0.25s; }
.delay-4 { animation-delay: 0.35s; }
.delay-5 { animation-delay: 0.45s; }

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

.bubble-float {
  animation: gentleFloat 3.2s ease-in-out infinite;
}

.btn-shimmer {
  animation: shimmerGlow 4s ease-in-out infinite;
}

/* Accessibility: respect prefers-reduced-motion */
@media (prefers-reduced-motion: reduce) {
  *, ::before, ::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
</style><script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script><script id="tailwind-config">tailwind.config={darkMode:"class",theme:{extend:{colors:{"surface-container-low":"#eff4ff","surface-container-lowest":"#ffffff","outline":"#737686","on-background":"#0b1c30","outline-variant":"#c3c6d7","surface-container-highest":"#d3e4fe","surface-dim":"#cbdbf5","on-primary":"#ffffff","error-container":"#ffdad6","surface-container":"#e5eeff","tertiary":"#005b7c","surface":"#f8f9ff","secondary-fixed-dim":"#93ccff","primary-fixed-dim":"#b4c5ff","on-secondary-fixed":"#001d31","on-surface-variant":"#434655","on-primary-fixed-variant":"#003ea8","secondary-fixed":"#cce5ff","surface-variant":"#d3e4fe","on-surface":"#0b1c30","primary":"#004ac6","secondary":"#006398","secondary-container":"#5bb8fe","primary-container":"#2563eb","primary-fixed":"#dbe1ff","on-tertiary":"#ffffff","background":"#f8f9ff","on-tertiary-fixed-variant":"#004c69","on-primary-fixed":"#00174b","tertiary-fixed-dim":"#7bd0ff","on-secondary-fixed-variant":"#004b73","surface-bright":"#f8f9ff","inverse-surface":"#213145","on-error-container":"#93000a","surface-container-high":"#dce9ff","on-tertiary-container":"#e1f2ff","surface-tint":"#0053db","on-primary-container":"#eeefff","on-tertiary-fixed":"#001e2c","tertiary-container":"#00759f","inverse-on-surface":"#eaf1ff","on-secondary":"#ffffff","on-secondary-container":"#00476e","inverse-primary":"#b4c5ff","tertiary-fixed":"#c4e7ff","error":"#ba1a1a","on-error":"#ffffff"},borderRadius:{"DEFAULT":"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px"},spacing:{"space-md":"1rem","space-lg":"1.5rem","space-sm":"0.5rem","space-xs":"0.25rem","gutter":"1.25rem","margin":"2rem","space-xl":"2rem"},fontFamily:{"headline-md":["Outfit","sans-serif"],"label-sm":["Outfit","sans-serif"],"body-md":["Outfit","sans-serif"],"headline-lg":["Outfit","sans-serif"],"body-sm":["Outfit","sans-serif"],"headline-xl":["Outfit","sans-serif"],"body-lg":["Outfit","sans-serif"],"display-lg":["Outfit","sans-serif"],"label-lg":["Outfit","sans-serif"],"label-md":["Outfit","sans-serif"]},fontSize:{"headline-md":["18px",{"lineHeight":"26px","fontWeight":"600"}],"label-sm":["11px",{"lineHeight":"14px","letterSpacing":"0.02em","fontWeight":"600"}],"body-md":["14px",{"lineHeight":"22px","fontWeight":"400"}],"headline-lg":["22px",{"lineHeight":"30px","letterSpacing":"-0.01em","fontWeight":"700"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"headline-xl":["28px",{"lineHeight":"36px","letterSpacing":"-0.015em","fontWeight":"700"}],"body-lg":["16px",{"lineHeight":"24px","fontWeight":"400"}],"display-lg":["40px",{"lineHeight":"48px","letterSpacing":"-0.02em","fontWeight":"800"}],"label-lg":["14px",{"lineHeight":"20px","fontWeight":"600"}],"label-md":["12px",{"lineHeight":"16px","letterSpacing":"0.01em","fontWeight":"600"}]}}}};</script></head><body class="bg-gradient-to-b from-surface-container-lowest to-surface font-body-md text-body-md text-on-surface min-h-screen"><svg aria-hidden="true" class="inline-defs-container" style="position:absolute;width:0;height:0;overflow:hidden"></svg><header class="fixed top-0 left-0 right-0 h-16 z-50 bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] px-gutter flex items-center justify-between gap-space-lg"><?php renderHeaderBrand('warga'); ?><div class="flex-1 max-w-xl mx-auto"><div class="relative flex items-center w-full group"><span class="material-symbols-outlined absolute left-space-md text-outline group-focus-within:text-primary transition-colors text-[20px] pointer-events-none">search</span><input class="w-full pl-11 pr-space-md py-2 bg-surface-container-low rounded-xl text-on-surface placeholder-outline font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 transition-all shadow-none focus:shadow-sm" placeholder="Cari bantuan, laporan, atau informasi..." type="text"/></div></div><div class="flex items-center gap-space-md shrink-0"><button aria-label="Notifikasi" onclick="window.location.href='notifikasi.php'" class="relative w-10 h-10 rounded-xl bg-surface-container-low hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-primary active:scale-95 transition-all" type="button"><span class="material-symbols-outlined text-[22px]">notifications</span><?php if($unreadNotif > 0): ?><span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-error text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white"><?= $unreadNotif > 9 ? '9+' : $unreadNotif ?></span><?php else: ?><span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-surface-container-lowest"></span><?php endif; ?></button><a href="Profile.php" class="flex items-center gap-space-sm pl-space-sm py-1 pr-space-sm rounded-xl hover:bg-surface-container-low cursor-pointer transition-all active:scale-95"><div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center ring-2 ring-transparent hover:ring-primary/30 transition-all"><span class="material-symbols-outlined text-on-primary text-[18px]">person</span></div><div class="hidden md:flex flex-col text-left"><span class="font-label-md text-label-md text-on-surface"><?= htmlspecialchars($user['nama']) ?></span><span class="font-label-sm text-label-sm text-outline"><?= ucfirst($user['role']) ?></span></div><span class="material-symbols-outlined text-outline text-[18px]">keyboard_arrow_down</span></a></div></header><?php renderSidebarNav('dashboard-warga', 'warga'); ?><div class="pl-64"><main class="w-full pt-16 px-margin py-margin"><div class="flex flex-col w-full">
<!-- Top Greeting & Header Section -->
<div class="animate-entrance delay-1 flex flex-col md:flex-row md:items-center justify-between gap-space-md mb-space-xl">
<div class="flex flex-col">
<div class="flex items-center gap-space-md"><h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight">Selamat Datang, <?= htmlspecialchars($user['nama']) ?></h1></div>
<p class="font-body-md text-body-md text-outline mt-0.5">
        Bersama kita wujudkan lingkungan yang lebih baik, bersih, dan aman.
      </p>
</div>
<!-- Quick Date / Badge Info (Auto Geolocation Detection) -->
<div id="aiWilayahBadge" onclick="refreshUserWilayah(true)" class="group flex items-center gap-space-sm px-space-md py-2 bg-surface-container-lowest rounded-xl shadow-sm border border-surface-container-high/40 self-start md:self-auto hover:shadow-md hover:border-primary/40 transition-all cursor-pointer select-none active:scale-95" title="Klik untuk mendeteksi atau menyegarkan wilayah Anda secara otomatis">
  <span id="geoPulseDot" class="relative flex h-2.5 w-2.5 shrink-0">
    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
  </span>
  <span id="aiWilayahLabel" class="font-label-md text-label-md text-on-surface-variant font-medium flex items-center gap-1.5">
    <span>Sistem AI Wilayah</span>
    <span id="currentWilayahText" class="text-primary font-bold transition-all">Sukamaju</span>
    <span>Aktif</span>
  </span>
  <span id="geoIconIndicator" class="material-symbols-outlined text-[16px] text-outline group-hover:text-primary transition-colors ml-0.5" title="Deteksi GPS">my_location</span>
</div>
</div>
<!-- 3 Summary Metrics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-space-lg mb-space-xl">
<!-- Stat 1: Total Laporan -->
<div class="animate-entrance delay-2 p-space-lg bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group cursor-default border border-transparent hover:border-primary/20">
<div class="flex flex-col">
<span class="font-label-md text-label-md text-outline group-hover:text-primary transition-colors">Total Laporan</span>
<span class="font-display-lg text-display-lg text-on-surface my-0.5 transition-transform group-hover:scale-105 origin-left duration-200"><?= $totalLaporan ?></span>
<div class="flex items-center gap-1 font-label-sm text-label-sm text-primary">
<span class="material-symbols-outlined text-[15px] group-hover:-translate-y-0.5 transition-transform">arrow_upward</span>
<span class="">+ 30% dari bulan lalu</span>
</div>
</div>
<div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-100/80 via-sky-50 to-indigo-100/70 p-2.5 shadow-sm ring-1 ring-blue-500/15 group-hover:ring-blue-500/35 group-hover:scale-110 transition-all duration-300 flex items-center justify-center overflow-hidden">
<img src="Logo%20aksesori/Document.jpg" alt="Icon Total Laporan" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform duration-300 group-hover:scale-105" />
</div>
</div>
<!-- Stat 2: Dalam Penanganan -->
<div class="animate-entrance delay-2 p-space-lg bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group cursor-default border border-transparent hover:border-amber-500/20">
<div class="flex flex-col">
<span class="font-label-md text-label-md text-outline group-hover:text-amber-700 transition-colors">Dalam Penanganan</span>
<span class="font-display-lg text-display-lg text-on-surface my-0.5 transition-transform group-hover:scale-105 origin-left duration-200"><?= $dalamPenanganan ?></span>
<div class="flex items-center gap-1 font-label-sm text-label-sm text-amber-600">
<span class="material-symbols-outlined text-[15px] group-hover:rotate-45 transition-transform">trending_up</span>
<span class="">+ 10% dari bulan lalu</span>
</div>
</div>
<div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-100/80 via-amber-50 to-orange-100/70 p-2.5 shadow-sm ring-1 ring-amber-500/15 group-hover:ring-amber-500/35 group-hover:scale-110 transition-all duration-300 flex items-center justify-center overflow-hidden">
<img src="Logo%20aksesori/Timer.jpg" alt="Icon Dalam Penanganan" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform duration-300 group-hover:scale-105" />
</div>
</div>
<!-- Stat 3: Selesai -->
<div class="animate-entrance delay-2 p-space-lg bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group cursor-default border border-transparent hover:border-emerald-500/20">
<div class="flex flex-col">
<span class="font-label-md text-label-md text-outline group-hover:text-emerald-700 transition-colors">Selesai</span>
<span class="font-display-lg text-display-lg text-on-surface my-0.5 transition-transform group-hover:scale-105 origin-left duration-200"><?= $selesai ?></span>
<div class="flex items-center gap-1 font-label-sm text-label-sm text-emerald-600">
<span class="material-symbols-outlined text-[15px] group-hover:scale-110 transition-transform">check_circle</span>
<span class="">+ 40% dari bulan lalu</span>
</div>
</div>
<div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-100/80 via-emerald-50 to-teal-100/70 p-2.5 shadow-sm ring-1 ring-emerald-500/15 group-hover:ring-emerald-500/35 group-hover:scale-110 transition-all duration-300 flex items-center justify-center overflow-hidden">
<img src="Logo%20aksesori/Ceklis%20(2).jpg" alt="Icon Selesai" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform duration-300 group-hover:scale-105" />
</div>
</div>
</div>
<!-- Main 2-Column Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg">
<!-- Left Column: Map & Latest Reports (8 Cols) -->
<div class="lg:col-span-8 flex flex-col gap-space-lg">
<!-- Dashboard Analisis Statistik & Tren Masalah -->
<div class="animate-entrance delay-3 p-space-lg bg-surface-container-lowest rounded-2xl shadow-sm flex flex-col border border-surface-container-high/40 hover:shadow-md transition-shadow">
  <!-- Header Card -->
  <div class="flex items-center justify-between mb-space-md">
    <div class="flex items-center gap-space-sm">
      <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-blue-100/80 via-sky-50 to-indigo-100/70 p-2 shadow-xs ring-1 ring-blue-500/15 flex items-center justify-center shrink-0 overflow-hidden">
        <img src="Logo%20aksesori/Static.jpg" alt="Icon Analisis &amp; Tren" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs" />
      </div>
      <div class="flex flex-col">
        <h2 class="font-headline-md text-headline-md text-on-surface font-bold leading-tight">Analisis &amp; Tren Masalah</h2>
        <span class="font-label-sm text-label-sm text-outline">Statistik Lingkungan Bulan Ini (September 2026)</span>
      </div>
    </div>
    <a class="group px-space-md py-1.5 bg-surface-container-low hover:bg-surface-container rounded-xl text-primary font-label-md text-label-md transition-all flex items-center gap-1 active:scale-95" href="peta_laporan.php">
      <span class="material-symbols-outlined text-[18px]">map</span>
      <span>Peta Laporan</span>
      <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
    </a>
  </div>

  <!-- 3 Quick KPI Metric Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-sm mb-space-lg">
    <!-- KPI 1: Kecepatan Respon -->
    <div class="p-3.5 rounded-xl bg-surface-container-low/70 border border-surface-container-high/40 flex flex-col justify-between hover:bg-surface-container-low transition-colors">
      <div class="flex items-center justify-between">
        <span class="font-label-sm text-outline font-medium">Rata-rata Respon</span>
        <span class="material-symbols-outlined text-primary text-[18px]">speed</span>
      </div>
      <div class="flex items-baseline gap-2 mt-1">
        <span class="text-2xl font-extrabold text-on-surface">3.2</span>
        <span class="text-xs font-semibold text-outline">Jam</span>
      </div>
      <div class="flex items-center gap-1 text-[11px] text-emerald-600 font-semibold mt-1">
        <span class="material-symbols-outlined text-[14px]">trending_up</span>
        <span>45% lebih cepat</span>
      </div>
    </div>

    <!-- KPI 2: Tingkat Penyelesaian -->
    <div class="p-3.5 rounded-xl bg-surface-container-low/70 border border-surface-container-high/40 flex flex-col justify-between hover:bg-surface-container-low transition-colors">
      <div class="flex items-center justify-between">
        <span class="font-label-sm text-outline font-medium">Tingkat Penanganan</span>
        <span class="material-symbols-outlined text-emerald-600 text-[18px]">verified</span>
      </div>
      <div class="flex items-baseline gap-2 mt-1">
        <span class="text-2xl font-extrabold text-emerald-700">88.5%</span>
      </div>
      <div class="flex items-center gap-1 text-[11px] text-outline font-medium mt-1">
        <span>21 dari 24 laporan tuntas</span>
      </div>
    </div>

    <!-- KPI 3: Kepuasan Warga -->
    <div class="p-3.5 rounded-xl bg-surface-container-low/70 border border-surface-container-high/40 flex flex-col justify-between hover:bg-surface-container-low transition-colors">
      <div class="flex items-center justify-between">
        <span class="font-label-sm text-outline font-medium">Kepuasan Warga</span>
        <span class="material-symbols-outlined text-amber-500 text-[18px]">star</span>
      </div>
      <div class="flex items-baseline gap-2 mt-1">
        <span class="text-2xl font-extrabold text-amber-800">4.8</span>
        <span class="text-xs font-semibold text-outline">/ 5.0</span>
      </div>
      <div class="flex items-center gap-1 text-[11px] text-emerald-600 font-semibold mt-1">
        <span class="material-symbols-outlined text-[14px]">thumb_up</span>
        <span>94% warga puas</span>
      </div>
    </div>
  </div>

  <!-- Distribution Progress Bars -->
  <div class="flex flex-col gap-3.5 mb-space-md">
    <div class="flex items-center justify-between">
      <span class="font-label-md text-label-md text-on-surface font-bold">Kategori Masalah Paling Sering Dilaporkan</span>
      <span class="text-xs text-outline font-medium">Total 24 Aduan</span>
    </div>

    <!-- Bar 1: Jalan Rusak -->
    <div class="flex flex-col gap-1.5">
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-[#EA580C]"></span>
          <span class="font-semibold text-on-surface">Jalan Rusak &amp; Ambles</span>
        </div>
        <span class="font-bold text-on-surface">14 Laporan <span class="text-outline font-normal">(42%)</span></span>
      </div>
      <div class="w-full h-2.5 bg-surface-container-high rounded-full overflow-hidden">
        <div class="h-full bg-gradient-to-r from-orange-500 to-amber-500 rounded-full transition-all duration-1000" style="width: 42%;"></div>
      </div>
    </div>

    <!-- Bar 2: Banjir & Genangan -->
    <div class="flex flex-col gap-1.5">
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-[#DC2626]"></span>
          <span class="font-semibold text-on-surface">Banjir &amp; Genangan Air</span>
        </div>
        <span class="font-bold text-on-surface">6 Laporan <span class="text-outline font-normal">(25%)</span></span>
      </div>
      <div class="w-full h-2.5 bg-surface-container-high rounded-full overflow-hidden">
        <div class="h-full bg-gradient-to-r from-red-500 to-rose-400 rounded-full transition-all duration-1000" style="width: 25%;"></div>
      </div>
    </div>

    <!-- Bar 3: Sampah Liar -->
    <div class="flex flex-col gap-1.5">
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-[#16A34A]"></span>
          <span class="font-semibold text-on-surface">Sampah Liar di Saluran/Bantaran</span>
        </div>
        <span class="font-bold text-on-surface">4 Laporan <span class="text-outline font-normal">(18%)</span></span>
      </div>
      <div class="w-full h-2.5 bg-surface-container-high rounded-full overflow-hidden">
        <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-1000" style="width: 18%;"></div>
      </div>
    </div>

    <!-- Bar 4: Penerangan Jalan -->
    <div class="flex flex-col gap-1.5">
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-[#D97706]"></span>
          <span class="font-semibold text-on-surface">Penerangan Jalan Umum (PJU)</span>
        </div>
        <span class="font-bold text-on-surface">3 Laporan <span class="text-outline font-normal">(15%)</span></span>
      </div>
      <div class="w-full h-2.5 bg-surface-container-high rounded-full overflow-hidden">
        <div class="h-full bg-gradient-to-r from-amber-400 to-yellow-400 rounded-full transition-all duration-1000" style="width: 15%;"></div>
      </div>
    </div>
  </div>

  <!-- AI Prediction & Environment Insight Box -->
  <div class="p-3.5 rounded-xl bg-gradient-to-r from-blue-50/70 via-surface-container-low to-indigo-50/70 border border-primary/15 flex items-start gap-3 mt-1">
    <div class="w-8 h-8 rounded-lg bg-primary text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
      <span class="material-symbols-outlined text-[18px]">psychology</span>
    </div>
    <div class="flex flex-col text-left">
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold text-primary">Insight AI Lingkungan</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Kondisi Terkendali</span>
      </div>
      <p class="text-xs text-on-surface-variant leading-relaxed mt-1">
        Titik aduan terbanyak minggu ini terkonsentrasi pada kerusakan aspal di koridor <strong>Jl. Melati &amp; Pitara</strong>. Berdasarkan jadwal dinas, perbaikan tambal sulam aspal telah dialokasikan untuk dikerjakan hari ini.
      </p>
    </div>
  </div>
</div>
<!-- Laporan Terbaru Section -->
<div class="animate-entrance delay-4 p-space-lg bg-surface-container-lowest rounded-xl shadow-sm flex flex-col border border-surface-container-high/40 hover:shadow-md transition-shadow">
<div class="flex items-center justify-between mb-space-md">
<h2 class="font-headline-md text-headline-md text-on-surface font-bold">Laporan Terbaru</h2>
<span class="font-label-sm text-label-sm text-outline flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Terakhir diperbarui: Baru saja</span>
</div>
<div class="flex flex-col gap-space-md">
<!-- Item 1: Jalan Berlubang -->
<div onclick="window.location.href='detail_Laporan.php'" class="p-space-md bg-surface-container-low/60 hover:bg-surface-container-low rounded-xl transition-all duration-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-space-md cursor-pointer group hover:shadow-md hover:-translate-y-0.5 border border-transparent hover:border-surface-container-high">
<div class="flex items-center gap-space-md">
<div class="overflow-hidden rounded-xl w-20 h-20 shrink-0 shadow-sm"><img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Close up photography of an asphalt road with severe deep potholes filled with rainwater and crushed stones, surrounded by urban roadside pavement, daylight, realistic civic reporting view." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBtsU1pqIJ4jg9Cdtv4MThZ6tXGeC3sWyDDcJXhq9Z4JoAXtq8Wc8DCVK7opzr5WHXbNkx_VKlAVDNO7-WSGsRwrgC4U7gx_uAg1OAFLAhTWuhxvebPTh3Rf5ZN4DVn5ZunkiAh9vH4SA75mTxW2K8XifW5zusJg02BIYh0u3TejOVUe8mcJH36MqHeZE5OHczQhGkhhR6j4N3akY603IKKOzU7MU4AD0-UOG7YAT1Bk0_kx27yx0I"/></div>
<div class="flex flex-col">
<span class="font-headline-md text-[16px] text-on-surface font-semibold group-hover:text-primary transition-colors">
                  Jalan berlubang di depan SMP 2
                </span>
<div class="flex items-center gap-1 text-outline font-body-sm text-body-sm mt-0.5">
<span class="material-symbols-outlined text-[16px] text-primary">location_on</span>
<span class="">Jl. Melati, Kec. Sukamaju</span>
</div>
<div class="flex items-center gap-1 text-outline font-label-sm text-label-sm mt-1">
<span class="material-symbols-outlined text-[14px]">schedule</span>
<span class="">2 jam yang lalu</span>
</div>
</div>
</div>
<!-- Status Badge -->
<div class="shrink-0 self-end sm:self-center">
<span class="inline-flex items-center justify-center w-[155px] py-1.5 rounded-lg text-xs font-semibold tracking-wide bg-amber-500/10 text-amber-700 border border-amber-500/30 shadow-[0_1px_4px_rgba(245,158,11,0.12)] group-hover:bg-amber-500/20 group-hover:border-amber-500/45 transition-all">
Dalam Penanganan
</span>
</div>
</div>
<!-- Item 2: Sampah Sungai -->
<div onclick="window.location.href='detail_Laporan.php'" class="p-space-md bg-surface-container-low/60 hover:bg-surface-container-low rounded-xl transition-all duration-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-space-md cursor-pointer group hover:shadow-md hover:-translate-y-0.5 border border-transparent hover:border-surface-container-high">
<div class="flex items-center gap-space-md">
<div class="overflow-hidden rounded-xl w-20 h-20 shrink-0 shadow-sm"><img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Outdoor street level documentary shot of an overflowing garbage pile with plastic waste along a suburban canal drainage bank in Indonesia, soft morning natural lighting." src="https://lh3.googleusercontent.com/aida-public/AB6AXuCMyjDNgaJHtS2ibeskU1OEztEyzysYLPh03NTiCtVrLVBBo7Fszf_pY-Lexj8k5mAG9yOPcl7JUik4NCWXIE_ESs4VdHJCCifI6SKDCGcqAPNzwHoFAP-ePd-rsyW9QoXA1zT4T-A9f6VJf1bqxp9WgOwupx_rDbQoKapUof2Vsz-iYXZLavD7Dqqi-3EiwhsLXwe4zoxFcmrFMzhdsCSTMiH3ixi3ViZ76fBnJBUZEPCPSQKa3RQ"/></div>
<div class="flex flex-col">
<span class="font-headline-md text-[16px] text-on-surface font-semibold group-hover:text-primary transition-colors">
                  Tumpukan sampah di pinggir sungai
                </span>
<div class="flex items-center gap-1 text-outline font-body-sm text-body-sm mt-0.5">
<span class="material-symbols-outlined text-[16px] text-primary">location_on</span>
<span class="">Jl. Cempaka, Kec. Sukamaju</span>
</div>
<div class="flex items-center gap-1 text-outline font-label-sm text-label-sm mt-1">
<span class="material-symbols-outlined text-[14px]">schedule</span>
<span class="">4 jam yang lalu</span>
</div>
</div>
</div>
<!-- Status Badge -->
<div class="shrink-0 self-end sm:self-center">
<span class="inline-flex items-center justify-center w-[155px] py-1.5 rounded-lg text-xs font-semibold tracking-wide bg-blue-500/10 text-blue-700 border border-blue-500/30 shadow-[0_1px_4px_rgba(59,130,246,0.12)] group-hover:bg-blue-500/20 group-hover:border-blue-500/45 transition-all">
Laporan Terbaru
</span>
</div>
</div>
</div>
</div>
</div>
<!-- Right Column: Card 'Lapor Cepat' & Assistant Mini Banner (4 Cols) -->
<div class="lg:col-span-4 flex flex-col gap-space-lg">
<!-- Card Lapor Cepat -->
<div class="animate-entrance delay-3 p-space-lg bg-surface-container-lowest rounded-xl shadow-sm flex flex-col border border-surface-container-high/40 hover:shadow-md transition-shadow">
<div class="flex items-center justify-between mb-space-md">
<div class="flex items-center gap-space-sm"><span class="material-symbols-outlined text-primary text-[22px]">bolt</span><h2 class="font-headline-md text-headline-md text-on-surface font-bold">Lapor Cepat</h2></div>
<span class="font-label-sm text-label-sm px-2 py-0.5 bg-surface-container rounded text-outline font-medium">Kategori Populer</span>
</div>
<p class="font-body-sm text-body-sm text-outline mb-space-md">
          Pilih kategori masalah yang ingin Anda adukan untuk klasifikasi instan oleh AI:
        </p>
<!-- Category Options List -->
<div class="flex flex-col gap-2.5 mb-space-lg">
<!-- 1. Jalan Rusak -->
<a class="p-2.5 bg-surface-container-low hover:bg-surface-container rounded-xl flex items-center justify-between group transition-all duration-200 hover:shadow-sm active:scale-[0.98] border border-transparent hover:border-blue-500/20" href="buat_laporan_Masyarakat.php">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-100/90 via-sky-50 to-indigo-100/70 p-1.5 shadow-xs ring-1 ring-blue-500/15 group-hover:ring-blue-500/35 flex items-center justify-center group-hover:scale-110 transition-all duration-300 overflow-hidden shrink-0">
<img src="Logo%20aksesori/Tanah%20rusak.jpg" alt="Jalan Rusak" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform duration-300 group-hover:scale-105" />
</div>
<span class="font-label-lg text-label-lg text-on-surface group-hover:text-primary transition-colors font-medium">Jalan Rusak</span>
</div>
<span class="material-symbols-outlined text-outline text-[18px] group-hover:translate-x-1.5 group-hover:text-primary transition-all">chevron_right</span>
</a>
<!-- 2. Sampah -->
<a class="p-2.5 bg-surface-container-low hover:bg-surface-container rounded-xl flex items-center justify-between group transition-all duration-200 hover:shadow-sm active:scale-[0.98] border border-transparent hover:border-emerald-500/20" href="buat_laporan_Masyarakat.php">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-100/90 via-emerald-50 to-teal-100/70 p-1.5 shadow-xs ring-1 ring-emerald-500/15 group-hover:ring-emerald-500/35 flex items-center justify-center group-hover:scale-110 transition-all duration-300 overflow-hidden shrink-0">
<img src="Logo%20aksesori/Sampah.jpg" alt="Sampah &amp; Kebersihan" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform duration-300 group-hover:scale-105" />
</div>
<span class="font-label-lg text-label-lg text-on-surface group-hover:text-emerald-700 transition-colors font-medium">Sampah &amp; Kebersihan</span>
</div>
<span class="material-symbols-outlined text-outline text-[18px] group-hover:translate-x-1.5 group-hover:text-emerald-700 transition-all">chevron_right</span>
</a>
<!-- 3. Lampu Jalan Mati -->
<a class="p-2.5 bg-surface-container-low hover:bg-surface-container rounded-xl flex items-center justify-between group transition-all duration-200 hover:shadow-sm active:scale-[0.98] border border-transparent hover:border-amber-500/20" href="buat_laporan_Masyarakat.php">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-100/90 via-amber-50 to-orange-100/70 p-1.5 shadow-xs ring-1 ring-amber-500/15 group-hover:ring-amber-500/35 flex items-center justify-center group-hover:scale-110 transition-all duration-300 overflow-hidden shrink-0">
<img src="Logo%20aksesori/Lampu.jpg" alt="Lampu Jalan Mati" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform duration-300 group-hover:scale-105" />
</div>
<span class="font-label-lg text-label-lg text-on-surface group-hover:text-amber-700 transition-colors font-medium">Lampu Jalan Mati</span>
</div>
<span class="material-symbols-outlined text-outline text-[18px] group-hover:translate-x-1.5 group-hover:text-amber-700 transition-all">chevron_right</span>
</a>
<!-- 4. Banjir -->
<a class="p-2.5 bg-surface-container-low hover:bg-surface-container rounded-xl flex items-center justify-between group transition-all duration-200 hover:shadow-sm active:scale-[0.98] border border-transparent hover:border-sky-500/20" href="buat_laporan_Masyarakat.php">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-100/90 via-sky-50 to-blue-100/70 p-1.5 shadow-xs ring-1 ring-cyan-500/15 group-hover:ring-cyan-500/35 flex items-center justify-center group-hover:scale-110 transition-all duration-300 overflow-hidden shrink-0">
<img src="Logo%20aksesori/Banjir.jpg" alt="Banjir &amp; Genangan" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform duration-300 group-hover:scale-105" />
</div>
<span class="font-label-lg text-label-lg text-on-surface group-hover:text-sky-700 transition-colors font-medium">Banjir &amp; Genangan</span>
</div>
<span class="material-symbols-outlined text-outline text-[18px] group-hover:translate-x-1.5 group-hover:text-sky-700 transition-all">chevron_right</span>
</a>
<!-- 5. Fasilitas Umum Rusak -->
<a class="p-2.5 bg-surface-container-low hover:bg-surface-container rounded-xl flex items-center justify-between group transition-all duration-200 hover:shadow-sm active:scale-[0.98] border border-transparent hover:border-indigo-500/20" href="buat_laporan_Masyarakat.php">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-100/90 via-violet-50 to-purple-100/70 p-1.5 shadow-xs ring-1 ring-indigo-500/15 group-hover:ring-indigo-500/35 flex items-center justify-center group-hover:scale-110 transition-all duration-300 overflow-hidden shrink-0">
<img src="Logo%20aksesori/Fasilitas%20Umum.jpg" alt="Fasilitas Umum Rusak" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform duration-300 group-hover:scale-105" />
</div>
<span class="font-label-lg text-label-lg text-on-surface group-hover:text-indigo-700 transition-colors font-medium">Fasilitas Umum Rusak</span>
</div>
<span class="material-symbols-outlined text-outline text-[18px] group-hover:translate-x-1.5 group-hover:text-indigo-700 transition-all">chevron_right</span>
</a>
</div>
<!-- Big CTA Button with subtle shimmer glow -->
<a class="relative overflow-hidden w-full py-3.5 bg-primary-container hover:bg-primary text-on-primary rounded-xl font-label-lg text-label-lg flex items-center justify-center gap-2 shadow-md shadow-primary/25 transition-all duration-200 hover:shadow-lg hover:shadow-primary/30 active:scale-[0.98] group" href="buat_laporan_Masyarakat.php">
<span class="absolute inset-0 w-1/2 h-full bg-gradient-to-r from-transparent via-white/25 to-transparent btn-shimmer pointer-events-none"></span>
<span class="material-symbols-outlined text-[20px] group-hover:rotate-90 transition-transform duration-300">add_circle</span>
<span class="">Buat Laporan Baru</span>
<span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
</a>
</div>
<!-- AI Civic Bot Companion Banner -->
<div class="animate-entrance delay-4 p-space-lg bg-gradient-to-br from-primary-container via-primary to-secondary rounded-xl text-on-primary shadow-md relative overflow-hidden flex flex-col hover:shadow-lg transition-all duration-300 group">
<!-- Subtle decorative circular lights -->
<div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10 blur-xl pointer-events-none group-hover:scale-125 transition-transform duration-700"></div>
<div class="absolute -left-8 -bottom-8 w-28 h-28 rounded-full bg-secondary-container/20 blur-lg pointer-events-none"></div>
<div class="flex items-center gap-space-sm mb-space-sm relative z-10">
<div class="w-10 h-10 rounded-xl bg-white/95 p-1.5 shadow-sm group-hover:scale-110 group-hover:rotate-6 transition-all duration-300 flex items-center justify-center shrink-0">
<img src="Logo%20aksesori/messages%201.jpg" alt="Logo Teman AI" class="w-full h-full object-contain mix-blend-multiply drop-shadow-2xs" />
</div>
<span class="font-label-lg text-label-lg font-bold">Teman Warga AI</span>
</div>
<p class="font-body-sm text-body-sm text-white/90 mb-space-md leading-relaxed relative z-10">
          Bingung cara buat surat domisili, info jadwal imunisasi posyandu, atau izin warga? Tanyakan langsung pada asisten pintar kami!
        </p>
<a class="relative z-10 inline-flex items-center justify-center gap-2 px-space-md py-2.5 bg-white text-primary rounded-xl font-label-md text-label-md hover:bg-white/95 transition-all self-start shadow-sm hover:shadow active:scale-95 group/btn" href="Ai_bot.php">
<span class="">Buka AI Chat</span>
<span class="material-symbols-outlined text-[16px] group-hover/btn:translate-x-0.5 transition-transform">forum</span>
</a>
</div>
</div>
</div>
</div></main></div>
<script>


  // Auto-detect user's current wilayah via Geolocation + Nominatim Reverse Geocoding
  (function initWilayahDetection() {
    const badge = document.getElementById('aiWilayahBadge');
    const wilayahText = document.getElementById('currentWilayahText');
    const dot = document.getElementById('geoPulseDot');
    const icon = document.getElementById('geoIconIndicator');

    if (!wilayahText) return;

    // Check cached wilayah in sessionStorage or localStorage
    const savedWilayah = sessionStorage.getItem('detected_wilayah_name') || localStorage.getItem('detected_wilayah_name');
    if (savedWilayah) {
      wilayahText.textContent = savedWilayah;
      if (badge) {
        badge.setAttribute('title', `📍 Wilayah Anda: ${savedWilayah} (Klik untuk deteksi ulang)`);
      }
    }

    function setDetectingState() {
      if (dot) dot.innerHTML = '<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500"></span>';
      if (icon) icon.classList.add('animate-spin');
      wilayahText.innerHTML = '<span class="text-xs text-outline font-normal animate-pulse">Mendeteksi...</span>';
    }

    function setSuccessState(name, fullAddress = '') {
      if (dot) dot.innerHTML = '<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>';
      if (icon) icon.classList.remove('animate-spin');
      wilayahText.textContent = name;
      sessionStorage.setItem('detected_wilayah_name', name);
      localStorage.setItem('detected_wilayah_name', name);
      if (badge) {
        badge.setAttribute('title', `📍 Wilayah Anda: ${fullAddress || name} (Klik untuk deteksi ulang)`);
      }
    }

    function setErrorFallback(fallbackName = 'Sukamaju') {
      if (dot) dot.innerHTML = '<span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>';
      if (icon) icon.classList.remove('animate-spin');
      wilayahText.textContent = savedWilayah || fallbackName;
    }

    window.refreshUserWilayah = function(isManual = false) {
      if (!navigator.geolocation) {
        if (isManual) showNotificationToast('⚠️ Browser Anda tidak mendukung geolokasi GPS.');
        setErrorFallback('Sukamaju');
        return;
      }

      setDetectingState();

      navigator.geolocation.getCurrentPosition(
        async (pos) => {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;
          try {
            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=14&addressdetails=1`, {
              headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Geocoding response error');
            const data = await res.json();
            const addr = data.address || {};

            // Extract best readable district/subdistrict/village name in Indonesia
            const kelurahan = addr.village || addr.suburb || addr.neighbourhood || addr.quarter || addr.residential;
            const kecamatan = addr.city_district || addr.subdistrict || addr.district;
            const kota = addr.city || addr.town || addr.municipality || addr.county || addr.regency;

            let displayWilayah = '';
            if (kelurahan && kota) {
              displayWilayah = `${kelurahan}, ${kota.replace('Kota ', '').replace('Kabupaten ', '')}`;
            } else if (kelurahan) {
              displayWilayah = kelurahan;
            } else if (kecamatan && kota) {
              displayWilayah = `${kecamatan}, ${kota.replace('Kota ', '').replace('Kabupaten ', '')}`;
            } else if (kota) {
              displayWilayah = kota.replace('Kota ', '').replace('Kabupaten ', '');
            } else {
              displayWilayah = addr.state || 'Sukamaju';
            }

            setSuccessState(displayWilayah, data.display_name);

            if (isManual) {
              showNotificationToast(`📍 Wilayah terdeteksi: ${displayWilayah}`);
            }
          } catch (err) {
            console.warn('Nominatim reverse geocode error:', err);
            // IP Fallback
            try {
              const ipRes = await fetch('https://ipapi.co/json/');
              const ipData = await ipRes.json();
              if (ipData.city) {
                setSuccessState(ipData.city, `${ipData.city}, ${ipData.region}`);
                if (isManual) showNotificationToast(`📍 Wilayah terdeteksi (IP): ${ipData.city}`);
                return;
              }
            } catch(e) {}
            setErrorFallback('Sukamaju');
          }
        },
        (err) => {
          console.warn('Geolocation error:', err.message);
          // Try IP-based detection if GPS permission is denied or unavailable
          fetch('https://ipapi.co/json/')
            .then(r => r.json())
            .then(ipData => {
              if (ipData.city) {
                setSuccessState(ipData.city, `${ipData.city}, ${ipData.region}`);
                if (isManual) showNotificationToast(`📍 Wilayah terdeteksi (IP): ${ipData.city}`);
              } else {
                setErrorFallback('Sukamaju');
              }
            })
            .catch(() => {
              setErrorFallback('Sukamaju');
              if (isManual) showNotificationToast('⚠️ Izin lokasi tidak diizinkan. Menggunakan Sukamaju');
            });
        },
        { timeout: 7000, maximumAge: 300000, enableHighAccuracy: true }
      );
    };

    function showNotificationToast(msg) {
      let toast = document.getElementById('wilayahToast');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'wilayahToast';
        toast.className = 'fixed bottom-6 right-6 z-50 px-4 py-3 bg-surface-container-lowest text-on-surface text-sm font-semibold rounded-2xl shadow-xl border border-primary/20 flex items-center gap-2 transform translate-y-10 opacity-0 transition-all duration-300';
        document.body.appendChild(toast);
      }
      toast.innerHTML = `<span class="material-symbols-outlined text-primary text-[20px]">explore</span><span>${msg}</span>`;
      toast.classList.remove('translate-y-10', 'opacity-0');
      setTimeout(() => {
        toast.classList.add('translate-y-10', 'opacity-0');
      }, 3500);
    }

    // Auto-detect silently on page load
    setTimeout(() => {
      window.refreshUserWilayah(false);
    }, 500);
  })();
</script>
</body></html>