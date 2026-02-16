<?php
/**
 * NoteFlow - PHP + Firebase yapılandırması
 * Firebase bilgileri: config/firebase.credentials.php (şablon: firebase.credentials.php.example)
 * Detay: FIREBASE-KURULUM.md
 */

session_start();

// Firebase: önce firebase.credentials.php, yoksa ortam değişkenleri
$firebase = [];
$credsFile = __DIR__ . '/firebase.credentials.php';
if (is_file($credsFile)) {
    $firebase = (array) require $credsFile;
}
define('FIREBASE_API_KEY', $firebase['apiKey'] ?? getenv('FIREBASE_API_KEY') ?: '');
define('FIREBASE_PROJECT_ID', $firebase['projectId'] ?? getenv('FIREBASE_PROJECT_ID') ?: 'notlar-cf9d7');
define('FIREBASE_AUTH_DOMAIN', $firebase['authDomain'] ?? getenv('FIREBASE_AUTH_DOMAIN') ?: 'notlar-cf9d7.firebaseapp.com');
define('FIREBASE_STORAGE_BUCKET', $firebase['storageBucket'] ?? getenv('FIREBASE_STORAGE_BUCKET') ?: 'notlar-cf9d7.appspot.com');
define('FIREBASE_MESSAGING_SENDER_ID', $firebase['messagingSenderId'] ?? getenv('FIREBASE_MESSAGING_SENDER_ID') ?: '433163769442');
define('FIREBASE_APP_ID', $firebase['appId'] ?? getenv('FIREBASE_APP_ID') ?: '');
define('FIREBASE_MEASUREMENT_ID', $firebase['measurementId'] ?? getenv('FIREBASE_MEASUREMENT_ID') ?: '');

// Uygulama
define('APP_NAME', 'NoteFlow');
define('APP_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define('BASE_PATH', dirname(__DIR__));

// Kullanıcı oturumu (Firebase kullanılmıyorsa örnek kullanıcı)
function current_user() {
    if (!empty($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return null;
}

function is_logged_in() {
    return current_user() !== null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function base_url() {
    static $base;
    if ($base === null) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($script);
        if (strpos($base, '\\') !== false) {
            $base = str_replace('\\', '/', $base);
        }
        if ($base !== '/' && $base !== '') {
            $base .= '/';
        } else {
            $base = '/';
        }
    }
    return $base;
}

function is_admin() {
    $u = current_user();
    return $u && ($u['role'] ?? '') === 'admin';
}
