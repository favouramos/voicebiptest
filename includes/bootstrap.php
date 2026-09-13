<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config.php';

date_default_timezone_set($config['app']['timezone'] ?? 'Africa/Lagos');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($config['app']['session_name'] ?? 'appointment_admin');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
