<?php
require_once "auth.php";
check_login();
// pages/kursus_detail.php

// Pastikan slug tersedia
if (empty($currentCourseSlug)) {
    ?>
    <div class="container my-5">
        <div class="alert alert-danger">
            Kursus tidak ditemukan (slug kosong).
        </div>
    </div>
    <?php
    return;
}

// Ambil data kursus berdasarkan slug (?kursus=waris)
$stmt = $pdo->prepare("SELECT * FROM courses WHERE slug = ?");
$stmt->execute([$currentCourseSlug]);
$course = $stmt->fetch();

if (!$course) {
    ?>
    <div class="container my-5">
        <div class="alert alert-danger">
            Kursus dengan kode <strong><?= htmlspecialchars($currentCourseSlug) ?></strong> tidak ditemukan.
        </div>
    </div>
    <?php
    return;
}

// Ambil daftar modul/bab kursus
$stmtMod = $pdo->prepare("
    SELECT *
    FROM course_modules
    WHERE course_id = ?
    ORDER BY module_order ASC, id ASC
");
$stmtMod->execute([$course['id']]);
$modules = $stmtMod->fetchAll();

// Ambil daftar materi (lessons) per modul
$userId = $_SESSION['user']['id'] ?? 0;


$stmtLessons = $pdo->prepare("
    SELECT l.id, l.module_id, l.lesson_order, l.title
    FROM lessons l
    WHERE l.course_id = ?
    ORDER BY l.module_id ASC, l.lesson_order ASC, l.id ASC
");
$stmtLessons->execute([$course['id']]);
$lessonsAll = $stmtLessons->fetchAll();

$lessonsByModule = [];
if ($lessonsAll) {
    foreach ($lessonsAll as $ls) {
        $mid = (int)$ls['module_id'];
        if (!isset($lessonsByModule[$mid])) {
            $lessonsByModule[$mid] = [];
        }
        $lessonsByModule[$mid][] = $ls;
    }
}

// Ambil progres lesson untuk user ini
$stmtProg = $pdo->prepare("
    SELECT lesson_id, has_passed
    FROM lesson_progress
    WHERE user_id = ?
");
$stmtProg->execute([$userId]);
$progressRows = $stmtProg->fetchAll();

$progressByLesson = [];
if ($progressRows) {
    foreach ($progressRows as $pr) {
        $progressByLesson[(int)$pr['lesson_id']] = (int)$pr['has_passed'];
    }
}



// Tentukan materi mana yang harus dikerjakan (Resume)
$resumeLesson = null;
$isCourseComplete = false;
$hasStarted = false;

if ($lessonsAll) {
    foreach ($lessonsAll as $ls) {
        $lid = (int)$ls['id'];
        // Cek apakah user sudah lulus materi ini
        $passed = isset($progressByLesson[$lid]) && $progressByLesson[$lid] == 1;
        
        if ($passed) {
            $hasStarted = true;
        } else {
            // Ketemu materi pertama yang BELUM lulus -> ini targetnya
            $resumeLesson = $ls;
            break;
        }
    }
    
    // Jika loop selesai dan $resumeLesson masih null, berarti semua sudah lulus
    if (!$resumeLesson && count($lessonsAll) > 0) {
        $isCourseComplete = true;
        // Arahkan ke materi terakhir
        $resumeLesson = end($lessonsAll); 
    }
}
?>

<section class="hero-section">
    <div class="container">
        <div class="mb-2">
            <a href="index.php" class="small text-decoration-none">
                ← Kembali ke daftar kursus
            </a>
        </div>

        <div class="row gy-3">
            <div class="col-12 col-lg-8">
                <div class="hero-badge mb-2">
                    <span><?= htmlspecialchars($course['level']) ?></span>
                    <span>•</span>
                    <span><?= htmlspecialchars($course['status']) ?></span>
                </div>
                <h1 class="h4 mb-2">
                    <?= htmlspecialchars($course['title']) ?>
                </h1>
                <p class="text-muted mb-3">
                    <?= htmlspecialchars($course['description']) ?>
                </p>

                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-4">
                        <div class="small text-muted">Perkiraan durasi</div>
                        <div class="fw-semibold">
                            <?= htmlspecialchars($course['duration']) ?>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="small text-muted">Jumlah materi</div>
                        <div class="fw-semibold">
                            <?= count($lessonsAll) ?> materi
                        </div>
                    </div>
                </div>

                <div class="alert alert-info small mb-3">
                    <div class="fw-semibold mb-1">Alur belajar kursus ini:</div>
                    <ol class="mb-0 ps-3">
                        <li>Baca materi dari awal sampai akhir, atau tonton video sampai selesai.</li>
                        <li>Kerjakan soal sesuai poin-poin materi (teori & praktik).</li>
                        <li>Jika semua soal benar, materi berikutnya akan terbuka.</li>
                        <li>Jika belum benar semua, Anda boleh mengulang soal sampai berhasil.</li>
                    </ol>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="section-label mb-2">
                            <?php if ($isCourseComplete): ?>
                                Kursus Selesai
                            <?php elseif ($hasStarted): ?>
                                Lanjut Belajar
                            <?php else: ?>
                                Mulai Kursus
                            <?php endif; ?>
                        </div>
                        
                        <p class="small text-muted">
                            <?php if ($isCourseComplete): ?>
                                Selamat! Anda telah menyelesaikan semua materi di kursus ini.
                            <?php elseif ($hasStarted): ?>
                                Lanjutkan progres belajar Anda di materi terakhir yang belum selesai.
                            <?php else: ?>
                                Mulailah dari materi pertama. Baca materi, kerjakan soal, lalu lanjut ke materi berikutnya.
                            <?php endif; ?>
                        </p>

                        <?php if ($isCourseComplete): ?>
                            <a class="btn btn-success w-100 mb-2" target="_blank"
                               href="certificate.php?course_id=<?= (int)$course['id'] ?>">
                                <i class="bi bi-award"></i> Download Sertifikat
                            </a>
                        <?php elseif ($resumeLesson): ?>
                            <a class="btn btn-primary w-100 mb-2"
                               href="index.php?kursus=<?= urlencode($course['slug']) ?>&lesson=<?= (int)$resumeLesson['id'] ?>">
                                <?php if ($hasStarted): ?>
                                    Lanjut: <?= htmlspecialchars($resumeLesson['title']) ?>
                                <?php else: ?>
                                    Mulai dari Bab 1
                                <?php endif; ?>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100 mb-2" type="button" disabled>
                                Belum ada materi
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar modul/bab -->
        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h6 mb-0">Struktur Kursus</h2>
                <small class="text-muted">
                    Data bab/modul diambil dari database (table <code>course_modules</code>).
                </small>
            </div>

                        <div class="row g-3">
                <?php foreach ($modules as $mod): ?>
                    <?php
                    $mid = (int)$mod['id'];
                    $moduleLessons = isset($lessonsByModule[$mid]) ? $lessonsByModule[$mid] : [];
                    ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="step-badge bg-primary text-white me-2">
                                        <?= (int)$mod['module_order'] ?>
                                    </span>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($mod['title']) ?>
                                    </div>
                                </div>

                                <?php if (!empty($mod['summary'])): ?>
                                    <p class="small text-muted mb-2">
                                        <?= nl2br(htmlspecialchars($mod['summary'])) ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($moduleLessons)): ?>
                                    <ul class="small ps-3 mb-0">
                                        <?php foreach ($moduleLessons as $ls): ?>
                                            <?php
                                            $lsId     = (int)$ls['id'];
                                            $hasPassed = isset($progressByLesson[$lsId]) && $progressByLesson[$lsId] === 1;
                                            ?>
                                            <li class="mb-1">
                                                <a href="index.php?kursus=<?= htmlspecialchars($course['slug']) ?>&lesson=<?= $lsId ?>"
                                                   class="text-decoration-none">
                                                    Materi <?= (int)$ls['lesson_order'] ?>:
                                                    <?= htmlspecialchars($ls['title']) ?>
                                                </a>
                                                <?php if ($hasPassed): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success ms-1">
                                                        Lulus
                                                    </span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="small text-muted mb-0">
                                        Belum ada materi pada bab ini.
                                    </p>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>


                <?php if (empty($modules)): ?>
                    <div class="col-12">
                        <div class="alert alert-warning small">
                            Belum ada modul/bab yang terdaftar untuk kursus ini.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>
