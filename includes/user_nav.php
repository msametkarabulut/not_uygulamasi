<?php
require_once __DIR__ . '/icons.php';
$user = current_user();
$name = $user['name'] ?? 'Kullanıcı';
$email = $user['email'] ?? '';
$initials = implode('', array_map(function ($s) { return mb_substr($s, 0, 1); }, preg_split('/\s+/', $name, 2)));
$initials = mb_strtoupper($initials ?: 'K');
$is_admin = is_admin();
$base = $base ?? '';
?>
<div class="user-nav" id="user-nav">
    <button type="button" class="user-btn" onclick="document.getElementById('user-nav').classList.toggle('open')" aria-label="Menü">
        <?= $initials ?>
    </button>
    <div class="dropdown">
        <div class="user-info">
            <p class="user-info-name"><?= htmlspecialchars($name) ?></p>
            <p class="user-info-email"><?= htmlspecialchars($email) ?></p>
        </div>
        <a href="<?= $base ?>profile.php" class="dropdown-item">
            <span class="dropdown-icon"><?= icon_user('w-4 h-4') ?></span>
            <span>Profil</span>
        </a>
        <a href="<?= $base ?>settings.php" class="dropdown-item">
            <span class="dropdown-icon"><?= icon_settings('w-4 h-4') ?></span>
            <span>Ayarlar</span>
        </a>
        <?php if ($is_admin): ?>
        <a href="<?= $base ?>admin/" class="dropdown-item">
            <span class="dropdown-icon"><?= icon_shield('w-4 h-4') ?></span>
            <span>Admin Panel</span>
        </a>
        <?php endif; ?>
        <div class="dropdown-divider"></div>
        <a href="<?= $base ?>logout.php" class="dropdown-item text-danger">
            <span class="dropdown-icon"><?= icon_logout('w-4 h-4') ?></span>
            <span>Çıkış Yap</span>
        </a>
    </div>
</div>
