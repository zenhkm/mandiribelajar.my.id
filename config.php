<?php
if (session_status() === PHP_SESSION_NONE) {
    // Konfigurasi penyimpanan sesi kustom agar tidak mudah logout
    $sessionPath = __DIR__ . '/sessions';
    if (!file_exists($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }
    session_save_path($sessionPath);
    
    // Perpanjang durasi sesi (opsional, misal 30 hari)
    ini_set('session.gc_maxlifetime', 2592000); // 30 hari
    ini_set('session.cookie_lifetime', 2592000);
    
    session_start();
}

$dsn  = 'mysql:host=localhost;dbname=quic1934_kursus;charset=utf8mb4';
$user = 'quic1934_zenhkm';
$pass = '03Maret1990';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (\PDOException $e) {
    die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
}

/**
 * Helper function to migrate guest data to real user
 */
function migrate_guest_data($guestUserId, $realUserId) {
    global $pdo;
    if (!$guestUserId || !$realUserId || $guestUserId == $realUserId) return;

    try {
        // 1. Migrate Lesson Progress
        // Use INSERT IGNORE ... SELECT to keep real user's existing progress if conflict
        $sql = "
            INSERT IGNORE INTO lesson_progress 
            (user_id, lesson_id, has_read, has_passed, attempts, last_score, created_at, updated_at)
            SELECT :real_id, lesson_id, has_read, has_passed, attempts, last_score, created_at, updated_at
            FROM lesson_progress
            WHERE user_id = :guest_id
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':real_id' => $realUserId, ':guest_id' => $guestUserId]);

        // 2. Delete guest progress (since it's moved or ignored)
        $stmtDel = $pdo->prepare("DELETE FROM lesson_progress WHERE user_id = ?");
        $stmtDel->execute([$guestUserId]);

    } catch (Exception $e) {
        // Log error but don't stop login
        error_log("Migration Error: " . $e->getMessage());
    }
}