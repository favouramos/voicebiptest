<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$pdo = db();
$today = (new DateTimeImmutable('today'))->format('Y-m-d');
$total = (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ?");
$stmt->execute([$today]); $todayCount = (int)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date >= ? AND status IN ('confirmed','pending')");
$stmt->execute([$today]); $upcomingCount = (int)$stmt->fetchColumn();
$completed = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status='completed'")->fetchColumn();
$recent = $pdo->query("SELECT * FROM appointments ORDER BY created_at DESC LIMIT 8")->fetchAll();

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="hero"><div><h1>Appointment Dashboard</h1><div class="muted">Bookings captured by your Voicebip AI agent.</div></div><a class="btn btn-primary" href="appointments.php">View all appointments</a></div>
<div class="grid stats">
    <div class="card"><div class="muted">Total bookings</div><div class="stat-num"><?= $total ?></div></div>
    <div class="card"><div class="muted">Today</div><div class="stat-num"><?= $todayCount ?></div></div>
    <div class="card"><div class="muted">Upcoming</div><div class="stat-num"><?= $upcomingCount ?></div></div>
    <div class="card"><div class="muted">Completed</div><div class="stat-num"><?= $completed ?></div></div>
</div>
<div class="card" style="margin-top:18px">
    <div class="hero" style="margin-bottom:10px"><div><h2 style="margin:0">Recent bookings</h2></div></div>
    <?php if (!$recent): ?><div class="empty">No appointments have been saved yet.</div><?php else: ?>
    <div class="table-wrap"><table class="table"><thead><tr><th>Customer</th><th>Phone</th><th>Date</th><th>Time</th><th>Service</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($recent as $row): ?><tr>
        <td><?= e($row['customer_name']) ?></td><td><?= e($row['customer_phone']) ?></td>
        <td><?= e($row['appointment_date']) ?></td><td><?= e(substr($row['appointment_time'],0,5)) ?></td>
        <td><?= e($row['service']) ?></td><td><span class="badge <?= e($row['status']) ?>"><?= e(ucfirst($row['status'])) ?></span></td>
    </tr><?php endforeach; ?>
    </tbody></table></div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
