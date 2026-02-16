<?php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}
if (is_logged_in() && !isset($folders)) {
    require_once __DIR__ . '/db.php';
    $folders = folders_with_counts();
}
if (is_logged_in() && !isset($notes)) {
    if (!function_exists('load_notes')) require_once __DIR__ . '/db.php';
    $notes = load_notes();
}
$current_page = $current_page ?? '';
$page_title = $page_title ?? APP_NAME;
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$base = (strpos($script, '/admin/') !== false || strpos($script, '\\admin\\') !== false) ? '../' : '';
$is_admin_layout = !empty($admin_layout);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a2744">
    <title><?= htmlspecialchars($page_title) ?> - <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>
<!-- Mobil sidebar overlay -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="document.body.classList.remove('sidebar-open')"></div>

<div class="app-wrap">
    <?php if ($is_admin_layout) { include __DIR__ . '/admin_sidebar.php'; } else { include __DIR__ . '/sidebar.php'; } ?>
    <main class="main-content">
        <?= $content ?? '' ?>
    </main>
</div>

<!-- Mobil hamburger butonu -->
<button type="button" class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Menü" onclick="document.body.classList.toggle('sidebar-open')">
    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
</button>

<script src="<?= $base ?>assets/js/app.js"></script>
<?= $footer_scripts ?? '' ?>
</body>
</html>
