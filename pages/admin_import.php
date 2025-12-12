<?php
require_once 'auth.php';
check_admin();

$message = '';
$error = '';

// Handle Template Download
if (isset($_GET['download_template'])) {
    $type = $_GET['download_template'];
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="template_' . $type . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    if ($type === 'lessons') {
        fputcsv($output, ['course_id', 'module_id', 'lesson_order', 'title', 'content_type', 'content_text', 'video_url']);
        fputcsv($output, ['1', '1', '1', 'Judul Materi', 'text', 'Isi materi disini...', '']);
    } elseif ($type === 'questions') {
        fputcsv($output, ['lesson_id', 'question_text', 'explanation', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_answer']);
        fputcsv($output, ['1', 'Pertanyaan contoh?', 'Penjelasan jawaban', 'Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D', 'Pilihan E', 'A']);
    }
    
    fclose($output);
    exit;
}

// Handle File Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    $importType = $_POST['import_type'];
    $file = $_FILES['import_file']['tmp_name'];
    
    if (empty($file)) {
        $error = 'Silakan pilih file CSV.';
    } else {
        $handle = fopen($file, "r");
        if ($handle !== FALSE) {
            $row = 0;
            $successCount = 0;
            $pdo->beginTransaction();
            
            try {
                // Skip header row
                fgetcsv($handle);
                
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;
                    
                    if ($importType === 'lessons') {
                        // Expected: course_id, module_id, lesson_order, title, content_type, content_text, video_url
                        if (count($data) < 4) continue; // Basic validation
                        
                        $stmt = $pdo->prepare("INSERT INTO lessons (course_id, module_id, lesson_order, title, content_type, content_text, video_url, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                        $stmt->execute([
                            $data[0], // course_id
                            $data[1], // module_id
                            $data[2], // lesson_order
                            $data[3], // title
                            $data[4] ?? 'text', // content_type
                            $data[5] ?? '', // content_text
                            $data[6] ?? ''  // video_url
                        ]);
                        $successCount++;
                        
                    } elseif ($importType === 'questions') {
                        // Expected: lesson_id, question_text, explanation, option_a, option_b, option_c, option_d, option_e, correct_answer
                        if (count($data) < 9) continue;
                        
                        // Insert Question
                        $stmtQ = $pdo->prepare("INSERT INTO lesson_questions (lesson_id, question_text, explanation) VALUES (?, ?, ?)");
                        $stmtQ->execute([
                            $data[0], // lesson_id
                            $data[1], // question_text
                            $data[2]  // explanation
                        ]);
                        $questionId = $pdo->lastInsertId();
                        
                        // Insert Options
                        $options = [
                            'A' => $data[3],
                            'B' => $data[4],
                            'C' => $data[5],
                            'D' => $data[6],
                            'E' => $data[7]
                        ];
                        $correctAnswer = strtoupper(trim($data[8]));
                        
                        $stmtOpt = $pdo->prepare("INSERT INTO lesson_options (question_id, option_label, option_text, is_correct) VALUES (?, ?, ?, ?)");
                        
                        foreach ($options as $label => $text) {
                            if (!empty($text)) {
                                $isCorrect = ($label === $correctAnswer) ? 1 : 0;
                                $stmtOpt->execute([$questionId, $label, $text, $isCorrect]);
                            }
                        }
                        $successCount++;
                    }
                }
                
                $pdo->commit();
                $message = "Berhasil mengimport $successCount data.";
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Gagal import: " . $e->getMessage();
            }
            
            fclose($handle);
        } else {
            $error = "Gagal membuka file.";
        }
    }
}
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Import Data (CSV)</h2>
        <a href="index.php?page=admin" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Import Materi (Lessons)</h5>
                </div>
                <div class="card-body">
                    <p>Gunakan fitur ini untuk mengupload banyak materi sekaligus.</p>
                    <div class="mb-3">
                        <a href="index.php?page=admin_import&download_template=lessons" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-download"></i> Download Template CSV
                        </a>
                    </div>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="import_type" value="lessons">
                        <div class="mb-3">
                            <label class="form-label">File CSV</label>
                            <input type="file" name="import_file" class="form-control" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Materi</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Import Soal (Questions)</h5>
                </div>
                <div class="card-body">
                    <p>Gunakan fitur ini untuk mengupload banyak soal sekaligus beserta pilihannya.</p>
                    <div class="mb-3">
                        <a href="index.php?page=admin_import&download_template=questions" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-download"></i> Download Template CSV
                        </a>
                    </div>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="import_type" value="questions">
                        <div class="mb-3">
                            <label class="form-label">File CSV</label>
                            <input type="file" name="import_file" class="form-control" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn btn-success">Upload Soal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info mt-4">
        <h5>Panduan Import:</h5>
        <ul>
            <li>Pastikan format file adalah <strong>.csv</strong> (Comma Separated Values).</li>
            <li>Gunakan template yang disediakan untuk menghindari kesalahan kolom.</li>
            <li>Untuk <strong>Materi</strong>, pastikan ID Kursus dan ID Modul sudah ada di database.</li>
            <li>Untuk <strong>Soal</strong>, pastikan ID Materi (Lesson ID) sudah ada.</li>
            <li>Kolom <strong>correct_answer</strong> pada soal harus diisi dengan huruf (A, B, C, D, atau E).</li>
        </ul>
    </div>
</div>
