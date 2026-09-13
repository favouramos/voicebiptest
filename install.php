<?php
// First-run installer. Delete or rename this file after installation.
require __DIR__ . '/includes/bootstrap.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim((string)($_POST['db_host'] ?? 'localhost'));
    $dbPort = (int)($_POST['db_port'] ?? 3306);
    $dbName = trim((string)($_POST['db_name'] ?? ''));
    $dbUser = trim((string)($_POST['db_user'] ?? ''));
    $dbPass = (string)($_POST['db_pass'] ?? '');
    $adminUser = trim((string)($_POST['admin_user'] ?? 'admin'));
    $adminPass = (string)($_POST['admin_pass'] ?? '');
    $voiceApi = trim((string)($_POST['voice_api'] ?? ''));
    $voiceAgent = trim((string)($_POST['voice_agent'] ?? ''));
    $voiceSecret = trim((string)($_POST['voice_secret'] ?? ''));

    if ($dbName === '' || $dbUser === '') $errors[] = 'Database name and username are required.';
    if ($adminUser === '' || strlen($adminPass) < 10) $errors[] = 'Admin username is required and password must be at least 10 characters.';

    if (!$errors) {
        try {
            $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};charset=utf8mb4", $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $dbName) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo->exec('USE `' . str_replace('`', '``', $dbName) . '`');
            $schema = file_get_contents(__DIR__ . '/database/schema_mysql.sql');
            $pdo->exec($schema);

            $newConfig = [
                'app' => [
                    'name' => 'Sophia The Promoter — Appointment Booking',
                    'base_url' => 'https://calling.sophiathepromoter.com',
                    'timezone' => 'Africa/Lagos',
                    'session_name' => 'sophia_booking_admin',
                ],
                'admin' => [
                    'username' => $adminUser,
                    'password_hash' => password_hash($adminPass, PASSWORD_DEFAULT),
                ],
                'db' => [
                    'driver' => 'mysql', 'host' => $dbHost, 'port' => $dbPort, 'name' => $dbName, 'user' => $dbUser, 'pass' => $dbPass, 'charset' => 'utf8mb4'
                ],
                'voicebip' => [
                    'api_base' => 'https://api.voicebip.com/v1',
                    'api_key' => $voiceApi ?: 'YOUR_VOICEBIP_API_KEY',
                    'agent_id' => $voiceAgent ?: 'agt_YOUR_AGENT_ID',
                    'signing_secret' => $voiceSecret ?: 'YOUR_VOICEBIP_SIGNING_SECRET',
                    'webhook_url' => 'https://calling.sophiathepromoter.com/api/voicebip.php',
                ],
                'booking' => ['default_status' => 'confirmed', 'business_name' => 'Sophia The Promoter'],
            ];

            $export = "<?php\nreturn " . var_export($newConfig, true) . ";\n";
            if (file_put_contents(__DIR__ . '/config.php', $export, LOCK_EX) === false) {
                throw new RuntimeException('Could not write config.php. Check hosting file permissions.');
            }
            $success = 'Installation completed. Delete install.php now, then log in.';
        } catch (Throwable $e) {
            $errors[] = 'Installation failed: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Installation';
require __DIR__ . '/includes/header.php';
?>
<div class="login-card card" style="max-width:720px"><h1>Appointment Booking Installer</h1><p class="muted">This creates the MySQL tables and writes your settings to config.php.</p>
<?php foreach ($errors as $error): ?><div class="notice notice-err"><?= e($error) ?></div><?php endforeach; ?>
<?php if ($success): ?><div class="notice notice-ok"><?= e($success) ?></div><a class="btn btn-primary" href="index.php">Go to login</a><?php else: ?>
<form method="post" class="form-grid">
<div class="field"><label>MySQL host</label><input name="db_host" value="localhost" required></div>
<div class="field"><label>MySQL port</label><input name="db_port" value="3306" type="number" required></div>
<div class="field"><label>Database name</label><input name="db_name" placeholder="your_database" required></div>
<div class="field"><label>Database user</label><input name="db_user" placeholder="your_db_user" required></div>
<div class="field"><label>Database password</label><input name="db_pass" type="password"></div>
<div class="field"><label>Admin username</label><input name="admin_user" value="admin" required></div>
<div class="field"><label>Admin password</label><input name="admin_pass" type="password" minlength="10" required></div>
<div class="field"><label>Voicebip API key</label><input name="voice_api" placeholder="pk_live_..." value="<?= e($_POST['voice_api'] ?? '') ?>"></div>
<div class="field"><label>Voicebip Agent ID</label><input name="voice_agent" placeholder="agt_..." value="<?= e($_POST['voice_agent'] ?? '') ?>"></div>
<div class="field full"><label>Voicebip Signing Secret</label><input name="voice_secret" placeholder="Workspace → Signing Secret"></div>
<div class="field full"><button class="btn btn-primary" type="submit">Install system</button></div>
</form>
<p class="small muted">Webhook URL will be <b>https://calling.sophiathepromoter.com/api/voicebip.php</b>.</p>
<?php endif; ?></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
