<?php
// pages/admin_import.php

// Coba load auth.php (asumsi file ini di-include dari root atau path sudah benar)
// Jika dijalankan langsung dari pages/, ini mungkin perlu penyesuaian path '../auth.php'
// Namun kita ikuti pola file sebelumnya.
require_once 'auth.php';
check_admin();

// --- DETEKSI LIBRARY PHPSPREADSHEET ---
// Cek di folder vendor lokal (jika ada)
$vendorLocal = __DIR__ . '/../vendor/autoload.php';
// Cek di folder sibling (familyhood.my.id) sesuai request user
$vendorSibling = __DIR__ . '/../../familyhood.my.id/vendor/autoload.php';

$spreadsheetReady = false;
if (file_exists($vendorLocal)) {
    require_once $vendorLocal;
    $spreadsheetReady = true;
} elseif (file_exists($vendorSibling)) {
    require_once $vendorSibling;
    $spreadsheetReady = true;
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// --- AJAX HANDLER FOR DROPDOWNS ---
if (isset($_GET['ajax_action'])) {
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

// --- HANDLE TEMPLATE DOWNLOAD (EXCEL) ---
if (isset($_GET['download_template'])) {
    if (!$spreadsheetReady) {
        die("Library PhpSpreadsheet tidak ditemukan. Pastikan folder vendor tersedia.");
    }

    if (ob_get_length()) ob_clean();
    
    $type = $_GET['download_template'];
    $filename = 'template_' . $type . '.xlsx';
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    if ($type === 'lessons') {
        // Header
        $headers = ['Judul Materi', 'Tipe Konten (text/video)', 'Isi Materi', 'URL Video'];
        $sheet->fromArray([$headers], NULL, 'A1');
        // Contoh Data
        $example = ['Contoh Judul', 'text', 'Isi materi disini...', ''];
        $sheet->fromArray([$example], NULL, 'A2');
        
        // Auto size columns
        foreach(range('A','D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
    } elseif ($type === 'modules') {
        $headers = ['Judul Modul', 'Deskripsi'];
        $sheet->fromArray([$headers], NULL, 'A1');
        $example = ['Modul 1: Pendahuluan', 'Deskripsi singkat modul ini'];
        $sheet->fromArray([$example], NULL, 'A2');
        
        foreach(range('A','B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

    } elseif ($type === 'questions') {
        $headers = ['Pertanyaan', 'Opsi A', 'Opsi B', 'Opsi C', 'Opsi D', 'Jawaban Benar (A/B/C/D)', 'Penjelasan'];
        $sheet->fromArray([$headers], NULL, 'A1');
        $example = ['Apa ibukota Indonesia?', 'Jakarta', 'Bandung', 'Surabaya', 'Medan', 'A', 'Jakarta adalah ibukota negara.'];
        $sheet->fromArray([$example], NULL, 'A2');
        
        foreach(range('A','G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// --- HANDLE IMPORT (EXCEL) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_type'])) {
    if (!$spreadsheetReady) {
        $error = "Library PhpSpreadsheet tidak ditemukan. Tidak dapat memproses file Excel.";
    } elseif (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['import_file']['tmp_name'];
        $importType = $_POST['import_type'];
        
        try {
            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            // Hapus header (baris pertama)
            array_shift($rows);
            
            $count = 0;
            $pdo->beginTransaction();
            
            if ($importType === 'lessons') {
                $moduleId = (int)$_POST['module_id'];
                // Ambil urutan terakhir
                $stmtOrder = $pdo->prepare("SELECT MAX(lesson_order) FROM lessons WHERE module_id = ?");
                $stmtOrder->execute([$moduleId]);
                $lastOrder = $stmtOrder->fetchColumn() ?: 0;
                
                $stmt = $pdo->prepare("INSERT INTO lessons (module_id, title, content_type, content, video_url, lesson_order) VALUES (?, ?, ?, ?, ?, ?)");
                
                foreach ($rows as $row) {
                    // Pastikan baris tidak kosong
                    if (empty(array_filter($row))) continue;
                    
                    $title = $row[0] ?? '';
                    $contentType = strtolower($row[1] ?? 'text');
                    $content = $row[2] ?? '';
                    $videoUrl = $row[3] ?? '';
                    
                    if ($title) {
                        $lastOrder++;
                        $stmt->execute([$moduleId, $title, $contentType, $content, $videoUrl, $lastOrder]);
                        $count++;
                    }
                }
                
            } elseif ($importType === 'modules') {
                $courseId = (int)$_POST['course_id'];
                $stmtOrder = $pdo->prepare("SELECT MAX(module_order) FROM course_modules WHERE course_id = ?");
                $stmtOrder->execute([$courseId]);
                $lastOrder = $stmtOrder->fetchColumn() ?: 0;
                
                $stmt = $pdo->prepare("INSERT INTO course_modules (course_id, title, description, module_order) VALUES (?, ?, ?, ?)");
                
                foreach ($rows as $row) {
                    if (empty(array_filter($row))) continue;
                    
                    $title = $row[0] ?? '';
                    $desc = $row[1] ?? '';
                    
                    if ($title) {
                        $lastOrder++;
                        $stmt->execute([$courseId, $title, $desc, $lastOrder]);
                        $count++;
                    }
                }
                
            } elseif ($importType === 'questions') {
                $lessonId = (int)$_POST['lesson_id'];
                $stmt = $pdo->prepare("INSERT INTO quiz_questions (lesson_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($rows as $row) {
                    if (empty(array_filter($row))) continue;
                    
                    $qText = $row[0] ?? '';
                    $optA = $row[1] ?? '';
                    $optB = $row[2] ?? '';
                    $optC = $row[3] ?? '';
                    $optD = $row[4] ?? '';
                    $correct = strtoupper($row[5] ?? 'A');
                    $explanation = $row[6] ?? '';
                    
                    if ($qText) {
                        $stmt->execute([$lessonId, $qText, $optA, $optB, $optC, $optD, $correct, $explanation]);
                        $count++;
                    }
                }
            }
            
            $pdo->commit();
            $message = "Berhasil mengimport $count data.";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Gagal import: " . $e->getMessage();
        }
    } else {
        $error = "Gagal upload file.";
    }
}

// Ambil data kursus untuk dropdown
$stmt = $pdo->query("SELECT id, title FROM courses ORDER BY title ASC");
$courses = $stmt->fetchAll();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Import Data (Excel .xlsx)</h1>
    
    <?php if (!$spreadsheetReady): ?>
        <div class="alert alert-warning">
            <strong>Peringatan:</strong> Library PhpSpreadsheet tidak ditemukan.<br>
            Sistem mencoba mencari di: <br>
            1. <code><?= htmlspecialchars($vendorLocal) ?></code> (Tidak ada)<br>
            2. <code><?= htmlspecialchars($vendorSibling) ?></code> (Tidak ada)<br>
            Fitur import/export Excel tidak akan berfungsi.
        </div>
    <?php endif; ?>

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
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-file-excel me-1"></i> Import Modul
                </div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
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
                            <label class="form-label">File Excel (.xlsx)</label>
                            <input type="file" name="import_file" class="form-control" accept=".xlsx" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" <?= !$spreadsheetReady ? 'disabled' : '' ?>>Import Modul</button>
                    </form>
                    <hr>
                    <a href="?page=admin_import&download_template=modules" class="btn btn-outline-secondary btn-sm w-100" <?= !$spreadsheetReady ? 'disabled' : '' ?>>
                        <i class="fas fa-download"></i> Download Template Modul
                    </a>
                </div>
            </div>
        </div>

        <!-- IMPORT LESSONS -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-file-excel me-1"></i> Import Materi (Lesson)
                </div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="import_type" value="lessons">
                        <div class="mb-3">
                            <label class="form-label">Pilih Kursus</label>
                            <select id="course_select_lesson" class="form-select" required onchange="loadModules(this.value, 'module_select_lesson')">
                                <option value="">-- Pilih Kursus --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Modul</label>
                            <select id="module_select_lesson" name="module_id" class="form-select" required disabled>
                                <option value="">-- Pilih Kursus Dulu --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File Excel (.xlsx)</label>
                            <input type="file" name="import_file" class="form-control" accept=".xlsx" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100" <?= !$spreadsheetReady ? 'disabled' : '' ?>>Import Materi</button>
                    </form>
                    <hr>
                    <a href="?page=admin_import&download_template=lessons" class="btn btn-outline-secondary btn-sm w-100" <?= !$spreadsheetReady ? 'disabled' : '' ?>>
                        <i class="fas fa-download"></i> Download Template Materi
                    </a>
                </div>
            </div>
        </div>

        <!-- IMPORT QUESTIONS -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-file-excel me-1"></i> Import Soal Kuis
                </div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="import_type" value="questions">
                        <div class="mb-3">
                            <label class="form-label">Pilih Kursus</label>
                            <select id="course_select_quiz" class="form-select" required onchange="loadModules(this.value, 'module_select_quiz')">
                                <option value="">-- Pilih Kursus --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Modul</label>
                            <select id="module_select_quiz" class="form-select" required disabled onchange="loadLessons(this.value, 'lesson_select_quiz')">
                                <option value="">-- Pilih Kursus Dulu --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Materi (Lesson)</label>
                            <select id="lesson_select_quiz" name="lesson_id" class="form-select" required disabled>
                                <option value="">-- Pilih Modul Dulu --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File Excel (.xlsx)</label>
                            <input type="file" name="import_file" class="form-control" accept=".xlsx" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100" <?= !$spreadsheetReady ? 'disabled' : '' ?>>Import Soal</button>
                    </form>
                    <hr>
                    <a href="?page=admin_import&download_template=questions" class="btn btn-outline-secondary btn-sm w-100" <?= !$spreadsheetReady ? 'disabled' : '' ?>>
                        <i class="fas fa-download"></i> Download Template Soal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadModules(courseId, targetId) {
    const target = document.getElementById(targetId);
    target.innerHTML = '<option>Loading...</option>';
    target.disabled = true;
    
    // Reset lesson dropdown if exists
    if(targetId === 'module_select_quiz') {
        const lessonSelect = document.getElementById('lesson_select_quiz');
        lessonSelect.innerHTML = '<option value="">-- Pilih Modul Dulu --</option>';
        lessonSelect.disabled = true;
    }

    if (!courseId) {
        target.innerHTML = '<option value="">-- Pilih Kursus Dulu --</option>';
        return;
    }

    fetch(`?page=admin_import&ajax_action=get_modules&course_id=${courseId}`)
        .then(response => response.json())
        .then(data => {
            target.innerHTML = '<option value="">-- Pilih Modul --</option>';
            data.forEach(item => {
                target.innerHTML += `<option value="${item.id}">${item.title}</option>`;
            });
            target.disabled = false;
        })
        .catch(err => {
            console.error(err);
            target.innerHTML = '<option>Error loading modules</option>';
        });
}

function loadLessons(moduleId, targetId) {
    const target = document.getElementById(targetId);
    target.innerHTML = '<option>Loading...</option>';
    target.disabled = true;

    if (!moduleId) {
        target.innerHTML = '<option value="">-- Pilih Modul Dulu --</option>';
        return;
    }

    fetch(`?page=admin_import&ajax_action=get_lessons&module_id=${moduleId}`)
        .then(response => response.json())
        .then(data => {
            target.innerHTML = '<option value="">-- Pilih Materi --</option>';
            data.forEach(item => {
                target.innerHTML += `<option value="${item.id}">${item.title}</option>`;
            });
            target.disabled = false;
        })
        .catch(err => {
            console.error(err);
            target.innerHTML = '<option>Error loading lessons</option>';
        });
}
</script>
