# Panduan Konfigurasi Google Login

Fitur Login dengan Google telah ditambahkan. Ikuti langkah-langkah berikut untuk mengaktifkannya.

## 1. Update Database
Jalankan script berikut untuk menambahkan kolom `google_id` ke tabel `users`.
Buka browser dan akses:
`http://localhost/mandiribelajar.my.id/run_google_db_update.php`

Jika sukses, akan muncul pesan "Database updated successfully".

## 2. Install Library (TIDAK PERLU)
Versi terbaru sistem ini telah dioptimalkan menggunakan **Native PHP cURL**, sehingga Anda **TIDAK PERLU** menginstall library `google/apiclient` yang besar.

Anda bisa melewati langkah instalasi Composer. Sistem akan langsung bekerja asalkan ekstensi `php-curl` aktif di server Anda (biasanya sudah aktif secara default di XAMPP/Laragon).

## 3. Dapatkan Google Client ID & Secret
Anda perlu mendaftarkan aplikasi Anda di Google Cloud Console.

1.  Buka [Google Cloud Console](https://console.cloud.google.com/).
2.  Buat Project baru (misal: `Mandiri Belajar`).
3.  Masuk ke menu **APIs & Services** > **Credentials**.
4.  Klik **Create Credentials** > **OAuth client ID**.
5.  Pilih **Web application**.
6.  Isi nama aplikasi.
7.  Pada **Authorized redirect URIs**, tambahkan URL callback Anda:
    *   Jika di localhost: `http://localhost/mandiribelajar.my.id/auth_google.php`
    *   Sesuaikan jika folder project Anda berbeda.
8.  Klik **Create**.
9.  Salin **Client ID** dan **Client Secret** yang muncul.

## 4. Konfigurasi Project
Buka file `google_config.php` di editor Anda.
Isi nilai berikut dengan data yang Anda dapatkan dari Google Cloud Console:

```php
define('GOOGLE_CLIENT_ID', 'PASTE_CLIENT_ID_DISINI');
define('GOOGLE_CLIENT_SECRET', 'PASTE_CLIENT_SECRET_DISINI');
define('GOOGLE_REDIRECT_URL', 'http://localhost/mandiribelajar.my.id/auth_google.php');
```

## 5. Selesai
Sekarang buka halaman Login, Anda akan melihat tombol "Login dengan Google".

---

### Catatan Penting
*   Pastikan `GOOGLE_REDIRECT_URL` di `google_config.php` **SAMA PERSIS** dengan yang Anda daftarkan di Google Cloud Console.
*   Jika user login dengan Google, password mereka akan kosong di database (karena login via Google tidak butuh password).
*   Avatar user dari Google akan langsung digunakan.
