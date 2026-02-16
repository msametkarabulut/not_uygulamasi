<?php
/**
 * Notlar ve klasörler: Firestore (service account varsa) veya yerel JSON.
 */

function get_user_id() {
    $u = current_user();
    return $u ? ($u['id'] ?? '1') : '1';
}

function _use_firestore() {
    static $use = null;
    if ($use === null) {
        $base = defined('BASE_PATH') ? BASE_PATH : (__DIR__ . '/..');
        $configDir = $base . '/config';
        $path = null;
        if (is_file($configDir . '/firebase-service-account.json')) {
            $path = $configDir . '/firebase-service-account.json';
        } elseif (is_file($base . '/firebase-service-account.json')) {
            $path = $base . '/firebase-service-account.json';
        } elseif (is_file($base . '/notlar-cf9d7-firebase-adminsdk-fbsvc-b83b4365f3.json')) {
            $path = $base . '/notlar-cf9d7-firebase-adminsdk-fbsvc-b83b4365f3.json';
        } else {
            foreach ([$configDir, $base] as $dir) {
                if (is_dir($dir)) {
                    foreach (glob($dir . '/*firebase*adminsdk*.json') ?: [] as $p) {
                        if (is_file($p) && strpos(basename($p), 'example') === false) {
                            $path = $p;
                            break 2;
                        }
                    }
                }
            }
        }
        $use = $path && is_file($path);
        if ($use && !class_exists('GuzzleHttp\Client')) {
            $autoload = defined('BASE_PATH') ? BASE_PATH . '/vendor/autoload.php' : __DIR__ . '/../vendor/autoload.php';
            $use = is_file($autoload);
            if ($use) {
                require_once $autoload;
            }
        }
        if ($use) {
            require_once __DIR__ . '/firestore_client.php';
            try {
                $use = function_exists('firestore_get_access_token') && firestore_get_access_token() !== null;
            } catch (Throwable $e) {
                $use = false;
            }
        }
    }
    return $use;
}

/* ---------- Notlar ---------- */

function load_notes() {
    $uid = get_user_id();
    if (_use_firestore()) {
        $list = firestore_get_documents('notes', $uid);
        return array_values($list);
    }
    $dir = BASE_PATH . '/data/notes';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $file = $dir . '/' . $uid . '.json';
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function save_notes(array $notes) {
    $uid = get_user_id();
    if (_use_firestore()) {
        $existing = load_notes();
        $existingIds = array_column($existing, 'id');
        $newIds = array_column($notes, 'id');
        foreach ($notes as $n) {
            $id = $n['id'] ?? '';
            if ($id === '') {
                continue;
            }
            $data = [
                'userId' => $uid,
                'title' => $n['title'] ?? '',
                'content' => $n['content'] ?? '',
                'color' => $n['color'] ?? '#FFDAB9',
                'folderId' => (string)($n['folderId'] ?? ''),
                'isFavorite' => !empty($n['isFavorite']),
                'createdAt' => $n['createdAt'] ?? date('c'),
                'updatedAt' => $n['updatedAt'] ?? date('c'),
            ];
            firestore_set_document('notes', $id, $data);
        }
        foreach (array_diff($existingIds, $newIds) as $id) {
            firestore_delete_document('notes', $id);
        }
        return;
    }
    $dir = BASE_PATH . '/data/notes';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($dir . '/' . $uid . '.json', json_encode($notes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/* ---------- Klasörler ---------- */

function load_folders() {
    $uid = get_user_id();
    if (_use_firestore()) {
        $list = firestore_get_documents('folders', $uid);
        return array_values($list);
    }
    $dir = BASE_PATH . '/data/folders';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $file = $dir . '/' . $uid . '.json';
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function save_folders(array $folders) {
    $uid = get_user_id();
    if (_use_firestore()) {
        $existing = load_folders();
        $existingIds = array_column($existing, 'id');
        $newIds = array_column($folders, 'id');
        foreach ($folders as $f) {
            $id = $f['id'] ?? '';
            if ($id === '') {
                continue;
            }
            $data = [
                'userId' => $uid,
                'name' => $f['name'] ?? '',
                'color' => $f['color'] ?? '#FFDAB9',
                'createdAt' => $f['createdAt'] ?? date('c'),
            ];
            firestore_set_document('folders', $id, $data);
        }
        foreach (array_diff($existingIds, $newIds) as $id) {
            firestore_delete_document('folders', $id);
        }
        return;
    }
    $dir = BASE_PATH . '/data/folders';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($dir . '/' . $uid . '.json', json_encode($folders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function get_note_by_id($id) {
    $notes = load_notes();
    foreach ($notes as $n) {
        if (($n['id'] ?? '') === $id) {
            return $n;
        }
    }
    return null;
}

function folders_with_counts() {
    $folders = load_folders();
    $notes = load_notes();
    foreach ($folders as &$f) {
        $f['noteCount'] = 0;
        foreach ($notes as $n) {
            if (($n['folderId'] ?? null) === ($f['id'] ?? null)) {
                $f['noteCount']++;
            }
        }
    }
    return $folders;
}
