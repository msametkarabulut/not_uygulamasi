<?php
require_once __DIR__ . '/config/config.php';
if (is_logged_in()) {
    header('Location: notes.php');
} else {
    header('Location: login.php');
}
exit;
