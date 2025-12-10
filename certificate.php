<?php
require_once "auth.php";
check_login();
require_once "config.php";

// 1. Validasi Input
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$userId   = $_SESSION['user']['id'];

if (!$courseId) {
    die("Kursus tidak valid.");
}

// 2. Ambil Data Kursus
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    die("Kursus tidak ditemukan.");
}

// 3. Cek Kelulusan (Validasi Server-Side)
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
$stmtTotal->execute([$courseId]);
$totalLessons = $stmtTotal->fetchColumn();

$stmtPassed = $pdo->prepare("
    SELECT COUNT(DISTINCT lesson_id) 
    FROM lesson_progress lp
    JOIN lessons l ON lp.lesson_id = l.id
    WHERE lp.user_id = ? AND l.course_id = ? AND lp.has_passed = 1
");
$stmtPassed->execute([$userId, $courseId]);
$passedLessons = $stmtPassed->fetchColumn();

if ($passedLessons < $totalLessons || $totalLessons == 0) {
    die("Anda belum menyelesaikan semua materi di kursus ini. ($passedLessons / $totalLessons)");
}

// 4. Cek Kelulusan Uji Komprehensif
$stmtExam = $pdo->prepare("SELECT COUNT(*) FROM course_exam_attempts WHERE user_id = ? AND course_id = ? AND passed = 1");
$stmtExam->execute([$userId, $courseId]);
if ($stmtExam->fetchColumn() == 0) {
    die("Anda belum lulus Uji Komprehensif. Silakan ikuti ujian terlebih dahulu.");
}

// Format Tanggal Indonesia
function tgl_indo($tanggal){
	$bulan = array (
		1 =>   'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
	);
	$pecahkan = explode('-', $tanggal);
	
	// variabel pecahkan 0 = tanggal
	// variabel pecahkan 1 = bulan
	// variabel pecahkan 2 = tahun
 
	return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Kelulusan - <?= htmlspecialchars($course['title']) ?></title>
    <!-- Library untuk convert HTML ke PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- Font Keren -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Great+Vibes&family=Lato:wght@300;400&family=Pinyon+Script&display=swap" rel="stylesheet">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #e9ecef;
            font-family: 'Lato', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .toolbar {
            width: 100%;
            background: #333;
            color: white;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .btn-download {
            background-color: #DAA520;
            color: #fff;
            border: none;
            padding: 10px 25px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-download:hover {
            background-color: #b8860b;
        }
        .btn-back {
            background-color: transparent;
            color: #ccc;
            border: 1px solid #ccc;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 10px;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background-color: #fff;
            color: #333;
        }

        /* Area Sertifikat (A4 Landscape Ratio) */
        #certificate-area {
            width: 1123px; /* A4 width at 96dpi approx, scaled for quality */
            height: 794px;
            background: #fff;
            margin: 30px auto;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
            /* Background Pattern */
            background-image: radial-gradient(#f8f9fa 20%, transparent 20%),
                              radial-gradient(#f8f9fa 20%, transparent 20%);
            background-color: #fff;
            background-position: 0 0, 50px 50px;
            background-size: 100px 100px;
        }

        .border-outer {
            position: absolute;
            top: 20px; left: 20px; right: 20px; bottom: 20px;
            border: 5px solid #1a3c5e; /* Dark Blue */
        }
        
        .border-inner {
            position: absolute;
            top: 35px; left: 35px; right: 35px; bottom: 35px;
            border: 2px solid #DAA520; /* Gold */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px;
        }

        /* Ornamen Sudut */
        .corner-ornament {
            position: absolute;
            width: 150px;
            height: 150px;
            background-repeat: no-repeat;
            background-size: contain;
            opacity: 0.2;
        }
        /* Kita pakai CSS shapes simple untuk ornamen jika tidak ada gambar */
        .corner-tl { top: 10px; left: 10px; border-top: 10px solid #1a3c5e; border-left: 10px solid #1a3c5e; width: 100px; height: 100px; }
        .corner-tr { top: 10px; right: 10px; border-top: 10px solid #1a3c5e; border-right: 10px solid #1a3c5e; width: 100px; height: 100px; }
        .corner-bl { bottom: 10px; left: 10px; border-bottom: 10px solid #1a3c5e; border-left: 10px solid #1a3c5e; width: 100px; height: 100px; }
        .corner-br { bottom: 10px; right: 10px; border-bottom: 10px solid #1a3c5e; border-right: 10px solid #1a3c5e; width: 100px; height: 100px; }

        .header-title {
            font-family: 'Cinzel', serif;
            font-size: 64px;
            color: #1a3c5e;
            margin: 0;
            letter-spacing: 5px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .cert-no {
            font-family: 'Lato', sans-serif;
            font-size: 16px;
            color: #666;
            letter-spacing: 2px;
            margin-bottom: 40px;
        }

        .presented-text {
            font-family: 'Pinyon Script', cursive;
            font-size: 32px;
            color: #DAA520;
            margin-bottom: 10px;
        }

        .student-name {
            font-family: 'Great Vibes', cursive;
            font-size: 86px;
            color: #1a3c5e;
            margin: 10px 0 30px 0;
            line-height: 1.2;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            display: inline-block;
            min-width: 500px;
        }

        .description {
            font-size: 20px;
            color: #444;
            max-width: 800px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .course-name {
            font-weight: 700;
            color: #1a3c5e;
            font-size: 28px;
            display: block;
            margin-top: 10px;
            font-family: 'Cinzel', serif;
        }

        .footer-section {
            display: flex;
            justify-content: space-between;
            width: 80%;
            margin-top: 60px;
            align-items: flex-end;
        }

        .signature-box {
            text-align: center;
            width: 250px;
        }

        .sign-img {
            height: 60px;
            margin-bottom: 10px;
            font-family: 'Great Vibes', cursive;
            font-size: 40px;
            color: #1a3c5e;
        }

        .sign-line {
            border-top: 2px solid #DAA520;
            margin: 10px auto;
            width: 100%;
        }

        .sign-name {
            font-weight: bold;
            color: #333;
            font-size: 18px;
        }

        .sign-title {
            font-size: 14px;
            color: #777;
        }

        .seal-badge {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 4px double #DAA520;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #DAA520;
            font-family: 'Cinzel', serif;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            letter-spacing: 1px;
            box-shadow: 0 0 0 10px rgba(218, 165, 32, 0.1);
            transform: rotate(-15deg);
        }

    </style>
</head>
<body>

    <div class="toolbar">
        <button class="btn-download" onclick="generatePDF()">
            <i class="bi bi-download"></i> Download PDF
        </button>
        <a href="index.php?kursus=<?= htmlspecialchars($course['slug']) ?>" class="btn-back">Kembali</a>
    </div>

    <div id="certificate-area">
        <div class="border-outer"></div>
        <div class="corner-tl"></div>
        <div class="corner-tr"></div>
        <div class="corner-bl"></div>
        <div class="corner-br"></div>

        <div class="border-inner">
            
            <h1 class="header-title">Sertifikat Kelulusan</h1>
            <div class="cert-no">NO: CERT/<?= date('Y') ?>/<?= str_pad($courseId, 3, '0', STR_PAD_LEFT) ?>/<?= str_pad($userId, 4, '0', STR_PAD_LEFT) ?></div>

            <div class="presented-text">Diberikan Kepada</div>
            
            <div class="student-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></div>

            <div class="description">
                Atas keberhasilannya menyelesaikan seluruh materi pembelajaran dan ujian kompetensi pada kursus:
                <span class="course-name"><?= htmlspecialchars($course['title']) ?></span>
            </div>

            <div class="footer-section">
                <div class="signature-box">
                    <div class="sign-img"><?= tgl_indo(date('Y-m-d')) ?></div>
                    <div class="sign-line"></div>
                    <div class="sign-name">Tanggal Lulus</div>
                </div>

                <div class="seal-badge">
                    MANDIRI<br>BELAJAR<br>VERIFIED
                </div>

                <div class="signature-box">
                    <!-- Simulasi Tanda Tangan -->
                    <div class="sign-img">Administrator</div> 
                    <div class="sign-line"></div>
                    <div class="sign-name">Kepala Program</div>
                    <div class="sign-title">Mandiri Belajar</div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function generatePDF() {
            const element = document.getElementById('certificate-area');
            const button = document.querySelector('.btn-download');
            
            button.innerHTML = 'Sedang Memproses...';
            button.disabled = true;

            const opt = {
                margin:       0,
                filename:     'Sertifikat_<?= preg_replace("/[^a-zA-Z0-9]/", "_", $course['title']) ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).save().then(function(){
                button.innerHTML = 'Download PDF';
                button.disabled = false;
            });
        }
    </script>
</body>
</html>
