<?php
require_once "auth.php";
check_login();

$userId = $_SESSION['user']['id'] ?? 0;

// 1. Ambil semua kursus
$stmt = $pdo->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) as real_lesson_count
    FROM courses c 
    ORDER BY c.id ASC
");
$courses = $stmt->fetchAll();

// 2. Hitung progres user per kursus
$sqlProgress = "
    SELECT 
        l.course_id, 
        COUNT(lp.lesson_id) as total_passed
    FROM lesson_progress lp
    JOIN lessons l ON lp.lesson_id = l.id
    WHERE lp.user_id = ? AND lp.has_passed = 1
    GROUP BY l.course_id
";
$stmtProg = $pdo->prepare($sqlProgress);
$stmtProg->execute([$userId]);
$progressData = $stmtProg->fetchAll(PDO::FETCH_KEY_PAIR);

?>

<div class="container py-4">
    <h2 class="h4 mb-4">Progress Belajar Saya</h2>

    <div class="row g-3">
        <?php foreach ($courses as $course): ?>
            <?php 
                $courseId    = $course['id'];
                $totalLesson = (int)$course['real_lesson_count'];
                $passedCount = isset($progressData[$courseId]) ? (int)$progressData[$courseId] : 0;
                $isAvailable = ($course['status'] === 'Tersedia');
                
                // Hitung Persentase
                $percent = 0;
                if ($totalLesson > 0) {
                    $percent = round(($passedCount / $totalLesson) * 100);
                }
                if ($percent > 100) $percent = 100;

                // Cari materi selanjutnya (yang belum lulus)
                $stmtNext = $pdo->prepare("
                    SELECT id, title FROM lessons 
                    WHERE course_id = ? 
                    AND id NOT IN (
                        SELECT lesson_id FROM lesson_progress 
                        WHERE user_id = ? AND has_passed = 1
                    )
                    ORDER BY lesson_order ASC, id ASC 
                    LIMIT 1
                ");
                $stmtNext->execute([$courseId, $userId]);
                $nextLesson = $stmtNext->fetch();
            ?>
            
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="card-title mb-1"><?= htmlspecialchars($course['title']) ?></h5>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($course['summary']) ?></p>
                            </div>
                            <?php if ($percent >= 100): ?>
                                <span class="badge bg-success rounded-pill">Selesai</span>
                            <?php elseif ($percent > 0): ?>
                                <span class="badge bg-primary rounded-pill">Berjalan</span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill">Belum Mulai</span>
                            <?php endif; ?>
                        </div>

                        <div class="progress my-3" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?= $percent ?>%;" 
                                 aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                            <span><?= $passedCount ?> / <?= $totalLesson ?> Materi Selesai</span>
                            <span><?= $percent ?>%</span>
                        </div>

                        <?php if ($isAvailable): ?>
                            <?php if ($percent >= 100): ?>
                                <a href="certificate.php?course_id=<?= $courseId ?>" class="btn btn-outline-success w-100">
                                    <span class="me-2">🏆</span> Lihat Sertifikat
                                </a>
                            <?php elseif ($nextLesson): ?>
                                <a href="index.php?kursus=<?= urlencode($course['slug']) ?>&lesson=<?= $nextLesson['id'] ?>" class="btn btn-primary w-100">
                                    Lanjut Belajar: <?= htmlspecialchars($nextLesson['title']) ?> →
                                </a>
                            <?php else: ?>
                                <a href="index.php?kursus=<?= urlencode($course['slug']) ?>" class="btn btn-outline-primary w-100">
                                    Mulai Belajar
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100" disabled>Segera Hadir</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($courses)): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">Belum ada kursus yang tersedia.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
