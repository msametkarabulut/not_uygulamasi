<?php
require_once __DIR__ . '/config/config.php';
require_login();
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: notes.php');
    exit;
}
$id = $_POST['id'] ?? '';
if ($id === '') {
    header('Location: notes.php');
    exit;
}
$notes = load_notes();
foreach ($notes as $i => $n) {
    if (($n['id'] ?? '') === $id) {
        $notes[$i]['isFavorite'] = empty($n['isFavorite']);
        break;
    }
}
save_notes($notes);
$ref = $_SERVER['HTTP_REFERER'] ?? 'notes.php';
header('Location: ' . $ref);
exit;
