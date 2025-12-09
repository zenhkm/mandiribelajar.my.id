-- ============================================================================
-- TEMPLATE SCRIPT UNTUK MENAMBAH MATERI DAN SOAL
-- ============================================================================
-- File ini berisi template yang siap pakai untuk menambah data baru
-- Cukup uncomment bagian yang ingin digunakan dan sesuaikan dengan data Anda
-- ============================================================================

-- ============================================================================
-- TEMPLATE 1: MENAMBAH MATERI BARU KE BAB YANG SUDAH ADA
-- ============================================================================

/*
-- Pastikan Anda tahu:
-- @course_id = ID kursus (lihat di tabel courses)
-- @module_id = ID bab (lihat di tabel course_modules)

SET @course_id = 1;                    -- GANTI DENGAN COURSE ID ANDA
SET @module_id = 1;                    -- GANTI DENGAN MODULE ID ANDA
SET @lesson_order = 3;                 -- Urutan materi (3 jika sudah ada 2 materi)

INSERT INTO lessons (course_id, module_id, lesson_order, title, content_type, content_text, video_url)
VALUES (
    @course_id,
    @module_id,
    @lesson_order,
    'Judul Materi Baru',
    'text',
    '<h3>Judul Besar</h3>
    <p>Paragraph pertama...</p>
    <h4>Sub judul</h4>
    <p>Paragraph kedua...</p>
    <ul>
        <li>Poin penting 1</li>
        <li>Poin penting 2</li>
    </ul>',
    NULL                                 -- Jika ada video, ganti NULL dengan URL
);

SET @lesson_id = LAST_INSERT_ID();
*/

-- ============================================================================
-- TEMPLATE 2: MENAMBAH SOAL PILIHAN GANDA KE MATERI
-- ============================================================================

/*
-- Soal 1
INSERT INTO lesson_questions (lesson_id, question_text, explanation)
VALUES (
    @lesson_id,
    'Pertanyaan Anda di sini?',
    'Jelaskan pembahasan jawaban dengan lengkap...'
);

SET @question_id = LAST_INSERT_ID();

-- Opsi A, B, C, D
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct)
VALUES
(@question_id, 'A', 'Teks opsi A (bukan jawaban benar)', 0),
(@question_id, 'B', 'Teks opsi B (bukan jawaban benar)', 0),
(@question_id, 'C', 'Teks opsi C (INI JAWABAN BENAR)', 1),
(@question_id, 'D', 'Teks opsi D (bukan jawaban benar)', 0);
*/

-- ============================================================================
-- TEMPLATE 3: MENAMBAH BAB LENGKAP DENGAN MATERI DAN SOAL
-- ============================================================================

/*
-- Step 1: Tambah Bab Baru
SET @course_id = 1;  -- GANTI DENGAN COURSE ID ANDA

INSERT INTO course_modules (course_id, module_order, title, summary)
VALUES (
    @course_id,
    2,                          -- Urutan bab (2 jika Bab 1 sudah ada)
    'Judul Bab Baru',
    'Ringkasan singkat tentang apa yang akan dipelajari di bab ini'
);

SET @module_id = LAST_INSERT_ID();

-- Step 2: Tambah Materi 1 untuk Bab
INSERT INTO lessons (course_id, module_id, lesson_order, title, content_type, content_text, video_url)
VALUES (
    @course_id,
    @module_id,
    1,
    'Materi 1 - Judul Materi',
    'text',
    '<p>Konten HTML materi 1...</p>',
    NULL
);

SET @lesson_id_1 = LAST_INSERT_ID();

-- Step 3: Tambah Soal untuk Materi 1
INSERT INTO lesson_questions (lesson_id, question_text, explanation)
VALUES (@lesson_id_1, 'Soal 1?', 'Jawaban soal 1');
SET @q1 = LAST_INSERT_ID();
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct) VALUES
(@q1, 'A', 'Opsi A', 0),
(@q1, 'B', 'Opsi B', 1),
(@q1, 'C', 'Opsi C', 0),
(@q1, 'D', 'Opsi D', 0);

-- Step 4: Tambah Materi 2 untuk Bab
INSERT INTO lessons (course_id, module_id, lesson_order, title, content_type, content_text, video_url)
VALUES (
    @course_id,
    @module_id,
    2,
    'Materi 2 - Judul Materi',
    'text',
    '<p>Konten HTML materi 2...</p>',
    NULL
);

SET @lesson_id_2 = LAST_INSERT_ID();

-- Step 5: Tambah Soal untuk Materi 2
INSERT INTO lesson_questions (lesson_id, question_text, explanation)
VALUES (@lesson_id_2, 'Soal 1?', 'Jawaban soal 1');
SET @q2 = LAST_INSERT_ID();
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct) VALUES
(@q2, 'A', 'Opsi A', 0),
(@q2, 'B', 'Opsi B', 1),
(@q2, 'C', 'Opsi C', 0),
(@q2, 'D', 'Opsi D', 0);
*/

-- ============================================================================
-- TEMPLATE 4: SOAL ESAI (JIKA DIPERLUKAN)
-- ============================================================================
-- Catatan: Sistem saat ini mungkin hanya support pilihan ganda
-- Uncomment jika Anda ingin menambah tabel untuk soal esai

/*
CREATE TABLE lesson_essay_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lesson_id INT NOT NULL,
    question_text TEXT,
    explanation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

CREATE TABLE lesson_essay_answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_text TEXT,
    graded_score INT,
    feedback TEXT,
    graded_by INT,
    graded_date TIMESTAMP NULL,
    submitted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES lesson_essay_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL
);
*/

-- ============================================================================
-- TEMPLATE 5: MENAMBAH KURSUS KOMPLEKS (MULTIPLE COURSES)
-- ============================================================================

/*
-- Kursus 1: Ushul Fiqh
INSERT INTO courses (title, slug, image, level, status, description, duration, lessons)
VALUES (
    'Ushul Fiqh Dasar',
    'ushul-fiqh-dasar',
    '1733754000_123.jpg',
    'Pemula',
    'Tersedia',
    'Pelajari dasar-dasar Ushul Fiqh...',
    '5 Jam',
    0
);
SET @course_id_1 = LAST_INSERT_ID();

-- Kursus 2: Fiqh Islam
INSERT INTO courses (title, slug, image, level, status, description, duration, lessons)
VALUES (
    'Fiqh Islam Dasar',
    'fiqh-islam-dasar',
    '1733754001_456.jpg',
    'Pemula',
    'Tersedia',
    'Pelajari hukum-hukum Islam praktis...',
    '6 Jam',
    0
);
SET @course_id_2 = LAST_INSERT_ID();

-- Kursus 3: Hadis
INSERT INTO courses (title, slug, image, level, status, description, duration, lessons)
VALUES (
    'Ilmu Hadis Dasar',
    'ilmu-hadis-dasar',
    '1733754002_789.jpg',
    'Pemula',
    'Segera Hadir',
    'Pelajari cara memahami dan menilai hadis...',
    '4 Jam',
    0
);
SET @course_id_3 = LAST_INSERT_ID();
*/

-- ============================================================================
-- TEMPLATE 6: QUERY UTILITY - MENAMPILKAN DATA YANG ADA
-- ============================================================================

-- Lihat semua kursus
-- SELECT id, title, slug, level, status FROM courses ORDER BY id;

-- Lihat semua bab untuk kursus tertentu
-- SELECT id, module_order, title FROM course_modules WHERE course_id = 1 ORDER BY module_order;

-- Lihat semua materi untuk bab tertentu
-- SELECT id, lesson_order, title FROM lessons WHERE module_id = 1 ORDER BY lesson_order;

-- Lihat semua soal untuk materi tertentu
-- SELECT lq.id, lq.question_text, COUNT(lo.id) as jumlah_opsi 
-- FROM lesson_questions lq
-- LEFT JOIN lesson_options lo ON lq.id = lo.question_id
-- WHERE lq.lesson_id = 1
-- GROUP BY lq.id;

-- ============================================================================
-- TEMPLATE 7: UPDATE DATA YANG SUDAH ADA
-- ============================================================================

/*
-- Update judul kursus
UPDATE courses SET title = 'Judul Baru' WHERE id = 1;

-- Update deskripsi materi
UPDATE lessons SET content_text = '<p>Konten baru...</p>' WHERE id = 1;

-- Update pertanyaan soal
UPDATE lesson_questions SET question_text = 'Pertanyaan baru?' WHERE id = 1;

-- Update opsi jawaban
UPDATE lesson_options SET option_text = 'Opsi baru' WHERE id = 1;
*/

-- ============================================================================
-- TEMPLATE 8: DELETE DATA (HATI-HATI!)
-- ============================================================================

/*
-- Hapus soal (opsi akan otomatis terhapus karena CASCADE)
DELETE FROM lesson_questions WHERE id = 1;

-- Hapus materi (soal akan otomatis terhapus karena CASCADE)
DELETE FROM lessons WHERE id = 1;

-- Hapus bab (materi akan otomatis terhapus karena CASCADE)
DELETE FROM course_modules WHERE id = 1;

-- Hapus kursus (semua data di bawahnya akan terhapus)
DELETE FROM courses WHERE id = 1;
*/

-- ============================================================================
-- TEMPLATE 9: BACKUP DAN RESTORE
-- ============================================================================

-- Backup struktur kursus ke file baru (untuk development staging):
-- mysqldump -u quic1934_zenhkm -p quic1934_kursus courses course_modules lessons lesson_questions lesson_options > backup_kursus.sql

-- Restore dari file backup:
-- mysql -u quic1934_zenhkm -p quic1934_kursus < backup_kursus.sql

-- ============================================================================
-- CONTOH IMPLEMENTASI LENGKAP: TAMBAH KURSUS "TAUHID DASAR"
-- ============================================================================

/*
-- 1. TAMBAH KURSUS
INSERT INTO courses (title, slug, image, level, status, description, duration, lessons)
VALUES (
    'Tauhid Dasar',
    'tauhid-dasar',
    'tauhid-sampul.jpg',
    'Pemula',
    'Tersedia',
    'Pelajari konsep monotheisme dalam Islam, memahami sifat-sifat Allah, dan aqidah Islami yang benar.',
    '3.5 Jam',
    0
);
SET @course_id = LAST_INSERT_ID();

-- 2. TAMBAH BAB 1: KONSEP TAUHID
INSERT INTO course_modules (course_id, module_order, title, summary)
VALUES (@course_id, 1, 'Konsep Tauhid', 'Memahami pengertian dan macam-macam tauhid dalam Islam');
SET @module_id_1 = LAST_INSERT_ID();

-- 3. TAMBAH MATERI 1.1: PENGERTIAN TAUHID
INSERT INTO lessons (course_id, module_id, lesson_order, title, content_type, content_text, video_url)
VALUES (
    @course_id, @module_id_1, 1,
    'Pengertian Tauhid dan Akar Katanya',
    'text',
    '<h3>Definisi Tauhid</h3>
    <p>Tauhid berasal dari kata "Wahid" yang berarti satu. Secara istilah, tauhid adalah meyakini keesaan Allah dalam semua aspek, termasuk dalam sifat, perbuatan, dan ibadah.</p>
    <h4>Macam-Macam Tauhid:</h4>
    <ol>
        <li><strong>Tauhid Rububiyah</strong> - Percaya Allah satu-satunya Tuhan dan Pencipta</li>
        <li><strong>Tauhid Uluhiyah</strong> - Hanya Allah yang layak disembah</li>
        <li><strong>Tauhid Asma wa-Sifat</strong> - Percaya pada nama dan sifat Allah</li>
    </ol>',
    NULL
);
SET @lesson_id_1_1 = LAST_INSERT_ID();

-- 4. TAMBAH SOAL UNTUK MATERI 1.1
INSERT INTO lesson_questions (lesson_id, question_text, explanation)
VALUES (
    @lesson_id_1_1,
    'Apa arti dari kata "Tauhid"?',
    'Tauhid berasal dari bahasa Arab "Wahid" yang berarti satu, mengacu pada keesaan Allah dalam semua aspek.'
);
SET @q1 = LAST_INSERT_ID();
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct) VALUES
(@q1, 'A', 'Percaya pada banyak tuhan', 0),
(@q1, 'B', 'Meyakini keesaan Allah', 1),
(@q1, 'C', 'Sistem kepercayaan', 0),
(@q1, 'D', 'Ritual keagamaan', 0);

-- 5. TAMBAH MATERI 1.2: SIFAT-SIFAT ALLAH
INSERT INTO lessons (course_id, module_id, lesson_order, title, content_type, content_text, video_url)
VALUES (
    @course_id, @module_id_1, 2,
    'Sifat-Sifat Allah dan Nama-Namanya',
    'text',
    '<h3>Asma-ul-Husna (Nama-Nama Indah Allah)</h3>
    <p>Allah memiliki 99 nama yang mulia. Setiap nama mencerminkan sifat dan kualitas sempurna Allah.</p>
    <h4>Contoh Nama-Nama Allah:</h4>
    <ul>
        <li><strong>Allah</strong> - Nama terbesar</li>
        <li><strong>Ar-Rahman</strong> - Yang Maha Pengasih</li>
        <li><strong>Al-Qawi</strong> - Yang Maha Kuat</li>
        <li><strong>Al-Alim</strong> - Yang Maha Mengetahui</li>
    </ul>',
    NULL
);
SET @lesson_id_1_2 = LAST_INSERT_ID();

INSERT INTO lesson_questions (lesson_id, question_text, explanation)
VALUES (
    @lesson_id_1_2,
    'Berapa jumlah nama-nama indah Allah (Asma-ul-Husna)?',
    'Allah memiliki 99 nama yang mulia yang disebut Asma-ul-Husna. Mempelajari dan memahami nama-nama Allah adalah bagian penting dari ibadah dalam Islam.'
);
SET @q2 = LAST_INSERT_ID();
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct) VALUES
(@q2, 'A', '7 nama', 0),
(@q2, 'B', '50 nama', 0),
(@q2, 'C', '99 nama', 1),
(@q2, 'D', '999 nama', 0);

-- KURSUS TAUHID DASAR SELESAI!
-- Total: 1 Kursus, 1 Bab, 2 Materi, 2 Soal
-- Anda bisa terus menambah bab dan materi dengan template di atas
*/

-- ============================================================================
-- CATATAN PENTING
-- ============================================================================
/*
1. SET @variable = value; digunakan untuk menyimpan ID terakhir
2. LAST_INSERT_ID() mengambil ID otomatis yang baru saja dibuat
3. Selalu gunakan transaction jika insert multiple related records:
   BEGIN;
   INSERT ...;
   INSERT ...;
   COMMIT;

4. Foreign Key constraints memastikan data integritas:
   - Jika hapus course, semua modules/lessons/questions ikut terhapus (CASCADE)
   - Jika reference tidak ada, INSERT akan gagal

5. HTML content di content_text harus valid:
   - Gunakan <h3>, <h4> untuk heading
   - Gunakan <p>, <ul>, <ol> untuk paragraph dan list
   - Jangan lupa close semua tag HTML

6. Option dengan is_correct = 1 adalah jawaban benar (hanya 1 per soal)
*/

-- ============================================================================
-- VERSI TERBARU: 1.0
-- TERAKHIR DIUPDATE: 9 Desember 2024
-- ============================================================================
