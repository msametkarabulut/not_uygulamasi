<?php
/**
 * Firestore REST API istemcisi (service account ile).
 * config/firebase-service-account.json dosyası gerekli.
 */

if (!function_exists('firestore_get_db')) {

function firestore_service_account_path() {
    $base = defined('BASE_PATH') ? BASE_PATH : (__DIR__ . '/..');
    $configDir = $base . '/config';
    $candidates = [
        $configDir . '/firebase-service-account.json',
        $base . '/firebase-service-account.json',
        $base . '/notlar-cf9d7-firebase-adminsdk-fbsvc-b83b4365f3.json',
    ];
    foreach ($candidates as $path) {
        if (is_file($path)) {
            return $path;
        }
    }
    // config veya proje kökünde *firebase*adminsdk*.json ara
    foreach ([$configDir, $base] as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        foreach (glob($dir . '/*firebase*adminsdk*.json') ?: [] as $path) {
            if (is_file($path) && strpos(basename($path), 'example') === false) {
                return $path;
            }
        }
    }
    return $configDir . '/firebase-service-account.json';
}

function firestore_has_service_account() {
    $path = firestore_service_account_path();
    return $path && is_file($path);
}

/**
 * Service account JSON ile OAuth2 access token alır.
 */
function firestore_get_access_token() {
    static $cached = null;
    if ($cached !== null && $cached['expires_at'] > time()) {
        return $cached['access_token'];
    }
    $path = firestore_service_account_path();
    if (!is_file($path)) {
        return null;
    }
    $key = json_decode(file_get_contents($path), true);
    if (!$key || empty($key['client_email']) || empty($key['private_key'])) {
        return null;
    }
    $now = time();
    $payload = [
        'iss' => $key['client_email'],
        'sub' => $key['client_email'],
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600,
        'scope' => 'https://www.googleapis.com/auth/datastore',
    ];
    $base64url = function ($data) {
        return rtrim(strtr(base64_encode(is_string($data) ? $data : json_encode($data)), '+/', '-_'), '=');
    };
    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $segments = [
        $base64url($header),
        $base64url($payload),
    ];
    $signing = implode('.', $segments);
    $sig = '';
    $pk = openssl_pkey_get_private($key['private_key']);
    if (!$pk) {
        return null;
    }
    openssl_sign($signing, $sig, $pk, OPENSSL_ALGO_SHA256);
    $segments[] = $base64url($sig);
    $jwt = implode('.', $segments);
    $client = new \GuzzleHttp\Client();
    $res = $client->post('https://oauth2.googleapis.com/token', [
        'form_params' => [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ],
        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
    ]);
    $body = json_decode($res->getBody()->getContents(), true);
    $cached = [
        'access_token' => $body['access_token'],
        'expires_at' => $now + (int)($body['expires_in'] ?? 3600) - 60,
    ];
    return $cached['access_token'];
}

/**
 * Firestore REST API base URL
 */
function firestore_base_url() {
    $projectId = defined('FIREBASE_PROJECT_ID') ? FIREBASE_PROJECT_ID : 'notlar-cf9d7';
    return "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents";
}

/**
 * Belge verisini Firestore 'fields' formatına çevirir.
 */
function firestore_encode_fields($data) {
    $fields = [];
    foreach ($data as $k => $v) {
        if ($v === null) {
            $fields[$k] = ['nullValue' => 'NULL_VALUE'];
        } elseif (is_bool($v)) {
            $fields[$k] = ['booleanValue' => $v];
        } elseif (is_int($v)) {
            $fields[$k] = ['integerValue' => (string)$v];
        } elseif (is_float($v)) {
            $fields[$k] = ['doubleValue' => $v];
        } else {
            $fields[$k] = ['stringValue' => (string)$v];
        }
    }
    return $fields;
}

/**
 * Firestore 'fields' formatından düz array'e çevirir.
 */
function firestore_decode_document($doc) {
    if (empty($doc['fields'])) {
        return [];
    }
    $out = [];
    foreach ($doc['fields'] as $k => $v) {
        if (isset($v['stringValue'])) {
            $out[$k] = $v['stringValue'];
        } elseif (isset($v['booleanValue'])) {
            $out[$k] = $v['booleanValue'];
        } elseif (isset($v['integerValue'])) {
            $out[$k] = (int)$v['integerValue'];
        } elseif (isset($v['doubleValue'])) {
            $out[$k] = (float)$v['doubleValue'];
        } elseif (isset($v['nullValue'])) {
            $out[$k] = null;
        } else {
            $out[$k] = null;
        }
    }
    return $out;
}

/**
 * Koleksiyondan userId eşleşen belgeleri getirir.
 */
function firestore_get_documents($collection, $userId) {
    $token = firestore_get_access_token();
    if (!$token) {
        return [];
    }
    $projectId = defined('FIREBASE_PROJECT_ID') ? FIREBASE_PROJECT_ID : 'notlar-cf9d7';
    $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents:runQuery";
    $body = [
        'structuredQuery' => [
            'from' => [['collectionId' => $collection]],
            'where' => [
                'fieldFilter' => [
                    'field' => ['fieldPath' => 'userId'],
                    'op' => 'EQUAL',
                    'value' => ['stringValue' => $userId],
                ],
            ],
        ],
    ];
    $client = new \GuzzleHttp\Client();
    try {
        $res = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => $body,
        ]);
    } catch (\Throwable $e) {
        return [];
    }
    $items = json_decode($res->getBody()->getContents(), true);
    $list = [];
    foreach ($items ?: [] as $item) {
        if (empty($item['document'])) {
            continue;
        }
        $doc = $item['document'];
        $name = $doc['name'] ?? '';
        $id = $name ? substr($name, strrpos($name, '/') + 1) : '';
        $data = firestore_decode_document($doc);
        $data['id'] = $id;
        $list[] = $data;
    }
    return $list;
}

/**
 * Tek belge yazar (oluşturur veya günceller).
 */
function firestore_set_document($collection, $documentId, $data) {
    $token = firestore_get_access_token();
    if (!$token) {
        return false;
    }
    $base = firestore_base_url();
    $url = "{$base}/{$collection}/{$documentId}";
    $payload = ['fields' => firestore_encode_fields($data)];
    $client = new \GuzzleHttp\Client();
    try {
        $client->patch($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => ['fields' => $payload['fields']],
        ]);
        return true;
    } catch (\Throwable $e) {
        return false;
    }
}

/**
 * Tek belge siler.
 */
function firestore_delete_document($collection, $documentId) {
    $token = firestore_get_access_token();
    if (!$token) {
        return false;
    }
    $base = firestore_base_url();
    $url = "{$base}/{$collection}/{$documentId}";
    $client = new \GuzzleHttp\Client();
    try {
        $client->delete($url, [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        return true;
    } catch (\Throwable $e) {
        return false;
    }
}

}
