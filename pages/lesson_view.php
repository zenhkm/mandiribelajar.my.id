<?php
require_once "auth.php";
check_login();
// pages/lesson_view.php

// --- Debug ringan (kalau mau lihat error, boleh aktifkan lalu nanti dimatikan) ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Pastikan $lessonId dan $currentCourseSlug sudah diset dari index.php
if (empty($lessonId) || empty($currentCourseSlug)) {
?>
    <div class="container my-5">
        <div class="alert alert-danger">
            Parameter materi tidak lengkap.
        </div>
    </div>
<?php
    return;
}

// Ambil data lesson + course + module
$sql = "
    SELECT 
        l.*,
        c.title AS course_title,
        c.slug  AS course_slug,
        m.title AS module_title,
        m.module_order
    FROM lessons l
    JOIN courses c ON l.course_id = c.id
    JOIN course_modules m ON l.module_id = m.id
    WHERE l.id = ? AND c.slug = ?
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute(array($lessonId, $currentCourseSlug));
$lesson = $stmt->fetch();

if (!$lesson) {
?>
    <div class="container my-5">
        <div class="alert alert-danger">
            Materi tidak ditemukan untuk kursus ini.
        </div>
    </div>
<?php
    return;
}

// ----------------------------------------------------
// Cek apakah user boleh mengakses materi ini
// Syarat: kalau ada materi sebelumnya, harus sudah lulus
// ----------------------------------------------------
$userId = $_SESSION['user']['id'] ?? 0;
$canAccessLesson = true;

// Cari lesson sebelumnya dalam kursus yang sama
// Cari lesson sebelumnya yang paling dekat posisinya
// Logika: Cari di modul yang sama dengan order lebih kecil, ATAU cari di modul sebelumnya
// PERBAIKAN: Cek materi sebelumnya berdasarkan Bab dan Materi
$sqlPrev = "
    SELECT l.id, l.title
    FROM lessons l
    JOIN course_modules m ON l.module_id = m.id
    WHERE l.course_id = ?
      AND (
          (m.module_order = ? AND l.lesson_order < ?) 
          OR 
          (m.module_order < ?)
      )
    ORDER BY m.module_order DESC, l.lesson_order DESC
    LIMIT 1
";
$stmtPrev = $pdo->prepare($sqlPrev);
$stmtPrev->execute(array(
    $lesson['course_id'],
    $lesson['module_order'],
    $lesson['lesson_order'],
    $lesson['module_order']
));
$prevLesson = $stmtPrev->fetch();

if ($prevLesson) {
    $sqlPrevProg = "
        SELECT has_passed
        FROM lesson_progress
        WHERE user_id = ? AND lesson_id = ?
        LIMIT 1
    ";
    $stmtPrevProg = $pdo->prepare($sqlPrevProg);
    $stmtPrevProg->execute(array($userId, $prevLesson['id']));
    $rowPrevProg = $stmtPrevProg->fetch();

    // Kalau belum pernah mengerjakan atau belum lulus → tidak boleh akses
    if (!$rowPrevProg || (int)$rowPrevProg['has_passed'] !== 1) {
        $canAccessLesson = false;
    }
}

// Kalau tidak boleh akses, tampilkan pesan dan hentikan
if (!$canAccessLesson) {
?>
    <div class="container my-5">
        <div class="alert alert-warning">
            <h4 class="alert-heading h5">Akses Ditolak</h4>
            <p>Untuk mengakses materi ini, Anda harus menyelesaikan dan lulus materi sebelumnya terlebih dahulu.</p>
            <hr>
            <p class="mb-0">
                Silakan selesaikan materi: <br>
                <a href="index.php?kursus=<?= htmlspecialchars($currentCourseSlug) ?>&lesson=<?= (int)$prevLesson['id'] ?>" class="btn btn-warning mt-2">
                    ← Kembali ke <?= htmlspecialchars($prevLesson['title']) ?>
                </a>
            </p>
        </div>
    </div>
<?php
    return;
}

// Ambil soal & opsi untuk lesson ini
// CATATAN: Kode soal sudah dipindahkan ke quiz_view.php
// Kita hanya perlu cek apakah ada soal atau tidak
$sqlQ = "
    SELECT COUNT(*) as count
    FROM lesson_questions q
    WHERE q.lesson_id = ?
    LIMIT 1
";
$stmtQ = $pdo->prepare($sqlQ);
$stmtQ->execute(array($lesson['id']));
$rowQ = $stmtQ->fetch();
$hasQuiz = isset($rowQ['count']) && (int)$rowQ['count'] > 0;

// CATATAN: Logika penilaian soal sudah dipindahkan ke quiz_view.php
// Di halaman materi, kita hanya menampilkan status dan tombol untuk membuka soal

// ============================================================
// FIX: Pastikan $hasPassedLesson selalu terdefinisi
// (Agar tidak error saat halaman dibuka pertama kali)
// ============================================================
if (!isset($hasPassedLesson)) {
    $hasPassedLesson = false;

    // LOGIC FIX: Jika tidak ada soal (hasQuiz==false), user otomatis dianggap lulus
    // (Supaya bisa lanjut ke materi berikutnya)
    if (!$hasQuiz) {
        $hasPassedLesson = true;
        
        // Simpan ke database bahwa user sudah "lulus" lesson ini
        try {
            $stmtAutoPass = $pdo->prepare("
                INSERT INTO lesson_progress (user_id, lesson_id, has_read, has_passed, attempts, last_score)
                VALUES (:user_id, :lesson_id, 1, 1, 0, 100)
                ON DUPLICATE KEY UPDATE
                    has_passed = 1,
                    updated_at = CURRENT_TIMESTAMP
            ");
            $stmtAutoPass->execute([
                ':user_id'   => $userId,
                ':lesson_id' => $lesson['id'],
            ]);
        } catch (Exception $e) {
            // Biarkan, sudah otomatis lulus
        }
    } else {
        // Cek ke database: Apakah user ini sudah pernah lulus materi ini?
        $stmtCheckPass = $pdo->prepare("
            SELECT has_passed 
            FROM lesson_progress 
            WHERE user_id = ? AND lesson_id = ? 
            LIMIT 1
        ");
        $stmtCheckPass->execute([$userId, $lesson['id']]);
        $rowPass = $stmtCheckPass->fetch();

        if ($rowPass && (int)$rowPass['has_passed'] === 1) {
            $hasPassedLesson = true;
        }
    }
}
// ============================================================

// Ambil apakah user sudah membaca materi ini
$hasRead = false;
$stmtRead = $pdo->prepare("SELECT has_read FROM lesson_progress WHERE user_id = ? AND lesson_id = ? LIMIT 1");
$stmtRead->execute([$userId, $lesson['id']]);
$rowRead = $stmtRead->fetch();
if ($rowRead && (int)$rowRead['has_read'] === 1) {
    $hasRead = true;
}

// Cari next lesson untuk navigasi
$nextLesson = null;
$sqlNext = "
    SELECT l.id, l.title
    FROM lessons l
    JOIN course_modules m ON l.module_id = m.id
    WHERE l.course_id = ?
      AND (
          (m.module_order = ? AND l.lesson_order > ?)
          OR
          (m.module_order > ?)
      )
    ORDER BY m.module_order ASC, l.lesson_order ASC
    LIMIT 1
";
$stmtNext = $pdo->prepare($sqlNext);
$stmtNext->execute(array(
    $lesson['course_id'],
    $lesson['module_order'],
    $lesson['lesson_order'],
    $lesson['module_order']
));
$nextLesson = $stmtNext->fetch();
?>

<section class="hero-section">
    <div class="container">
        <div class="mb-2 small">
            <a href="index.php" class="text-decoration-none">Beranda</a>
            &nbsp;›&nbsp;
            <a href="index.php?kursus=<?= htmlspecialchars($lesson['course_slug']) ?>"
                class="text-decoration-none">
                <?= htmlspecialchars($lesson['course_title']) ?>
            </a>
            &nbsp;›&nbsp;
            <span class="text-muted">
                <?= htmlspecialchars($lesson['module_title']) ?>
            </span>
        </div>

        <div class="row gy-3">
            <div class="col-12 col-lg-8">
                <div class="hero-badge mb-2">
                    <span>Bab <?= (int)$lesson['module_order'] ?></span>
                    <span>•</span>
                    <span>Materi</span>
                </div>
                <h1 class="h4 mb-2">
                    <?= htmlspecialchars($lesson['title']) ?>
                </h1>

                <!-- Konten materi -->
                <div class="card border-0 shadow-sm mb-3" id="lesson-content">
                    <div class="card-body">
                        <?php if ($lesson['content_type'] === 'video' || $lesson['content_type'] === 'mixed'): ?>
                            <?php if (!empty($lesson['video_url'])): ?>
                                <div class="mb-3">
                                    <video width="100%" controls>
                                        <source src="<?= htmlspecialchars($lesson['video_url']) ?>" type="video/mp4">
                                        Browser Anda tidak mendukung pemutar video.
                                    </video>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (
                            $lesson['content_type'] === 'text'
                            || $lesson['content_type'] === 'mixed'
                        ): ?>
                            <?php
                            // Pecah materi per baris sebagai poin
                            $rawLines = preg_split('/\r\n|\r|\n/', trim($lesson['content_text']));
                            $points = array();
                            if (is_array($rawLines)) {
                                foreach ($rawLines as $line) {
                                    $line = trim($line);
                                    if ($line === '') continue;
                                    $points[] = $line;
                                }
                            }
                            ?>
                            <?php if (!empty($points)): ?>
                                <ol id="lesson-points" class="small">
                                    <?php foreach ($points as $i => $p): ?>
                                        <?php
                                        // Hitung jumlah kata
                                        $plain = strip_tags($p);
                                        $wordCount = str_word_count($plain);
                                        if ($wordCount <= 0) $wordCount = 1;
                                        // 0.3 detik per kata, min 3 detik
                                        $requiredSeconds = max(3, ceil($wordCount * 0.3));
                                        ?>
                                        <li class="lesson-point"
                                            data-required-seconds="<?= $requiredSeconds ?>"
                                            data-index="<?= $i ?>"
                                            <?= $i > 0 ? 'style="display:none;"' : '' ?>>
                                            <?= nl2br(htmlspecialchars($p)) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>

                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <button type="button"
                                        id="btn-next-point"
                                        class="btn btn-primary btn-sm">
                                        Lanjut
                                    </button>
                                    <small class="text-muted" id="lesson-hint">
                                        Tekan tombol <strong>Lanjut</strong> setelah membaca poin ini sampai selesai.
                                    </small>
                                </div>
                            <?php else: ?>
                                <div class="small" style="white-space: pre-wrap;">
                                    <?= nl2br(htmlspecialchars($lesson['content_text'])) ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Panel samping -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="section-label mb-2">Status Belajar</div>

                        <?php if ($hasPassedLesson): ?>
                            <div class="alert alert-success d-flex align-items-center mb-0 p-2 small">
                                <span class="fs-4 me-2">🎉</span>
                                <div>
                                    <strong>Lulus!</strong><br>
                                    Anda sudah menyelesaikan materi ini.
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning d-flex align-items-center mb-0 p-2 small">
                                <span class="fs-4 me-2">⏳</span>
                                <div>
                                    <strong>Belum Selesai</strong><br>
                                    Silakan baca materi & kerjakan soal dengan benar.
                                </div>
                            </div>
                        <?php endif; ?>

                        <hr class="my-3 opacity-25">

                        <div class="small text-muted">
                            <strong>Ketentuan Lulus:</strong>
                            <ul class="mb-0 ps-3 mt-1">
                                <li>Baca semua poin materi sampai selesai.</li>
                                <li>Jawab semua soal kuis dengan benar (100%).</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian Soal (di halaman terpisah) -->
        <div class="mt-4">
            <h2 class="h6 mb-3">Soal Materi Ini</h2>
            
            <?php if (!$hasQuiz): ?>
                <div class="alert alert-info small">
                    <p class="mb-0">
                        Materi ini tidak memiliki soal. Anda dianggap sudah menyelesaikan materi ini.
                    </p>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            Setelah membaca materi di atas, silakan kerjakan soal untuk menguji pemahaman Anda.
                            Untuk dinyatakan lulus, semua jawaban harus benar (100%).
                        </p>
                        
                        <?php if ($hasPassedLesson): ?>
                            <div class="alert alert-success small mb-3">
                                <strong>✓ Selamat!</strong> Anda sudah menyelesaikan kuis ini dengan benar.
                            </div>
                            <a href="index.php?kursus=<?= htmlspecialchars($lesson['course_slug']) ?>&lesson=<?= (int)$lesson['id'] ?>&quiz=1"
                               class="btn btn-success btn-sm">
                                Lihat Hasil Kuis
                            </a>
                        <?php else: ?>
                            <!-- Disabled button initially, enabled after reading via AJAX -->
                                <button id="btn-go-quiz" class="btn btn-primary btn-sm" <?php if (!$hasPassedLesson && !$hasRead) echo 'disabled'; ?>
                                    title="<?php echo (!$hasPassedLesson && !$hasRead) ? 'Baca materi sampai selesai untuk membuka soal' : ''; ?>"
                                    data-quiz-url="index.php?kursus=<?= htmlspecialchars($lesson['course_slug']) ?>&lesson=<?= (int)$lesson['id'] ?>&quiz=1">
                                Kerjakan Soal →
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tombol Navigasi Materi -->
        <div class="mt-4">
            <?php if ($hasPassedLesson): ?>
                <?php if (!empty($nextLesson)): ?>
                    <a href="index.php?kursus=<?= htmlspecialchars($lesson['course_slug']) ?>&lesson=<?= (int)$nextLesson['id'] ?>"
                       class="btn btn-success w-100">
                        ✓ Lanjut ke Materi Berikutnya: <?= htmlspecialchars($nextLesson['title']) ?> →
                    </a>
                <?php else: ?>
                    <div class="alert alert-success">
                        <p class="mb-2">🎉 <strong>Selamat!</strong> Anda telah menyelesaikan semua materi di kursus ini.</p>
                        <a href="index.php?kursus=<?= htmlspecialchars($lesson['course_slug']) ?>" class="btn btn-sm btn-outline-primary">
                            ← Kembali ke Detail Kursus
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($hasQuiz): ?>
                    <div class="alert alert-info">
                        Untuk lanjut ke materi berikutnya, silakan selesaikan dan lulus kuis materi ini terlebih dahulu.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

<script>
    (function() {
        var list = document.getElementById('lesson-points');
        var btnNext = document.getElementById('btn-next-point');
        var hint = document.getElementById('lesson-hint');
        var btnGoQuiz = document.getElementById('btn-go-quiz');

        // Jika tidak ada list poin materi, tidak perlu lakukan apa-apa
        if (!list || !btnNext) {
            // No points to read -> immediately mark as read and enable quiz button (if present)
            if (btnGoQuiz) {
                markLessonRead();
            }
            return;
        }

        var points = Array.prototype.slice.call(list.querySelectorAll('.lesson-point'));
        if (!points.length) {
            return;
        }

        var currentIndex = 0;
        var lastShownAt = Date.now();

        function updateHint(msg) {
            if (hint) {
                hint.textContent = msg;
            }
        }

        btnNext.addEventListener('click', function() {
            var currentPoint = points[currentIndex];
            var requiredSeconds = parseInt(currentPoint.getAttribute('data-required-seconds') || '0', 10);
            var requiredMs = requiredSeconds * 1000;
            var elapsed = Date.now() - lastShownAt;

            if (elapsed < requiredMs) {
                var remaining = Math.ceil((requiredMs - elapsed) / 1000);
                updateHint('Silakan baca dulu, sekitar ' + remaining +
                    ' detik lagi baru bisa lanjut ke poin berikutnya.');
                return;
            }

            var lastIndex = points.length - 1;

            if (currentIndex < lastIndex - 1) {
                // Masih ada minimal 2 poin lagi
                currentIndex++;
                points[currentIndex].style.display = 'list-item';
                lastShownAt = Date.now();
                updateHint('Tekan tombol Lanjut lagi setelah membaca poin ini.');
            } else if (currentIndex === lastIndex - 1) {
                // Klik ini akan menampilkan poin terakhir
                currentIndex++;
                points[currentIndex].style.display = 'list-item';
                lastShownAt = Date.now();
                btnNext.disabled = true;

                // Semua poin sudah tampil
                updateHint('Semua poin materi sudah ditampilkan. Silakan klik "Kerjakan Soal" untuk melanjutkan.');
                // Mark lesson read and enable button
                if (btnGoQuiz) {
                    markLessonRead();
                }
            } else {
                // Sudah di poin terakhir sejak awal
                btnNext.disabled = true;
                updateHint('Semua poin materi sudah ditampilkan. Silakan klik "Kerjakan Soal" untuk melanjutkan.');
            }
        });
    })();

    // MARK LESSON AS READ FUNCTION
    function markLessonRead() {
        // Prevent multiple calls
        if (typeof window._lessonReadMarked !== 'undefined' && window._lessonReadMarked) return;
        window._lessonReadMarked = true;

        var lessonId = <?= (int)$lesson['id'] ?>;
        var formData = new FormData();
        formData.append('lesson_id', lessonId);

        fetch('mark_lesson_read.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(d) {
            if (d && d.status === 'success') {
                var btnGoQuiz = document.getElementById('btn-go-quiz');
                if (btnGoQuiz) {
                    btnGoQuiz.disabled = false;
                    btnGoQuiz.removeAttribute('title');
                    // convert to clickable behavior
                    btnGoQuiz.addEventListener('click', function() {
                        var url = this.getAttribute('data-quiz-url');
                        if (url) window.location.href = url;
                    });
                }
            } else {
                console.warn('mark_lesson_read failed', d);
            }
        })
        .catch(function(err){
            console.error('mark_lesson_read error', err);
        });
    }

    // Jika halaman sudah menandai read pada server (hasRead), enable tombol dan pasang click
    <?php if ($hasRead || $hasPassedLesson): ?>
    (function() {
        var btn = document.getElementById('btn-go-quiz');
        if (btn) {
            btn.disabled = false;
            btn.addEventListener('click', function() {
                var url = this.getAttribute('data-quiz-url');
                if (url) window.location.href = url;
            });
        }
    })();
    <?php endif; ?>
</script>