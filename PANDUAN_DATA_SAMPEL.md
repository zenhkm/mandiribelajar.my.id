# 📚 Panduan Penggunaan Data Sampel Kursus Ushul Fiqh

## 📌 Daftar Isi
1. [Pengenalan](#pengenalan)
2. [Cara Menggunakan Data Sampel](#cara-menggunakan-data-sampel)
3. [Struktur Data yang Dibuat](#struktur-data-yang-dibuat)
4. [Penjelasan Setiap Komponen](#penjelasan-setiap-komponen)
5. [Cara Menambah Data Lebih Lanjut](#cara-menambah-data-lebih-lanjut)
6. [FAQ & Troubleshooting](#faq--troubleshooting)

---

## Pengenalan

Data sampel ini dibuat untuk membantu Anda:
- ✅ Memahami struktur database kursus
- ✅ Melakukan testing dan perencanaan
- ✅ Mengetahui apa yang bisa dilakukan setelah penyelesaian materi
- ✅ Memiliki template untuk menambah kursus baru

**Konten yang disediakan:**
- 1 Kursus: **Ushul Fiqh Dasar**
- 1 Bab: **Pengenalan Ushul Fiqh**
- 2 Materi lengkap dengan penjelasan mendalam
- 6 Soal pilihan ganda (3 soal per materi)

---

## Cara Menggunakan Data Sampel

### Option 1: Via phpMyAdmin (Recommended untuk Pemula)

1. **Buka phpMyAdmin**
   - Akses melalui web browser: `http://localhost/phpmyadmin`
   - Login dengan akun database Anda

2. **Pilih Database**
   - Klik pada database `quic1934_kursus` (sesuai di `config.php`)

3. **Import Script SQL**
   - Klik tab **"SQL"** atau **"Import"**
   - Pilih file: `sql/sample_data_ushul_fiqh.sql`
   - Klik **"Go"** atau **"Execute"**

4. **Verifikasi Data**
   ```sql
   SELECT * FROM courses WHERE slug = 'ushul-fiqh-dasar';
   SELECT * FROM course_modules WHERE course_id = LAST_INSERT_ID();
   SELECT * FROM lessons WHERE course_id = LAST_INSERT_ID();
   ```

### Option 2: Via Command Line MySQL

```bash
# Buka terminal/Command Prompt

# Masuk ke MySQL
mysql -h localhost -u quic1934_zenhkm -p

# Pilih database
USE quic1934_kursus;

# Import file SQL
SOURCE C:\Users\zenhk\OneDrive\Documents\GitHub\mandiribelajar.my.id\sql\sample_data_ushul_fiqh.sql;

# Atau gunakan command line langsung
mysql -h localhost -u quic1934_zenhkm -p quic1934_kursus < sql\sample_data_ushul_fiqh.sql
```

### Option 3: Via PHP Script

Buat file `import_sample_data.php` di folder root:

```php
<?php
require_once 'config.php';

$sqlFile = file_get_contents('sql/sample_data_ushul_fiqh.sql');
$queries = array_filter(array_map('trim', explode(';', $sqlFile)));

try {
    foreach ($queries as $query) {
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }
    echo "✅ Data sampel berhasil diimport!";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

---

## Struktur Data yang Dibuat

### Diagram Relasi

```
courses (1 Kursus)
    ├── course_modules (1 Bab)
    │   ├── lessons (2 Materi)
    │   │   ├── lessons[id=1] (Pengertian Ushul Fiqh)
    │   │   │   ├── lesson_questions (3 Soal)
    │   │   │   │   ├── Question 1
    │   │   │   │   ├── Question 2
    │   │   │   │   └── Question 3
    │   │   │
    │   │   └── lessons[id=2] (Sumber Hukum: Al-Quran & Hadis)
    │   │       ├── lesson_questions (3 Soal)
    │   │       │   ├── Question 1
    │   │       │   ├── Question 2
    │   │       │   └── Question 3
```

### Tabel yang Terisi

| Tabel | Jumlah Data | Keterangan |
|-------|-------------|-----------|
| `courses` | 1 | Kursus Ushul Fiqh Dasar |
| `course_modules` | 1 | Bab "Pengenalan Ushul Fiqh" |
| `lessons` | 2 | 2 Materi pembelajaran |
| `lesson_questions` | 6 | 6 Pertanyaan (3 per materi) |
| `lesson_options` | 24 | 4 Opsi per pertanyaan (A, B, C, D) |

---

## Penjelasan Setiap Komponen

### 1. Courses (Kursus)

```sql
Kursus: "Ushul Fiqh Dasar"
├── Title: Ushul Fiqh Dasar
├── Slug: ushul-fiqh-dasar (untuk URL)
├── Level: Pemula
├── Status: Tersedia
├── Duration: 4 Jam
├── Image: 1733754000_123.jpg (gambar sampul)
└── Description: [Deskripsi lengkap tentang kursus]
```

### 2. Course Modules (Bab)

```sql
Bab 1: "Pengenalan Ushul Fiqh"
├── Module Order: 1
├── Title: Pengenalan Ushul Fiqh
└── Summary: [Ringkasan singkat bab]
```

### 3. Lessons (Materi)

**Materi 1: Pengertian Ushul Fiqh dan Ruang Lingkupnya**
- Berisi: Definisi, sumber hukum, ruang lingkup, manfaat
- Tipe: Text (konten HTML)
- Jumlah soal: 3

**Materi 2: Sumber Hukum Islam - Al-Quran dan Hadis**
- Berisi: Penjelasan Al-Quran dan Hadis, fungsinya, kategori, perbandingan
- Tipe: Text (konten HTML)
- Jumlah soal: 3

### 4. Lesson Questions (Soal & Jawaban)

Setiap soal memiliki:
- **Question Text**: Pertanyaan
- **Explanation**: Pembahasan (ditampilkan setelah jawab)
- **4 Opsi**: A, B, C, D dengan 1 jawaban benar

---

## Cara Menambah Data Lebih Lanjut

### Scenario 1: Menambah Bab Baru (di Kursus yang Sama)

```sql
-- Kursus ID adalah 1 (hasil dari LAST_INSERT_ID setelah insert courses)

INSERT INTO course_modules (course_id, module_order, title, summary) 
VALUES (1, 2, 'Judul Bab Baru', 'Ringkasan singkat bab');

SET @module_id = LAST_INSERT_ID();

-- Kemudian tambah materi ke bab ini (lihat Scenario 2)
```

### Scenario 2: Menambah Materi ke Bab

```sql
INSERT INTO lessons (course_id, module_id, lesson_order, title, content_type, content_text, video_url)
VALUES (
    1,                              -- Course ID
    @module_id,                     -- Module ID dari bab yang dituju
    1,                              -- Urutan materi (1, 2, 3...)
    'Judul Materi',                 -- Judul materi
    'text',                         -- Tipe konten (text atau video)
    '<p>Konten HTML...</p>',        -- Isi konten (format HTML)
    NULL                            -- URL video (opsional)
);

SET @lesson_id = LAST_INSERT_ID();
```

### Scenario 3: Menambah Soal ke Materi

```sql
-- Tambah soal
INSERT INTO lesson_questions (lesson_id, question_text, explanation)
VALUES (
    @lesson_id,
    'Teks pertanyaan...',
    'Pembahasan setelah menjawab...'
);

SET @question_id = LAST_INSERT_ID();

-- Tambah 4 opsi jawaban
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct)
VALUES
(@question_id, 'A', 'Teks opsi A', 0),
(@question_id, 'B', 'Teks opsi B', 1),  -- 1 berarti jawaban benar
(@question_id, 'C', 'Teks opsi C', 0),
(@question_id, 'D', 'Teks opsi D', 0);
```

### Scenario 4: Menambah Kursus Baru

```sql
INSERT INTO courses (title, slug, image, level, status, description, duration, lessons)
VALUES (
    'Nama Kursus Baru',
    'slug-kursus-baru',
    'nama_gambar.jpg',
    'Pemula',                    -- Pemula / Menengah / Mahir
    'Tersedia',                  -- Tersedia / Segera Hadir
    'Deskripsi kursus...',
    '2 Jam',
    0
);

SET @course_id_baru = LAST_INSERT_ID();

-- Kemudian tambah modul, lesson, dan questions
```

---

## Fungsi Utama Setelah Penyelesaian Materi

### 1. **Certificate / Sertifikat Penyelesaian**
Sistem akan menghasilkan sertifikat digital ketika siswa menyelesaikan semua materi

Fitur yang bisa ditambahkan:
```
- Sertifikat digital dengan nomor unik
- Download PDF dengan tanda tangan digital
- Share ke LinkedIn / CV
- Verifikasi sertifikat di website
```

### 2. **Achievement & Badge System**
Penghargaan untuk memotivasi siswa

Contoh badge:
```
🥇 Certified - Menyelesaikan 1 kursus
🔥 On Fire - Pembelajaran 7 hari berturut-turut
⭐ Perfect Score - Mendapat 100% di semua kuis
👍 Helpful - Jawaban di forum dipilih sebagai helpful
```

### 3. **Next Course Recommendation**
Rekomendasi kursus lanjutan

Contoh flow:
```
Setelah Selesai Ushul Fiqh Dasar
  ↓
Rekomendasi: Ushul Fiqh Lanjutan
  ↓
Atau: Fiqh Islam Dasar (terkait)
```

### 4. **Module Final Exam**
Ujian akhir bab sebelum lanjut ke bab berikutnya

```
Bab 1: Pengenalan Ushul Fiqh
  ├── Materi 1: Pengertian ✓
  ├── Materi 2: Sumber Hukum ✓
  └── Final Exam Bab 1: Minimal 70% harus lulus
        ↓
Bab 2: Ijma dan Qiyas (Terbuka jika lulus ujian)
```

### 5. **Forum Diskusi**
Tempat siswa bertanya dan berdiskusi

Fitur:
```
- Per materi atau per bab
- Moderator menjawab pertanyaan
- Mark as Helpful
- Points untuk jawaban terbaik
```

### 6. **Daily Challenge / Quiz Harian**
Soal random dari materi yang sudah dikerjakan

```
- 3-5 soal random setiap hari
- Bonus points jika benar semua
- Streak counter (berapa hari berturut-turut)
- Leaderboard harian/mingguan
```

### 7. **Progress Tracking & Analytics**
Dashboard untuk melihat progres

```
- Persentase penyelesaian per siswa
- Nilai rata-rata per materi
- Soal yang paling banyak salah
- Waktu belajar total
```

---

## FAQ & Troubleshooting

### Q: Bagaimana jika saya ingin mengubah data yang sudah diimport?

**A:** Anda bisa:
1. Edit langsung di aplikasi admin (jika sudah ada form edit)
2. Edit via phpMyAdmin
3. Hapus dan re-import dengan data yang sudah diubah di SQL file

**Untuk hapus semua data Ushul Fiqh:**
```sql
-- HATI-HATI! Ini akan menghapus semua data
DELETE FROM courses WHERE id = 1;
-- (Tabel lain akan otomatis terhapus karena CASCADE di FK)
```

### Q: Gambar sampul tidak muncul, bagaimana?

**A:** 
1. Pastikan folder `uploads/` ada di root project
2. Ganti nama gambar di SQL dengan gambar yang sudah ada
3. Atau upload gambar baru ke folder `uploads/` dan ubah nama file di SQL

```sql
UPDATE courses SET image = 'nama_gambar_anda.jpg' WHERE id = 1;
```

### Q: Bagaimana cara melihat data yang sudah diimport?

**A:**
```sql
-- Lihat kursus
SELECT * FROM courses WHERE slug = 'ushul-fiqh-dasar';

-- Lihat bab
SELECT * FROM course_modules WHERE course_id = 1;

-- Lihat materi
SELECT * FROM lessons WHERE course_id = 1 ORDER BY lesson_order;

-- Lihat soal
SELECT * FROM lesson_questions ORDER BY lesson_id;

-- Lihat opsi jawaban
SELECT * FROM lesson_options ORDER BY question_id;
```

### Q: Cara menambah soal tambahan di materi yang sudah ada?

**A:**
```sql
SET @lesson_id = 2;  -- ID materi target

INSERT INTO lesson_questions (lesson_id, question_text, explanation)
VALUES (@lesson_id, 'Pertanyaan baru...', 'Pembahasan...');

SET @question_id = LAST_INSERT_ID();

-- Tambah 4 opsi
INSERT INTO lesson_options (question_id, option_label, option_text, is_correct)
VALUES
(@question_id, 'A', 'Opsi A', 0),
(@question_id, 'B', 'Opsi B', 1),
(@question_id, 'C', 'Opsi C', 0),
(@question_id, 'D', 'Opsi D', 0);
```

### Q: Seberapa banyak materi yang sebaiknya dibuat?

**A:** Rekomendasi:
```
- 1 Bab = 2-4 materi
- 1 Materi = 15-30 menit pembelajaran
- 1 Materi = 3-5 soal
- 1 Kursus = 3-5 bab

Untuk Ushul Fiqh:
- Total estimasi: 5 bab × 3 materi × 20 menit = 300 menit ≈ 5 jam
```

### Q: Bagaimana tracking progress siswa?

**A:** Query untuk tracking:
```sql
-- Progress siswa per materi
SELECT 
    u.username,
    l.title as materi,
    COUNT(DISTINCT up.id) as lessons_completed
FROM users u
LEFT JOIN user_progress up ON u.id = up.user_id
LEFT JOIN lessons l ON up.lesson_id = l.id
WHERE l.course_id = 1
GROUP BY u.id, l.id
ORDER BY u.username;
```

---

## Checklist Sebelum Go-Live

- [ ] Data sampel sudah diimport dengan sukses
- [ ] Semua gambar sampul sudah ter-upload
- [ ] Konten sudah di-review oleh SME (Subject Matter Expert)
- [ ] Semua soal sudah diverifikasi jawaban benarnya
- [ ] User admin sudah bisa upload materi baru
- [ ] User student bisa akses dan belajar
- [ ] Sertifikat sudah bisa didownload
- [ ] Forum diskusi sudah aktif
- [ ] Email notification sudah berfungsi

---

## Dokumentasi Terkait

- `sql/sample_data_ushul_fiqh.sql` - Script data sampel
- `sql/development_roadmap.sql` - Rencana pengembangan
- `README.md` - Dokumentasi umum proyek

---

**Dibuat**: 9 Desember 2024
**Versi**: 1.0
**Status**: Ready to Use
