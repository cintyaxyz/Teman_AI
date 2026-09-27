<?php
require_once __DIR__ . '/auth.php';
// Jika sudah login, redirect ke beranda
if (isLoggedIn()) {
    header('Location: beranda.php');
    exit;
}
?>
<!DOCTYPE html>

<html class="scroll-smooth" lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Teman Warga AI - AI yang Mendengar, Membantu, dan Menghubungkan Warga</title>
<link rel="icon" type="image/svg+xml" href="logo.php"/>
<!-- Google Fonts: Outfit -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<!-- Load Tailwind CSS v3 with forms & container queries plugins -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#eef6ff',
              100: '#d9ebff',
              200: '#bcd9ff',
              300: '#8ec2ff',
              400: '#58a0ff',
              500: '#2f7eff',
              600: '#2563eb',
              700: '#1d4ed8',
              800: '#1e40af',
              900: '#1e3a8a',
            }
          },
          fontFamily: {
            sans: ['Outfit', 'Inter', 'system-ui', 'sans-serif'],
          }
        }
      }
    }
  </script>
<!-- BEGIN: Style - Custom Animations & Base Decorators -->
<style data-purpose="decorations">
    /* Mascot Float Animation */
    @keyframes floatRobot {
      0%, 100% {
        transform: translateY(0px) rotate(0deg);
      }
      50% {
        transform: translateY(-10px) rotate(0.8deg);
      }
    }

    /* Eye Blink Animation */
    @keyframes blinkEyes {
      0%, 92%, 100% {
        transform: scaleY(1);
      }
      96% {
        transform: scaleY(0.08);
      }
    }

    /* Ambient Glow Pulse */
    @keyframes pulseGlow {
      0%, 100% {
        opacity: 0.55;
        transform: scale(1);
      }
      50% {
        opacity: 0.85;
        transform: scale(1.06);
      }
    }

    /* Speech Bubble Floating Bounce */
    @keyframes bubbleFloat {
      0%, 100% {
        transform: translateY(0px);
      }
      50% {
        transform: translateY(-5px);
      }
    }

    /* Chest Power Breathing Effect */
    @keyframes chestPulse {
      0%, 100% {
        opacity: 0.9;
        transform: scale(1);
      }
      50% {
        opacity: 1;
        transform: scale(1.08);
      }
    }

    /* Waving Hand */
    @keyframes waveHand {
      0%, 100% { transform: rotate(0deg); }
      50% { transform: rotate(14deg); }
    }

    /* Soft Hero Stagger Fade-up */
    @keyframes heroFadeInUp {
      0% {
        opacity: 0;
        transform: translateY(18px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-float-robot {
      animation: floatRobot 4.8s ease-in-out infinite;
    }

    .animate-bubble-float {
      animation: bubbleFloat 3.6s ease-in-out infinite;
    }

    .robot-eyes {
      transform-origin: center;
      animation: blinkEyes 4.4s ease-in-out infinite;
    }

    .robot-chest-icon {
      transform-origin: 160px 222px;
      animation: chestPulse 2.8s ease-in-out infinite;
    }

    .waving-arm {
      transform-origin: 86px 204px;
      animation: waveHand 2.2s ease-in-out infinite;
    }

    .ambient-glow-pulse {
      animation: pulseGlow 5s ease-in-out infinite;
    }

    /* Staggered entrance classes */
    .fade-up-1 {
      animation: heroFadeInUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .fade-up-2 {
      animation: heroFadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.12s forwards;
      opacity: 0;
    }
    .fade-up-3 {
      animation: heroFadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.22s forwards;
      opacity: 0;
    }
    .fade-up-4 {
      animation: heroFadeInUp 0.75s cubic-bezier(0.16, 1, 0.3, 1) 0.32s forwards;
      opacity: 0;
    }
    .fade-up-cards {
      animation: heroFadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.42s forwards;
      opacity: 0;
    }

    /* Interactive Nav underline transition */
    .nav-link {
      position: relative;
    }
    .nav-link::after {
      content: '';
      position: absolute;
      bottom: -6px;
      left: 0;
      width: 0%;
      height: 2.5px;
      background-color: #2563eb;
      border-radius: 9999px;
      transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .nav-link:hover::after,
    .nav-link.active::after {
      width: 100%;
    }
    .nav-link.active {
      color: #2563eb;
      font-weight: 600;
    }

    /* FAQ Accordion Transitions */
    .faq-content {
      max-height: 0;
      opacity: 0;
      overflow: hidden;
      transition: max-height 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease-out, padding 0.25s ease-out;
    }
    .faq-item.active .faq-content {
      max-height: 420px;
      opacity: 1;
    }
    .faq-item.active .faq-chevron {
      transform: rotate(180deg);
    }
    .faq-item.active {
      border-color: #93c5fd;
      background-color: #ffffff;
      box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.08);
    }
    .feature-glow {
      box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.08);
    }

    /* Accessibility: Respect Reduced Motion */
    @media (prefers-reduced-motion: reduce) {
      *, ::before, ::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
      }
      .fade-up-1, .fade-up-2, .fade-up-3, .fade-up-4, .fade-up-cards {
        opacity: 1 !important;
        transform: none !important;
      }
    }

    /* ── Scroll Reveal ───────────────────────────────── */
    .reveal-up {
      opacity: 0 !important;
      transform: translateY(28px) !important;
      transition: opacity 0.65s cubic-bezier(0.16, 1, 0.3, 1),
                  transform 0.65s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
    .reveal-up.in-view {
      opacity: 1 !important;
      transform: translateY(0) !important;
    }
    .reveal-up.delay-1 { transition-delay: 0ms !important; }
    .reveal-up.delay-2 { transition-delay: 80ms !important; }
    .reveal-up.delay-3 { transition-delay: 160ms !important; }
    .reveal-up.delay-4 { transition-delay: 240ms !important; }
    .reveal-up.delay-5 { transition-delay: 320ms !important; }
    .reveal-up.delay-6 { transition-delay: 400ms !important; }
    @media (prefers-reduced-motion: reduce) {
      .reveal-up { opacity: 1 !important; transform: none !important; }
    }
  </style>
<!-- END: Style - Custom Animations & Base Decorators -->
</head>
<body class="bg-[#F8FAFD] text-slate-800 font-sans antialiased min-h-screen flex flex-col selection:bg-blue-100 selection:text-blue-700">
<!-- BEGIN: MainHeader -->
<header class="w-full bg-white/95 backdrop-blur-md border-b border-slate-100 sticky top-0 z-50 transition-colors duration-200">
<div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
<!-- Brand Logo -->
<a class="flex items-center gap-2.5 group cursor-pointer" href="landing-page.php">
<div class="w-10 h-10 rounded-xl overflow-hidden shadow-md shadow-brand-500/20 group-hover:scale-105 group-hover:shadow-brand-500/40 group-active:scale-95 transition-all duration-300 shrink-0">
  <img src="logo.php" class="w-full h-full object-cover" alt="Logo Teman Warga AI"/>
</div>
<span class="text-xl font-bold tracking-tight text-slate-900 transition-colors duration-200 group-hover:text-brand-600">
          Teman Warga <span class="text-brand-600">AI</span>
</span>
</a>
<!-- Center Navigation Links (Desktop) -->
<nav aria-label="Main Navigation" class="hidden md:flex items-center gap-9" id="mainNav">
<a class="nav-link text-sm font-semibold text-brand-600 active py-1 cursor-pointer transition-colors duration-200" data-nav="beranda" href="#beranda">Beranda</a>
<a class="nav-link text-sm font-medium text-slate-600 hover:text-brand-600 transition-colors duration-200 py-1 cursor-pointer" data-nav="fitur" href="#fitur">Fitur</a>
<a class="nav-link text-sm font-medium text-slate-600 hover:text-brand-600 transition-colors duration-200 py-1 cursor-pointer" data-nav="tentang" href="#tentang">Tentang</a>
<a class="nav-link text-sm font-medium text-slate-600 hover:text-brand-600 transition-colors duration-200 py-1 cursor-pointer" data-nav="faq" href="#faq">FAQ</a>
</nav>
<!-- Right Action CTA (Masuk / Daftar & Mobile Toggle) -->
<div class="flex items-center gap-4">
<a class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors duration-200 px-3 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer active:scale-95 hidden sm:inline-block" href="Halaman_daftar.php?tab=masuk">
          Masuk
</a>
<a class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 active:bg-brand-800 rounded-full shadow-md shadow-brand-500/25 transition-all duration-300 hover:shadow-lg hover:shadow-brand-500/40 hover:-translate-y-0.5 active:scale-95 cursor-pointer" href="Halaman_daftar.php?tab=daftar">
          Daftar
</a>
<!-- Mobile Hamburger Button -->
<button type="button" id="mobileMenuBtn" aria-label="Buka Menu" class="md:hidden w-10 h-10 rounded-xl bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-slate-600 transition-colors">
  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
  </svg>
</button>
</div>
</div>
<!-- Mobile Navigation Drawer -->
<div id="mobileNavMenu" class="hidden md:hidden border-t border-slate-100 bg-white/98 backdrop-blur-xl px-6 py-4 space-y-3">
  <a class="block py-2 text-sm font-semibold text-brand-600" href="#beranda" onclick="toggleMobileNav()">Beranda</a>
  <a class="block py-2 text-sm font-medium text-slate-600 hover:text-brand-600" href="#fitur" onclick="toggleMobileNav()">Fitur</a>
  <a class="block py-2 text-sm font-medium text-slate-600 hover:text-brand-600" href="#tentang" onclick="toggleMobileNav()">Tentang</a>
  <a class="block py-2 text-sm font-medium text-slate-600 hover:text-brand-600" href="#faq" onclick="toggleMobileNav()">FAQ</a>
  <div class="pt-2 border-t border-slate-100 flex gap-3">
    <a class="w-1/2 text-center py-2 text-sm font-semibold text-slate-700 bg-slate-50 rounded-lg hover:bg-slate-100" href="Halaman_daftar.php?tab=masuk">Masuk</a>
    <a class="w-1/2 text-center py-2 text-sm font-semibold text-white bg-brand-600 rounded-lg shadow-sm" href="Halaman_daftar.php?tab=daftar">Daftar</a>
  </div>
</div>
</header>
<!-- END: MainHeader -->
<!-- BEGIN: HeroSection -->
<main class="flex-1 relative overflow-hidden">
<!-- Background Ambient Glow & Cityscape Backdrop Pattern -->
<div class="absolute inset-0 pointer-events-none z-0">
<div class="ambient-glow-pulse absolute top-10 right-10 w-96 h-96 bg-blue-200/50 rounded-full blur-3xl"></div>
<div class="ambient-glow-pulse absolute bottom-20 left-10 w-80 h-80 bg-brand-100/50 rounded-full blur-2xl" style="animation-delay: -2.5s;"></div>
</div>
<section class="relative z-10 max-w-7xl mx-auto px-6 pt-10 pb-16 lg:py-14" id="beranda">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
<!-- Left Hero Content Column -->
<div class="lg:col-span-6 space-y-6 text-left" data-purpose="hero-copy-column">
<!-- Main Product Title -->
<h1 class="fade-up-1 text-4xl sm:text-5xl lg:text-[3.25rem] font-extrabold tracking-tight text-slate-900 leading-[1.18]">
            Teman Warga <span class="text-brand-600 inline-block transition-transform duration-300 hover:scale-105">AI</span>
</h1>
<!-- Key Tagline Headline -->
<p class="fade-up-2 text-2xl sm:text-3xl font-bold text-slate-800 leading-tight">
            AI yang Mendengar, Membantu,<br class="hidden sm:inline"/>
            dan Menghubungkan Warga.
          </p>
<!-- Descriptive Subtitle -->
<p class="fade-up-3 text-base sm:text-lg text-slate-500 leading-relaxed max-w-xl">
            Laporkan masalah lingkungan sekitar, dapatkan informasi layanan publik, dan berpartisipasi membangun lingkungan yang lebih baik bersama Teman Warga AI.
          </p>
<!-- Action Buttons Group -->
<div class="fade-up-4 flex flex-wrap items-center gap-4 pt-3">
<a class="group relative inline-flex items-center justify-center gap-2 px-7 py-3.5 text-base font-semibold text-white bg-brand-600 hover:bg-brand-700 active:bg-brand-800 rounded-full shadow-lg shadow-brand-500/25 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-brand-500/40 active:translate-y-0 active:scale-[0.98] overflow-hidden" href="Halaman_daftar.php">
<span class="relative z-10">Mulai Sekarang</span>
<svg class="relative z-10 w-4 h-4 transition-transform duration-300 ease-out group-hover:translate-x-1.5" fill="none" stroke="currentColor" viewbox="0 0 24 24">
<path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
</svg>
<span class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700 ease-in-out"></span>
</a>
<a class="inline-flex items-center justify-center px-7 py-3.5 text-base font-semibold text-brand-600 hover:text-brand-700 bg-white hover:bg-brand-50/50 border border-brand-200 hover:border-brand-400 rounded-full shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:scale-[1.02] active:scale-[0.98]" href="#fitur">
              Pelajari Lebih Lanjut
            </a>
</div>
</div>
<!-- Right Column - Friendly AI Robot Character with Cityscape & Chat Bubble -->
<div class="lg:col-span-6 relative flex items-center justify-center" data-purpose="hero-robot-illustration">
<div class="relative w-full max-w-md lg:max-w-lg aspect-square flex items-center justify-center">
<!-- Cityscape & Sky Background Illustration SVG -->
<svg class="absolute inset-0 w-full h-full pointer-events-none" fill="none" viewbox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
<defs>
<lineargradient gradientunits="userSpaceOnUse" id="skyGrad" x1="250" x2="250" y1="0" y2="500">
<stop stop-color="#E9F3FF" stop-opacity="0.85"></stop>
<stop offset="1" stop-color="#F8FAFD" stop-opacity="0.2"></stop>
</lineargradient>
<lineargradient id="cloudGrad" x1="0" x2="0" y1="0" y2="1">
<stop stop-color="#FFFFFF" stop-opacity="0.9"></stop>
<stop offset="1" stop-color="#F1F6FD" stop-opacity="0.6"></stop>
</lineargradient>
</defs>
<!-- Rounded Soft Background Circle Container -->
<circle class="ambient-glow-pulse origin-center" cx="250" cy="250" fill="url(#skyGrad)" r="230"></circle>
<!-- Distant Buildings Silhouette -->
<g opacity="0.45">
<rect fill="#BFDBFE" height="180" rx="3" width="45" x="70" y="210"></rect>
<rect fill="#93C5FD" height="220" rx="3" width="55" x="100" y="170"></rect>
<polygon fill="#93C5FD" points="127,130 115,170 140,170"></polygon>
<rect fill="#BFDBFE" height="160" rx="2" width="40" x="145" y="230"></rect>
<rect fill="#BFDBFE" height="195" rx="3" width="50" x="310" y="195"></rect>
<rect fill="#93C5FD" height="230" rx="3" width="60" x="345" y="160"></rect>
<polygon fill="#93C5FD" points="375,120 360,160 390,160"></polygon>
<rect fill="#BFDBFE" height="170" rx="2" width="45" x="390" y="220"></rect>
</g>
<!-- Closer City Buildings with Windows -->
<g opacity="0.8">
<!-- Left Building -->
<rect fill="#60A5FA" height="140" opacity="0.6" rx="4" width="50" x="90" y="250"></rect>
<circle cx="102" cy="270" fill="#FFFFFF" r="2"></circle>
<circle cx="115" cy="270" fill="#FFFFFF" r="2"></circle>
<circle cx="128" cy="270" fill="#FFFFFF" r="2"></circle>
<circle cx="102" cy="290" fill="#FFFFFF" r="2"></circle>
<circle cx="115" cy="290" fill="#FFFFFF" r="2"></circle>
<circle cx="128" cy="290" fill="#FFFFFF" r="2"></circle>
<!-- Right Building -->
<rect fill="#60A5FA" height="150" opacity="0.5" rx="4" width="55" x="360" y="240"></rect>
<rect fill="#FFFFFF" height="10" opacity="0.8" rx="1" width="8" x="370" y="260"></rect>
<rect fill="#FFFFFF" height="10" opacity="0.8" rx="1" width="8" x="385" y="260"></rect>
<rect fill="#FFFFFF" height="10" opacity="0.8" rx="1" width="8" x="398" y="260"></rect>
<rect fill="#FFFFFF" height="10" opacity="0.8" rx="1" width="8" x="370" y="280"></rect>
<rect fill="#FFFFFF" height="10" opacity="0.8" rx="1" width="8" x="385" y="280"></rect>
<rect fill="#FFFFFF" height="10" opacity="0.8" rx="1" width="8" x="398" y="280"></rect>
</g>
<!-- Trees & Ground Elements -->
<circle class="animate-pulse" cx="75" cy="385" fill="#34D399" opacity="0.7" r="22" style="animation-duration: 4s;"></circle>
<circle class="animate-pulse" cx="92" cy="390" fill="#10B981" opacity="0.8" r="16" style="animation-duration: 3s;"></circle>
<circle class="animate-pulse" cx="420" cy="385" fill="#34D399" opacity="0.7" r="24" style="animation-duration: 4s;"></circle>
<circle class="animate-pulse" cx="438" cy="390" fill="#10B981" opacity="0.8" r="18" style="animation-duration: 3s;"></circle>
<path d="M 50 420 Q 250 405 450 420 L 450 460 L 50 460 Z" fill="#E2E8F0" opacity="0.5"></path>
</svg>
<!-- Floating Robot Avatar Wrapper -->
<div class="relative z-10 flex flex-col items-center animate-float-robot">
<!-- Speech Bubble with smooth bounce -->
<div class="animate-bubble-float absolute -top-8 -left-4 sm:-left-8 bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-2xl p-4 shadow-xl shadow-slate-200/60 max-w-[240px] z-20 transition-transform duration-300 hover:scale-105 cursor-default">
<div class="relative text-left">
<p class="text-xs sm:text-sm font-semibold text-slate-800 leading-snug">
                    Halo! Saya <span class="text-brand-600 font-bold">Teman Warga AI</span>.
                  </p>
<p class="text-xs text-slate-500 mt-0.5">
                    Ada yang bisa saya bantu?
                  </p>
<!-- Speech Pointer/Tail -->
<div class="absolute -bottom-6 right-8 w-0 h-0 border-x-8 border-x-transparent border-t-8 border-t-white"></div>
</div>
</div>
<!-- Robot Figure SVG -->
<svg class="w-64 h-64 sm:w-80 sm:h-80 drop-shadow-2xl transition-transform duration-500 hover:scale-105" fill="none" viewbox="0 0 320 320" xmlns="http://www.w3.org/2000/svg">
<defs>
<!-- Shading Gradients for 3D White Robot Plastic -->
<radialgradient cx="40%" cy="30%" id="robotBodyGrad" r="65%">
<stop offset="0%" stop-color="#FFFFFF"></stop>
<stop offset="70%" stop-color="#F1F5F9"></stop>
<stop offset="100%" stop-color="#CBD5E1"></stop>
</radialgradient>
<lineargradient id="faceScreen" x1="0%" x2="0%" y1="0%" y2="100%">
<stop offset="0%" stop-color="#0F172A"></stop>
<stop offset="100%" stop-color="#1E293B"></stop>
</lineargradient>
<lineargradient id="neonBlue" x1="0%" x2="100%" y1="0%" y2="100%">
<stop offset="0%" stop-color="#38BDF8"></stop>
<stop offset="100%" stop-color="#2563EB"></stop>
</lineargradient>
</defs>
<!-- Antenna Rod and Glowing Orb with Ping -->
<rect fill="#94A3B8" height="24" rx="3" width="6" x="157" y="32"></rect>
<circle cx="160" cy="28" fill="url(#neonBlue)" r="10"></circle>
<circle class="animate-ping origin-center" cx="160" cy="28" fill="#38BDF8" opacity="0.6" r="10" style="transform-origin: 160px 28px; animation-duration: 3s;"></circle>
<circle cx="160" cy="28" fill="#BAE6FD" r="5"></circle>
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
<!-- Glowing Smiling Eyes -->
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
<!-- Chest Emblem / Screen -->
<rect fill="#E0F2FE" height="42" rx="14" stroke="#38BDF8" stroke-width="2" width="50" x="135" y="206"></rect>
<!-- AI Logo inside Chest with Pulse Animation -->
<g class="robot-chest-icon">
<path d="M 152 227 L 160 216 L 168 227 Z" fill="#0284C7"></path>
<rect fill="#0284C7" height="3" rx="1.5" width="8" x="156" y="231"></rect>
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
</div>
</div>
</div>
</section>
<!-- BEGIN: QuickFeatureCardsSection -->
<section class="fade-up-cards relative z-10 max-w-7xl mx-auto px-6 pb-16 pt-4" data-purpose="bottom-quick-cards">
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
<!-- Feature Card 1: Tanya AI -->
<div onclick="window.location.href='Ai_bot.php'" class="relative overflow-hidden bg-gradient-to-b from-blue-50/50 via-white to-white rounded-2xl p-6 border border-blue-100/80 shadow-sm hover:shadow-xl hover:-translate-y-2 hover:border-blue-300 transition-all duration-300 ease-out flex items-start gap-4 group cursor-pointer" title="Buka AI Chat Bot">
<div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-gradient-to-br from-blue-400/15 via-indigo-300/10 to-transparent blur-xl pointer-events-none group-hover:scale-125 transition-transform duration-500"></div>
<div class="relative z-10 w-14 h-14 rounded-2xl p-2.5 bg-gradient-to-br from-blue-100 via-sky-50 to-indigo-100 border border-blue-200/70 shadow-sm shadow-blue-500/10 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:rotate-6 transition-all duration-300 overflow-hidden">
<img src="Logo%20aksesori/messages%201.jpg" alt="Tanya AI" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform group-hover:scale-105 duration-300" />
</div>
<div class="relative z-10">
<h3 class="text-base font-bold text-slate-800 mb-1 group-hover:text-brand-600 transition-colors duration-200">Tanya AI</h3>
<p class="text-xs sm:text-sm text-slate-500 leading-relaxed group-hover:text-slate-600 transition-colors duration-200">
Dapatkan jawaban cepat seputar layanan publik.
</p>
</div>
</div>
<!-- Feature Card 2: Lapor Masalah -->
<div onclick="window.location.href='buat_laporan_Masyarakat.php'" class="relative overflow-hidden bg-gradient-to-b from-emerald-50/50 via-white to-white rounded-2xl p-6 border border-emerald-100/80 shadow-sm hover:shadow-xl hover:-translate-y-2 hover:border-emerald-300 transition-all duration-300 ease-out flex items-start gap-4 group cursor-pointer" title="Buat Laporan Lingkungan">
<div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-gradient-to-br from-emerald-400/15 via-teal-300/10 to-transparent blur-xl pointer-events-none group-hover:scale-125 transition-transform duration-500"></div>
<div class="relative z-10 w-14 h-14 rounded-2xl p-2.5 bg-gradient-to-br from-emerald-100 via-teal-50 to-green-100 border border-emerald-200/70 shadow-sm shadow-emerald-500/10 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:-rotate-6 transition-all duration-300 overflow-hidden">
<img src="Logo%20aksesori/Laporan.jpg" alt="Lapor Masalah" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform group-hover:scale-105 duration-300" />
</div>
<div class="relative z-10">
<h3 class="text-base font-bold text-slate-800 mb-1 group-hover:text-emerald-700 transition-colors duration-200">Lapor Masalah</h3>
<p class="text-xs sm:text-sm text-slate-500 leading-relaxed group-hover:text-slate-600 transition-colors duration-200">
Laporkan masalah lingkungan dengan mudah.
</p>
</div>
</div>
<!-- Feature Card 3: Pantau Lokasi -->
<div onclick="window.location.href='peta_laporan.php'" class="relative overflow-hidden bg-gradient-to-b from-amber-50/50 via-white to-white rounded-2xl p-6 border border-amber-100/80 shadow-sm hover:shadow-xl hover:-translate-y-2 hover:border-amber-300 transition-all duration-300 ease-out flex items-start gap-4 group cursor-pointer" title="Buka Peta Interaktif">
<div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-gradient-to-br from-amber-400/15 via-orange-300/10 to-transparent blur-xl pointer-events-none group-hover:scale-125 transition-transform duration-500"></div>
<div class="relative z-10 w-14 h-14 rounded-2xl p-2.5 bg-gradient-to-br from-amber-100 via-yellow-50 to-orange-100 border border-amber-200/70 shadow-sm shadow-amber-500/10 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:rotate-6 transition-all duration-300 overflow-hidden">
<img src="Logo%20aksesori/Maps.jpg" alt="Pantau Lokasi" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform group-hover:scale-105 duration-300" />
</div>
<div class="relative z-10">
<h3 class="text-base font-bold text-slate-800 mb-1 group-hover:text-amber-700 transition-colors duration-200">Pantau Lokasi</h3>
<p class="text-xs sm:text-sm text-slate-500 leading-relaxed group-hover:text-slate-600 transition-colors duration-200">
Lihat peta laporan secara real-time.
</p>
</div>
</div>
<!-- Feature Card 4: Forum Warga -->
<div onclick="window.location.href='forum_warga.php'" class="relative overflow-hidden bg-gradient-to-b from-indigo-50/50 via-white to-white rounded-2xl p-6 border border-indigo-100/80 shadow-sm hover:shadow-xl hover:-translate-y-2 hover:border-indigo-300 transition-all duration-300 ease-out flex items-start gap-4 group cursor-pointer" title="Buka Forum Diskusi Warga">
<div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-gradient-to-br from-indigo-400/15 via-purple-300/10 to-transparent blur-xl pointer-events-none group-hover:scale-125 transition-transform duration-500"></div>
<div class="relative z-10 w-14 h-14 rounded-2xl p-2.5 bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-100 border border-indigo-200/70 shadow-sm shadow-indigo-500/10 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:-rotate-6 transition-all duration-300 overflow-hidden">
<img src="Logo%20aksesori/Forum.jpg" alt="Forum Warga" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform group-hover:scale-105 duration-300" />
</div>
<div class="relative z-10">
<h3 class="text-base font-bold text-slate-800 mb-1 group-hover:text-indigo-700 transition-colors duration-200">Forum Warga</h3>
<p class="text-xs sm:text-sm text-slate-500 leading-relaxed group-hover:text-slate-600 transition-colors duration-200">
Diskusi dan berbagi informasi antar warga.
</p>
</div>
</div>
</div>
</section>
<!-- END: QuickFeatureCardsSection -->

<!-- ========================================== -->
<!-- BEGIN: SECTION 1 - FITUR UNGGULAN (#fitur) -->
<!-- ========================================== -->
<section id="fitur" class="scroll-mt-24 py-20 relative bg-gradient-to-b from-white via-slate-50/70 to-white border-y border-slate-100 overflow-hidden">
  <!-- Subtle ambient decorative circles -->
  <div class="absolute top-1/4 -left-32 w-96 h-96 bg-brand-100/40 rounded-full blur-3xl pointer-events-none"></div>
  <div class="absolute bottom-10 -right-32 w-96 h-96 bg-blue-100/40 rounded-full blur-3xl pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-6 relative z-10">
    <!-- Section Header -->
    <div class="text-center max-w-3xl mx-auto mb-16 reveal-up">
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
        Empat Pilar Layanan Modern <br class="hidden sm:inline"/>
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-blue-500">Teman Warga AI</span>
      </h2>
      <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed">
        Dirancang khusus untuk menghapus kerumitan birokrasi, mempercepat respons masalah lingkungan, dan mempererat kebersamaan antar warga secara real-time.
      </p>
    </div>

    <!-- 4 In-Depth Feature Showcase Cards (2x2 Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      
      <!-- Showcase 1: AI Chatbot 24/7 -->
      <div class="reveal-up delay-1 relative overflow-hidden bg-gradient-to-b from-blue-50/60 via-white to-white rounded-3xl p-8 sm:p-10 border border-blue-100/80 shadow-[0_8px_30px_rgb(59,130,246,0.06)] hover:shadow-[0_20px_40px_rgb(59,130,246,0.14)] hover:border-blue-300 hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group">
        <!-- Ambient decorative soft gradient glow -->
        <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full bg-gradient-to-br from-blue-400/15 via-indigo-300/10 to-transparent blur-2xl pointer-events-none group-hover:scale-125 transition-transform duration-500"></div>

        <div class="relative z-10 flex flex-col flex-1">
          <div class="flex items-center justify-between gap-4 mb-6">
            <!-- Cute Logo with Eye-Pleasing Gradient Badge -->
            <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-3xl p-3 bg-gradient-to-br from-blue-100 via-sky-50 to-indigo-100 border-2 border-white shadow-md shadow-blue-500/15 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 overflow-hidden shrink-0">
              <img src="Logo%20aksesori/messages%201.jpg" alt="Logo AI Assistant" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform group-hover:scale-105 duration-300" />
            </div>
            <div class="flex items-center gap-2 flex-wrap justify-end">
              <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100/80 text-emerald-800 border border-emerald-300/60 flex items-center gap-1.5 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Siaga 24/7
              </span>
              <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-800 border border-blue-200/80 shadow-2xs">
                ⚡ Respons &lt; 3 Detik
              </span>
            </div>
          </div>
          <h3 class="text-2xl font-bold text-slate-900 group-hover:text-brand-600 transition-colors">
            AI Assistant Chatbot Interaktif
          </h3>
          <p class="mt-3 text-slate-600 leading-relaxed text-sm sm:text-base">
            Konsultasikan syarat pengurusan berkas kependudukan, jam operasional posyandu, perizinan RT/RW, hingga info darurat. Asisten AI memahami bahasa sehari-hari dan langsung memberikan jawaban terverifikasi dari kelurahan.
          </p>
        </div>

        <div class="relative z-10 mt-8 pt-6 border-t border-slate-100 flex items-center justify-between gap-3">
          <span class="text-xs font-medium text-slate-400 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
            Didukung NLP Bahasa Indonesia
          </span>
          <a href="Ai_bot.php" class="inline-flex items-center gap-2 text-sm font-bold text-brand-600 group-hover:text-brand-700 bg-blue-50/80 hover:bg-blue-100/80 px-4 py-2.5 rounded-xl transition-all shadow-2xs hover:shadow-xs group-hover:translate-x-0.5">
            Buka AI Chat 
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </a>
        </div>
      </div>

      <!-- Showcase 2: Lapor Masalah Lingkungan -->
      <div class="reveal-up delay-2 relative overflow-hidden bg-gradient-to-b from-emerald-50/60 via-white to-white rounded-3xl p-8 sm:p-10 border border-emerald-100/80 shadow-[0_8px_30px_rgb(16,185,129,0.06)] hover:shadow-[0_20px_40px_rgb(16,185,129,0.14)] hover:border-emerald-300 hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group">
        <!-- Ambient decorative soft gradient glow -->
        <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full bg-gradient-to-br from-emerald-400/15 via-teal-300/10 to-transparent blur-2xl pointer-events-none group-hover:scale-125 transition-transform duration-500"></div>

        <div class="relative z-10 flex flex-col flex-1">
          <div class="flex items-center justify-between gap-4 mb-6">
            <!-- Cute Logo with Eye-Pleasing Gradient Badge -->
            <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-3xl p-3 bg-gradient-to-br from-emerald-100 via-teal-50 to-green-100 border-2 border-white shadow-md shadow-emerald-500/15 flex items-center justify-center group-hover:scale-110 group-hover:-rotate-3 transition-all duration-300 overflow-hidden shrink-0">
              <img src="Logo%20aksesori/Kamera.jpg" alt="Logo Lapor Masalah" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform group-hover:scale-105 duration-300" />
            </div>
            <div class="flex items-center gap-2 flex-wrap justify-end">
              <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-emerald-50 to-teal-50 text-emerald-800 border border-emerald-200/80 shadow-2xs flex items-center gap-1">
                🎯 Auto-Geotagging
              </span>
              <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100/90 text-slate-700 border border-slate-200/80 shadow-2xs">
                ✨ Prioritas AI
              </span>
            </div>
          </div>
          <h3 class="text-2xl font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">
            Lapor Masalah &amp; Validasi Geotagging
          </h3>
          <p class="mt-3 text-slate-600 leading-relaxed text-sm sm:text-base">
            Foto jalan berlubang, lampu PJU padam, atau tumpukan sampah. Kecerdasan buatan langsung mengenali kategori masalah, mengunci koordinat GPS secara akurat, dan mendisposisikan tiket resmi ke dinas terkait.
          </p>
        </div>

        <div class="relative z-10 mt-8 pt-6 border-t border-slate-100 flex items-center justify-between gap-3">
          <span class="text-xs font-medium text-slate-400 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            Transparan &amp; Bisa Dipantau
          </span>
          <a href="buat_laporan_Masyarakat.php" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-700 group-hover:text-emerald-800 bg-emerald-50/80 hover:bg-emerald-100/80 px-4 py-2.5 rounded-xl transition-all shadow-2xs hover:shadow-xs group-hover:translate-x-0.5">
            Kirim Laporan Sekarang
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </a>
        </div>
      </div>

      <!-- Showcase 3: Peta Wilayah Real-Time -->
      <div class="reveal-up delay-3 relative overflow-hidden bg-gradient-to-b from-amber-50/60 via-white to-white rounded-3xl p-8 sm:p-10 border border-amber-100/80 shadow-[0_8px_30px_rgb(245,158,11,0.06)] hover:shadow-[0_20px_40px_rgb(245,158,11,0.14)] hover:border-amber-300 hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group">
        <!-- Ambient decorative soft gradient glow -->
        <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full bg-gradient-to-br from-amber-400/15 via-orange-300/10 to-transparent blur-2xl pointer-events-none group-hover:scale-125 transition-transform duration-500"></div>

        <div class="relative z-10 flex flex-col flex-1">
          <div class="flex items-center justify-between gap-4 mb-6">
            <!-- Cute Logo with Eye-Pleasing Gradient Badge -->
            <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-3xl p-3 bg-gradient-to-br from-amber-100 via-yellow-50 to-orange-100 border-2 border-white shadow-md shadow-amber-500/15 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 overflow-hidden shrink-0">
              <img src="Logo%20aksesori/Maps.jpg" alt="Logo Peta Wilayah" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform group-hover:scale-105 duration-300" />
            </div>
            <div class="flex items-center gap-2 flex-wrap justify-end">
              <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-amber-50 to-orange-50 text-amber-800 border border-amber-200/80 shadow-2xs flex items-center gap-1">
                🧭 Peta Interaktif
              </span>
              <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100/90 text-slate-700 border border-slate-200/80 shadow-2xs">
                📍 Cluster Pin Dinamis
              </span>
            </div>
          </div>
          <h3 class="text-2xl font-bold text-slate-900 group-hover:text-amber-700 transition-colors">
            Peta Sebaran &amp; Pantauan Wilayah
          </h3>
          <p class="mt-3 text-slate-600 leading-relaxed text-sm sm:text-base">
            Eksplorasi peta digital interaktif lingkungan Anda. Warga dapat memantau titik-titik kendala lingkungan yang sedang ditangani, melihat wilayah hijau bebas masalah, hingga mendeteksi genangan air saat hujan.
          </p>
        </div>

        <div class="relative z-10 mt-8 pt-6 border-t border-slate-100 flex items-center justify-between gap-3">
          <span class="text-xs font-medium text-slate-400 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
            Peta Berbasis OpenStreetMap
          </span>
          <a href="peta_laporan.php" class="inline-flex items-center gap-2 text-sm font-bold text-amber-700 group-hover:text-amber-800 bg-amber-50/80 hover:bg-amber-100/80 px-4 py-2.5 rounded-xl transition-all shadow-2xs hover:shadow-xs group-hover:translate-x-0.5">
            Jelajahi Peta Laporan
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </a>
        </div>
      </div>

      <!-- Showcase 4: Forum Komunitas Warga -->
      <div class="reveal-up delay-4 relative overflow-hidden bg-gradient-to-b from-indigo-50/60 via-white to-white rounded-3xl p-8 sm:p-10 border border-indigo-100/80 shadow-[0_8px_30px_rgb(99,102,241,0.06)] hover:shadow-[0_20px_40px_rgb(99,102,241,0.14)] hover:border-indigo-300 hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group">
        <!-- Ambient decorative soft gradient glow -->
        <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full bg-gradient-to-br from-purple-400/15 via-pink-300/10 to-transparent blur-2xl pointer-events-none group-hover:scale-125 transition-transform duration-500"></div>

        <div class="relative z-10 flex flex-col flex-1">
          <div class="flex items-center justify-between gap-4 mb-6">
            <!-- Cute Logo with Eye-Pleasing Gradient Badge -->
            <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-3xl p-3 bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-100 border-2 border-white shadow-md shadow-indigo-500/15 flex items-center justify-center group-hover:scale-110 group-hover:-rotate-3 transition-all duration-300 overflow-hidden shrink-0">
              <img src="Logo%20aksesori/Forum.jpg" alt="Logo Forum Warga" class="w-full h-full object-contain mix-blend-multiply drop-shadow-xs transition-transform group-hover:scale-105 duration-300" />
            </div>
            <div class="flex items-center gap-2 flex-wrap justify-end">
              <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-800 border border-indigo-200/80 shadow-2xs flex items-center gap-1">
                💬 Ruang Aspirasi
              </span>
              <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100/90 text-slate-700 border border-slate-200/80 shadow-2xs">
                🛡️ Warga Terverifikasi
              </span>
            </div>
          </div>
          <h3 class="text-2xl font-bold text-slate-900 group-hover:text-indigo-700 transition-colors">
            Forum Komunitas Warga Terpadu
          </h3>
          <p class="mt-3 text-slate-600 leading-relaxed text-sm sm:text-base">
            Ruang musyawarah digital warga untuk saling bertukar informasi lingkungan, menggalang gotong royong, menyampaikan ide pembangunan, serta menerima pengumuman resmi dari Ketua RT/RW dan Lurah.
          </p>
        </div>

        <div class="relative z-10 mt-8 pt-6 border-t border-slate-100 flex items-center justify-between gap-3">
          <span class="text-xs font-medium text-slate-400 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
            Bebas Hoaks &amp; Dimoderasi Santun
          </span>
          <a href="forum_warga.php" class="inline-flex items-center gap-2 text-sm font-bold text-indigo-700 group-hover:text-indigo-800 bg-indigo-50/80 hover:bg-indigo-100/80 px-4 py-2.5 rounded-xl transition-all shadow-2xs hover:shadow-xs group-hover:translate-x-0.5">
            Masuk ke Forum Warga
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </a>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- END: SECTION 1 - FITUR UNGGULAN -->

<!-- ========================================== -->
<!-- BEGIN: SECTION 2 - TENTANG (#tentang)      -->
<!-- ========================================== -->
<section id="tentang" class="scroll-mt-24 py-20 relative bg-white overflow-hidden">
  <div class="max-w-7xl mx-auto px-6 relative z-10">
    <!-- Header Tentang -->
    <div class="text-center max-w-3xl mx-auto mb-16 reveal-up">
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
        Membangun Jembatan Kolaborasi Nyata <br class="hidden sm:inline"/>
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-indigo-600">Antara Warga &amp; Pemerintah</span>
      </h2>
      <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed">
        Kami percaya bahwa lingkungan yang aman, bersih, dan harmonis bermula dari kemudahan masyarakat dalam bersuara dan kecepatan dinas dalam menindaklanjuti.
      </p>
    </div>

    <!-- 2-Column Story & 3-Step Journey -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center mb-16">
      
      <!-- Left Column: Story & 3 Core Values -->
      <div class="lg:col-span-6 space-y-6 reveal-up delay-1">
        <div class="bg-gradient-to-br from-brand-50/60 to-slate-50 p-8 rounded-3xl border border-brand-100">
          <h3 class="text-xl font-bold text-slate-900 mb-3">Dari Masalah Menjadi Solusi Tanpa Berbelit</h3>
          <p class="text-sm sm:text-base text-slate-600 leading-relaxed mb-6">
            Teman Warga AI hadir untuk meniadakan keluhan warga yang hilang tanpa kejelasan kabar, antrean panjang di loket pelayanan kelurahan, serta minimnya transparansi proses pengerjaan fasilitas umum.
          </p>

          <div class="space-y-4">
            <div class="flex items-start gap-3.5">
              <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center shrink-0 text-sm font-bold shadow-xs">✓</div>
              <div>
                <h4 class="text-sm font-bold text-slate-900">Kecepatan &amp; Otomatisasi AI</h4>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Sistem memverifikasi dan mendistribusikan laporan ke dinas berwenang dalam hitungan menit.</p>
              </div>
            </div>
            <div class="flex items-start gap-3.5">
              <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center shrink-0 text-sm font-bold shadow-xs">✓</div>
              <div>
                <h4 class="text-sm font-bold text-slate-900">Transparansi 100% Bebas Rahasia</h4>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Seluruh progres tiket dari Menunggu, Diproses, hingga Selesai dapat dipantau langsung oleh publik.</p>
              </div>
            </div>
            <div class="flex items-start gap-3.5">
              <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center shrink-0 text-sm font-bold shadow-xs">✓</div>
              <div>
                <h4 class="text-sm font-bold text-slate-900">Inklusif &amp; Melindungi Privasi</h4>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Antarmuka ramah untuk semua usia, data NIK disamarkan, dan mendukung opsi pelaporan rahasia/anonim.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: 3-Step Citizen Journey Workflow -->
      <div class="lg:col-span-6 space-y-4 reveal-up delay-2">
        <h3 class="text-xs font-bold uppercase tracking-wider text-brand-600 mb-2">Bagaimana Teman Warga AI Bekerja</h3>

        <!-- Step 1 Card -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4">
          <div class="w-12 h-12 rounded-2xl bg-blue-100 text-brand-700 flex items-center justify-center text-lg font-black shrink-0">1</div>
          <div>
            <h4 class="text-base font-bold text-slate-900">Warga Mengadu atau Bertanya</h4>
            <p class="text-sm text-slate-600 mt-1">Cukup ambil foto kendala lingkungan atau ajukan pertanyaan dokumen administrasi dari perangkat Anda.</p>
          </div>
        </div>

        <!-- Step 2 Card -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4">
          <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-lg font-black shrink-0">2</div>
          <div>
            <h4 class="text-base font-bold text-slate-900">AI Memvalidasi &amp; Teruskan ke Dinas</h4>
            <p class="text-sm text-slate-600 mt-1">Kecerdasan buatan memverifikasi koordinat GPS, mengelompokkan kategori, dan menerbitkan tiket dinas resmi.</p>
          </div>
        </div>

        <!-- Step 3 Card -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4">
          <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg font-black shrink-0">3</div>
          <div>
            <h4 class="text-base font-bold text-slate-900">Penanganan Lapangan &amp; Selesai</h4>
            <p class="text-sm text-slate-600 mt-1">Petugas menuntaskan perbaikan fisik, mengunggah bukti foto selesai, dan notifikasi konfirmasi dikirim ke warga.</p>
          </div>
        </div>
      </div>

    </div>

    <!-- Impact & Trust Statistics Bar -->
    <div class="bg-gradient-to-r from-slate-900 via-brand-900 to-slate-900 rounded-3xl p-8 sm:p-12 text-white shadow-xl relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-blue-400/20 via-transparent to-transparent pointer-events-none"></div>
      
      <div class="relative z-10 grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-white/10">
        <div class="pt-4 md:pt-0">
          <div class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-white">&lt; 15 Mnt</div>
          <p class="text-xs sm:text-sm text-blue-200/80 mt-1.5 font-medium">Rata-rata Respon Pertama</p>
        </div>
        <div class="pt-4 md:pt-0">
          <div class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-white">99.2%</div>
          <p class="text-xs sm:text-sm text-blue-200/80 mt-1.5 font-medium">Validasi Laporan Akurat</p>
        </div>
        <div class="pt-4 md:pt-0">
          <div class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-white">12+</div>
          <p class="text-xs sm:text-sm text-blue-200/80 mt-1.5 font-medium">Kelurahan Terintegrasi</p>
        </div>
        <div class="pt-4 md:pt-0">
          <div class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-white">4.9 / 5.0</div>
          <p class="text-xs sm:text-sm text-blue-200/80 mt-1.5 font-medium">Indeks Kepuasan Warga</p>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- END: SECTION 2 - TENTANG -->

<!-- ========================================== -->
<!-- BEGIN: SECTION 3 - FAQ (#faq)             -->
<!-- ========================================== -->
<section id="faq" class="scroll-mt-24 py-20 relative bg-slate-50/70 border-t border-slate-100 overflow-hidden">
  <div class="max-w-4xl mx-auto px-6 relative z-10">
    <!-- Header FAQ -->
    <div class="text-center max-w-2xl mx-auto mb-14">
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
        Pertanyaan yang Sering Diajukan
      </h2>
      <p class="mt-4 text-base text-slate-600 leading-relaxed">
        Punya pertanyaan seputar Teman Warga AI? Temukan jawaban lengkap mengenai layanan, privasi, dan alur pelaporan di bawah ini.
      </p>
    </div>

    <!-- Interactive FAQ Accordion List -->
    <div class="space-y-4" id="faqAccordion">

      <!-- FAQ Item 1 (Active by default) -->
      <div class="faq-item active bg-white rounded-2xl border border-slate-200/80 transition-all duration-300 overflow-hidden cursor-pointer" onclick="toggleFaq(this)">
        <div class="p-6 flex items-center justify-between gap-4">
          <h3 class="text-base sm:text-lg font-bold text-slate-900">
            Apa itu Teman Warga AI dan siapa saja yang dapat menggunakannya?
          </h3>
          <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-slate-500 faq-chevron transition-transform duration-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>
        <div class="faq-content px-6 pb-6 text-sm sm:text-base text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
          Teman Warga AI adalah portal pelayanan publik terpadu berbasis kecerdasan buatan untuk seluruh warga. Setiap penduduk di wilayah terdaftar dapat mengakses informasi kelurahan, membuat laporan fasilitas lingkungan rusak, memantau peta wilayah, serta berdiskusi di forum warga secara langsung tanpa perlu repot datang ke kantor kelurahan.
        </div>
      </div>

      <!-- FAQ Item 2 -->
      <div class="faq-item bg-white rounded-2xl border border-slate-200/80 transition-all duration-300 overflow-hidden cursor-pointer" onclick="toggleFaq(this)">
        <div class="p-6 flex items-center justify-between gap-4">
          <h3 class="text-base sm:text-lg font-bold text-slate-900">
            Apakah seluruh layanan dan fitur di Teman Warga AI bebas biaya (gratis)?
          </h3>
          <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-slate-500 faq-chevron transition-transform duration-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>
        <div class="faq-content px-6 pb-6 text-sm sm:text-base text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
          Ya, 100% Gratis! Seluruh masyarakat dapat memanfaatkan asisten AI, membuat tiket laporan, mengakses peta wilayah, dan bergabung ke forum komunitas tanpa dipungut biaya apa pun. Inisiatif ini didukung untuk mewujudkan smart governance yang merata.
        </div>
      </div>

      <!-- FAQ Item 3 -->
      <div class="faq-item bg-white rounded-2xl border border-slate-200/80 transition-all duration-300 overflow-hidden cursor-pointer" onclick="toggleFaq(this)">
        <div class="p-6 flex items-center justify-between gap-4">
          <h3 class="text-base sm:text-lg font-bold text-slate-900">
            Berapa lama laporan masalah lingkungan akan ditindaklanjuti oleh petugas?
          </h3>
          <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-slate-500 faq-chevron transition-transform duration-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>
        <div class="faq-content px-6 pb-6 text-sm sm:text-base text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
          Setelah Anda mengirimkan laporan dengan foto dan koordinat GPS, sistem AI akan memverifikasi dan mendisposisikan tiket dalam waktu kurang dari 15 menit ke dashboard petugas dinas terkait. Penanganan di lapangan disesuaikan dengan tingkat urgensi (biasanya 1x24 jam untuk laporan mendesak seperti pohon tumbang atau kabel terputus).
        </div>
      </div>

      <!-- FAQ Item 4 -->
      <div class="faq-item bg-white rounded-2xl border border-slate-200/80 transition-all duration-300 overflow-hidden cursor-pointer" onclick="toggleFaq(this)">
        <div class="p-6 flex items-center justify-between gap-4">
          <h3 class="text-base sm:text-lg font-bold text-slate-900">
            Bagaimana keamanan data identitas pribadi (NIK &amp; Nomor HP) saya?
          </h3>
          <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-slate-500 faq-chevron transition-transform duration-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>
        <div class="faq-content px-6 pb-6 text-sm sm:text-base text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
          Keamanan privasi Anda adalah prioritas utama. Kami menerapkan standar enkripsi ketat. NIK Anda otomatis disamarkan (masked) sehingga tidak dapat dilihat oleh publik. Anda juga dapat memilih opsi 'Lapor Anonim' saat mengirimkan aduan tertentu bila diperlukan.
        </div>
      </div>

      <!-- FAQ Item 5 -->
      <div class="faq-item bg-white rounded-2xl border border-slate-200/80 transition-all duration-300 overflow-hidden cursor-pointer" onclick="toggleFaq(this)">
        <div class="p-6 flex items-center justify-between gap-4">
          <h3 class="text-base sm:text-lg font-bold text-slate-900">
            Apakah saya harus mengunduh aplikasi terpisah di Play Store atau App Store?
          </h3>
          <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-slate-500 faq-chevron transition-transform duration-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>
        <div class="faq-content px-6 pb-6 text-sm sm:text-base text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
          Tidak perlu menginstal aplikasi apa pun. Teman Warga AI dibangun dengan teknologi web responsif modern yang ringan dan cepat. Anda cukup membuka browser di handphone, tablet, atau laptop Anda tanpa membebani kapasitas memori penyimpanan ponsel.
        </div>
      </div>

      <!-- FAQ Item 6 -->
      <div class="faq-item bg-white rounded-2xl border border-slate-200/80 transition-all duration-300 overflow-hidden cursor-pointer" onclick="toggleFaq(this)">
        <div class="p-6 flex items-center justify-between gap-4">
          <h3 class="text-base sm:text-lg font-bold text-slate-900">
            Bagaimana jika terjadi situasi darurat (kebakaran, medis, atau keamanan)?
          </h3>
          <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-slate-500 faq-chevron transition-transform duration-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>
        <div class="faq-content px-6 pb-6 text-sm sm:text-base text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
          Untuk keadaan darurat yang mengancam nyawa, segera hubungi tombol Bantuan Darurat Call Center 112 yang selalu tersedia di pojok antarmuka web, atau telepon darurat kelurahan. Petugas posko siaga darurat akan langsung terhubung tanpa menunggu antrean tiket.
        </div>
      </div>

    </div>

    <!-- Still Have Questions Box -->
    <div class="mt-12 p-8 rounded-3xl bg-white border border-slate-200/90 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6 text-center sm:text-left reveal-up">
      <div>
        <h4 class="text-lg font-bold text-slate-900">Masih punya pertanyaan yang belum terjawab?</h4>
        <p class="text-sm text-slate-500 mt-1">Asisten AI kami siap berdialog dan memberikan panduan 24 jam nonstop.</p>
      </div>
      <a href="Ai_bot.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/25 transition-all hover:-translate-y-0.5 shrink-0">
        Tanya Asisten AI Sekarang
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
      </a>
    </div>

    <!-- Conversion Call To Action Banner -->
    <div class="mt-16 rounded-3xl bg-gradient-to-r from-brand-600 via-blue-600 to-indigo-700 p-8 sm:p-12 text-center text-white shadow-xl relative overflow-hidden reveal-up">
      <div class="relative z-10 max-w-2xl mx-auto space-y-4">
        <span class="inline-block px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold uppercase tracking-wider">Ayo Mulai Hari Ini</span>
        <h3 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">Wujudkan Lingkungan Tempat Tinggal yang Nyaman &amp; Teratur</h3>
        <p class="text-sm sm:text-base text-blue-100 leading-relaxed">
          Bergabunglah sekarang bersama ribuan warga lainnya dan rasakan kemudahan pelayanan publik berbasis AI.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-4 pt-4">
          <a href="Halaman_daftar.php?tab=daftar" class="px-8 py-3.5 rounded-full bg-white text-brand-700 hover:bg-blue-50 font-bold text-sm shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
            Daftar Akun Warga
          </a>
          <a href="beranda.php" class="px-8 py-3.5 rounded-full bg-white/10 hover:bg-white/20 border border-white/30 text-white font-semibold text-sm backdrop-blur-sm transition-all">
            Masuk ke Beranda
          </a>
        </div>
      </div>
    </div>

  </div>
</section>
<!-- END: SECTION 3 - FAQ -->

</main>
<!-- END: HeroSection & Main Content -->

<!-- BEGIN: MainFooter -->
<footer class="bg-white border-t border-slate-200 py-12">
  <div class="max-w-7xl mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
      
      <!-- Brand & Info -->
      <div class="md:col-span-2 space-y-4">
        <a class="flex items-center gap-2.5" href="landing-page.php">
          <div class="w-10 h-10 rounded-xl overflow-hidden shadow-sm shrink-0">
            <img src="logo.php" class="w-full h-full object-cover" alt="Logo Teman Warga AI"/>
          </div>
          <span class="text-xl font-bold tracking-tight text-slate-900">
            Teman Warga <span class="text-brand-600">AI</span>
          </span>
        </a>
        <p class="text-sm text-slate-500 max-w-sm leading-relaxed">
          Platform pelayanan publik cerdas yang mendengarkan aspirasi, mempercepat penanganan fasilitas lingkungan, dan mempererat kebersamaan antar warga.
        </p>
      </div>

      <!-- Quick Nav Links -->
      <div>
        <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Navigasi Halaman</h4>
        <ul class="space-y-2.5 text-sm text-slate-500">
          <li><a class="hover:text-brand-600 transition-colors" href="#beranda">Beranda</a></li>
          <li><a class="hover:text-brand-600 transition-colors" href="#fitur">Fitur Unggulan</a></li>
          <li><a class="hover:text-brand-600 transition-colors" href="#tentang">Tentang Platform</a></li>
          <li><a class="hover:text-brand-600 transition-colors" href="#faq">Pusat Tanya Jawab (FAQ)</a></li>
        </ul>
      </div>

      <!-- Services Links -->
      <div>
        <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Akses Cepat Layanan</h4>
        <ul class="space-y-2.5 text-sm text-slate-500">
          <li><a class="hover:text-brand-600 transition-colors" href="Ai_bot.php">Tanya AI Assistant</a></li>
          <li><a class="hover:text-brand-600 transition-colors" href="buat_laporan_Masyarakat.php">Buat Laporan Lingkungan</a></li>
          <li><a class="hover:text-brand-600 transition-colors" href="peta_laporan.php">Peta Sebaran Wilayah</a></li>
          <li><a class="hover:text-brand-600 transition-colors" href="forum_warga.php">Forum Musyawarah Warga</a></li>
        </ul>
      </div>

    </div>

    <!-- Bottom Copyright -->
    <div class="pt-8 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs sm:text-sm text-slate-400">
      <p>© 2026 Teman Warga AI. Seluruh hak cipta dilindungi undang-undang.</p>
      <div class="flex items-center gap-6">
        <a class="hover:text-brand-600 transition-colors" href="kebijakan_privasi.php">Kebijakan Privasi</a>
        <a class="hover:text-brand-600 transition-colors" href="syarat_ketentuan.php">Syarat &amp; Ketentuan</a>
        <a class="hover:text-brand-600 transition-colors" href="tel:112">Bantuan Darurat 112</a>
      </div>
    </div>
  </div>
</footer>
<!-- END: MainFooter -->

<!-- Interactive Scripts for FAQ Accordion, ScrollSpy, and Mobile Menu -->
<script>
  // FAQ Accordion Toggle Function
  function toggleFaq(element) {
    const allItems = document.querySelectorAll('.faq-item');
    const isCurrentlyActive = element.classList.contains('active');

    // Close other items for neat single-accordion UX
    allItems.forEach(item => {
      if (item !== element) {
        item.classList.remove('active');
      }
    });

    // Toggle selected item
    if (isCurrentlyActive) {
      element.classList.remove('active');
    } else {
      element.classList.add('active');
    }
  }

  // Mobile Navigation Drawer Toggle
  function toggleMobileNav() {
    const mobileMenu = document.getElementById('mobileNavMenu');
    if (mobileMenu) {
      mobileMenu.classList.toggle('hidden');
    }
  }

  document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleMobileNav);

  // Active ScrollSpy for Header Navigation Links
  window.addEventListener('scroll', () => {
    const sections = ['beranda', 'fitur', 'tentang', 'faq'];
    const scrollPosition = window.scrollY + 120; // 120px offset for header

    sections.forEach(sectionId => {
      const section = document.getElementById(sectionId);
      const navLink = document.querySelector(`[data-nav="${sectionId}"]`);

      if (section && navLink) {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.offsetHeight;

        if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
          document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active', 'text-brand-600', 'font-semibold');
            link.classList.add('text-slate-600', 'font-medium');
          });
          navLink.classList.add('active', 'text-brand-600', 'font-semibold');
          navLink.classList.remove('text-slate-600', 'font-medium');
        }
      }
    });
  });

  // ── Scroll-Reveal: Intersection Observer untuk Landing Page ──
  (function() {
    var revealEls = document.querySelectorAll('.reveal-up');
    if (!revealEls.length) return;

    function showEl(el) {
      el.classList.add('in-view');
    }

    if (!('IntersectionObserver' in window)) {
      // Fallback: langsung tampilkan semua
      revealEls.forEach(function(el) { showEl(el); });
      return;
    }

    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          showEl(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0,
      rootMargin: '60px 0px 0px 0px'
    });

    revealEls.forEach(function(el) { observer.observe(el); });
  })();
</script>
</body></html>