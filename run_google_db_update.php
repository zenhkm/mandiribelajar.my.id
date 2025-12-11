<?php
require_once "config.php";

echo "<h1>Update Database untuk Google Login</h1>";

try {
    // Cek apakah kolom google_id sudah ada
    $check = $pdo->query("SHOW COLUMNS FROM users LIKE 'google_id'");
    if ($check->rowCount() == 0) {
        // Tambahkan kolom google_id
        $sql = "ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL AFTER email";
        $pdo->exec($sql);
        echo "<p style='color:green'>Berhasil menambahkan kolom 'google_id' ke tabel users.</p>";
        
        // Tambahkan index agar pencarian cepat
        $pdo->exec("ALTER TABLE users ADD INDEX (google_id)");
    } else {
        echo "<p style='color:blue'>Kolom 'google_id' sudah ada.</p>";
    }

    // Opsional: Ubah kolom password agar boleh NULL (karena user Google tidak punya password di awal)
    // Namun, untuk keamanan dan kompatibilitas kode lama, kita biarkan NOT NULL
    // Nanti kita isi password random untuk user Google.

} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p>Selesai. <a href='index.php'>Kembali ke Home</a></p>";
?>