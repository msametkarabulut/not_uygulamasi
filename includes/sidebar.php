<?php
require_once __DIR__ . '/icons.php';
$current_page = $current_page ?? '';
$notes = $notes ?? [];
$folders = $folders ?? [];
$favorite_count = 0;
foreach ($notes as $n) {
    if (!empty($n['isFavorite'])) $favorite_count++;
}
$folders_open = isset($folders_open) ? $folders_open : true;
$base = $base ?? '';
$firestore_active = function_exists('_use_firestore') && _use_firestore();
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon"><?= icon_note('w-5 h-5') ?></div>
        <div>
            <h1 class="sidebar-title">NoteFlow</h1>
            <span class="sidebar-badge"><span class="dot" style="background:<?= $firestore_active ? '#22c55e' : '#eab308' ?>"></span> <?= $firestore_active ? 'Firebase' : 'Yerel' ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= $base ?>notes.php" class="nav-item <?= $current_page === 'notes' ? 'active' : '' ?>">
            <span class="nav-item-inner"><?= icon_note('w-4 h-4') ?> Tüm Notlar</span>
        </a>
        <a href="<?= $base ?>favorites.php" class="nav-item <?= $current_page === 'favorites' ? 'active' : '' ?>">
            <span class="nav-item-inner"><?= icon_star('w-4 h-4') ?> Favoriler</span>
            <span class="nav-badge"><?= $favorite_count ?></span>
        </a>

        <div class="sidebar-section">
            <button type="button" class="section-toggle" onclick="document.getElementById('folders-list').classList.toggle('open')">
                <?= $folders_open ? icon_chevron_down('w-3 h-3') : icon_chevron_right('w-3 h-3') ?>
                Klasörler
            </button>
            <div id="folders-list" class="folders-list <?= $folders_open ? 'open' : '' ?>">
                <?php foreach ($folders as $f): ?>
                    <div class="folder-row <?= ($current_page === 'folder' && ($folder_id ?? '') === $f['id']) ? 'active' : '' ?>"
                         data-folder-id="<?= htmlspecialchars($f['id']) ?>">
                        <a href="<?= $base ?>folder.php?id=<?= htmlspecialchars($f['id']) ?>" class="folder-link">
                            <?= icon_folder('w-4 h-4') ?> <span><?= htmlspecialchars($f['name']) ?></span>
                        </a>
                        <span class="folder-count"><?= (int)($f['noteCount'] ?? 0) ?></span>
                    </div>
                <?php endforeach; ?>
                <a href="<?= $base ?>folder.php?new=1" class="nav-item create-folder">
                    <span class="nav-item-inner"><?= icon_plus('w-4 h-4') ?> Klasör Oluştur</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="sidebar-bottom">
        <a href="<?= $base ?>settings.php" class="nav-item <?= $current_page === 'settings' ? 'active' : '' ?>">
            <span class="nav-item-inner"><?= icon_settings('w-4 h-4') ?> Ayarlar</span>
        </a>
    </div>
</aside>
