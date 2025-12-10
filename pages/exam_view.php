<?php
require_once "auth.php";
check_login();

// DEBUG: Aktifkan error reporting sementara
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Validasi Input
$courseSlug = isset($_GET['kursus']) ? $_GET['kursus'] : '';
if (!$courseSlug) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Kursus tidak ditemukan.</div></div>";
    return;
}

// 2. Ambil Data Kursus
$stmt = $pdo->prepare("SELECT * FROM courses WHERE slug = ?");
$stmt->execute([$courseSlug]);
$course = $stmt->fetch();

if (!$course) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Kursus tidak ditemukan.</div></div>";
    return;
}

$courseId = $course['id'];
$userId   = $_SESSION['user']['id'];

// 3. Cek Apakah User Sudah Menyelesaikan Semua Materi
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
$stmtTotal->execute([$courseId]);
$totalLessons = $stmtTotal->fetchColumn();

$stmtPassed = $pdo->prepare("
    SELECT COUNT(DISTINCT lesson_id) 
    FROM lesson_progress lp
    JOIN lessons l ON lp.lesson_id = l.id
    WHERE lp.user_id = ? AND l.course_id = ? AND lp.has_passed = 1
");
$stmtPassed->execute([$userId, $courseId]);
$passedLessons = $stmtPassed->fetchColumn();

if ($passedLessons < $totalLessons && $totalLessons > 0) {
    echo "<div class='container my-5'><div class='alert alert-warning'>Anda belum menyelesaikan semua materi. Silakan selesaikan materi terlebih dahulu.</div></div>";
    return;
}

// 4. Cek Apakah User Sedang Mengerjakan Ujian (Session)
// Jika ada POST submit, proses penilaian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_exam'])) {
    // Ambil jawaban user
    $answers = $_POST['answers'] ?? [];
    $questionIds = $_POST['q_ids'] ?? []; // ID soal yang dikerjakan (untuk validasi)
    
    if (empty($questionIds)) {
        echo "Error: Tidak ada soal yang dikerjakan.";
        return;
    }

    // Hitung Score
    $correctCount = 0;
    $totalQuestions = count($questionIds);

    // Ambil kunci jawaban dari DB untuk soal-soal ini
    // Optimasi: Ambil semua kunci jawaban sekaligus
    $inQuery = implode(',', array_fill(0, count($questionIds), '?'));
    $stmtKey = $pdo->prepare("
        SELECT q.id, o.id as correct_option_id
        FROM lesson_questions q
        JOIN lesson_options o ON o.question_id = q.id
        WHERE q.id IN ($inQuery) AND o.is_correct = 1
    ");
    $stmtKey->execute($questionIds);
    $keys = $stmtKey->fetchAll(PDO::FETCH_KEY_PAIR); // [question_id => correct_option_id]

    foreach ($questionIds as $qid) {
        $userAns = isset($answers[$qid]) ? (int)$answers[$qid] : 0;
        $correctAns = isset($keys[$qid]) ? (int)$keys[$qid] : 0;
        
        if ($userAns && $userAns === $correctAns) {
            $correctCount++;
        }
    }

    $score = ($totalQuestions > 0) ? round(($correctCount / $totalQuestions) * 100) : 0;
    
    // Cek Passing Grade
    $passingGrade = $course['exam_passing_grade'] ?? 80;
    $passed = ($score >= $passingGrade) ? 1 : 0;

    // Simpan Hasil
    $stmtSave = $pdo->prepare("
        INSERT INTO course_exam_attempts (user_id, course_id, score, passed)
        VALUES (?, ?, ?, ?)
    ");
    $stmtSave->execute([$userId, $courseId, $score, $passed]);

    // Redirect ke Halaman Hasil (Self Redirect dengan parameter result)
    // Gunakan session flash message atau parameter URL
    // Kita pakai parameter URL simple
    $resultId = $pdo->lastInsertId();
    echo "<script>window.location.href='index.php?page=exam_result&id=$resultId';</script>";
    exit;
}

// 5. Persiapan Soal (Jika belum submit)
// Aturan Jumlah Soal
$limit = 20; // Default fallback
$totalAvailable = $pdo->query("SELECT COUNT(*) FROM lesson_questions q JOIN lessons l ON q.lesson_id = l.id WHERE l.course_id = $courseId")->fetchColumn();

if (!empty($course['exam_question_limit']) && $course['exam_question_limit'] > 0) {
    $limit = (int)$course['exam_question_limit'];
} else {
    // Aturan Default
    if ($totalAvailable < 50) {
        $limit = 20; // Atau $totalAvailable jika kurang dari 20 (handled by SQL LIMIT)
    } elseif ($totalAvailable < 100) {
        $limit = 50;
    } else {
        $limit = 100;
    }
}

// Ambil Soal secara Acak
$stmtQ = $pdo->prepare("
    SELECT q.id, q.question_text, q.question_image
    FROM lesson_questions q
    JOIN lessons l ON q.lesson_id = l.id
    WHERE l.course_id = ?
    ORDER BY RAND()
    LIMIT $limit
");
$stmtQ->execute([$courseId]);
$questions = $stmtQ->fetchAll();

if (empty($questions)) {
    echo "<div class='container my-5'><div class='alert alert-info'>Belum ada bank soal untuk kursus ini.</div></div>";
    return;
}

// Ambil Opsi Jawaban untuk soal-soal terpilih
$qIds = array_column($questions, 'id');
$inQuery = implode(',', array_fill(0, count($qIds), '?'));
$stmtOpt = $pdo->prepare("
    SELECT * FROM lesson_options 
    WHERE question_id IN ($inQuery)
    ORDER BY RAND()
");
$stmtOpt->execute($qIds);
$allOptions = $stmtOpt->fetchAll();

// Group options by question_id
$optionsByQ = [];
foreach ($allOptions as $opt) {
    $optionsByQ[$opt['question_id']][] = $opt;
}
?>

<div class="container my-5" style="max-width: 800px;">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h4 class="mb-0">Uji Komprehensif: <?= htmlspecialchars($course['title']) ?></h4>
            <p class="text-muted mb-0 small">Jawablah semua pertanyaan dengan benar.</p>
        </div>
        <div class="card-body">
            <form method="post" id="examForm">
                <?php foreach ($questions as $index => $q): ?>
                    <div class="mb-4">
                        <p class="fw-bold mb-2"><?= ($index + 1) ?>. <?= nl2br(htmlspecialchars($q['question_text'])) ?></p>
                        
                        <?php if (!empty($q['question_image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($q['question_image']) ?>" class="img-fluid mb-3 rounded" style="max-height: 200px;">
                        <?php endif; ?>

                        <input type="hidden" name="q_ids[]" value="<?= $q['id'] ?>">

                        <div class="list-group">
                            <?php 
                            $opts = isset($optionsByQ[$q['id']]) ? $optionsByQ[$q['id']] : [];
                            foreach ($opts as $opt): 
                            ?>
                                <label class="list-group-item list-group-item-action d-flex align-items-center gap-2" style="cursor: pointer;">
                                    <input class="form-check-input flex-shrink-0" type="radio" 
                                           name="answers[<?= $q['id'] ?>]" 
                                           value="<?= $opt['id'] ?>" required>
                                    <span><?= htmlspecialchars($opt['option_text']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="d-grid gap-2 mt-5">
                    <button type="submit" name="submit_exam" class="btn btn-primary btn-lg" onclick="return confirm('Yakin ingin mengumpulkan jawaban?');">
                        Selesai & Kumpulkan Jawaban
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
