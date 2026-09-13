<?php
return [
    'app' => [
        'name' => 'Sophia The Promoter — Appointment Booking',
        'base_url' => 'https://calling.sophiathepromoter.com',
        'timezone' => 'Africa/Lagos',
        'session_name' => 'sophia_booking_admin',
    ],
    'admin' => [
        'username' => 'admin',
        'password_hash' => 'PASTE_PASSWORD_HASH_HERE',
    ],
    'db' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'YOUR_DATABASE_NAME',
        'user' => 'YOUR_DATABASE_USER',
        'pass' => 'YOUR_DATABASE_PASSWORD',
        'charset' => 'utf8mb4',
    ],
    'voicebip' => [
        'api_base' => 'https://api.voicebip.com/v1',
        'api_key' => 'YOUR_VOICEBIP_API_KEY',
        'agent_id' => 'agt_YOUR_AGENT_ID',
        'signing_secret' => 'YOUR_VOICEBIP_SIGNING_SECRET',
        'webhook_url' => 'https://calling.sophiathepromoter.com/api/voicebip.php',
    ],
    'booking' => [
        'default_status' => 'confirmed',
        'business_name' => 'Sophia The Promoter',
    ],
];
