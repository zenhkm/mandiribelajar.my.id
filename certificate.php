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
// Hitung total materi
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
$stmtTotal->execute([$courseId]);
$totalLessons = $stmtTotal->fetchColumn();

// Hitung materi yang lulus
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

// 4. Generate Sertifikat dengan GD Library
// Ukuran A4 Landscape (kurang lebih dalam pixel 72 DPI)
// Width: 842, Height: 595. Kita pakai resolusi lebih tinggi dikit biar bagus: 1000 x 700
$width  = 1000;
$height = 700;

$image = imagecreatetruecolor($width, $height);

// Warna
$white      = imagecolorallocate($image, 255, 255, 255);
$black      = imagecolorallocate($image, 0, 0, 0);
$grey       = imagecolorallocate($image, 100, 100, 100);
$lightGrey  = imagecolorallocate($image, 240, 240, 240);
$gold       = imagecolorallocate($image, 218, 165, 32);
$blue       = imagecolorallocate($image, 0, 51, 102);

// Background Putih
imagefilledrectangle($image, 0, 0, $width, $height, $white);

// Border Emas Tebal
imagesetthickness($image, 10);
imagerectangle($image, 20, 20, $width - 20, $height - 20, $gold);

// Border Tipis Dalam
imagesetthickness($image, 2);
imagerectangle($image, 35, 35, $width - 35, $height - 35, $blue);

// Fungsi Helper untuk Text Center
function centerText($img, $size, $angle, $x, $y, $color, $font, $text) {
    // Karena kita tidak punya font TTF eksternal yang pasti ada, kita pakai font bawaan GD (1-5)
    // Font bawaan GD ukurannya kecil.
    // Opsi: Gunakan imagestring (font 1-5) atau imagettftext jika ada font.
    // Untuk amannya di server hosting standar, kita coba cari font sistem atau pakai font bawaan tapi di-scale (agak pecah).
    // TERBAIK: Kita asumsikan server punya font Arial atau sejenis, atau kita pakai font bawaan GD dengan layout sederhana.
    
    // Kita pakai font bawaan GD (imagestring) karena paling aman tanpa file .ttf
    // Font 5 adalah yang terbesar.
    
    $fontWidth  = imagefontwidth($font);
    $textWidth  = strlen($text) * $fontWidth;
    $centerX    = ($x - ($textWidth / 2));
    
    imagestring($img, $font, $centerX, $y, $text, $color);
}

// Karena font bawaan GD (imagestring) sangat kecil untuk sertifikat 1000px,
// Kita akan coba pakai imagettftext jika memungkinkan.
// Kita coba cari font default di Windows/Linux.
$fontFile = '';
$os = PHP_OS;
if (strtoupper(substr($os, 0, 3)) === 'WIN') {
    $fontFile = 'C:\Windows\Fonts\arial.ttf';
} else {
    // Linux path umum
    $fontFile = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf'; 
    if (!file_exists($fontFile)) {
        $fontFile = '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf';
    }
}

// Fallback jika font tidak ditemukan: Pakai font bawaan GD tapi kita buat gambar kecil lalu resize (agak buram tapi terbaca)
// ATAU: Kita pakai font bawaan GD tapi layoutnya kita sesuaikan.
// Mari kita coba pakai font bawaan GD saja tapi dengan teknik scaling sederhana atau layout yang rapi.
// UPDATE: Kita pakai font bawaan GD (5) tapi kita posisikan dengan baik.

// Header
$text = "SERTIFIKAT KELULUSAN";
// Font 5 width approx 9px, height 15px.
// Center X = 1000/2 = 500.
// Text width = len * 9.
$len = strlen($text);
$xPos = ($width - ($len * 15)) / 2; // Estimasi lebar font 5 agak besar
// imagestring($image, 5, $xPos, 100, $text, $blue);
// Font bawaan terlalu kecil untuk sertifikat resolusi ini.
// SOLUSI: Kita gunakan imagefttext (FreeType) jika tersedia, biasanya ada di PHP modern.
// Kita akan gunakan font file relatif jika ada, atau download font sementara? Tidak bisa download.
// Kita akan gunakan font bawaan tapi kita perbesar gambarnya saat output? Tidak.
// Kita akan gunakan font bawaan tapi kita buat teksnya "pixelated" besar dengan cara menggambar pixel manual? Terlalu rumit.
// Kita akan gunakan font bawaan GD tapi kita zoom?
// Opsi paling aman: Gunakan font bawaan GD tapi layoutnya minimalis.

// Header "SERTIFIKAT"
// Kita buat huruf besar dengan menggambar garis? Tidak.
// Kita pakai imagestringup? Tidak.

// Mari kita coba cari font Arial.ttf di folder layout/ atau root jika user punya.
// Karena tidak ada, kita pakai font sistem. Jika gagal, fallback ke GD font.

$useTTF = false;
if (file_exists($fontFile)) {
    $useTTF = true;
}

if ($useTTF) {
    // Judul
    $bbox = imagettfbbox(40, 0, $fontFile, "SERTIFIKAT KELULUSAN");
    $textW = $bbox[2] - $bbox[0];
    imagettftext($image, 40, 0, ($width - $textW)/2, 150, $blue, $fontFile, "SERTIFIKAT KELULUSAN");

    // Subjudul
    $text = "No: CERT/" . date('Y') . "/" . str_pad($courseId, 3, '0', STR_PAD_LEFT) . "/" . str_pad($userId, 4, '0', STR_PAD_LEFT);
    $bbox = imagettfbbox(12, 0, $fontFile, $text);
    $textW = $bbox[2] - $bbox[0];
    imagettftext($image, 12, 0, ($width - $textW)/2, 190, $grey, $fontFile, $text);

    // Diberikan kepada
    $text = "Diberikan kepada:";
    $bbox = imagettfbbox(16, 0, $fontFile, $text);
    $textW = $bbox[2] - $bbox[0];
    imagettftext($image, 16, 0, ($width - $textW)/2, 280, $black, $fontFile, $text);

    // Nama User
    $name = strtoupper($_SESSION['user']['name']);
    $bbox = imagettfbbox(32, 0, $fontFile, $name);
    $textW = $bbox[2] - $bbox[0];
    imagettftext($image, 32, 0, ($width - $textW)/2, 340, $gold, $fontFile, $name);

    // Atas kelulusan
    $text = "Telah menyelesaikan seluruh materi dan ujian pada kursus:";
    $bbox = imagettfbbox(16, 0, $fontFile, $text);
    $textW = $bbox[2] - $bbox[0];
    imagettftext($image, 16, 0, ($width - $textW)/2, 400, $black, $fontFile, $text);

    // Nama Kursus
    $courseTitle = $course['title'];
    $bbox = imagettfbbox(24, 0, $fontFile, $courseTitle);
    $textW = $bbox[2] - $bbox[0];
    imagettftext($image, 24, 0, ($width - $textW)/2, 450, $blue, $fontFile, $courseTitle);

    // Tanggal
    $date = "Tanggal Lulus: " . date("d F Y");
    $bbox = imagettfbbox(12, 0, $fontFile, $date);
    $textW = $bbox[2] - $bbox[0];
    imagettftext($image, 12, 0, ($width - $textW)/2, 550, $grey, $fontFile, $date);

} else {
    // FALLBACK JIKA TIDAK ADA FONT TTF (Tampilan Sederhana)
    // Kita pakai font 5 (terbesar bawaan)
    // Kita akali dengan membuat gambar kecil lalu di-resize? Tidak, pecah.
    // Kita pakai layout sederhana saja.
    
    $font = 5;
    $lineHeight = 20;
    $y = 100;

    $str = "SERTIFIKAT KELULUSAN";
    $len = strlen($str) * 9; // approx width per char font 5
    imagestring($image, $font, ($width - $len)/2, $y, $str, $blue);
    
    $y += 40;
    $str = "No: CERT/" . date('Y') . "/" . $courseId . "/" . $userId;
    $len = strlen($str) * 9;
    imagestring($image, $font, ($width - $len)/2, $y, $str, $grey);

    $y += 60;
    $str = "Diberikan kepada:";
    $len = strlen($str) * 9;
    imagestring($image, $font, ($width - $len)/2, $y, $str, $black);

    $y += 40;
    $str = strtoupper($_SESSION['user']['name']);
    $len = strlen($str) * 9;
    // Hack: Tulis nama 2x geser 1px biar tebal
    imagestring($image, $font, ($width - $len)/2, $y, $str, $gold);
    imagestring($image, $font, ($width - $len)/2 + 1, $y, $str, $gold);

    $y += 60;
    $str = "Telah menyelesaikan kursus:";
    $len = strlen($str) * 9;
    imagestring($image, $font, ($width - $len)/2, $y, $str, $black);

    $y += 40;
    $str = $course['title'];
    $len = strlen($str) * 9;
    imagestring($image, $font, ($width - $len)/2, $y, $str, $blue);
    imagestring($image, $font, ($width - $len)/2 + 1, $y, $str, $blue);

    $y += 80;
    $str = "Tanggal: " . date("d F Y");
    $len = strlen($str) * 9;
    imagestring($image, $font, ($width - $len)/2, $y, $str, $grey);
}

// Output Image
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="Sertifikat_' . $course['slug'] . '.png"');
imagepng($image);
imagedestroy($image);
?>