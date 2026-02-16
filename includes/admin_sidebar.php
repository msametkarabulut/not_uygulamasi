<?php
require_once __DIR__ . '/icons.php';
$current_page = $current_page ?? '';
$base = $base ?? '';
?>
<aside class="sidebar admin-sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon"><?= icon_shield('w-5 h-5') ?></div>
        <div>
            <h1 class="sidebar-title">Admin Panel</h1>
            <span class="sidebar-badge">NoteFlow Yönetim</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= $base ?>admin/" class="nav-item <?= $current_page === 'admin' ? 'active' : '' ?>">
            <span class="nav-item-inner"><?= icon_dashboard('w-4 h-4') ?> Dashboard</span>
        </a>
        <a href="<?= $base ?>admin/users.php" class="nav-item <?= $current_page === 'admin_users' ? 'active' : '' ?>">
            <span class="nav-item-inner"><?= icon_users('w-4 h-4') ?> Kullanıcılar</span>
        </a>
        <a href="<?= $base ?>admin/stats.php" class="nav-item <?= $current_page === 'admin_stats' ? 'active' : '' ?>">
            <span class="nav-item-inner"><?= icon_chart('w-4 h-4') ?> İstatistikler</span>
        </a>
        <a href="<?= $base ?>admin/settings.php" class="nav-item <?= $current_page === 'admin_settings' ? 'active' : '' ?>">
            <span class="nav-item-inner"><?= icon_settings('w-4 h-4') ?> Ayarlar</span>
        </a>
    </nav>
    <div class="sidebar-bottom">
        <a href="<?= $base ?>notes.php" class="nav-item">
            <span class="nav-item-inner"><?= icon_arrow_left('w-4 h-4') ?> Uygulamaya Dön</span>
        </a>
    </div>
</aside>
