<?php
// Test simple untuk cek lesson 38
require_once 'config.php';

$lessonId = 38;
$sql = "SELECT id, title, lesson_order, module_id FROM lessons WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();

if ($lesson) {
    echo "Lesson 38 ditemukan:<br>";
    echo "- Title: " . $lesson['title'] . "<br>";
    echo "- Lesson Order: " . $lesson['lesson_order'] . "<br>";
    echo "- Module ID: " . $lesson['module_id'] . "<br>";
    
    // Cek bab
    $sql2 = "SELECT id, module_order, title FROM course_modules WHERE id = ?";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([$lesson['module_id']]);
    $module = $stmt2->fetch();
    
    if ($module) {
        echo "- Module Order: " . $module['module_order'] . "<br>";
        echo "- Module Title: " . $module['title'] . "<br>";
        
        // Cek apakah ada materi berikutnya
        $sql3 = "
            SELECT COUNT(*) as count
            FROM lessons
            WHERE module_id = ? AND lesson_order > ?
        ";
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->execute([$module['id'], $lesson['lesson_order']]);
        $result3 = $stmt3->fetch();
        echo "- Materi berikutnya di bab yang sama: " . $result3['count'] . "<br>";
    }
} else {
    echo "Lesson 38 tidak ditemukan";
}
?>
