<?php
require_once __DIR__ . '/config/config.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/icons.php';

$id = $_GET['id'] ?? $_POST['id'] ?? null;
$note = $id ? get_note_by_id($id) : null;
$folders = folders_with_counts();
$note_colors = ['#FFFFFF','#FFE4B5','#FFDAB9','#FFD6D6','#E8F5E9','#C7CEEA','#F8E8EE','#FFC8A2'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $color = $_POST['color'] ?? '#FFDAB9';
    $folder_id = $_POST['folder_id'] ?? '';
    $folder_id = $folder_id === '' || $folder_id === 'none' ? null : $folder_id;
    $is_favorite = isset($_POST['is_favorite']);

    if ($title !== '') {
        $notes = load_notes();
        $now = date('c');
        if ($note) {
            foreach ($notes as $i => $n) {
                if (($n['id'] ?? '') === $note['id']) {
                    $notes[$i] = [
                        'id' => $n['id'],
                        'title' => $title,
                        'content' => $content,
                        'color' => $color,
                        'folderId' => $folder_id,
                        'isFavorite' => $is_favorite,
                        'createdAt' => $n['createdAt'] ?? $now,
                        'updatedAt' => $now,
                        'userId' => get_user_id(),
                    ];
                    break;
                }
            }
        } else {
            array_unshift($notes, [
                'id' => uniqid('n', true),
                'title' => $title,
                'content' => $content,
                'color' => $color,
                'folderId' => $folder_id,
                'isFavorite' => $is_favorite,
                'createdAt' => $now,
                'updatedAt' => $now,
                'userId' => get_user_id(),
            ]);
        }
        save_notes($notes);
        header('Location: notes.php');
        exit;
    }
}

if (!$note && $id) {
    header('Location: notes.php');
    exit;
}

$current_page = 'notes';
$page_title = $note ? 'Notu Düzenle' : 'Yeni Not';
$selColor = $note['color'] ?? '#FFDAB9';
$content = ob_start();
?>
<header class="page-header">
    <a href="notes.php" class="btn btn-ghost btn-icon"><?= icon_arrow_left('w-4 h-4') ?></a>
    <h2 class="text-lg font-semibold"><?= $note ? 'Notu Düzenle' : 'Yeni Not' ?></h2>
    <div class="header-actions">
        <?php include __DIR__ . '/includes/user_nav.php'; ?>
    </div>
</header>
<div class="page-body">
    <div class="max-w-2xl">
        <form method="post" action="note_edit.php" class="section-card" style="padding:0;overflow:hidden">
            <?php if ($note): ?><input type="hidden" name="id" value="<?= htmlspecialchars($note['id']) ?>"><?php endif; ?>
            <input type="hidden" name="color" id="color_input" value="<?= htmlspecialchars($selColor) ?>">

            <div class="note-editor-head">
                <div class="color-dots">
                    <?php foreach ($note_colors as $c): ?>
                    <button type="button" class="color-dot <?= $selColor === $c ? 'selected' : '' ?>"
                            style="background:<?= $c ?>" data-color="<?= $c ?>"
                            onclick="document.getElementById('color_input').value='<?= $c ?>';document.getElementById('editor-body').style.backgroundColor='<?= $c ?>';document.querySelectorAll('.color-dot').forEach(function(d){d.classList.remove('selected')});this.classList.add('selected')"></button>
                    <?php endforeach; ?>
                </div>
                <div class="editor-actions">
                    <label style="cursor:pointer;display:flex;align-items:center" title="Favori">
                        <input type="checkbox" name="is_favorite" value="1" <?= !empty($note['isFavorite']) ? 'checked' : '' ?> style="display:none" onchange="this.nextElementSibling.querySelector('svg').style.fill=this.checked?'currentColor':'none'">
                        <span><?= icon_star('w-4 h-4', !empty($note['isFavorite'])) ?></span>
                    </label>
                </div>
            </div>

            <div class="note-editor-body" id="editor-body" style="background-color:<?= htmlspecialchars($selColor) ?>">
                <input type="text" name="title" placeholder="Başlık" autocomplete="off" required value="<?= htmlspecialchars($note['title'] ?? '') ?>">
                <textarea name="content" placeholder="Aklındakileri yaz..."><?= htmlspecialchars($note['content'] ?? '') ?></textarea>
            </div>

            <div class="note-editor-foot">
                <select name="folder_id">
                    <option value="none">Klasörsüz</option>
                    <?php foreach ($folders as $f): ?>
                    <option value="<?= htmlspecialchars($f['id']) ?>" <?= ($note['folderId'] ?? null) === $f['id'] ? 'selected' : '' ?>><?= htmlspecialchars($f['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div style="display:flex;gap:.5rem">
                    <a href="notes.php" class="btn btn-ghost btn-sm">İptal</a>
                    <button type="submit" class="btn btn-primary btn-sm"><?= $note ? 'Güncelle' : 'Oluştur' ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
