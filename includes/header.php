<?php
/** @var array $config */
$pageTitle = $pageTitle ?? $config['app']['name'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="shell">
<?php if (is_logged_in()): ?>
    <aside class="sidebar">
        <div class="brand"><span class="brand-dot"></span><?= e($config['booking']['business_name']) ?></div>
        <div class="brand-sub">Appointment Control Center</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="appointments.php">Appointments</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>
<?php endif; ?>
<main class="main <?= is_logged_in() ? '' : 'main-full' ?>">
<?php if (is_logged_in()): ?>
    <header class="topbar">
        <div><strong><?= e($pageTitle) ?></strong></div>
        <div class="topbar-right">Logged in as <?= e($_SESSION['admin_username'] ?? 'admin') ?></div>
    </header>
<?php endif; ?>
<div class="content">
