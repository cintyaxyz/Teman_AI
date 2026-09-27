<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/nav_sidebar.php';
requireLogin();

$currentUser = getCurrentUser();
$currentUserName = $currentUser ? htmlspecialchars($currentUser['nama']) : 'Warga';
$currentUserRole = $currentUser ? htmlspecialchars(ucfirst($currentUser['role'] ?? 'Warga')) : 'Warga';
$unreadCount = getUnreadNotificationCount();

// Teman Warga AI - Forum Warga
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"/><meta content="width=device-width, initial-scale=1.0" name="viewport"/><meta content="web_dashboard" name="shell-type"/>
<title>Forum Warga - Teman Warga AI</title>
<link rel="icon" type="image/svg+xml" href="logo.php"/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/><style>@layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}main>:first-child{margin-top:0!important;}main>:last-child{margin-bottom:0!important;}}::-webkit-scrollbar{display:none;}
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

</style><script src="https://cdn.tailwindcss.com"></script><script id="tailwind-config">tailwind.config={darkMode:"class",theme:{extend:{colors:{"surface-container-low":"#eff4ff","surface-container-lowest":"#ffffff","outline":"#737686","on-background":"#0b1c30","outline-variant":"#c3c6d7","surface-container-highest":"#d3e4fe","surface-dim":"#cbdbf5","on-primary":"#ffffff","error-container":"#ffdad6","surface-container":"#e5eeff","tertiary":"#005b7c","surface":"#f8f9ff","secondary-fixed-dim":"#93ccff","primary-fixed-dim":"#b4c5ff","on-secondary-fixed":"#001d31","on-surface-variant":"#434655","on-primary-fixed-variant":"#003ea8","secondary-fixed":"#cce5ff","surface-variant":"#d3e4fe","on-surface":"#0b1c30","primary":"#004ac6","secondary":"#006398","secondary-container":"#5bb8fe","primary-container":"#2563eb","primary-fixed":"#dbe1ff","on-tertiary":"#ffffff","background":"#f8f9ff","on-tertiary-fixed-variant":"#004c69","on-primary-fixed":"#00174b","tertiary-fixed-dim":"#7bd0ff","on-secondary-fixed-variant":"#004b73","surface-bright":"#f8f9ff","inverse-surface":"#213145","on-error-container":"#93000a","surface-container-high":"#dce9ff","on-tertiary-container":"#e1f2ff","surface-tint":"#0053db","on-primary-container":"eeefff","on-tertiary-fixed":"#001e2c","tertiary-container":"#00759f","inverse-on-surface":"#eaf1ff","on-secondary":"#ffffff","on-secondary-container":"#00476e","inverse-primary":"#b4c5ff","tertiary-fixed":"#c4e7ff","error":"#ba1a1a","on-error":"#ffffff"},borderRadius:{"DEFAULT":"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px"},spacing:{"space-md":"1rem","space-lg":"1.5rem","space-sm":"0.5rem","space-xs":"0.25rem","gutter":"1.25rem","margin":"2rem","space-xl":"2rem"},fontFamily:{"headline-md":["Outfit","sans-serif"],"label-sm":["Outfit","sans-serif"],"body-md":["Outfit","sans-serif"],"headline-lg":["Outfit","sans-serif"],"body-sm":["Outfit","sans-serif"],"headline-xl":["Outfit","sans-serif"],"body-lg":["Outfit","sans-serif"],"display-lg":["Outfit","sans-serif"],"label-lg":["Outfit","sans-serif"],"label-md":["Outfit","sans-serif"]},fontSize:{"headline-md":["18px",{"lineHeight":"26px","fontWeight":"600"}],"label-sm":["11px",{"lineHeight":"14px","letterSpacing":"0.02em","fontWeight":"600"}],"body-md":["14px",{"lineHeight":"22px","fontWeight":"400"}],"headline-lg":["22px",{"lineHeight":"30px","letterSpacing":"-0.01em","fontWeight":"700"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"headline-xl":["28px",{"lineHeight":"36px","letterSpacing":"-0.015em","fontWeight":"700"}],"body-lg":["16px",{"lineHeight":"24px","fontWeight":"400"}],"display-lg":["40px",{"lineHeight":"48px","letterSpacing":"-0.02em","fontWeight":"800"}],"label-lg":["14px",{"lineHeight":"20px","fontWeight":"600"}],"label-md":["12px",{"lineHeight":"16px","letterSpacing":"0.01em","fontWeight":"600"}]}}}};</script></head><body class="bg-gradient-to-b from-surface-container-lowest to-surface font-body-md text-body-md text-on-surface min-h-screen"><header class="fixed top-0 left-0 right-0 h-16 z-50 bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] px-gutter flex items-center justify-between gap-space-lg"><?php renderHeaderBrand(); ?><div class="flex-1 max-w-xl mx-auto"><div class="relative flex items-center w-full"><span class="material-symbols-outlined absolute left-space-md text-outline text-[20px] pointer-events-none">search</span><input class="w-full pl-11 pr-space-md py-2 bg-surface-container-low rounded-xl text-on-surface placeholder-outline font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 transition-all" placeholder="Cari bantuan, laporan, atau informasi..." type="text"/></div></div><div class="flex items-center gap-space-md shrink-0"><a href="notifikasi.php" aria-label="Notifikasi" class="relative w-10 h-10 rounded-xl bg-surface-container-low hover:bg-surface-container flex items-center justify-center text-on-surface-variant transition-colors"><span class="material-symbols-outlined text-[22px]">notifications</span><?php if ($unreadCount > 0): ?><span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-error ring-2 ring-surface-container-lowest"></span><?php endif; ?></a><a href="Profile.php" class="flex items-center gap-space-sm pl-space-sm py-1 pr-space-sm rounded-xl hover:bg-surface-container-low cursor-pointer transition-colors"><div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center"><span class="material-symbols-outlined text-on-primary text-[18px]">person</span></div><div class="hidden md:flex flex-col text-left"><span class="font-label-md text-label-md text-on-surface"><?php echo $currentUserName; ?></span><span class="font-label-sm text-label-sm text-outline"><?php echo $currentUserRole; ?></span></div><span class="material-symbols-outlined text-outline text-[18px]">keyboard_arrow_down</span></a></div></header><?php renderSidebarNav('forum-warga'); ?><div class="pl-64"><main class="w-full pt-16 px-margin py-margin"><div class="flex flex-col w-full">
<!-- Interactive Style Hooks & Keyframe Animations -->
<style>
    @keyframes subtleEntrance {
      0% {
        opacity: 0;
        transform: translateY(14px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .topic-enter-1 { animation: subtleEntrance 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .topic-enter-2 { animation: subtleEntrance 0.45s 0.08s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .topic-enter-3 { animation: subtleEntrance 0.45s 0.16s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .topic-enter-4 { animation: subtleEntrance 0.45s 0.24s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .metric-enter  { animation: subtleEntrance 0.5s 0.32s cubic-bezier(0.16, 1, 0.3, 1) both; }
  </style>
<!-- 1. Page Header with CTA & Meta Info -->
<header class="flex flex-col md:flex-row md:items-center justify-between gap-space-md mb-space-lg">
<div class="space-y-1">
<div class="flex items-center gap-space-xs">
<h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight">Forum Warga</h1>
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-label-sm font-semibold bg-primary-fixed text-on-primary-fixed">
          Aktif
        </span>
</div>
<p class="font-body-md text-body-md text-on-surface-variant">
        Diskusi, berbagi informasi, dan cari solusi bersama secara transparan.
      </p>
</div>
<!-- Buat Topik CTA Button with Popover Dialog Trigger -->
<div class="flex items-center gap-space-sm shrink-0">
<button class="inline-flex items-center gap-2 px-space-lg py-2.5 bg-primary text-on-primary rounded-xl font-label-lg text-label-lg shadow-sm hover:shadow-md hover:bg-primary/95 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 focus:outline-none cursor-pointer" id="btn-buat-topik" onclick="toggleModal(true)" type="button">
<span class="material-symbols-outlined text-[20px] transition-transform duration-200 group-hover:rotate-90">add</span>
<span>Buat Topik</span>
</button>
</div>
</header>
<!-- 2. Search Bar & Filter Tabs Row -->
<section class="bg-surface-container-lowest rounded-2xl p-space-md shadow-sm mb-space-lg flex flex-col md:flex-row items-stretch md:items-center justify-between gap-space-md">
<!-- Category Filter Tabs -->
<div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5" id="category-filters">
<button class="filter-pill active-pill px-4 py-2 rounded-xl text-label-md font-label-md bg-primary text-on-primary transition-all duration-200 shadow-sm cursor-pointer" data-cat="all" onclick="filterCategory('all', this)" type="button">
        Semua
      </button>
<button class="filter-pill px-4 py-2 rounded-xl text-label-md font-label-md text-on-surface-variant bg-surface-container-low hover:bg-surface-container hover:text-on-surface transition-all duration-200 cursor-pointer" data-cat="Lingkungan" onclick="filterCategory('Lingkungan', this)" type="button">
        Lingkungan
      </button>
<button class="filter-pill px-4 py-2 rounded-xl text-label-md font-label-md text-on-surface-variant bg-surface-container-low hover:bg-surface-container hover:text-on-surface transition-all duration-200 cursor-pointer" data-cat="Layanan Publik" onclick="filterCategory('Layanan Publik', this)" type="button">
        Layanan Publik
      </button>
<button class="filter-pill px-4 py-2 rounded-xl text-label-md font-label-md text-on-surface-variant bg-surface-container-low hover:bg-surface-container hover:text-on-surface transition-all duration-200 cursor-pointer" data-cat="Sampah" onclick="filterCategory('Sampah', this)" type="button">
        Kebersihan &amp; Sampah
      </button>
<button class="filter-pill px-4 py-2 rounded-xl text-label-md font-label-md text-on-surface-variant bg-surface-container-low hover:bg-surface-container hover:text-on-surface transition-all duration-200 cursor-pointer" data-cat="Infrastruktur" onclick="filterCategory('Infrastruktur', this)" type="button">
        Infrastruktur
      </button>
<button class="filter-pill px-4 py-2 rounded-xl text-label-md font-label-md text-on-surface-variant bg-surface-container-low hover:bg-surface-container hover:text-on-surface transition-all duration-200 cursor-pointer" data-cat="Lainnya" onclick="filterCategory('Lainnya', this)" type="button">
        Lainnya
      </button>
</div>
<!-- Search Input Bar -->
<div class="relative w-full md:w-80 shrink-0">
<span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[20px] pointer-events-none">search</span>
<input class="w-full pl-10 pr-9 py-2 bg-surface-container-low text-on-surface placeholder:text-outline font-body-sm text-body-sm rounded-xl focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/25 transition-all duration-200" id="search-input" onkeyup="filterTopicsBySearch(this.value)" placeholder="Cari topik diskusi..." type="text"/>
<button class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface p-0.5 rounded-lg transition-colors cursor-pointer" id="clear-search-btn" onclick="clearSearch()" type="button">
<span class="material-symbols-outlined text-[18px]">close</span>
</button>
</div>
</section>
<!-- 3. Discussion Topic List (Interactive Staggered Cards) -->
<main class="flex flex-col gap-space-md mb-space-xl" id="topic-container">
<!-- Topik 1 -->
<article class="topic-card topic-enter-1 bg-surface-container-lowest p-space-md md:p-space-lg rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-1 hover:bg-surface-container-lowest transition-all duration-300 group cursor-pointer" data-category="Lingkungan" data-title="Solusi untuk mengatasi banjir di Perumahan Melati" onclick="toggleTopicDetails(this)">
<div class="flex items-start gap-space-md">
<!-- Avatar User with hover scale -->
<div class="shrink-0 relative overflow-hidden rounded-full w-12 h-12 shadow-sm ring-2 ring-blue-100 flex items-center justify-center bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-sm tracking-wider uppercase select-none group-hover:scale-105 transition-transform duration-300">
<span>AP</span>
</div>
<!-- Topic Center Information -->
<div class="flex-1 min-w-0">
<div class="flex items-center gap-space-xs flex-wrap mb-1">
<span class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors duration-200 line-clamp-1">
              Solusi untuk mengatasi banjir di Perumahan Melati
            </span>
</div>
<!-- Metadata -->
<div class="flex flex-wrap items-center gap-x-2 gap-y-1 font-body-sm text-body-sm text-on-surface-variant">
<span class="font-label-md text-label-md text-on-surface">Andi Pratama</span>
<span class="text-outline text-xs">&bull;</span>
<span class="text-outline">2 jam yang lalu</span>
<span class="text-outline text-xs">&bull;</span>
<span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-primary-fixed text-on-primary-fixed-variant">
              Lingkungan
            </span>
</div>
<!-- Expandable Excerpt Content -->
<p class="topic-excerpt font-body-md text-body-md text-on-surface-variant mt-2 line-clamp-2 transition-all">
            Saluran air di blok B dan C mulai tersumbat lumpur pasca hujan deras kemarin malam. Apakah ada inisiatif kerja bakti akhir pekan ini atau usulan pompa darurat ke pihak kelurahan?
          </p>
</div>
<!-- Right Counters & Badges -->
<div class="flex items-center gap-space-md shrink-0 pt-1 self-start">
<div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-surface-container-low text-on-surface-variant group-hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined text-[18px] text-outline">visibility</span>
<span class="font-label-md text-label-md text-on-surface font-semibold">24</span>
</div>
<div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-surface-container-low text-on-surface-variant group-hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined text-[18px] text-primary">chat_bubble</span>
<span class="font-label-md text-label-md text-on-surface font-semibold">8</span>
</div>
<button aria-label="Bookmark topik" class="p-1.5 rounded-xl text-outline hover:text-primary hover:bg-surface-container transition-all cursor-pointer" onclick="event.stopPropagation(); toggleBookmark(this)" type="button">
<span class="material-symbols-outlined text-[20px]">bookmark_border</span>
</button>
</div>
</div>
</article>
<!-- Topik 2 -->
<article class="topic-card topic-enter-2 bg-surface-container-lowest p-space-md md:p-space-lg rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-1 hover:bg-surface-container-lowest transition-all duration-300 group cursor-pointer" data-category="Sampah" data-title="Jadwal pengambilan sampah di wilayah RT 03" onclick="toggleTopicDetails(this)">
<div class="flex items-start gap-space-md">
<!-- Avatar User with hover scale -->
<div class="shrink-0 relative overflow-hidden rounded-full w-12 h-12 shadow-sm ring-2 ring-emerald-100 flex items-center justify-center bg-gradient-to-tr from-emerald-600 to-teal-600 text-white font-bold text-sm tracking-wider uppercase select-none group-hover:scale-105 transition-transform duration-300">
<span>SN</span>
</div>
<!-- Topic Center Information -->
<div class="flex-1 min-w-0">
<div class="flex items-center gap-space-xs flex-wrap mb-1">
<span class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors duration-200 line-clamp-1">
              Jadwal pengambilan sampah di wilayah RT 03
            </span>
</div>
<!-- Metadata -->
<div class="flex flex-wrap items-center gap-x-2 gap-y-1 font-body-sm text-body-sm text-on-surface-variant">
<span class="font-label-md text-label-md text-on-surface">Siti Nurhaliza</span>
<span class="text-outline text-xs">&bull;</span>
<span class="text-outline">5 jam yang lalu</span>
<span class="text-outline text-xs">&bull;</span>
<span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-secondary-fixed text-on-secondary-fixed-variant">
              Sampah
            </span>
</div>
<!-- Expandable Excerpt Content -->
<p class="topic-excerpt font-body-md text-body-md text-on-surface-variant mt-2 line-clamp-2 transition-all">
            Truk pengangkut sampah sudah dua hari tidak terlihat di jalan mawar. Mohon info apakah ada penyesuaian armada dari dinas terkait minggu ini?
          </p>
</div>
<!-- Right Counters & Badges -->
<div class="flex items-center gap-space-md shrink-0 pt-1 self-start">
<div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-surface-container-low text-on-surface-variant group-hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined text-[18px] text-outline">visibility</span>
<span class="font-label-md text-label-md text-on-surface font-semibold">15</span>
</div>
<div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-surface-container-low text-on-surface-variant group-hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined text-[18px] text-primary">chat_bubble</span>
<span class="font-label-md text-label-md text-on-surface font-semibold">3</span>
</div>
<button aria-label="Bookmark topik" class="p-1.5 rounded-xl text-outline hover:text-primary hover:bg-surface-container transition-all cursor-pointer" onclick="event.stopPropagation(); toggleBookmark(this)" type="button">
<span class="material-symbols-outlined text-[20px]">bookmark_border</span>
</button>
</div>
</div>
</article>
<!-- Topik 3 -->
<article class="topic-card topic-enter-3 bg-surface-container-lowest p-space-md md:p-space-lg rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-1 hover:bg-surface-container-lowest transition-all duration-300 group cursor-pointer" data-category="Infrastruktur" data-title="Lampu jalan di Jl. Kenanga mati lagi" onclick="toggleTopicDetails(this)">
<div class="flex items-start gap-space-md">
<!-- Avatar User with hover scale -->
<div class="shrink-0 relative overflow-hidden rounded-full w-12 h-12 shadow-sm ring-2 ring-amber-100 flex items-center justify-center bg-gradient-to-tr from-amber-600 to-orange-600 text-white font-bold text-sm tracking-wider uppercase select-none group-hover:scale-105 transition-transform duration-300">
<span>BS</span>
</div>
<!-- Topic Center Information -->
<div class="flex-1 min-w-0">
<div class="flex items-center gap-space-xs flex-wrap mb-1">
<span class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors duration-200 line-clamp-1">
              Lampu jalan di Jl. Kenanga mati lagi
            </span>
</div>
<!-- Metadata -->
<div class="flex flex-wrap items-center gap-x-2 gap-y-1 font-body-sm text-body-sm text-on-surface-variant">
<span class="font-label-md text-label-md text-on-surface">Budi Santoso</span>
<span class="text-outline text-xs">&bull;</span>
<span class="text-outline">8 jam yang lalu</span>
<span class="text-outline text-xs">&bull;</span>
<span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-tertiary-fixed text-on-tertiary-fixed-variant">
              Infrastruktur
            </span>
</div>
<!-- Expandable Excerpt Content -->
<p class="topic-excerpt font-body-md text-body-md text-on-surface-variant mt-2 line-clamp-2 transition-all">
            Penerangan jalan umum di tiang nomor 14 dan 16 kembali padam sejak semalam. Area persimpangan jadi cukup gelap dan berbahaya saat gerimis malam.
          </p>
</div>
<!-- Right Counters & Badges -->
<div class="flex items-center gap-space-md shrink-0 pt-1 self-start">
<div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-surface-container-low text-on-surface-variant group-hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined text-[18px] text-outline">visibility</span>
<span class="font-label-md text-label-md text-on-surface font-semibold">12</span>
</div>
<div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-surface-container-low text-on-surface-variant group-hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined text-[18px] text-primary">chat_bubble</span>
<span class="font-label-md text-label-md text-on-surface font-semibold">5</span>
</div>
<button aria-label="Bookmark topik" class="p-1.5 rounded-xl text-outline hover:text-primary hover:bg-surface-container transition-all cursor-pointer" onclick="event.stopPropagation(); toggleBookmark(this)" type="button">
<span class="material-symbols-outlined text-[20px]">bookmark_border</span>
</button>
</div>
</div>
</article>
<!-- Topik 4 -->
<article class="topic-card topic-enter-4 bg-surface-container-lowest p-space-md md:p-space-lg rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-1 hover:bg-surface-container-lowest transition-all duration-300 group cursor-pointer" data-category="Layanan Publik" data-title="Informasi pelayanan pembuatan KTP" onclick="toggleTopicDetails(this)">
<div class="flex items-start gap-space-md">
<!-- Avatar User with hover scale -->
<div class="shrink-0 relative overflow-hidden rounded-full w-12 h-12 shadow-sm ring-2 ring-purple-100 flex items-center justify-center bg-gradient-to-tr from-purple-600 to-pink-600 text-white font-bold text-sm tracking-wider uppercase select-none group-hover:scale-105 transition-transform duration-300">
<span>RW</span>
</div>
<!-- Topic Center Information -->
<div class="flex-1 min-w-0">
<div class="flex items-center gap-space-xs flex-wrap mb-1">
<span class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors duration-200 line-clamp-1">
              Informasi pelayanan pembuatan KTP
            </span>
</div>
<!-- Metadata -->
<div class="flex flex-wrap items-center gap-x-2 gap-y-1 font-body-sm text-body-sm text-on-surface-variant">
<span class="font-label-md text-label-md text-on-surface">Rina Wulandari</span>
<span class="text-outline text-xs">&bull;</span>
<span class="text-outline">1 hari yang lalu</span>
<span class="text-outline text-xs">&bull;</span>
<span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-primary-fixed text-on-primary-fixed-variant">
              Layanan Publik
            </span>
</div>
<!-- Expandable Excerpt Content -->
<p class="topic-excerpt font-body-md text-body-md text-on-surface-variant mt-2 line-clamp-2 transition-all">
            Bagi warga yang ingin cetak ulang e-KTP rusak atau rekam baru usia 17 tahun, loket kelurahan buka kuota antrian online via bot Teman Warga setiap hari kerja.
          </p>
</div>
<!-- Right Counters & Badges -->
<div class="flex items-center gap-space-md shrink-0 pt-1 self-start">
<div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-surface-container-low text-on-surface-variant group-hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined text-[18px] text-outline">visibility</span>
<span class="font-label-md text-label-md text-on-surface font-semibold">30</span>
</div>
<div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-surface-container-low text-on-surface-variant group-hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined text-[18px] text-primary">chat_bubble</span>
<span class="font-label-md text-label-md text-on-surface font-semibold">12</span>
</div>
<button aria-label="Bookmark topik" class="p-1.5 rounded-xl text-outline hover:text-primary hover:bg-surface-container transition-all cursor-pointer" onclick="event.stopPropagation(); toggleBookmark(this)" type="button">
<span class="material-symbols-outlined text-[20px]">bookmark_border</span>
</button>
</div>
</div>
</article>
<!-- Empty State Fallback -->
<div class="hidden flex-col items-center justify-center p-12 bg-surface-container-lowest rounded-2xl text-center shadow-sm" id="empty-state">
<div class="w-14 h-14 rounded-2xl bg-surface-container-low flex items-center justify-center mb-3 text-outline">
<span class="material-symbols-outlined text-[32px]">manage_search</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-1">Topik Tidak Ditemukan</h3>
<p class="font-body-md text-body-md text-on-surface-variant max-w-sm mb-4">
        Tidak ada diskusi yang cocok dengan kata kunci atau filter yang Anda pilih.
      </p>
<button class="px-4 py-2 bg-surface-container text-primary font-label-md text-label-md rounded-xl hover:bg-surface-container-high transition-colors cursor-pointer" onclick="resetFilters()" type="button">
        Reset Filter
      </button>
</div>
</main>
<!-- 4. Three Elevated Metric Summary Cards -->
<section class="metric-enter grid grid-cols-1 md:grid-cols-3 gap-space-lg mb-space-lg">
<!-- Card 1: TOTAL DISKUSI -->
<div class="bg-surface-container-lowest rounded-2xl p-space-lg shadow-sm hover:shadow-md hover:-translate-y-1.5 transition-all duration-300 flex items-center gap-space-md group">
<div class="w-14 h-14 rounded-xl bg-surface-container-low flex items-center justify-center text-primary group-hover:scale-110 transition-transform duration-300 shrink-0">
<span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">forum</span>
</div>
<div class="flex flex-col">
<span class="font-label-sm text-label-sm uppercase tracking-wider text-outline font-bold">TOTAL DISKUSI</span>
<span class="font-display-lg text-display-lg text-on-surface font-extrabold leading-none mt-1">148</span>
<span class="font-body-sm text-body-sm text-tertiary-container mt-1 flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">trending_up</span>
<span>+12 minggu ini</span>
</span>
</div>
</div>
<!-- Card 2: WARGA BERPARTISIPASI -->
<div class="bg-surface-container-lowest rounded-2xl p-space-lg shadow-sm hover:shadow-md hover:-translate-y-1.5 transition-all duration-300 flex items-center gap-space-md group">
<div class="w-14 h-14 rounded-xl bg-surface-container-low flex items-center justify-center text-secondary group-hover:scale-110 transition-transform duration-300 shrink-0">
<span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">groups</span>
</div>
<div class="flex flex-col">
<span class="font-label-sm text-label-sm uppercase tracking-wider text-outline font-bold">WARGA BERPARTISIPASI</span>
<span class="font-display-lg text-display-lg text-on-surface font-extrabold leading-none mt-1">1,240</span>
<span class="font-body-sm text-body-sm text-tertiary-container mt-1 flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">trending_up</span>
<span>+48 warga baru</span>
</span>
</div>
</div>
<!-- Card 3: SOLUSI DISEPAKATI -->
<div class="bg-surface-container-lowest rounded-2xl p-space-lg shadow-sm hover:shadow-md hover:-translate-y-1.5 transition-all duration-300 flex items-center gap-space-md group">
<div class="w-14 h-14 rounded-xl bg-surface-container-high flex items-center justify-center text-primary group-hover:scale-110 transition-transform duration-300 shrink-0">
<span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">verified</span>
</div>
<div class="flex flex-col">
<span class="font-label-sm text-label-sm uppercase tracking-wider text-outline font-bold">SOLUSI DISEPAKATI</span>
<span class="font-display-lg text-display-lg text-on-surface font-extrabold leading-none mt-1">89</span>
<span class="font-body-sm text-body-sm text-tertiary-container mt-1 flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">task_alt</span>
<span>60% tuntas</span>
</span>
</div>
</div>
</section>
<!-- Interactive Modal for "+ Buat Topik" -->
<aside aria-labelledby="modal-title" aria-modal="true" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-on-background/40 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200" id="modal-buat-topik" role="dialog">
<div class="bg-surface-container-lowest rounded-2xl w-full max-w-lg p-space-lg shadow-2xl transform scale-95 transition-transform duration-200" id="modal-box">
<div class="flex items-center justify-between pb-space-sm mb-space-md">
<div class="flex items-center gap-2">
<div class="w-8 h-8 rounded-lg bg-surface-container-low flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-[20px]">add_comment</span>
</div>
<h2 class="font-headline-md text-headline-md text-on-surface" id="modal-title">Buat Topik Diskusi Baru</h2>
</div>
<button class="p-1 rounded-lg text-outline hover:text-on-surface hover:bg-surface-container-low transition-colors cursor-pointer" onclick="toggleModal(false)" type="button">
<span class="material-symbols-outlined text-[20px]">close</span>
</button>
</div>
<form class="space-y-space-md" id="form-topik-baru" onsubmit="handleNewTopic(event)">
<div>
<label class="block font-label-md text-label-md text-on-surface mb-1">Kategori Masalah</label>
<select class="w-full px-space-md py-2.5 bg-surface-container-low rounded-xl text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/25 transition-all cursor-pointer" id="input-kategori" required="">
<option value="Lingkungan">Lingkungan &amp; Sanitasi</option>
<option value="Layanan Publik">Layanan Publik &amp; Dokumen</option>
<option value="Sampah">Sampah &amp; Kebersihan</option>
<option value="Infrastruktur">Infrastruktur &amp; Jalan</option>
<option value="Lainnya">Lainnya</option>
</select>
</div>
<div>
<label class="block font-label-md text-label-md text-on-surface mb-1">Judul Topik</label>
<input class="w-full px-space-md py-2 bg-surface-container-low rounded-xl text-on-surface font-body-md text-body-md placeholder:text-outline focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/25 transition-all" id="input-judul" placeholder="Tulis judul diskusi yang jelas dan spesifik..." required="" type="text"/>
</div>
<div>
<label class="block font-label-md text-label-md text-on-surface mb-1">Keterangan / Uraian Masalah</label>
<textarea class="w-full px-space-md py-2 bg-surface-container-low rounded-xl text-on-surface font-body-md text-body-md placeholder:text-outline focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/25 transition-all resize-none" id="input-deskripsi" placeholder="Jelaskan kronologi, titik lokasi, dan solusi yang diusulkan..." required="" rows="3"></textarea>
</div>
<div class="flex items-center justify-end gap-space-sm pt-space-sm">
<button class="px-4 py-2 rounded-xl font-label-lg text-label-lg text-on-surface-variant bg-surface-container-low hover:bg-surface-container transition-colors cursor-pointer" onclick="toggleModal(false)" type="button">
            Batal
          </button>
<button class="px-5 py-2 rounded-xl font-label-lg text-label-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm active:scale-95 transition-all cursor-pointer" type="submit">
            Terbitkan Topik
          </button>
</div>
</form>
</div>
</aside>
<!-- Interactive Vanilla JS Logic -->
<script>
    let activeFilter = 'all';

    function toggleModal(show) {
      const modal = document.getElementById('modal-buat-topik');
      const box = document.getElementById('modal-box');
      if (show) {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        box.classList.remove('scale-95');
        box.classList.add('scale-100');
        setTimeout(() => document.getElementById('input-judul').focus(), 100);
      } else {
        modal.classList.add('opacity-0', 'pointer-events-none');
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
      }
    }

    function filterCategory(category, buttonEl) {
      activeFilter = category;
      const pills = document.querySelectorAll('#category-filters button');
      pills.forEach(pill => {
        pill.className = 'filter-pill px-4 py-2 rounded-xl text-label-md font-label-md text-on-surface-variant bg-surface-container-low hover:bg-surface-container hover:text-on-surface transition-all duration-200 cursor-pointer';
      });
      buttonEl.className = 'filter-pill px-4 py-2 rounded-xl text-label-md font-label-md bg-primary text-on-primary transition-all duration-200 shadow-sm cursor-pointer';
      applyAllFilters();
    }

    function filterTopicsBySearch(val) {
      const clearBtn = document.getElementById('clear-search-btn');
      if (val.trim().length > 0) {
        clearBtn.classList.remove('hidden');
      } else {
        clearBtn.classList.add('hidden');
      }
      applyAllFilters();
    }

    function clearSearch() {
      const input = document.getElementById('search-input');
      input.value = '';
      document.getElementById('clear-search-btn').classList.add('hidden');
      applyAllFilters();
      input.focus();
    }

    function applyAllFilters() {
      const query = document.getElementById('search-input').value.toLowerCase().trim();
      const cards = document.querySelectorAll('.topic-card');
      let visibleCount = 0;

      cards.forEach(card => {
        const cat = card.getAttribute('data-category');
        const title = card.getAttribute('data-title').toLowerCase();
        const matchesCategory = (activeFilter === 'all' || cat === activeFilter);
        const matchesQuery = (!query || title.includes(query));

        if (matchesCategory && matchesQuery) {
          card.classList.remove('hidden');
          card.classList.add('flex');
          visibleCount++;
        } else {
          card.classList.add('hidden');
          card.classList.remove('flex');
        }
      });

      const emptyState = document.getElementById('empty-state');
      if (visibleCount === 0) {
        emptyState.classList.remove('hidden');
        emptyState.classList.add('flex');
      } else {
        emptyState.classList.add('hidden');
        emptyState.classList.remove('flex');
      }
    }

    function resetFilters() {
      clearSearch();
      const firstTab = document.querySelector('#category-filters button[data-cat="all"]');
      if (firstTab) filterCategory('all', firstTab);
    }

    function toggleTopicDetails(cardElement) {
      const excerpt = cardElement.querySelector('.topic-excerpt');
      if (excerpt) {
        excerpt.classList.toggle('line-clamp-2');
      }
    }

    function toggleBookmark(btn) {
      const icon = btn.querySelector('.material-symbols-outlined');
      if (icon.textContent === 'bookmark_border') {
        icon.textContent = 'bookmark';
        icon.classList.add('text-primary');
        icon.style.fontVariationSettings = "'FILL' 1";
      } else {
        icon.textContent = 'bookmark_border';
        icon.classList.remove('text-primary');
        icon.style.fontVariationSettings = "'FILL' 0";
      }
    }

    function handleNewTopic(e) {
      e.preventDefault();
      const title = document.getElementById('input-judul').value.trim();
      const cat = document.getElementById('input-kategori').value;
      const desc = document.getElementById('input-deskripsi').value.trim();
      if (!title || !desc) return;

      const container = document.getElementById('topic-container');
      const newCard = document.createElement('article');
      newCard.className = 'topic-card bg-surface-container-lowest p-space-md md:p-space-lg rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 group cursor-pointer animate-fade-in flex';
      newCard.setAttribute('data-category', cat);
      newCard.setAttribute('data-title', title);
      newCard.onclick = function() { toggleTopicDetails(this); };

      newCard.innerHTML = `
        <div class="flex items-start gap-space-md w-full">
          <div class="shrink-0 relative overflow-hidden rounded-full w-12 h-12 shadow-sm ring-2 ring-blue-100 group-hover:scale-105 transition-transform duration-300 flex items-center justify-center bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-sm tracking-wider uppercase select-none">
            <span>RA</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-space-xs flex-wrap mb-1">
              <span class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors duration-200 line-clamp-1">
                ${escapeHTML(title)}
              </span>
              <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-primary text-on-primary">Baru</span>
            </div>
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 font-body-sm text-body-sm text-on-surface-variant">
              <span class="font-label-md text-label-md text-on-surface">Rasya (Anda)</span>
              <span class="text-outline text-xs">&bull;</span>
              <span class="text-outline">Baru saja</span>
              <span class="text-outline text-xs">&bull;</span>
              <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm bg-primary-fixed text-on-primary-fixed-variant">
                ${escapeHTML(cat)}
              </span>
            </div>
            <p class="topic-excerpt font-body-md text-body-md text-on-surface-variant mt-2 line-clamp-2 transition-all">
              ${escapeHTML(desc)}
            </p>
          </div>
          <div class="flex items-center gap-space-md shrink-0 pt-1 self-start">
            <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-surface-container-low text-on-surface-variant">
              <span class="material-symbols-outlined text-[18px] text-outline">visibility</span>
              <span class="font-label-md text-label-md text-on-surface font-semibold">1</span>
            </div>
            <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-surface-container-low text-on-surface-variant">
              <span class="material-symbols-outlined text-[18px] text-primary">chat_bubble</span>
              <span class="font-label-md text-label-md text-on-surface font-semibold">0</span>
            </div>
          </div>
        </div>
      `;

      container.insertBefore(newCard, container.firstChild);
      toggleModal(false);
      document.getElementById('form-topik-baru').reset();
    }

    function escapeHTML(str) {
      return str.replace(/[&<>'"]/g, 
        tag => ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          "'": '&#39;',
          '"': '&quot;'
        }[tag] || tag)
      );
    }
  </script>
</div></main></div>

</body></html>
