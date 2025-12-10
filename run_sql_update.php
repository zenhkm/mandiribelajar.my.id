<?php
require_once "config.php";

echo "<h1>Database Update Tool</h1>";

try {
    // 1. Add columns to courses table
    // Check if column exists first to avoid error
    $check = $pdo->query("SHOW COLUMNS FROM courses LIKE 'exam_question_limit'");
    if ($check->rowCount() == 0) {
        $sql = "
            ALTER TABLE courses
            ADD COLUMN exam_question_limit INT DEFAULT NULL,
            ADD COLUMN exam_passing_grade INT DEFAULT 80,
            ADD COLUMN exam_show_score TINYINT(1) DEFAULT 1;
        ";
        $pdo->exec($sql);
        echo "<p style='color:green'>Added columns to courses table.</p>";
    } else {
        echo "<p style='color:blue'>Columns already exist in courses table.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Error adding columns: " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    // 2. Create course_exam_attempts table
    $sql = "
        CREATE TABLE IF NOT EXISTS course_exam_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            course_id INT NOT NULL,
            score INT NOT NULL,
            passed TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (user_id, course_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sql);
    echo "<p style='color:green'>Created course_exam_attempts table.</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Error creating table: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p>Done. <a href='index.php'>Back to Home</a></p>";
?>