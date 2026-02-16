<?php
$id = $note['id'] ?? '';
$title = $note['title'] ?? '';
$body = $note['content'] ?? '';
$color = $note['color'] ?? '#FFDAB9';
$isFavorite = !empty($note['isFavorite']);
$folderId = $note['folderId'] ?? '';
$updatedAt = $note['updatedAt'] ?? $note['createdAt'] ?? '';
$dateStr = $updatedAt ? date('d M Y', strtotime($updatedAt)) : '';
?>
<div class="note-card <?= $isFavorite ? 'favorite' : '' ?>"
     data-note-id="<?= htmlspecialchars($id) ?>"
     data-note-title="<?= htmlspecialchars($title) ?>"
     data-note-content="<?= htmlspecialchars($body) ?>"
     data-note-color="<?= htmlspecialchars($color) ?>"
     data-note-folder-id="<?= htmlspecialchars($folderId) ?>"
     data-note-favorite="<?= $isFavorite ? '1' : '0' ?>"
     style="background-color:<?= htmlspecialchars($color) ?>; border-color:<?= htmlspecialchars($color) ?>dd"
     draggable="true">
    <div class="note-actions">
        <button type="button" data-fav-btn data-note-id="<?= htmlspecialchars($id) ?>" aria-label="Favori"><?= icon_star('w-3.5 h-3.5', $isFavorite) ?></button>
        <button type="button" data-delete-btn data-note-id="<?= htmlspecialchars($id) ?>" aria-label="Sil"><?= icon_trash('w-3.5 h-3.5') ?></button>
    </div>
    <a href="note_edit.php?id=<?= htmlspecialchars($id) ?>" class="note-body" onclick="return false;">
        <h3 class="note-title"><?= htmlspecialchars($title) ?></h3>
        <p class="note-preview"><?= htmlspecialchars(mb_substr($body, 0, 120)) ?><?= mb_strlen($body) > 120 ? '…' : '' ?></p>
    </a>
    <div class="note-footer">
        <span><?= $dateStr ?></span>
        <?php if ($isFavorite): ?><span class="star-icon"><?= icon_star('w-3 h-3', true) ?></span><?php endif; ?>
    </div>
</div>
