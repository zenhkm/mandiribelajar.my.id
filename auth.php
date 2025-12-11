<?php
// auth.php
require __DIR__ . '/config.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'login';
$errors = [];
$success = '';


// BENAR:
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        // Jika belum login, otomatis login sebagai tamu (Guest)
        login_as_guest();
    }
}

function login_as_guest() {
    global $pdo;
    
    // Dapatkan IP Address
    $ip = $_SERVER['REMOTE_ADDR'];
    // Bersihkan IP agar aman untuk string
    $safe_ip = preg_replace('/[^0-9a-fA-F:.]/', '', $ip);
    
    $guest_email = "guest_{$safe_ip}@mandiribelajar.local";
    
    // Cek apakah user guest ini sudah ada
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$guest_email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // User guest sudah ada, login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = [
            'id'     => $user['id'],
            'name'   => $user['name'],
            'role'   => $user['role'],
            'avatar' => $user['avatar'] ?? null
        ];
        $_SESSION['is_guest'] = true;
    } else {
        // Buat user guest baru
        $name = "Tamu ({$safe_ip})";
        // Password random, tidak akan dipakai login manual
        $password_hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        
        $stmtInsert = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, role, created_at) 
            VALUES (?, ?, ?, 'student', NOW())
        ");
        
        if ($stmtInsert->execute([$name, $guest_email, $password_hash])) {
            $newUserId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user'] = [
                'id'     => $newUserId,
                'name'   => $name,
                'role'   => 'student',
                'avatar' => null
            ];
            $_SESSION['is_guest'] = true;
        } else {
            // Fallback jika gagal create user (misal database error)
            // Redirect ke login page biasa
            header("Location: auth.php?action=login"); 
            exit;
        }
    }
}


// ... di bawah function check_login() ...

function check_admin() {
    check_login(); // Pastikan login dulu
    // Cek apakah role-nya admin
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
        // Jika bukan admin, lempar ke home
        header("Location: index.php");
        exit;
    }
}

// PERBAIKAN: Tambahkan cek basename()
// Izinkan akses ke halaman login jika user adalah Guest
if (basename($_SERVER['PHP_SELF']) === 'auth.php' && isset($_SESSION['user']) && !isset($_SESSION['is_guest']) && $action === 'login' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// =============== PROSES REGISTER ===============
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($name === '') {
        $errors[] = 'Nama wajib diisi.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    if ($password !== $password2) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

    if (empty($errors)) {
        // cek email sudah ada atau belum
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, role, password_hash)
                VALUES (?, ?, 'user', ?)
            ");
            $stmt->execute([$name, $email, $hash]);

            $success = 'Pendaftaran berhasil. Silakan login dengan email dan password Anda.';
            $action  = 'login'; // setelah daftar, tampilkan form login
        }
    }
}

// =============== PROSES LOGIN ===============
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } else {
        // ⚠️ PENTING: hanya cek email, password diverifikasi dengan password_verify
        $stmt = $pdo->prepare("
            SELECT id, name, email, role, password_hash
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Hapus status guest jika ada
            if (isset($_SESSION['is_guest'])) {
                unset($_SESSION['is_guest']);
            }

            // Simpan seluruh data user ke dalam array 'user'
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role'],
                'avatar' => $user['avatar'] ?? null
            ];
            // Opsional: Simpan user_id terpisah jika file lain membutuhkannya
            $_SESSION['user_id'] = $user['id']; 
        
            header("Location: index.php");
            exit;
        }

    }
}

// =============== PROSES LOGOUT ===============
if ($action === 'logout') {
    unset($_SESSION['user']);
    session_destroy();
    header('Location: index.php');
    exit;
}



if (basename($_SERVER['PHP_SELF']) !== 'auth.php') {
    return;
}




$pageTitle = ($action === 'register') ? 'Daftar Akun Kursus' : 'Login Kursus';

// Sesuaikan path header/footer dengan struktur project-mu.
// Kalau sebelumnya pakai layout/header.php & layout/footer.php, pakai ini:
include __DIR__ . '/layout/header.php';
?>

<div class="container my-4" style="max-width: 480px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title mb-3 text-center">
                <?= $action === 'register' ? 'Daftar Akun Baru' : 'Login Peserta Kursus' ?>
            </h3>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($action === 'register'): ?>

                <form method="post" action="auth.php?action=register">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= isset($name) ? htmlspecialchars($name) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ulangi Password</label>
                        <input type="password" name="password2" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Daftar</button>
                </form>

                <div class="text-center my-3">
                    <span class="text-muted small">atau</span>
                </div>
                
                <a href="auth_google.php" class="btn btn-outline-danger w-100 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google me-2" viewBox="0 0 16 16">
                        <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352-2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z"/>
                    </svg>
                    Daftar dengan Google
                </a>

                <p class="mt-3 text-center">
                    Sudah punya akun?
                    <a href="auth.php?action=login">Login di sini</a>
                </p>

            <?php else: ?>

                <form method="post" action="auth.php?action=login">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                    </div>
                    <div class="mb-2"> <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3 text-end">
                        <a href="auth_forgot.php" class="small text-decoration-none text-muted">
                            Lupa Password?
                        </a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <div class="text-center my-3">
                    <span class="text-muted small">atau</span>
                </div>
                
                <a href="auth_google.php" class="btn btn-outline-danger w-100 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google me-2" viewBox="0 0 16 16">
                        <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352-2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z"/>
                    </svg>
                    Login dengan Google
                </a>

                <p class="mt-3 text-center">
                    Belum punya akun?
                    <a href="auth.php?action=register">Daftar dulu</a>
                </p>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/layout/footer.php';
