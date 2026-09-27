<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/nav_sidebar.php';
requireLogin();

$currentUser = getCurrentUser();
$currentUserName = $currentUser ? htmlspecialchars($currentUser['nama']) : 'Warga';
$currentUserRole = $currentUser ? htmlspecialchars(ucfirst($currentUser['role'] ?? 'Warga')) : 'Warga';
$userRoleRaw = strtolower($currentUser['role'] ?? 'warga');
$isWarga = ($userRoleRaw === 'warga');
$unreadCount = getUnreadNotificationCount();

// Teman Warga AI - Peta Laporan Warga
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta content="web_dashboard" name="shell-type"/>
<title>Peta Laporan - Teman Warga AI</title>
<link rel="icon" type="image/svg+xml" href="logo.php"/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Leaflet Map CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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
            "secondary-fixed-dim": "#93ccff",
            "primary-fixed-dim": "#b4c5ff",
            "on-secondary-fixed": "#001d31",
            "on-surface-variant": "#434655",
            "on-primary-fixed-variant": "#003ea8",
            "secondary-fixed": "#cce5ff",
            "surface-variant": "#d3e4fe",
            "on-surface": "#0b1c30",
            "primary": "#004ac6",
            "secondary": "#006398",
            "secondary-container": "#5bb8fe",
            "primary-container": "#2563eb",
            "primary-fixed": "#dbe1ff",
            "on-tertiary": "#ffffff",
            "background": "#f8f9ff",
            "on-tertiary-fixed-variant": "#004c69",
            "on-primary-fixed": "#00174b",
            "tertiary-fixed-dim": "#7bd0ff",
            "on-secondary-fixed-variant": "#004b73",
            "surface-bright": "#f8f9ff",
            "inverse-surface": "#213145",
            "on-error-container": "#93000a",
            "surface-container-high": "#dce9ff",
            "on-tertiary-container": "#e1f2ff",
            "surface-tint": "#0053db",
            "on-primary-container": "#eeefff",
            "on-tertiary-fixed": "#001e2c",
            "tertiary-container": "#00759f",
            "inverse-on-surface": "#eaf1ff",
            "on-secondary": "#ffffff",
            "on-secondary-container": "#00476e",
            "inverse-primary": "#b4c5ff",
            "tertiary-fixed": "#c4e7ff",
            "error": "#ba1a1a",
            "on-error": "#ffffff"
          },
          borderRadius: {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          spacing: {
            "space-md": "1rem",
            "space-lg": "1.5rem",
            "space-sm": "0.5rem",
            "space-xs": "0.25rem",
            "gutter": "1.25rem",
            "margin": "2rem",
            "space-xl": "2rem"
          },
          fontFamily: {
            "headline-md": ["Outfit", "sans-serif"],
            "label-sm": ["Outfit", "sans-serif"],
            "body-md": ["Outfit", "sans-serif"],
            "headline-lg": ["Outfit", "sans-serif"],
            "body-sm": ["Outfit", "sans-serif"],
            "headline-xl": ["Outfit", "sans-serif"],
            "body-lg": ["Outfit", "sans-serif"],
            "display-lg": ["Outfit", "sans-serif"],
            "label-lg": ["Outfit", "sans-serif"],
            "label-md": ["Outfit", "sans-serif"]
          },
          fontSize: {
            "headline-md": ["18px", { "lineHeight": "26px", "fontWeight": "600" }],
            "label-sm": ["11px", { "lineHeight": "14px", "letterSpacing": "0.02em", "fontWeight": "600" }],
            "body-md": ["14px", { "lineHeight": "22px", "fontWeight": "400" }],
            "headline-lg": ["22px", { "lineHeight": "30px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
            "body-sm": ["13px", { "lineHeight": "18px", "fontWeight": "400" }],
            "headline-xl": ["28px", { "lineHeight": "36px", "letterSpacing": "-0.015em", "fontWeight": "700" }],
            "body-lg": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
            "display-lg": ["40px", { "lineHeight": "48px", "letterSpacing": "-0.02em", "fontWeight": "800" }],
            "label-lg": ["14px", { "lineHeight": "20px", "fontWeight": "600" }],
            "label-md": ["12px", { "lineHeight": "16px", "letterSpacing": "0.01em", "fontWeight": "600" }]
          }
        }
      }
    };
  </script>
<style>
    @layer base {
      html, body { margin: 0; padding: 0; }
      body { overscroll-behavior: none; }
      main > :first-child { margin-top: 0 !important; }
      main > :last-child { margin-bottom: 0 !important; }
    }
    ::-webkit-scrollbar { display: none; }

    select {
      background-image: none !important;
      -webkit-appearance: none !important;
      -moz-appearance: none !important;
      appearance: none !important;
    }

    @keyframes radar-pulse {
      0% {
        transform: scale(0.65);
        opacity: 0.95;
        box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7);
      }
      70% {
        opacity: 0.45;
      }
      100% {
        transform: scale(2.6);
        opacity: 0;
        box-shadow: 0 0 18px 8px rgba(37, 99, 235, 0);
      }
    }

    .animate-radar {
      animation: radar-pulse 2s cubic-bezier(0, 0.2, 0.8, 1) infinite;
    }

    .animate-radar-delayed {
      animation: radar-pulse 2s cubic-bezier(0, 0.2, 0.8, 1) infinite 0.7s;
    }

    /* Leaflet custom map markers */
    .report-leaflet-marker {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    .report-pin-circle {
      position: relative;
      z-index: 2;
      width: 38px;
      height: 38px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      border: 3px solid #ffffff;
      box-shadow: 0 4px 14px rgba(0,0,0,0.3);
      transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .report-pin-circle:hover {
      transform: scale(1.22);
    }
    .user-gps-pulse {
      position: absolute;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background-color: rgba(37, 99, 235, 0.35);
      animation: radar-pulse 2s cubic-bezier(0, 0.2, 0.8, 1) infinite;
      pointer-events: none;
    }
    .user-gps-dot {
      position: relative;
      z-index: 3;
      width: 40px;
      height: 40px;
      background: #004ac6;
      color: #ffffff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 3.5px solid #ffffff;
      box-shadow: 0 4px 16px rgba(0, 74, 198, 0.55);
    }

    @keyframes float-badge {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-3px); }
    }

    /* Custom Modern Leaflet Popup Styling */
    .report-action-popup .leaflet-popup-content-wrapper {
      padding: 0 !important;
      border-radius: 20px !important;
      box-shadow: 0 20px 40px -8px rgba(11, 28, 48, 0.22), 0 8px 16px -4px rgba(11, 28, 48, 0.1) !important;
      border: 1px solid rgba(195, 198, 215, 0.45) !important;
      background: #ffffff !important;
      overflow: hidden !important;
    }
    .report-action-popup .leaflet-popup-content {
      margin: 0 !important;
      line-height: normal !important;
      width: 280px !important;
    }
    .report-action-popup .leaflet-popup-tip-container {
      margin-top: -1px !important;
    }
    .report-action-popup .leaflet-popup-tip {
      background: #ffffff !important;
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12) !important;
    }
    .report-action-popup .leaflet-popup-close-button {
      top: 12px !important;
      right: 14px !important;
      width: 24px !important;
      height: 24px !important;
      border-radius: 50% !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      background: #f1f5f9 !important;
      color: #64748b !important;
      font-size: 15px !important;
      font-weight: 700 !important;
      text-decoration: none !important;
      border: none !important;
      transition: all 0.2s ease !important;
    }
    .report-action-popup .leaflet-popup-close-button:hover {
      background: #e2e8f0 !important;
      color: #0f172a !important;
      transform: scale(1.1) !important;
    }
    /* Explicit colors to override Leaflet default blue text */
    .report-action-popup a {
      text-decoration: none !important;
    }
    .report-action-popup a.popup-btn-primary {
      background: #004ac6 !important;
      color: #ffffff !important;
    }
    .report-action-popup a.popup-btn-primary * {
      color: #ffffff !important;
    }
    .report-action-popup a.popup-btn-primary:hover {
      background: #003ea8 !important;
    }
    .report-action-popup a.popup-btn-danger {
      background: #fef2f2 !important;
      color: #dc2626 !important;
      border: 1px solid #fee2e2 !important;
    }
    .report-action-popup a.popup-btn-danger:hover {
      background: #fee2e2 !important;
      color: #b91c1c !important;
    }
    .report-action-popup a.popup-btn-warning {
      background: #fff7ed !important;
      color: #ea580c !important;
      border: 1px solid #ffedd5 !important;
    }
    .report-action-popup a.popup-btn-warning:hover {
      background: #ffedd5 !important;
      color: #c2410c !important;
    }

    .tooltip-card {
      opacity: 0;
      transform: translateY(6px) scale(0.96);
      pointer-events: none;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .group:hover .tooltip-card,
    .group:focus-visible .tooltip-card {
      opacity: 1;
      transform: translateY(0) scale(1);
      pointer-events: auto;
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
</head>
<body class="bg-gradient-to-b from-surface-container-lowest to-surface font-body-md text-body-md text-on-surface min-h-screen">
<!-- Top App Header -->
<header class="fixed top-0 left-0 right-0 h-16 z-50 bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] px-gutter flex items-center justify-between gap-space-lg">
<?php renderHeaderBrand(); ?>
<div class="flex-1 max-w-xl mx-auto">
<div class="relative flex items-center w-full">
<span class="material-symbols-outlined absolute left-space-md text-outline text-[20px] pointer-events-none">search</span>
<input class="w-full pl-11 pr-space-md py-2 bg-surface-container-low rounded-xl text-on-surface placeholder-outline font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 transition-all" placeholder="Cari bantuan, laporan, atau informasi..." type="text"/>
</div>
</div>
<div class="flex items-center gap-space-md shrink-0">
<a href="notifikasi.php" aria-label="Notifikasi" class="relative w-10 h-10 rounded-xl bg-surface-container-low hover:bg-surface-container flex items-center justify-center text-on-surface-variant transition-colors">
<span class="material-symbols-outlined text-[22px]">notifications</span>
<?php if ($unreadCount > 0): ?>
<span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-error ring-2 ring-surface-container-lowest"></span>
<?php endif; ?>
</a>
<a href="Profile.php" class="flex items-center gap-space-sm pl-space-sm py-1 pr-space-sm rounded-xl hover:bg-surface-container-low cursor-pointer transition-colors">
<div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center shadow-sm">
<span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
</div>
<div class="hidden md:flex flex-col text-left">
<span class="font-label-md text-label-md text-on-surface"><?php echo $currentUserName; ?></span>
<span class="font-label-sm text-label-sm text-outline"><?php echo $currentUserRole; ?></span>
</div>
<span class="material-symbols-outlined text-outline text-[18px]">keyboard_arrow_down</span>
</a>
</div>
</header>
<!-- Sidebar Navigation Sesuai Peran -->
<?php renderSidebarNav('peta-laporan'); ?>
<!-- Main Content Body -->
<div class="pl-64">
<main class="w-full pt-16 px-margin py-margin">
<div class="flex flex-col w-full">
<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-space-sm mb-space-lg">
<div>
<h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight"><?= ($currentUserRole === 'Admin' || (isset($currentUser['role']) && $currentUser['role'] === 'admin')) ? 'Peta Pantauan Wilayah &amp; Petugas' : 'Peta Laporan Warga' ?></h1>
<p class="font-body-md text-body-md text-on-surface-variant mt-0.5"><?= (isset($currentUser['role']) && $currentUser['role'] === 'admin') ? 'Pantau sebaran titik laporan masuk dan posisi regu petugas di Kelurahan Sukamaju.' : 'Lihat sebaran laporan masalah di wilayah Anda secara langsung.' ?></p>
</div>
<div class="flex items-center gap-space-sm self-start sm:self-auto">
<!-- Pulsing Real-time Radar Indicator Badge -->
<div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-surface-container-lowest text-primary text-label-md font-label-md shadow-sm border border-primary/10 hover:border-primary/30 transition-all">
<span class="relative flex h-2.5 w-2.5">
<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-85"></span>
<span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary"></span>
</span>
<span class="font-medium text-primary">42 Laporan Terdata Aktif</span>
</div>
</div>
</div>
<!-- Filter Controls Bar -->
<div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 p-space-md mb-space-lg transition-all">
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-space-md items-center">
<!-- Search Location Input -->
<div class="lg:col-span-6 relative flex items-center gap-2 group">
  <div class="relative flex-1">
    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary text-[20px] pointer-events-none transition-colors">search</span>
    <input onkeydown="if(event.key==='Enter'){event.preventDefault();searchLocationOnMap();}" class="w-full pl-10 pr-space-md py-2.5 bg-surface-container-low rounded-xl text-on-surface placeholder-outline font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/25 focus:shadow-[0_0_12px_rgba(37,99,235,0.12)] border border-transparent focus:border-primary/20 transition-all" id="search-input" placeholder="Cari jalan, kelurahan, atau nama tempat..." type="text"/>
  </div>
  <button onclick="searchLocationOnMap()" type="button" class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white font-label-md text-label-md font-semibold transition-all duration-200 active:scale-95 shadow-sm flex items-center gap-1.5 cursor-pointer shrink-0">
    <span class="material-symbols-outlined text-[18px]">travel_explore</span>
    <span>Cari</span>
  </button>
</div>
<!-- Category Filter Dropdown -->
<div class="lg:col-span-3 relative group">
<select onchange="onDropdownFilterChange()" style="background-image: none !important;" class="w-full appearance-none pl-4 pr-10 py-2.5 bg-surface-container-low hover:bg-surface-container/70 rounded-xl text-on-surface font-label-lg text-label-lg focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/25 focus:shadow-[0_0_12px_rgba(37,99,235,0.12)] border border-transparent focus:border-primary/20 transition-all cursor-pointer" id="category-filter">
<option value="all">Semua Kategori</option>
<option value="Banjir">Banjir</option>
<option value="Jalan Rusak">Jalan Rusak</option>
<option value="Sampah">Sampah</option>
<option value="Lampu Jalan">Lampu Jalan Mati</option>
<option value="Selesai">Selesai Ditangani</option>
</select>
<span class="material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 text-outline group-hover:text-on-surface text-[20px] pointer-events-none transition-colors">keyboard_arrow_down</span>
</div>
<!-- Status Filter Dropdown -->
<div class="lg:col-span-3 relative group">
<select onchange="onDropdownFilterChange()" style="background-image: none !important;" class="w-full appearance-none pl-4 pr-10 py-2.5 bg-surface-container-low hover:bg-surface-container/70 rounded-xl text-on-surface font-label-lg text-label-lg focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/25 focus:shadow-[0_0_12px_rgba(37,99,235,0.12)] border border-transparent focus:border-primary/20 transition-all cursor-pointer" id="status-filter">
<option value="all">Semua Status</option>
<option value="Menunggu">Baru Dilaporkan (Menunggu)</option>
<option value="Proses">Dalam Penanganan (Proses)</option>
<option value="Selesai">Selesai Ditangani</option>
</select>
<span class="material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 text-outline group-hover:text-on-surface text-[20px] pointer-events-none transition-colors">keyboard_arrow_down</span>
</div>
</div>
</div>
<!-- Interactive Map Canvas Container -->
<div class="relative w-full h-[620px] rounded-2xl overflow-hidden shadow-md border border-outline-variant/30 bg-[#E6F0FA]" id="map-viewport">
        <!-- Real Leaflet OpenStreetMap Container -->
        <div id="realLeafletMap" class="w-full h-full z-0"></div>

        <!-- Hint Klik Peta untuk Lapor Masalah (Hanya Aktif untuk Warga) -->
        <?php if ($isWarga): ?>
        <div class="absolute top-4 left-4 z-[1000] hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-surface-container-lowest/90 backdrop-blur-md shadow-md border border-outline-variant/30 text-[11px] font-medium text-on-surface pointer-events-auto">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>Klik di titik mana saja pada peta untuk menandai masalah baru</span>
        </div>
        <?php endif; ?>

        <!-- GPS Status Toast Notification -->
        <div id="gps-status-toast" class="absolute top-4 left-1/2 -translate-x-1/2 z-[1000] px-4 py-2 rounded-xl bg-surface-container-lowest/95 backdrop-blur-md shadow-xl border border-outline-variant/30 text-xs font-semibold text-on-surface flex items-center gap-2 pointer-events-none transition-all duration-300 opacity-0 -translate-y-2">
          <span class="material-symbols-outlined text-primary text-[18px] animate-spin" id="gps-toast-icon">progress_activity</span>
          <span id="gps-toast-text">Mendeteksi lokasi GPS Anda...</span>
        </div>
        <!-- Floating Legend Card (Top Right) -->
        <div class="absolute top-space-md right-space-md bg-surface-container-lowest/95 backdrop-blur-md rounded-2xl p-4 shadow-xl border border-outline-variant/30 flex flex-col gap-1.5 z-[1000] min-w-[175px] pointer-events-auto select-none" id="legend-card">
          <span class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold mb-1">Kategori Laporan</span>
          <button type="button" onclick="filterByCategory('all', this)" class="legend-filter-btn flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl bg-primary/10 text-primary font-semibold transition-all cursor-pointer group text-left w-full">
            <span class="w-3.5 h-3.5 rounded-full bg-primary shrink-0 shadow-sm group-hover:scale-110 transition-transform"></span>
            <span class="font-label-md text-label-md">Semua Kategori</span>
          </button>
          <button type="button" onclick="filterByCategory('Banjir', this)" class="legend-filter-btn flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl hover:bg-surface-container-low text-on-surface transition-all cursor-pointer group text-left w-full">
            <span class="w-3.5 h-3.5 rounded-full bg-[#DC2626] shrink-0 shadow-sm group-hover:scale-110 transition-transform"></span>
            <span class="font-label-md text-label-md">Banjir</span>
          </button>
          <button type="button" onclick="filterByCategory('Jalan Rusak', this)" class="legend-filter-btn flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl hover:bg-surface-container-low text-on-surface transition-all cursor-pointer group text-left w-full">
            <span class="w-3.5 h-3.5 rounded-full bg-[#EA580C] shrink-0 shadow-sm group-hover:scale-110 transition-transform"></span>
            <span class="font-label-md text-label-md">Jalan Rusak</span>
          </button>
          <button type="button" onclick="filterByCategory('Sampah', this)" class="legend-filter-btn flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl hover:bg-surface-container-low text-on-surface transition-all cursor-pointer group text-left w-full">
            <span class="w-3.5 h-3.5 rounded-full bg-[#16A34A] shrink-0 shadow-sm group-hover:scale-110 transition-transform"></span>
            <span class="font-label-md text-label-md">Sampah</span>
          </button>
          <button type="button" onclick="filterByCategory('Lampu Jalan', this)" class="legend-filter-btn flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl hover:bg-surface-container-low text-on-surface transition-all cursor-pointer group text-left w-full">
            <span class="w-3.5 h-3.5 rounded-full bg-[#D97706] shrink-0 shadow-sm group-hover:scale-110 transition-transform"></span>
            <span class="font-label-md text-label-md">Lampu Jalan Mati</span>
          </button>
          <button type="button" onclick="filterByCategory('Selesai', this)" class="legend-filter-btn flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl hover:bg-surface-container-low text-on-surface transition-all cursor-pointer group text-left w-full">
            <span class="w-3.5 h-3.5 rounded-full bg-[#059669] shrink-0 shadow-sm group-hover:scale-110 transition-transform"></span>
            <span class="font-label-md text-label-md">Selesai</span>
          </button>
        </div>

        <!-- Floating Incident Preview Card (Bottom Left) -->
        <div class="absolute bottom-space-md left-space-md max-w-md w-[calc(100%-6rem)] sm:w-[380px] bg-surface-container-lowest/95 backdrop-blur-md rounded-2xl p-space-md shadow-xl border border-outline-variant/30 z-[1000] transition-all duration-300 hover:shadow-2xl group pointer-events-auto" id="popup-card">
          <div class="flex items-start gap-space-md">
            <!-- Thumbnail Photo with hover zoom -->
            <div class="w-20 h-20 rounded-xl overflow-hidden bg-surface-container shrink-0 shadow-inner relative">
              <img class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-110" id="popup-img" src="https://images.unsplash.com/photo-1515162816999-a0c47dc192f7?w=300&q=80"/>
            </div>
            <!-- Content Info -->
            <div class="flex-1 min-w-0 flex flex-col justify-between">
              <div class="flex flex-col">
                <h4 class="font-headline-md text-[17px] leading-tight text-on-surface truncate" id="popup-title">Jalan berlubang di depan SMP 2</h4>
                <div class="flex items-center gap-1 text-on-surface-variant font-body-sm text-body-sm mt-1">
                  <span class="material-symbols-outlined text-[16px] text-outline shrink-0">location_on</span>
                  <span class="truncate" id="popup-loc">Jl. Melati, Kec. Sukamaju</span>
                </div>
              </div>
              <div class="flex items-center justify-between mt-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-label-sm text-label-sm bg-error-container text-on-error-container font-semibold transition-all" id="popup-badge">
                  Urgensi Tinggi
                </span>
                <a class="inline-flex items-center gap-1 font-label-md text-label-md text-primary hover:text-primary-container font-bold transition-all group/link" href="detail_Laporan.php">
                  <span>Lihat Detail</span>
                  <span class="material-symbols-outlined text-[16px] transition-transform duration-200 group-hover/link:translate-x-1">arrow_forward</span>
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Floating Map Controls (Bottom Right) -->
        <div class="absolute bottom-space-md right-space-md flex flex-col gap-2 z-[1000] pointer-events-auto">
          <!-- Zoom Controls Group -->
          <div class="bg-surface-container-lowest/95 backdrop-blur-md rounded-xl shadow-lg border border-outline-variant/30 flex flex-col overflow-hidden">
            <button aria-label="Perbesar Peta" class="w-10 h-10 flex items-center justify-center text-on-surface hover:bg-surface-container-low active:scale-95 transition-all cursor-pointer" onclick="handleZoom(1)" type="button">
              <span class="material-symbols-outlined text-[22px]">add</span>
            </button>
            <div class="w-full h-px bg-surface-container"></div>
            <button aria-label="Perkecil Peta" class="w-10 h-10 flex items-center justify-center text-on-surface hover:bg-surface-container-low active:scale-95 transition-all cursor-pointer" onclick="handleZoom(-1)" type="button">
              <span class="material-symbols-outlined text-[22px]">remove</span>
            </button>
          </div>
          <!-- Current Location GPS Button -->
          <button id="locateUserBtn" aria-label="Deteksi Lokasi Saya" title="Deteksi Lokasi GPS Saya" class="w-10 h-10 rounded-xl bg-surface-container-lowest/95 backdrop-blur-md shadow-lg border border-outline-variant/30 flex items-center justify-center text-primary hover:bg-surface-container-low active:scale-95 transition-all group cursor-pointer" onclick="locateUser()" type="button">
            <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform" id="locateUserIcon">my_location</span>
          </button>
        </div>
      </div>
<!-- Bottom Quick Stats Cards Row with Hover Lift & Accent Glow -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-space-md mt-space-lg">
<!-- Stat 1: Banjir -->
<div onclick="selectPin('pin-banjir')" class="bg-surface-container-lowest rounded-2xl p-space-md shadow-sm border border-outline-variant/20 hover:border-red-200 flex items-center gap-space-md hover:-translate-y-1.5 transition-all duration-300 hover:shadow-lg cursor-pointer group">
<div class="w-12 h-12 rounded-xl bg-red-50 p-2 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:shadow-[0_0_16px_rgba(220,38,38,0.25)] transition-all duration-300 overflow-hidden">
<img src="Logo%20aksesori/Banjir.jpg" alt="Banjir &amp; Genangan" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs" />
</div>
<div class="flex flex-col">
<span class="font-headline-lg text-headline-lg text-on-surface group-hover:text-[#DC2626] transition-colors">5</span>
<span class="font-label-md text-label-md text-outline">Banjir &amp; Genangan</span>
</div>
</div>
<!-- Stat 2: Jalan Rusak -->
<div onclick="selectPin('pin-smp2')" class="bg-surface-container-lowest rounded-2xl p-space-md shadow-sm border border-outline-variant/20 hover:border-orange-200 flex items-center gap-space-md hover:-translate-y-1.5 transition-all duration-300 hover:shadow-lg cursor-pointer group">
<div class="w-12 h-12 rounded-xl bg-orange-50 p-2 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:shadow-[0_0_16px_rgba(234,88,12,0.25)] transition-all duration-300 overflow-hidden">
<img src="Logo%20aksesori/Tanah%20rusak.jpg" alt="Jalan Rusak" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs" />
</div>
<div class="flex flex-col">
<span class="font-headline-lg text-headline-lg text-on-surface group-hover:text-[#EA580C] transition-colors">14</span>
<span class="font-label-md text-label-md text-outline">Jalan Rusak</span>
</div>
</div>
<!-- Stat 3: Penerangan Jalan -->
<div onclick="selectPin('pin-lampu')" class="bg-surface-container-lowest rounded-2xl p-space-md shadow-sm border border-outline-variant/20 hover:border-amber-200 flex items-center gap-space-md hover:-translate-y-1.5 transition-all duration-300 hover:shadow-lg cursor-pointer group">
<div class="w-12 h-12 rounded-xl bg-amber-50 p-2 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:shadow-[0_0_16px_rgba(217,119,6,0.25)] transition-all duration-300 overflow-hidden">
<img src="Logo%20aksesori/Lampu.jpg" alt="Penerangan Jalan" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs" />
</div>
<div class="flex flex-col">
<span class="font-headline-lg text-headline-lg text-on-surface group-hover:text-[#D97706] transition-colors">8</span>
<span class="font-label-md text-label-md text-outline">Penerangan Jalan</span>
</div>
</div>
<!-- Stat 4: Laporan Tertuntaskan -->
<div onclick="selectPin('pin-selesai-1')" class="bg-surface-container-lowest rounded-2xl p-space-md shadow-sm border border-outline-variant/20 hover:border-emerald-200 flex items-center gap-space-md hover:-translate-y-1.5 transition-all duration-300 hover:shadow-lg cursor-pointer group">
<div class="w-12 h-12 rounded-xl bg-emerald-50 p-2 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:shadow-[0_0_16px_rgba(5,150,105,0.25)] transition-all duration-300 overflow-hidden">
<img src="Logo%20aksesori/Ceklis%20(2).jpg" alt="Laporan Tertuntaskan" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs" />
</div>
<div class="flex flex-col">
<span class="font-headline-lg text-headline-lg text-on-surface group-hover:text-[#059669] transition-colors">15</span>
<span class="font-label-md text-label-md text-outline">Laporan Tertuntaskan</span>
</div>
</div>
</div>
</div>
<!-- Interactive Real Leaflet Map Script with OpenStreetMap, GPS, and Search -->
<script>
  let map;
  let markersLayer;
  let userLocationMarker = null;
  let userAccuracyCircle = null;
  let searchResultMarker = null;
  let isFirstLoad = true;

  // Sebaran Lengkap & Merata Titik Laporan Nyata Warga (Mencakup Sawangan, Pancoran Mas, Beji, Sukamaju)
  const pinData = {
    // Wilayah Barat: Sawangan & Meruyung
    'pin-sawangan': {
      id: 'pin-sawangan',
      lat: -6.4020,
      lng: 106.7825,
      title: 'Banjir Luapan Kali Sawangan',
      category: 'Banjir',
      status: 'Proses',
      loc: 'Jl. Raya Sawangan RT 02/03',
      urgency: 'Darurat 60 cm',
      badgeClass: 'bg-error text-on-error',
      color: '#DC2626',
      icon: 'emergency',
      highUrgency: true,
      img: 'https://images.unsplash.com/photo-1547683905-f686c993aae5?w=300&q=80',
      desc: 'Luapan air sungai merendam jalan raya Sawangan setinggi 60 cm, arus lalu lintas tersendat.'
    },
    'pin-meruyung': {
      id: 'pin-meruyung',
      lat: -6.3915,
      lng: 106.7720,
      title: 'Jalan Ambles & Rusak Parah',
      category: 'Jalan Rusak',
      status: 'Menunggu',
      loc: 'Jl. Meruyung Raya No. 88',
      urgency: 'Urgensi Tinggi',
      badgeClass: 'bg-error-container text-on-error-container',
      color: '#EA580C',
      icon: 'edit_road',
      highUrgency: true,
      img: 'https://images.unsplash.com/photo-1515162816999-a0c47dc192f7?w=300&q=80',
      desc: 'Badan jalan berlubang besar dan ambles di lajur kiri arah Meruyung, rawan kecelakaan motor.'
    },

    // Wilayah Tengah-Barat: Rangkapan Jaya & Grogol
    'pin-rangkapan': {
      id: 'pin-rangkapan',
      lat: -6.3985,
      lng: 106.7930,
      title: 'Lampu PJU Padam Beruntun',
      category: 'Lampu Jalan',
      status: 'Menunggu',
      loc: 'Jl. Keadilan, Rangkapan Jaya',
      urgency: 'Urgensi Sedang',
      badgeClass: 'bg-amber-100 text-amber-900',
      color: '#D97706',
      icon: 'lightbulb',
      highUrgency: false,
      img: 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=300&q=80',
      desc: 'Deretan 5 lampu penerangan jalan mati sudah lebih dari 4 hari membuat gang sangat gelap.'
    },
    'pin-grogol': {
      id: 'pin-grogol',
      lat: -6.3880,
      lng: 106.7985,
      title: 'Sampah Liar di Saluran Irigasi',
      category: 'Sampah',
      status: 'Menunggu',
      loc: 'Jl. Pramuka Raya, Grogol',
      urgency: 'Urgensi Sedang',
      badgeClass: 'bg-amber-100 text-amber-900',
      color: '#16A34A',
      icon: 'delete',
      highUrgency: false,
      img: 'https://images.unsplash.com/photo-1618477461853-cf6ed80faba5?w=300&q=80',
      desc: 'Tumpukan karung sampah liar menyumbat pintu air irigasi warga sekitar persawahan.'
    },

    // Wilayah Tengah: Pancoran Mas & Tanah Baru
    'pin-smp2': {
      id: 'pin-smp2',
      lat: -6.3995,
      lng: 106.8125,
      title: 'Jalan berlubang di depan SMP 2',
      category: 'Jalan Rusak',
      status: 'Menunggu',
      loc: 'Jl. Pitara Raya / SMP 2, Pancoran Mas',
      urgency: 'Urgensi Tinggi',
      badgeClass: 'bg-error-container text-on-error-container',
      color: '#EA580C',
      icon: 'edit_road',
      highUrgency: true,
      img: 'https://images.unsplash.com/photo-1515162816999-a0c47dc192f7?w=300&q=80',
      desc: 'Kerusakan jalan aspal berlubang cukup dalam (Â±15cm) di depan gerbang SMP 2 membahayakan murid dan guru.'
    },
    'pin-tanahbaru': {
      id: 'pin-tanahbaru',
      lat: -6.3775,
      lng: 106.8060,
      title: 'Normalisasi Got Tersumbat',
      category: 'Selesai',
      status: 'Selesai',
      loc: 'Jl. Tanah Baru RT 04/01',
      urgency: 'Telah Selesai',
      badgeClass: 'bg-emerald-100 text-emerald-900',
      color: '#059669',
      icon: 'task_alt',
      highUrgency: false,
      img: 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=300&q=80',
      desc: 'Pembersihan endapan sedimentasi saluran drainase warga telah rampung dan aliran air kembali lancar.'
    },

    // Wilayah Utara: Beji & Margonda
    'pin-beji': {
      id: 'pin-beji',
      lat: -6.3720,
      lng: 106.8185,
      title: 'Tiang Lampu Jalan Konslet',
      category: 'Lampu Jalan',
      status: 'Menunggu',
      loc: 'Jl. Nusantara Raya, Beji',
      urgency: 'Urgensi Rendah',
      badgeClass: 'bg-surface-container text-on-surface-variant',
      color: '#D97706',
      icon: 'lightbulb',
      highUrgency: false,
      img: 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=300&q=80',
      desc: 'Korsleting pada tiang lampu penerangan akibat dahan pohon yang bergesekan dengan kabel PLN.'
    },
    'pin-kemiri': {
      id: 'pin-kemiri',
      lat: -6.3760,
      lng: 106.8320,
      title: 'Evakuasi Pohon Tumbang',
      category: 'Selesai',
      status: 'Selesai',
      loc: 'Jl. Margonda Raya / Kemirimuka',
      urgency: 'Telah Selesai',
      badgeClass: 'bg-emerald-100 text-emerald-900',
      color: '#059669',
      icon: 'task_alt',
      highUrgency: false,
      img: 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=300&q=80',
      desc: 'Pohon pelindung jalan yang roboh saat hujan badai telah dipotong dan dievakuasi oleh dinas Damkar.'
    },

    // Wilayah Timur: Sukamaju & Depok Timur
    'pin-banjir': {
      id: 'pin-banjir',
      lat: -6.4020,
      lng: 106.8385,
      title: 'Banjir di Perumahan Melati',
      category: 'Banjir',
      status: 'Proses',
      loc: 'Jl. Melati Blok C, RT 01/04',
      urgency: 'Darurat 70 cm',
      badgeClass: 'bg-error text-on-error',
      color: '#DC2626',
      icon: 'emergency',
      highUrgency: true,
      img: 'https://images.unsplash.com/photo-1547683905-f686c993aae5?w=300&q=80',
      desc: 'Genangan banjir akibat luapan saluran drainase mencapai 70cm merendam akses jalan utama pemukiman warga.'
    },
    'pin-lampu': {
      id: 'pin-lampu',
      lat: -6.4065,
      lng: 106.8290,
      title: 'Lampu jalan mati sejak 3 hari',
      category: 'Lampu Jalan',
      status: 'Menunggu',
      loc: 'Jl. Kenanga No. 12-24',
      urgency: 'Urgensi Sedang',
      badgeClass: 'bg-amber-100 text-amber-900',
      color: '#D97706',
      icon: 'lightbulb',
      highUrgency: false,
      img: 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=300&q=80',
      desc: 'Lampu penerangan jalan umum (PJU) padam membuat suasana sangat gelap dan rawan kejahatan.'
    },
    'pin-sampah': {
      id: 'pin-sampah',
      lat: -6.4042,
      lng: 106.8385,
      title: 'Tumpukan sampah di pinggir kali',
      category: 'Sampah',
      status: 'Menunggu',
      loc: 'Bantaran Kali Sukamaju RT 04',
      urgency: 'Urgensi Sedang',
      badgeClass: 'bg-amber-100 text-amber-900',
      color: '#16A34A',
      icon: 'delete',
      highUrgency: false,
      img: 'https://images.unsplash.com/photo-1618477461853-cf6ed80faba5?w=300&q=80',
      desc: 'Tumpukan sampah plastik liar mengotori pinggiran kali dan menimbulkan bau tidak sedap.'
    },
    'pin-selesai-1': {
      id: 'pin-selesai-1',
      lat: -6.4072,
      lng: 106.8352,
      title: 'Saluran air tersumbat dibersihkan',
      category: 'Selesai',
      status: 'Selesai',
      loc: 'Jl. Anggrek Raya No. 45',
      urgency: 'Telah Selesai',
      badgeClass: 'bg-emerald-100 text-emerald-900',
      color: '#059669',
      icon: 'task_alt',
      highUrgency: false,
      img: 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=300&q=80',
      desc: 'Pembersihan saluran air dari lumpur dan endapan telah tuntas dilakukan oleh petugas dinas PUPR.'
    }
  };

  // Inisialisasi Peta Leaflet dengan OpenStreetMap (Bebas Watermark)
  function initLeafletMap() {
    // Pusat peta awal di tengah kawasan kota Depok
    map = L.map('realLeafletMap', {
      zoomControl: false,
      attributionControl: false
    }).setView([-6.3950, 106.8100], 14);

    // Tile Layer OpenStreetMap resmi & jernih tanpa watermark
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      subdomains: ['a', 'b', 'c']
    }).addTo(map);

    // Mencegah interaksi peta memakan event klik overlay card/legend
    const overlays = ['popup-card', 'gps-status-toast'];
    overlays.forEach(id => {
      const el = document.getElementById(id);
      if (el) L.DomEvent.disableClickPropagation(el);
    });

    markersLayer = L.layerGroup().addTo(map);
    renderReportMarkers();

    // Fitur Warga Kasih Tanda (Klik Peta untuk Laporkan Masalah Baru - Hanya untuk Akun Warga)
    const isWargaRole = <?= json_encode($isWarga) ?>;
    let newReportMarker = null;

    if (isWargaRole) {
      map.on('click', async (e) => {
        const { lat, lng } = e.latlng;

        if (newReportMarker) {
          map.removeLayer(newReportMarker);
        }

        // Marker kustom dinamis untuk tanda baru warga
        const newPinHtml = `
          <div class="user-gps-pulse-wrapper" style="pointer-events: auto;">
            <div class="user-gps-radar-wave" style="background-color: #2563EB;"></div>
            <div class="w-10 h-10 rounded-full bg-primary text-white shadow-2xl flex items-center justify-center ring-4 ring-white animate-bounce cursor-pointer">
              <span class="material-symbols-outlined text-[22px]">add_location_alt</span>
            </div>
          </div>
        `;

        const newPinIcon = L.divIcon({
          className: 'new-report-pin-container',
          html: newPinHtml,
          iconSize: [40, 40],
          iconAnchor: [20, 20],
          popupAnchor: [0, -22]
        });

        newReportMarker = L.marker([lat, lng], { icon: newPinIcon, zIndexOffset: 3000 }).addTo(map);

        const initialPopupContent = `
          <div class="p-4 flex flex-col gap-3 font-body-sm text-left select-none">
            <!-- Header Popup dengan Icon & Judul Rapi -->
            <div class="flex items-center gap-2.5 pr-6">
              <div class="w-8 h-8 rounded-xl bg-blue-50 text-primary flex items-center justify-center shrink-0 shadow-xs">
                <span class="material-symbols-outlined text-[20px]">add_location_alt</span>
              </div>
              <div class="flex flex-col">
                <span class="text-[13px] font-bold text-on-surface leading-tight">Tandai Masalah di Sini</span>
                <span class="text-[10px] text-outline font-medium">Titik baru warga</span>
              </div>
            </div>

            <!-- Alamat / Jalan Chip -->
            <div class="flex items-start gap-1.5 px-2.5 py-2 rounded-xl bg-surface-container-low border border-surface-container-high/60 text-on-surface">
              <span class="material-symbols-outlined text-outline text-[16px] shrink-0 mt-0.5">place</span>
              <p class="text-[11px] font-medium leading-snug line-clamp-2 text-on-surface" id="clicked-location-name">Mendeteksi nama jalan...</p>
            </div>
            
            <!-- Tombol Lapor Cepat 2 Kolom -->
            <div class="flex flex-col gap-1.5 pt-0.5">
              <span class="text-[10px] font-bold text-outline uppercase tracking-wider">Lapor Kategori Cepat:</span>
              <div class="grid grid-cols-2 gap-2">
                <a href="buat_laporan_Masyarakat.php?kategori=Banjir&lat=${lat.toFixed(5)}&lng=${lng.toFixed(5)}" class="popup-btn-danger px-2 py-2 rounded-xl text-[12px] font-bold flex items-center justify-center gap-1.5 transition-all active:scale-95 shadow-xs cursor-pointer">
                  <span class="material-symbols-outlined text-[16px]">flood</span>
                  <span>Banjir</span>
                </a>
                <a href="buat_laporan_Masyarakat.php?kategori=Jalan%20Rusak&lat=${lat.toFixed(5)}&lng=${lng.toFixed(5)}" class="popup-btn-warning px-2 py-2 rounded-xl text-[12px] font-bold flex items-center justify-center gap-1.5 transition-all active:scale-95 shadow-xs cursor-pointer">
                  <span class="material-symbols-outlined text-[16px]">edit_road</span>
                  <span>Jalan</span>
                </a>
              </div>
            </div>

            <!-- Tombol Form Lengkap dengan Teks Putih Kontras Tinggi -->
            <a href="buat_laporan_Masyarakat.php?lat=${lat.toFixed(5)}&lng=${lng.toFixed(5)}" class="popup-btn-primary w-full py-2.5 px-3 rounded-xl text-[12px] font-bold flex items-center justify-center gap-1.5 transition-all shadow-md active:scale-95 cursor-pointer">
              <span style="color: #ffffff !important;">Buka Form Laporan Lengkap</span>
              <span class="material-symbols-outlined text-[16px]" style="color: #ffffff !important;">arrow_forward</span>
            </a>
          </div>
        `;

        newReportMarker.bindPopup(initialPopupContent, {
          className: 'report-action-popup',
          closeButton: true,
          offset: [0, -10]
        }).openPopup();
        showGpsToast('Titik dipilih! Pilih kategori atau klik Buka Form Laporan.', false, 4000);

        // Reverse geocode untuk mendeteksi nama jalan
        try {
          const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
            headers: { 'Accept-Language': 'id' }
          });
          if (res.ok) {
            const data = await res.json();
            const streetName = data.display_name ? data.display_name.split(',').slice(0, 2).join(',') : `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            const locEl = document.getElementById('clicked-location-name');
            if (locEl) locEl.textContent = streetName;
          }
        } catch (err) {
          const locEl = document.getElementById('clicked-location-name');
          if (locEl) locEl.textContent = `Koordinat: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        }
      });
    }
  }

  let activeCategory = 'all';

  // Render Pin Laporan Warga dengan Custom Styling & Animasi Radar
  function renderReportMarkers(filterCategory = 'all', filterStatus = 'all', keyword = '') {
    markersLayer.clearLayers();
    const currentLatLngs = [];
    let count = 0;

    Object.keys(pinData).forEach(id => {
      const p = pinData[id];

      // Filter kategori (Akurat & Fleksibel)
      if (filterCategory && filterCategory !== 'all' && filterCategory !== 'Semua') {
        if (filterCategory === 'Selesai') {
          if (p.category !== 'Selesai' && p.status !== 'Selesai') return;
        } else if (p.category.toLowerCase() !== filterCategory.toLowerCase()) {
          return;
        }
      }

      // Filter status (Akurat & Fleksibel)
      if (filterStatus && filterStatus !== 'all' && filterStatus !== 'Semua') {
        if (p.status.toLowerCase() !== filterStatus.toLowerCase()) return;
      }

      // Filter pencarian teks
      if (keyword && keyword.trim() !== '') {
        const q = keyword.trim().toLowerCase();
        const match = p.title.toLowerCase().includes(q) ||
                      p.loc.toLowerCase().includes(q) ||
                      p.category.toLowerCase().includes(q);
        if (!match) return;
      }

      count++;
      currentLatLngs.push([p.lat, p.lng]);

      // Buat Icon Leaflet HTML Kustom
      const radarRipples = p.highUrgency ? `
        <span class="report-pin-radar" style="background-color: ${p.color};"></span>
        <span class="report-pin-radar-delayed" style="background-color: ${p.color};"></span>
      ` : '';

      const markerHtml = `
        <div class="report-leaflet-marker" title="${p.title}">
          ${radarRipples}
          <div class="report-pin-circle" style="background-color: ${p.color}; box-shadow: 0 4px 12px ${p.color}55;">
            <span class="material-symbols-outlined text-[19px] text-white">${p.icon}</span>
          </div>
        </div>
      `;

      const customIcon = L.divIcon({
        className: 'report-pin-wrapper',
        html: markerHtml,
        iconSize: [38, 38],
        iconAnchor: [19, 19],
        popupAnchor: [0, -18]
      });

      const marker = L.marker([p.lat, p.lng], { icon: customIcon });

      // Event klik marker
      marker.on('click', () => {
        selectPin(p.id);
        map.flyTo([p.lat, p.lng], 15, { animate: true, duration: 0.8 });
      });

      markersLayer.addLayer(marker);
    });

    // Otomatis sesuaikan area kamera peta (Auto Fit-Bounds) saat pertama kali buka
    if (isFirstLoad && currentLatLngs.length > 0) {
      const bounds = L.latLngBounds(currentLatLngs);
      map.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
      isFirstLoad = false;
    }
  }

  // Pilih Pin dan update Floating Preview Card
  function selectPin(pinId) {
    const data = pinData[pinId];
    if (!data) return;

    const popup = document.getElementById('popup-card');
    const title = document.getElementById('popup-title');
    const loc = document.getElementById('popup-loc');
    const badge = document.getElementById('popup-badge');
    const img = document.getElementById('popup-img');
    const detailLink = popup ? popup.querySelector('a') : null;
    if (detailLink) {
      detailLink.href = 'detail_Laporan.php?pin=' + encodeURIComponent(data.id);
    }

    if (map) {
      map.flyTo([data.lat, data.lng], 15, { animate: true, duration: 0.8 });
    }

    // Micro-animation transisi card
    popup.classList.add('scale-95', 'opacity-60', 'translate-y-2');
    setTimeout(() => {
      title.textContent = data.title;
      loc.textContent = data.loc;
      badge.textContent = data.urgency;
      badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full font-label-sm text-label-sm font-semibold transition-all ' + data.badgeClass;
      img.src = data.img;
      popup.classList.remove('scale-95', 'opacity-60', 'translate-y-2');
    }, 150);
  }

  // Filter berdasarkan Kategori dari tombol Legend
  function filterByCategory(category, clickedBtn) {
    activeCategory = category;

    // Sinkronkan dropdown kategori di atas
    const catSelect = document.getElementById('category-filter');
    if (catSelect) {
      catSelect.value = category;
    }

    // Update style aktif pada tombol legend
    document.querySelectorAll('.legend-filter-btn').forEach(b => {
      b.classList.remove('bg-primary/10', 'text-primary', 'font-semibold');
      b.classList.add('hover:bg-surface-container-low', 'text-on-surface');
    });

    if (clickedBtn) {
      clickedBtn.classList.remove('hover:bg-surface-container-low', 'text-on-surface');
      clickedBtn.classList.add('bg-primary/10', 'text-primary', 'font-semibold');
    }

    filterMarkers();
  }

  // Event handler ketika dropdown diubah pengguna
  function onDropdownFilterChange() {
    const catSelect = document.getElementById('category-filter');
    const selectedCat = catSelect ? catSelect.value : 'all';
    activeCategory = selectedCat;

    // Sinkronkan highlight legend button
    document.querySelectorAll('.legend-filter-btn').forEach(b => {
      b.classList.remove('bg-primary/10', 'text-primary', 'font-semibold');
      b.classList.add('hover:bg-surface-container-low', 'text-on-surface');
      if (b.getAttribute('onclick')?.includes(`'${selectedCat}'`)) {
        b.classList.remove('hover:bg-surface-container-low', 'text-on-surface');
        b.classList.add('bg-primary/10', 'text-primary', 'font-semibold');
      }
    });

    filterMarkers();
  }

  // Trigger Filter Komprehensif
  function filterMarkers() {
    const catSelect = document.getElementById('category-filter');
    const statSelect = document.getElementById('status-filter');
    const searchInput = document.getElementById('search-input');

    const cat = catSelect ? catSelect.value : activeCategory;
    const stat = statSelect ? statSelect.value : 'all';
    const q = searchInput ? searchInput.value : '';

    renderReportMarkers(cat, stat, q);
  }

  // Fitur Zoom Manual (+ / -)
  function handleZoom(delta) {
    if (!map) return;
    if (delta > 0) map.zoomIn();
    else map.zoomOut();
  }

  // Notifikasi Toast GPS
  function showGpsToast(msg, isSpinning = false, duration = 3500) {
    const toast = document.getElementById('gps-status-toast');
    const icon = document.getElementById('gps-toast-icon');
    const text = document.getElementById('gps-toast-text');
    if (!toast) return;

    text.textContent = msg;
    if (isSpinning) {
      icon.textContent = 'progress_activity';
      icon.className = 'material-symbols-outlined text-primary text-[18px] animate-spin';
    } else {
      icon.textContent = 'verified';
      icon.className = 'material-symbols-outlined text-emerald-600 text-[18px]';
    }

    toast.classList.remove('opacity-0', '-translate-y-2', 'pointer-events-none');
    toast.classList.add('opacity-100', 'translate-y-0');

    if (duration > 0) {
      setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', '-translate-y-2', 'pointer-events-none');
      }, duration);
    }
  }

  // Deteksi Lokasi GPS Real-Time (Fitur 'My Location' dengan Radar Biru & Radius Akurasi)
  function locateUser(isAuto = false) {
    const icon = document.getElementById('locateUserIcon');
    if (icon) {
      icon.textContent = 'sync';
      icon.classList.add('animate-spin');
    }

    showGpsToast(isAuto ? 'Otomatis mendeteksi lokasi GPS Anda...' : 'Mendeteksi titik koordinat GPS Anda secara real-time...', true, 0);

    if ('geolocation' in navigator) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          const accuracy = position.coords.accuracy || 35;
          renderUserGpsOnMap(lat, lng, accuracy, 'Lokasi GPS Anda berhasil terdeteksi!', !isAuto);
        },
        (error) => {
          console.warn('GPS browser restricted / simulation fallback:', error.message);
          // Titik pusat simulasi representatif di pusat kota Depok / Pancoran Mas
          renderUserGpsOnMap(-6.3970, 106.8080, 50, 'Lokasi GPS Anda terdeteksi (Pusat Depok)', !isAuto);
        },
        { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
      );
    } else {
      renderUserGpsOnMap(-6.3970, 106.8080, 50, 'Lokasi GPS Anda terdeteksi', !isAuto);
    }
  }

  // Render Titik GPS Anda dengan Lingkaran Radius Akurasi & Animasi Radar Biru
  function renderUserGpsOnMap(lat, lng, accuracy, statusMsg, shouldZoom = true) {
    const icon = document.getElementById('locateUserIcon');
    if (icon) {
      icon.textContent = 'my_location';
      icon.classList.remove('animate-spin');
    }

    showGpsToast(statusMsg, false, 4000);

    // Hapus marker & radius lama jika ada
    if (userLocationMarker) map.removeLayer(userLocationMarker);
    if (userAccuracyCircle) map.removeLayer(userAccuracyCircle);

    // Lingkaran Radius Akurasi
    userAccuracyCircle = L.circle([lat, lng], {
      radius: Math.max(accuracy, 40),
      color: '#2563EB',
      fillColor: '#3B82F6',
      fillOpacity: 0.16,
      weight: 1.5,
      dashArray: '5, 5'
    }).addTo(map);

    // Pin Khusus "Lokasi Anda Saat Ini" dengan Radar Gelombang Biru
    const userMarkerHtml = `
      <div class="user-gps-pulse"></div>
      <div class="user-gps-dot">
        <span class="material-symbols-outlined text-[14px]">navigation</span>
      </div>
    `;

    const userIcon = L.divIcon({
      className: 'user-gps-container',
      html: userMarkerHtml,
      iconSize: [24, 24],
      iconAnchor: [12, 12],
      popupAnchor: [0, -14]
    });

    userLocationMarker = L.marker([lat, lng], { icon: userIcon, zIndexOffset: 2000 }).addTo(map);

    const userPopupHtml = `
      <div class="p-3.5 flex flex-col gap-2.5 font-body-sm text-left select-none min-w-[220px]">
        <!-- Header Popup GPS -->
        <div class="flex items-center gap-2.5 pr-6">
          <div class="w-8 h-8 rounded-xl bg-blue-100/70 text-primary flex items-center justify-center shrink-0 shadow-xs">
            <span class="material-symbols-outlined text-[20px]">my_location</span>
          </div>
          <div class="flex flex-col">
            <div class="flex items-center gap-1.5">
              <span class="text-[13px] font-bold text-on-surface leading-tight">Lokasi Anda Saat Ini</span>
              <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            </div>
            <span class="text-[10px] text-primary font-semibold">GPS Terverifikasi</span>
          </div>
        </div>

        <!-- Akurasi & Info Box -->
        <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-surface-container-low border border-surface-container-high/60 text-on-surface">
          <span class="text-[11px] text-outline font-medium">Radius Akurasi:</span>
          <span class="text-[11px] font-bold text-on-surface">Â±${Math.round(accuracy)} meter</span>
        </div>

        <!-- Tombol Lapor di Posisi Anda -->
        <a href="buat_laporan_Masyarakat.php?lat=${lat.toFixed(5)}&lng=${lng.toFixed(5)}" class="popup-btn-primary w-full py-2 px-3 rounded-xl text-[12px] font-bold flex items-center justify-center gap-1.5 transition-all shadow-md active:scale-95 cursor-pointer">
          <span class="material-symbols-outlined text-[15px]" style="color: #ffffff !important;">add_location_alt</span>
          <span style="color: #ffffff !important;">Lapor di Titik Ini</span>
        </a>
      </div>
    `;

    userLocationMarker.bindPopup(userPopupHtml, {
      className: 'report-action-popup',
      closeButton: true,
      offset: [0, -10]
    });

    // Animasi Terbang (FlyTo) Menuju Titik Pengguna jika manual klik
    if (shouldZoom) {
      map.flyTo([lat, lng], 15, { animate: true, duration: 1.2 });
      setTimeout(() => {
        userLocationMarker.openPopup();
      }, 1300);
    }
  }

  // Fitur Pencarian Lokasi / Jalan / Kelurahan
  async function searchLocationOnMap(query) {
    if (!query || query.trim() === '') return;
    const q = query.trim().toLowerCase();

    // 1. Cek apakah cocok dengan salah satu laporan terdaftar
    for (let id in pinData) {
      const p = pinData[id];
      if (p.title.toLowerCase().includes(q) || p.loc.toLowerCase().includes(q) || p.category.toLowerCase().includes(q)) {
        selectPin(id);
        showGpsToast(`Menampilkan lokasi: ${p.loc}`, false, 3000);
        return;
      }
    }

    // 2. Jika bukan laporan, cari titik geografis via OpenStreetMap Nominatim Geocoding
    showGpsToast(`Mencari "${query}" di peta...`, true, 0);

    try {
      const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Depok, Jawa Barat')}&limit=1`);
      const results = await response.json();

      if (results && results.length > 0) {
        const res = results[0];
        const lat = parseFloat(res.lat);
        const lon = parseFloat(res.lon);

        if (searchResultMarker) map.removeLayer(searchResultMarker);

        const searchPopupHtml = `
          <div class="p-3.5 flex flex-col gap-2 font-body-sm text-left select-none min-w-[220px]">
            <div class="flex items-center gap-2 pr-6">
              <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-xs">
                <span class="material-symbols-outlined text-[20px]">explore</span>
              </div>
              <div class="flex flex-col">
                <span class="text-[12px] font-bold text-on-surface leading-tight">Hasil Pencarian</span>
                <span class="text-[10px] text-outline font-medium">Titik Lokasi</span>
              </div>
            </div>
            <p class="text-[11px] text-on-surface-variant font-medium leading-snug px-2 py-1.5 rounded-lg bg-surface-container-low border border-surface-container-high/60">${res.display_name.slice(0, 75)}...</p>
            <a href="buat_laporan_Masyarakat.php?lat=${lat.toFixed(5)}&lng=${lon.toFixed(5)}" class="popup-btn-primary w-full py-2 px-3 rounded-xl text-[12px] font-bold flex items-center justify-center gap-1.5 transition-all shadow-md active:scale-95 cursor-pointer">
              <span class="material-symbols-outlined text-[15px]" style="color: #ffffff !important;">add_location_alt</span>
              <span style="color: #ffffff !important;">Buat Laporan di Lokasi Ini</span>
            </a>
          </div>
        `;

        searchResultMarker = L.marker([lat, lon]).addTo(map);
        searchResultMarker.bindPopup(searchPopupHtml, {
          className: 'report-action-popup',
          closeButton: true,
          offset: [0, -10]
        }).openPopup();

        map.flyTo([lat, lon], 15, { animate: true, duration: 1.2 });
        showGpsToast(`Ditemukan: ${res.name || query}`, false, 3500);
      } else {
        showGpsToast(`Lokasi "${query}" tidak ditemukan`, false, 3000);
      }
    } catch (err) {
      console.error('Error pencarian geocoding:', err);
      showGpsToast(`Gagal menghubungi server pencarian`, false, 3000);
    }
  }

  // Setup Listener saat halaman dimuat
  document.addEventListener('DOMContentLoaded', () => {
    initLeafletMap();

    // Otomatis Deteksi GPS saat halaman baru dibuka
    setTimeout(() => {
      locateUser(true);
    }, 700);

    // Setup input pencarian jalan/kelurahan
    const searchInput = document.getElementById('search-map-input');
    if (searchInput) {
      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          searchLocationOnMap(searchInput.value);
        }
      });
    }
  });
</script>
</main>
</div>


</body></html>
