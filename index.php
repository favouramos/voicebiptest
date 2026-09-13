<?php
require __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($username === ($config['admin']['username'] ?? '') && password_verify($password, (string)($config['admin']['password_hash'] ?? ''))) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        redirect('dashboard.php');
    }
    $error = 'Invalid username or password.';
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="login-card card">
    <div class="login-logo">Sophia The Promoter</div>
    <p class="muted">Appointment Booking Control Center</p>
    <?php if ($error): ?><div class="notice notice-err"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="grid" style="gap:14px">
        <div class="field"><label>Username</label><input name="username" autocomplete="username" required></div>
        <div class="field"><label>Password</label><input type="password" name="password" autocomplete="current-password" required></div>
        <button class="btn btn-primary" type="submit">Sign in</button>
    </form>
    <p class="small muted" style="margin-top:14px">Use install.php once after upload to configure the database and admin login.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
