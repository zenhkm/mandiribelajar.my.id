/*
================================================================================
RENCANA PENGEMBANGAN KURSUS USHUL FIQH
================================================================================
Dokumen ini berisi rencana lengkap untuk pengembangan Kursus Ushul Fiqh
setelah penyelesaian semua materi.

CATATAN: Data sampel saat ini hanya 1 BAB dengan 2 MATERI
         Gunakan sebagai template untuk perluasan kursus
================================================================================
*/

-- ============================================================================
-- FASE 1: PENAMBAHAN BAB LENGKAP (DALAM KURSUS YANG SAMA)
-- ============================================================================
-- Rencana menambah 4 BAB lagi (total 5 BAB untuk Ushul Fiqh)

/*
BAB 1: ✓ SUDAH ADA "Pengenalan Ushul Fiqh" (2 materi)
       - Materi 1: Pengertian Ushul Fiqh
       - Materi 2: Sumber Hukum (Al-Quran & Hadis)

BAB 2: "Ijma dan Qiyas" (2-3 materi)
       - Materi 1: Pengertian dan Syarat Ijma
       - Materi 2: Macam-macam Ijma
       - Materi 3: Pengertian dan Syarat Qiyas

BAB 3: "Teori Abrogasi (Nasakh) dan Khilaf" (2 materi)
       - Materi 1: Nasakh dalam Al-Quran
       - Materi 2: Perbedaan Pendapat dan Khilaf Fuqaha

BAB 4: "Ijtihad dan Mujtahid" (2 materi)
       - Materi 1: Definisi dan Syarat Ijtihad
       - Materi 2: Tingkatan Mujtahid

BAB 5: "Aplikasi Praktis Ushul Fiqh" (2 materi)
       - Materi 1: Contoh Istinbat Hukum Modern
       - Materi 2: Studi Kasus Fatwa Kontemporer
*/

-- ============================================================================
-- FASE 2: SCRIPT SQL UNTUK MENAMBAH BAB BERIKUTNYA
-- ============================================================================

-- Template untuk menambah BAB 2 (Ijma dan Qiyas):
-- Uncomment dan sesuaikan jika ingin menjalankan

/*
-- BAB 2
INSERT INTO course_modules (course_id, module_order, title, summary) 
VALUES (1, 2, 'Ijma dan Qiyas', 'Memahami konsep Ijma (kesepakatan ulama) dan Qiyas (analogi) sebagai sumber hukum Islam');

SET @module_id_2 = LAST_INSERT_ID();

-- Materi 1 BAB 2: Pengertian dan Syarat Ijma
INSERT INTO lessons (course_id, module_id, lesson_order, title, content_type, content_text, video_url)
VALUES 
(1, @module_id_2, 1, 'Pengertian Ijma', 'text', '[ISI KONTEN IJMA]', NULL),
(1, @module_id_2, 2, 'Syarat Ijma', 'text', '[ISI KONTEN SYARAT IJMA]', NULL),
(1, @module_id_2, 3, 'Qiyas dan Aplikasinya', 'text', '[ISI KONTEN QIYAS]', NULL);
*/

-- ============================================================================
-- FASE 3: FITUR DAN FUNGSIONALITAS SETELAH PENYELESAIAN MATERI
-- ============================================================================

/*
Setelah siswa menyelesaikan semua materi dalam 1 BAB, sistem bisa:

1. SERTIFIKAT PENYELESAIAN
   - Buat tabel: course_certificates
   - Berisi: user_id, course_id, certificate_no, issued_date
   - Download PDF sertifikat

2. PENCAPAIAN DAN BADGE
   - Buat tabel: user_achievements
   - Tracking: badge_type, earned_date, points
   - Display: Progress bar, medal icon

3. UJIAN AKHIR BAB
   - Buat tabel: module_exams
   - Berisi: module_id, total_questions, passing_score
   - User harus lulus ujian untuk melanjut ke BAB berikutnya

4. QUIZ HARIAN / DAILY CHALLENGE
   - Soal random dari semua materi yang sudah dikerjakan
   - Insentif: Points, streak badges

5. FORUM DISKUSI
   - Tabel: forum_topics, forum_replies
   - Diskusi per materi atau per BAB
   - Moderator dan answer verification

6. LEADERBOARD / RANKING
   - Top students by points
   - By course completion percentage
   - By quiz score
*/

-- ============================================================================
-- FASE 4: QUERY UTAMA UNTUK FITUR-FITUR BARU
-- ============================================================================

-- 1. Progress Tracking - Cek progres siswa per bab
/*
SELECT 
    u.id,
    u.username,
    cm.id as module_id,
    cm.title as bab_title,
    COUNT(DISTINCT l.id) as total_lessons,
    COUNT(DISTINCT up.lesson_id) as completed_lessons,
    ROUND((COUNT(DISTINCT up.lesson_id) / COUNT(DISTINCT l.id)) * 100) as progress_percentage
FROM users u
LEFT JOIN user_progress up ON u.id = up.user_id
LEFT JOIN lessons l ON up.lesson_id = l.id
LEFT JOIN course_modules cm ON l.module_id = cm.id
WHERE l.course_id = 1  -- Course ID Ushul Fiqh
GROUP BY u.id, cm.id
ORDER BY u.username, cm.module_order;
*/

-- 2. Scoring Sistem - Hitung score siswa per materi
/*
SELECT 
    u.id,
    u.username,
    l.id as lesson_id,
    l.title as lesson_title,
    COUNT(lq.id) as total_questions,
    SUM(CASE WHEN ur.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers,
    ROUND((SUM(CASE WHEN ur.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(lq.id)) * 100) as score
FROM users u
LEFT JOIN user_responses ur ON u.id = ur.user_id
LEFT JOIN lesson_questions lq ON ur.question_id = lq.id
LEFT JOIN lessons l ON lq.lesson_id = l.id
WHERE l.course_id = 1  -- Course ID Ushul Fiqh
GROUP BY u.id, l.id
ORDER BY u.username, l.lesson_order;
*/

-- 3. Tingkat Kesulitan Soal - Analisis soal berdasarkan answer rate
/*
SELECT 
    lq.id as question_id,
    lq.question_text,
    COUNT(ur.id) as total_attempts,
    SUM(CASE WHEN ur.is_correct = 1 THEN 1 ELSE 0 END) as correct_count,
    ROUND((SUM(CASE WHEN ur.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(ur.id)) * 100) as difficulty_rate
FROM lesson_questions lq
LEFT JOIN user_responses ur ON lq.id = ur.question_id
WHERE lq.lesson_id IN (SELECT id FROM lessons WHERE course_id = 1)
GROUP BY lq.id
HAVING COUNT(ur.id) > 0
ORDER BY difficulty_rate ASC;  -- Soal tersulit di atas
*/

-- ============================================================================
-- FASE 5: TABEL TAMBAHAN YANG MUNGKIN DIPERLUKAN
-- ============================================================================

/*
CREATE TABLE module_exams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    module_id INT NOT NULL,
    title VARCHAR(255),
    total_questions INT DEFAULT 10,
    passing_score INT DEFAULT 70,
    duration_minutes INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE
);

CREATE TABLE user_achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    achievement_type ENUM('first_lesson', 'module_complete', 'perfect_score', 'streak', 'helpful_comment'),
    achievement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    points INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE user_certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    certificate_number VARCHAR(50) UNIQUE,
    issued_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE forum_topics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    module_id INT,
    lesson_id INT,
    user_id INT NOT NULL,
    title VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE SET NULL,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE forum_replies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    topic_id INT NOT NULL,
    user_id INT NOT NULL,
    reply_text TEXT,
    is_helpful BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES forum_topics(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
*/

-- ============================================================================
-- CHECKLIST IMPLEMENTASI UNTUK PENGEMBANGAN SELANJUTNYA
-- ============================================================================

/*
□ Tambah 4 BAB lagi (Total 5 BAB untuk Kursus Ushul Fiqh)
□ Setiap BAB minimal 2-3 materi dengan konten lengkap
□ Setiap materi minimal 3-4 soal pilihan ganda
□ Implementasi Sertifikat Penyelesaian
□ Implementasi Badge & Achievement System
□ Implementasi Ujian Akhir BAB (Module Final Exam)
□ Implementasi Quiz Harian / Daily Challenge
□ Implementasi Forum Diskusi per Materi
□ Implementasi Leaderboard / Ranking System
□ Implementasi Rating & Review System
□ Implementasi Video Tutorial (untuk materi visual)
□ Implementasi Bookmark / Save Progress
□ Implementasi Email Notification untuk mengingatkan pembelajaran
□ Implementasi Analytics Dashboard untuk Admin
□ Testing & Quality Assurance
*/

-- ============================================================================
-- ESTIMASI WAKTU & RESOURCES
-- ============================================================================

/*
Kegiatan                          Estimasi Waktu    Prioritas
─────────────────────────────────────────────────────────────
1. Penambahan Konten BAB 2-5      4-6 minggu       TINGGI
2. Sertifikat & Badge             1-2 minggu       TINGGI
3. Module Final Exam              2-3 minggu       TINGGI
4. Forum Diskusi                  2-3 minggu       SEDANG
5. Daily Challenge & Leaderboard  2-3 minggu       SEDANG
6. Video Integration              3-4 minggu       RENDAH
7. Analytics Dashboard            2-3 minggu       SEDANG
8. Testing & Deployment           2-3 minggu       TINGGI
─────────────────────────────────────────────────────────────
TOTAL ESTIMASI                    20-30 minggu      -

Resources yang diperlukan:
- Content Writer (3 orang untuk konten materi & soal)
- Video Producer (1 orang jika ada video)
- QA Tester (1-2 orang)
- Database Designer (1 orang)
*/

-- ============================================================================
-- CATATAN PENTING
-- ============================================================================

/*
1. BACKUP DATA SEBELUM RUNNING SCRIPT INI
   Pastikan Anda sudah backup database sebelum menjalankan perubahan

2. KOORDINASI DENGAN STAKEHOLDER
   Pastikan rencana ini sudah disetujui oleh admin dan tim

3. TESTING DI ENVIRONMENT DEVELOPMENT
   Jangan langsung ke production, test di development dulu

4. DOCUMENTATION
   Update dokumentasi setiap ada perubahan fitur atau database

5. VERSION CONTROL
   Simpan semua script SQL di version control (Git)
*/
