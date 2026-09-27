<?php
/**
 * syarat_ketentuan.php — Halaman Syarat & Ketentuan
 */
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Syarat & Ketentuan — Teman Warga AI</title>
<meta name="description" content="Syarat dan ketentuan penggunaan platform Teman Warga AI.">
<link rel="icon" type="image/svg+xml" href="logo.php"/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: { extend: { fontFamily: { sans: ['"Outfit"','sans-serif'] },
    colors: { brand: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',600:'#2563eb',700:'#1d4ed8',900:'#1e3a8a' } } } }
}
</script>
<style>body{font-family:'Outfit',sans-serif;} .prose h2{font-size:1.2rem;font-weight:700;color:#0f172a;margin-top:1.8rem;margin-bottom:.6rem;} .prose p,.prose li{color:#475569;line-height:1.75;font-size:.93rem;} .prose ul{list-style:disc;padding-left:1.25rem;} .prose li{margin-bottom:.3rem;}</style>
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
  <!-- Hero -->
  <div class="text-center mb-12">
    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-50 border border-brand-200 text-brand-700 text-xs font-bold uppercase tracking-wider mb-4">📋 Dokumen Legal</div>
    <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Syarat & Ketentuan</h1>
    <p class="text-slate-500 mt-2 text-sm">Terakhir diperbarui: 1 Januari 2026 &nbsp;·&nbsp; Berlaku sejak: 1 Januari 2026</p>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-12 prose max-w-none">
    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 mb-8">
      <p class="text-amber-800 text-sm font-semibold">⚠️ Mohon baca syarat dan ketentuan ini dengan seksama sebelum menggunakan layanan Teman Warga AI. Dengan mendaftar dan menggunakan layanan ini, Anda dianggap telah membaca, memahami, dan menyetujui semua ketentuan di bawah ini.</p>
    </div>

    <h2>1. Definisi dan Ruang Lingkup</h2>
    <p>Teman Warga AI ("Platform", "kami", "layanan") adalah aplikasi berbasis web yang menyediakan layanan pelayanan publik terpadu berbasis kecerdasan buatan untuk warga. Platform ini dikelola oleh tim pengembang Teman Warga AI dan beroperasi sesuai peraturan perundang-undangan Republik Indonesia.</p>
    <p>"Pengguna" mengacu pada setiap individu yang mengakses, mendaftar, atau menggunakan layanan Platform ini, termasuk warga, petugas, dan administrator.</p>

    <h2>2. Pendaftaran dan Akun Pengguna</h2>
    <ul>
      <li>Anda wajib memberikan informasi yang akurat, terkini, dan lengkap saat mendaftar.</li>
      <li>Setiap pengguna hanya boleh memiliki satu akun aktif.</li>
      <li>Anda bertanggung jawab atas kerahasiaan kata sandi akun Anda.</li>
      <li>Segera laporkan kepada kami jika Anda mencurigai adanya akses tidak sah ke akun Anda.</li>
      <li>Kami berhak menangguhkan atau menghapus akun yang melanggar ketentuan ini tanpa pemberitahuan sebelumnya.</li>
    </ul>

    <h2>3. Penggunaan yang Diizinkan</h2>
    <p>Anda boleh menggunakan Platform untuk:</p>
    <ul>
      <li>Melaporkan masalah lingkungan di wilayah Anda dengan informasi yang akurat.</li>
      <li>Berkonsultasi dengan asisten AI mengenai layanan publik.</li>
      <li>Berpartisipasi dalam forum diskusi warga secara konstruktif.</li>
      <li>Memantau status laporan yang telah Anda buat.</li>
    </ul>

    <h2>4. Larangan Penggunaan</h2>
    <p>Anda dilarang menggunakan Platform untuk:</p>
    <ul>
      <li>Mengirimkan laporan palsu, menyesatkan, atau tidak berdasar fakta.</li>
      <li>Menyebarkan konten yang mengandung unsur SARA, hoaks, atau ujaran kebencian.</li>
      <li>Melakukan tindakan yang dapat mengganggu operasional Platform.</li>
      <li>Menggunakan data pengguna lain tanpa izin.</li>
      <li>Melakukan reverse engineering atau eksploitasi kerentanan sistem.</li>
      <li>Mendaftarkan akun bot atau otomatis tanpa izin tertulis.</li>
    </ul>

    <h2>5. Hak Kekayaan Intelektual</h2>
    <p>Seluruh konten di Platform termasuk logo, desain, teks, dan kode sumber adalah milik Teman Warga AI dan dilindungi oleh undang-undang hak cipta Indonesia. Penggunaan komersial tanpa izin tertulis dilarang.</p>

    <h2>6. Privasi dan Keamanan Data</h2>
    <p>Penggunaan data pribadi Anda diatur dalam <a href="kebijakan_privasi.php" class="text-brand-600 font-semibold hover:underline">Kebijakan Privasi</a> kami yang merupakan bagian tidak terpisahkan dari Syarat & Ketentuan ini.</p>

    <h2>7. Penafian (Disclaimer)</h2>
    <p>Platform disediakan "apa adanya" (as-is). Kami tidak menjamin bahwa layanan akan selalu tersedia tanpa gangguan. Teman Warga AI tidak bertanggung jawab atas kerugian akibat keterlambatan penanganan laporan oleh instansi pemerintah terkait.</p>

    <h2>8. Perubahan Ketentuan</h2>
    <p>Kami berhak mengubah Syarat & Ketentuan ini kapan saja. Perubahan akan diberitahukan melalui notifikasi di Platform atau email terdaftar. Penggunaan Platform setelah perubahan dianggap sebagai persetujuan atas ketentuan yang baru.</p>

    <h2>9. Hukum yang Berlaku</h2>
    <p>Syarat & Ketentuan ini tunduk pada hukum yang berlaku di Republik Indonesia. Segala sengketa diselesaikan melalui musyawarah mufakat, atau jika tidak tercapai, melalui pengadilan yang berwenang di Indonesia.</p>

    <h2>10. Hubungi Kami</h2>
    <p>Jika ada pertanyaan mengenai Syarat & Ketentuan ini, silakan hubungi kami melalui fitur AI Chat atau menghubungi Call Center 112.</p>
  </div>

  <!-- CTA Bottom -->
  <div class="mt-8 text-center">
    <a href="Halaman_daftar.php?tab=daftar" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-full shadow-md transition-all hover:-translate-y-0.5">
      Daftar Sekarang — Gratis
    </a>
    <p class="text-xs text-slate-400 mt-2">Dengan mendaftar, Anda menyetujui Syarat & Ketentuan dan Kebijakan Privasi kami.</p>
  </div>
</main>

<footer class="border-t border-slate-200 py-8 mt-12">
  <div class="max-w-4xl mx-auto px-6 text-center text-xs text-slate-400">
    <p>© 2026 Teman Warga AI. Seluruh hak cipta dilindungi.
      <a href="kebijakan_privasi.php" class="hover:text-brand-600 ml-3">Kebijakan Privasi</a>
      <a href="landing-page.php" class="hover:text-brand-600 ml-3">Beranda</a>
    </p>
  </div>
</footer>
</body>
</html>
