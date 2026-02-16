<?php
/**
 * AJAX API — not taşıma, favori, silme.
 * POST /api.php?action=move_note | toggle_favorite | delete_note | save_note
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Giriş yapılmamış.']);
    exit;
}

require_once __DIR__ . '/includes/db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    /* ── Notu klasöre taşı (drag-drop) ── */
    case 'move_note':
        $noteId   = $_POST['note_id'] ?? '';
        $folderId = $_POST['folder_id'] ?? '';
        if ($noteId === '') {
            echo json_encode(['error' => 'note_id gerekli.']);
            exit;
        }
        $notes = load_notes();
        $found = false;
        foreach ($notes as &$n) {
            if (($n['id'] ?? '') === $noteId) {
                $n['folderId'] = $folderId === '' || $folderId === 'none' ? null : $folderId;
                $n['updatedAt'] = date('c');
                $found = true;
                break;
            }
        }
        unset($n);
        if (!$found) {
            echo json_encode(['error' => 'Not bulunamadı.']);
            exit;
        }
        save_notes($notes);
        echo json_encode(['ok' => true]);
        break;

    /* ── Favori toggle ── */
    case 'toggle_favorite':
        $noteId = $_POST['note_id'] ?? '';
        if ($noteId === '') {
            echo json_encode(['error' => 'note_id gerekli.']);
            exit;
        }
        $notes = load_notes();
        $newState = false;
        foreach ($notes as &$n) {
            if (($n['id'] ?? '') === $noteId) {
                $n['isFavorite'] = empty($n['isFavorite']);
                $newState = $n['isFavorite'];
                break;
            }
        }
        unset($n);
        save_notes($notes);
        echo json_encode(['ok' => true, 'isFavorite' => $newState]);
        break;

    /* ── Not sil ── */
    case 'delete_note':
        $noteId = $_POST['note_id'] ?? '';
        if ($noteId === '') {
            echo json_encode(['error' => 'note_id gerekli.']);
            exit;
        }
        $notes = load_notes();
        $notes = array_values(array_filter($notes, function ($n) use ($noteId) {
            return ($n['id'] ?? '') !== $noteId;
        }));
        save_notes($notes);
        echo json_encode(['ok' => true]);
        break;

    /* ── Not kaydet (yeni/güncelle) ── */
    case 'save_note':
        $id       = $_POST['note_id'] ?? '';
        $title    = trim($_POST['title'] ?? '');
        $body     = trim($_POST['content'] ?? '');
        $color    = $_POST['color'] ?? '#FFDAB9';
        $folderId = $_POST['folder_id'] ?? '';
        $isFav    = !empty($_POST['is_favorite']);

        if ($title === '') {
            echo json_encode(['error' => 'Başlık gerekli.']);
            exit;
        }
        $notes = load_notes();
        $now = date('c');
        $fid = ($folderId === '' || $folderId === 'none') ? null : $folderId;

        if ($id !== '') {
            foreach ($notes as &$n) {
                if (($n['id'] ?? '') === $id) {
                    $n['title']      = $title;
                    $n['content']    = $body;
                    $n['color']      = $color;
                    $n['folderId']   = $fid;
                    $n['isFavorite'] = $isFav;
                    $n['updatedAt']  = $now;
                    break;
                }
            }
            unset($n);
        } else {
            $id = uniqid('n', true);
            array_unshift($notes, [
                'id'         => $id,
                'title'      => $title,
                'content'    => $body,
                'color'      => $color,
                'folderId'   => $fid,
                'isFavorite' => $isFav,
                'createdAt'  => $now,
                'updatedAt'  => $now,
                'userId'     => get_user_id(),
            ]);
        }
        save_notes($notes);
        echo json_encode(['ok' => true, 'id' => $id]);
        break;

    /* ── Veri versiyonu kontrol (polling) ── */
    case 'check_version':
        $notes = load_notes();
        $folders = load_folders();
        $hash = md5(json_encode($notes) . json_encode($folders));
        $count = count($notes);
        echo json_encode(['hash' => $hash, 'count' => $count]);
        break;

    /* ── Tüm notları JSON olarak getir ── */
    case 'get_notes':
        $notes = load_notes();
        $folders = load_folders();
        $hash = md5(json_encode($notes) . json_encode($folders));
        echo json_encode(['notes' => $notes, 'folders' => $folders, 'hash' => $hash]);
        break;

    default:
        echo json_encode(['error' => 'Geçersiz aksiyon.']);
}
