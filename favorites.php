<?php
require_once __DIR__ . '/config/config.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/icons.php';

$notes = load_notes();
$folders = folders_with_counts();
$favorite_notes = array_filter($notes, function ($n) { return !empty($n['isFavorite']); });

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $q = mb_strtolower($search);
    $favorite_notes = array_filter($favorite_notes, function ($n) use ($q) {
        return strpos(mb_strtolower($n['title'] ?? ''), $q) !== false
            || strpos(mb_strtolower($n['content'] ?? ''), $q) !== false;
    });
}
$view = $_GET['view'] ?? 'grid';
if (!in_array($view, ['grid', 'list'])) $view = 'grid';

$note_colors = ['#FFFFFF','#FFE4B5','#FFDAB9','#FFD6D6','#E8F5E9','#C7CEEA','#F8E8EE','#FFC8A2'];

$current_page = 'favorites';
$page_title = 'Favoriler';
$content = ob_start();
?>
<header class="page-header">
    <form method="get" action="favorites.php" class="search-wrap">
        <?= icon_search('w-4 h-4') ?>
        <input type="search" name="q" placeholder="Favorilerde ara..." value="<?= htmlspecialchars($search) ?>">
    </form>
    <div class="header-actions">
        <div class="view-toggle">
            <a href="favorites.php?q=<?= urlencode($search) ?>&view=grid" class="btn btn-ghost btn-icon <?= $view === 'grid' ? 'active' : '' ?>"><?= icon_grid('w-4 h-4') ?></a>
            <a href="favorites.php?q=<?= urlencode($search) ?>&view=list" class="btn btn-ghost btn-icon <?= $view === 'list' ? 'active' : '' ?>"><?= icon_list('w-4 h-4') ?></a>
        </div>
        <?php include __DIR__ . '/includes/user_nav.php'; ?>
    </div>
</header>
<div class="page-body">
    <?php if (empty($favorite_notes)): ?>
    <div class="empty-state">
        <div class="empty-icon"><?= icon_star('w-8 h-8') ?></div>
        <h3><?= $search ? 'Sonuç bulunamadı' : 'Henüz favori not yok' ?></h3>
        <p><?= $search ? 'Farklı bir arama terimi deneyin.' : 'Notlarınızı favorilere ekleyerek hızlı erişim sağlayın.' ?></p>
    </div>
    <?php elseif ($view === 'list'): ?>
    <div class="notes-list">
        <?php foreach ($favorite_notes as $note): ?>
        <?php include __DIR__ . '/includes/note_row.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="notes-grid">
        <?php foreach ($favorite_notes as $note): ?>
        <?php include __DIR__ . '/includes/note_card.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Editör -->
<div class="modal-overlay note-editor-modal" id="note-modal">
    <div class="modal-content">
        <form id="note-modal-form">
            <input type="hidden" name="note_id" value="">
            <input type="hidden" name="color" value="#FFDAB9">
            <input type="hidden" name="is_favorite" value="">
            <div class="note-editor-head">
                <div class="color-dots">
                    <?php foreach ($note_colors as $c): ?>
                    <button type="button" class="color-dot" style="background:<?= $c ?>" data-color="<?= $c ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="editor-actions">
                    <button type="button" data-modal-fav title="Favori"><?= icon_star('w-4 h-4') ?></button>
                    <button type="button" data-modal-delete title="Sil"><?= icon_trash('w-4 h-4') ?></button>
                    <button type="button" data-close-modal title="Kapat"><?= icon_x('w-4 h-4') ?></button>
                </div>
            </div>
            <div class="note-editor-body" style="background-color:#FFDAB9">
                <input type="text" name="title" placeholder="Başlık" autocomplete="off">
                <textarea name="content" placeholder="Aklındakileri yaz..."></textarea>
            </div>
            <div class="note-editor-foot">
                <select name="folder_id">
                    <option value="none">Klasörsüz</option>
                    <?php foreach ($folders as $f): ?>
                    <option value="<?= htmlspecialchars($f['id']) ?>"><?= htmlspecialchars($f['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
