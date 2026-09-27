<?php
/**
 * kebijakan_privasi.php — Halaman Kebijakan Privasi
 */
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kebijakan Privasi — Teman Warga AI</title>
<meta name="description" content="Kebijakan privasi dan perlindungan data pengguna Teman Warga AI.">
<link rel="icon" type="image/svg+xml" href="logo.php"/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Outfit"','sans-serif'] },
  colors: { brand: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',600:'#2563eb',700:'#1d4ed8' } } } } }
</script>
<style>body{font-family:'Outfit',sans-serif;} .prose h2{font-size:1.15rem;font-weight:700;color:#0f172a;margin-top:1.8rem;margin-bottom:.6rem;display:flex;align-items:center;gap:.5rem;} .prose p,.prose li{color:#475569;line-height:1.75;font-size:.93rem;} .prose ul{list-style:disc;padding-left:1.25rem;} .prose li{margin-bottom:.3rem;}</style>
</head>
<body class="bg-slate-50 min-h-screen antialiased">
<!-- Header -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-50">
  <div class="max-w-4xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="landing-page.php" class="flex items-center gap-2 group">
      <div class="w-9 h-9 rounded-lg overflow-hidden shadow-sm group-hover:scale-105 transition-transform"><img src="logo.php" class="w-full h-full object-cover" alt="Logo"></div>
      <span class="text-lg font-bold text-slate-900">Teman Warga <span class="text-brand-600">AI</span></span>
    </a>
    <a href="javascript:history.back()" class="text-sm font-semibold text-slate-500 hover:text-brand-600 flex items-center gap-1 transition-colors">← Kembali</a>
  </div>
</header>

<main class="max-w-4xl mx-auto px-6 py-12">
  <div class="text-center mb-12">
    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold uppercase tracking-wider mb-4">🔒 Keamanan & Privasi</div>
    <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Kebijakan Privasi</h1>
    <p class="text-slate-500 mt-2 text-sm">Terakhir diperbarui: 1 Januari 2026 &nbsp;·&nbsp; Berlaku sejak: 1 Januari 2026</p>
  </div>

  <!-- Komitmen Privasi -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm text-center">
      <div class="text-3xl mb-2">🔐</div>
      <h3 class="text-sm font-bold text-slate-900">Enkripsi Data</h3>
      <p class="text-xs text-slate-500 mt-1">Semua data disimpan dengan enkripsi bcrypt dan HTTPS</p>
    </div>
    <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm text-center">
      <div class="text-3xl mb-2">🕵️</div>
      <h3 class="text-sm font-bold text-slate-900">NIK Disamarkan</h3>
      <p class="text-xs text-slate-500 mt-1">Nomor Induk Kependudukan tidak pernah ditampilkan ke publik</p>
    </div>
    <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm text-center">
      <div class="text-3xl mb-2">🚫</div>
      <h3 class="text-sm font-bold text-slate-900">Tanpa Iklan Tracking</h3>
      <p class="text-xs text-slate-500 mt-1">Kami tidak menjual data Anda kepada pihak ketiga</p>
    </div>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-12 prose max-w-none">
    <h2>🗂️ 1. Data yang Kami Kumpulkan</h2>
    <p>Kami mengumpulkan data yang Anda berikan secara langsung saat mendaftar dan menggunakan layanan:</p>
    <ul>
      <li><strong>Data Identitas:</strong> Nama lengkap, email, nomor HP, NIK (opsional).</li>
      <li><strong>Data Laporan:</strong> Foto, deskripsi, dan koordinat GPS lokasi masalah yang Anda laporkan.</li>
      <li><strong>Data Penggunaan:</strong> Log aktivitas, preferensi wilayah, dan riwayat interaksi dengan AI.</li>
      <li><strong>Data Teknis:</strong> Alamat IP, jenis browser, dan device yang digunakan (untuk keamanan sistem).</li>
    </ul>

    <h2>🎯 2. Tujuan Penggunaan Data</h2>
    <p>Data Anda kami gunakan untuk:</p>
    <ul>
      <li>Memverifikasi identitas dan mengamankan akun Anda.</li>
      <li>Memproses dan menindaklanjuti laporan lingkungan yang Anda kirim.</li>
      <li>Meningkatkan kualitas layanan AI dan pengalaman pengguna.</li>
      <li>Mengirimkan notifikasi status laporan dan pengumuman penting.</li>
      <li>Analisis agregat (anonim) untuk perencanaan layanan publik.</li>
    </ul>

    <h2>🤝 3. Berbagi Data dengan Pihak Ketiga</h2>
    <p>Kami <strong>tidak menjual</strong> data pribadi Anda. Data hanya dibagikan kepada:</p>
    <ul>
      <li><strong>Instansi Pemerintah Terkait:</strong> Detail laporan (tanpa NIK) diteruskan ke dinas berwenang untuk penanganan.</li>
      <li><strong>Penyedia Layanan Teknis:</strong> Server hosting dan layanan peta (OpenStreetMap) yang terikat perjanjian kerahasiaan.</li>
      <li><strong>Kewajiban Hukum:</strong> Jika diwajibkan oleh peraturan perundang-undangan yang berlaku.</li>
    </ul>

    <h2>🛡️ 4. Keamanan Data</h2>
    <p>Kami menerapkan langkah-langkah keamanan berikut:</p>
    <ul>
      <li>Password disimpan dalam bentuk hash bcrypt yang tidak dapat dibalik.</li>
      <li>NIK ditampilkan dalam format sensor (contoh: 3201****0001) di antarmuka publik.</li>
      <li>Koneksi ke server menggunakan protokol HTTPS/TLS.</li>
      <li>Akses database dibatasi dan dimonitor secara berkala.</li>
      <li>Fitur laporan anonim tersedia untuk melindungi identitas pelapor.</li>
    </ul>

    <h2>🕐 5. Retensi Data</h2>
    <p>Data akun aktif disimpan selama Anda menggunakan layanan. Jika akun dihapus, data pribadi akan dihapus dalam 30 hari kerja, kecuali data yang diwajibkan disimpan oleh hukum. Data laporan yang telah selesai diproses disimpan selama 2 tahun untuk keperluan statistik dan perencanaan publik.</p>

    <h2>👤 6. Hak Pengguna</h2>
    <p>Anda memiliki hak untuk:</p>
    <ul>
      <li><strong>Akses:</strong> Melihat dan mengunduh data pribadi Anda.</li>
      <li><strong>Koreksi:</strong> Memperbarui data yang tidak akurat melalui halaman Profil.</li>
      <li><strong>Penghapusan:</strong> Meminta penghapusan akun dan data pribadi Anda.</li>
      <li><strong>Portabilitas:</strong> Mendapatkan salinan data dalam format yang dapat dibaca mesin.</li>
      <li><strong>Keberatan:</strong> Menolak pemrosesan data untuk tujuan tertentu.</li>
    </ul>

    <h2>🍪 7. Cookie dan Penyimpanan Lokal</h2>
    <p>Kami menggunakan cookie sesi (session cookie) yang bersifat sementara untuk menjaga status login Anda. Preferensi wilayah dan tema disimpan di localStorage browser. Kami tidak menggunakan cookie tracking atau iklan.</p>

    <h2>📞 8. Kontak dan Pengaduan</h2>
    <p>Untuk pertanyaan privasi, permintaan penghapusan data, atau pengaduan, gunakan fitur AI Chat di Platform atau hubungi kami melalui Call Center 112. Kami berkomitmen merespons dalam 3 hari kerja.</p>
  </div>

  <div class="mt-8 text-center">
    <a href="syarat_ketentuan.php" class="text-sm text-brand-600 font-semibold hover:underline">Lihat juga: Syarat & Ketentuan →</a>
  </div>
</main>

<footer class="border-t border-slate-200 py-8 mt-12">
  <div class="max-w-4xl mx-auto px-6 text-center text-xs text-slate-400">
    <p>© 2026 Teman Warga AI. Seluruh hak cipta dilindungi.
      <a href="syarat_ketentuan.php" class="hover:text-brand-600 ml-3">Syarat & Ketentuan</a>
      <a href="landing-page.php" class="hover:text-brand-600 ml-3">Beranda</a>
    </p>
  </div>
</footer>
</body>
</html>
