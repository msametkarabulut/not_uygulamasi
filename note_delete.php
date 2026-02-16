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
$notes = array_values(array_filter($notes, function ($n) use ($id) {
    return ($n['id'] ?? '') !== $id;
}));
save_notes($notes);
$ref = $_SERVER['HTTP_REFERER'] ?? 'notes.php';
header('Location: ' . $ref);
exit;
