<?php
require_once 'config.php';

// Debug: Cek materi dengan ID 38 dan cari next lesson-nya
$lessonId = 38;
$courseSlug = 'ushul-fiqh-dasar';

// 1. Ambil data lesson 38
$sql = "
    SELECT 
        l.*,
        c.title AS course_title,
        c.slug  AS course_slug,
        c.id AS course_id,
        m.title AS module_title,
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

echo "=== DEBUG LESSON 38 ===\n\n";

if (!$lesson) {
    echo "Lesson 38 tidak ditemukan!\n";
    exit;
}

echo "Lesson Found:\n";
echo "- ID: " . $lesson['id'] . "\n";
echo "- Title: " . $lesson['title'] . "\n";
echo "- Module Order: " . $lesson['module_order'] . "\n";
echo "- Lesson Order: " . $lesson['lesson_order'] . "\n";
echo "- Course ID: " . $lesson['course_id'] . "\n";
echo "\n";

// 2. Cek query next lesson
$sqlNext = "
    SELECT l.id, l.title, l.lesson_order, m.module_order
    FROM lessons l
    JOIN course_modules m ON l.module_id = m.id
    WHERE l.course_id = ?
      AND (
        (m.module_order = ? AND l.lesson_order > ?) -- Materi selanjutnya di Bab yang sama
        OR 
        (m.module_order > ?) -- Materi pertama di Bab selanjutnya
      )
    ORDER BY m.module_order ASC, l.lesson_order ASC
    LIMIT 1
";

$stmtNext = $pdo->prepare($sqlNext);
$stmtNext->execute([
    $lesson['course_id'],       // Parameter 1: ID Kursus
    $lesson['module_order'],    // Parameter 2: Bab saat ini
    $lesson['lesson_order'],    // Parameter 3: Materi saat ini
    $lesson['module_order']     // Parameter 4: Bab saat ini (untuk cari bab > ini)
]);
$nextLesson = $stmtNext->fetch();

echo "Next Lesson Query Result:\n";
if ($nextLesson) {
    echo "- Found Next Lesson!\n";
    echo "  ID: " . $nextLesson['id'] . "\n";
    echo "  Title: " . $nextLesson['title'] . "\n";
    echo "  Module Order: " . $nextLesson['module_order'] . "\n";
    echo "  Lesson Order: " . $nextLesson['lesson_order'] . "\n";
} else {
    echo "- NO NEXT LESSON FOUND!\n";
}

echo "\n";

// 3. Cek semua materi di course ini
echo "=== ALL LESSONS IN COURSE ===\n";
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
    $marker = ($row['id'] == 38) ? " <-- CURRENT" : "";
    echo "- Bab {$row['module_order']} | Materi {$row['lesson_order']} | ID {$row['id']} | {$row['title']}{$marker}\n";
}

?>
