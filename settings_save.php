<?php
require_once __DIR__ . '/config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($name !== '' && $email !== '') {
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
    }
}
header('Location: settings.php');
exit;
