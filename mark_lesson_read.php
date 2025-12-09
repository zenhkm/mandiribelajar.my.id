<?php
/**
 * mark_lesson_read.php
 * Endpoint to mark lesson as read (has_read = 1) for current user
 */

require_once "auth.php";
check_login();

header('Content-Type: application/json');

try {
    $lessonId = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;
    if (!$lessonId) throw new Exception('lesson_id diperlukan');

    $userId = $_SESSION['user']['id'] ?? 0;
    if (!$userId) throw new Exception('User tidak terautentikasi');

    require_once 'config.php';

    // Upsert: set has_read = 1 but do not overwrite has_passed (preserve existing value)
    $sql = "
        INSERT INTO lesson_progress (user_id, lesson_id, has_read, has_passed, attempts, last_score)
        VALUES (:user_id, :lesson_id, 1, 0, 0, 0)
        ON DUPLICATE KEY UPDATE
            has_read = 1,
            updated_at = CURRENT_TIMESTAMP
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $userId,
        ':lesson_id' => $lessonId,
    ]);

    echo json_encode(['status' => 'success', 'message' => 'marked read']);
    exit;
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
