<?php
/**
 * nav_sidebar.php — Komponen Sidebar & Navigasi Dinamis Berdasarkan Role
 * Memastikan pemisahan struktur antara Admin, Petugas, dan Warga konsisten di semua halaman.
 */
require_once __DIR__ . '/auth.php';

function getAppRole($customRole = null) {
    if ($customRole) {
        return strtolower($customRole);
    }
    $u = currentUser();
    return strtolower($u['role'] ?? 'warga');
}

function getHomeDashboardUrl($role = null) {
    $r = getAppRole($role);
    if ($r === 'admin') return 'dashboard_admin.php';
    if ($r === 'petugas') return 'dashboard_petugas.php';
    return 'beranda.php';
}

function getPortalSubtitle($role = null) {
    $r = getAppRole($role);
    if ($r === 'admin') return 'Portal Admin Kelurahan';
    if ($r === 'petugas') return 'Portal Petugas Kelurahan';
    return 'Portal Pelayanan Warga';
}

function renderHeaderBrand($role = null) {
    $homeUrl = getHomeDashboardUrl($role);
    $subTitle = getPortalSubtitle($role);
    $r = getAppRole($role);
    
    // Status dot color matching portal identity
    if ($r === 'admin') {
        $dotClass = 'bg-amber-500 shadow-xs';
    } elseif ($r === 'petugas') {
        $dotClass = 'bg-blue-600 shadow-xs';
    } else {
        $dotClass = 'bg-emerald-500 shadow-xs';
    }
    ?>
    <a href="<?= $homeUrl; ?>" class="flex items-center gap-3 w-64 shrink-0 group hover:opacity-95 transition-all select-none" style="font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
      <div class="w-10 h-10 rounded-xl overflow-hidden shadow-md shadow-blue-500/15 border border-slate-100 group-hover:scale-105 group-hover:shadow-blue-500/25 group-active:scale-95 transition-all duration-300 shrink-0">
        <img src="logo.php" class="w-full h-full object-cover" alt="Logo Teman Warga AI"/>
      </div>
      <div class="flex flex-col justify-center">
        <div class="text-[18px] font-bold tracking-tight text-slate-900 leading-tight group-hover:text-blue-600 transition-colors flex items-center gap-1">
          Teman Warga <span class="text-blue-600 font-extrabold">AI</span>
        </div>
        <div class="flex items-center gap-1.5 mt-0.5">
          <span class="w-1.5 h-1.5 rounded-full <?= $dotClass; ?>"></span>
          <span class="text-[11px] font-medium text-slate-500 tracking-normal leading-none"><?= $subTitle; ?></span>
        </div>
      </div>
    </a>
    <?php
}

function renderSidebarNav($activeKey = '', $role = null) {
    $r = getAppRole($role);
    
    // Antrean verifikasi admin
    $antreanCount = 0;
    if ($r === 'admin') {
        try {
            $db = getDB();
            $antreanCount = (int)$db->query("SELECT COUNT(*) FROM laporan WHERE status = 'menunggu'")->fetchColumn();
        } catch (Exception $e) {
            $antreanCount = 2;
        }
    }

    $activeClass = "bg-primary text-white font-semibold shadow-sm py-3 px-4 rounded-xl flex items-center gap-3.5 text-[15px]";
    $inactiveClass = "text-slate-700 hover:text-slate-950 hover:bg-slate-100/90 py-3 px-4 rounded-xl flex items-center gap-3.5 text-[15px] font-medium transition-colors";
    ?>
    <aside class="fixed left-0 top-16 bottom-0 w-64 z-40 bg-surface-container-lowest/90 backdrop-blur-xl border-r border-slate-200/70 flex flex-col justify-between py-6 px-3.5 shadow-[1px_0_12px_rgba(0,0,0,0.02)]" style="font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
      <nav class="flex flex-col gap-1.5">

      <?php if ($r === 'admin'): ?>
        <!-- === MENU KHUSUS ADMIN SESUAI SCREENSHOT === -->
        <a class="<?= ($activeKey === 'dashboard-admin') ? $activeClass : $inactiveClass; ?>" href="dashboard_admin.php">
          <span class="material-symbols-outlined text-[22px]">dashboard</span>
          <span>Dashboard Admin</span>
        </a>

        <a class="<?= ($activeKey === 'verifikasi') ? $activeClass : $inactiveClass; ?>" href="verifikasi_acc.php">
          <div class="flex items-center gap-3.5">
            <span class="material-symbols-outlined text-[22px]">verified</span>
            <span>Verifikasi &amp; ACC</span>
          </div>
          <?php if ($antreanCount > 0): ?>
          <span class="w-6 h-6 rounded-full bg-[#fef3c7] text-[#92400e] text-xs font-bold flex items-center justify-center ml-auto shrink-0"><?= $antreanCount; ?></span>
          <?php endif; ?>
        </a>

        <a class="<?= ($activeKey === 'peta-laporan') ? $activeClass : $inactiveClass; ?>" href="peta_laporan.php">
          <span class="material-symbols-outlined text-[22px]">map</span>
          <span>Peta Pantauan</span>
        </a>

        <a class="<?= ($activeKey === 'monitoring') ? $activeClass : $inactiveClass; ?>" href="monitoring_petugas.php">
          <span class="material-symbols-outlined text-[22px]">engineering</span>
          <span>Monitoring Petugas</span>
        </a>

        <a class="<?= ($activeKey === 'forum-warga') ? $activeClass : $inactiveClass; ?>" href="forum_warga.php">
          <span class="material-symbols-outlined text-[22px]">forum</span>
          <span>Forum Warga</span>
        </a>

        <a class="<?= ($activeKey === 'profil') ? $activeClass : $inactiveClass; ?>" href="Profile.php">
          <span class="material-symbols-outlined text-[22px]">account_circle</span>
          <span>Profil</span>
        </a>

        <a class="text-slate-700 hover:text-red-600 hover:bg-red-50/80 py-3 px-4 rounded-xl flex items-center gap-3.5 text-[15px] font-medium transition-colors mt-2" href="logout.php">
          <span class="material-symbols-outlined text-[22px]">logout</span>
          <span>Keluar</span>
        </a>

      <?php elseif ($r === 'petugas'): ?>
        <!-- === MENU KHUSUS PETUGAS === -->
        <a class="<?= ($activeKey === 'dashboard-petugas') ? $activeClass : $inactiveClass; ?>" href="dashboard_petugas.php">
          <span class="material-symbols-outlined text-[22px]">assignment_ind</span>
          <span>Dashboard Petugas</span>
        </a>

        <a class="<?= ($activeKey === 'peta-laporan') ? $activeClass : $inactiveClass; ?>" href="peta_laporan.php">
          <span class="material-symbols-outlined text-[22px]">map</span>
          <span>Peta Lokasi Tugas</span>
        </a>

        <a class="<?= ($activeKey === 'forum-warga') ? $activeClass : $inactiveClass; ?>" href="forum_warga.php">
          <span class="material-symbols-outlined text-[22px]">groups</span>
          <span>Forum Warga</span>
        </a>

        <a class="<?= ($activeKey === 'profil') ? $activeClass : $inactiveClass; ?>" href="Profile.php">
          <span class="material-symbols-outlined text-[22px]">account_circle</span>
          <span>Profil Petugas</span>
        </a>

        <a class="text-slate-700 hover:text-red-600 hover:bg-red-50/80 py-3 px-4 rounded-xl flex items-center gap-3.5 text-[15px] font-medium transition-colors mt-2" href="logout.php">
          <span class="material-symbols-outlined text-[22px]">logout</span>
          <span>Keluar</span>
        </a>

      <?php else: ?>
        <!-- === MENU KHUSUS WARGA === -->
        <a class="<?= ($activeKey === 'dashboard-warga') ? $activeClass : $inactiveClass; ?>" href="beranda.php">
          <span class="material-symbols-outlined text-[22px]">home</span>
          <span>Beranda</span>
        </a>

        <a class="<?= ($activeKey === 'buat-laporan') ? $activeClass : $inactiveClass; ?>" href="buat_laporan_Masyarakat.php">
          <span class="material-symbols-outlined text-[22px]">campaign</span>
          <span>Lapor Masalah</span>
        </a>

        <a class="<?= ($activeKey === 'ai-chat') ? $activeClass : $inactiveClass; ?>" href="Ai_bot.php">
          <span class="material-symbols-outlined text-[22px]">forum</span>
          <span>AI Chat</span>
        </a>

        <a class="<?= ($activeKey === 'peta-laporan') ? $activeClass : $inactiveClass; ?>" href="peta_laporan.php">
          <span class="material-symbols-outlined text-[22px]">map</span>
          <span>Peta Laporan</span>
        </a>

        <a class="<?= ($activeKey === 'forum-warga') ? $activeClass : $inactiveClass; ?>" href="forum_warga.php">
          <span class="material-symbols-outlined text-[22px]">groups</span>
          <span>Forum Warga</span>
        </a>

        <a class="<?= ($activeKey === 'profil') ? $activeClass : $inactiveClass; ?>" href="Profile.php">
          <span class="material-symbols-outlined text-[22px]">account_circle</span>
          <span>Profil</span>
        </a>

        <a class="text-slate-700 hover:text-red-600 hover:bg-red-50/80 py-3 px-4 rounded-xl flex items-center gap-3.5 text-[15px] font-medium transition-colors mt-2" href="logout.php">
          <span class="material-symbols-outlined text-[22px]">logout</span>
          <span>Keluar</span>
        </a>
      <?php endif; ?>

      </nav>

      <!-- Interactive Floating Mascot Robot Teman Warga AI -->
      <a id="robotMascot" href="Ai_bot.php" title="Teman Warga AI Assistant — Siap Membantu 24 Jam" class="mb-2 flex flex-col items-center text-center relative group/bot cursor-pointer select-none transition-all duration-300 hover:scale-105 active:scale-95 mt-auto px-2 py-1 block">
        <div class="relative flex flex-col items-center w-full">
          <div class="w-28 h-28 relative shrink-0 my-1 flex items-center justify-center">
            <svg class="w-full h-full drop-shadow-md overflow-visible" fill="none" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
              <!-- Ground Shadow -->
              <ellipse class="robot-shadow" cx="80" cy="148" fill="#00174b" opacity="0.32" rx="35" ry="6"></ellipse>
              <!-- Floating Mascot Body Group -->
              <g class="robot-body-floating">
                <path d="M80 34 V18" stroke="#cce5ff" stroke-linecap="round" stroke-width="4"></path>
                <circle class="robot-antenna" cx="80" cy="15" fill="#5bb8fe" r="7"></circle>
                <circle cx="80" cy="15" fill="#ffffff" opacity="0.8" r="3"></circle>
                <rect fill="#2563eb" height="14" opacity="0.8" rx="4" width="12" x="24" y="46"></rect>
                <rect fill="#2563eb" height="14" opacity="0.8" rx="4" width="12" x="124" y="46"></rect>
                <rect fill="url(#botHeadGradSideNav)" height="56" rx="24" width="88" x="36" y="26"></rect>
                <rect fill="url(#botFaceGradSideNav)" height="42" rx="18" stroke="#93ccff" stroke-width="1.5" width="74" x="43" y="33"></rect>
                <g class="robot-eyes-track">
                  <g class="robot-eye origin-[64px_52px] group-hover/bot:scale-y-75 transition-transform">
                    <ellipse cx="64" cy="52" fill="#67e8f9" rx="6.5" ry="8"></ellipse>
                  </g>
                  <g class="robot-eye origin-[96px_52px] group-hover/bot:scale-y-75 transition-transform">
                    <ellipse cx="96" cy="52" fill="#67e8f9" rx="6.5" ry="8"></ellipse>
                  </g>
                </g>
                <ellipse cx="55" cy="61" fill="#f472b6" opacity="0.6" rx="4" ry="2.5"></ellipse>
                <ellipse cx="105" cy="61" fill="#f472b6" opacity="0.6" rx="4" ry="2.5"></ellipse>
                <path class="transition-all duration-300 group-hover/bot:stroke-width-[3]" d="M73 61 Q80 67 87 61" fill="none" stroke="#67e8f9" stroke-linecap="round" stroke-width="2.5"></path>
                <rect fill="#93ccff" height="6" rx="3" width="16" x="72" y="81"></rect>
                <g class="origin-[48px_94px] transition-transform duration-300 group-hover/bot:-rotate-12">
                  <path d="M47 94 C35 101 27 111 29 119 C31 125 39 123 45 115 C50 108 51 100 52 94 Z" fill="url(#botArmGradLeftNav)" stroke="#93ccff" stroke-width="1"></path>
                  <circle cx="33" cy="120" fill="#67e8f9" opacity="0.8" r="3.5"></circle>
                </g>
                <rect fill="url(#botBodyGradSideNav)" height="50" rx="20" width="68" x="46" y="86"></rect>
                <rect fill="#ffffff" fill-opacity="0.15" height="28" rx="12" stroke="#ffffff" stroke-opacity="0.3" stroke-width="1" width="46" x="57" y="94"></rect>
                <path d="M74 104 L80 110 L88 102" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
                <circle class="animate-ping" cx="80" cy="124" fill="#67e8f9" opacity="0.8" r="2"></circle>
                <g class="robot-wave">
                  <path d="M113 94 C125 101 133 111 131 119 C129 125 121 123 115 115 C110 108 109 100 108 94 Z" fill="url(#botArmGradRightNav)" stroke="#93ccff" stroke-width="1"></path>
                  <circle cx="127" cy="120" fill="#67e8f9" opacity="0.8" r="3.5"></circle>
                </g>
              </g>
              <defs>
                <linearGradient id="botHeadGradSideNav" x1="36" x2="124" y1="26" y2="82">
                  <stop offset="0%" stop-color="#ffffff"></stop>
                  <stop offset="40%" stop-color="#e0e7ff"></stop>
                  <stop offset="100%" stop-color="#93c5fd"></stop>
                </linearGradient>
                <linearGradient id="botFaceGradSideNav" x1="43" x2="117" y1="33" y2="75">
                  <stop offset="0%" stop-color="#0b1c30"></stop>
                  <stop offset="100%" stop-color="#1e293b"></stop>
                </linearGradient>
                <linearGradient id="botBodyGradSideNav" x1="46" x2="114" y1="86" y2="136">
                  <stop offset="0%" stop-color="#ffffff"></stop>
                  <stop offset="100%" stop-color="#93c5fd"></stop>
                </linearGradient>
                <linearGradient id="botArmGradLeftNav" x1="52" x2="28" y1="94" y2="120">
                  <stop offset="0%" stop-color="#ffffff"></stop>
                  <stop offset="100%" stop-color="#93c5fd"></stop>
                </linearGradient>
                <linearGradient id="botArmGradRightNav" x1="108" x2="132" y1="94" y2="120">
                  <stop offset="0%" stop-color="#ffffff"></stop>
                  <stop offset="100%" stop-color="#93c5fd"></stop>
                </linearGradient>
              </defs>
            </svg>
          </div>
        </div>
      </a>

      <!-- Bottom Card Bantuan Darurat -->
      <a href="tel:112" class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-100 flex items-center gap-3 hover:bg-blue-100/70 transition-all cursor-pointer">
        <div class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center shrink-0 shadow-xs">
          <span class="material-symbols-outlined text-[20px]">support_agent</span>
        </div>
        <div class="flex flex-col min-w-0">
          <span class="text-xs font-bold text-slate-900 leading-tight">Bantuan Darurat</span>
          <span class="text-[11px] text-slate-500 font-medium">Call Center 112</span>
        </div>
      </a>
    </aside>

    <!-- Robot Mascot Animation & Tracking Styles & Global Typography -->
    <style>
      /* Ensure crisp modern typography matching landing page */
      body, button, input, select, textarea {
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
      }
      .material-symbols-outlined {
        font-family: 'Material Symbols Outlined' !important;
      }
      @keyframes floatRobotNav {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-12px) rotate(1.5deg); }
      }
      @keyframes shadowScaleNav {
        0%, 100% { transform: scale(1); opacity: 0.32; filter: blur(0px); }
        50% { transform: scale(0.65, 0.45); opacity: 0.1; filter: blur(1.5px); }
      }
      .robot-body-floating {
        animation: floatRobotNav 2.6s cubic-bezier(0.45, 0.05, 0.55, 0.95) infinite;
        transform-origin: 80px 100px;
      }
      .robot-shadow {
        transform-origin: 80px 148px;
        animation: shadowScaleNav 2.6s cubic-bezier(0.45, 0.05, 0.55, 0.95) infinite;
      }
      @keyframes gentleWaveNav {
        0%, 100% { transform: rotate(0deg); }
        20% { transform: rotate(-26deg); }
        45% { transform: rotate(-6deg); }
        70% { transform: rotate(-22deg); }
        85% { transform: rotate(2deg); }
      }
      .robot-wave {
        transform-origin: 110px 96px;
        animation: gentleWaveNav 2.4s ease-in-out infinite;
      }
      .robot-eyes-track {
        transition: transform 0.08s cubic-bezier(0.2, 0.8, 0.4, 1);
        will-change: transform;
      }
      @keyframes eyeBlinkNav {
        0%, 88%, 100% { transform: scaleY(1); }
        92% { transform: scaleY(0.08); }
        96% { transform: scaleY(1); }
      }
      .robot-eye {
        transform-origin: center;
        animation: eyeBlinkNav 4.8s ease-in-out infinite;
      }
      .robot-eye-click-blink {
        animation: eyeSingleBlinkNav 0.4s ease-in-out !important;
      }
      @keyframes eyeSingleBlinkNav {
        0%, 100% { transform: scaleY(1); }
        50% { transform: scaleY(0.08); }
      }
      .robot-stopped .robot-body-floating,
      .robot-stopped .robot-shadow,
      .robot-stopped .robot-wave,
      .robot-stopped .robot-eye,
      .robot-stopped .robot-antenna {
        animation-play-state: paused !important;
      }
      .robot-stopped {
        opacity: 0.88;
        filter: grayscale(12%);
      }
      @keyframes antennaGlowNav {
        0%, 100% { filter: drop-shadow(0 0 3px rgba(91, 184, 254, 0.6)); opacity: 0.85; }
        50% { filter: drop-shadow(0 0 14px rgba(91, 184, 254, 1)) drop-shadow(0 0 20px rgba(37, 99, 235, 0.5)); opacity: 1; }
      }
      .robot-antenna { animation: antennaGlowNav 2s ease-in-out infinite; }
      .group\/bot:hover .robot-body-floating { animation-duration: 1.6s; }
      .group\/bot:hover .robot-shadow { animation-duration: 1.6s; }
      .group\/bot:hover .robot-wave { animation-duration: 1.2s; }
    </style>

    <script>
      (function() {
        if (window.__robotTrackingInit) return;
        window.__robotTrackingInit = true;

        let mouseX = window.innerWidth / 2;
        let mouseY = window.innerHeight / 2;
        let isStopped = false;
        let rafId = null;

        function updateEyes() {
          if (isStopped) return;
          const eyeTracks = document.querySelectorAll('.robot-eyes-track');
          if (!eyeTracks.length) return;
          eyeTracks.forEach(track => {
            const rect = track.getBoundingClientRect();
            if (!rect.width || !rect.height) return;
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;
            const dx = mouseX - cx;
            const dy = mouseY - cy;
            const angle = Math.atan2(dy, dx);
            const dist = Math.min(5.5, Math.hypot(dx, dy) / 32);
            track.style.transform = `translate(${(Math.cos(angle) * dist).toFixed(2)}px, ${(Math.sin(angle) * dist).toFixed(2)}px)`;
          });
          rafId = null;
        }

        window.addEventListener('mousemove', function(e) {
          mouseX = e.clientX;
          mouseY = e.clientY;
          if (isStopped) return;
          if (!rafId) rafId = requestAnimationFrame(updateEyes);
        }, { passive: true });

        document.addEventListener('mouseleave', function() {
          if (isStopped) return;
          document.querySelectorAll('.robot-eyes-track').forEach(el => {
            el.style.transform = 'translate(0px, 0px)';
          });
        });

        const robotMascot = document.getElementById('robotMascot');
        if (robotMascot) {
          function setRobotMoving() {
            isStopped = false;
            robotMascot.classList.remove('robot-stopped');
            robotMascot.setAttribute('title', 'Teman Warga AI — Klik kanan untuk mendiamkan robot');
            updateEyes();
          }

          function setRobotStopped() {
            isStopped = true;
            robotMascot.classList.add('robot-stopped');
            document.querySelectorAll('.robot-eyes-track').forEach(el => {
              el.style.transform = 'translate(0px, 0px)';
            });
            robotMascot.setAttribute('title', 'Robot sedang diam. Klik kanan atau klik kiri untuk menggerakkan kembali');
          }

          // Klik Kanan: Buat robot diam (atau gerak lagi jika sedang diam)
          robotMascot.addEventListener('contextmenu', function(e) {
            e.preventDefault(); // Mencegah popup context menu bawaan browser
            if (isStopped) {
              setRobotMoving();
            } else {
              setRobotStopped();
            }
          });

          // Klik Kiri: Jika diam -> gerak lagi; Jika sedang gerak -> kedip santai & natural
          robotMascot.addEventListener('click', function(e) {
            e.preventDefault(); // Mencegah redirect tidak sengaja saat bermain dengan maskot
            if (isStopped) {
              setRobotMoving();
            } else {
              // Kedip santai halus saat diklik (tidak getar cepat)
              const eyes = robotMascot.querySelectorAll('.robot-eye');
              eyes.forEach(eye => {
                eye.classList.remove('robot-eye-click-blink');
                void eye.offsetWidth;
                eye.classList.add('robot-eye-click-blink');
              });
              setTimeout(() => {
                eyes.forEach(eye => eye.classList.remove('robot-eye-click-blink'));
              }, 420);
            }
          });

          // Dobel klik: Navigasi ke Ai_bot.php jika memang ingin membuka obrolan AI
          robotMascot.addEventListener('dblclick', function() {
            window.location.href = 'Ai_bot.php';
          });
        }
      })();
    </script>
    <?php
}
