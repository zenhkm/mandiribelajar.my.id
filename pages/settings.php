<?php
require_once "auth.php";
check_login();

$userId = $_SESSION['user']['id'];
$msg    = isset($_GET['msg']) ? $_GET['msg'] : '';
$error  = '';

// Ambil data user untuk verifikasi password lama
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

// ==========================================
// PROSES GANTI PASSWORD
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_change_password'])) {
    $oldPass = $_POST['old_password'];
    $newPass = $_POST['new_password'];
    $cnfPass = $_POST['confirm_password'];

    if (!password_verify($oldPass, $user['password_hash'])) {
        $error = "Password lama salah.";
    } elseif (strlen($newPass) < 6) {
        $error = "Password baru minimal 6 karakter.";
    } elseif ($newPass !== $cnfPass) {
        $error = "Konfirmasi password baru tidak cocok.";
    } else {
        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $stmtUpdPass = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmtUpdPass->execute([$newHash, $userId]);

        header("Location: index.php?page=settings&msg=pass_changed");
        exit;
    }
}
?>

<div class="container my-5" style="max-width: 600px;">
    <h1 class="h3 mb-4">Pengaturan</h1>

    <?php if ($msg == 'pass_changed'): ?>
        <div class="alert alert-success">Password berhasil diubah.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Tampilan (Dark/Light Mode) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Tampilan</div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Mode Tema</label>
                <select id="themeSelect" class="form-select">
                    <option value="auto">Otomatis (Ikut Perangkat)</option>
                    <option value="light">Terang (Light)</option>
                    <option value="dark">Gelap (Dark)</option>
                </select>
                <div class="form-text">
                    Pilih "Otomatis" untuk menyesuaikan dengan pengaturan HP/Laptop Anda.
                </div>
            </div>
        </div>
    </div>

    <!-- Ganti Password -->
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold text-danger">Ganti Password</div>
        <div class="card-body">
            <form method="post">
                <div class="mb-2">
                    <label class="form-label small">Password Lama</label>
                    <input type="password" name="old_password" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Password Baru</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Ulangi Password Baru</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="btn_change_password" class="btn btn-outline-danger w-100">Update Password</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const themeSelect = document.getElementById('themeSelect');
    
    // Load saved setting
    const savedTheme = localStorage.getItem('appTheme') || 'auto';
    themeSelect.value = savedTheme;

    themeSelect.addEventListener('change', function() {
        const theme = this.value;
        localStorage.setItem('appTheme', theme);
        applyTheme(theme);
    });
});
</script>
