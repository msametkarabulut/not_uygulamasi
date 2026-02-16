<?php
require_once __DIR__ . '/../config/config.php';
require_login();
if (!is_admin()) {
    header('Location: ../notes.php');
    exit;
}
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/icons.php';
$notes = load_notes();
$folders = folders_with_counts();
$current_page = 'admin_stats';
$page_title = 'İstatistikler';
$content = ob_start();
?>
<header class="page-header">
    <h2 class="text-lg font-semibold">İstatistikler</h2>
    <div class="header-actions">
        <?php include __DIR__ . '/../includes/user_nav.php'; ?>
    </div>
</header>
<div class="page-body">
    <div class="section-card">
        <h3>Özet</h3>
        <p class="section-desc">Toplam not: <?= count($notes) ?>, Klasör: <?= count($folders) ?></p>
    </div>
</div>
<?php
$content = ob_get_clean();
$admin_layout = true;
include __DIR__ . '/../includes/layout.php';
