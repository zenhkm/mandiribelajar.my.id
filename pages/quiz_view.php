<?php
/**
 * pages/quiz_view.php
 * Halaman untuk menampilkan dan mengerjakan soal sebagai halaman terpisah
 */

require_once "auth.php";
check_login();

// Debug
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

// Cek apakah user boleh mengakses soal ini
// Syarat: user harus sudah membaca/menyelesaikan materi ini
$userId = $_SESSION['user']['id'] ?? 0;
$canAccessQuiz = true;

// Cari lesson sebelumnya
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

    if (!$rowPrevProg || (int)$rowPrevProg['has_passed'] !== 1) {
        $canAccessQuiz = false;
    }
}

// Jika tidak boleh akses, tampilkan pesan
if (!$canAccessQuiz) {
?>
    <div class="container my-5">
        <div class="alert alert-warning">
            Untuk mengakses soal ini, Anda harus <strong>lulus</strong> materi sebelumnya terlebih dahulu.
        </div>
    </div>
<?php
    return;
}

// Ambil soal & opsi untuk lesson ini
$sqlQ = "
    SELECT q.*
    FROM lesson_questions q
    WHERE q.lesson_id = ?
    ORDER BY RAND()   
";
$stmtQ = $pdo->prepare($sqlQ);
$stmtQ->execute(array($lesson['id']));
$questions = $stmtQ->fetchAll();

// Ambil semua options per question
$optionsByQuestion = array();
if (!empty($questions)) {
    $questionIds = array();
    foreach ($questions as $q) {
        $questionIds[] = (int)$q['id'];
    }

    if (count($questionIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $sqlOpt = "
            SELECT * FROM lesson_options
            WHERE question_id IN ($placeholders)
            ORDER BY question_id ASC, option_label ASC
        ";
        $stmtOpt = $pdo->prepare($sqlOpt);
        $stmtOpt->execute($questionIds);
        $options = $stmtOpt->fetchAll();

        foreach ($options as $opt) {
            $qid = (int)$opt['question_id'];
            if (!isset($optionsByQuestion[$qid])) {
                $optionsByQuestion[$qid] = array();
            }
            $optionsByQuestion[$qid][] = $opt;
        }
    }
}

// ============================================================
// LOGIKA PENILAIAN SOAL
// ============================================================
$quizSubmitted   = ($_SERVER['REQUEST_METHOD'] === 'POST');
$quizResult      = null;
$selectedAnswers = array();

// Muat jawaban dari session jika ada
if (isset($_SESSION['quiz_answers'][$lesson['id']])) {
    $selectedAnswers = $_SESSION['quiz_answers'][$lesson['id']];
}

if ($quizSubmitted && !empty($questions)) {
    $totalQuestions = count($questions);
    $correctCount   = 0;
    $details        = array();

    foreach ($questions as $q) {
        $qid       = (int)$q['id'];
        $fieldName = 'q_' . $qid;
        $selected  = isset($_POST[$fieldName]) ? $_POST[$fieldName] : null;
        $selectedAnswers[$qid] = $selected;

        // Cari opsi yg benar
        if (isset($optionsByQuestion[$qid])) {
            $opts = $optionsByQuestion[$qid];
        } else {
            $opts = array();
        }

        $correctOption = null;
        foreach ($opts as $opt) {
            if ((int)$opt['is_correct'] === 1) {
                $correctOption = $opt;
                break;
            }
        }

        $isCorrect = false;
        if ($selected && $correctOption && $selected === $correctOption['option_label']) {
            $isCorrect = true;
            $correctCount++;
        }

        $details[$qid] = array(
            'is_correct'      => $isCorrect,
            'selected'        => $selected,
            'correct_option'  => $correctOption,
        );
    }

    $allCorrect = ($correctCount === $totalQuestions);

    $quizResult = array(
        'total'       => $totalQuestions,
        'correct'     => $correctCount,
        'all_correct' => $allCorrect,
        'details'     => $details,
    );

    // Simpan hasil ke database
    try {
        $stmtSaveQuiz = $pdo->prepare("
            INSERT INTO lesson_progress (user_id, lesson_id, has_read, has_passed, attempts, last_score)
            VALUES (:user_id, :lesson_id, 1, :passed, 
                    (SELECT COALESCE(attempts, 0) + 1 FROM lesson_progress 
                     WHERE user_id = :user_id AND lesson_id = :lesson_id), 
                    :score)
            ON DUPLICATE KEY UPDATE
                has_passed = :passed,
                attempts = attempts + 1,
                last_score = :score,
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmtSaveQuiz->execute([
            ':user_id'   => $userId,
            ':lesson_id' => $lesson['id'],
            ':passed'    => $allCorrect ? 1 : 0,
            ':score'     => ($correctCount / $totalQuestions) * 100,
        ]);
    } catch (Exception $e) {
        // Biarkan jika gagal simpan, sudah menampilkan result
    }
}

// Cek apakah user sudah pernah lulus
$hasPassedLesson = false;

// Auto-pass untuk lesson tanpa soal
if (empty($questions)) {
    $hasPassedLesson = true;
    
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
    // Cek ke database
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

// Cari next lesson
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
            <a href="index.php?kursus=<?= htmlspecialchars($lesson['course_slug']) ?>&lesson=<?= (int)$lesson['id'] ?>"
                class="text-decoration-none">
                <?= htmlspecialchars($lesson['module_title']) ?>
            </a>
            &nbsp;›&nbsp;
            <span class="text-muted">Kuis</span>
        </div>

        <div class="row gy-3">
            <div class="col-12 col-lg-8">
                <div class="hero-badge mb-2">
                    <span>Bab <?= (int)$lesson['module_order'] ?></span>
                    <span>•</span>
                    <span>Kuis</span>
                </div>
                <h1 class="h4 mb-2">
                    Kuis: <?= htmlspecialchars($lesson['title']) ?>
                </h1>

                <!-- Soal -->
                <div class="mt-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="h6 mb-1">Soal Materi Ini</h2>
                            <p class="small text-muted mb-0">
                                Jawablah soal berdasarkan materi yang telah Anda pelajari.
                                Untuk dinyatakan lulus, semua jawaban harus benar.
                            </p>
                        </div>
                        <a href="index.php?kursus=<?= htmlspecialchars($lesson['course_slug']) ?>&lesson=<?= (int)$lesson['id'] ?>" 
                           class="btn btn-outline-secondary btn-sm">
                            ← Kembali ke Materi
                        </a>
                    </div>

                    <?php if (empty($questions)): ?>
                        <div class="alert alert-warning small">
                            Belum ada soal untuk materi ini. Materi dianggap sudah selesai.
                        </div>
                    <?php else: ?>

                        <?php if ($quizSubmitted && $quizResult): ?>
                            <?php if ($quizResult['all_correct']): ?>
                                <div class="alert alert-success small">
                                    <p class="mb-2">
                                        Alhamdulillah, semua jawaban Anda <strong>benar</strong>
                                        (<?= $quizResult['correct'] ?>/<?= $quizResult['total'] ?>).
                                        Materi ini dinyatakan <strong>LULUS</strong>.
                                    </p>

                                    <?php if (!empty($nextLesson)): ?>
                                        <a href="index.php?kursus=<?= htmlspecialchars($lesson['course_slug']) ?>&lesson=<?= (int)$nextLesson['id'] ?>"
                                            class="btn btn-success btn-sm fw-bold mt-2">
                                            Lanjut ke materi berikutnya: <?= htmlspecialchars($nextLesson['title']) ?> →
                                        </a>
                                    <?php else: ?>
                                        <div class="alert alert-success small mt-2 mb-0">
                                            <p class="mb-2">🎉 <strong>Selamat!</strong> Anda telah menyelesaikan semua materi di kursus ini.</p>
                                            <a href="index.php?kursus=<?= htmlspecialchars($lesson['course_slug']) ?>" class="btn btn-sm btn-outline-primary">
                                                ← Kembali ke detail kursus
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger small">
                                    Jawaban Anda belum semuanya benar
                                    (<?= $quizResult['correct'] ?>/<?= $quizResult['total'] ?>).
                                    Silakan perhatikan pembahasan, lalu <strong>coba lagi</strong>.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <form method="post" action="">
                            <?php foreach ($questions as $idx => $q): ?>
                                <?php
                                $qid = (int)$q['id'];

                                $detail = null;
                                if (!empty($quizResult) && isset($quizResult['details'][$qid])) {
                                    $detail = $quizResult['details'][$qid];
                                }

                                if (isset($optionsByQuestion[$qid])) {
                                    $opts = $optionsByQuestion[$qid];
                                } else {
                                    $opts = array();
                                }
                                ?>
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body">
                                        <div class="fw-semibold mb-2">
                                            Soal <?= $idx + 1 ?>:
                                            <?= nl2br(htmlspecialchars($q['question_text'])) ?>
                                        </div>

                                        <?php
                                        if (!$quizSubmitted && !empty($opts)) {
                                            shuffle($opts);
                                        }
                                        $abcLabels = ['A', 'B', 'C', 'D', 'E'];
                                        ?>

                                        <?php foreach ($opts as $key => $opt): ?>
                                            <?php
                                            $visualLabel = isset($abcLabels[$key]) ? $abcLabels[$key] : '?';
                                            $fieldName = 'q_' . $qid;
                                            $idInput   = $fieldName . '_' . $opt['option_label'];

                                            $wasSelected = isset($selectedAnswers[$qid]) &&
                                                $selectedAnswers[$qid] === $opt['option_label'];

                                            $checkedAttr = $wasSelected ? 'checked' : '';
                                            ?>
                                            <div class="form-check small mb-1">
                                                <input class="form-check-input"
                                                    type="radio"
                                                    name="<?= htmlspecialchars($fieldName) ?>"
                                                    id="<?= htmlspecialchars($idInput) ?>"
                                                    value="<?= htmlspecialchars($opt['option_label']) ?>"
                                                    <?= $checkedAttr ?>>
                                                <label class="form-check-label" for="<?= htmlspecialchars($idInput) ?>">
                                                    <strong><?= $visualLabel ?>.</strong>
                                                    <?= nl2br(htmlspecialchars($opt['option_text'])) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>

                                        <?php if ($quizSubmitted && $detail): ?>
                                            <div class="mt-2 small">
                                                <?php if (!empty($detail['is_correct'])): ?>
                                                    <span class="text-success">✔ Jawaban Anda benar.</span>
                                                <?php else: ?>
                                                    <span class="text-danger">✘ Jawaban Anda belum tepat.</span><br>
                                                    <?php if (!empty($detail['correct_option'])): ?>
                                                        <span class="text-muted">
                                                            Jawaban yang benar:
                                                            <strong>
                                                                <?= htmlspecialchars($detail['correct_option']['option_label']) ?>.
                                                                <?= nl2br(htmlspecialchars($detail['correct_option']['option_text'])) ?>
                                                            </strong>
                                                        </span>
                                                    <?php endif; ?>

                                                    <?php if (!empty($q['explanation'])): ?>
                                                        <div class="text-muted mt-1">
                                                            <?= nl2br(htmlspecialchars($q['explanation'])) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <button class="btn btn-primary" type="submit" id="btn-submit-quiz">
                                Kirim Jawaban
                            </button>
                        </form>

                        <!-- Tombol kembali ke materi di bawah form -->
                        <div class="mt-2 mb-3">
                            <a href="index.php?kursus=<?= htmlspecialchars($lesson['course_slug']) ?>&lesson=<?= (int)$lesson['id'] ?>" 
                               class="btn btn-outline-secondary btn-sm">
                                ← Kembali ke Materi
                            </a>
                        </div>

                    <?php endif; ?>
                </div>
            </div>

            <!-- Panel samping -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="section-label mb-2">Status Kuis</div>

                        <?php if ($hasPassedLesson): ?>
                            <div class="alert alert-success d-flex align-items-center mb-0 p-2 small">
                                <span class="fs-4 me-2">🎉</span>
                                <div>
                                    <strong>Lulus!</strong><br>
                                    Anda sudah menyelesaikan kuis ini.
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning d-flex align-items-center mb-0 p-2 small">
                                <span class="fs-4 me-2">⏳</span>
                                <div>
                                    <strong>Belum Selesai</strong><br>
                                    Silakan kerjakan soal dengan benar.
                                </div>
                            </div>
                        <?php endif; ?>

                        <hr class="my-3 opacity-25">

                        <div class="small text-muted">
                            <strong>Ketentuan Lulus:</strong>
                            <ul class="mb-0 ps-3 mt-1">
                                <li>Jawab semua soal kuis dengan benar (100%).</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    (function() {
        var lessonId = <?= (int)$lesson['id'] ?>;
        var form = document.querySelector('form');
        if (!form) return;

        var radioButtons = form.querySelectorAll('input[type="radio"]');
        
        radioButtons.forEach(function(radio) {
            radio.addEventListener('change', function() {
                var fieldName = this.name;
                var questionId = fieldName.replace('q_', '');
                var answer = this.value;
                
                var formData = new FormData();
                formData.append('lesson_id', lessonId);
                formData.append('question_id', questionId);
                formData.append('answer', answer);
                
                fetch('save_quiz_answers.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    console.log('Jawaban tersimpan:', data);
                })
                .catch(function(error) {
                    console.error('Error menyimpan jawaban:', error);
                });
            });
        });
    })();
</script>
