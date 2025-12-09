<?php
/**
 * save_quiz_answers.php
 * Handler untuk menyimpan jawaban soal ke session via AJAX
 * Tujuan: Agar jawaban tetap tersimpan ketika user kembali dari materi
 */

require_once "auth.php";
check_login();

header('Content-Type: application/json');

try {
    $lessonId = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;
    $questionId = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
    $answer = isset($_POST['answer']) ? trim($_POST['answer']) : '';
    
    if (!$lessonId || !$questionId) {
        throw new Exception('Parameter tidak lengkap');
    }
    
    // Inisialisasi session storage untuk quiz answers jika belum ada
    if (!isset($_SESSION['quiz_answers'])) {
        $_SESSION['quiz_answers'] = array();
    }
    
    if (!isset($_SESSION['quiz_answers'][$lessonId])) {
        $_SESSION['quiz_answers'][$lessonId] = array();
    }
    
    // Simpan jawaban
    $_SESSION['quiz_answers'][$lessonId][$questionId] = $answer;
    
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Jawaban tersimpan'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
