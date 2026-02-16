<?php
/**
 * Firebase client tarafı için JavaScript config.
 * Değerler config.php'de tanımlı (firebase.credentials.php veya ortam değişkenleri).
 */
$firebase_config = [
    'apiKey'            => FIREBASE_API_KEY,
    'authDomain'        => FIREBASE_AUTH_DOMAIN,
    'projectId'         => FIREBASE_PROJECT_ID,
    'storageBucket'     => FIREBASE_STORAGE_BUCKET,
    'messagingSenderId' => FIREBASE_MESSAGING_SENDER_ID,
    'appId'             => FIREBASE_APP_ID,
    'measurementId'     => defined('FIREBASE_MEASUREMENT_ID') ? FIREBASE_MEASUREMENT_ID : '',
];
