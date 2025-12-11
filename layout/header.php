<?php
// layout/header.php
if (!isset($pageTitle)) {
    $pageTitle = 'Mandiri Belajar';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">

    <!-- Bootstrap 5 CDN -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Global Helper for SweetAlert2 Confirmations
        function confirmAction(event, url, message, confirmText = 'Ya, Lanjutkan!') {
            event.preventDefault();
            Swal.fire({
                title: 'Konfirmasi',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: confirmText,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

        function confirmSubmit(event, formId, message) {
            event.preventDefault();
            Swal.fire({
                title: 'Konfirmasi',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Kirim!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If formId is a string, get element, else assume it's the form element itself
                    const form = (typeof formId === 'string') ? document.getElementById(formId) : formId;
                    if(form) form.submit();
                }
            });
        }

        // Apply theme immediately to prevent flash
        (function() {
            const savedTheme = localStorage.getItem('appTheme') || 'auto';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (savedTheme === 'dark' || (savedTheme === 'auto' && prefersDark)) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                document.documentElement.classList.add('dark-mode');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
                document.documentElement.classList.remove('dark-mode');
            }
        })();

        // Function to be called from settings page
        function applyTheme(theme) {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (theme === 'auto' && prefersDark)) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                document.documentElement.classList.add('dark-mode');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
                document.documentElement.classList.remove('dark-mode');
            }
        }
    </script>

    <style>
        body {
            background: #f5f7fb;
            transition: background-color 0.3s, color 0.3s;
        }
        
        /* Dark Mode Overrides */
        [data-bs-theme="dark"] body {
            background: #121212 !important;
            color: #e0e0e0;
        }
        [data-bs-theme="dark"] .navbar,
        [data-bs-theme="dark"] .mobile-bottom-nav,
        [data-bs-theme="dark"] .card,
        [data-bs-theme="dark"] .list-group-item,
        [data-bs-theme="dark"] .bg-white {
            background-color: #1e1e1e !important;
            color: #e0e0e0 !important;
            border-color: #333 !important;
        }
        [data-bs-theme="dark"] .text-dark {
            color: #e0e0e0 !important;
        }
        [data-bs-theme="dark"] .text-muted {
            color: #a0a0a0 !important;
        }
        [data-bs-theme="dark"] .border-bottom,
        [data-bs-theme="dark"] .border-top,
        [data-bs-theme="dark"] .border {
            border-color: #333 !important;
        }
        [data-bs-theme="dark"] .bg-light {
            background-color: #2c2c2c !important;
        }
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #2c2c2c;
            border-color: #444;
            color: #e0e0e0;
        }
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #333;
            color: #fff;
            border-color: #6ea8fe;
        }
        [data-bs-theme="dark"] .btn-outline-light {
            color: #e0e0e0;
            border-color: #444;
        }
        [data-bs-theme="dark"] .btn-outline-light:hover {
            background-color: #444;
            color: #fff;
        }
        [data-bs-theme="dark"] .hero-badge {
            background: #1a2744;
            color: #6ea8fe;
        }
        [data-bs-theme="dark"] .status-tersedia {
            background: #0f3d24;
            color: #75b798;
        }
        [data-bs-theme="dark"] .status-segera {
            background: #3d2605;
            color: #ffda6a;
        }
        [data-bs-theme="dark"] .mobile-bottom-nav .nav-item.active {
            color: #6ea8fe;
        }
        [data-bs-theme="dark"] .text-primary {
            color: #6ea8fe !important;
        }
        [data-bs-theme="dark"] .bg-primary {
            background-color: #0d6efd !important; /* Keep brand color or slightly darken */
        }
        [data-bs-theme="dark"] a {
            color: #6ea8fe;
        }
        [data-bs-theme="dark"] .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        [data-bs-theme="dark"] .btn-outline-primary {
            color: #6ea8fe;
            border-color: #6ea8fe;
        }
        [data-bs-theme="dark"] .btn-outline-primary:hover {
            background-color: #6ea8fe;
            color: #000;
        }

        .navbar-brand span {
            font-weight: 700;
        }

        .hero-section {
            padding: 40px 0 24px 0;
        }

        .hero-badge {
            font-size: 0.85rem;
            padding: 4px 10px;
            border-radius: 999px;
            background: #e8f0ff;
            color: #2153ff;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .course-card {
            border-radius: 16px;
            border: 1px solid #e0e4f0;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            background: #ffffff;
        }

        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        }

        .course-status {
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 999px;
        }

        .status-tersedia {
            background: #e7f8ef;
            color: #13723d;
        }

        .status-segera {
            background: #fff4e5;
            color: #b56500;
        }

        .footer {
            font-size: 0.85rem;
            color: #6b7280;
            padding: 20px 0;
        }

        .section-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
        }

        .step-badge {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Mobile Bottom Nav */
        .mobile-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #e0e4f0;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            z-index: 1000;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        .mobile-bottom-nav .nav-item {
            text-align: center;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.75rem;
            flex: 1;
            cursor: pointer;
        }
        .mobile-bottom-nav .nav-item.active {
            color: #0d6efd;
            font-weight: 600;
        }
        .mobile-bottom-nav .nav-icon {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 2px;
        }
        /* Hide on desktop */
        @media (min-width: 768px) {
            .mobile-bottom-nav {
                display: none;
            }
        }
        /* Adjust main content for bottom nav on mobile */
        @media (max-width: 767px) {
            body {
                padding-bottom: 70px; /* Height of bottom nav */
                padding-top: 80px;    /* Height of top nav + spacing */
            }

            /* Fixed Top Navbar on Mobile */
            .navbar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1030;
            }

            /* Center Logo on Mobile */
            .navbar .container {
                justify-content: center;
            }
        }
    </style>
</head>

<body>




    <nav class="navbar navbar-light bg-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <!-- Logo SVG -->
                <svg width="36" height="36" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                    <rect width="40" height="40" rx="10" fill="#0d6efd"/>
                    <path d="M12 28V14L20 20L28 14V28" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div class="d-flex flex-column" style="line-height: 1.1;">
                    <span class="fw-bold text-primary" style="font-size: 1.1rem;">Mandiri</span>
                    <span class="fw-bold text-dark" style="font-size: 1.1rem;">Belajar</span>
                </div>
            </a>
            <div class="d-flex align-items-center d-none d-md-flex">
                <?php if (isset($_SESSION['user'])): ?>
                    
                    <?php if (isset($_SESSION['is_guest']) && $_SESSION['is_guest']): ?>
                        <div class="alert alert-warning py-1 px-2 mb-0 me-2 d-flex align-items-center" style="font-size: 0.8rem;">
                            <span class="me-2">⚠️ Mode Tamu</span>
                            <a href="auth.php?action=login" class="btn btn-sm btn-primary py-0" style="font-size: 0.8rem;">Login Member</a>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                        <a href="index.php?page=admin" class="btn btn-danger btn-sm me-2">
                            ⚙️ Admin Panel
                        </a>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['is_guest']) && $_SESSION['is_guest']): ?>
                        <!-- Tampilan Header untuk Tamu (Tanpa Link Profil) -->
                        <div class="d-flex align-items-center gap-2 border rounded-pill px-2 py-1 bg-light">
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px; font-size: 0.9rem; font-weight: bold;">
                                T
                            </div>
                            <span class="text-dark small fw-semibold me-1 d-none d-sm-inline">
                                Tamu
                            </span>
                        </div>
                    <?php else: ?>
                        <!-- Tampilan Header untuk Member (Link Profil Aktif) -->
                        <a href="index.php?page=profile" class="text-decoration-none d-flex align-items-center gap-2 border rounded-pill px-2 py-1 bg-light">

                            <?php
                            $userAvatar = $_SESSION['user']['avatar'] ?? null;
                            $userName   = $_SESSION['user']['name'] ?? 'User';

                            $avatarSrc = null;
                            if (!empty($userAvatar)) {
                                if (strpos($userAvatar, 'http') === 0) {
                                    $avatarSrc = $userAvatar;
                                } elseif (file_exists('uploads/' . $userAvatar)) {
                                    $avatarSrc = 'uploads/' . $userAvatar;
                                }
                            }

                            if ($avatarSrc):
                            ?>
                                <img src="<?= htmlspecialchars($avatarSrc) ?>"
                                    alt="User"
                                    class="rounded-circle"
                                    style="width: 32px; height: 32px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px; font-size: 0.9rem; font-weight: bold;">
                                    <?= strtoupper(substr($userName, 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <span class="text-dark small fw-semibold me-1 d-none d-sm-inline">
                                <?= htmlspecialchars($userName) ?>
                            </span>
                        </a>
                    <?php endif; ?>

                    <?php if (!isset($_SESSION['is_guest'])): ?>
                    <a href="auth.php?action=logout" class="btn btn-outline-danger btn-sm ms-2" onclick="confirmAction(event, this.href, 'Yakin ingin keluar?', 'Ya, Logout')">
                        Logout
                    </a>
                    <?php endif; ?>

                <?php else: ?>
                    <a href="auth.php?action=login" class="btn btn-outline-light btn-sm me-2">Login</a>
                    <a href="auth.php?action=register" class="btn btn-warning btn-sm">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div id="main-content">
        <?php if (isset($_SESSION['is_guest']) && $_SESSION['is_guest']): ?>
            <div class="container mt-3">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Perhatian!</strong> Anda sedang mengakses sebagai Tamu. Progress belajar Anda mungkin hilang jika IP berubah atau cache dibersihkan. 
                    <a href="auth.php?action=login" class="alert-link">Login atau Daftar</a> untuk menyimpan progress secara permanen.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>