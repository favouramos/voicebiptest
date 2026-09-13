<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('index.php');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf(string $token): void
{
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function normalize_phone(string $phone): string
{
    $phone = trim($phone);
    $digits = preg_replace('/\D+/', '', $phone) ?? '';

    if (str_starts_with($phone, '+')) {
        return '+' . $digits;
    }
    if (str_starts_with($digits, '234')) {
        return '+' . $digits;
    }
    if (str_starts_with($digits, '0') && strlen($digits) === 11) {
        return '+234' . substr($digits, 1);
    }
    return $phone;
}

function parse_date_input(string $value): ?string
{
    $value = trim($value);
    $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'];

    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat('!' . $format, $value, new DateTimeZone('Africa/Lagos'));
        if ($dt && $dt->format($format) === $value) {
            return $dt->format('Y-m-d');
        }
    }

    $ts = strtotime($value);
    if ($ts !== false) {
        return (new DateTime('@' . $ts))->setTimezone(new DateTimeZone('Africa/Lagos'))->format('Y-m-d');
    }
    return null;
}

function parse_time_input(string $value): ?string
{
    $value = trim($value);
    foreach (['H:i', 'H:i:s', 'g:i A', 'g A', 'H'] as $format) {
        $dt = DateTime::createFromFormat('!' . $format, $value, new DateTimeZone('Africa/Lagos'));
        if ($dt && $dt->format($format) === $value) {
            return $dt->format('H:i:00');
        }
    }
    $ts = strtotime($value);
    if ($ts !== false) {
        return date('H:i:00', $ts);
    }
    return null;
}

function tool_json(array $data): never
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function verify_voicebip_signature(string $rawBody): bool
{
    global $config;
    $secret = (string)($config['voicebip']['signing_secret'] ?? '');
    if ($secret === '' || str_contains($secret, 'YOUR_')) {
        return false;
    }

    $signature = (string)($_SERVER['HTTP_X_VOICEBIP_SIGNATURE'] ?? '');
    $timestamp = (string)($_SERVER['HTTP_X_VOICEBIP_TIMESTAMP'] ?? '');

    if ($signature === '' || $timestamp === '' || !ctype_digit($timestamp)) {
        return false;
    }

    $age = abs(time() - (int)$timestamp);
    if ($age > 300) {
        return false;
    }

    $expected = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $rawBody, $secret);
    return hash_equals($expected, $signature);
}

function install_schema(PDO $pdo): void
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $sql = file_get_contents(__DIR__ . '/../database/schema_sqlite.sql');
    } else {
        $sql = file_get_contents(__DIR__ . '/../database/schema_mysql.sql');
    }
    if ($sql === false) {
        throw new RuntimeException('Database schema file not found.');
    }
    $pdo->exec($sql);
}
