<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/nav_sidebar.php';
requireLogin();

$currentUser = currentUser();
$userRoleClean = strtolower($currentUser['role'] ?? 'petugas');

// Role access guard: pisahkan struktur admin, petugas, dan warga
if ($userRoleClean === 'admin') {
    header('Location: dashboard_admin.php');
    exit;
} elseif ($userRoleClean === 'warga') {
    header('Location: beranda.php');
    exit;
}

$currentUserName = $currentUser ? htmlspecialchars($currentUser['nama']) : 'Petugas Lapangan';
$currentUserRole = 'Petugas Lapangan';
$unreadCount = getUnreadNotificationCount();

// Dashboard Petugas - Teman Warga AI
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta content="web_dashboard" name="shell-type"/>
  <title>Dashboard Petugas - Teman Warga AI</title>
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
    @keyframes barGrow {
      from { transform: scaleY(0); }
      to { transform: scaleY(1); }
    }
    @keyframes donutSpinReveal {
      from { stroke-dasharray: 0 238.76; opacity: 0.2; }
      to { opacity: 1; }
    }
    @keyframes gentlePulseGlow {
      0%, 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.2), 0 2px 8px rgba(37, 99, 235, 0.08); }
      50% { box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15), 0 4px 16px rgba(37, 99, 235, 0.16); }
    }
    @keyframes gentleFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-3px); }
    }
    @keyframes radarPing {
      0% { transform: scale(1); opacity: 0.8; }
      70% { transform: scale(2.6); opacity: 0; }
      100% { transform: scale(2.6); opacity: 0; }
    }
    .animate-fade-in-up {
      animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .animate-bar-grow {
      transform-origin: bottom;
      animation: barGrow 0.85s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .animate-soft-glow {
      animation: gentlePulseGlow 3s ease-in-out infinite;
    }
    .animate-float {
      animation: gentleFloat 2.6s ease-in-out infinite;
    }
    .radar-ring {
      animation: radarPing 2.2s cubic-bezier(0, 0.2, 0.8, 1) infinite;
    }
    .radar-ring-delayed {
      animation: radarPing 2.2s cubic-bezier(0, 0.2, 0.8, 1) infinite 1.1s;
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
            sans: ["Outfit", "Inter", "system-ui", "sans-serif"],
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
    <?php renderHeaderBrand('petugas'); ?>
    <div class="flex-1 max-w-xl mx-auto">
      <div class="relative flex items-center w-full">
        <span class="material-symbols-outlined absolute left-space-md text-outline text-[20px] pointer-events-none transition-colors group-focus-within:text-primary">search</span>
        <input class="w-full pl-11 pr-space-md py-2 bg-surface-container-low rounded-xl text-on-surface placeholder-outline font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/30 transition-all shadow-none focus:shadow-sm" placeholder="Cari bantuan, laporan, atau informasi..." type="text"/>
      </div>
    </div>
    <div class="flex items-center gap-space-md shrink-0">
      <a href="notifikasi.php" aria-label="Notifikasi" class="relative w-10 h-10 rounded-xl bg-surface-container-low hover:bg-surface-container active:scale-95 flex items-center justify-center text-on-surface-variant transition-all">
        <span class="material-symbols-outlined text-[22px]">notifications</span>
        <?php if ($unreadCount > 0): ?>
        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-error ring-2 ring-surface-container-lowest"></span>
        <?php endif; ?>
      </a>
      <a href="Profile.php" class="flex items-center gap-space-sm pl-space-sm py-1 pr-space-sm rounded-xl hover:bg-surface-container-low active:scale-95 cursor-pointer transition-all">
        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center transition-transform hover:scale-105">
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

  <!-- Left Sidebar Navigation Sesuai Peran Petugas -->
  <?php renderSidebarNav('dashboard-petugas', 'petugas'); ?>

  <!-- Main Content -->
  <div class="pl-64">
    <main class="w-full pt-16 px-margin py-margin">
      <div class="flex flex-col w-full gap-space-lg pb-10">
        
        <!-- Header Page Title -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md animate-fade-in-up" style="animation-delay: 50ms;">
          <div class="flex flex-col">
            <div class="inline-flex items-center gap-space-xs text-primary mb-1 bg-primary/5 border border-primary/20 px-2.5 py-0.5 rounded-full w-fit shadow-[0_0_12px_rgba(0,74,198,0.12)]">
              <span class="material-symbols-outlined text-[16px] text-primary">admin_panel_settings</span>
              <span class="font-label-md text-label-md tracking-wide uppercase font-semibold">Area Kerja Kelurahan Sukamaju</span>
            </div>
            <h1 class="font-headline-xl text-headline-xl text-on-surface"><?= $currentUserRole === 'Admin' ? 'Dashboard Admin &amp; Verifikasi' : 'Dashboard Petugas' ?></h1>
            <p class="font-body-md text-body-md text-on-surface-variant"><?= $currentUserRole === 'Admin' ? 'Pantau, verifikasi (ACC), dan disposisikan laporan warga ke regu petugas lapangan dengan bantuan AI.' : 'Pantau dan tindaklanjuti laporan penanganan lapangan warga dengan bantuan AI.' ?></p>
          </div>
          <div class="flex items-center gap-space-md shrink-0">
            <div class="flex items-center gap-space-sm bg-surface-container-lowest px-space-md py-2 rounded-xl shadow-sm border border-outline-variant/30 hover:border-primary/40 transition-colors">
              <span class="relative flex h-3 w-3 items-center justify-center">
                <span class="radar-ring absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                <span class="radar-ring-delayed absolute inline-flex h-full w-full rounded-full bg-primary opacity-60"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary shadow-[0_0_8px_#004ac6]"></span>
              </span>
              <span class="font-label-md text-label-md text-on-surface font-medium">Sistem AI Monitoring Aktif</span>
            </div>
            <div class="flex items-center gap-space-sm bg-surface-container-lowest p-1.5 pr-space-md rounded-xl shadow-sm border border-outline-variant/30 hover:shadow-md hover:border-primary/30 active:scale-95 transition-all cursor-pointer group" onclick="window.location.href='Profile.php'">
              <div class="w-9 h-9 rounded-lg bg-primary-container text-on-primary-container flex items-center justify-center font-headline-md text-headline-md group-hover:rotate-3 group-hover:scale-105 transition-transform duration-300">
                <?= strtoupper(substr($currentUserName, 0, 1) . (strpos($currentUserName, ' ') !== false ? substr($currentUserName, strpos($currentUserName, ' ') + 1, 1) : 'P')); ?>
              </div>
              <div class="flex flex-col text-left">
                <span class="font-label-md text-label-md text-on-surface font-semibold group-hover:text-primary transition-colors"><?= $currentUserName; ?></span>
                <span class="font-label-sm text-label-sm text-outline"><?= $currentUserRole === 'Admin' ? 'Admin Kelurahan (Verifikator)' : 'Petugas Kelurahan' ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-space-lg">
          <!-- Metric Card 1 -->
          <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm border border-transparent hover:border-primary/30 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 flex flex-col justify-between relative overflow-hidden group cursor-pointer animate-fade-in-up" style="animation-delay: 100ms;">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-primary/5 rounded-full blur-xl group-hover:scale-150 group-hover:bg-primary/10 transition-all duration-500"></div>
            <div class="flex items-center justify-between">
              <span class="font-label-lg text-label-lg text-on-surface-variant">Total Laporan</span>
              <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-100/80 via-sky-50 to-indigo-100/70 p-2 shadow-sm ring-1 ring-blue-500/15 group-hover:scale-110 group-hover:ring-blue-500/35 transition-all duration-300 flex items-center justify-center overflow-hidden">
                <img src="Logo%20aksesori/Document.jpg" alt="Total Laporan" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform duration-300 group-hover:scale-105" />
              </div>
            </div>
            <div class="mt-4 flex items-baseline gap-space-md">
              <span class="font-display-lg text-display-lg text-on-surface tracking-tight group-hover:text-primary transition-colors">48</span>
              <div class="flex items-center gap-0.5 text-primary text-label-sm font-label-sm bg-primary/10 px-2 py-0.5 rounded-full group-hover:bg-primary group-hover:text-on-primary transition-colors">
                <span class="material-symbols-outlined text-[14px]">trending_up</span>
                <span>+12%</span>
              </div>
            </div>
            <span class="font-body-sm text-body-sm text-outline mt-1">dibandingkan minggu lalu</span>
          </div>

          <!-- Metric Card 2 -->
          <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm border border-transparent hover:border-secondary/30 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 flex flex-col justify-between relative overflow-hidden group cursor-pointer animate-fade-in-up" style="animation-delay: 180ms;">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-secondary-container/10 rounded-full blur-xl group-hover:scale-150 group-hover:bg-secondary-container/20 transition-all duration-500"></div>
            <div class="flex items-center justify-between">
              <span class="font-label-lg text-label-lg text-on-surface-variant">Dalam Penanganan</span>
              <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-amber-100/80 via-amber-50 to-orange-100/70 p-2 shadow-sm ring-1 ring-amber-500/15 group-hover:scale-110 group-hover:ring-amber-500/35 transition-all duration-300 flex items-center justify-center overflow-hidden">
                <img src="Logo%20aksesori/Timer.jpg" alt="Dalam Penanganan" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform duration-300 group-hover:scale-105" />
              </div>
            </div>
            <div class="mt-4 flex items-baseline gap-space-md">
              <span class="font-display-lg text-display-lg text-on-surface tracking-tight group-hover:text-secondary transition-colors">15</span>
              <span class="font-label-sm text-label-sm text-outline">Laporan aktif</span>
            </div>
            <span class="font-body-sm text-body-sm text-outline mt-1">3 prioritas darurat</span>
          </div>

          <!-- Metric Card 3 -->
          <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm border border-transparent hover:border-tertiary/30 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 flex flex-col justify-between relative overflow-hidden group cursor-pointer animate-fade-in-up" style="animation-delay: 260ms;">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-tertiary-container/10 rounded-full blur-xl group-hover:scale-150 group-hover:bg-tertiary-container/20 transition-all duration-500"></div>
            <div class="flex items-center justify-between">
              <span class="font-label-lg text-label-lg text-on-surface-variant">Terselesaikan</span>
              <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-emerald-100/80 via-emerald-50 to-teal-100/70 p-2 shadow-sm ring-1 ring-emerald-500/15 group-hover:scale-110 group-hover:ring-emerald-500/35 transition-all duration-300 flex items-center justify-center overflow-hidden">
                <img src="Logo%20aksesori/Ceklis%20Ijau%20buat%20dashboard.jpg" alt="Terselesaikan" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform duration-300 group-hover:scale-105" />
              </div>
            </div>
            <div class="mt-4 flex items-baseline gap-space-md">
              <span class="font-display-lg text-display-lg text-on-surface tracking-tight group-hover:text-tertiary transition-colors">33</span>
              <div class="flex items-center gap-0.5 text-secondary text-label-sm font-label-sm bg-secondary-fixed/50 px-2 py-0.5 rounded-full font-bold">
                <span class="material-symbols-outlined text-[14px]">verified</span>
                <span>68.7% Selesai</span>
              </div>
            </div>
            <span class="font-body-sm text-body-sm text-outline mt-1">Rata-rata 1.4 hari per tiket</span>
          </div>
        </div>

        <!-- 2 Column Layout (Charts & Analytics) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg">
          
          <div class="lg:col-span-8 flex flex-col gap-space-lg">
            
            <!-- Tren Laporan Mingguan Chart -->
            <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm flex flex-col hover:shadow-md transition-shadow animate-fade-in-up" style="animation-delay: 300ms;">
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h2 class="font-headline-md text-headline-md text-on-surface">Tren Laporan Mingguan</h2>
                  <p class="font-body-sm text-body-sm text-outline">Statistik volume laporan masuk 7 hari terakhir</p>
                </div>
                <div class="flex items-center gap-1.5 bg-surface-container-low p-1 rounded-xl text-label-sm font-label-sm">
                  <button class="px-3 py-1 rounded-lg bg-surface-container-lowest text-primary font-bold shadow-xs">Mingguan</button>
                  <button class="px-3 py-1 rounded-lg text-outline hover:text-on-surface transition-colors">Bulanan</button>
                </div>
              </div>

              <!-- Bar Chart Container -->
              <div class="flex items-end justify-between gap-3 h-44 pt-6 px-4 border-b border-surface-container">
                <!-- Day 1 -->
                <div class="flex flex-col items-center gap-2 flex-1 group relative cursor-pointer">
                  <div class="opacity-0 group-hover:opacity-100 group-hover:-translate-y-1 transition-all duration-200 pointer-events-none absolute -top-9 bg-inverse-surface text-inverse-on-surface text-label-sm py-1 px-2.5 rounded-lg shadow-md whitespace-nowrap z-20 flex items-center gap-1">
                    <span>12 Agu: 4 laporan</span>
                    <span class="w-1.5 h-1.5 bg-inverse-surface rotate-45 absolute -bottom-0.5 left-1/2 -translate-x-1/2"></span>
                  </div>
                  <span class="font-label-sm text-label-sm text-on-surface font-semibold group-hover:text-primary transition-colors">4</span>
                  <div class="w-9 bg-surface-container group-hover:bg-primary-container/80 group-hover:-translate-y-1 rounded-t-lg transition-all duration-300 animate-bar-grow" style="height: 38px; animation-delay: 100ms;"></div>
                  <span class="font-label-sm text-label-sm text-outline mt-2 group-hover:text-on-surface group-hover:font-semibold transition-colors">12 Agu</span>
                </div>
                <!-- Day 2 -->
                <div class="flex flex-col items-center gap-2 flex-1 group relative cursor-pointer">
                  <div class="opacity-0 group-hover:opacity-100 group-hover:-translate-y-1 transition-all duration-200 pointer-events-none absolute -top-9 bg-inverse-surface text-inverse-on-surface text-label-sm py-1 px-2.5 rounded-lg shadow-md whitespace-nowrap z-20 flex items-center gap-1">
                    <span>13 Agu: 5 laporan</span>
                    <span class="w-1.5 h-1.5 bg-inverse-surface rotate-45 absolute -bottom-0.5 left-1/2 -translate-x-1/2"></span>
                  </div>
                  <span class="font-label-sm text-label-sm text-on-surface font-semibold group-hover:text-primary transition-colors">5</span>
                  <div class="w-9 bg-surface-container group-hover:bg-primary-container/80 group-hover:-translate-y-1 rounded-t-lg transition-all duration-300 animate-bar-grow" style="height: 48px; animation-delay: 160ms;"></div>
                  <span class="font-label-sm text-label-sm text-outline mt-2 group-hover:text-on-surface group-hover:font-semibold transition-colors">13 Agu</span>
                </div>
                <!-- Day 3 -->
                <div class="flex flex-col items-center gap-2 flex-1 group relative cursor-pointer">
                  <div class="opacity-0 group-hover:opacity-100 group-hover:-translate-y-1 transition-all duration-200 pointer-events-none absolute -top-9 bg-inverse-surface text-inverse-on-surface text-label-sm py-1 px-2.5 rounded-lg shadow-md whitespace-nowrap z-20 flex items-center gap-1">
                    <span>14 Agu: 7 laporan</span>
                    <span class="w-1.5 h-1.5 bg-inverse-surface rotate-45 absolute -bottom-0.5 left-1/2 -translate-x-1/2"></span>
                  </div>
                  <span class="font-label-sm text-label-sm text-on-surface font-semibold group-hover:text-primary transition-colors">7</span>
                  <div class="w-9 bg-surface-container group-hover:bg-primary-container/80 group-hover:-translate-y-1 rounded-t-lg transition-all duration-300 animate-bar-grow" style="height: 67px; animation-delay: 220ms;"></div>
                  <span class="font-label-sm text-label-sm text-outline mt-2 group-hover:text-on-surface group-hover:font-semibold transition-colors">14 Agu</span>
                </div>
                <!-- Day 4 (Peak) -->
                <div class="flex flex-col items-center gap-2 flex-1 group relative cursor-pointer">
                  <div class="opacity-0 group-hover:opacity-100 group-hover:-translate-y-1 transition-all duration-200 pointer-events-none absolute -top-9 bg-inverse-surface text-inverse-on-surface text-label-sm py-1 px-2.5 rounded-lg shadow-md whitespace-nowrap z-20 flex items-center gap-1">
                    <span>15 Agu: 11 laporan (Puncak)</span>
                    <span class="w-1.5 h-1.5 bg-inverse-surface rotate-45 absolute -bottom-0.5 left-1/2 -translate-x-1/2"></span>
                  </div>
                  <span class="font-label-sm text-label-sm text-primary font-bold group-hover:scale-110 transition-transform">11</span>
                  <div class="w-9 bg-primary-container rounded-t-lg shadow-sm transition-all duration-300 group-hover:brightness-110 group-hover:-translate-y-1 shadow-primary/30 animate-bar-grow" style="height: 105px; animation-delay: 280ms;"></div>
                  <span class="font-label-sm text-label-sm text-on-surface font-bold mt-2">15 Agu</span>
                </div>
                <!-- Day 5 -->
                <div class="flex flex-col items-center gap-2 flex-1 group relative cursor-pointer">
                  <div class="opacity-0 group-hover:opacity-100 group-hover:-translate-y-1 transition-all duration-200 pointer-events-none absolute -top-9 bg-inverse-surface text-inverse-on-surface text-label-sm py-1 px-2.5 rounded-lg shadow-md whitespace-nowrap z-20 flex items-center gap-1">
                    <span>16 Agu: 9 laporan</span>
                    <span class="w-1.5 h-1.5 bg-inverse-surface rotate-45 absolute -bottom-0.5 left-1/2 -translate-x-1/2"></span>
                  </div>
                  <span class="font-label-sm text-label-sm text-on-surface font-semibold group-hover:text-primary transition-colors">9</span>
                  <div class="w-9 bg-surface-container group-hover:bg-primary-container/80 group-hover:-translate-y-1 rounded-t-lg transition-all duration-300 animate-bar-grow" style="height: 86px; animation-delay: 340ms;"></div>
                  <span class="font-label-sm text-label-sm text-outline mt-2 group-hover:text-on-surface group-hover:font-semibold transition-colors">16 Agu</span>
                </div>
                <!-- Day 6 -->
                <div class="flex flex-col items-center gap-2 flex-1 group relative cursor-pointer">
                  <div class="opacity-0 group-hover:opacity-100 group-hover:-translate-y-1 transition-all duration-200 pointer-events-none absolute -top-9 bg-inverse-surface text-inverse-on-surface text-label-sm py-1 px-2.5 rounded-lg shadow-md whitespace-nowrap z-20 flex items-center gap-1">
                    <span>17 Agu: 6 laporan</span>
                    <span class="w-1.5 h-1.5 bg-inverse-surface rotate-45 absolute -bottom-0.5 left-1/2 -translate-x-1/2"></span>
                  </div>
                  <span class="font-label-sm text-label-sm text-on-surface font-semibold group-hover:text-primary transition-colors">6</span>
                  <div class="w-9 bg-surface-container group-hover:bg-primary-container/80 group-hover:-translate-y-1 rounded-t-lg transition-all duration-300 animate-bar-grow" style="height: 57px; animation-delay: 400ms;"></div>
                  <span class="font-label-sm text-label-sm text-outline mt-2 group-hover:text-on-surface group-hover:font-semibold transition-colors">17 Agu</span>
                </div>
                <!-- Day 7 -->
                <div class="flex flex-col items-center gap-2 flex-1 group relative cursor-pointer">
                  <div class="opacity-0 group-hover:opacity-100 group-hover:-translate-y-1 transition-all duration-200 pointer-events-none absolute -top-9 bg-inverse-surface text-inverse-on-surface text-label-sm py-1 px-2.5 rounded-lg shadow-md whitespace-nowrap z-20 flex items-center gap-1">
                    <span>18 Agu: 6 laporan</span>
                    <span class="w-1.5 h-1.5 bg-inverse-surface rotate-45 absolute -bottom-0.5 left-1/2 -translate-x-1/2"></span>
                  </div>
                  <span class="font-label-sm text-label-sm text-on-surface font-semibold group-hover:text-primary transition-colors">6</span>
                  <div class="w-9 bg-surface-container group-hover:bg-primary-container/80 group-hover:-translate-y-1 rounded-t-lg transition-all duration-300 animate-bar-grow" style="height: 57px; animation-delay: 460ms;"></div>
                  <span class="font-label-sm text-label-sm text-outline mt-2 group-hover:text-on-surface group-hover:font-semibold transition-colors">18 Agu</span>
                </div>
              </div>
            </div>

            <!-- Table Container (Laporan Terbaru) -->
            <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow animate-fade-in-up" style="animation-delay: 380ms;">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-space-sm">
                  <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary"></span>
                  </span>
                  <h2 class="font-headline-md text-headline-md text-on-surface">Laporan Terbaru</h2>
                </div>
                <div class="flex items-center gap-1.5 text-outline">
                  <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
                  <span class="font-label-sm text-label-sm">Real-time update</span>
                </div>
              </div>
              <div class="w-full overflow-x-auto">
                <table class="w-full text-left">
                  <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant font-label-md text-label-md">
                      <th class="py-3 px-4 rounded-l-xl">Jenis</th>
                      <th class="py-3 px-4">Lokasi</th>
                      <th class="py-3 px-4">Status</th>
                      <th class="py-3 px-4 rounded-r-xl text-center">Prioritas</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y-0 text-body-md font-body-md">
                    <!-- Row 1 -->
                    <tr onclick="window.location.href='detail_Laporan.php'" class="hover:bg-surface-container-low/70 transition-all cursor-pointer group animate-fade-in-up" style="animation-delay: 440ms;">
                      <td class="py-3.5 px-4 font-semibold text-on-surface group-hover:text-primary transition-colors">
                        <div class="flex items-center gap-2">
                          <span class="w-2 h-2 rounded-full bg-secondary group-hover:scale-125 transition-transform"></span>
                          <span>Banjir</span>
                        </div>
                      </td>
                      <td class="py-3.5 px-4 text-on-surface-variant group-hover:text-on-surface transition-colors">Perumahan Melati</td>
                      <td class="py-3.5 px-4 text-on-surface-variant group-hover:text-on-surface transition-colors font-medium">Dalam Penanganan</td>
                      <td class="py-3.5 px-4 text-center text-on-surface-variant group-hover:text-on-surface transition-colors font-medium">9.5</td>
                    </tr>
                    <!-- Row 2 -->
                    <tr onclick="window.location.href='detail_Laporan.php'" class="hover:bg-surface-container-low/70 transition-all cursor-pointer group animate-fade-in-up" style="animation-delay: 500ms;">
                      <td class="py-3.5 px-4 font-semibold text-on-surface group-hover:text-primary transition-colors">
                        <div class="flex items-center gap-2">
                          <span class="w-2 h-2 rounded-full bg-primary group-hover:scale-125 transition-transform"></span>
                          <span>Jalan Rusak</span>
                        </div>
                      </td>
                      <td class="py-3.5 px-4 text-on-surface-variant group-hover:text-on-surface transition-colors">Jl. Anggrek</td>
                      <td class="py-3.5 px-4 text-on-surface-variant group-hover:text-on-surface transition-colors font-medium">Baru</td>
                      <td class="py-3.5 px-4 text-center text-on-surface-variant group-hover:text-on-surface transition-colors font-medium">7.2</td>
                    </tr>
                    <!-- Row 3 -->
                    <tr onclick="window.location.href='detail_Laporan.php'" class="hover:bg-surface-container-low/70 transition-all cursor-pointer group animate-fade-in-up" style="animation-delay: 560ms;">
                      <td class="py-3.5 px-4 font-semibold text-on-surface group-hover:text-primary transition-colors">
                        <div class="flex items-center gap-2">
                          <span class="w-2 h-2 rounded-full bg-tertiary group-hover:scale-125 transition-transform"></span>
                          <span>Sampah</span>
                        </div>
                      </td>
                      <td class="py-3.5 px-4 text-on-surface-variant group-hover:text-on-surface transition-colors">Jl. Kenanga</td>
                      <td class="py-3.5 px-4 text-on-surface-variant group-hover:text-on-surface transition-colors font-medium">Selesai</td>
                      <td class="py-3.5 px-4 text-center text-on-surface-variant group-hover:text-on-surface transition-colors font-medium">6.8</td>
                    </tr>
                    <!-- Row 4 -->
                    <tr onclick="window.location.href='detail_Laporan.php'" class="hover:bg-surface-container-low/70 transition-all cursor-pointer group animate-fade-in-up" style="animation-delay: 620ms;">
                      <td class="py-3.5 px-4 font-semibold text-on-surface group-hover:text-primary transition-colors">
                        <div class="flex items-center gap-2">
                          <span class="w-2 h-2 rounded-full bg-outline group-hover:scale-125 transition-transform"></span>
                          <span>Lampu Jalan</span>
                        </div>
                      </td>
                      <td class="py-3.5 px-4 text-on-surface-variant group-hover:text-on-surface transition-colors">Jl. Flamboyan</td>
                      <td class="py-3.5 px-4 text-on-surface-variant group-hover:text-on-surface transition-colors font-medium">Dalam Penanganan</td>
                      <td class="py-3.5 px-4 text-center text-on-surface-variant group-hover:text-on-surface transition-colors font-medium">5.1</td>
                    </tr>
                    <!-- Row 5 -->
                    <tr onclick="window.location.href='detail_Laporan.php'" class="hover:bg-surface-container-low/70 transition-all cursor-pointer group animate-fade-in-up" style="animation-delay: 680ms;">
                      <td class="py-3.5 px-4 font-semibold text-on-surface group-hover:text-primary transition-colors">
                        <div class="flex items-center gap-2">
                          <span class="w-2 h-2 rounded-full bg-secondary group-hover:scale-125 transition-transform"></span>
                          <span>Banjir</span>
                        </div>
                      </td>
                      <td class="py-3.5 px-4 text-on-surface-variant group-hover:text-on-surface transition-colors">Jl. Cendana</td>
                      <td class="py-3.5 px-4 text-on-surface-variant group-hover:text-on-surface transition-colors font-medium">Selesai</td>
                      <td class="py-3.5 px-4 text-center text-on-surface-variant group-hover:text-on-surface transition-colors font-medium">8.9</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="mt-4 pt-3 flex justify-end">
                <a class="group font-label-md text-label-md text-primary hover:text-primary-container flex items-center gap-1 transition-all font-semibold active:scale-95" href="peta_laporan.php">
                  <span>Lihat Semua</span>
                  <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1.5 transition-transform duration-200">arrow_forward</span>
                </a>
              </div>
            </div>
          </div>

          <!-- Right Column Analytics (Donut + AI Recom) -->
          <div class="lg:col-span-4 flex flex-col gap-space-lg">
            <!-- Masalah Paling Banyak Card -->
            <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm flex flex-col h-full hover:shadow-md transition-shadow animate-fade-in-up" style="animation-delay: 340ms;">
              <div class="flex items-center justify-between mb-4">
                <h2 class="font-headline-md text-headline-md text-on-surface">Masalah Paling Banyak</h2>
                <button aria-label="Opsi analitik" class="text-outline hover:text-on-surface active:scale-90 transition-all p-1 rounded-lg hover:bg-surface-container-low" type="button">
                  <span class="material-symbols-outlined text-[20px]">more_vert</span>
                </button>
              </div>
              <div class="relative flex items-center justify-center my-6 group">
                <svg class="w-48 h-48 transform -rotate-90 transition-transform duration-700 hover:rotate-[-75deg]" viewBox="0 0 100 100">
                  <!-- Background track -->
                  <circle cx="50" cy="50" fill="none" r="38" stroke="#eff4ff" stroke-width="14"></circle>
                  <!-- Segment 1: Banjir 35% -->
                  <circle class="transition-all duration-300 hover:stroke-width-[16] cursor-pointer" cx="50" cy="50" fill="none" r="38" stroke="#2563eb" stroke-dasharray="83.5 238.76" stroke-dashoffset="0" stroke-width="14"></circle>
                  <!-- Segment 2: Jalan Rusak 25% -->
                  <circle class="transition-all duration-300 hover:stroke-width-[16] cursor-pointer" cx="50" cy="50" fill="none" r="38" stroke="#5bb8fe" stroke-dasharray="59.7 238.76" stroke-dashoffset="-83.5" stroke-width="14"></circle>
                  <!-- Segment 3: Sampah 20% -->
                  <circle class="transition-all duration-300 hover:stroke-width-[16] cursor-pointer" cx="50" cy="50" fill="none" r="38" stroke="#00759f" stroke-dasharray="47.8 238.76" stroke-dashoffset="-143.2" stroke-width="14"></circle>
                  <!-- Segment 4: Lampu Jalan 12% -->
                  <circle class="transition-all duration-300 hover:stroke-width-[16] cursor-pointer" cx="50" cy="50" fill="none" r="38" stroke="#93ccff" stroke-dasharray="28.7 238.76" stroke-dashoffset="-191.0" stroke-width="14"></circle>
                  <!-- Segment 5: Lainnya 8% -->
                  <circle class="transition-all duration-300 hover:stroke-width-[16] cursor-pointer" cx="50" cy="50" fill="none" r="38" stroke="#c3c6d7" stroke-dasharray="19.1 238.76" stroke-dashoffset="-219.7" stroke-width="14"></circle>
                </svg>
                <div class="absolute flex flex-col items-center justify-center text-center pointer-events-none transition-transform duration-300 group-hover:scale-105">
                  <span class="font-headline-xl text-headline-xl text-on-surface font-bold leading-none">48</span>
                  <span class="font-label-sm text-label-sm text-outline mt-1">Laporan Total</span>
                </div>
              </div>
              <div class="flex flex-col gap-2 mt-auto pt-4">
                <!-- Row Banjir -->
                <div class="flex items-center justify-between py-1.5 px-2.5 rounded-xl hover:bg-surface-container-low transition-all cursor-pointer group">
                  <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-primary-container shrink-0 group-hover:scale-125 transition-transform duration-200"></span>
                    <span class="font-body-md text-body-md text-on-surface group-hover:font-semibold transition-all">Banjir</span>
                  </div>
                  <span class="font-label-lg text-label-lg font-bold text-on-surface group-hover:text-primary transition-colors">35%</span>
                </div>
                <!-- Row Jalan Rusak -->
                <div class="flex items-center justify-between py-1.5 px-2.5 rounded-xl hover:bg-surface-container-low transition-all cursor-pointer group">
                  <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-secondary-container shrink-0 group-hover:scale-125 transition-transform duration-200"></span>
                    <span class="font-body-md text-body-md text-on-surface group-hover:font-semibold transition-all">Jalan Rusak</span>
                  </div>
                  <span class="font-label-lg text-label-lg font-bold text-on-surface group-hover:text-secondary transition-colors">25%</span>
                </div>
                <!-- Row Sampah -->
                <div class="flex items-center justify-between py-1.5 px-2.5 rounded-xl hover:bg-surface-container-low transition-all cursor-pointer group">
                  <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-tertiary-container shrink-0 group-hover:scale-125 transition-transform duration-200"></span>
                    <span class="font-body-md text-body-md text-on-surface group-hover:font-semibold transition-all">Sampah</span>
                  </div>
                  <span class="font-label-lg text-label-lg font-bold text-on-surface group-hover:text-tertiary transition-colors">20%</span>
                </div>
                <!-- Row Lampu Jalan -->
                <div class="flex items-center justify-between py-1.5 px-2.5 rounded-xl hover:bg-surface-container-low transition-all cursor-pointer group">
                  <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-secondary-fixed-dim shrink-0 group-hover:scale-125 transition-transform duration-200"></span>
                    <span class="font-body-md text-body-md text-on-surface group-hover:font-semibold transition-all">Lampu Jalan</span>
                  </div>
                  <span class="font-label-lg text-label-lg font-bold text-on-surface group-hover:text-primary transition-colors">12%</span>
                </div>
                <!-- Row Lainnya -->
                <div class="flex items-center justify-between py-1.5 px-2.5 rounded-xl hover:bg-surface-container-low transition-all cursor-pointer group">
                  <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-outline-variant shrink-0 group-hover:scale-125 transition-transform duration-200"></span>
                    <span class="font-body-md text-body-md text-on-surface group-hover:font-semibold transition-all">Lainnya</span>
                  </div>
                  <span class="font-label-lg text-label-lg font-bold text-on-surface group-hover:text-outline transition-colors">8%</span>
                </div>
              </div>

              <!-- Smart AI Recommendation Box -->
              <div class="mt-6 bg-surface-container-low border border-primary/20 rounded-xl p-3.5 flex items-center gap-3 animate-soft-glow transition-all hover:border-primary/40 cursor-pointer group">
                <div class="w-8 h-8 rounded-lg bg-primary-container text-on-primary-container flex items-center justify-center shrink-0 shadow-sm animate-float">
                  <span class="material-symbols-outlined text-[18px]">psychology</span>
                </div>
                <div class="flex flex-col">
                  <span class="font-label-sm text-label-sm font-semibold text-primary flex items-center gap-1">
                    <span>Rekomendasi Petugas AI</span>
                    <span class="material-symbols-outlined text-[13px]">auto_awesome</span>
                  </span>
                  <span class="font-body-sm text-body-sm text-on-surface-variant group-hover:text-on-surface transition-colors leading-snug">Fokus penugasan pompa air RT 04 sebelum prakiraan hujan sore.</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>


</body>
</html>
