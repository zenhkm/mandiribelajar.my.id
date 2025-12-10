<?php
$dsn  = 'mysql:host=localhost;dbname=quic1934_kursus;charset=utf8mb4';
$user = 'root'; // Assuming local dev environment
$pass = ''; // Assuming local dev environment

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (\PDOException $e) {
    // Fallback to config.php credentials if root fails
    require_once "config.php";
}

try {
    // 1. Add columns to courses table
    $sql = "
        ALTER TABLE courses
        ADD COLUMN exam_question_limit INT DEFAULT NULL,
        ADD COLUMN exam_passing_grade INT DEFAULT 80,
        ADD COLUMN exam_show_score TINYINT(1) DEFAULT 1;
    ";
    $pdo->exec($sql);
    echo "Added columns to courses table.\n";
} catch (PDOException $e) {
    echo "Columns might already exist or error: " . $e->getMessage() . "\n";
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
    echo "Created course_exam_attempts table.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>