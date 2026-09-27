<?php
/**
 * Halaman_daftar.php — Halaman Masuk & Daftar Teman Warga AI
 */
require_once __DIR__ . '/auth.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    $cur = currentUser();
    $role = strtolower($cur['role'] ?? 'warga');
    if ($role === 'admin') {
        header('Location: dashboard_admin.php');
    } elseif ($role === 'petugas') {
        header('Location: dashboard_petugas.php');
    } else {
        header('Location: beranda.php');
    }
    exit;
}

$active_tab = isset($_GET['tab']) && $_GET['tab'] === 'masuk' ? 'masuk' : 'daftar';
$error   = '';
$success = '';

if (isset($_GET['logout'])) {
    $success = 'Anda telah berhasil keluar dari sistem.';
    $active_tab = 'masuk';
}

// ─── Proses Form Daftar ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tab_mode'] ?? '') === 'daftar') {
    $nama    = clean($_POST['full_name']   ?? '');
    $kontak  = clean($_POST['contact_info'] ?? '');
    $nik     = clean($_POST['nik']          ?? '');
    $pass    = $_POST['password']           ?? '';
    $confirm = $_POST['password_confirmation'] ?? '';

    if (empty($nama) || empty($kontak) || empty($pass)) {
        $error      = 'Nama lengkap, email/HP, dan kata sandi wajib diisi.';
        $active_tab = 'daftar';
    } elseif (strlen($pass) < 8) {
        $error      = 'Kata sandi minimal 8 karakter.';
        $active_tab = 'daftar';
    } elseif ($pass !== $confirm) {
        $error      = 'Konfirmasi kata sandi tidak cocok.';
        $active_tab = 'daftar';
    } else {
        try {
            $db     = getDB();
            $isEmail = filter_var($kontak, FILTER_VALIDATE_EMAIL);
            $email   = $isEmail ? $kontak : null;
            $no_hp   = !$isEmail ? $kontak : null;

            if ($email) {
                $cek = $db->prepare('SELECT id FROM users WHERE email = ?');
                $cek->execute([$email]);
            } else {
                $cek = $db->prepare('SELECT id FROM users WHERE no_hp = ?');
                $cek->execute([$no_hp]);
            }

            if ($cek->fetch()) {
                $error      = 'Email atau nomor HP sudah terdaftar. Silakan masuk.';
                $active_tab = 'daftar';
            } else {
                $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $db->prepare('INSERT INTO users (nama_lengkap, email, no_hp, nik, password) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$nama, $email, $no_hp, $nik ?: null, $hash]);
                $userId = (int) $db->lastInsertId();

                $db->prepare('INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, ?, ?, ?)')
                   ->execute([$userId, 'Selamat Datang!', 'Halo ' . $nama . '! Selamat bergabung di Teman Warga AI.', 'sistem']);

                $newUser = ['id' => $userId, 'nama_lengkap' => $nama, 'email' => $email ?? '', 'role' => 'warga', 'avatar' => ''];
                loginUser($newUser);
                header('Location: beranda.php');
                exit;
            }
        } catch (Exception $e) {
            $error      = 'Terjadi kesalahan sistem. Coba lagi nanti.';
            $active_tab = 'daftar';
        }
    }
}

// ─── Proses Form Masuk ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tab_mode'] ?? '') === 'masuk') {
    $kontak = clean($_POST['contact_info'] ?? '');
    $pass   = $_POST['password']           ?? '';

    if (empty($kontak) || empty($pass)) {
        $error      = 'Email/HP dan kata sandi wajib diisi.';
        $active_tab = 'masuk';
    } else {
        try {
            $db   = getDB();
            $stmt = $db->prepare('SELECT * FROM users WHERE email = ? OR no_hp = ? LIMIT 1');
            $stmt->execute([$kontak, $kontak]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user['password'])) {
                loginUser($user);
                $role = strtolower($user['role'] ?? 'warga');
                if ($role === 'admin') {
                    header('Location: dashboard_admin.php');
                } elseif ($role === 'petugas') {
                    header('Location: dashboard_petugas.php');
                } else {
                    header('Location: beranda.php');
                }
                exit;
            } else {
                $error      = 'Email/HP atau kata sandi salah. Silakan coba lagi.';
                $active_tab = 'masuk';
            }
        } catch (Exception $e) {
            $error      = 'Terjadi kesalahan sistem. Coba lagi nanti.';
            $active_tab = 'masuk';
        }
    }
}
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Daftar / Masuk - Teman Warga AI</title>
<link rel="icon" type="image/svg+xml" href="logo.php"/>
<!-- Google Fonts: Outfit -->
<link href="https://fonts.googleapis.com" rel="preconnect">
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet">
<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['"Outfit"', 'sans-serif'],
          },
          colors: {
            brand: {
              50: '#eff6ff',
              100: '#dbeafe',
              200: '#bfdbfe',
              300: '#93c5fd',
              400: '#60a5fa',
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8',
              800: '#1e40af',
              900: '#1e3a8a',
            }
          },
          animation: {
            'float-slow': 'float 4s ease-in-out infinite',
            'pulse-glow': 'pulseGlow 2.5s ease-in-out infinite',
            'badge-bob': 'badgeBob 3.5s ease-in-out infinite',
          },
          keyframes: {
            float: {
              '0%, 100%': { transform: 'translateY(0px)' },
              '50%': { transform: 'translateY(-12px)' },
            },
            pulseGlow: {
              '0%, 100%': { opacity: '0.6', transform: 'scale(1)' },
              '50%': { opacity: '1', transform: 'scale(1.15)' },
            },
            badgeBob: {
              '0%, 100%': { transform: 'translateY(0px)' },
              '50%': { transform: 'translateY(-6px)' },
            }
          }
        }
      }
    }
  </script>
<style data-purpose="custom-animations">
    @keyframes blink {
      0%, 88%, 100% {
        transform: scaleY(1);
      }
      92%, 96% {
        transform: scaleY(0.08);
      }
    }
    .robot-eyes {
      transform-origin: 160px 110px;
      animation: blink 3.5s infinite ease-in-out;
    }
    @keyframes antennaPulse {
      0%, 100% {
        opacity: 0.7;
        filter: drop-shadow(0 0 4px #38bdf8);
      }
      50% {
        opacity: 1;
        filter: drop-shadow(0 0 12px #38bdf8);
      }
    }
    .antenna-glow {
      animation: antennaPulse 2s ease-in-out infinite;
    }
    @keyframes chestPulse {
      0%, 100% {
        filter: drop-shadow(0 0 2px rgba(56, 189, 248, 0.4));
      }
      50% {
        filter: drop-shadow(0 0 8px rgba(56, 189, 248, 0.9));
      }
    }
    .chest-glow {
      animation: chestPulse 2.5s ease-in-out infinite;
    }
    .mascot-interactive {
      transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
      transform-origin: center bottom;
    }
    .mascot-interactive:hover {
      transform: scale(1.04) rotate(-1.5deg);
    }
    /* Input focus styling with glowing aura */
    .input-aura-group:focus-within svg.input-icon {
      color: #2563eb;
      transform: scale(1.08);
    }
    .input-aura-group svg.input-icon {
      transition: color 0.2s ease, transform 0.2s ease;
    }
    .input-aura {
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .input-aura:focus {
      background-color: #ffffff;
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
    }
    /* Tab indicator slide transition */
    .tab-nav {
      position: relative;
    }
    .tab-slider {
      position: absolute;
      bottom: -2px;
      height: 2px;
      background-color: #2563eb;
      transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    /* Smooth checkbox check icon */
    .custom-check {
      transition: all 0.15s ease-in-out;
    }
    .custom-check:checked {
      animation: checkPop 0.2s ease-out;
    }
    @keyframes checkPop {
      0% { transform: scale(0.85); }
      50% { transform: scale(1.15); }
      100% { transform: scale(1); }
    }
    .custom-scrollbar::-webkit-scrollbar {
      width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
      background: #f1f5f9;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 9999px;
    }

    /* Accessibility: Respect Reduced Motion */
    @media (prefers-reduced-motion: reduce) {
      *, ::before, ::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
      }
      .animate-float-slow,
      .animate-pulse-glow,
      .animate-badge-bob,
      .robot-eyes,
      .antenna-glow,
      .chest-glow {
        animation: none !important;
      }
    }
  </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans min-h-screen flex items-center justify-center p-0 md:p-6 lg:p-8 antialiased selection:bg-brand-500 selection:text-white">
<!-- BEGIN: MainContainer -->
<main class="w-full max-w-6xl bg-white shadow-2xl rounded-none md:rounded-3xl overflow-hidden min-h-screen md:min-h-[740px] flex flex-col lg:flex-row border border-slate-100">
<!-- BEGIN: LeftHeroBranding -->
<section class="lg:w-1/2 relative bg-gradient-to-b from-white via-slate-50 to-slate-100 flex flex-col justify-between items-center text-center p-8 lg:p-12 overflow-hidden text-slate-800 border-b lg:border-b-0 lg:border-r border-slate-100" data-purpose="hero-mascot-panel">
<!-- Ambient Decorative Circles & Lights -->
<div class="absolute -top-24 -left-24 w-80 h-80 bg-brand-500/5 rounded-full blur-2xl pointer-events-none"></div>
<div class="absolute bottom-10 right-0 w-96 h-96 bg-sky-200/20 rounded-full blur-3xl pointer-events-none"></div>
<!-- Modern City Skyline Silhouette Background -->
<div class="absolute inset-x-0 bottom-0 pointer-events-none opacity-75"><svg class="w-full h-80 pointer-events-none opacity-40" viewBox="0 0 600 450" fill="none" preserveAspectRatio="xMidYMax slice" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="skyBldGrad1" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#cbd5e1" stop-opacity="0.35"></stop>
      <stop offset="100%" stop-color="#94a3b8" stop-opacity="0.1"></stop>
    </linearGradient>
    <linearGradient id="skyBldGrad2" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#93c5fd" stop-opacity="0.45"></stop>
      <stop offset="100%" stop-color="#bfdbfe" stop-opacity="0.15"></stop>
    </linearGradient>
    <linearGradient id="skyBldGrad3" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#cbd5e1" stop-opacity="0.5"></stop>
      <stop offset="100%" stop-color="#e2e8f0" stop-opacity="0.2"></stop>
    </linearGradient>
  </defs>
  <!-- Distant Background Buildings and Spire -->
  <g opacity="0.5">
    <rect x="50" y="200" width="65" height="250" rx="5" fill="url(#skyBldGrad1)"></rect>
    <rect x="105" y="140" width="80" height="310" rx="5" fill="url(#skyBldGrad2)"></rect>
    <polygon points="145,90 128,140 162,140" fill="url(#skyBldGrad2)"></polygon>
    <rect x="175" y="220" width="60" height="230" rx="4" fill="url(#skyBldGrad1)"></rect>
    <rect x="365" y="210" width="70" height="240" rx="5" fill="url(#skyBldGrad1)"></rect>
    <rect x="415" y="130" width="90" height="320" rx="5" fill="url(#skyBldGrad2)"></rect>
    <polygon points="460,80 440,130 480,130" fill="url(#skyBldGrad2)"></polygon>
    <rect x="485" y="190" width="75" height="260" rx="4" fill="url(#skyBldGrad1)"></rect>
  </g>
  <!-- Midground Detailed Modern Buildings with Windows -->
  <g opacity="0.75">
    <rect x="75" y="230" width="75" height="220" rx="6" fill="url(#skyBldGrad3)"></rect>
    <circle cx="95" cy="260" r="3.5" fill="#94a3b8" opacity="0.6"></circle>
    <circle cx="115" cy="260" r="3.5" fill="#94a3b8" opacity="0.6"></circle>
    <circle cx="132" cy="260" r="3.5" fill="#94a3b8" opacity="0.6"></circle>
    <circle cx="95" cy="285" r="3.5" fill="#94a3b8" opacity="0.6"></circle>
    <circle cx="115" cy="285" r="3.5" fill="#94a3b8" opacity="0.6"></circle>
    <circle cx="132" cy="285" r="3.5" fill="#94a3b8" opacity="0.6"></circle>
    <circle cx="95" cy="310" r="3.5" fill="#94a3b8" opacity="0.6"></circle>
    <circle cx="115" cy="310" r="3.5" fill="#94a3b8" opacity="0.6"></circle>
    <circle cx="132" cy="310" r="3.5" fill="#94a3b8" opacity="0.6"></circle>
    <rect x="440" y="220" width="85" height="230" rx="6" fill="url(#skyBldGrad3)"></rect>
    <rect x="455" y="250" width="14" height="18" rx="2.5" fill="#94a3b8" opacity="0.6"></rect>
    <rect x="478" y="250" width="14" height="18" rx="2.5" fill="#94a3b8" opacity="0.6"></rect>
    <rect x="500" y="250" width="14" height="18" rx="2.5" fill="#94a3b8" opacity="0.6"></rect>
    <rect x="455" y="280" width="14" height="18" rx="2.5" fill="#94a3b8" opacity="0.6"></rect>
    <rect x="478" y="280" width="14" height="18" rx="2.5" fill="#94a3b8" opacity="0.6"></rect>
    <rect x="500" y="280" width="14" height="18" rx="2.5" fill="#94a3b8" opacity="0.6"></rect>
  </g>
  <!-- Accent Foliage Bubbles -->
  <circle cx="60" cy="415" r="38" fill="#34d399" opacity="0.4"></circle>
  <circle cx="86" cy="425" r="26" fill="#10b981" opacity="0.5"></circle>
  <circle cx="525" cy="415" r="42" fill="#34d399" opacity="0.4"></circle>
  <circle cx="552" cy="425" r="28" fill="#10b981" opacity="0.5"></circle>
</svg></div>
<!-- Top Branding Section -->
<div class="relative z-10 space-y-2 mt-4"><a href="landing-page.php" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200 shadow-sm mb-2 hover:bg-slate-50 hover:border-brand-300 transition-all group" title="Kembali ke Landing Page"><div class="w-6 h-6 rounded-full overflow-hidden shadow-sm group-hover:scale-110 transition-transform shrink-0"><img src="logo.php" class="w-full h-full object-cover" alt="Logo"/></div><span class="text-xs font-semibold tracking-wide text-slate-700 group-hover:text-brand-600 transition-colors">Platform Pelayanan Warga</span></a><h1 class="text-3xl font-extrabold tracking-tight text-slate-900"><a href="landing-page.php" class="hover:opacity-90 transition-opacity" title="Kembali ke Landing Page">Teman Warga <span class="text-brand-600">AI</span></a></h1><p class="text-slate-600 text-sm font-normal max-w-xs mx-auto leading-relaxed mt-1">Bersama membangun lingkungan yang lebih baik, terhubung, dan responsif.</p></div>
<!-- Mascot Illustration Container with Animation -->
<div class="relative z-10 my-auto animate-float-slow flex flex-col items-center py-2">
<!-- Mascot Glow Background -->
<div class="absolute w-56 h-56 bg-cyan-400/25 rounded-full blur-2xl -z-10 animate-pulse-glow"></div>
<!-- 3D-styled AI Mascot Vector with hover tilt and friendly nod -->
<div class="mascot-interactive cursor-pointer select-none">
<svg class="w-60 h-60 lg:w-72 lg:h-72 drop-shadow-2xl" fill="none" viewBox="0 0 320 320" xmlns="http://www.w3.org/2000/svg">
<defs>
<radialGradient cx="40%" cy="30%" id="robotBodyGrad" r="65%">
<stop offset="0%" stop-color="#FFFFFF"></stop>
<stop offset="70%" stop-color="#F1F5F9"></stop>
<stop offset="100%" stop-color="#CBD5E1"></stop>
</radialGradient>
<linearGradient id="faceScreen" x1="0%" x2="0%" y1="0%" y2="100%">
<stop offset="0%" stop-color="#0F172A"></stop>
<stop offset="100%" stop-color="#1E293B"></stop>
</linearGradient>
<linearGradient id="neonBlue" x1="0%" x2="100%" y1="0%" y2="100%">
<stop offset="0%" stop-color="#38BDF8"></stop>
<stop offset="100%" stop-color="#2563EB"></stop>
</linearGradient>
</defs>
<!-- Antenna Rod and Glowing Orb -->
<rect fill="#94A3B8" height="24" rx="3" width="6" x="157" y="32"></rect>
<g class="antenna-glow">
<circle cx="160" cy="28" fill="url(#neonBlue)" r="10"></circle>
<circle class="animate-ping origin-center" cx="160" cy="28" fill="#38BDF8" opacity="0.6" r="10" style="transform-origin: 160px 28px; animation-duration: 2.5s;"></circle>
<circle cx="160" cy="28" fill="#BAE6FD" r="5"></circle>
</g>
<!-- Robot Ears (Side Dials) -->
<rect fill="#64748B" height="34" rx="7" width="14" x="66" y="96"></rect>
<rect fill="#38BDF8" height="22" rx="3" width="6" x="70" y="102"></rect>
<rect fill="#64748B" height="34" rx="7" width="14" x="240" y="96"></rect>
<rect fill="#38BDF8" height="22" rx="3" width="6" x="244" y="102"></rect>
<!-- Robot Head Outer Shell -->
<rect fill="url(#robotBodyGrad)" height="124" rx="46" stroke="#E2E8F0" stroke-width="2" width="168" x="76" y="52"></rect>
<!-- Robot Face Screen (Dark Glass) -->
<rect fill="url(#faceScreen)" height="92" rx="30" width="132" x="94" y="68"></rect>
<!-- Eyes Group with Blink Animation -->
<g class="robot-eyes">
<ellipse cx="128" cy="110" fill="#38BDF8" rx="14" ry="16"></ellipse>
<ellipse cx="132" cy="106" fill="#FFFFFF" rx="5" ry="6"></ellipse>
<ellipse cx="192" cy="110" fill="#38BDF8" rx="14" ry="16"></ellipse>
<ellipse cx="196" cy="106" fill="#FFFFFF" rx="5" ry="6"></ellipse>
</g>
<!-- Cute Robotic Smile -->
<path d="M 148 132 Q 160 142 172 132" fill="none" stroke="#38BDF8" stroke-linecap="round" stroke-width="4"></path>
<!-- Soft Rosy Cheeks -->
<ellipse cx="114" cy="126" fill="#0EA5E9" opacity="0.6" rx="6" ry="3.5"></ellipse>
<ellipse cx="206" cy="126" fill="#0EA5E9" opacity="0.6" rx="6" ry="3.5"></ellipse>
<!-- Neck Joint -->
<rect fill="#94A3B8" height="12" rx="4" width="32" x="144" y="174"></rect>
<!-- Robot Body / Torso -->
<rect fill="url(#robotBodyGrad)" height="96" rx="36" stroke="#E2E8F0" stroke-width="2" width="120" x="100" y="184"></rect>
<!-- Chest Emblem / Screen with pulse glow -->
<g class="chest-glow">
<rect fill="#E0F2FE" height="42" rx="14" stroke="#38BDF8" stroke-width="2" width="50" x="135" y="206"></rect>
<!-- AI Logo inside Chest -->
<g class="robot-chest-icon">
<path d="M 152 227 L 160 216 L 168 227 Z" fill="#0284C7"></path>
<rect fill="#0284C7" height="3" rx="1.5" width="8" x="156" y="231"></rect>
</g>
</g>
<!-- Left Arm Waving -->
<g class="waving-arm">
<rect fill="url(#robotBodyGrad)" height="18" rx="9" stroke="#CBD5E1" stroke-width="1.5" width="32" x="62" y="196"></rect>
<circle cx="56" cy="192" fill="url(#robotBodyGrad)" r="14" stroke="#CBD5E1" stroke-width="1.5"></circle>
</g>
<!-- Right Arm Resting -->
<g>
<rect fill="url(#robotBodyGrad)" height="18" rx="9" stroke="#CBD5E1" stroke-width="1.5" width="32" x="226" y="206"></rect>
<circle cx="264" cy="222" fill="url(#robotBodyGrad)" r="14" stroke="#CBD5E1" stroke-width="1.5"></circle>
</g>
</svg>
</div>
<!-- Floating Chat Bubble / Speech Badge with subtle bobbing -->
<div class="mt-2 bg-white text-slate-800 text-xs md:text-sm font-semibold py-2 px-4 rounded-2xl shadow-xl border border-cyan-100 flex items-center gap-2 animate-badge-bob hover:scale-105 transition-transform duration-200"><span class="relative flex h-2 w-2 flex-shrink-0"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span><span class="text-xs font-semibold text-slate-800 whitespace-nowrap">Halo! Saya Teman Warga AI siap membantu.</span></div>
</div>
<!-- Left Bottom Features Highlights -->
<div class="relative z-10 w-full pt-4 border-t border-slate-200 grid grid-cols-3 gap-2 text-xs text-slate-500"><div class="flex flex-col items-center hover:text-slate-800 transition-colors duration-200 cursor-default"><span class="font-bold text-slate-800 text-sm">24/7</span><span class="">Respon Cepat</span></div><div class="flex flex-col items-center border-x border-slate-200 hover:text-slate-800 transition-colors duration-200 cursor-default"><span class="font-bold text-slate-800 text-sm">Terpadu</span><span class="">Laporan RT/RW</span></div><div class="flex flex-col items-center hover:text-slate-800 transition-colors duration-200 cursor-default"><span class="font-bold text-slate-800 text-sm">Akurat</span><span class="">Pantau Status</span></div></div>
</section>
<!-- END: LeftHeroBranding -->
<!-- BEGIN: RightAuthRegistrationCard -->
<section class="lg:w-1/2 p-6 sm:p-8 lg:p-10 flex flex-col justify-center bg-white overflow-y-auto max-h-[850px] custom-scrollbar" data-purpose="registration-form-panel">
<!-- Back link to Landing Page -->
<div class="flex items-center justify-between mb-4">
  <a href="landing-page.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-brand-600 transition-colors group" title="Kembali ke Landing Page">
    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    <span>Kembali ke Landing Page</span>
  </a>
</div>
<!-- Top Masuk / Daftar Tab Switcher with sliding indicator -->
<div class="relative border-b border-slate-200 mb-6 tab-nav" data-purpose="tab-switcher">
<div class="flex items-center justify-center">
<button class="w-1/2 pb-3.5 text-center <?= $active_tab === 'masuk' ? 'text-brand-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium' ?> text-sm sm:text-base transition-colors relative cursor-pointer" id="tab-masuk" type="button">
          Masuk
        </button>
<button class="w-1/2 pb-3.5 text-center <?= $active_tab === 'daftar' ? 'text-brand-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium' ?> text-sm sm:text-base transition-colors relative cursor-pointer" id="tab-daftar" type="button">
          Daftar
</button>
</div>
<!-- Sliding active bar indicator -->
<div class="tab-slider" id="tab-indicator" style="width: 50%; left: <?= $active_tab === 'masuk' ? '0%' : '50%'; ?>;"></div>
</div>
<!-- Form Heading -->
<div class="mb-5 text-left">
<h2 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight" id="form-title"><?= $active_tab === 'masuk' ? 'Masuk ke Akun Anda' : 'Buat Akun Warga' ?></h2>
<p class="text-xs sm:text-sm text-slate-500 mt-1" id="form-subtitle"><?= $active_tab === 'masuk' ? 'Selamat datang kembali! Silakan masuk untuk mengakses layanan.' : 'Daftarkan diri Anda untuk kemudahan pelaporan &amp; layanan lingkungan.' ?></p>
</div>
<!-- Registration Form -->
<?php if ($error): ?>
<div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
  <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
  <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>
<form action="Halaman_daftar.php" class="space-y-3.5" data-purpose="signup-form" method="POST" id="auth-form">
<!-- Field 1: Nama Lengkap -->
<div class="input-aura-group" id="group-full-name" style="<?= $active_tab === 'masuk' ? 'display: none;' : '' ?>">
<label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1" for="full_name">
            Nama Lengkap <span class="text-rose-500">*</span>
</label>
<div class="relative rounded-lg shadow-sm">
<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
<svg class="h-4 w-4 input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</div>
<input class="input-aura block w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none" id="full_name" name="full_name" placeholder="Masukkan nama lengkap sesuai KTP" type="text">
</div>
</div>
<!-- Field 2: Email atau Nomor HP -->
<div class="input-aura-group">
<label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1" for="contact_info">
            Email atau No. HP <span class="text-rose-500">*</span>
</label>
<div class="relative rounded-lg shadow-sm">
<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
<svg class="h-4 w-4 input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</div>
<input class="input-aura block w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none" id="contact_info" name="contact_info" placeholder="contoh@email.com / 0812xxxx" required="" type="text">
</div>
</div>
<!-- Field 3: NIK (Nomor Induk Kependudukan) -->
<div class="input-aura-group" id="group-nik" style="<?= $active_tab === 'masuk' ? 'display: none;' : '' ?>">
<div class="flex justify-between items-center mb-1">
<label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider" for="nik">
              Nomor Induk Kependudukan (NIK)
            </label>
<span class="text-[10px] text-slate-400 font-medium">Opsional/Verifikasi</span>
</div>
<div class="relative rounded-lg shadow-sm">
<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
<svg class="h-4 w-4 input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</div>
<input class="input-aura block w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none" id="nik" maxlength="16" name="nik" placeholder="327xxxxxxxxxxxxx (16 digit)" type="text">
</div>
</div>
<!-- Field 4: Password & Confirm Password (Grid) -->
<div class="grid grid-cols-1 <?= $active_tab === 'masuk' ? '' : 'sm:grid-cols-2' ?> gap-3" id="group-passwords">
<!-- Password Field -->
<div class="input-aura-group">
<label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1" for="password">
              Kata Sandi <span class="text-rose-500">*</span>
</label>
<div class="relative rounded-lg shadow-sm">
<input class="input-aura block w-full pl-3 pr-9 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none" id="password" name="password" placeholder="Min. 8 karakter" required="" type="password">
<button class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-brand-600 focus:outline-none transition-colors" onclick="togglePasswordVisibility('password', this)" title="Lihat password" type="button">
<svg class="h-4 w-4 eye-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
<path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</button>
</div>
</div>
<!-- Confirm Password Field -->
<div class="input-aura-group" id="group-password-confirm" style="<?= $active_tab === 'masuk' ? 'display: none;' : '' ?>">
<label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1" for="password_confirmation">
              Konfirmasi Sandi <span class="text-rose-500">*</span>
</label>
<div class="relative rounded-lg shadow-sm">
<input class="input-aura block w-full pl-3 pr-9 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none" id="password_confirmation" name="password_confirmation" placeholder="Ulangi sandi" type="password">
<button class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-brand-600 focus:outline-none transition-colors" onclick="togglePasswordVisibility('password_confirmation', this)" title="Lihat konfirmasi password" type="button">
<svg class="h-4 w-4 eye-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
<path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
</button>
</div>
</div>
</div>
<!-- Terms and Privacy Checkbox -->
<div class="pt-1" id="group-terms" style="<?= $active_tab === 'masuk' ? 'display: none;' : '' ?>">
<label class="flex items-start gap-2.5 cursor-pointer text-xs text-slate-600 select-none group">
<input class="custom-check w-4 h-4 mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500 focus:ring-offset-0 transition cursor-pointer" type="checkbox" id="check-terms" checked>
<span class="group-hover:text-slate-800 transition-colors">
              Saya menyetujui <a class="text-brand-600 hover:text-brand-700 font-semibold underline underline-offset-2 transition-colors" href="syarat_ketentuan.php" target="_blank">Syarat &amp; Ketentuan</a> serta <a class="text-brand-600 hover:text-brand-700 font-semibold underline underline-offset-2 transition-colors" href="kebijakan_privasi.php" target="_blank">Kebijakan Privasi</a> Teman Warga AI.
            </span>
</label>
</div>
<!-- Submit Button with hover elevation and arrow slide -->
<input type="hidden" name="tab_mode" id="hidden_action" value="<?= htmlspecialchars($active_tab) ?>">
<button class="w-full px-4 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 active:scale-[0.99] text-white font-semibold text-sm rounded-lg shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2 group mt-2 cursor-pointer focus:outline-none focus:ring-4 focus:ring-brand-500/30 py-3" type="submit" id="btn-submit"><span class="leading-normal" id="btn-submit-text"><?= $active_tab === 'masuk' ? 'Masuk Sekarang' : 'Daftar Sekarang' ?></span><svg class="w-4 h-4 transform group-hover:translate-x-1.5 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></button>
<!-- Link Lupa Password (hanya tampil di tab masuk) -->
<div id="forgot-pass-link" class="
text-right <?= $active_tab === 'masuk' ? '' : 'hidden' ?>">
  <a href="lupa_password.php" class="text-xs text-brand-600 hover:text-brand-700 font-semibold hover:underline">Lupa kata sandi?</a>
</div>

<!-- Demo Quick Fill Box (Tab Masuk) -->
<div id="demo-account-box" class="<?= $active_tab === 'masuk' ? '' : 'hidden' ?> mt-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-left">
  <div class="flex items-center justify-between mb-1.5">
    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
      <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
      <span>Akun Demo (1-Klik)</span>
    </span>
    <span class="text-[10px] text-slate-400">Password: password123</span>
  </div>
  <div class="grid grid-cols-2 gap-2">
    <button type="button" onclick="fillDemoLogin('admin@temanwarga.id', 'password123')" class="px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-brand-500 text-slate-700 hover:text-brand-600 text-[11px] font-semibold transition text-center shadow-xs cursor-pointer flex items-center justify-center gap-1.5 group/btn">
      <img src="Logo%20aksesori/Admin.jpg" alt="Admin" class="w-4 h-4 rounded-full object-contain shrink-0 group-hover/btn:scale-105 transition-transform" />
      <span>Admin</span>
    </button>
    <button type="button" onclick="fillDemoLogin('petugas@temanwarga.id', 'password123')" class="px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-brand-500 text-slate-700 hover:text-brand-600 text-[11px] font-semibold transition text-center shadow-xs cursor-pointer flex items-center justify-center gap-1.5 group/btn">
      <img src="Logo%20aksesori/Petugas.jpg" alt="Petugas" class="w-4 h-4 rounded-full object-contain shrink-0 group-hover/btn:scale-105 transition-transform" />
      <span>Petugas</span>
    </button>
  </div>
</div>
</form>

<!-- Bottom Redirect Link -->
<div class="text-center mt-5 pt-3 border-t border-slate-100">
<p class="text-xs text-slate-500" id="bottom-text-wrapper">
          Sudah memiliki akun? 
          <a class="font-bold text-brand-600 hover:text-brand-700 transition" href="#" id="link-masuk-bawah">Masuk di sini</a>
</p>
</div>
</section>
<!-- END: RightAuthRegistrationCard -->
</main>
<!-- END: MainContainer -->
<!-- BEGIN: InteractiveScripts -->
<script data-purpose="toggle-password">
    // Toggle show/hide password logic with smooth transition
    function togglePasswordVisibility(fieldId, btn) {
      const input = document.getElementById(fieldId);
      if (!input) return;
      
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      
      const icon = btn.querySelector('.eye-icon');
      if (icon) {
        if (isPassword) {
          icon.classList.add('text-brand-600', 'scale-110');
          icon.classList.remove('text-slate-400');
          icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />';
        } else {
          icon.classList.remove('text-brand-600', 'scale-110');
          icon.classList.add('text-slate-400');
          icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
        }
      }
    }
  </script>
<script data-purpose="tab-switcher-interaction">
    const tabMasuk = document.getElementById('tab-masuk');
    const tabDaftar = document.getElementById('tab-daftar');
    const tabIndicator = document.getElementById('tab-indicator');
    const linkMasukBawah = document.getElementById('link-masuk-bawah');
    const formTitle = document.getElementById('form-title');
    const formSubtitle = document.getElementById('form-subtitle');
    const groupFullName = document.getElementById('group-full-name');
    const groupNik = document.getElementById('group-nik');
    const groupPasswords = document.getElementById('group-passwords');
    const groupPasswordConfirm = document.getElementById('group-password-confirm');
    const groupTerms = document.getElementById('group-terms');
    const btnSubmitText = document.getElementById('btn-submit-text');
    const dividerText = document.getElementById('divider-text');
    const bottomTextWrapper = document.getElementById('bottom-text-wrapper');

    function activateTab(tab) {
      const hiddenAction   = document.getElementById('hidden_action');
      const forgotPassLink = document.getElementById('forgot-pass-link');
      if (tab === 'masuk') {
        tabMasuk.classList.add('text-brand-600', 'font-bold');
        tabMasuk.classList.remove('text-slate-500', 'font-medium');
        tabDaftar.classList.remove('text-brand-600', 'font-bold');
        tabDaftar.classList.add('text-slate-500', 'font-medium');
        if (tabIndicator) { tabIndicator.style.left = '0%'; }
        // Switch hidden action ke masuk
        if (hiddenAction)   { hiddenAction.value = 'masuk'; }
        if (forgotPassLink) { forgotPassLink.classList.remove('hidden'); }
        const demoBox = document.getElementById('demo-account-box');
        if (demoBox) demoBox.classList.remove('hidden');
        // Switch to Masuk View
        formTitle.textContent = 'Masuk ke Akun Anda';
        formSubtitle.textContent = 'Selamat datang kembali! Silakan masuk untuk mengakses layanan.';
        groupFullName.style.display = 'none';
        groupNik.style.display = 'none';
        groupPasswordConfirm.style.display = 'none';
        if (groupPasswords) groupPasswords.classList.remove('sm:grid-cols-2');
        groupTerms.style.display = 'none';
        btnSubmitText.textContent = 'Masuk Sekarang';
        if (dividerText) dividerText.textContent = 'atau masuk dengan';
        bottomTextWrapper.innerHTML = 'Belum memiliki akun? <a class="font-bold text-brand-600 hover:text-brand-700 transition" href="#" id="link-daftar-bawah">Daftar di sini</a>';
        
        document.getElementById('link-daftar-bawah')?.addEventListener('click', (e) => {
          e.preventDefault();
          activateTab('daftar');
        });
      } else {
        tabDaftar.classList.add('text-brand-600', 'font-bold');
        tabDaftar.classList.remove('text-slate-500', 'font-medium');
        tabMasuk.classList.remove('text-brand-600', 'font-bold');
        tabMasuk.classList.add('text-slate-500', 'font-medium');
        if (tabIndicator) {
          tabIndicator.style.left = '50%';
        }
        // Reset hidden action ke daftar
        if (hiddenAction)   { hiddenAction.value = 'daftar'; }
        if (forgotPassLink) { forgotPassLink.classList.add('hidden'); }
        const demoBox = document.getElementById('demo-account-box');
        if (demoBox) demoBox.classList.add('hidden');
        // Switch to Daftar View
        formTitle.textContent = 'Buat Akun Warga';
        formSubtitle.textContent = 'Daftarkan diri Anda untuk kemudahan pelaporan & layanan lingkungan.';
        groupFullName.style.display = 'block';
        groupNik.style.display = 'block';
        groupPasswordConfirm.style.display = 'block';
        if (groupPasswords) groupPasswords.classList.add('sm:grid-cols-2');
        groupTerms.style.display = 'block';
        btnSubmitText.textContent = 'Daftar Sekarang';
        if (dividerText) dividerText.textContent = 'atau daftar dengan';
        bottomTextWrapper.innerHTML = 'Sudah memiliki akun? <a class="font-bold text-brand-600 hover:text-brand-700 transition" href="#" id="link-masuk-bawah">Masuk di sini</a>';
        
        document.getElementById('link-masuk-bawah')?.addEventListener('click', (e) => {
          e.preventDefault();
          activateTab('masuk');
        });
      }
    }

    function fillDemoLogin(email, pass) {
      const contactInput = document.getElementById('contact_info');
      const passInput = document.getElementById('password');
      if (contactInput) {
        contactInput.value = email;
        contactInput.dispatchEvent(new Event('input'));
      }
      if (passInput) {
        passInput.value = pass;
        passInput.dispatchEvent(new Event('input'));
      }
    }

    tabMasuk?.addEventListener('click', () => {
      activateTab('masuk');
    });

    tabDaftar?.addEventListener('click', () => {
      activateTab('daftar');
    });

    linkMasukBawah?.addEventListener('click', (e) => {
      e.preventDefault();
      activateTab('masuk');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Initial state based on PHP active tab
    <?php if ($active_tab === 'masuk'): ?>
    activateTab('masuk');
    <?php endif; ?>
  </script>
<!-- END: InteractiveScripts -->
</body></html>
