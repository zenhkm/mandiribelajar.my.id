-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 10 Des 2025 pada 06.03
-- Versi server: 11.4.8-MariaDB-cll-lve
-- Versi PHP: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quic1934_kursus`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `level` varchar(50) NOT NULL,
  `status` enum('Tersedia','Segera Hadir') NOT NULL DEFAULT 'Tersedia',
  `description` text DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `lessons` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `courses`
--

INSERT INTO `courses` (`id`, `slug`, `title`, `image`, `level`, `status`, `description`, `duration`, `lessons`) VALUES
(1, 'waris', 'Kursus Ilmu Waris Dasar', '1764903941_245.png', 'Pemula', 'Tersedia', 'Belajar ilmu waris langkah demi langkah: dari pengantar, ahli waris, ashabul furud, sampai latihan kasus.', '8–12 jam belajar efektif', 12),
(3, 'nahwu', 'Nahwu', NULL, 'Pemula', 'Segera Hadir', 'Belajar Nahwu bertahap', '8 jam', 0),
(4, 'balaghah', 'Balaghah', NULL, 'Pemula', 'Segera Hadir', 'Be;ajar Balaghah secara bertahap', '12 Jam', 0),
(5, 'ushul-fiqh-dasar', 'Ushul Fiqh Dasar', '1733754000_123.jpg', 'Pemula', 'Tersedia', 'Pelajari dasar-dasar Ushul Fiqh (Ilmu Kaidah Hukum Islam) untuk memahami bagaimana Hukum Islam dibentuk dari Al-Quran, Hadis, Ijma, dan Qiyas.', '4 Jam', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `course_modules`
--

CREATE TABLE `course_modules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `module_order` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `course_modules`
--

INSERT INTO `course_modules` (`id`, `course_id`, `module_order`, `title`, `summary`) VALUES
(1, 1, 1, 'Bab 1 – Pengantar Ilmu Waris', 'Pengertian ilmu waris, dasar hukum, syarat dan rukun pewarisan.'),
(2, 1, 2, 'Bab 2 – Ashabul Furud', 'Definisi ashabul furud, daftar ahli waris laki-laki & perempuan, dan bagian mereka.'),
(3, 1, 3, 'Bab 3 – ‘Ashabah & Dzawil Arham', 'Macam-macam ashabah, prioritas ashabah, dan sekilas tentang dzawil arham.'),
(4, 1, 4, 'Bab 4 – Langkah Praktis Hitung Waris', 'Cara menyusun data kasus dan membagi harta melalui contoh.'),
(5, 1, 5, 'Bab 5 – Latihan Kasus & Evaluasi', 'Latihan teori, praktik hitung kasus, dan evaluasi akhir kursus.'),
(6, 1, 6, 'Bab 6: Ujian Akhir Ilmu Waris', 'Bab penutup berisi ujian akhir untuk menguji penguasaan Ashabul Furud, \'Ashabah, Dzawil Arham, dan kasus praktis.'),
(7, 3, 1, 'Bab 1: Pendahuluan', 'Pengenalan dasar mata pelajaran ini.'),
(8, 3, 2, 'Bab 2: Pembahasan Inti', 'Materi utama dan praktek.'),
(9, 5, 1, 'Pengenalan Ushul Fiqh', 'Memahami definisi, ruang lingkup, dan pentingnya mempelajari Ushul Fiqh dalam Islam');

-- --------------------------------------------------------

--
-- Struktur dari tabel `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `lesson_order` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content_type` enum('text','video','mixed') NOT NULL DEFAULT 'text',
  `content_text` longtext DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `lessons`
--

INSERT INTO `lessons` (`id`, `course_id`, `module_id`, `lesson_order`, `title`, `content_type`, `content_text`, `video_url`, `created_at`, `updated_at`) VALUES
(2, 1, 1, 1, 'Pengantar Ilmu Waris', 'text', 'Pada materi ini kita mengenal pengertian ilmu waris, dasar hukumnya, dan hikmahnya.\r\n\r\n1. Pengertian ilmu waris: ilmu yang membahas pembagian harta peninggalan orang yang meninggal kepada ahli warisnya sesuai aturan syariat.\r\n2. Dasar hukum ilmu waris bersumber dari Al-Qur\'an, Sunnah Nabi, dan ijma\' ulama.\r\n3. Hikmah mempelajari ilmu waris: menjaga keadilan, mencegah sengketa keluarga, dan menunaikan hak-hak yang diwajibkan Allah.', NULL, '2025-11-26 06:31:21', '2025-11-26 06:31:21'),
(3, 1, 1, 2, 'Syarat dan Penghalang Waris', 'text', 'Pada materi ini dibahas syarat terjadinya pewarisan dan hal-hal yang menghalangi seseorang menerima warisan.\r\n\r\nPoin-poin penting:\r\n1. Syarat pewarisan:\r\n   - Mati pewaris secara hakiki atau hukum.\r\n   - Hidupnya ahli waris saat pewaris meninggal.\r\n   - Adanya hubungan yang menyebabkan pewarisan: nasab, pernikahan, atau wala\'.\r\n2. Penghalang waris yang masyhur:\r\n   - Perbedaan agama.\r\n   - Pembunuhan yang tidak dibenarkan.\r\n   - Budak (dalam pembahasan klasik).\r\n3. Orang yang terhalang waris tetap berhak mendapatkan nafkah atau wasiat (dalam batas tertentu) bila disepakati keluarga.', NULL, '2025-11-26 06:31:21', '2025-11-26 06:31:21'),
(4, 1, 1, 3, 'Macam-macam Ahli Waris', 'text', 'Pada materi ini dibahas pembagian ahli waris secara umum.\r\n\r\nPoin-poin penting:\r\n1. Dilihat dari jenis kelamin:\r\n   - Ahli waris laki-laki.\r\n   - Ahli waris perempuan.\r\n2. Dilihat dari jenis hak:\r\n   - Ashabul furud: ahli waris yang bagiannya sudah ditentukan (seperdua, seperempat, dsb.).\r\n   - \'Ashabah: ahli waris yang mendapatkan sisa setelah ashabul furud.\r\n3. Setiap ahli waris memiliki syarat kemasukan dan penghalang tersendiri dalam bab waris.', NULL, '2025-11-26 06:31:21', '2025-11-26 06:31:21'),
(5, 1, 2, 1, 'Definisi dan Anggota Ashabul Furud', 'text', 'Ashabul Furud adalah ahli waris yang bagiannya disebutkan secara pasti dalam Al-Qur’an, seperti separuh, seperempat, seperdelapan, dua pertiga, sepertiga, dan seperenam.\r\n\r\nAnggota Ashabul Furud terdiri dari 12 pihak, sebagaimana disepakati para ulama.\r\n\r\nPihak laki-laki yang menjadi Ashabul Furud ada 4:\r\n1. Suami \r\n2. Ayah \r\n3. Kakek \r\n4. Saudara laki-laki seibu\r\n\r\nPihak perempuan yang menjadi Ashabul Furud ada 8:\r\n1. Istri\r\n2. Ibu\r\n3. Nenek dari jalur ibu\r\n4. Nenek dari jalur ayah\r\n5. Anak perempuan\r\n6. Cucu perempuan dari anak laki-laki\r\n7. Saudara perempuan sekandung\r\n8. Saudara perempuan seayah\r\n\r\nBagian Ashabul Furud berubah sesuai kondisi ahli waris lain.\r\n\r\nAshabul Furud mendapatkan bagian sebelum ’Ashabah.', NULL, '2025-11-26 06:41:21', '2025-11-26 06:41:21'),
(6, 1, 2, 2, 'Macam-macam Bagian Ashabul Furud', 'text', 'Bagian Ashabul Furud yang disebutkan dalam Al-Qur’an ada enam jenis: ½, ¼, ⅛, ⅔, ⅓, dan ⅙.\r\n\r\nBagian ½: suami tanpa anak; anak perempuan tunggal; cucu perempuan tunggal.\r\n\r\nBagian ¼: suami jika pewaris punya anak; istri jika pewaris tanpa anak.\r\n\r\nBagian ⅛: istri jika pewaris punya anak.\r\n\r\nBagian ⅔: dua anak perempuan atau lebih; dua cucu perempuan atau lebih; dua saudara perempuan sekandung; dua saudara perempuan seayah.\r\n\r\nBagian ⅓: ibu tanpa adanya anak dan tanpa dua saudara; saudara seibu jika dua atau lebih.\r\n\r\nBagian ⅙: ibu dengan hadirnya anak; ayah jika pewaris punya anak; kakek; saudara seibu tunggal; cucu perempuan jika terhalang.', NULL, '2025-11-26 06:41:21', '2025-11-26 06:41:21'),
(7, 1, 2, 3, 'Catatan Penting tentang Ashabul Furud', 'text', 'Ashabul Furud dapat terhalang oleh pihak lain dan dapat berubah menjadi ’Ashabah dalam beberapa kondisi.\r\n\r\nPenghalang waris: perbedaan agama, pembunuhan yang tidak dibenarkan, status budak.\r\n\r\nContoh berubah menjadi ’Ashabah:\r\n1. Anak perempuan bersama anak laki-laki.\r\n2. Saudara perempuan bersama saudara laki-laki.\r\n\r\nJika bagian Ashabul Furud melebihi harta → *aul*.\r\n\r\nJika bagian Ashabul Furud kurang dari harta dan tidak ada ’Ashabah → radd.\r\n\r\nPemahaman Ashabul Furud adalah dasar utama sebelum bab ’Ashabah.', NULL, '2025-11-26 06:41:21', '2025-11-26 06:41:21'),
(8, 1, 3, 1, 'Definisi dan Pembagian \'Ashabah', 'text', 'Ashabah adalah ahli waris yang mendapatkan sisa harta setelah diberikan kepada Ashabul Furud.\r\n\r\nJika tidak ada Ashabul Furud, maka harta dibagikan seluruhnya kepada \'Ashabah.\r\n\r\n\'Ashabah terbagi menjadi tiga:\r\n1. \'Ashabah bi nafsi\r\n2. \'Ashabah bil ghair\r\n3. \'Ashabah ma\'al ghair.\r\n\r\nBagian \'Ashabah adalah sisa harta, bukan bagian pasti.', NULL, '2025-11-26 06:48:40', '2025-11-26 06:48:40'),
(9, 1, 3, 2, 'Ashabah bi Nafsi', 'text', 'Ashabah bi nafsi adalah ahli waris laki-laki yang mewaris tanpa bantuan orang lain.\r\n\r\nContoh:\r\n1. Anak laki-laki\r\n2. Cucu laki-laki\r\n3. Ayah\r\n4. Kakek\r\n5. Saudara laki-laki sekandung\r\n6. Paman\r\n\r\n\'Ashabah bi nafsi mengambil seluruh sisa setelah Ashabul Furud.', NULL, '2025-11-26 06:48:40', '2025-11-26 06:48:40'),
(10, 1, 3, 3, 'Ashabah bil Ghair dan Ma\'al Ghair', 'text', 'Ashabah bil ghair: perempuan menjadi Ashabah karena hadirnya laki-laki sederajat.\r\n\r\nContoh:\r\n- Anak perempuan bersama anak laki-laki\r\n- Saudara perempuan bersama saudara laki-laki.\r\n\r\nAshabah ma`al ghair: perempuan jadi `Ashabah bersama perempuan lain yang lebih kuat, misalnya saudara perempuan bersama anak perempuan.', NULL, '2025-11-26 06:48:40', '2025-11-26 06:48:40'),
(11, 1, 4, 1, 'Dzawil Arham: Definisi dan Dasar', 'text', 'Dzawil Arham adalah kerabat yang memiliki hubungan rahim namun tidak termasuk Ashabul Furud dan tidak termasuk \'Ashabah.\r\n\r\nMereka hanya mewarisi jika tidak ada Ashabul Furud maupun \'Ashabah.\r\n\r\nDalil: \"Wa ulul arhaami ba’dhuhum awlaa biba’dhin\" (QS. Al-Anfal: 75).\r\n\r\nContoh Dzawil Arham:\r\n1. Anak dari saudara perempuan\r\n2. Bibi dari pihak ibu\r\n3. Paman dari pihak ibu\r\n4. Cucu dari anak perempuan\r\n\r\nDzawil Arham lebih lemah kedudukannya dibanding Ashabul Furud dan \'Ashabah.', NULL, '2025-11-26 06:52:14', '2025-11-26 06:52:14'),
(12, 1, 4, 2, 'Kelompok-kelompok Dzawil Arham', 'text', 'Dzawil Arham terdiri dari tiga kelompok utama:\r\n\r\n1. Keturunan dari perempuan (misalnya cucu dari anak perempuan)\r\n2. Keluarga dari pihak ibu (misalnya bibi atau paman pihak ibu)\r\n3. Kerabat dekat yang terhubung melalui perempuan (misalnya anak saudara perempuan)\r\n\r\nMereka tidak memiliki bagian pasti, dan hanya mendapat warisan jika tidak ada ahli waris lainnya.', NULL, '2025-11-26 06:52:14', '2025-11-26 06:52:14'),
(13, 1, 4, 3, 'Cara Pembagian Dzawil Arham', 'text', 'Dzawil Arham diwarisi dengan metode at-tartib (berjenjang) atau metode al-qurba (kedekatan).\r\n\r\nMetode at-tartib: mendahulukan kelompok terdekat dengan pewaris.\r\n\r\nMetode al-qurba: siapa yang lebih dekat, dia mendapat lebih dulu.\r\n\r\nDzawil Arham tidak mewaris bersama Ashabul Furud atau \'Ashabah.\r\n\r\nJika tingkatnya sama, harta dibagi rata.\r\nJika tingkat berbeda, yang lebih dekat didahulukan.', NULL, '2025-11-26 06:52:14', '2025-11-26 06:52:14'),
(14, 1, 5, 1, 'Kasus Dasar: Ashabul Furud Saja', 'text', 'Kasus dasar waris terjadi ketika ahli waris hanya terdiri dari Ashabul Furud.\r\n\r\nContoh: suami, ibu, dua anak perempuan.\r\n\r\nSuami mendapat 1/4 karena ada anak.\r\nIbu mendapat 1/6 karena ada anak.\r\nDua anak perempuan mendapat 2/3.\r\n\r\nJika bagian Ashabul Furud lengkap, pembagian selesai tanpa sisa.\r\n\r\nJika total bagian melebihi harta, maka dilakukan \'aul.', NULL, '2025-11-26 10:55:07', '2025-11-26 10:55:07'),
(15, 1, 5, 2, 'Kasus Gabungan: Ashabul Furud + \'Ashabah', 'text', 'Kasus gabungan terjadi jika ada ahli waris Ashabul Furud dan juga \'Ashabah.\r\n\r\nContoh: istri, ayah, satu anak laki-laki, satu anak perempuan.\r\n\r\nIstri mendapat 1/8.\r\nAyah mendapat 1/6.\r\nAnak laki-laki dan perempuan menjadi \'Ashabah bil ghair.\r\n\r\nSisanya dibagi dengan rumus: laki-laki mendapat dua bagian perempuan.', NULL, '2025-11-26 10:55:07', '2025-11-26 10:55:07'),
(16, 1, 5, 3, 'Kasus Tanpa Ashabul Furud & \'Ashabah: Dzawil Arham', 'text', 'Jika tidak ada Ashabul Furud dan \'Ashabah, maka Dzawil Arham mewaris.\r\n\r\nContoh ahli waris: anak perempuan dari saudara perempuan, bibi dari pihak ibu.\r\n\r\nDzawil Arham mewaris memakai metode at-tartib atau al-qurba.\r\n\r\nYang lebih dekat dengan pewaris didahulukan.', NULL, '2025-11-26 10:55:07', '2025-11-26 10:55:07'),
(35, 1, 6, 1, 'Ujian Akhir Bagian 1: Teori Dasar', 'text', 'Ujian Akhir Bagian 1 berisi soal-soal konsep dasar Ilmu Waris.\r\n\r\nTermasuk:\r\n1. Ashabul Furud\r\n2. \'Ashabah\r\n3. Penghalang waris\r\n4. Kaidah prioritas waris.\r\n\r\nJawablah seluruh pertanyaan di bawah.', NULL, '2025-11-30 08:50:32', '2025-11-30 08:50:32'),
(36, 1, 6, 2, 'Ujian Akhir Bagian 2: Kasus Sedang', 'text', 'Ujian Bagian 2 berisi soal-soal penggabungan Ashabul Furud dan \'Ashabah.\r\n\r\nKasus:\r\n1. Suami/istri + anak\r\n2. Ayah + anak\r\n3. Ibu + saudara\r\n4. Pembagian sisa (ta\'sib).', NULL, '2025-11-30 08:50:32', '2025-11-30 08:50:32'),
(37, 1, 6, 3, 'Ujian Akhir Bagian 3: Kasus Lanjut', 'text', 'Ujian tingkat lanjut.\r\n\r\nMeliputi:\r\n1. Dzawil Arham\r\n2. Kasus \'aul\r\n3. Kasus radd\r\n4. Kasus multi generasi.', NULL, '2025-11-30 08:50:32', '2025-11-30 08:50:32'),
(38, 5, 9, 1, 'Pengertian Ushul Fiqh dan Ruang Lingkupnya', 'text', '<h3>Definisi Ushul Fiqh</h3>\r\n<p><strong>Ushul Fiqh</strong> secara bahasa berasal dari dua kata:</p>\r\n<ul>\r\n<li><strong>Ushul</strong> = Akar, dasar, fondasi</li>\r\n<li><strong>Fiqh</strong> = Pemahaman mendalam tentang hukum</li>\r\n</ul>\r\n\r\n<p>Jadi, Ushul Fiqh adalah ilmu yang membahas metode, prinsip, dan kaidah untuk menggali dan menetapkan hukum Islam dari sumber-sumbernya yang asli.</p>\r\n\r\n<h3>Sumber-Sumber Hukum Islam (Adillah Syariah)</h3>\r\n<ol>\r\n<li><strong>Al-Quran</strong> - Firman Allah yang diturunkan kepada Nabi Muhammad</li>\r\n<li><strong>Hadis (Sunnah)</strong> - Perkataan, perbuatan, dan taqrir Nabi Muhammad</li>\r\n<li><strong>Ijma</strong> - Konsensus (kesepakatan) para ulama dalam menetapkan hukum</li>\r\n<li><strong>Qiyas</strong> - Analogi atau perbandingan kasus baru dengan kasus yang sudah ada hukumnya</li>\r\n</ol>\r\n\r\n<h3>Ruang Lingkup Ushul Fiqh</h3>\r\n<p>Ushul Fiqh mempelajari:</p>\r\n<ul>\r\n<li>Teori tentang Al-Quran (pengumpulan, pembacaan, aspek umum dan khusus)</li>\r\n<li>Teori tentang Hadis (kesahihan, penerimaan, nasakh)</li>\r\n<li>Konsep Ijma dan Qiyas serta metode penerapannya</li>\r\n<li>Kaidah-kaidah interpretasi dan istinbat (penggalian hukum)</li>\r\n<li>Teori tentang mujtahid dan ijtihad</li>\r\n</ul>\r\n\r\n<h3>Manfaat Mempelajari Ushul Fiqh</h3>\r\n<ul>\r\n<li>✓ Memahami cara berijtihad para ulama klasik</li>\r\n<li>✓ Mampu menganalisis hukum Islam secara sistematis</li>\r\n<li>✓ Dapat membedakan antara hukum yang pasti dan yang relatif</li>\r\n<li>✓ Memperkuat pemahaman tentang fleksibilitas Syariah Islam</li>\r\n<li>✓ Dasar untuk menjadi seorang mujtahid modern</li>\r\n</ul>\r\n\r\n<h3>Hubungan Ushul Fiqh dengan Fiqh</h3>\r\n<p><strong>Ushul Fiqh</strong> adalah metode (cara) untuk mendapatkan hukum, sedangkan <strong>Fiqh</strong> adalah hasil dari metode tersebut. Analogi sederhananya:</p>\r\n<ul>\r\n<li>Ushul Fiqh = Resep masakan (cara membuat)</li>\r\n<li>Fiqh = Hidangan yang sudah jadi</li>\r\n</ul>', NULL, '2025-12-09 23:54:56', '2025-12-09 23:54:56'),
(39, 5, 9, 2, 'Sumber Hukum Islam: Al-Quran dan Hadis', 'text', '<h3>Al-Quran sebagai Sumber Hukum Utama</h3>\r\n\r\n<p><strong>Al-Quran</strong> adalah sumber hukum Islam yang paling utama dan tidak tertandingi. Semua umat Islam meyakini keabsahan Al-Quran sebagai sumber syariah.</p>\r\n\r\n<h4>Keunggulan Al-Quran</h4>\r\n<ul>\r\n<li>Tersimpan dengan sempurna dalam hafalan jutaan umat Islam</li>\r\n<li>Terjaga dalam bentuk manuskrip kuno yang dapat diverifikasi</li>\r\n<li>Mengandung hukum-hukum yang jelas dan terperinci</li>\r\n<li>Berlaku untuk semua tempat dan waktu (universal)</li>\r\n<li>Dilengkapi dengan contoh-contoh praktis dari kehidupan</li>\r\n</ul>\r\n\r\n<h4>Jenis Ayat-Ayat Hukum dalam Al-Quran</h4>\r\n<ol>\r\n<li><strong>Ayat Qath\'i (pasti)</strong> - Maknanya tidak bisa diragukan, hanya satu interpretasi</li>\r\n<li><strong>Ayat Dzanni (spekulatif)</strong> - Bisa memiliki lebih dari satu interpretasi, perlu ijtihad</li>\r\n</ol>\r\n\r\n<h3>Hadis Sebagai Sumber Hukum Kedua</h3>\r\n\r\n<p><strong>Hadis</strong> atau <strong>Sunnah</strong> adalah perkataan, perbuatan, dan ketetapan Nabi Muhammad SAW yang menjadi sumber syariah kedua setelah Al-Quran.</p>\r\n\r\n<h4>Kategori Hadis Berdasarkan Kesahihan</h4>\r\n<ul>\r\n<li><strong>Hadis Sahih</strong> - Hadis yang diterima tanpa keraguan, sanadnya bersambung dengan perawi adil dan dhabit</li>\r\n<li><strong>Hadis Hasan</strong> - Hadis yang mudah diterima, hampir separah dengan hadis sahih</li>\r\n<li><strong>Hadis Dha\'if (lemah)</strong> - Hadis yang tidak memenuhi kriteria sahih atau hasan</li>\r\n<li><strong>Hadis Maudhu\' (palsu)</strong> - Hadis yang diperkuat dengan dusta, tidak boleh dirujuk</li>\r\n</ul>\r\n\r\n<h4>Fungsi Hadis dalam Hukum Islam</h4>\r\n<ol>\r\n<li><strong>Menerangkan (Tafsir)</strong> - Menjelaskan ayat Al-Quran yang masih umum</li>\r\n<li><strong>Menambah (Idhafah)</strong> - Menambah ketentuan hukum yang belum dijelaskan Al-Quran</li>\r\n<li><strong>Membatasi (Takhsis)</strong> - Membatasi ayat umum Al-Quran</li>\r\n<li><strong>Menghapus (Nasakh)</strong> - Membatalkan atau mengubah ketentuan sebelumnya</li>\r\n</ol>\r\n\r\n<h4>Syarat Hadis Dapat Dijadikan Hujjah (Dalil)</h4>\r\n<ul>\r\n<li>Sanadnya bersambung (mutassil)</li>\r\n<li>Perawi harus adil dan dhabit (hafal dan teliti)</li>\r\n<li>Hadisnya tidak cacat (illa) dalam sanad maupun matan</li>\r\n<li>Hadisnya bukan hadis yang asing (ghorib) tanpa pendukung</li>\r\n</ul>\r\n\r\n<h3>Perbandingan Al-Quran dan Hadis</h3>\r\n<table border=\"1\" cellpadding=\"10\" cellspacing=\"0\">\r\n<thead>\r\n<tr>\r\n<th>Aspek</th>\r\n<th>Al-Quran</th>\r\n<th>Hadis</th>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td>Sumber</td>\r\n<td>Langsung dari Allah</td>\r\n<td>Dari Nabi Muhammad</td>\r\n</tr>\r\n<tr>\r\n<td>Keniscayaan</td>\r\n<td>Mutawatir (pasti)</td>\r\n<td>Bisa Ahad (riwayat tunggal)</td>\r\n</tr>\r\n<tr>\r\n<td>Keabadian</td>\r\n<td>Kekal sampai hari kiamat</td>\r\n<td>Terbatas pada masa hidup Nabi</td>\r\n</tr>\r\n<tr>\r\n<td>Prioritas</td>\r\n<td>Tertinggi (Pertama)</td>\r\n<td>Kedua setelah Al-Quran</td>\r\n</tr>\r\n</tbody>\r\n</table>', NULL, '2025-12-09 23:54:56', '2025-12-09 23:54:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `lesson_options`
--

CREATE TABLE `lesson_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_label` char(1) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `lesson_options`
--

INSERT INTO `lesson_options` (`id`, `question_id`, `option_label`, `option_text`, `is_correct`) VALUES
(9, 3, 'A', 'Meninggalnya pewaris dan hidupnya ahli waris saat pewaris meninggal.', 1),
(10, 3, 'B', 'Adanya wasiat tertulis dari pewaris.', 0),
(11, 3, 'C', 'Ahli waris sudah baligh dan bekerja.', 0),
(12, 3, 'D', 'Harta pewaris harus berupa tanah.', 0),
(13, 4, 'A', 'Kedekatan hubungan nasab.', 0),
(14, 4, 'B', 'Perbedaan agama antara pewaris dan ahli waris.', 1),
(15, 4, 'C', 'Ahli waris masih kecil (belum baligh).', 0),
(16, 4, 'D', 'Ahli waris belum menikah.', 0),
(17, 5, 'A', 'Ahli waris yang mendapat bagian sisa setelah pembagian.', 0),
(18, 5, 'B', 'Ahli waris yang bagiannya sudah ditentukan dalam nash.', 1),
(19, 5, 'C', 'Semua kerabat laki-laki pewaris.', 0),
(20, 5, 'D', 'Semua kerabat perempuan pewaris.', 0),
(21, 6, 'A', 'Ashabul furud selalu mendapat bagian lebih besar dari \'ashabah.', 0),
(22, 6, 'B', 'Ashabul furud mendapat bagian tetap, sisa harta diberikan kepada \'ashabah.', 1),
(23, 6, 'C', 'Ashabul furud baru mendapat bagian setelah \'ashabah.', 0),
(24, 6, 'D', 'Ashabul furud dan \'ashabah selalu mendapat bagian yang sama.', 0),
(25, 7, 'A', '\'Ashabah', 1),
(26, 7, 'B', 'Dzawil arham', 0),
(27, 7, 'C', 'Suami', 0),
(28, 7, 'D', 'Ibu', 0),
(29, 7, 'E', 'Anak perempuan', 0),
(30, 8, 'A', 'Tiga jenis', 1),
(31, 8, 'B', 'Dua jenis', 0),
(32, 8, 'C', 'Empat jenis', 0),
(33, 8, 'D', 'Lima jenis', 0),
(34, 8, 'E', 'Enam jenis', 0),
(35, 9, 'A', 'Anak laki-laki', 1),
(36, 9, 'B', 'Anak perempuan', 0),
(37, 9, 'C', 'Ibu', 0),
(38, 9, 'D', 'Saudara seibu', 0),
(39, 9, 'E', 'Suami', 0),
(40, 10, 'A', 'Mendapat seluruh harta', 1),
(41, 10, 'B', 'Setengah harta', 0),
(42, 10, 'C', 'Seperdelapan', 0),
(43, 10, 'D', 'Sepertiga', 0),
(44, 10, 'E', 'Tidak mendapat bagian', 0),
(45, 11, 'A', 'Bersama anak laki-laki', 1),
(46, 11, 'B', 'Bersama ibu', 0),
(47, 11, 'C', 'Bersama paman', 0),
(48, 11, 'D', 'Bersama suami', 0),
(49, 11, 'E', 'Bersama kakek', 0),
(50, 12, 'A', 'Saudara perempuan sekandung bersama anak perempuan', 1),
(51, 12, 'B', 'Anak perempuan bersama anak laki-laki', 0),
(52, 12, 'C', 'Suami bersama istri', 0),
(53, 12, 'D', 'Cucu perempuan bersama cucu laki-laki', 0),
(54, 12, 'E', 'Nenek bersama ayah', 0),
(55, 13, 'A', 'Kerabat rahim yang bukan Ashabul Furud dan bukan \'Ashabah', 1),
(56, 13, 'B', 'Semua kerabat laki-laki', 0),
(57, 13, 'C', 'Ashabul Furud perempuan', 0),
(58, 13, 'D', 'Saudara kandung', 0),
(59, 13, 'E', 'Ayah dan ibu', 0),
(60, 14, 'A', 'Tidak ada Ashabul Furud maupun \'Ashabah', 1),
(61, 14, 'B', 'Tidak ada anak', 0),
(62, 14, 'C', 'Tidak ada cucu', 0),
(63, 14, 'D', 'Ada wasiat', 0),
(64, 14, 'E', 'Tidak ada harta', 0),
(65, 15, 'A', 'Cucu dari anak perempuan', 1),
(66, 15, 'B', 'Paman sekandung', 0),
(67, 15, 'C', 'Anak laki-laki', 0),
(68, 15, 'D', 'Saudara laki-laki', 0),
(69, 15, 'E', 'Suami', 0),
(70, 16, 'A', 'Tiga kelompok', 1),
(71, 16, 'B', 'Dua kelompok', 0),
(72, 16, 'C', 'Empat kelompok', 0),
(73, 16, 'D', 'Lima kelompok', 0),
(74, 16, 'E', 'Enam kelompok', 0),
(75, 17, 'A', 'Metode at-tartib atau al-qurba', 1),
(76, 17, 'B', 'Metode qiyas', 0),
(77, 17, 'C', 'Metode \'aul', 0),
(78, 17, 'D', 'Metode radd', 0),
(79, 17, 'E', 'Metode hisab', 0),
(80, 18, 'A', 'Mewarisi jika tidak ada Ashabul Furud dan \'Ashabah', 1),
(81, 18, 'B', 'Mewarisi seluruh harta', 0),
(82, 18, 'C', 'Mendapat bagian pasti', 0),
(83, 18, 'D', 'Didahulukan dari Ashabul Furud', 0),
(84, 18, 'E', 'Bagian laki-laki dua kali lipat perempuan', 0),
(85, 19, 'A', '2/3', 1),
(86, 19, 'B', '1/2', 0),
(87, 19, 'C', '1/4', 0),
(88, 19, 'D', '1/6', 0),
(89, 19, 'E', 'Tidak mendapat bagian', 0),
(90, 20, 'A', '\'Aul', 1),
(91, 20, 'B', 'Radd', 0),
(92, 20, 'C', 'Ta\'sib', 0),
(93, 20, 'D', 'Hudzudz', 0),
(94, 20, 'E', 'Musyarakah', 0),
(95, 21, 'A', '1/8', 1),
(96, 21, 'B', '1/4', 0),
(97, 21, 'C', '1/2', 0),
(98, 21, 'D', '1/6', 0),
(99, 21, 'E', 'Tidak mendapat bagian', 0),
(100, 22, 'A', '\'Ashabah bil ghair', 1),
(101, 22, 'B', 'Ashabul Furud', 0),
(102, 22, 'C', 'Dzawil Arham', 0),
(103, 22, 'D', 'Dzawil Asabah', 0),
(104, 22, 'E', 'Mustahik', 0),
(105, 23, 'A', 'Tidak ada Ashabul Furud maupun \'Ashabah', 1),
(106, 23, 'B', 'Ada anak perempuan', 0),
(107, 23, 'C', 'Ada suami', 0),
(108, 23, 'D', 'Ada ayah', 0),
(109, 23, 'E', 'Ada cucu laki-laki', 0),
(110, 24, 'A', 'At-tartib dan al-qurba', 1),
(111, 24, 'B', '\'Aul dan radd', 0),
(112, 24, 'C', 'Qiyas dan istihsan', 0),
(113, 24, 'D', 'Ijma\' dan qiyas', 0),
(114, 24, 'E', 'Hisab dan maslahah', 0),
(115, 25, 'A', 'Ibu', 1),
(116, 25, 'B', 'Anak laki-laki', 0),
(117, 25, 'C', 'Cucu laki-laki', 0),
(118, 25, 'D', 'Saudara seayah', 0),
(119, 25, 'E', 'Paman', 0),
(120, 26, 'A', '\'Ashabah bi nafsi', 1),
(121, 26, 'B', 'Ashabul Furud', 0),
(122, 26, 'C', 'Dzawil Arham', 0),
(123, 26, 'D', 'Mustahik', 0),
(124, 26, 'E', 'Hamba sahaya', 0),
(125, 27, 'A', '1/4', 1),
(126, 27, 'B', '1/2', 0),
(127, 27, 'C', '1/8', 0),
(128, 27, 'D', '1/6', 0),
(129, 27, 'E', 'Tidak mendapat bagian', 0),
(130, 28, 'A', 'Pewaris punya anak', 1),
(131, 28, 'B', 'Pewaris tidak punya saudara', 0),
(132, 28, 'C', 'Pewaris meninggalkan cucu', 0),
(133, 28, 'D', 'Pewaris meninggalkan nenek', 0),
(134, 28, 'E', 'Pewaris meninggalkan paman', 0),
(135, 29, 'A', '\'Ashabah bil ghair', 1),
(136, 29, 'B', 'Dzawil Arham', 0),
(137, 29, 'C', 'Ashabul Furud', 0),
(138, 29, 'D', 'Suami istri', 0),
(139, 29, 'E', 'Nenek', 0),
(140, 32, 'A', 'Ibu', 1),
(141, 32, 'B', 'Anak laki-laki', 0),
(142, 32, 'C', 'Cucu laki-laki', 0),
(143, 32, 'D', 'Saudara seayah', 0),
(144, 32, 'E', 'Paman', 0),
(145, 33, 'A', 'Ashabah bi nafsi', 1),
(146, 33, 'B', 'Ashabul Furud', 0),
(147, 33, 'C', 'Dzawil Arham', 0),
(148, 33, 'D', 'Mustahik', 0),
(149, 33, 'E', 'Hamba sahaya', 0),
(150, 34, 'A', '1/4', 1),
(151, 34, 'B', '1/2', 0),
(152, 34, 'C', '1/8', 0),
(153, 34, 'D', '1/6', 0),
(154, 34, 'E', 'Tidak mendapat bagian', 0),
(155, 35, 'A', 'Jika pewaris mempunyai anak', 1),
(156, 35, 'B', 'Jika pewaris tidak punya saudara', 0),
(157, 35, 'C', 'Jika pewaris meninggalkan cucu', 0),
(158, 35, 'D', 'Jika pewaris meninggalkan nenek', 0),
(159, 35, 'E', 'Jika pewaris meninggalkan paman', 0),
(160, 36, 'A', 'Ashabah bil ghair', 1),
(161, 36, 'B', 'Dzawil Arham', 0),
(162, 36, 'C', 'Ashabul Furud', 0),
(163, 36, 'D', 'Suami dan istri', 0),
(164, 36, 'E', 'Nenek', 0),
(165, 37, 'A', '1/8', 1),
(166, 37, 'B', '1/4', 0),
(167, 37, 'C', '1/2', 0),
(168, 37, 'D', '2/3', 0),
(169, 37, 'E', '1/6', 0),
(170, 38, 'A', '1/6 ditambah sisa', 1),
(171, 38, 'B', '1/3', 0),
(172, 38, 'C', '1/2', 0),
(173, 38, 'D', 'Tidak mendapat bagian', 0),
(174, 38, 'E', '1/4', 0),
(175, 39, 'A', 'Laki-laki dua kali bagian perempuan', 1),
(176, 39, 'B', 'Sama rata', 0),
(177, 39, 'C', 'Perempuan dua kali laki-laki', 0),
(178, 39, 'D', 'Hanya laki-laki yang mendapat bagian', 0),
(179, 39, 'E', 'Hanya perempuan yang mendapat bagian', 0),
(180, 40, 'A', '1/6', 1),
(181, 40, 'B', '1/3', 0),
(182, 40, 'C', '1/2', 0),
(183, 40, 'D', '2/3', 0),
(184, 40, 'E', '1/8', 0),
(185, 41, 'A', '1/8', 1),
(186, 41, 'B', '1/4', 0),
(187, 41, 'C', '1/6', 0),
(188, 41, 'D', '2/3', 0),
(189, 41, 'E', '1/2', 0),
(190, 42, 'A', 'Jika tidak ada Ashabul Furud dan Ashabah', 1),
(191, 42, 'B', 'Jika ada anak perempuan', 0),
(192, 42, 'C', 'Jika ada suami', 0),
(193, 42, 'D', 'Jika ada ayah', 0),
(194, 42, 'E', 'Jika ada cucu laki-laki', 0),
(195, 43, 'A', 'Aul', 1),
(196, 43, 'B', 'Radd', 0),
(197, 43, 'C', 'Al-qurba', 0),
(198, 43, 'D', 'Tartib', 0),
(199, 43, 'E', 'Taksir', 0),
(200, 44, 'A', 'Jika tidak ada Ashabah dan ada sisa harta', 1),
(201, 44, 'B', 'Jika total bagian melebihi harta', 0),
(202, 44, 'C', 'Jika ada anak laki-laki', 0),
(203, 44, 'D', 'Jika ada ayah', 0),
(204, 44, 'E', 'Jika ada paman', 0),
(205, 45, 'A', 'Kerabat yang paling dekat derajatnya', 1),
(206, 45, 'B', 'Kerabat yang paling jauh derajatnya', 0),
(207, 45, 'C', 'Kerabat yang belum menikah', 0),
(208, 45, 'D', 'Kerabat yang paling tua', 0),
(209, 45, 'E', 'Kerabat perempuan saja', 0),
(210, 46, 'A', 'Dzawil Arham', 1),
(211, 46, 'B', 'Ashabul Furud', 0),
(212, 46, 'C', 'Ashabah', 0),
(213, 46, 'D', 'Orang tua', 0),
(214, 46, 'E', 'Saudara kandung', 0),
(215, 201, 'A', 'Ashabul Furud', 1),
(216, 201, 'B', '\'Ashabah', 0),
(217, 201, 'C', 'Dzawil Arham', 0),
(218, 201, 'D', 'Hijab', 0),
(219, 202, 'A', 'Nenek dari Ibu', 0),
(220, 202, 'B', 'Suami', 1),
(221, 202, 'C', 'Ibu', 0),
(222, 202, 'D', 'Cucu Perempuan', 0),
(223, 203, 'A', '12 Golongan', 1),
(224, 203, 'B', '5 Golongan', 0),
(225, 203, 'C', '8 Golongan', 0),
(226, 203, 'D', '15 Golongan', 0),
(227, 204, 'A', 'Paman', 0),
(228, 204, 'B', 'Anak Laki-laki', 0),
(229, 204, 'C', 'Ayah', 1),
(230, 204, 'D', 'Cucu Laki-laki', 0),
(231, 205, 'A', 'Istri tidak memiliki anak', 0),
(232, 205, 'B', 'Istri memiliki anak atau cucu', 1),
(233, 205, 'C', 'Istri meninggalkan hutang', 0),
(234, 205, 'D', 'Suami menikah lagi', 0),
(235, 206, 'A', '1/2 (Setengah)', 0),
(236, 206, 'B', '1/8 (Seperdelapan)', 0),
(237, 206, 'C', '1/5 (Seperlima)', 1),
(238, 206, 'D', '2/3 (Dua pertiga)', 0),
(239, 207, 'A', 'Akar, dasar, atau fondasi', 1),
(240, 207, 'B', 'Hukum atau undang-undang', 0),
(241, 207, 'C', 'Pemahaman mendalam', 0),
(242, 207, 'D', 'Interpretasi ayat', 0),
(243, 208, 'A', '2 sumber', 0),
(244, 208, 'B', '3 sumber', 0),
(245, 208, 'C', '4 sumber', 1),
(246, 208, 'D', '5 sumber', 0),
(247, 209, 'A', 'Teori tentang Al-Quran', 0),
(248, 209, 'B', 'Teori tentang mujtahid dan ijtihad', 0),
(249, 209, 'C', 'Kaidah-kaidah interpretasi hukum', 0),
(250, 209, 'D', 'Teori ekonomi modern', 1),
(251, 210, 'A', 'Al-Quran', 1),
(252, 210, 'B', 'Hadis', 0),
(253, 210, 'C', 'Ijma', 0),
(254, 210, 'D', 'Qiyas', 0),
(255, 211, 'A', 'Hadis yang diriwayatkan oleh satu orang', 0),
(256, 211, 'B', 'Hadis yang diterima tanpa keraguan dengan sanad bersambung', 1),
(257, 211, 'C', 'Hadis yang diucapkan langsung oleh Khalifah', 0),
(258, 211, 'D', 'Hadis yang paling panjang matan-nya', 0),
(259, 212, 'A', 'Nasakh', 0),
(260, 212, 'B', 'Tafsir atau Bayan', 1),
(261, 212, 'C', 'Idhafah', 0),
(262, 212, 'D', 'Takhsis', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `lesson_progress`
--

CREATE TABLE `lesson_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `lesson_id` int(11) NOT NULL,
  `has_read` tinyint(1) DEFAULT 0,
  `has_passed` tinyint(1) DEFAULT 0,
  `attempts` int(11) DEFAULT 0,
  `last_score` int(11) DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `lesson_progress`
--

INSERT INTO `lesson_progress` (`id`, `user_id`, `lesson_id`, `has_read`, `has_passed`, `attempts`, `last_score`, `updated_at`) VALUES
(14, 4, 2, 1, 1, 7, 2, '2025-12-05 08:43:26'),
(21, 4, 3, 1, 1, 2, 2, '2025-12-05 08:52:16'),
(23, 4, 4, 1, 1, 1, 2, '2025-12-05 08:53:06'),
(24, 4, 5, 1, 1, 2, 2, '2025-12-05 08:57:30'),
(26, 4, 6, 1, 1, 1, 2, '2025-12-08 02:39:45'),
(33, 4, 7, 1, 1, 2, 2, '2025-12-09 23:57:28'),
(38, 4, 38, 1, 1, 1, 3, '2025-12-10 03:56:58'),
(39, 4, 39, 1, 1, 1, 3, '2025-12-10 04:17:09'),
(40, 4, 8, 1, 1, 0, 100, '2025-12-10 04:16:52'),
(42, 4, 9, 1, 1, 0, 100, '2025-12-10 04:16:57'),
(44, 4, 10, 1, 1, 0, 100, '2025-12-10 04:17:29'),
(45, 4, 11, 1, 1, 0, 100, '2025-12-10 04:17:41'),
(46, 4, 12, 1, 1, NULL, 100, '2025-12-10 04:26:19'),
(48, 4, 13, 1, 1, 2, 100, '2025-12-10 04:50:10'),
(51, 4, 14, 1, 0, 0, 0, '2025-12-10 04:50:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `lesson_questions`
--

CREATE TABLE `lesson_questions` (
  `id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `explanation` text DEFAULT NULL,
  `is_practice` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `lesson_questions`
--

INSERT INTO `lesson_questions` (`id`, `lesson_id`, `question_text`, `explanation`, `is_practice`) VALUES
(3, 2, 'Apa salah satu syarat terjadinya pewarisan?', 'Syarat utama pewarisan adalah matinya pewaris dan hidupnya ahli waris saat pewaris meninggal.', 0),
(4, 2, 'Berikut ini yang termasuk penghalang waris adalah...', 'Di antara penghalang waris yang masyhur adalah perbedaan agama dan pembunuhan yang tidak dibenarkan.', 0),
(5, 3, 'Apa yang dimaksud dengan ashabul furud?', 'Ashabul furud adalah ahli waris yang bagiannya sudah ditentukan secara pasti dalam nash (seperdua, seperempat, dsb.).', 0),
(6, 3, 'Bagaimana hubungan antara ashabul furud dan \'ashabah?', 'Ashabul furud mendapat bagian yang telah ditentukan terlebih dahulu, kemudian sisanya diberikan kepada \'ashabah.', 0),
(7, 7, 'Siapa yang menerima sisa harta setelah Ashabul Furud?', 'Yang menerima sisa adalah \'Ashabah.', 0),
(8, 7, '’Ashabah terbagi menjadi berapa?', 'Tiga: bi nafsi, bil ghair, ma\'al ghair.', 0),
(9, 8, 'Siapa yang termasuk \'Ashabah bi nafsi?', 'Anak laki-laki termasuk \'Ashabah bi nafsi.', 0),
(10, 8, 'Jika tidak ada Ashabul Furud, apa bagian \'Ashabah bi nafsi?', 'Mereka mengambil seluruh harta.', 0),
(11, 9, 'Anak perempuan menjadi \'Ashabah ketika ...?', 'Jika bersama anak laki-laki.', 0),
(12, 9, 'Contoh \'Ashabah ma\'al ghair adalah ...?', 'Saudara perempuan bersama anak perempuan.', 0),
(13, 10, 'Dzawil Arham adalah...', 'Mereka kerabat rahim yang bukan Ashabul Furud dan bukan \'Ashabah.', 0),
(14, 10, 'Dzawil Arham hanya mewarisi jika ...?', 'Mereka mewarisi hanya jika tidak ada Ashabul Furud dan \'Ashabah.', 0),
(15, 11, 'Siapa yang termasuk Dzawil Arham?', 'Cucu dari anak perempuan termasuk Dzawil Arham.', 0),
(16, 11, 'Kelompok Dzawil Arham terbagi menjadi ...?', 'Ada tiga kelompok.', 0),
(17, 12, 'Dzawil Arham mendapatkan warisan dengan metode ...?', 'Metode at-tartib dan al-qurba.', 0),
(18, 12, 'Manakah yang benar tentang Dzawil Arham?', 'Mereka hanya mewaris jika tidak ada Ashabul Furud maupun \'Ashabah.', 0),
(19, 13, 'Bagian dua anak perempuan dalam kasus suami + ibu + dua anak perempuan adalah ...?', 'Dua anak perempuan mendapat 2/3.', 0),
(20, 13, 'Jika total bagian Ashabul Furud melebihi harta, dilakukan ...?', 'Solusinya adalah \'aul.', 0),
(21, 14, 'Dalam kasus istri + ayah + anak laki-laki + anak perempuan, istri mendapat ...?', 'Istri mendapat 1/8.', 0),
(22, 14, 'Anak laki-laki dan anak perempuan dalam kasus ini menjadi ...?', 'Mereka menjadi \'Ashabah bil ghair.', 0),
(23, 15, 'Dzawil Arham mewaris jika ...?', 'Mereka hanya mewaris jika tidak ada Ashabul Furud maupun \'Ashabah.', 0),
(24, 15, 'Metode pembagian Dzawil Arham adalah ...?', 'Metode at-tartib dan al-qurba.', 0),
(25, 16, 'Siapa yang termasuk Ashabul Furud?', 'Ibu adalah Ashabul Furud.', 0),
(26, 16, 'Anak laki-laki termasuk ...?', 'Anak laki-laki adalah \'Ashabah bi nafsi.', 0),
(27, 16, 'Bagian suami jika pewaris punya anak adalah ...?', 'Suami mendapat 1/4 jika pewaris punya anak.', 0),
(28, 16, 'Ibu mendapatkan 1/6 jika ...?', 'Jika pewaris punya anak.', 0),
(29, 16, '“Lil dzakari mitslu hazzil untsayain” berlaku untuk ...?', 'Ini berlaku bagi \'Ashabah bil ghair.', 0),
(32, 35, 'Siapa yang termasuk Ashabul Furud?', 'Ibu adalah salah satu Ashabul Furud.', 0),
(33, 35, 'Anak laki-laki termasuk kelompok ahli waris apa?', 'Anak laki-laki termasuk Ashabah bi nafsi.', 0),
(34, 35, 'Bagian suami jika pewaris mempunyai anak adalah berapa?', 'Jika pewaris mempunyai anak, suami mendapat seperempat (1/4).', 0),
(35, 35, 'Ibu mendapatkan 1/6 dalam kondisi apa?', 'Ibu mendapat 1/6 jika pewaris mempunyai anak.', 0),
(36, 35, 'Kaidah laki-laki mendapat dua kali bagian perempuan berlaku pada kelompok apa?', 'Kaidah ini berlaku pada Ashabah bil ghair, seperti anak laki-laki dan anak perempuan.', 0),
(37, 36, 'Dalam kasus istri dan dua anak perempuan, berapa bagian istri?', 'Jika ada anak, istri mendapat 1/8.', 0),
(38, 36, 'Dalam kasus ayah, ibu, dan satu anak perempuan, bagaimana bagian ayah?', 'Ayah mendapat 1/6 dan bisa mendapat sisa sebagai Ashabah.', 0),
(39, 36, 'Dalam kasus anak laki-laki dan anak perempuan, bagaimana cara pembagiannya?', 'Bagian anak laki-laki dua kali bagian anak perempuan.', 0),
(40, 36, 'Dalam kasus suami, ibu, dan saudara perempuan sekandung, berapa bagian ibu?', 'Dalam kasus ini ibu mendapat 1/6.', 0),
(41, 36, 'Dalam kasus istri, dua anak laki-laki, dan satu anak perempuan, berapa bagian istri?', 'Jika ada anak, istri mendapat 1/8.', 0),
(42, 37, 'Dzawil Arham mulai mewaris dalam kondisi apa?', 'Dzawil Arham mewaris jika tidak ada Ashabul Furud dan tidak ada Ashabah.', 0),
(43, 37, 'Jika total bagian Ashabul Furud lebih besar dari harta, apa yang dilakukan?', 'Jika total bagian lebih besar dari harta, dilakukan aul, yaitu pengurangan proporsional.', 0),
(44, 37, 'Kapan terjadi radd dalam pembagian waris?', 'Radd terjadi jika tidak ada Ashabah, sehingga sisa harta dikembalikan kepada Ashabul Furud.', 0),
(45, 37, 'Dalam Dzawil Arham, siapa yang didahulukan dalam pembagian?', 'Yang lebih dekat derajatnya kepada pewaris didahulukan.', 0),
(46, 37, 'Pewaris tidak meninggalkan Ashabul Furud dan Ashabah, tetapi meninggalkan cucu dari anak perempuan. Kelompok apakah ahli waris ini?', 'Cucu dari anak perempuan termasuk Dzawil Arham.', 0),
(201, 4, 'Ahli waris yang bagiannya sudah ditentukan secara pasti dalam Al-Qur\'an (seperti 1/2, 1/4) disebut...', 'Ashabul Furud adalah ahli waris dengan bagian pasti. Sedangkan \'Ashabah menerima sisa.', 0),
(202, 4, 'Manakah di bawah ini yang termasuk kelompok Ahli Waris Laki-laki?', 'Suami adalah ahli waris laki-laki. Nenek dan Ibu adalah perempuan, Cucu perempuan juga perempuan.', 0),
(203, 5, 'Berapa jumlah total golongan Ashabul Furud yang disepakati ulama?', 'Total ada 12 golongan: 4 laki-laki dan 8 perempuan.', 0),
(204, 5, 'Siapakah di bawah ini yang termasuk Ashabul Furud dari pihak laki-laki?', 'Ayah termasuk Ashabul Furud (bisa dapat 1/6). Paman dan Anak Laki-laki biasanya menjadi \'Ashabah.', 0),
(205, 6, 'Bagian 1/4 (seperempat) diberikan kepada suami apabila...', 'Suami mendapat 1/4 jika istri meninggal dan meninggalkan anak/cucu. Jika tidak ada anak, suami dapat 1/2.', 0),
(206, 6, 'Manakah pecahan di bawah ini yang TIDAK termasuk dalam pembagian waris Al-Qur\'an?', 'Pecahan 1/5 tidak ada dalam pembagian waris Islam (Faraid). Yang ada: 1/2, 1/4, 1/8, 2/3, 1/3, 1/6.', 0),
(207, 38, 'Apa arti dari kata \"Ushul\" dalam \"Ushul Fiqh\"?', 'Kata \"Ushul\" dalam bahasa Arab berarti akar, dasar, atau fondasi. Dalam konteks Ushul Fiqh, ini merujuk pada prinsip-prinsip dasar atau metode untuk memahami dan menggali hukum Islam.', 0),
(208, 38, 'Berapa jumlah sumber hukum Islam (Adillah Syariah) yang utama?', 'Sumber hukum Islam yang utama ada 4, yaitu: 1) Al-Quran, 2) Hadis/Sunnah, 3) Ijma (kesepakatan ulama), dan 4) Qiyas (analogi). Keempat sumber ini disebut dengan Adillah Syariah Al-Ashliyah (sumber hukum utama).', 0),
(209, 38, 'Manakah pernyataan berikut yang TIDAK termasuk dalam ruang lingkup Ushul Fiqh?', 'Ushul Fiqh mempelajari teori tentang Al-Quran, Hadis, Ijma, Qiyas, kaidah interpretasi, dan teori tentang mujtahid. Namun, teori ekonomi modern BUKAN bagian dari ruang lingkup Ushul Fiqh karena tidak berkaitan langsung dengan metode penetapan hukum Islam.', 0),
(210, 39, 'Sumber hukum Islam mana yang paling utama dan tidak tertandingi?', 'Al-Quran adalah sumber hukum Islam yang paling utama karena merupakan firman Allah yang diturunkan langsung kepada Nabi Muhammad SAW. Tidak ada sumber hukum lain yang lebih tinggi derajatnya dari Al-Quran.', 0),
(211, 39, 'Apa yang dimaksud dengan \"Hadis Sahih\"?', 'Hadis Sahih adalah hadis yang diterima tanpa keraguan karena memiliki kriteria kesahihan yang ketat: sanadnya bersambung, perawi bersifat adil (jujur) dan dhabit (hafal), tidak ada cacat dalam sanad maupun teks hadis, dan bukan hadis ghorib (aneh) tanpa dukungan perawi lain.', 0),
(212, 39, 'Fungsi Hadis dalam menerangkan ayat Al-Quran disebut dengan istilah apa?', 'Fungsi Hadis untuk menerangkan atau menjelaskan ayat Al-Quran disebut \"Tafsir\" atau \"Bayan\". Contohnya, Hadis menjelaskan cara melaksanakan shalat yang dijelaskan secara umum dalam Al-Quran.', 0);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indeks untuk tabel `course_modules`
--
ALTER TABLE `course_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indeks untuk tabel `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indeks untuk tabel `lesson_options`
--
ALTER TABLE `lesson_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indeks untuk tabel `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_lesson` (`user_id`,`lesson_id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indeks untuk tabel `lesson_questions`
--
ALTER TABLE `lesson_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `course_modules`
--
ALTER TABLE `course_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `lesson_options`
--
ALTER TABLE `lesson_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=263;

--
-- AUTO_INCREMENT untuk tabel `lesson_progress`
--
ALTER TABLE `lesson_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT untuk tabel `lesson_questions`
--
ALTER TABLE `lesson_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `course_modules`
--
ALTER TABLE `course_modules`
  ADD CONSTRAINT `course_modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lessons_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `course_modules` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `lesson_options`
--
ALTER TABLE `lesson_options`
  ADD CONSTRAINT `lesson_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `lesson_questions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD CONSTRAINT `lesson_progress_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `lesson_questions`
--
ALTER TABLE `lesson_questions`
  ADD CONSTRAINT `lesson_questions_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
