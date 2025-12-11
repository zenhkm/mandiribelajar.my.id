<?php
require_once "auth.php";
check_login();

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

// 4. Proses Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_exam'])) {
    $answers = $_POST['answers'] ?? [];
    $questionIds = $_POST['q_ids'] ?? []; 
    
    if (empty($questionIds)) {
        echo "Error: Tidak ada soal yang dikerjakan.";
        return;
    }

    $correctCount = 0;
    $totalQuestions = count($questionIds);

    $inQuery = implode(',', array_fill(0, count($questionIds), '?'));
    $stmtKey = $pdo->prepare("
        SELECT q.id, o.id as correct_option_id
        FROM lesson_questions q
        JOIN lesson_options o ON o.question_id = q.id
        WHERE q.id IN ($inQuery) AND o.is_correct = 1
    ");
    $stmtKey->execute($questionIds);
    $keys = $stmtKey->fetchAll(PDO::FETCH_KEY_PAIR); 

    foreach ($questionIds as $qid) {
        $userAns = isset($answers[$qid]) ? (int)$answers[$qid] : 0;
        $correctAns = isset($keys[$qid]) ? (int)$keys[$qid] : 0;
        
        if ($userAns && $userAns === $correctAns) {
            $correctCount++;
        }
    }

    $score = ($totalQuestions > 0) ? round(($correctCount / $totalQuestions) * 100) : 0;
    $passingGrade = $course['exam_passing_grade'] ?? 80;
    $passed = ($score >= $passingGrade) ? 1 : 0;

    $stmtSave = $pdo->prepare("
        INSERT INTO course_exam_attempts (user_id, course_id, score, passed)
        VALUES (?, ?, ?, ?)
    ");
    $stmtSave->execute([$userId, $courseId, $score, $passed]);

    $resultId = $pdo->lastInsertId();
    echo "<script>window.location.href='index.php?page=exam_result&id=$resultId';</script>";
    exit;
}

// 5. Persiapan Soal
$limit = 20; 
$totalAvailable = $pdo->query("SELECT COUNT(*) FROM lesson_questions q JOIN lessons l ON q.lesson_id = l.id WHERE l.course_id = $courseId")->fetchColumn();

if (!empty($course['exam_question_limit']) && $course['exam_question_limit'] > 0) {
    $limit = (int)$course['exam_question_limit'];
} else {
    if ($totalAvailable < 50) {
        $limit = 20; 
    } elseif ($totalAvailable < 100) {
        $limit = 50;
    } else {
        $limit = 100;
    }
}

$stmtQ = $pdo->prepare("
    SELECT q.id, q.question_text
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

$qIds = array_column($questions, 'id');
$inQuery = implode(',', array_fill(0, count($qIds), '?'));
$stmtOpt = $pdo->prepare("
    SELECT * FROM lesson_options 
    WHERE question_id IN ($inQuery)
    ORDER BY RAND()
");
$stmtOpt->execute($qIds);
$allOptions = $stmtOpt->fetchAll();

$optionsByQ = [];
foreach ($allOptions as $opt) {
    $optionsByQ[$opt['question_id']][] = $opt;
}
?>

<style>
    .question-nav-btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        background: #fff;
        color: #333;
    }
    .question-nav-btn:hover {
        background: #f8f9fa;
    }
    .question-nav-btn.active {
        border-color: #0d6efd;
        background-color: #e7f1ff;
        color: #0d6efd;
    }
    .question-nav-btn.answered {
        background-color: #198754;
        color: white;
        border-color: #198754;
    }
    .question-nav-btn.active.answered {
        background-color: #157347; /* Darker green for active answered */
        border-color: #0d6efd;
        border-width: 2px;
    }
    
    /* Dark Mode Support */
    [data-bs-theme="dark"] .question-nav-btn {
        background: #2c2c2c;
        border-color: #444;
        color: #e0e0e0;
    }
    [data-bs-theme="dark"] .question-nav-btn:hover {
        background: #333;
    }
    [data-bs-theme="dark"] .question-nav-btn.active {
        background-color: #1a2744;
        border-color: #6ea8fe;
        color: #6ea8fe;
    }
    [data-bs-theme="dark"] .question-nav-btn.answered {
        background-color: #198754;
        color: white;
    }
</style>

<div class="container-fluid my-4 px-md-4">
    <form method="post" id="examForm">
        <div class="row g-4">
            <!-- Kolom Kiri: Soal -->
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Uji Komprehensif</h5>
                        <span class="badge bg-primary rounded-pill">
                            Soal <span id="currentNum">1</span> dari <?= count($questions) ?>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <?php foreach ($questions as $index => $q): ?>
                            <div class="question-block" id="q-block-<?= $index ?>" style="<?= $index === 0 ? '' : 'display:none;' ?>">
                                <p class="fw-bold mb-4" style="font-size: 1.1rem;">
                                    <?= nl2br(htmlspecialchars($q['question_text'])) ?>
                                </p>
                                
                                <input type="hidden" name="q_ids[]" value="<?= $q['id'] ?>">

                                <div class="list-group">
                                    <?php 
                                    $opts = isset($optionsByQ[$q['id']]) ? $optionsByQ[$q['id']] : [];
                                    foreach ($opts as $opt): 
                                    ?>
                                        <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3 mb-2 border rounded" style="cursor: pointer;">
                                            <input class="form-check-input flex-shrink-0 mt-0" type="radio" 
                                                   name="answers[<?= $q['id'] ?>]" 
                                                   value="<?= $opt['id'] ?>" 
                                                   onchange="markAnswered(<?= $index ?>)"
                                                   style="width: 1.2em; height: 1.2em;">
                                            <span class="fs-6"><?= htmlspecialchars($opt['option_text']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer bg-white py-3">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary px-4" id="btnPrev" onclick="changeQuestion(-1)" disabled>
                                &laquo; Sebelumnya
                            </button>
                            
                            <button type="button" class="btn btn-primary px-4" id="btnNext" onclick="changeQuestion(1)">
                                Selanjutnya &raquo;
                            </button>
                            
                            <button type="submit" name="submit_exam" class="btn btn-success px-4" id="btnFinish" style="display:none;" onclick="return confirm('Yakin ingin mengumpulkan jawaban?');">
                                Selesai & Kumpulkan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Navigasi Soal -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold py-3">
                        Daftar Soal
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <?php foreach ($questions as $index => $q): ?>
                                <div class="question-nav-btn <?= $index === 0 ? 'active' : '' ?>" 
                                     id="nav-btn-<?= $index ?>"
                                     onclick="jumpToQuestion(<?= $index ?>)">
                                    <?= $index + 1 ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-4 small text-muted">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div style="width:15px; height:15px; background:#198754; border-radius:4px;"></div> Sudah Dijawab
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:15px; height:15px; border:1px solid #dee2e6; border-radius:4px;"></div> Belum Dijawab
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    let currentIndex = 0;
    const totalQuestions = <?= count($questions) ?>;

    function showQuestion(index) {
        // Hide all questions
        document.querySelectorAll('.question-block').forEach(el => el.style.display = 'none');
        
        // Show target question
        document.getElementById('q-block-' + index).style.display = 'block';
        
        // Update Nav Buttons
        document.querySelectorAll('.question-nav-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('nav-btn-' + index).classList.add('active');
        
        // Update Header Number
        document.getElementById('currentNum').innerText = index + 1;

        // Handle Prev/Next Buttons
        document.getElementById('btnPrev').disabled = (index === 0);
        
        if (index === totalQuestions - 1) {
            document.getElementById('btnNext').style.display = 'none';
            document.getElementById('btnFinish').style.display = 'inline-block';
        } else {
            document.getElementById('btnNext').style.display = 'inline-block';
            document.getElementById('btnFinish').style.display = 'none';
        }

        currentIndex = index;
        
        // Scroll to top of card on mobile
        if (window.innerWidth < 992) {
            document.querySelector('.card').scrollIntoView({behavior: 'smooth'});
        }
    }

    function changeQuestion(step) {
        const newIndex = currentIndex + step;
        if (newIndex >= 0 && newIndex < totalQuestions) {
            showQuestion(newIndex);
        }
    }

    function jumpToQuestion(index) {
        showQuestion(index);
    }

    function markAnswered(index) {
        document.getElementById('nav-btn-' + index).classList.add('answered');
    }
</script>
