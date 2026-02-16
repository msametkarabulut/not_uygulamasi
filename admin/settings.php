<?php
require_once __DIR__ . '/../config/config.php';
require_login();
if (!is_admin()) {
    header('Location: ../notes.php');
    exit;
}
require_once __DIR__ . '/../includes/icons.php';
$current_page = 'admin_settings';
$page_title = 'Admin Ayarlar';
$content = ob_start();
?>
<header class="page-header">
    <h2 class="text-lg font-semibold">Admin Ayarlar</h2>
    <div class="header-actions">
        <?php include __DIR__ . '/../includes/user_nav.php'; ?>
    </div>
</header>
<div class="page-body">
    <div class="section-card">
        <h3>Sistem Ayarları</h3>
        <p class="section-desc">Firebase ve genel uygulama ayarları config üzerinden yapılandırılır.</p>
    </div>
</div>
<?php
$content = ob_get_clean();
$admin_layout = true;
include __DIR__ . '/../includes/layout.php';
