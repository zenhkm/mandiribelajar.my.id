<?php
// google_config.php

// ============================================================
// KONFIGURASI GOOGLE CLIENT
// ============================================================
// Dapatkan kredensial ini dari Google Cloud Console:
// https://console.cloud.google.com/apis/credentials
// ============================================================

define('GOOGLE_CLIENT_ID', 'GANTI_DENGAN_CLIENT_ID_ANDA');
define('GOOGLE_CLIENT_SECRET', 'GANTI_DENGAN_CLIENT_SECRET_ANDA');
define('GOOGLE_REDIRECT_URL', 'http://mandiribelajar.my.id/auth_google.php'); 
// Catatan: Sesuaikan GOOGLE_REDIRECT_URL dengan domain asli Anda saat online.
// Contoh: https://mandiribelajar.my.id/auth_google.php

// (Opsional) Autoload jika ada, tapi tidak wajib untuk versi cURL
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
