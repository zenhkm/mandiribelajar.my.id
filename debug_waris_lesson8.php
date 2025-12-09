<?php
require_once 'config.php';

// Debug: Cek lesson 8 untuk kursus 'waris'
$lessonId = 8;
$courseSlug = 'waris';

// 1. Ambil data lesson 8
$sql = "
    SELECT 
        l.*,
        c.title AS course_title,
        c.slug  AS course_slug,
        c.id AS course_id,
        m.title AS module_title,
        m.id AS module_id,
        m.module_order
    FROM lessons l
    JOIN courses c ON l.course_id = c.id
    JOIN course_modules m ON l.module_id = m.id
    WHERE l.id = ? AND c.slug = ?
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$lessonId, $courseSlug]);
$lesson = $stmt->fetch();

echo "=== DEBUG LESSON 8 (KURSUS WARIS) ===\n\n";

if (!$lesson) {
    echo "❌ Lesson 8 TIDAK DITEMUKAN!\n";
    exit;
}

echo "✅ Lesson Found:\n";
echo "- ID: " . $lesson['id'] . "\n";
echo "- Title: " . $lesson['title'] . "\n";
echo "- Module Order: " . $lesson['module_order'] . "\n";
echo "- Lesson Order: " . $lesson['lesson_order'] . "\n";
echo "- Course ID: " . $lesson['course_id'] . "\n";
echo "- Course: " . $lesson['course_slug'] . "\n";
echo "\n";

// 2. Cek apakah ada soal untuk lesson ini
$sqlQ = "SELECT COUNT(*) as count FROM lesson_questions WHERE lesson_id = ?";
$stmtQ = $pdo->prepare($sqlQ);
$stmtQ->execute([$lesson['id']]);
$qCount = $stmtQ->fetch();
echo "Jumlah soal: " . $qCount['count'] . "\n\n";

// 3. Cek query next lesson
echo "=== CHECKING NEXT LESSON QUERY ===\n";
$sqlNext = "
    SELECT l.id, l.title, l.lesson_order, m.module_order
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
$stmtNext->execute([
    $lesson['course_id'],
    $lesson['module_order'],
    $lesson['lesson_order'],
    $lesson['module_order']
]);
$nextLesson = $stmtNext->fetch();

if ($nextLesson) {
    echo "✅ NEXT LESSON DITEMUKAN:\n";
    echo "- ID: " . $nextLesson['id'] . "\n";
    echo "- Title: " . $nextLesson['title'] . "\n";
    echo "- Module Order: " . $nextLesson['module_order'] . "\n";
    echo "- Lesson Order: " . $nextLesson['lesson_order'] . "\n";
} else {
    echo "❌ NEXT LESSON TIDAK DITEMUKAN!\n";
}

echo "\n";

// 4. Cek semua materi di course ini
echo "=== ALL LESSONS IN COURSE WARIS ===\n";
$sqlAll = "
    SELECT l.id, l.lesson_order, l.title, m.module_order, m.title as module_title
    FROM lessons l
    JOIN course_modules m ON l.module_id = m.id
    WHERE l.course_id = ?
    ORDER BY m.module_order ASC, l.lesson_order ASC
";
$stmtAll = $pdo->prepare($sqlAll);
$stmtAll->execute([$lesson['course_id']]);
$allLessons = $stmtAll->fetchAll();

foreach ($allLessons as $row) {
    $marker = ($row['id'] == 8) ? " <-- CURRENT (lesson 8)" : "";
    echo "- Bab {$row['module_order']} | Materi {$row['lesson_order']} | ID {$row['id']} | {$row['title']}{$marker}\n";
}

echo "\n";

// 5. Cek progress pengguna untuk lesson 8
echo "=== USER PROGRESS CHECK ===\n";
$userId = 1; // Ganti dengan user_id yang sesuai jika perlu
$sqlProg = "SELECT * FROM lesson_progress WHERE lesson_id = ? LIMIT 5";
$stmtProg = $pdo->prepare($sqlProg);
$stmtProg->execute([$lesson['id']]);
$progRows = $stmtProg->fetchAll();

if (count($progRows) > 0) {
    echo "Progress records untuk lesson 8:\n";
    foreach ($progRows as $p) {
        echo "- User {$p['user_id']}: has_passed=" . $p['has_passed'] . ", completed_at=" . $p['completed_at'] . "\n";
    }
} else {
    echo "Belum ada progress record untuk lesson 8\n";
}

?>
