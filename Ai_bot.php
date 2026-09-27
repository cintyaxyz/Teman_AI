<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/nav_sidebar.php';
requireLogin();

$user = currentUser();
$unreadNotif = countUnreadNotif();
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"/><meta content="width=device-width, initial-scale=1.0" name="viewport"/><meta content="web_dashboard" name="shell-type"/><title>AI Chat Assistant - Teman Warga AI</title>
<link rel="icon" type="image/svg+xml" href="logo.php"/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/><style>@layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}main>:first-child{margin-top:0!important;}main>:last-child{margin-bottom:0!important;}}::-webkit-scrollbar{display:none;}
/* Subtle robot animations */
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

</style><script src="https://cdn.tailwindcss.com"></script><script id="tailwind-config">tailwind.config={darkMode:"class",theme:{extend:{colors:{"surface-container-low":"#eff4ff","surface-container-lowest":"#ffffff","outline":"#737686","on-background":"#0b1c30","outline-variant":"#c3c6d7","surface-container-highest":"#d3e4fe","surface-dim":"#cbdbf5","on-primary":"#ffffff","error-container":"#ffdad6","surface-container":"#e5eeff","tertiary":"#005b7c","surface":"#f8f9ff","secondary-fixed-dim":"#93ccff","primary-fixed-dim":"#b4c5ff","on-secondary-fixed":"#001d31","on-surface-variant":"#434655","on-primary-fixed-variant":"#003ea8","secondary-fixed":"#cce5ff","surface-variant":"#d3e4fe","on-surface":"#0b1c30","primary":"#004ac6","secondary":"#006398","secondary-container":"#5bb8fe","primary-container":"#2563eb","primary-fixed":"#dbe1ff","on-tertiary":"#ffffff","background":"#f8f9ff","on-tertiary-fixed-variant":"#004c69","on-primary-fixed":"#00174b","tertiary-fixed-dim":"#7bd0ff","on-secondary-fixed-variant":"#004b73","surface-bright":"#f8f9ff","inverse-surface":"#213145","on-error-container":"#93000a","surface-container-high":"#dce9ff","on-tertiary-container":"#e1f2ff","surface-tint":"#0053db","on-primary-container":"eeefff","on-tertiary-fixed":"#001e2c","tertiary-container":"#00759f","inverse-on-surface":"#eaf1ff","on-secondary":"#ffffff","on-secondary-container":"#00476e","inverse-primary":"#b4c5ff","tertiary-fixed":"#c4e7ff","error":"#ba1a1a","on-error":"#ffffff"},borderRadius:{"DEFAULT":"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px"},spacing:{"space-md":"1rem","space-lg":"1.5rem","space-sm":"0.5rem","space-xs":"0.25rem","gutter":"1.25rem","margin":"2rem","space-xl":"2rem"},fontFamily:{"headline-md":["Outfit","sans-serif"],"label-sm":["Outfit","sans-serif"],"body-md":["Outfit","sans-serif"],"headline-lg":["Outfit","sans-serif"],"body-sm":["Outfit","sans-serif"],"headline-xl":["Outfit","sans-serif"],"body-lg":["Outfit","sans-serif"],"display-lg":["Outfit","sans-serif"],"label-lg":["Outfit","sans-serif"],"label-md":["Outfit","sans-serif"]},fontSize:{"headline-md":["18px",{"lineHeight":"26px","fontWeight":"600"}],"label-sm":["11px",{"lineHeight":"14px","letterSpacing":"0.02em","fontWeight":"600"}],"body-md":["14px",{"lineHeight":"22px","fontWeight":"400"}],"headline-lg":["22px",{"lineHeight":"30px","letterSpacing":"-0.01em","fontWeight":"700"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"headline-xl":["28px",{"lineHeight":"36px","letterSpacing":"-0.015em","fontWeight":"700"}],"body-lg":["16px",{"lineHeight":"24px","fontWeight":"400"}],"display-lg":["40px",{"lineHeight":"48px","letterSpacing":"-0.02em","fontWeight":"800"}],"label-lg":["14px",{"lineHeight":"20px","fontWeight":"600"}],"label-md":["12px",{"lineHeight":"16px","letterSpacing":"0.01em","fontWeight":"600"}]}}}};</script></head><body class="bg-gradient-to-b from-surface-container-lowest to-surface font-body-md text-body-md text-on-surface min-h-screen"><header class="fixed top-0 left-0 right-0 h-16 z-50 bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] px-gutter flex items-center justify-between gap-space-lg"><?php renderHeaderBrand(); ?><div class="flex-1 max-w-xl mx-auto"><div class="relative flex items-center w-full"><span class="material-symbols-outlined absolute left-space-md text-outline text-[20px] pointer-events-none">search</span><input class="w-full pl-11 pr-space-md py-2 bg-surface-container-low rounded-xl text-on-surface placeholder-outline font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 transition-all" placeholder="Cari bantuan, laporan, atau informasi..." type="text"/></div></div><div class="flex items-center gap-space-md shrink-0"><button aria-label="Notifikasi" onclick="window.location.href='notifikasi.php'" class="relative w-10 h-10 rounded-xl bg-surface-container-low hover:bg-surface-container flex items-center justify-center text-on-surface-variant transition-colors" type="button"><span class="material-symbols-outlined text-[22px]">notifications</span><?php if($unreadNotif > 0): ?><span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-error text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white"><?= $unreadNotif > 9 ? '9+' : $unreadNotif ?></span><?php else: ?><span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-surface-container-lowest"></span><?php endif; ?></button><a href="Profile.php" class="flex items-center gap-space-sm pl-space-sm py-1 pr-space-sm rounded-xl hover:bg-surface-container-low cursor-pointer transition-colors"><div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center"><span class="material-symbols-outlined text-on-primary text-[18px]">person</span></div><div class="hidden md:flex flex-col text-left"><span class="font-label-md text-label-md text-on-surface"><?= htmlspecialchars($user['nama']) ?></span><span class="font-label-sm text-label-sm text-outline"><?= ucfirst($user['role']) ?></span></div><span class="material-symbols-outlined text-outline text-[18px]">keyboard_arrow_down</span></a></div></header><?php renderSidebarNav('ai-chat'); ?><div class="pl-64"><main class="w-full pt-16 px-margin py-margin"><div class="flex flex-col w-full">
<!-- Container Chat Viewport Card -->
<div class="w-full bg-surface-container-lowest rounded-2xl shadow-sm flex flex-col h-[calc(100vh-7rem)] overflow-hidden relative border border-slate-200/80">

  <!-- Header Ruang Percakapan -->
  <div class="px-4 sm:px-6 py-3.5 bg-surface-container-lowest flex items-center justify-between shadow-[0_1px_3px_rgba(0,0,0,0.03)] z-10 border-b border-surface-container/60 gap-3">
    <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
      <!-- Tombol Buka/Tutup Riwayat Chat -->
      <button id="btnToggleHistory" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200/90 text-slate-700 hover:text-primary hover:border-primary/40 hover:bg-blue-50/60 transition-all text-xs font-semibold cursor-pointer shrink-0 shadow-2xs active:scale-95" title="Buka/Tutup Riwayat Percakapan">
        <span class="material-symbols-outlined text-[19px] text-primary">history</span>
        <span class="hidden sm:inline">Riwayat Chat</span>
        <span id="badgeHistoryCount" class="min-w-[18px] h-[18px] px-1 rounded-full bg-blue-100 text-primary text-[10px] font-bold flex items-center justify-center">0</span>
      </button>

      <div class="h-6 w-px bg-slate-200/80 shrink-0 hidden sm:block"></div>

      <!-- Avatar with unclipped crisp online status badge -->
      <div class="relative w-10 h-10 shrink-0 transition-transform duration-300 hover:scale-105">
        <div class="w-full h-full rounded-2xl bg-gradient-to-tr from-blue-50 to-indigo-50/70 p-1 shadow-sm ring-1 ring-primary/15 overflow-hidden flex items-center justify-center">
          <img src="logo.php" class="w-full h-full object-cover rounded-xl" alt="AI Mascot"/>
        </div>
        <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-500 ring-2 ring-white shadow-xs"></span>
        <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-500 radar-ring pointer-events-none"></span>
      </div>

      <div class="flex flex-col justify-center min-w-0">
        <div class="flex items-center gap-2">
          <span class="font-headline-md text-headline-md text-on-surface font-bold tracking-tight truncate">AI Assistant</span>
          <span class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-extrabold tracking-wider uppercase shrink-0">Teman Warga</span>
        </div>
        <div class="flex items-center gap-1.5 mt-0.5 text-xs text-outline font-medium truncate">
          <span class="text-emerald-600 font-bold shrink-0">Aktif</span>
          <span class="text-slate-300 font-bold">&bull;</span>
          <span class="text-slate-600 font-medium truncate" id="activeChatTopicLabel">Percakapan Baru</span>
        </div>
      </div>
    </div>

    <!-- Header Action Tools (Kanan) -->
    <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
      <!-- Tombol Mulai Chat Baru -->
      <button id="btnNewChatHeader" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-semibold shadow-xs transition-all active:scale-95 cursor-pointer" title="Mulai Percakapan Baru">
        <span class="material-symbols-outlined text-[17px]">add_comment</span>
        <span class="hidden md:inline">Chat Baru</span>
      </button>

      <!-- Tombol Bersihkan Percakapan Aktif -->
      <button id="btnClearCurrentChat" class="p-2 rounded-xl text-on-surface-variant hover:bg-red-50 hover:text-red-600 transition-colors cursor-pointer" title="Bersihkan Percakapan Saat Ini" type="button">
        <span class="material-symbols-outlined text-[20px]">restart_alt</span>
      </button>

      <!-- Tombol Pengaturan Gemini API Key -->
      <button class="p-2 rounded-xl text-on-surface-variant hover:bg-surface-container-low transition-colors cursor-pointer" onclick="openGeminiModal()" title="Pengaturan Gemini API Key" type="button">
        <span class="material-symbols-outlined text-[20px]">settings</span>
      </button>
    </div>
  </div>

  <!-- Flex Container: Sidebar Riwayat Chat + Main Chat Content -->
  <div class="flex-1 flex overflow-hidden relative">

    <!-- SIDEBAR RIWAYAT PERCAKAPAN (Collapsible Drawer & Sidebar) -->
    <aside id="chatHistorySidebar" class="w-72 shrink-0 bg-slate-50/95 border-r border-slate-200/80 flex flex-col justify-between transition-all duration-300 z-20 overflow-hidden absolute inset-y-0 left-0 sm:relative shadow-xl sm:shadow-none -translate-x-full sm:translate-x-0">
      <!-- Top Action in Sidebar -->
      <div class="p-3 border-b border-slate-200/80 flex items-center justify-between gap-2 bg-white/70 backdrop-blur-xs">
        <button id="btnNewChatSidebar" type="button" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-semibold shadow-xs transition-all active:scale-95 cursor-pointer">
          <span class="material-symbols-outlined text-[16px]">add</span>
          <span>Percakapan Baru</span>
        </button>
        <button id="btnCloseSidebarMobile" type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 flex items-center justify-center sm:hidden cursor-pointer" title="Tutup">
          <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
      </div>

      <!-- Search Box inside Sidebar -->
      <div class="px-3 pt-2.5 pb-1">
        <div class="relative flex items-center">
          <span class="material-symbols-outlined absolute left-2.5 text-slate-400 text-[16px] pointer-events-none">search</span>
          <input type="text" id="inputSearchHistory" placeholder="Cari percakapan..." class="w-full pl-8 pr-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all"/>
        </div>
      </div>

      <!-- Session List Container -->
      <div class="flex-1 overflow-y-auto p-2.5 space-y-1" id="historySessionsList">
        <!-- Rendered dynamically -->
      </div>

      <!-- Footer in Sidebar -->
      <div class="p-2.5 border-t border-slate-200/80 bg-white/70 backdrop-blur-xs flex items-center justify-between text-xs text-slate-500">
        <span id="sessionCountText" class="font-medium text-[11px] text-slate-500">0 Percakapan</span>
        <button id="btnClearAllHistory" type="button" class="text-[11px] font-semibold text-slate-500 hover:text-red-600 transition-colors flex items-center gap-1 cursor-pointer" title="Hapus semua riwayat percakapan">
          <span class="material-symbols-outlined text-[14px]">delete_sweep</span>
          <span>Hapus Semua</span>
        </button>
      </div>
    </aside>

    <!-- Overlay backdrop for mobile when sidebar is open -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/30 z-10 hidden sm:hidden"></div>

    <!-- MAIN CHAT STREAM & INPUT CONTAINER -->
    <div class="flex-1 flex flex-col h-full overflow-hidden bg-surface/50 min-w-0">
      <!-- Riwayat Percakapan (Stream Area) -->
      <div class="flex-1 overflow-y-auto px-4 sm:px-8 py-5 flex flex-col gap-space-lg" id="chat-stream">
        <!-- Message bubbles will be loaded and appended here -->
      </div>

      <!-- Quick Suggestions Bar -->
      <div class="px-4 sm:px-8 py-2 bg-surface-container-lowest flex items-center gap-2 overflow-x-auto border-t border-surface-container/60" id="quick-suggestions-bar">
        <div class="flex items-center gap-1.5 shrink-0 mr-1 text-primary">
          <span class="material-symbols-outlined text-[18px]">lightbulb</span>
          <span class="font-label-sm text-label-sm text-outline">Topik Cepat:</span>
        </div>
        <div class="flex items-center gap-2" id="suggestions-list">
          <button class="suggestion-chip inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-surface-container-low hover:bg-primary hover:text-on-primary text-on-surface-variant font-label-sm text-label-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm active:scale-95 whitespace-nowrap border border-transparent hover:border-primary/20 cursor-pointer" type="button" data-question="Bagaimana cara pembuatan KTP Elektronik baru?">
            <span class="material-symbols-outlined text-[14px]">badge</span>
            <span>Pembuatan KTP Elektronik</span>
          </button>
          <button class="suggestion-chip inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-surface-container-low hover:bg-primary hover:text-on-primary text-on-surface-variant font-label-sm text-label-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm active:scale-95 whitespace-nowrap border border-transparent hover:border-primary/20 cursor-pointer" type="button" data-question="Kapan jadwal pelayanan dan vaksinasi di Puskesmas?">
            <span class="material-symbols-outlined text-[14px]">vaccines</span>
            <span>Jadwal Puskesmas &amp; Vaksin</span>
          </button>
          <button class="suggestion-chip inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-surface-container-low hover:bg-primary hover:text-on-primary text-on-surface-variant font-label-sm text-label-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm active:scale-95 whitespace-nowrap border border-transparent hover:border-primary/20 cursor-pointer" type="button" data-question="Apa syarat pendaftaran bantuan sosial warga (PKH/BLT)?">
            <span class="material-symbols-outlined text-[14px]">volunteer_activism</span>
            <span>Syarat Bantuan Sosial Warga</span>
          </button>
          <button class="suggestion-chip inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-surface-container-low hover:bg-primary hover:text-on-primary text-on-surface-variant font-label-sm text-label-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm active:scale-95 whitespace-nowrap border border-transparent hover:border-primary/20 cursor-pointer" type="button" data-question="Bagaimana cara lapor jalan rusak atau sampah menumpuk?">
            <span class="material-symbols-outlined text-[14px]">campaign</span>
            <span>Lapor Masalah Lingkungan</span>
          </button>
        </div>
      </div>

      <!-- Input Bar Bagian Bawah -->
      <div class="p-3 sm:p-4 bg-surface-container-lowest shadow-[0_-1px_3px_rgba(0,0,0,0.02)]">
        <form class="flex items-center gap-space-sm bg-surface-container-low rounded-2xl p-1.5 sm:p-2 focus-within:bg-surface-container-lowest focus-within:shadow-md focus-within:ring-2 focus-within:ring-primary/20 transition-all border border-slate-200/60" id="chat-form">
          <!-- Lampiran / Attachment -->
          <button id="btn-attach" class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center text-outline hover:text-primary hover:bg-surface-container transition-all hover:scale-105 active:scale-95 cursor-pointer" title="Lampirkan Dokumen atau Foto" type="button">
            <span class="material-symbols-outlined text-[22px]">attach_file</span>
          </button>
          <!-- Input Field -->
          <input class="flex-1 bg-transparent px-space-sm py-2 text-on-surface placeholder:text-outline font-body-md text-body-md focus:outline-none" id="chat-input" placeholder="Tanya seputar KTP, domisili, jadwal puskesmas, lapor masalah..." type="text" autocomplete="off"/>
          <!-- Rekam Suara -->
          <button id="btn-voice" class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center text-outline hover:text-primary hover:bg-surface-container transition-all hover:scale-105 active:scale-95 group cursor-pointer" title="Bicara (Pesan Suara)" type="button">
            <span class="material-symbols-outlined text-[22px]" id="mic-icon">mic</span>
          </button>
          <!-- Tombol Kirim -->
          <button class="group relative overflow-hidden w-10 h-10 shrink-0 rounded-xl bg-primary hover:bg-primary-container text-on-primary flex items-center justify-center shadow-md shadow-primary/25 transition-all duration-200 hover:scale-105 active:scale-95 cursor-pointer disabled:opacity-50 disabled:pointer-events-none" id="btn-send" title="Kirim Pesan" type="submit">
            <span class="material-symbols-outlined text-[20px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5">send</span>
            <span class="absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none rounded-xl"></span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
</div>

<script>
(function() {
  const input = document.getElementById('chat-input');
  const form = document.getElementById('chat-form');
  const stream = document.getElementById('chat-stream');
  const btnSend = document.getElementById('btn-send');
  const btnVoice = document.getElementById('btn-voice');
  const btnAttach = document.getElementById('btn-attach');
  const suggestionsList = document.getElementById('suggestions-list');

  // Elements Riwayat Chat
  const btnToggleHistory = document.getElementById('btnToggleHistory');
  const chatHistorySidebar = document.getElementById('chatHistorySidebar');
  const sidebarBackdrop = document.getElementById('sidebarBackdrop');
  const btnCloseSidebarMobile = document.getElementById('btnCloseSidebarMobile');
  const btnNewChatHeader = document.getElementById('btnNewChatHeader');
  const btnNewChatSidebar = document.getElementById('btnNewChatSidebar');
  const btnClearCurrentChat = document.getElementById('btnClearCurrentChat');
  const btnClearAllHistory = document.getElementById('btnClearAllHistory');
  const historySessionsList = document.getElementById('historySessionsList');
  const inputSearchHistory = document.getElementById('inputSearchHistory');
  const badgeHistoryCount = document.getElementById('badgeHistoryCount');
  const sessionCountText = document.getElementById('sessionCountText');
  const activeChatTopicLabel = document.getElementById('activeChatTopicLabel');

  const CURRENT_USER_ID = <?= json_encode((int)($user['id'] ?? 0)); ?>;
  const STORAGE_SESSIONS_KEY = `teman_warga_chat_sessions_u_${CURRENT_USER_ID}`;
  const STORAGE_ACTIVE_ID_KEY = `teman_warga_active_chat_id_u_${CURRENT_USER_ID}`;
  const STORAGE_SIDEBAR_KEY = `teman_warga_sidebar_open_u_${CURRENT_USER_ID}`;

  let isSubmitting = false;
  let chatHistory = []; // Untuk context API multi-turn
  let sessions = [];
  let activeSessionId = null;

  function getTimeStr() {
    const now = new Date();
    return now.getHours().toString().padStart(2, '0') + '.' + now.getMinutes().toString().padStart(2, '0');
  }

  function formatDateLabel(timestamp) {
    if (!timestamp) return 'Hari Ini';
    const date = new Date(timestamp);
    const now = new Date();
    const isToday = date.toDateString() === now.toDateString();
    if (isToday) {
      return date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0');
    }
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
  }

  function scrollToBottom(smooth = true) {
    setTimeout(() => {
      stream.scrollTo({ top: stream.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
    }, 40);
  }

  function triggerRobotReaction() {
    const mascot = document.getElementById('robotMascot');
    if (mascot) {
      mascot.classList.add('scale-110');
      setTimeout(() => mascot.classList.remove('scale-110'), 800);
    }
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function stripHtml(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
  }

  // ─── LocalStorage Session Management ───────────────────────────────────────
  function loadStoredSessions() {
    try {
      const raw = localStorage.getItem(STORAGE_SESSIONS_KEY);
      if (raw) {
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) return parsed;
      }
    } catch (e) {
      console.warn('Gagal membaca sesi riwayat chat dari storage:', e);
    }
    return [];
  }

  function saveStoredSessions() {
    try {
      localStorage.setItem(STORAGE_SESSIONS_KEY, JSON.stringify(sessions));
      if (activeSessionId) {
        localStorage.setItem(STORAGE_ACTIVE_ID_KEY, activeSessionId);
      }
    } catch (e) {
      console.warn('Gagal menyimpan sesi chat ke storage:', e);
    }
  }

  function getActiveSession() {
    return sessions.find(s => s.id === activeSessionId) || null;
  }

  function createNewSessionObj(initialTitle = 'Percakapan Baru') {
    const now = Date.now();
    return {
      id: 'chat_' + now + '_' + Math.random().toString(36).substr(2, 6),
      title: initialTitle,
      createdAt: now,
      updatedAt: now,
      messages: [
        {
          sender: 'bot',
          html: 'Halo! Saya <strong>Teman Warga AI</strong> — asisten cerdas pelayanan publik kelurahan. Ada yang bisa saya bantu? 😊',
          time: getTimeStr(),
          action: null,
          isWelcome: true
        }
      ]
    };
  }

  // ─── Render Dom Message Helpers ────────────────────────────────────────────
  function appendUserMessageDOM(text, timeStr = '') {
    if (!timeStr) timeStr = getTimeStr();
    const wrapper = document.createElement('div');
    wrapper.className = 'flex flex-col items-end gap-1.5 self-end max-w-xl transition-all duration-300';
    wrapper.innerHTML = `
      <div class="group relative bg-primary hover:bg-primary-container text-on-primary px-5 py-3.5 rounded-2xl rounded-tr-xs shadow-md shadow-primary/10 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
        <p class="font-body-md text-body-md text-on-primary leading-relaxed whitespace-pre-wrap">${escapeHtml(text)}</p>
      </div>
      <div class="flex items-center gap-1 px-1">
        <span class="font-label-sm text-label-sm text-outline">${timeStr}</span>
        <span class="material-symbols-outlined text-[14px] text-primary">done_all</span>
      </div>
    `;
    stream.appendChild(wrapper);
  }

  function appendBotResponseDOM(htmlReply, timeStr = '', action = null, isGemini = false, modelName = '') {
    if (!timeStr) timeStr = getTimeStr();

    let actionBtnHtml = '';
    if (action && action.label && action.url) {
      actionBtnHtml = `
        <div class="mt-3 pt-3 border-t border-surface-container flex items-center justify-between">
          <a href="${action.url}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary hover:bg-primary-container text-white text-sm font-semibold shadow-sm shadow-primary/20 active:scale-95 transition-all">
            <span>${action.label}</span>
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
          </a>
        </div>
      `;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'flex items-start gap-space-md max-w-2xl transition-all duration-300';
    wrapper.innerHTML = `
      <div class="w-9 h-9 shrink-0 rounded-xl bg-primary-container/10 flex items-center justify-center text-primary mt-1 shadow-sm ring-1 ring-primary/15 transition-transform duration-300 hover:scale-110 overflow-hidden p-1">
        <img src="logo.php" class="w-full h-full object-cover rounded-md" alt="AI Mascot"/>
      </div>
      <div class="flex flex-col gap-2">
        <div class="bg-surface-container-lowest text-on-surface p-5 rounded-2xl rounded-tl-xs shadow-sm ring-1 ring-outline-variant/30 hover:shadow-md transition-all duration-300 hover:border-primary/20">
          <div class="font-body-md text-body-md text-on-surface leading-relaxed">
            ${htmlReply}
          </div>
          ${actionBtnHtml}
        </div>
        <div class="flex items-center justify-between px-1">
          <span class="font-label-sm text-label-sm text-outline">${timeStr}</span>
          <div class="flex items-center gap-2">
            <span class="text-[11px] text-outline font-label-sm mr-1">Membantu?</span>
            <button class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-lowest hover:bg-emerald-50 text-outline hover:text-emerald-600 border border-outline-variant/40 text-[11px] font-label-sm transition-all hover:scale-105 active:scale-95 shadow-2xs cursor-pointer" type="button" onclick="this.classList.toggle('text-emerald-600'); this.classList.toggle('bg-emerald-50');">
              <span class="material-symbols-outlined text-[14px]">thumb_up</span>
              <span>Ya</span>
            </button>
          </div>
        </div>
      </div>
    `;
    stream.appendChild(wrapper);
  }

  function showTypingIndicator() {
    const existing = document.getElementById('typing-indicator');
    if (existing) existing.remove();

    const wrapper = document.createElement('div');
    wrapper.id = 'typing-indicator';
    wrapper.className = 'flex items-start gap-space-md max-w-2xl transition-all duration-300';
    wrapper.innerHTML = `
      <div class="w-9 h-9 shrink-0 rounded-xl bg-primary-container/10 flex items-center justify-center text-primary mt-1 shadow-sm ring-1 ring-primary/15 overflow-hidden p-1">
        <img src="logo.php" class="w-full h-full object-cover rounded-md animate-pulse" alt="AI Mascot"/>
      </div>
      <div class="flex flex-col gap-1.5">
        <div class="bg-surface-container-lowest text-on-surface px-4 py-3 rounded-2xl rounded-tl-xs shadow-sm ring-1 ring-outline-variant/30 flex items-center gap-2">
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-primary animate-bounce" style="animation-delay: 0ms"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-primary animate-bounce" style="animation-delay: 150ms"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-primary animate-bounce" style="animation-delay: 300ms"></span>
          </div>
          <span class="text-xs text-outline font-medium ml-1.5">Teman Warga AI sedang mencari informasi...</span>
        </div>
      </div>
    `;
    stream.appendChild(wrapper);
    scrollToBottom();
  }

  function hideTypingIndicator() {
    const el = document.getElementById('typing-indicator');
    if (el) el.remove();
  }

  // ─── Session Render and Switching ──────────────────────────────────────────
  function renderActiveSessionMessages() {
    stream.innerHTML = '';
    const current = getActiveSession();
    if (!current) return;

    // Date header
    const dateDiv = document.createElement('div');
    dateDiv.className = 'flex items-center justify-center my-space-xs';
    dateDiv.innerHTML = `
      <span class="px-3.5 py-1 rounded-full bg-surface-container-high/70 backdrop-blur-md text-on-surface-variant font-label-sm text-label-sm shadow-xs border border-white/60">
        ${formatDateLabel(current.createdAt)}
      </span>
    `;
    stream.appendChild(dateDiv);

    // Reconstruct Gemini API Context
    chatHistory = [];

    // Append each message
    current.messages.forEach(msg => {
      if (msg.sender === 'user') {
        appendUserMessageDOM(msg.text, msg.time);
        chatHistory.push({ role: 'user', text: msg.text });
      } else {
        appendBotResponseDOM(msg.html, msg.time, msg.action, msg.isGemini, msg.model);
        if (!msg.isWelcome) {
          chatHistory.push({ role: 'model', text: stripHtml(msg.html) });
        }
      }
    });

    // Update topic badge
    if (activeChatTopicLabel) {
      activeChatTopicLabel.textContent = current.title || 'Percakapan Aktif';
    }

    scrollToBottom(false);
  }

  function renderHistorySidebar(searchQuery = '') {
    if (!historySessionsList) return;
    historySessionsList.innerHTML = '';

    const q = searchQuery.trim().toLowerCase();
    const filtered = sessions.filter(s => {
      if (!q) return true;
      const matchTitle = (s.title || '').toLowerCase().includes(q);
      const matchMsg = (s.messages || []).some(m => (m.text || m.html || '').toLowerCase().includes(q));
      return matchTitle || matchMsg;
    });

    if (badgeHistoryCount) badgeHistoryCount.textContent = sessions.length;
    if (sessionCountText) sessionCountText.textContent = `${sessions.length} Percakapan`;

    if (filtered.length === 0) {
      historySessionsList.innerHTML = `
        <div class="p-4 text-center text-slate-400">
          <span class="material-symbols-outlined text-[28px] opacity-60">chat_bubble_outline</span>
          <p class="text-xs mt-1 font-medium">${q ? 'Tidak ada percakapan cocok' : 'Belum ada riwayat'}</p>
        </div>
      `;
      return;
    }

    filtered.forEach(s => {
      const isActive = s.id === activeSessionId;
      const itemBtn = document.createElement('div');
      itemBtn.className = `group relative flex items-center justify-between p-2.5 rounded-xl border transition-all cursor-pointer select-none ${
        isActive 
          ? 'bg-blue-50/90 border-blue-200/90 shadow-2xs' 
          : 'bg-white hover:bg-slate-100/80 border-slate-200/70 hover:border-slate-300'
      }`;

      // Hitung ringkasan pesan
      const userMsgs = (s.messages || []).filter(m => m.sender === 'user');
      const lastMsgSnippet = userMsgs.length > 0 ? userMsgs[userMsgs.length - 1].text : 'Percakapan baru...';

      itemBtn.innerHTML = `
        <div class="flex items-start gap-2.5 min-w-0 flex-1">
          <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5 ${isActive ? 'bg-primary text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'}">
            <span class="material-symbols-outlined text-[16px]">chat</span>
          </div>
          <div class="flex flex-col min-w-0 flex-1">
            <span class="text-xs font-bold truncate ${isActive ? 'text-blue-900' : 'text-slate-800'}">${escapeHtml(s.title || 'Percakapan')}</span>
            <span class="text-[11px] text-slate-400 truncate mt-0.5">${escapeHtml(lastMsgSnippet)}</span>
            <span class="text-[10px] text-slate-400 mt-1 font-medium">${formatDateLabel(s.updatedAt)}</span>
          </div>
        </div>
        <button type="button" class="btn-delete-session w-7 h-7 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50/80 opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity flex items-center justify-center shrink-0 ml-1.5 cursor-pointer" title="Hapus sesi percakapan ini" data-session-id="${s.id}">
          <span class="material-symbols-outlined text-[16px]">delete</span>
        </button>
      `;

      itemBtn.addEventListener('click', (e) => {
        if (e.target.closest('.btn-delete-session')) return;
        switchSession(s.id);
        closeMobileSidebar();
      });

      const delBtn = itemBtn.querySelector('.btn-delete-session');
      if (delBtn) {
        delBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          deleteSession(s.id);
        });
      }

      historySessionsList.appendChild(itemBtn);
    });
  }

  function switchSession(sessionId) {
    if (activeSessionId === sessionId) return;
    activeSessionId = sessionId;
    saveStoredSessions();
    renderActiveSessionMessages();
    renderHistorySidebar(inputSearchHistory ? inputSearchHistory.value : '');
    input.focus();
  }

  function createNewChat() {
    const newSession = createNewSessionObj();
    sessions.unshift(newSession);
    activeSessionId = newSession.id;
    saveStoredSessions();
    renderActiveSessionMessages();
    renderHistorySidebar();
    closeMobileSidebar();
    input.focus();
  }

  function deleteSession(sessionId) {
    if (!confirm('Apakah Anda yakin ingin menghapus riwayat percakapan ini?')) return;
    sessions = sessions.filter(s => s.id !== sessionId);
    if (activeSessionId === sessionId) {
      if (sessions.length > 0) {
        activeSessionId = sessions[0].id;
      } else {
        const fresh = createNewSessionObj();
        sessions = [fresh];
        activeSessionId = fresh.id;
      }
    }
    saveStoredSessions();
    renderActiveSessionMessages();
    renderHistorySidebar(inputSearchHistory ? inputSearchHistory.value : '');
  }

  function clearAllChatHistory() {
    if (!confirm('Hapus SEMUA riwayat percakapan yang tersimpan? Tindakan ini tidak dapat dibatalkan.')) return;
    const fresh = createNewSessionObj();
    sessions = [fresh];
    activeSessionId = fresh.id;
    saveStoredSessions();
    renderActiveSessionMessages();
    renderHistorySidebar();
  }

  function clearCurrentChat() {
    const current = getActiveSession();
    if (!current) return;
    if (!confirm('Bersihkan pesan dalam percakapan saat ini?')) return;
    current.title = 'Percakapan Baru';
    current.updatedAt = Date.now();
    current.messages = [
      {
        sender: 'bot',
        html: 'Halo! Percakapan telah dibersihkan. Ada yang bisa saya bantu hari ini seputar pelayanan warga Sukamaju? 😊',
        time: getTimeStr(),
        action: null,
        isWelcome: true
      }
    ];
    saveStoredSessions();
    renderActiveSessionMessages();
    renderHistorySidebar();
  }

  // ─── Mobile / Desktop Sidebar Drawer Control ──────────────────────────────
  let isSidebarOpenDesktop = localStorage.getItem(STORAGE_SIDEBAR_KEY) !== 'false';

  function applySidebarState() {
    if (!chatHistorySidebar) return;
    // Pada layar desktop (>= 640px)
    if (window.innerWidth >= 640) {
      if (isSidebarOpenDesktop) {
        chatHistorySidebar.classList.remove('sm:hidden');
        chatHistorySidebar.classList.remove('sm:-ml-72');
      } else {
        chatHistorySidebar.classList.add('sm:hidden');
      }
    }
  }

  function openMobileSidebar() {
    if (!chatHistorySidebar) return;
    chatHistorySidebar.classList.remove('-translate-x-full');
    chatHistorySidebar.classList.add('translate-x-0');
    if (sidebarBackdrop) sidebarBackdrop.classList.remove('hidden');
  }

  function closeMobileSidebar() {
    if (!chatHistorySidebar) return;
    chatHistorySidebar.classList.add('-translate-x-full');
    chatHistorySidebar.classList.remove('translate-x-0');
    if (sidebarBackdrop) sidebarBackdrop.classList.add('hidden');
  }

  function toggleSidebar() {
    if (window.innerWidth < 640) {
      const isOpen = chatHistorySidebar.classList.contains('translate-x-0');
      if (isOpen) closeMobileSidebar(); else openMobileSidebar();
    } else {
      isSidebarOpenDesktop = !isSidebarOpenDesktop;
      localStorage.setItem(STORAGE_SIDEBAR_KEY, isSidebarOpenDesktop ? 'true' : 'false');
      applySidebarState();
    }
  }

  if (btnToggleHistory) btnToggleHistory.addEventListener('click', toggleSidebar);
  if (btnCloseSidebarMobile) btnCloseSidebarMobile.addEventListener('click', closeMobileSidebar);
  if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeMobileSidebar);
  if (btnNewChatHeader) btnNewChatHeader.addEventListener('click', createNewChat);
  if (btnNewChatSidebar) btnNewChatSidebar.addEventListener('click', createNewChat);
  if (btnClearCurrentChat) btnClearCurrentChat.addEventListener('click', clearCurrentChat);
  if (btnClearAllHistory) btnClearAllHistory.addEventListener('click', clearAllChatHistory);

  if (inputSearchHistory) {
    inputSearchHistory.addEventListener('input', (e) => {
      renderHistorySidebar(e.target.value);
    });
  }

  window.addEventListener('resize', applySidebarState);

  // ─── Kirim Pesan dan Simpan ke Sesi Aktif ──────────────────────────────────
  async function sendChatMessage(messageText) {
    const text = messageText.trim();
    if (!text || isSubmitting) return;

    const current = getActiveSession();
    if (!current) return;

    isSubmitting = true;
    input.value = '';
    btnSend.disabled = true;

    const userTime = getTimeStr();

    // Simpan pesan user ke sesi aktif
    current.messages.push({
      sender: 'user',
      text: text,
      time: userTime
    });

    // Otomatis buat judul percakapan jika masih judul default
    if (current.title === 'Percakapan Baru' || !current.title) {
      current.title = text.length > 28 ? text.substring(0, 28) + '...' : text;
    }
    current.updatedAt = Date.now();

    // Render pesan user di DOM
    appendUserMessageDOM(text, userTime);
    saveStoredSessions();
    renderHistorySidebar(inputSearchHistory ? inputSearchHistory.value : '');
    showTypingIndicator();

    // Konfigurasi Gemini dari localStorage
    const savedGeminiKey = localStorage.getItem('gemini_api_key') || '';
    const savedGeminiModel = localStorage.getItem('gemini_model') || 'gemini-flash-lite-latest';

    try {
      const payload = {
        message: text,
        geminiKey: savedGeminiKey,
        model: savedGeminiModel,
        history: chatHistory.slice(-8)
      };

      const res = await fetch('api_ai_chat.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      hideTypingIndicator();

      if (data && data.success && data.reply) {
        const botTime = data.time || getTimeStr();
        // Simpan pesan bot ke sesi aktif
        current.messages.push({
          sender: 'bot',
          html: data.reply,
          time: botTime,
          action: data.action || null,
          isGemini: data.isGemini || false,
          model: data.model || ''
        });
        current.updatedAt = Date.now();

        // Tambah ke context multi-turn
        chatHistory.push({ role: 'user', text: text });
        chatHistory.push({ role: 'model', text: stripHtml(data.reply) });

        appendBotResponseDOM(data.reply, botTime, data.action, data.isGemini, data.model);
        if (data.suggestions && data.suggestions.length > 0) {
          updateSuggestions(data.suggestions);
        }
      } else {
        const errorMsg = (data && data.message) ? data.message : 'Maaf, terjadi kendala saat memproses jawaban. Silakan coba kembali.';
        appendBotResponseDOM(`<p class="text-error">${escapeHtml(errorMsg)}</p>`, getTimeStr());
      }

      saveStoredSessions();
      renderHistorySidebar(inputSearchHistory ? inputSearchHistory.value : '');
    } catch (err) {
      hideTypingIndicator();
      console.error('Chat error:', err);
      appendBotResponseDOM('<p class="text-error">Koneksi terputus. Pastikan server lokal Anda aktif lalu coba kirim kembali.</p>', getTimeStr());
    } finally {
      isSubmitting = false;
      btnSend.disabled = false;
      input.focus();
      scrollToBottom();
      triggerRobotReaction();
    }
  }

  // Update daftar saran cepat
  function updateSuggestions(suggestions) {
    if (!Array.isArray(suggestions) || suggestions.length === 0) return;
    suggestionsList.innerHTML = '';
    suggestions.forEach(item => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'suggestion-chip inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-surface-container-low hover:bg-primary hover:text-on-primary text-on-surface-variant font-label-sm text-label-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm active:scale-95 whitespace-nowrap border border-transparent hover:border-primary/20 cursor-pointer';
      btn.setAttribute('data-question', item);
      btn.innerHTML = `
        <span class="material-symbols-outlined text-[14px]">chat_bubble_outline</span>
        <span>${escapeHtml(item)}</span>
      `;
      btn.addEventListener('click', () => sendChatMessage(item));
      suggestionsList.appendChild(btn);
    });
  }

  // Submit form
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    sendChatMessage(input.value);
  });

  // Suggestion chips awal
  document.querySelectorAll('.suggestion-chip').forEach(btn => {
    btn.addEventListener('click', () => {
      const q = btn.getAttribute('data-question') || btn.innerText.trim();
      sendChatMessage(q);
    });
  });

  // Fitur Voice Input via Web Speech API
  if (btnVoice) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (SpeechRecognition) {
      const recognition = new SpeechRecognition();
      recognition.lang = 'id-ID';
      recognition.continuous = false;
      recognition.interimResults = false;

      let isListening = false;
      const micIcon = document.getElementById('mic-icon');

      btnVoice.addEventListener('click', () => {
        if (isListening) {
          recognition.stop();
        } else {
          try {
            recognition.start();
          } catch (e) {
            console.warn(e);
          }
        }
      });

      recognition.onstart = () => {
        isListening = true;
        btnVoice.classList.add('bg-error/10', 'text-error');
        if (micIcon) micIcon.classList.add('animate-pulse');
        input.placeholder = 'Mendengarkan suara Anda...';
      };

      recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        if (transcript) {
          input.value = transcript;
          sendChatMessage(transcript);
        }
      };

      recognition.onerror = (event) => {
        console.warn('Speech error:', event.error);
      };

      recognition.onend = () => {
        isListening = false;
        btnVoice.classList.remove('bg-error/10', 'text-error');
        if (micIcon) micIcon.classList.remove('animate-pulse');
        input.placeholder = 'Tanya seputar KTP, domisili, jadwal puskesmas, lapor masalah...';
      };
    } else {
      btnVoice.addEventListener('click', () => {
        alert('Fitur pengenalan suara tidak didukung di browser ini. Anda dapat mengetik langsung pada kolom input.');
      });
    }
  }

  // Fitur Lampiran
  if (btnAttach) {
    btnAttach.addEventListener('click', () => {
      alert('Untuk melampirkan berkas foto lokasi atau dokumen kerusakan lingkungan, silakan gunakan menu "Lapor Masalah".');
    });
  }

  // ─── INITIALIZATION ON PAGE LOAD ──────────────────────────────────────────
  function initChat() {
    sessions = loadStoredSessions();
    if (sessions.length === 0) {
      const initial = createNewSessionObj();
      sessions = [initial];
      activeSessionId = initial.id;
    } else {
      const savedActiveId = localStorage.getItem(STORAGE_ACTIVE_ID_KEY);
      if (savedActiveId && sessions.some(s => s.id === savedActiveId)) {
        activeSessionId = savedActiveId;
      } else {
        activeSessionId = sessions[0].id;
      }
    }

    saveStoredSessions();
    renderActiveSessionMessages();
    renderHistorySidebar();
    applySidebarState();
  }

  initChat();
})();
</script>

<!-- MODAL PENGATURAN GOOGLE GEMINI API -->
<div id="geminiModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 transition-all duration-300">
  <div class="relative w-full max-w-lg bg-surface-container-lowest rounded-3xl shadow-2xl border border-outline-variant/40 overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="geminiModalCard">
    <!-- Header Modal -->
    <div class="px-6 pt-6 pb-4 border-b border-surface-container flex items-start justify-between bg-gradient-to-r from-blue-50/70 via-indigo-50/40 to-surface-container-lowest">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
          <span class="material-symbols-outlined text-[26px]">neurology</span>
        </div>
        <div>
          <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
            Pengaturan Gemini AI
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary border border-primary/20">Official API</span>
          </h3>
          <p class="text-xs text-outline mt-0.5">Integrasi Google Gemini untuk asisten AI kelurahan</p>
        </div>
      </div>
      <button onclick="closeGeminiModal()" type="button" class="w-8 h-8 rounded-full flex items-center justify-center text-outline hover:text-on-surface hover:bg-surface-container transition-all cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <!-- Body Modal -->
    <div class="p-6 space-y-5">
      <!-- Status Box -->
      <div id="geminiStatusBox" class="p-4 rounded-2xl bg-surface-container border border-outline-variant/30 flex items-center gap-3">
        <span id="geminiModalStatusDot" class="w-3.5 h-3.5 rounded-full bg-slate-300 shrink-0"></span>
        <div class="flex-1">
          <p class="text-xs font-semibold text-on-surface" id="geminiModalStatusTitle">Engine Bawaan Aktif</p>
          <p class="text-[11px] text-outline" id="geminiModalStatusDesc">Smart Civic Engine bawaan aktif. Masukkan API Key untuk mengaktifkan Google Gemini.</p>
        </div>
      </div>

      <!-- Input API Key -->
      <div class="space-y-1.5">
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold text-on-surface flex items-center gap-1.5" for="geminiApiKeyInput">
            <span>Google Gemini API Key</span>
            <span class="text-error">*</span>
          </label>
          <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" class="text-[11px] font-semibold text-primary hover:underline inline-flex items-center gap-1">
            <span>Dapatkan Key Gratis</span>
            <span class="material-symbols-outlined text-[13px]">open_in_new</span>
          </a>
        </div>
        <div class="relative">
          <input 
            type="password" 
            id="geminiApiKeyInput" 
            placeholder="Contoh: AIzaSyD..." 
            class="w-full px-4 py-2.5 pr-11 rounded-xl bg-surface-container border border-outline-variant/40 focus:border-primary focus:ring-2 focus:ring-primary/20 text-on-surface text-sm outline-none font-mono transition-all"
            autocomplete="off"
          />
          <button 
            type="button" 
            onclick="toggleApiKeyVisibility()" 
            class="absolute right-3 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors cursor-pointer"
            title="Tampilkan / Sembunyikan"
          >
            <span class="material-symbols-outlined text-[18px]" id="eyeIcon">visibility_off</span>
          </button>
        </div>
        <p class="text-[11px] text-outline">API Key disimpan secara aman di browser lokal Anda (Local Storage) atau di file <code class="text-primary font-mono text-[10px] bg-primary/5 px-1 py-0.5 rounded">config.php</code>.</p>
      </div>

      <!-- Pilihan Model Gemini -->
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-on-surface" for="geminiModelSelect">Model Google Gemini</label>
        <div class="relative">
          <select 
            id="geminiModelSelect" 
            class="w-full px-4 py-2.5 rounded-xl bg-surface-container border border-outline-variant/40 focus:border-primary focus:ring-2 focus:ring-primary/20 text-on-surface text-sm outline-none cursor-pointer transition-all"
          >
            <option value="gemini-flash-lite-latest">Gemini Flash-Lite Latest ⚡ (Super Cepat &amp; Kuota Luas - Direkomendasikan)</option>
            <option value="gemini-3.1-flash-lite">Gemini 3.1 Flash Lite (Stabil)</option>
            <option value="gemini-flash-latest">Gemini Flash Latest</option>
            <option value="gemini-3.8-flash">Gemini 3.8 Flash</option>
            <option value="gemini-3.5-flash">Gemini 3.5 Flash</option>
            <option value="gemini-pro-latest">Gemini Pro Latest</option>
          </select>
        </div>
      </div>

      <!-- Feedback Area -->
      <div id="geminiTestFeedback" class="hidden p-3 rounded-xl text-xs font-medium border transition-all"></div>
    </div>

    <!-- Footer Modal -->
    <div class="px-6 py-4 bg-surface-container border-t border-outline-variant/30 flex items-center justify-between gap-3">
      <button 
        type="button" 
        id="btnDeleteGeminiKey" 
        onclick="clearGeminiSettings()" 
        class="text-xs font-semibold text-error hover:text-error/80 px-3 py-2 rounded-xl hover:bg-error/10 transition-colors hidden cursor-pointer"
      >
        Hapus API Key
      </button>
      <div class="flex items-center gap-2 ml-auto">
        <button 
          type="button" 
          id="btnTestGemini" 
          onclick="testGeminiConnection()" 
          class="px-4 py-2 rounded-xl border border-outline-variant/50 hover:bg-surface-container-high text-xs font-semibold text-on-surface-variant transition-all active:scale-95 cursor-pointer flex items-center gap-1.5"
        >
          <span class="material-symbols-outlined text-[16px]">sync</span>
          <span>Uji Koneksi</span>
        </button>
        <button 
          type="button" 
          onclick="saveGeminiSettings()" 
          class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold shadow-md shadow-primary/20 hover:shadow-lg transition-all active:scale-95 cursor-pointer flex items-center gap-1.5"
        >
          <span class="material-symbols-outlined text-[16px]">save</span>
          <span>Simpan & Aktifkan</span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// CONTROLLER MODAL & INTEGRASI GEMINI
function updateGeminiStatusUI() {
  const savedKey = localStorage.getItem('gemini_api_key') || '';
  const savedModel = localStorage.getItem('gemini_model') || 'gemini-flash-lite-latest';
  
  const statusLabel = document.getElementById('geminiStatusLabel');
  const activeDot = document.getElementById('geminiActiveDot');
  const modalStatusDot = document.getElementById('geminiModalStatusDot');
  const modalStatusTitle = document.getElementById('geminiModalStatusTitle');
  const modalStatusDesc = document.getElementById('geminiModalStatusDesc');
  const btnDelete = document.getElementById('btnDeleteGeminiKey');

  if (savedKey.trim() !== '') {
    if (statusLabel) statusLabel.textContent = 'Gemini Aktif';
    if (activeDot) activeDot.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse ring-2 ring-emerald-300';
    if (modalStatusDot) modalStatusDot.className = 'w-3.5 h-3.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100 shrink-0';
    if (modalStatusTitle) modalStatusTitle.innerHTML = `<span class="text-emerald-700 font-bold flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">check_circle</span> Google Gemini Aktif (${savedModel})</span>`;
    if (modalStatusDesc) modalStatusDesc.textContent = 'Pesan obrolan Anda diproses langsung menggunakan model AI tercanggih dari Google.';
    if (btnDelete) btnDelete.classList.remove('hidden');
  } else {
    if (statusLabel) statusLabel.textContent = 'Gemini AI';
    if (activeDot) activeDot.className = 'w-2 h-2 rounded-full bg-slate-300';
    if (modalStatusDot) modalStatusDot.className = 'w-3.5 h-3.5 rounded-full bg-slate-300 shrink-0';
    if (modalStatusTitle) modalStatusTitle.textContent = 'Smart Civic Engine Bawaan';
    if (modalStatusDesc) modalStatusDesc.textContent = 'Masukkan Google Gemini API Key Anda untuk mengaktifkan penalaran interaktif kelas dunia.';
    if (btnDelete) btnDelete.classList.add('hidden');
  }
}

function openGeminiModal() {
  const modal = document.getElementById('geminiModal');
  const card = document.getElementById('geminiModalCard');
  const keyInput = document.getElementById('geminiApiKeyInput');
  const modelSelect = document.getElementById('geminiModelSelect');
  const feedback = document.getElementById('geminiTestFeedback');

  if (feedback) {
    feedback.className = 'hidden p-3 rounded-xl text-xs font-medium border transition-all';
    feedback.innerHTML = '';
  }

  if (keyInput) keyInput.value = localStorage.getItem('gemini_api_key') || '';
  if (modelSelect) modelSelect.value = localStorage.getItem('gemini_model') || 'gemini-flash-lite-latest';

  updateGeminiStatusUI();

  modal.classList.remove('hidden');
  setTimeout(() => {
    card.classList.remove('scale-95', 'opacity-0');
    card.classList.add('scale-100', 'opacity-100');
  }, 10);
}

function closeGeminiModal() {
  const modal = document.getElementById('geminiModal');
  const card = document.getElementById('geminiModalCard');
  card.classList.remove('scale-100', 'opacity-100');
  card.classList.add('scale-95', 'opacity-0');
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 200);
}

function toggleApiKeyVisibility() {
  const input = document.getElementById('geminiApiKeyInput');
  const icon = document.getElementById('eyeIcon');
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    if (icon) icon.textContent = 'visibility';
  } else {
    input.type = 'password';
    if (icon) icon.textContent = 'visibility_off';
  }
}

async function testGeminiConnection() {
  const keyInput = document.getElementById('geminiApiKeyInput');
  const modelSelect = document.getElementById('geminiModelSelect');
  const feedback = document.getElementById('geminiTestFeedback');
  const btnTest = document.getElementById('btnTestGemini');

  const key = keyInput ? keyInput.value.trim() : '';
  const model = modelSelect ? modelSelect.value : 'gemini-flash-lite-latest';

  if (!key) {
    feedback.className = 'p-3 rounded-xl text-xs font-medium bg-error/10 text-error border border-error/20 flex items-center gap-2';
    feedback.innerHTML = '<span class="material-symbols-outlined text-[16px]">error</span> Silakan masukkan API Key terlebih dahulu sebelum menguji.';
    return;
  }

  btnTest.disabled = true;
  btnTest.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-spin">sync</span><span>Menguji...</span>';
  feedback.className = 'p-3 rounded-xl text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-2';
  feedback.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-pulse">cloud_sync</span> Menghubungkan ke server Google Gemini AI...';

  try {
    const res = await fetch('api_ai_chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ testKey: true, geminiKey: key, model: model })
    });
    const data = await res.json();

    if (data && data.success) {
      feedback.className = 'p-3 rounded-xl text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-start gap-2';
      feedback.innerHTML = `
        <span class="material-symbols-outlined text-emerald-600 text-[18px] shrink-0">check_circle</span>
        <div>
          <p class="font-bold">${escapeHtml(data.message)}</p>
          <p class="text-[11px] text-emerald-700 mt-1 italic leading-relaxed">"${escapeHtml(data.reply)}"</p>
        </div>
      `;
    } else {
      feedback.className = 'p-3 rounded-xl text-xs font-medium bg-error/10 text-error border border-error/20 flex items-start gap-2';
      feedback.innerHTML = `
        <span class="material-symbols-outlined text-[18px] shrink-0">cancel</span>
        <div>
          <p class="font-bold">Koneksi Gagal</p>
          <p class="text-[11px] mt-0.5">${escapeHtml(data && data.message ? data.message : 'API Key tidak valid atau kuota habis.')}</p>
        </div>
      `;
    }
  } catch (err) {
    feedback.className = 'p-3 rounded-xl text-xs font-medium bg-error/10 text-error border border-error/20 flex items-center gap-2';
    feedback.innerHTML = '<span class="material-symbols-outlined text-[16px]">wifi_off</span> Gagal menghubungi server lokal.';
  } finally {
    btnTest.disabled = false;
    btnTest.innerHTML = '<span class="material-symbols-outlined text-[16px]">sync</span><span>Uji Koneksi</span>';
  }
}

function saveGeminiSettings() {
  const keyInput = document.getElementById('geminiApiKeyInput');
  const modelSelect = document.getElementById('geminiModelSelect');
  const key = keyInput ? keyInput.value.trim() : '';
  const model = modelSelect ? modelSelect.value : 'gemini-flash-lite-latest';

  if (!key) {
    if (confirm('API Key kosong. Apakah Anda ingin menonaktifkan Google Gemini dan kembali menggunakan Smart Civic Engine bawaan?')) {
      clearGeminiSettings();
    }
    return;
  }

  localStorage.setItem('gemini_api_key', key);
  localStorage.setItem('gemini_model', model);
  updateGeminiStatusUI();
  closeGeminiModal();

  // Berikan feedback visual
  const statusLabel = document.getElementById('geminiStatusLabel');
  if (statusLabel) {
    statusLabel.textContent = 'Tersimpan!';
    setTimeout(updateGeminiStatusUI, 1500);
  }
}

function clearGeminiSettings() {
  localStorage.removeItem('gemini_api_key');
  localStorage.removeItem('gemini_model');
  const keyInput = document.getElementById('geminiApiKeyInput');
  if (keyInput) keyInput.value = '';
  updateGeminiStatusUI();
  closeGeminiModal();
}

// Tutup modal jika klik di luar card
window.addEventListener('DOMContentLoaded', () => {
  updateGeminiStatusUI();
  const modal = document.getElementById('geminiModal');
  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeGeminiModal();
    });
  }
});
</script>
</main></div>

</body></html>
