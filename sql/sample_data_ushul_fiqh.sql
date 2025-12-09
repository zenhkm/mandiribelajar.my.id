-- ============================================================================
-- DATA SAMPEL KURSUS USHUL FIQH
-- ============================================================================
-- Script ini membuat contoh data untuk 1 Kursus dengan 1 Bab berisi 2 Materi
-- Gunakan untuk perencanaan dan testing sebelum menambah data lengkap
-- ============================================================================

-- 1. INSERT KURSUS
-- Status bisa 'Tersedia' atau 'Segera Hadir'
INSERT INTO courses (title, slug, image, level, status, description, duration, lessons) VALUES
(
    'Ushul Fiqh Dasar',
    'ushul-fiqh-dasar',
    '1733754000_123.jpg',  -- Ganti dengan nama file gambar yang ada di folder uploads/
    'Pemula',
    'Tersedia',
    'Pelajari dasar-dasar Ushul Fiqh (Ilmu Kaidah Hukum Islam) untuk memahami bagaimana Hukum Islam dibentuk dari Al-Quran, Hadis, Ijma, dan Qiyas.',
    '4 Jam',
    2
);

-- Ambil ID kursus yang baru saja dibuat (gunakan LAST_INSERT_ID())
SET @course_id = LAST_INSERT_ID();

-- 2. INSERT BAB (MODUL)
-- Satu bab untuk sampel
INSERT INTO course_modules (course_id, module_order, title, summary) VALUES
(@course_id, 1, 'Pengenalan Ushul Fiqh', 'Memahami definisi, ruang lingkup, dan pentingnya mempelajari Ushul Fiqh dalam Islam');

-- Ambil ID modul yang baru saja dibuat
SET @module_id = LAST_INSERT_ID();

-- 3. INSERT MATERI (LESSONS) - 2 materi dalam 1 bab
-- MATERI 1: Pengertian dan Ruang Lingkup Ushul Fiqh
INSERT INTO lessons (course_id, module_id, lesson_order, title, content_type, content_text, video_url) VALUES
(
    @course_id,
    @module_id,
    1,
    'Pengertian Ushul Fiqh dan Ruang Lingkupnya',
    'text',
    '<h3>Definisi Ushul Fiqh</h3>
<p><strong>Ushul Fiqh</strong> secara bahasa berasal dari dua kata:</p>
<ul>
<li><strong>Ushul</strong> = Akar, dasar, fondasi</li>
<li><strong>Fiqh</strong> = Pemahaman mendalam tentang hukum</li>
</ul>

<p>Jadi, Ushul Fiqh adalah ilmu yang membahas metode, prinsip, dan kaidah untuk menggali dan menetapkan hukum Islam dari sumber-sumbernya yang asli.</p>

<h3>Sumber-Sumber Hukum Islam (Adillah Syariah)</h3>
<ol>
<li><strong>Al-Quran</strong> - Firman Allah yang diturunkan kepada Nabi Muhammad</li>
<li><strong>Hadis (Sunnah)</strong> - Perkataan, perbuatan, dan taqrir Nabi Muhammad</li>
<li><strong>Ijma</strong> - Konsensus (kesepakatan) para ulama dalam menetapkan hukum</li>
<li><strong>Qiyas</strong> - Analogi atau perbandingan kasus baru dengan kasus yang sudah ada hukumnya</li>
</ol>

<h3>Ruang Lingkup Ushul Fiqh</h3>
<p>Ushul Fiqh mempelajari:</p>
<ul>
<li>Teori tentang Al-Quran (pengumpulan, pembacaan, aspek umum dan khusus)</li>
<li>Teori tentang Hadis (kesahihan, penerimaan, nasakh)</li>
<li>Konsep Ijma dan Qiyas serta metode penerapannya</li>
<li>Kaidah-kaidah interpretasi dan istinbat (penggalian hukum)</li>
<li>Teori tentang mujtahid dan ijtihad</li>
</ul>

<h3>Manfaat Mempelajari Ushul Fiqh</h3>
<ul>
<li>✓ Memahami cara berijtihad para ulama klasik</li>
<li>✓ Mampu menganalisis hukum Islam secara sistematis</li>
<li>✓ Dapat membedakan antara hukum yang pasti dan yang relatif</li>
<li>✓ Memperkuat pemahaman tentang fleksibilitas Syariah Islam</li>
<li>✓ Dasar untuk menjadi seorang mujtahid modern</li>
</ul>

<h3>Hubungan Ushul Fiqh dengan Fiqh</h3>
<p><strong>Ushul Fiqh</strong> adalah metode (cara) untuk mendapatkan hukum, sedangkan <strong>Fiqh</strong> adalah hasil dari metode tersebut. Analogi sederhananya:</p>
<ul>
<li>Ushul Fiqh = Resep masakan (cara membuat)</li>
<li>Fiqh = Hidangan yang sudah jadi</li>
</ul>',
    NULL  -- Tidak ada video URL untuk materi ini
);

-- Ambil ID materi 1
SET @lesson_id_1 = LAST_INSERT_ID();

-- MATERI 2: Sumber Hukum Islam - Al-Quran
INSERT INTO lessons (course_id, module_id, lesson_order, title, content_type, content_text, video_url) VALUES
(
    @course_id,
    @module_id,
    2,
    'Sumber Hukum Islam: Al-Quran dan Hadis',
    'text',
    '<h3>Al-Quran sebagai Sumber Hukum Utama</h3>

<p><strong>Al-Quran</strong> adalah sumber hukum Islam yang paling utama dan tidak tertandingi. Semua umat Islam meyakini keabsahan Al-Quran sebagai sumber syariah.</p>

<h4>Keunggulan Al-Quran</h4>
<ul>
<li>Tersimpan dengan sempurna dalam hafalan jutaan umat Islam</li>
<li>Terjaga dalam bentuk manuskrip kuno yang dapat diverifikasi</li>
<li>Mengandung hukum-hukum yang jelas dan terperinci</li>
<li>Berlaku untuk semua tempat dan waktu (universal)</li>
<li>Dilengkapi dengan contoh-contoh praktis dari kehidupan</li>
</ul>

<h4>Jenis Ayat-Ayat Hukum dalam Al-Quran</h4>
<ol>
<li><strong>Ayat Qath''i (pasti)</strong> - Maknanya tidak bisa diragukan, hanya satu interpretasi</li>
<li><strong>Ayat Dzanni (spekulatif)</strong> - Bisa memiliki lebih dari satu interpretasi, perlu ijtihad</li>
</ol>

<h3>Hadis Sebagai Sumber Hukum Kedua</h3>

<p><strong>Hadis</strong> atau <strong>Sunnah</strong> adalah perkataan, perbuatan, dan ketetapan Nabi Muhammad SAW yang menjadi sumber syariah kedua setelah Al-Quran.</p>

<h4>Kategori Hadis Berdasarkan Kesahihan</h4>
<ul>
<li><strong>Hadis Sahih</strong> - Hadis yang diterima tanpa keraguan, sanadnya bersambung dengan perawi adil dan dhabit</li>
<li><strong>Hadis Hasan</strong> - Hadis yang mudah diterima, hampir separah dengan hadis sahih</li>
<li><strong>Hadis Dha''if (lemah)</strong> - Hadis yang tidak memenuhi kriteria sahih atau hasan</li>
<li><strong>Hadis Maudhu'' (palsu)</strong> - Hadis yang diperkuat dengan dusta, tidak boleh dirujuk</li>
</ul>

<h4>Fungsi Hadis dalam Hukum Islam</h4>
<ol>
<li><strong>Menerangkan (Tafsir)</strong> - Menjelaskan ayat Al-Quran yang masih umum</li>
<li><strong>Menambah (Idhafah)</strong> - Menambah ketentuan hukum yang belum dijelaskan Al-Quran</li>
<li><strong>Membatasi (Takhsis)</strong> - Membatasi ayat umum Al-Quran</li>
<li><strong>Menghapus (Nasakh)</strong> - Membatalkan atau mengubah ketentuan sebelumnya</li>
</ol>

<h4>Syarat Hadis Dapat Dijadikan Hujjah (Dalil)</h4>
<ul>
<li>Sanadnya bersambung (mutassil)</li>
<li>Perawi harus adil dan dhabit (hafal dan teliti)</li>
<li>Hadisnya tidak cacat (illa) dalam sanad maupun matan</li>
<li>Hadisnya bukan hadis yang asing (ghorib) tanpa pendukung</li>
</ul>

<h3>Perbandingan Al-Quran dan Hadis</h3>
<table border="1" cellpadding="10" cellspacing="0">
<thead>
<tr>
<th>Aspek</th>
<th>Al-Quran</th>
<th>Hadis</th>
</tr>
</thead>
<tbody>
<tr>
<td>Sumber</td>
<td>Langsung dari Allah</td>
<td>Dari Nabi Muhammad</td>
</tr>
<tr>
<td>Keniscayaan</td>
<td>Mutawatir (pasti)</td>
<td>Bisa Ahad (riwayat tunggal)</td>
</tr>
<tr>
<td>Keabadian</td>
<td>Kekal sampai hari kiamat</td>
<td>Terbatas pada masa hidup Nabi</td>
</tr>
<tr>
<td>Prioritas</td>
<td>Tertinggi (Pertama)</td>
<td>Kedua setelah Al-Quran</td>
</tr>
</tbody>
</table>',
    NULL
);

-- Ambil ID materi 2
SET @lesson_id_2 = LAST_INSERT_ID();

-- 4. INSERT SOAL UNTUK MATERI 1 (Pengertian Ushul Fiqh)
-- Soal 1 - Pilihan Ganda
INSERT INTO lesson_questions (lesson_id, question_text, explanation) VALUES
(@lesson_id_1, 'Apa arti dari kata "Ushul" dalam "Ushul Fiqh"?', 'Kata "Ushul" dalam bahasa Arab berarti akar, dasar, atau fondasi. Dalam konteks Ushul Fiqh, ini merujuk pada prinsip-prinsip dasar atau metode untuk memahami dan menggali hukum Islam.');

-- Ambil ID soal 1
SET @question_id = LAST_INSERT_ID();

-- Insert opsi jawaban untuk soal 1
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct) VALUES
(@question_id, 'A', 'Akar, dasar, atau fondasi', 1),
(@question_id, 'B', 'Hukum atau undang-undang', 0),
(@question_id, 'C', 'Pemahaman mendalam', 0),
(@question_id, 'D', 'Interpretasi ayat', 0);

-- Soal 2
INSERT INTO lesson_questions (lesson_id, question_text, explanation) VALUES
(@lesson_id_1, 'Berapa jumlah sumber hukum Islam (Adillah Syariah) yang utama?', 'Sumber hukum Islam yang utama ada 4, yaitu: 1) Al-Quran, 2) Hadis/Sunnah, 3) Ijma (kesepakatan ulama), dan 4) Qiyas (analogi). Keempat sumber ini disebut dengan Adillah Syariah Al-Ashliyah (sumber hukum utama).');

SET @question_id = LAST_INSERT_ID();
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct) VALUES
(@question_id, 'A', '2 sumber', 0),
(@question_id, 'B', '3 sumber', 0),
(@question_id, 'C', '4 sumber', 1),
(@question_id, 'D', '5 sumber', 0);

-- Soal 3
INSERT INTO lesson_questions (lesson_id, question_text, explanation) VALUES
(@lesson_id_1, 'Manakah pernyataan berikut yang TIDAK termasuk dalam ruang lingkup Ushul Fiqh?', 'Ushul Fiqh mempelajari teori tentang Al-Quran, Hadis, Ijma, Qiyas, kaidah interpretasi, dan teori tentang mujtahid. Namun, teori ekonomi modern BUKAN bagian dari ruang lingkup Ushul Fiqh karena tidak berkaitan langsung dengan metode penetapan hukum Islam.');

SET @question_id = LAST_INSERT_ID();
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct) VALUES
(@question_id, 'A', 'Teori tentang Al-Quran', 0),
(@question_id, 'B', 'Teori tentang mujtahid dan ijtihad', 0),
(@question_id, 'C', 'Kaidah-kaidah interpretasi hukum', 0),
(@question_id, 'D', 'Teori ekonomi modern', 1);

-- 5. INSERT SOAL UNTUK MATERI 2 (Sumber Hukum: Al-Quran dan Hadis)
-- Soal 1
INSERT INTO lesson_questions (lesson_id, question_text, explanation) VALUES
(@lesson_id_2, 'Sumber hukum Islam mana yang paling utama dan tidak tertandingi?', 'Al-Quran adalah sumber hukum Islam yang paling utama karena merupakan firman Allah yang diturunkan langsung kepada Nabi Muhammad SAW. Tidak ada sumber hukum lain yang lebih tinggi derajatnya dari Al-Quran.');

SET @question_id = LAST_INSERT_ID();
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct) VALUES
(@question_id, 'A', 'Al-Quran', 1),
(@question_id, 'B', 'Hadis', 0),
(@question_id, 'C', 'Ijma', 0),
(@question_id, 'D', 'Qiyas', 0);

-- Soal 2
INSERT INTO lesson_questions (lesson_id, question_text, explanation) VALUES
(@lesson_id_2, 'Apa yang dimaksud dengan "Hadis Sahih"?', 'Hadis Sahih adalah hadis yang diterima tanpa keraguan karena memiliki kriteria kesahihan yang ketat: sanadnya bersambung, perawi bersifat adil (jujur) dan dhabit (hafal), tidak ada cacat dalam sanad maupun teks hadis, dan bukan hadis ghorib (aneh) tanpa dukungan perawi lain.');

SET @question_id = LAST_INSERT_ID();
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct) VALUES
(@question_id, 'A', 'Hadis yang diriwayatkan oleh satu orang', 0),
(@question_id, 'B', 'Hadis yang diterima tanpa keraguan dengan sanad bersambung', 1),
(@question_id, 'C', 'Hadis yang diucapkan langsung oleh Khalifah', 0),
(@question_id, 'D', 'Hadis yang paling panjang matan-nya', 0);

-- Soal 3
INSERT INTO lesson_questions (lesson_id, question_text, explanation) VALUES
(@lesson_id_2, 'Fungsi Hadis dalam menerangkan ayat Al-Quran disebut dengan istilah apa?', 'Fungsi Hadis untuk menerangkan atau menjelaskan ayat Al-Quran disebut "Tafsir" atau "Bayan". Contohnya, Hadis menjelaskan cara melaksanakan shalat yang dijelaskan secara umum dalam Al-Quran.');

SET @question_id = LAST_INSERT_ID();
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct) VALUES
(@question_id, 'A', 'Nasakh', 0),
(@question_id, 'B', 'Tafsir atau Bayan', 1),
(@question_id, 'C', 'Idhafah', 0),
(@question_id, 'D', 'Takhsis', 0);

-- ============================================================================
-- RINGKASAN DATA YANG TELAH DITAMBAHKAN
-- ============================================================================
-- ✓ 1 Kursus: "Ushul Fiqh Dasar"
-- ✓ 1 Bab: "Pengenalan Ushul Fiqh"
-- ✓ 2 Materi:
--   - Materi 1: "Pengertian Ushul Fiqh dan Ruang Lingkupnya"
--   - Materi 2: "Sumber Hukum Islam: Al-Quran dan Hadis"
-- ✓ 6 Soal (3 soal per materi dengan 4 opsi pilihan ganda setiap soalnya)
-- ============================================================================

-- CATATAN:
-- 1. Jika Anda sudah punya gambar di folder uploads/, ganti nama file di bagian INSERT courses
-- 2. Untuk menambah bab lagi, tinggal tambah INSERT ke course_modules dengan course_id yang sama
-- 3. Untuk menambah materi, gunakan module_id dan course_id yang sesuai
-- 4. Untuk menambah soal, gunakan lesson_id yang sesuai
-- ============================================================================
