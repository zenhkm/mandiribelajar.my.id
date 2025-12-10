<?php
require_once "auth.php";
check_login();

$resultId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId   = $_SESSION['user']['id'];

// Ambil Data Hasil
$stmt = $pdo->prepare("
    SELECT r.*, c.title as course_title, c.slug as course_slug, c.exam_passing_grade, c.exam_show_score
    FROM course_exam_attempts r
    JOIN courses c ON r.course_id = c.id
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$resultId, $userId]);
$result = $stmt->fetch();

if (!$result) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Data hasil ujian tidak ditemukan.</div></div>";
    return;
}

$isPassed = $result['passed'] == 1;
$score    = $result['score'];
$minScore = $result['exam_passing_grade'] ?? 80;
$showScore= (!isset($result['exam_show_score']) || $result['exam_show_score'] == 1);

?>

<div class="container my-5 text-center" style="max-width: 600px;">
    <div class="card border-0 shadow-lg">
        <div class="card-body py-5">
            
            <?php if ($isPassed): ?>
                <div class="mb-4 text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                </div>
                <h2 class="h3 mb-3">Selamat! Anda Lulus</h2>
                <p class="text-muted mb-4">
                    Anda telah berhasil menyelesaikan Uji Komprehensif untuk kursus <strong><?= htmlspecialchars($result['course_title']) ?></strong>.
                </p>
            <?php else: ?>
                <div class="mb-4 text-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                    </svg>
                </div>
                <h2 class="h3 mb-3">Mohon Maaf, Belum Lulus</h2>
                <p class="text-muted mb-4">
                    Nilai Anda belum mencapai batas minimal kelulusan. Silakan pelajari materi kembali dan coba lagi.
                </p>
            <?php endif; ?>

            <?php if ($showScore): ?>
                <div class="display-1 fw-bold mb-2 <?= $isPassed ? 'text-success' : 'text-danger' ?>">
                    <?= $score ?>
                </div>
                <p class="text-muted small mb-4">Minimal Kelulusan: <?= $minScore ?></p>
            <?php endif; ?>

            <div class="d-grid gap-2 col-8 mx-auto">
                <?php if ($isPassed): ?>
                    <a href="index.php?kursus=<?= htmlspecialchars($result['course_slug']) ?>" class="btn btn-primary btn-lg">
                        Lihat Sertifikat
                    </a>
                <?php else: ?>
                    <a href="index.php?page=exam_view&kursus=<?= htmlspecialchars($result['course_slug']) ?>" class="btn btn-warning btn-lg">
                        Coba Ujian Lagi
                    </a>
                    <a href="index.php?kursus=<?= htmlspecialchars($result['course_slug']) ?>" class="btn btn-outline-secondary">
                        Kembali ke Materi
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
