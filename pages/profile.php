<?php
require_once "auth.php";
check_login();

$userId = $_SESSION['user']['id'];
$msg    = isset($_GET['msg']) ? $_GET['msg'] : '';
$error  = '';

// 1. Ambil Data User Terbaru
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

// 2. Hitung Statistik Belajar
$stmtStat = $pdo->prepare("
    SELECT COUNT(*) as total_lulus 
    FROM lesson_progress 
    WHERE user_id = ? AND has_passed = 1
");
$stmtStat->execute([$userId]);
$stat = $stmtStat->fetch();

// ==========================================
// PROSES UPDATE PROFIL (NAMA & FOTO)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_update_profile'])) {
    $newName = trim($_POST['name']);
    $avatar  = $user['avatar']; // Default pakai foto lama

    // Logic Upload Foto
    if (!empty($_FILES['avatar']['name'])) {
        $fileTmp  = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileSize = $_FILES['avatar']['size'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($fileExt, $allowed)) {
            $error = "Format foto harus JPG, PNG, atau WEBP.";
        } elseif ($fileSize > 2000000) { // 2MB
            $error = "Ukuran foto maksimal 2MB.";
        } else {
            // Nama file unik: user_ID_waktu.ext
            $newFileName = 'user_' . $userId . '_' . time() . '.' . $fileExt;
            $uploadPath  = 'uploads/' . $newFileName;

            if (move_uploaded_file($fileTmp, $uploadPath)) {
                // Hapus foto lama jika ada (biar hemat space)
                if ($user['avatar'] && file_exists('uploads/' . $user['avatar'])) {
                    unlink('uploads/' . $user['avatar']);
                }
                $avatar = $newFileName;
            } else {
                $error = "Gagal mengupload foto.";
            }
        }
    }
    
    if (empty($error)) {
        if (empty($newName)) {
            $error = "Nama tidak boleh kosong.";
        } else {
            $stmtUpd = $pdo->prepare("UPDATE users SET name = ?, avatar = ? WHERE id = ?");
            $stmtUpd->execute([$newName, $avatar, $userId]);
            
            // Update Session agar header langsung berubah
            $_SESSION['user']['name']   = $newName;
            $_SESSION['user']['avatar'] = $avatar;
            
            header("Location: index.php?page=profile&msg=updated");
            exit;
        }
    }
}
?>

<div class="container my-5" style="max-width: 900px;">
    <h1 class="h3 mb-4">Profil Saya</h1>

    <?php if ($msg == 'updated'): ?>
        <div class="alert alert-success">Profil berhasil diperbarui.</div>
    <?php elseif ($msg == 'pass_changed'): ?>
        <div class="alert alert-success">Password berhasil diubah.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Data Diri</div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        
                        <div class="text-center mb-3">
                            <?php if (!empty($user['avatar']) && file_exists('uploads/' . $user['avatar'])): ?>
                                <img src="uploads/<?= htmlspecialchars($user['avatar']) ?>" 
                                     alt="Foto Profil" 
                                     class="rounded-circle shadow-sm"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center shadow-sm"
                                     style="width: 100px; height: 100px; font-size: 2.5rem;">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ganti Foto</label>
                            <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <button type="submit" name="btn_update_profile" class="btn btn-primary w-100">Simpan Profil</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4 bg-primary text-white border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 display-4">🏆</div>
                    <div>
                        <div class="fs-5 fw-bold">Pencapaian</div>
                        <div>Lulus <strong><?= (int)$stat['total_lulus'] ?></strong> materi.</div>
                    </div>
                </div>
            </div>

            <!-- Menu Lainnya (Mobile Friendly) -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white fw-bold">Menu Lainnya</div>
                <div class="list-group list-group-flush">
                    <a href="index.php?page=settings" class="list-group-item list-group-item-action">
                        ⚙️ Pengaturan & Ganti Password
                    </a>
                    <a href="index.php?page=about" class="list-group-item list-group-item-action">
                        ℹ️ Tentang Kami
                    </a>
                    <a href="index.php?page=privacy" class="list-group-item list-group-item-action">
                        🔒 Kebijakan Privasi
                    </a>
                    <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                        <a href="index.php?page=admin" class="list-group-item list-group-item-action text-danger">
                            🛠️ Admin Panel
                        </a>
                    <?php endif; ?>
                    <a href="auth.php?action=logout" class="list-group-item list-group-item-action text-danger" onclick="return confirm('Yakin ingin keluar?');">
                        🚪 Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>