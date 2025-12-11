<?php
require_once "auth.php";
check_login();

// Proteksi Halaman Pengaturan untuk Tamu
if (isset($_SESSION['is_guest']) && $_SESSION['is_guest']) {
    header("Location: index.php");
    exit;
}

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
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="autoThemeToggle">
                <label class="form-check-label" for="autoThemeToggle">Ikuti Tema Perangkat (Otomatis)</label>
            </div>
            
            <div class="form-check form-switch mb-3" id="manualThemeContainer">
                <input class="form-check-input" type="checkbox" id="darkThemeToggle">
                <label class="form-check-label" for="darkThemeToggle">Mode Gelap</label>
            </div>
            
            <div class="form-text">
                Aktifkan "Otomatis" agar tampilan menyesuaikan dengan pengaturan HP/Laptop Anda.
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
    const autoToggle = document.getElementById('autoThemeToggle');
    const darkToggle = document.getElementById('darkThemeToggle');
    const manualContainer = document.getElementById('manualThemeContainer');
    
    // Load saved setting
    const savedTheme = localStorage.getItem('appTheme') || 'auto';
    
    // Initialize UI state
    if (savedTheme === 'auto') {
        autoToggle.checked = true;
        darkToggle.disabled = true;
        manualContainer.style.opacity = '0.5';
        // Set dark toggle visual state based on system preference for clarity
        darkToggle.checked = window.matchMedia('(prefers-color-scheme: dark)').matches;
    } else {
        autoToggle.checked = false;
        darkToggle.disabled = false;
        manualContainer.style.opacity = '1';
        darkToggle.checked = (savedTheme === 'dark');
    }

    // Handle Auto Toggle
    autoToggle.addEventListener('change', function() {
        if (this.checked) {
            localStorage.setItem('appTheme', 'auto');
            darkToggle.disabled = true;
            manualContainer.style.opacity = '0.5';
            // Update visual state of dark toggle to match system
            darkToggle.checked = window.matchMedia('(prefers-color-scheme: dark)').matches;
            applyTheme('auto');
        } else {
            // When turning off auto, default to current system state or light?
            // Let's default to whatever the dark toggle currently shows (which matches system)
            const newTheme = darkToggle.checked ? 'dark' : 'light';
            localStorage.setItem('appTheme', newTheme);
            darkToggle.disabled = false;
            manualContainer.style.opacity = '1';
            applyTheme(newTheme);
        }
    });

    // Handle Dark Mode Toggle
    darkToggle.addEventListener('change', function() {
        if (!autoToggle.checked) {
            const newTheme = this.checked ? 'dark' : 'light';
            localStorage.setItem('appTheme', newTheme);
            applyTheme(newTheme);
        }
    });
});
</script>
