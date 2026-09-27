<?php
/**
 * logout.php — Proses logout dan redirect ke halaman masuk
 */
require_once __DIR__ . '/auth.php';

logoutUser();

$target = isset($_GET['to']) && $_GET['to'] === 'landing' 
    ? 'landing-page.php' 
    : 'Halaman_daftar.php?tab=masuk&logout=1';

header('Location: ' . $target);
exit;

