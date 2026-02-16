<?php
require_once __DIR__ . '/config/config.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/icons.php';

$folders = folders_with_counts();
$notes = load_notes();
$folder_id = $_GET['id'] ?? null;
$is_new = isset($_GET['new']);

if ($is_new && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $new_id = (string) time();
        $folders[] = [
            'id' => $new_id,
            'name' => $name,
            'color' => '#FFDAB9',
            'noteCount' => 0,
            'userId' => get_user_id(),
            'createdAt' => date('c'),
        ];
        save_folders($folders);
        header('Location: folder.php?id=' . $new_id);
        exit;
    }
}

if (!$is_new && $folder_id) {
    $folder = null;
    foreach ($folders as $f) {
        if (($f['id'] ?? '') === $folder_id) {
            $folder = $f;
            break;
        }
    }
    if (!$folder) {
        header('Location: notes.php');
        exit;
    }
} elseif (!$is_new) {
    header('Location: notes.php');
    exit;
}

$folder_notes = [];
if (!$is_new && $folder_id) {
    $folder_notes = array_filter($notes, function ($n) use ($folder_id) {
        return ($n['folderId'] ?? null) === $folder_id;
    });
}

$search = trim($_GET['q'] ?? '');
if ($search !== '' && !empty($folder_notes)) {
    $q = mb_strtolower($search);
    $folder_notes = array_filter($folder_notes, function ($n) use ($q) {
        return strpos(mb_strtolower($n['title'] ?? ''), $q) !== false
            || strpos(mb_strtolower($n['content'] ?? ''), $q) !== false;
    });
}
$view = $_GET['view'] ?? 'grid';
if (!in_array($view, ['grid', 'list'])) {
    $view = 'grid';
}

$current_page = 'folder';
$page_title = $is_new ? 'Yeni Klasör' : ($folder['name'] ?? 'Klasör');
$content = ob_start();
?>
<header class="page-header">
    <a href="notes.php" class="btn btn-ghost btn-icon"><?= icon_arrow_left('w-4 h-4') ?></a>
    <?php if ($is_new): ?>
    <h2 class="text-lg font-semibold">Yeni Klasör</h2>
    <form method="post" action="folder.php?new=1" class="flex gap-2 flex-1 max-w-md">
        <input type="text" name="name" placeholder="Klasör adı" required style="flex:1;height:2.25rem;padding:0 .75rem;border:1px solid hsl(var(--border));border-radius:.5rem;font-size:.8125rem">
        <button type="submit" class="btn btn-primary">Oluştur</button>
    </form>
    <?php else: ?>
    <div class="flex items-center gap-2">
        <?= icon_folder('w-5 h-5'); ?>
        <h2 class="text-lg font-semibold"><?= htmlspecialchars($folder['name']) ?></h2>
    </div>
    <form method="get" action="folder.php" class="search-wrap">
        <input type="hidden" name="id" value="<?= htmlspecialchars($folder_id) ?>">
        <?= icon_search('w-4 h-4') ?>
        <input type="search" name="q" placeholder="Bu klasörde ara..." value="<?= htmlspecialchars($search) ?>">
    </form>
    <div class="header-actions">
        <div class="view-toggle">
            <a href="folder.php?id=<?= urlencode($folder_id) ?>&q=<?= urlencode($search) ?>&view=grid" class="btn btn-ghost btn-icon <?= $view === 'grid' ? 'active' : '' ?>"><?= icon_grid('w-4 h-4') ?></a>
            <a href="folder.php?id=<?= urlencode($folder_id) ?>&q=<?= urlencode($search) ?>&view=list" class="btn btn-ghost btn-icon <?= $view === 'list' ? 'active' : '' ?>"><?= icon_list('w-4 h-4') ?></a>
        </div>
        <?php include __DIR__ . '/includes/user_nav.php'; ?>
    </div>
    <?php endif; ?>
</header>
<?php if (!$is_new): ?>
<div class="page-body">
    <?php if (empty($folder_notes)): ?>
    <div class="empty-state">
        <div class="empty-icon"><?= icon_folder('w-8 h-8') ?></div>
        <h3><?= $search ? 'Sonuç bulunamadı' : 'Bu klasör boş' ?></h3>
        <p><?= $search ? 'Farklı bir arama terimi deneyin.' : 'Bu klasöre not eklemek için bir not oluşturun ve bu klasörü seçin.' ?></p>
    </div>
    <?php elseif ($view === 'list'): ?>
    <div class="notes-list">
        <?php foreach ($folder_notes as $note): ?>
        <?php include __DIR__ . '/includes/note_row.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="notes-grid">
        <?php foreach ($folder_notes as $note): ?>
        <?php include __DIR__ . '/includes/note_card.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
