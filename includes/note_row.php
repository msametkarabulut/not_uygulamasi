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
<div class="note-row <?= $isFavorite ? 'favorite' : '' ?>"
     data-note-id="<?= htmlspecialchars($id) ?>"
     data-note-title="<?= htmlspecialchars($title) ?>"
     data-note-content="<?= htmlspecialchars($body) ?>"
     data-note-color="<?= htmlspecialchars($color) ?>"
     data-note-folder-id="<?= htmlspecialchars($folderId) ?>"
     data-note-favorite="<?= $isFavorite ? '1' : '0' ?>"
     style="border-left:4px solid <?= htmlspecialchars($color) ?>"
     draggable="true">
    <div class="note-row-content" style="cursor:pointer">
        <h3 class="note-row-title"><?= htmlspecialchars($title) ?></h3>
        <p class="note-row-preview"><?= htmlspecialchars($body) ?></p>
    </div>
    <span class="note-row-date"><?= $dateStr ?></span>
    <div class="note-actions" style="display:flex;gap:.25rem">
        <button type="button" data-fav-btn data-note-id="<?= htmlspecialchars($id) ?>"><?= icon_star('w-4 h-4', $isFavorite) ?></button>
        <button type="button" data-delete-btn data-note-id="<?= htmlspecialchars($id) ?>" class="text-danger"><?= icon_trash('w-4 h-4') ?></button>
    </div>
</div>
