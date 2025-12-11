<?php
// auth_google.php
// Aktifkan Error Reporting untuk Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once 'config.php';
require_once 'google_config.php';

// Cek ekstensi cURL
if (!function_exists('curl_init')) {
    die("Error: PHP cURL extension is not enabled on this server.");
}

// Fungsi helper untuk melakukan request cURL
function http_request($url, $post_data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Matikan verifikasi SSL di localhost jika perlu
    curl_setopt($ch, CURLOPT_USERAGENT, 'MandiriBelajar/1.0'); // Tambahkan User Agent
    
    if ($post_data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    }
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        die("CURL Error: " . $error);
    }
    
    $decoded = json_decode($response, true);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        // Jika response bukan JSON, tampilkan raw response untuk debug
        die("JSON Decode Error. Raw Response: " . htmlspecialchars($response));
    }
    
    return $decoded;
}

// 1. Jika user mengklik tombol login Google (belum ada code)
if (!isset($_GET['code'])) {
    // Buat URL Login Google secara manual
    $params = [
        'client_id'     => GOOGLE_CLIENT_ID,
        'redirect_uri'  => GOOGLE_REDIRECT_URL,
        'response_type' => 'code',
        'scope'         => 'email profile',
        'access_type'   => 'online'
    ];
    
    $authUrl = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
    header('Location: ' . $authUrl);
    exit;
}

// 2. Jika Google mengembalikan kode (callback)
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Tukar Authorization Code dengan Access Token
    $tokenParams = [
        'code'          => $code,
        'client_id'     => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri'  => GOOGLE_REDIRECT_URL,
        'grant_type'    => 'authorization_code'
    ];
    
    $tokenData = http_request('https://oauth2.googleapis.com/token', $tokenParams);
    
    if (isset($tokenData['error'])) {
        die("Error fetching token: " . ($tokenData['error_description'] ?? $tokenData['error']));
    }
    
    if (!isset($tokenData['access_token'])) {
        die("Error: Access token not found.");
    }
    
    $accessToken = $tokenData['access_token'];
    
    // Ambil data profil user dari Google menggunakan Access Token
    $userInfo = http_request('https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $accessToken);
    
    if (isset($userInfo['error'])) {
        die("Error fetching user info: " . $userInfo['error']['message']);
    }
    
    $g_email   = $userInfo['email'];
    $g_name    = $userInfo['name'];
    $g_id      = $userInfo['id'];
    $g_picture = $userInfo['picture'] ?? '';

    // 3. Cek apakah user sudah ada di database
    // Cek berdasarkan google_id ATAU email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ? OR email = ? LIMIT 1");
    $stmt->execute([$g_id, $g_email]);
    $user = $stmt->fetch();

    if ($user) {
        // === USER SUDAH ADA ===
        
        // Jika user ada tapi google_id belum tersimpan (misal dulu daftar manual pakai email sama)
        // Maka kita update google_id nya agar terhubung
        if (empty($user['google_id'])) {
            $stmtUpdate = $pdo->prepare("UPDATE users SET google_id = ?, avatar = ? WHERE id = ?");
            $stmtUpdate->execute([$g_id, $g_picture, $user['id']]);
            
            // Refresh data user
            $user['google_id'] = $g_id;
            $user['avatar'] = $g_picture;
        }

        // Set Session Login
        $_SESSION['user_id'] = $user['id']; // Kompatibilitas kode lama
        $_SESSION['user'] = [
            'id'     => $user['id'],
            'name'   => $user['name'],
            'role'   => $user['role'],
            'avatar' => $user['avatar']
        ];

        header("Location: index.php");
        exit;

    } else {
        // === USER BARU (REGISTER OTOMATIS) ===
        
        // Buat password random karena user login via Google
        try {
            $random_password = bin2hex(random_bytes(8)); 
        } catch (Exception $e) {
            $random_password = substr(md5(mt_rand()), 0, 16);
        }
        $password_hash   = password_hash($random_password, PASSWORD_DEFAULT);

        // Insert ke database
        $stmtInsert = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, role, google_id, avatar, created_at) 
            VALUES (?, ?, ?, 'student', ?, ?, NOW())
        ");
        
        if ($stmtInsert->execute([$g_name, $g_email, $password_hash, $g_id, $g_picture])) {
            $newUserId = $pdo->lastInsertId();

            // Set Session Login
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user'] = [
                'id'     => $newUserId,
                'name'   => $g_name,
                'role'   => 'student',
                'avatar' => $g_picture
            ];

            header("Location: index.php");
            exit;
        } else {
            die("Gagal mendaftarkan pengguna baru.");
        }
    }
}
