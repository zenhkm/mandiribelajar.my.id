<?php
require_once 'auth.php';
check_admin();

// --- AJAX HANDLER FOR DROPDOWNS ---
if (isset($_GET['ajax_action'])) {
    // Bersihkan buffer agar tidak ada HTML header yang ikut
    if (ob_get_length()) ob_clean();

    header('Content-Type: application/json');
    $action = $_GET['ajax_action'];
    
    try {
        if ($action === 'get_modules') {
            $courseId = (int)$_GET['course_id'];
            $stmt = $pdo->prepare("SELECT id, title FROM course_modules WHERE course_id = ? ORDER BY module_order ASC");
            $stmt->execute([$courseId]);
            echo json_encode($stmt->fetchAll());
        } elseif ($action === 'get_lessons') {
            $moduleId = (int)$_GET['module_id'];
            $stmt = $pdo->prepare("SELECT id, title FROM lessons WHERE module_id = ? ORDER BY lesson_order ASC");
            $stmt->execute([$moduleId]);
            echo json_encode($stmt->fetchAll());
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

$message = '';
$error = '';

// Handle Template Download
if (isset($_GET['download_template'])) {
    // Bersihkan output buffer agar tidak ada HTML header yang ikut terdownload
    if (ob_get_length()) ob_clean();
    
    $type = $_GET['download_template'];
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="template_' . $type . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    if ($type === 'lessons') {
        // Header Bahasa Indonesia, Tanpa ID
        fputcsv($output, ['Judul Materi', 'Tipe Konten (text/video)', 'Isi Materi', 'URL Video']);
        fputcsv($output, ['Contoh Judul', 'text', 'Isi materi disini...', '']);
    } elseif ($type === 'modules') {
        // Header Bahasa Indonesia, Tanpa ID
        fputcsv($output, ['Judul Bab', 'Deskripsi Singkat']);
        fputcsv($output, ['Pendahuluan', 'Pengenalan dasar materi']);
    } elseif ($type === 'questions') {
        // Header Bahasa Indonesia, Tanpa ID
        fputcsv($output, ['Pertanyaan', 'Penjelasan', 'Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D', 'Pilihan E', 'Jawaban Benar (A-E)']);
        fputcsv($output, ['Contoh Pertanyaan?', 'Penjelasan jawaban', 'Opsi A', 'Opsi B', 'Opsi C', 'Opsi D', 'Opsi E', 'A']);
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
                    
                    if ($importType === 'modules') {
                        // Input dari Form
                        $courseId = (int)$_POST['course_id'];
                        
                        // Auto Calculate Order
                        $stmtOrder = $pdo->prepare("SELECT MAX(module_order) FROM course_modules WHERE course_id = ?");
                        $stmtOrder->execute([$courseId]);
                        $maxOrder = $stmtOrder->fetchColumn();
                        $nextOrder = ($maxOrder ? $maxOrder : 0) + 1;

                        // CSV Columns: 0=Judul, 1=Deskripsi
                        if (count($data) < 1) continue;
                        
                        $title = $data[0];
                        $summary = $data[1] ?? '';

                        $stmt = $pdo->prepare("INSERT INTO course_modules (course_id, module_order, title, summary) VALUES (?, ?, ?, ?)");
                        $stmt->execute([
                            $courseId,
                            $nextOrder,
                            $title,
                            $summary
                        ]);
                        $successCount++;

                    } elseif ($importType === 'lessons') {
                        // Input dari Form
                        $courseId = (int)$_POST['course_id'];
                        $moduleId = (int)$_POST['module_id'];
                        
                        // Auto Calculate Order: Get max order for this module
                        $stmtOrder = $pdo->prepare("SELECT MAX(lesson_order) FROM lessons WHERE module_id = ?");
                        $stmtOrder->execute([$moduleId]);
                        $maxOrder = $stmtOrder->fetchColumn();
                        $nextOrder = ($maxOrder ? $maxOrder : 0) + 1;

                        // CSV Columns: 0=Judul, 1=Tipe, 2=Isi, 3=Video
                        if (count($data) < 1) continue; 
                        
                        $title = $data[0];
                        $contentType = !empty($data[1]) ? strtolower($data[1]) : 'text';
                        $contentText = $data[2] ?? '';
                        $videoUrl = $data[3] ?? '';

                        $stmt = $pdo->prepare("INSERT INTO lessons (course_id, module_id, lesson_order, title, content_type, content_text, video_url, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                        $stmt->execute([
                            $courseId,
                            $moduleId,
                            $nextOrder, // Auto-incremented order
                            $title,
                            $contentType,
                            $contentText,
                            $videoUrl
                        ]);
                        $successCount++;
                        
                    } elseif ($importType === 'questions') {
                        // Input dari Form
                        $lessonId = (int)$_POST['lesson_id'];

                        // CSV Columns: 0=Pertanyaan, 1=Penjelasan, 2=A, 3=B, 4=C, 5=D, 6=E, 7=Jawaban
                        if (count($data) < 8) continue;
                        
                        $questionText = $data[0];
                        $explanation = $data[1];
                        
                        // Insert Question
                        $stmtQ = $pdo->prepare("INSERT INTO lesson_questions (lesson_id, question_text, explanation) VALUES (?, ?, ?)");
                        $stmtQ->execute([
                            $lessonId,
                            $questionText,
                            $explanation
                        ]);
                        $questionId = $pdo->lastInsertId();
                        
                        // Insert Options
                        $options = [
                            'A' => $data[2],
                            'B' => $data[3],
                            'C' => $data[4],
                            'D' => $data[5],
                            'E' => $data[6]
                        ];
                        $correctAnswer = strtoupper(trim($data[7]));
                        
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

// Fetch Courses for Dropdown
$stmtCourses = $pdo->query("SELECT id, title FROM courses ORDER BY title ASC");
$courses = $stmtCourses->fetchAll();
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
        <!-- IMPORT MODULES -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Import Bab (Modules)</h5>
                </div>
                <div class="card-body">
                    <p>Upload Bab untuk Kursus tertentu.</p>
                    <div class="mb-3">
                        <a href="index.php?page=admin_import&download_template=modules" class="btn btn-outline-info btn-sm" target="_blank">
                            <i class="bi bi-download"></i> Download Template CSV
                        </a>
                    </div>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="import_type" value="modules">
                        
                        <div class="mb-3">
                            <label class="form-label">Pilih Kursus</label>
                            <select name="course_id" class="form-select" required>
                                <option value="">-- Pilih Kursus --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File CSV</label>
                            <input type="file" name="import_file" class="form-control" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn btn-info text-white">Upload Bab</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- IMPORT MATERI -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Import Materi (Lessons)</h5>
                </div>
                <div class="card-body">
                    <p>Upload materi untuk Bab tertentu.</p>
                    <div class="mb-3">
                        <a href="index.php?page=admin_import&download_template=lessons" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="bi bi-download"></i> Download Template CSV
                        </a>
                    </div>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="import_type" value="lessons">
                        
                        <div class="mb-3">
                            <label class="form-label">Pilih Kursus</label>
                            <select name="course_id" class="form-select course-select" data-target="module-select-lessons" required>
                                <option value="">-- Pilih Kursus --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pilih Bab (Module)</label>
                            <select name="module_id" id="module-select-lessons" class="form-select" required disabled>
                                <option value="">-- Pilih Kursus Terlebih Dahulu --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File CSV</label>
                            <input type="file" name="import_file" class="form-control" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Materi</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- IMPORT SOAL -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Import Soal (Questions)</h5>
                </div>
                <div class="card-body">
                    <p>Upload soal untuk Materi tertentu.</p>
                    <div class="mb-3">
                        <a href="index.php?page=admin_import&download_template=questions" class="btn btn-outline-success btn-sm" target="_blank">
                            <i class="bi bi-download"></i> Download Template CSV
                        </a>
                    </div>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="import_type" value="questions">
                        
                        <div class="mb-3">
                            <label class="form-label">Pilih Kursus</label>
                            <select class="form-select course-select" data-target="module-select-questions" required>
                                <option value="">-- Pilih Kursus --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pilih Bab (Module)</label>
                            <select id="module-select-questions" class="form-select module-select" data-target="lesson-select-questions" required disabled>
                                <option value="">-- Pilih Kursus Terlebih Dahulu --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pilih Materi (Lesson)</label>
                            <select name="lesson_id" id="lesson-select-questions" class="form-select" required disabled>
                                <option value="">-- Pilih Bab Terlebih Dahulu --</option>
                            </select>
                        </div>

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
            <li>Pastikan format file adalah <strong>.csv</strong>.</li>
            <li>Gunakan template terbaru (Header Bahasa Indonesia).</li>
            <li>Pilih lokasi (Kursus/Bab/Materi) di form sebelum upload.</li>
            <li>Sistem akan otomatis mengatur urutan materi.</li>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Course Change -> Load Modules
    document.querySelectorAll('.course-select').forEach(select => {
        select.addEventListener('change', function() {
            const courseId = this.value;
            const targetId = this.getAttribute('data-target');
            const targetSelect = document.getElementById(targetId);
            
            // Reset target
            targetSelect.innerHTML = '<option value="">Loading...</option>';
            targetSelect.disabled = true;
            
            // Reset sub-target if exists (Lesson select)
            if (targetSelect.classList.contains('module-select')) {
                const subTargetId = targetSelect.getAttribute('data-target');
                const subTargetSelect = document.getElementById(subTargetId);
                if(subTargetSelect) {
                    subTargetSelect.innerHTML = '<option value="">-- Pilih Bab Terlebih Dahulu --</option>';
                    subTargetSelect.disabled = true;
                }
            }

            if (courseId) {
                fetch(`index.php?page=admin_import&ajax_action=get_modules&course_id=${courseId}&ajax=1`)
                    .then(response => response.json())
                    .then(data => {
                        targetSelect.innerHTML = '<option value="">-- Pilih Bab --</option>';
                        data.forEach(item => {
                            targetSelect.innerHTML += `<option value="${item.id}">${item.title}</option>`;
                        });
                        targetSelect.disabled = false;
                    })
                    .catch(err => {
                        console.error(err);
                        targetSelect.innerHTML = '<option value="">Error loading modules</option>';
                    });
            } else {
                targetSelect.innerHTML = '<option value="">-- Pilih Kursus Terlebih Dahulu --</option>';
            }
        });
    });

    // Handle Module Change -> Load Lessons
    document.querySelectorAll('.module-select').forEach(select => {
        select.addEventListener('change', function() {
            const moduleId = this.value;
            const targetId = this.getAttribute('data-target');
            const targetSelect = document.getElementById(targetId);
            
            targetSelect.innerHTML = '<option value="">Loading...</option>';
            targetSelect.disabled = true;

            if (moduleId) {
                fetch(`index.php?page=admin_import&ajax_action=get_lessons&module_id=${moduleId}&ajax=1`)
                    .then(response => response.json())
                    .then(data => {
                        targetSelect.innerHTML = '<option value="">-- Pilih Materi --</option>';
                        data.forEach(item => {
                            targetSelect.innerHTML += `<option value="${item.id}">${item.title}</option>`;
                        });
                        targetSelect.disabled = false;
                    })
                    .catch(err => {
                        console.error(err);
                        targetSelect.innerHTML = '<option value="">Error loading lessons</option>';
                    });
            } else {
                targetSelect.innerHTML = '<option value="">-- Pilih Bab Terlebih Dahulu --</option>';
            }
        });
    });
});
</script>