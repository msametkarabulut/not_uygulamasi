<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/icons.php';

if (is_logged_in()) {
    header('Location: notes.php');
    exit;
}

// Demo hesaplar (Firebase Auth kullanılmadan test için)
$demo_accounts = [
    'admin@test.com' => ['password' => 'admin123', 'name' => 'Admin', 'role' => 'admin'],
    'kullanici@test.com' => ['password' => '123456', 'name' => 'Ahmet Yılmaz', 'role' => 'user'],
];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $email_lower = strtolower($email);
    if ($email === '' || $password === '') {
        $error = 'E-posta ve şifre gerekli.';
    } elseif (isset($demo_accounts[$email_lower]) && $demo_accounts[$email_lower]['password'] === $password) {
        $acc = $demo_accounts[$email_lower];
        $_SESSION['user'] = [
            'id' => $acc['role'] === 'admin' ? '1' : '2',
            'name' => $acc['name'],
            'email' => $email,
            'role' => $acc['role'],
        ];
        header('Location: notes.php');
        exit;
    } else {
        $error = 'E-posta veya şifre hatalı.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-box">
        <div class="auth-header">
            <div class="auth-logo"><?= icon_note('w-7 h-7') ?></div>
            <h1><?= htmlspecialchars(APP_NAME) ?></h1>
            <p>Hesabınıza giriş yapın</p>
        </div>
        <div class="auth-card">
            <?php if ($error): ?><p class="auth-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <form method="post" action="login.php">
                <div class="form-group">
                    <label for="email">E-posta</label>
                    <div class="input-wrap">
                        <?= icon_mail('w-4 h-4') ?>
                        <input type="email" id="email" name="email" placeholder="ornek@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <div class="input-wrap">
                        <?= icon_lock('w-4 h-4') ?>
                        <input type="password" id="password" name="password" placeholder="Şifrenizi girin" required>
                    </div>
                </div>
                <button type="submit" class="btn">Giriş Yap</button>
            </form>
            <p class="auth-footer">Hesabınız yok mu? <a href="register.php">Kayıt Ol</a></p>

            <div class="demo-credentials">
                <p class="demo-title">Demo hesaplar</p>
                <div class="demo-row">
                    <span class="demo-role">Admin</span>
                    <span>admin@test.com</span>
                    <span class="demo-sep">/</span>
                    <span>admin123</span>
                </div>
                <div class="demo-row">
                    <span class="demo-role">Kullanıcı</span>
                    <span>kullanici@test.com</span>
                    <span class="demo-sep">/</span>
                    <span>123456</span>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
