<?php
ob_start();

// index.php
require_once __DIR__ . '/config.php';
require_once "auth.php";
check_login();

// Router
$page              = isset($_GET['page']) ? $_GET['page'] : '';
$currentCourseSlug = isset($_GET['kursus']) ? trim($_GET['kursus']) : null;
$lessonId          = isset($_GET['lesson']) ? (int)$_GET['lesson'] : null;
$isQuiz            = isset($_GET['quiz']) ? (int)$_GET['quiz'] : 0;
$isAjax            = isset($_GET['ajax']) && $_GET['ajax'] == '1';

$contentFile = '';
$pageTitle   = 'Mandiri Belajar';

// -- ROUTE KE HALAMAN ADMIN & USER --
if ($page === 'admin') {
    $pageTitle = 'Dashboard Admin';
    $contentFile = __DIR__ . '/pages/admin_home.php';

} elseif ($page === 'admin_lessons') {
    $pageTitle = 'Kelola Materi';
    $contentFile = __DIR__ . '/pages/admin_lessons.php';

} elseif ($page === 'admin_lesson_form') {
    $pageTitle = 'Form Materi';
    $contentFile = __DIR__ . '/pages/admin_lesson_form.php';

} elseif ($page === 'profile') {
    $pageTitle = 'Profil Saya';
    $contentFile = __DIR__ . '/pages/profile.php';
    
} elseif ($page === 'progress') {
    $pageTitle = 'Progress Belajar';
    $contentFile = __DIR__ . '/pages/progress.php';

} elseif ($page === 'settings') {
    $pageTitle = 'Pengaturan';
    $contentFile = __DIR__ . '/pages/settings.php';

} elseif ($page === 'about') {
    $pageTitle = 'Tentang Kami';
    $contentFile = __DIR__ . '/pages/about.php';

} elseif ($page === 'privacy') {
    $pageTitle = 'Kebijakan Privasi';
    $contentFile = __DIR__ . '/pages/privacy.php';

} elseif ($page === 'admin_course_form') {
    $pageTitle = 'Form Kursus Admin';
    $contentFile = __DIR__ . '/pages/admin_course_form.php';

} elseif ($page === 'admin_modules') {
    $pageTitle = 'Kelola Bab Kursus';
    $contentFile = __DIR__ . '/pages/admin_modules.php';

} elseif ($page === 'admin_questions') {
    $pageTitle = 'Kelola Soal';
    $contentFile = __DIR__ . '/pages/admin_questions.php';

} elseif ($page === 'admin_question_form') {
    $pageTitle = 'Form Soal';
    $contentFile = __DIR__ . '/pages/admin_question_form.php';

} elseif ($page === 'admin_progress') {
    $pageTitle = 'Rekap Progres';
    $contentFile = __DIR__ . '/pages/admin_progress.php';

} elseif ($page === 'admin_users') {
    $pageTitle = 'Kelola User';
    $contentFile = __DIR__ . '/pages/admin_users.php';

} elseif ($page === 'admin_user_form') {
    $pageTitle = 'Form User';
    $contentFile = __DIR__ . '/pages/admin_user_form.php';

} elseif ($page === 'exam_view') {
    $pageTitle = 'Uji Komprehensif';
    $contentFile = __DIR__ . '/pages/exam_view.php';

} elseif ($page === 'exam_result') {
    $pageTitle = 'Hasil Ujian';
    $contentFile = __DIR__ . '/pages/exam_result.php';

} elseif ($page === 'messages') {
    $pageTitle = 'Pesan';
    $contentFile = __DIR__ . '/pages/messages.php';

} elseif ($page === 'notifications') {
    $pageTitle = 'Notifikasi';
    $contentFile = __DIR__ . '/pages/notifications.php';

// -- ROUTE HALAMAN UTAMA (USER) --
} elseif ($currentCourseSlug && $lessonId && $isQuiz) {
    $pageTitle = 'Kuis Materi';
    $contentFile = __DIR__ . '/pages/quiz_view.php';

} elseif ($currentCourseSlug && $lessonId) {
    $pageTitle = 'Materi Kursus';
    $contentFile = __DIR__ . '/pages/lesson_view.php';

} elseif ($currentCourseSlug) {
    $pageTitle = 'Detail Kursus';
    $contentFile = __DIR__ . '/pages/kursus_detail.php';

} else {
    $pageTitle = 'Mandiri Belajar – Beranda';
    $contentFile = __DIR__ . '/pages/home.php';
}

// RENDER HALAMAN
if (!$isAjax) {
    include __DIR__ . '/layout/header.php';
}

if (file_exists($contentFile)) {
    include $contentFile;
} else {
    echo "<div class='container my-5'><h3>Halaman tidak ditemukan</h3></div>";
}

if (!$isAjax) {
    include __DIR__ . '/layout/footer.php';
}

ob_end_flush();
