-- ============================================================================
-- SCRIPT SQL UNTUK TESTING DAN VALIDATION DATA SAMPEL
-- ============================================================================
-- Script ini membantu Anda memverifikasi bahwa data sampel terimport dengan benar
-- Jalankan query ini di MySQL untuk memastikan semuanya OK
-- ============================================================================

-- ============================================================================
-- SECTION 1: VERIFIKASI DATA COURSES
-- ============================================================================

-- 1.1 Lihat semua kursus
SELECT 
    id,
    title,
    slug,
    level,
    status,
    duration,
    lessons,
    SUBSTRING(description, 1, 50) as description_preview
FROM courses
WHERE slug = 'ushul-fiqh-dasar';

-- Expected output: 1 row dengan data Ushul Fiqh Dasar

-- 1.2 Hitung total kursus
SELECT COUNT(*) as total_courses FROM courses;

-- Expected: 1

-- 1.3 Lihat ukuran gambar sampul (jika ada)
SELECT 
    id,
    title,
    image,
    CASE 
        WHEN image IS NOT NULL THEN 'Ada'
        ELSE 'Tidak Ada'
    END as image_status
FROM courses
WHERE id = 1;

-- Expected: image harus tidak NULL

-- ============================================================================
-- SECTION 2: VERIFIKASI DATA COURSE_MODULES (BAB)
-- ============================================================================

-- 2.1 Lihat semua bab dalam kursus
SELECT 
    cm.id,
    cm.module_order,
    cm.title,
    cm.summary,
    COUNT(l.id) as jumlah_materi
FROM course_modules cm
LEFT JOIN lessons l ON cm.id = l.module_id
WHERE cm.course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')
GROUP BY cm.id
ORDER BY cm.module_order;

-- Expected output: 1 row (Bab 1: Pengenalan Ushul Fiqh) dengan 2 materi

-- 2.2 Cek struktur modul
SELECT 
    id,
    course_id,
    module_order,
    title,
    summary
FROM course_modules
WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar');

-- Expected: module_order = 1, title = 'Pengenalan Ushul Fiqh'

-- ============================================================================
-- SECTION 3: VERIFIKASI DATA LESSONS (MATERI)
-- ============================================================================

-- 3.1 Lihat semua materi dalam bab
SELECT 
    l.id,
    l.lesson_order,
    l.title,
    l.content_type,
    LENGTH(l.content_text) as content_length,
    COUNT(lq.id) as jumlah_soal
FROM lessons l
LEFT JOIN lesson_questions lq ON l.id = lq.lesson_id
WHERE l.course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')
GROUP BY l.id
ORDER BY l.lesson_order;

-- Expected output: 2 rows
-- Row 1: lesson_order=1, title='Pengertian Ushul Fiqh...', jumlah_soal=3
-- Row 2: lesson_order=2, title='Sumber Hukum Islam...', jumlah_soal=3

-- 3.2 Lihat detail konten materi (tanpa content text yang panjang)
SELECT 
    id,
    lesson_order,
    title,
    content_type,
    video_url,
    CASE 
        WHEN content_text IS NOT NULL THEN 'Ada'
        ELSE 'Kosong'
    END as content_status
FROM lessons
WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')
ORDER BY lesson_order;

-- Expected: 2 rows, content_status = 'Ada' untuk keduanya

-- 3.3 Verifikasi panjang konten
SELECT 
    lesson_order,
    title,
    LENGTH(content_text) as content_bytes,
    ROUND(LENGTH(content_text) / 1024, 2) as content_kb
FROM lessons
WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')
ORDER BY lesson_order;

-- Expected: Konten setiap materi minimal 2000+ characters (2+ KB)

-- ============================================================================
-- SECTION 4: VERIFIKASI DATA LESSON_QUESTIONS (SOAL)
-- ============================================================================

-- 4.1 Lihat semua soal
SELECT 
    lq.id,
    l.lesson_order,
    l.title as lesson_title,
    SUBSTRING(lq.question_text, 1, 50) as question_preview,
    COUNT(lo.id) as jumlah_opsi
FROM lesson_questions lq
LEFT JOIN lessons l ON lq.lesson_id = l.id
LEFT JOIN lesson_options lo ON lq.id = lo.question_id
WHERE l.course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')
GROUP BY lq.id
ORDER BY l.lesson_order, lq.id;

-- Expected output: 6 rows (3 soal per materi)
-- Setiap soal harus punya 4 opsi (jumlah_opsi = 4)

-- 4.2 Verifikasi setiap soal punya pembahasan
SELECT 
    id,
    CASE 
        WHEN explanation IS NOT NULL AND explanation != '' THEN 'Ada'
        ELSE 'Kosong - WARNING!'
    END as explanation_status,
    LENGTH(explanation) as explanation_length
FROM lesson_questions
WHERE lesson_id IN (SELECT id FROM lessons WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar'))
ORDER BY id;

-- Expected: Semua baris harus 'Ada' dengan length > 100

-- 4.3 Hitung total soal per materi
SELECT 
    l.lesson_order,
    l.title,
    COUNT(lq.id) as total_soal
FROM lessons l
LEFT JOIN lesson_questions lq ON l.id = lq.lesson_id
WHERE l.course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')
GROUP BY l.id
ORDER BY l.lesson_order;

-- Expected: Materi 1 = 3 soal, Materi 2 = 3 soal

-- ============================================================================
-- SECTION 5: VERIFIKASI DATA LESSON_OPTIONS (PILIHAN JAWABAN)
-- ============================================================================

-- 5.1 Lihat semua opsi jawaban dengan detail
SELECT 
    lq.id as question_id,
    lo.option_label,
    SUBSTRING(lo.option_text, 1, 40) as option_preview,
    lo.is_correct,
    CASE 
        WHEN lo.is_correct = 1 THEN '✓ JAWABAN BENAR'
        ELSE '✗ Jawaban salah'
    END as status
FROM lesson_questions lq
LEFT JOIN lesson_options lo ON lq.id = lo.question_id
WHERE lq.lesson_id IN (SELECT id FROM lessons WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar'))
ORDER BY lq.id, lo.option_label;

-- Expected output: 24 rows (6 soal × 4 opsi)
-- Setiap soal harus punya TEPAT 1 jawaban benar (is_correct = 1)

-- 5.2 Verifikasi setiap soal punya 1 jawaban benar
SELECT 
    lq.id as question_id,
    SUBSTRING(lq.question_text, 1, 40) as question_preview,
    SUM(lo.is_correct) as correct_count,
    CASE 
        WHEN SUM(lo.is_correct) = 1 THEN '✓ VALID'
        WHEN SUM(lo.is_correct) = 0 THEN '✗ TIDAK ADA JAWABAN BENAR!'
        ELSE '✗ LEBIH DARI 1 JAWABAN BENAR!'
    END as status
FROM lesson_questions lq
LEFT JOIN lesson_options lo ON lq.id = lo.question_id
WHERE lq.lesson_id IN (SELECT id FROM lessons WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar'))
GROUP BY lq.id
ORDER BY lq.id;

-- Expected: Semua baris harus status = '✓ VALID'

-- 5.3 Lihat soal dan jawaban benarnya saja
SELECT 
    lq.id,
    SUBSTRING(lq.question_text, 1, 60) as soal,
    lo.option_label,
    lo.option_text as jawaban_benar
FROM lesson_questions lq
JOIN lesson_options lo ON lq.id = lo.question_id AND lo.is_correct = 1
WHERE lq.lesson_id IN (SELECT id FROM lessons WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar'))
ORDER BY lq.id;

-- Expected: 6 rows (1 jawaban per soal)

-- ============================================================================
-- SECTION 6: LAPORAN LENGKAP / SUMMARY
-- ============================================================================

-- 6.1 SUMMARY KOMPREHENSIF
SELECT 
    'COURSES' as kategori,
    COUNT(*) as jumlah
FROM courses
WHERE slug = 'ushul-fiqh-dasar'

UNION ALL

SELECT 
    'MODULES/BAB',
    COUNT(*)
FROM course_modules
WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')

UNION ALL

SELECT 
    'LESSONS/MATERI',
    COUNT(*)
FROM lessons
WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')

UNION ALL

SELECT 
    'QUESTIONS/SOAL',
    COUNT(*)
FROM lesson_questions
WHERE lesson_id IN (SELECT id FROM lessons WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar'))

UNION ALL

SELECT 
    'OPTIONS/PILIHAN',
    COUNT(*)
FROM lesson_options
WHERE question_id IN (SELECT id FROM lesson_questions WHERE lesson_id IN 
    (SELECT id FROM lessons WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')));

-- Expected output (5 rows):
-- COURSES: 1
-- MODULES/BAB: 1
-- LESSONS/MATERI: 2
-- QUESTIONS/SOAL: 6
-- OPTIONS/PILIHAN: 24

-- 6.2 DETAIL REPORT
SELECT 
    'Kursus' as level,
    c.title as nama,
    c.level,
    c.status,
    COUNT(DISTINCT cm.id) as bab,
    COUNT(DISTINCT l.id) as materi,
    COUNT(DISTINCT lq.id) as soal
FROM courses c
LEFT JOIN course_modules cm ON c.id = cm.course_id
LEFT JOIN lessons l ON cm.id = l.module_id
LEFT JOIN lesson_questions lq ON l.id = lq.lesson_id
WHERE c.slug = 'ushul-fiqh-dasar'
GROUP BY c.id;

-- Expected: 1 Kursus, 1 Bab, 2 Materi, 6 Soal

-- ============================================================================
-- SECTION 7: INTEGRITY CHECK (VALIDASI FOREIGN KEYS)
-- ============================================================================

-- 7.1 Cek orphaned lesson_questions (soal tanpa materi)
SELECT 
    lq.id,
    lq.question_text,
    lq.lesson_id
FROM lesson_questions lq
WHERE lq.lesson_id NOT IN (SELECT id FROM lessons)
ORDER BY lq.id;

-- Expected: 0 rows (tidak ada soal orphaned)

-- 7.2 Cek orphaned lesson_options (opsi tanpa soal)
SELECT 
    lo.id,
    lo.question_id
FROM lesson_options lo
WHERE lo.question_id NOT IN (SELECT id FROM lesson_questions)
ORDER BY lo.id;

-- Expected: 0 rows

-- 7.3 Cek orphaned lessons (materi tanpa bab)
SELECT 
    l.id,
    l.title,
    l.module_id
FROM lessons l
WHERE l.module_id NOT IN (SELECT id FROM course_modules)
ORDER BY l.id;

-- Expected: 0 rows

-- ============================================================================
-- SECTION 8: CONTENT QUALITY CHECK
-- ============================================================================

-- 8.1 Cek konten HTML yang valid (basic check)
SELECT 
    id,
    lesson_order,
    title,
    CASE 
        WHEN content_text LIKE '%<h%' THEN 'Ada heading'
        ELSE 'PERLU CEK - Tidak ada heading'
    END as has_heading,
    CASE 
        WHEN content_text LIKE '%<p%' THEN 'Ada paragraph'
        ELSE 'PERLU CEK - Tidak ada paragraph'
    END as has_paragraph,
    CASE 
        WHEN content_text LIKE '%</p>%' THEN '✓ Proper closure'
        ELSE 'PERLU CEK - HTML tidak tertutup'
    END as html_status
FROM lessons
WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')
ORDER BY lesson_order;

-- 8.2 Deteksi soal duplikat
SELECT 
    question_text,
    COUNT(*) as jumlah
FROM lesson_questions
WHERE lesson_id IN (SELECT id FROM lessons WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar'))
GROUP BY question_text
HAVING COUNT(*) > 1;

-- Expected: 0 rows (tidak ada soal duplikat)

-- 8.3 Cek panjang opsi (tidak terlalu pendek/panjang)
SELECT 
    lo.id,
    lq.id as question_id,
    lo.option_label,
    LENGTH(lo.option_text) as length,
    CASE 
        WHEN LENGTH(lo.option_text) < 5 THEN '⚠️ TERLALU PENDEK'
        WHEN LENGTH(lo.option_text) > 200 THEN '⚠️ TERLALU PANJANG'
        ELSE '✓ OK'
    END as length_status
FROM lesson_options lo
JOIN lesson_questions lq ON lo.question_id = lq.id
WHERE lq.lesson_id IN (SELECT id FROM lessons WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar'))
ORDER BY lo.id;

-- Expected: Semua status = '✓ OK'

-- ============================================================================
-- SECTION 9: USAGE SIMULATION
-- ============================================================================

-- 9.1 Simulasi: Student membaca materi 1 dan mengerjakan soal
-- (Tidak insert ke database, hanya preview)
SELECT 
    l.lesson_order as 'Materi #',
    l.title as 'Judul Materi',
    COUNT(lq.id) as 'Jumlah Soal',
    SUM(LENGTH(l.content_text)) / 1024 as 'Content Size (KB)'
FROM lessons l
LEFT JOIN lesson_questions lq ON l.id = lq.lesson_id
WHERE l.course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')
GROUP BY l.id
ORDER BY l.lesson_order;

-- 9.2 Simulasi: Lihat soal yang akan dijawab student
SELECT 
    l.lesson_order,
    l.title as materi,
    lq.id as soal_id,
    SUBSTRING(lq.question_text, 1, 60) as pertanyaan,
    GROUP_CONCAT(CONCAT(lo.option_label, '. ', SUBSTRING(lo.option_text, 1, 30)) SEPARATOR ' | ') as pilihan
FROM lessons l
JOIN lesson_questions lq ON l.id = lq.lesson_id
LEFT JOIN lesson_options lo ON lq.id = lo.question_id
WHERE l.course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')
GROUP BY lq.id
ORDER BY l.lesson_order, lq.id;

-- ============================================================================
-- SECTION 10: TROUBLESHOOTING QUERIES
-- ============================================================================

-- 10.1 Jika ada masalah: Cek ID terakhir yang diinsert
SELECT 
    c.id as course_id,
    MAX(cm.id) as last_module_id,
    MAX(l.id) as last_lesson_id,
    MAX(lq.id) as last_question_id
FROM courses c
LEFT JOIN course_modules cm ON c.id = cm.course_id
LEFT JOIN lessons l ON cm.id = l.module_id
LEFT JOIN lesson_questions lq ON l.id = lq.lesson_id
WHERE c.slug = 'ushul-fiqh-dasar';

-- 10.2 Jika insert failed: Lihat constraints
-- SHOW CREATE TABLE lessons; -- Lihat FK constraints
-- SHOW CREATE TABLE lesson_questions; -- Lihat FK constraints
-- SHOW CREATE TABLE lesson_options; -- Lihat FK constraints

-- 10.3 Jika ada error: Reset AUTO_INCREMENT
-- DELETE FROM courses WHERE id = 1;
-- ALTER TABLE courses AUTO_INCREMENT = 1;
-- Kemudian re-import data

-- ============================================================================
-- FINAL VALIDATION SCRIPT (Jalankan semua sekaligus)
-- ============================================================================

/*
Jika semua query di bawah ini return hasil yang diharapkan,
maka data sampel Ushul Fiqh sudah berhasil diimport dengan sempurna!

Expected Results:
1. Courses count = 1
2. Modules count = 1
3. Lessons count = 2
4. Questions count = 6
5. Options count = 24
6. Setiap soal punya 1 jawaban benar
7. Tidak ada data orphaned
8. Konten HTML valid
9. Tidak ada duplikat
*/

SELECT 'VALIDATION PASSED ✓' as status
WHERE (SELECT COUNT(*) FROM courses WHERE slug = 'ushul-fiqh-dasar') = 1
  AND (SELECT COUNT(*) FROM course_modules WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')) = 1
  AND (SELECT COUNT(*) FROM lessons WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')) = 2
  AND (SELECT COUNT(*) FROM lesson_questions WHERE lesson_id IN (SELECT id FROM lessons WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar'))) = 6
  AND (SELECT COUNT(*) FROM lesson_options WHERE question_id IN (SELECT id FROM lesson_questions WHERE lesson_id IN (SELECT id FROM lessons WHERE course_id = (SELECT id FROM courses WHERE slug = 'ushul-fiqh-dasar')))) = 24;

-- Jika tidak ada output, ada yang tidak sesuai. Run individual queries di atas untuk debug.

-- ============================================================================
-- NEXT STEPS
-- ============================================================================
/*
Setelah semua validation passed:

1. ✓ Login ke aplikasi sebagai ADMIN
2. ✓ Klik Dashboard > Kelola Kursus
3. ✓ Verifikasi Kursus "Ushul Fiqh Dasar" muncul
4. ✓ Klik "Bab" untuk lihat modularnya
5. ✓ Klik "Materi" untuk lihat lesson-nya
6. ✓ Klik "Soal" untuk verifikasi questions

Jika semua tampil dengan baik, maka READY FOR PRODUCTION!

Selanjutnya:
- Tambah 4 BAB lagi (gunakan template_menambah_materi_soal.sql)
- Implementasi fitur sertifikat
- Implementasi achievement badges
- Deploy ke production
*/

-- ============================================================================
-- VERSION: 1.0 | CREATED: 9 Desember 2024
-- ============================================================================
