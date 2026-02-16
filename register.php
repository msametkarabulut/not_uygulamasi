<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/icons.php';

if (is_logged_in()) {
    header('Location: notes.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($name === '' || $email === '' || $password === '') {
        $error = 'Ad, e-posta ve şifre gerekli.';
    } else {
        // Firebase Auth ile kayıt yapılabilir.
        $_SESSION['user'] = [
            'id' => (string) time(),
            'name' => $name,
            'email' => $email,
            'role' => 'user',
        ];
        header('Location: notes.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-box">
        <div class="auth-header">
            <div class="auth-logo"><?= icon_note('w-7 h-7') ?></div>
            <h1><?= htmlspecialchars(APP_NAME) ?></h1>
            <p>Yeni hesap oluşturun</p>
        </div>
        <div class="auth-card">
            <?php if ($error): ?><p style="color: #f87171; font-size: 0.875rem; margin-bottom: 1rem;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <form method="post" action="register.php">
                <div class="form-group">
                    <label for="name">Ad Soyad</label>
                    <div class="input-wrap">
                        <?= icon_user('w-4 h-4') ?>
                        <input type="text" id="name" name="name" placeholder="Adınız Soyadınız" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                </div>
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
                        <input type="password" id="password" name="password" placeholder="En az 6 karakter" required>
                    </div>
                </div>
                <button type="submit" class="btn">Kayıt Ol</button>
            </form>
            <p class="auth-footer">Zaten hesabınız var mı? <a href="login.php">Giriş Yap</a></p>
        </div>
    </div>
</div>
</body>
</html>
