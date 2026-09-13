<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf((string)($_POST['csrf'] ?? ''));
    $id = (int)($_POST['id'] ?? 0);
    $status = (string)($_POST['status'] ?? '');
    if (in_array($status, ['confirmed','pending','completed','cancelled'], true)) {
        $stmt = $pdo->prepare('UPDATE appointments SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$status, $id]);
    }
    redirect('appointments.php');
}

$search = trim((string)($_GET['q'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$params = [];
$where = [];
if ($search !== '') { $where[] = '(customer_name LIKE ? OR customer_phone LIKE ? OR service LIKE ? OR booking_id LIKE ?)'; $params = array_merge($params, array_fill(0, 4, '%' . $search . '%')); }
if (in_array($status, ['confirmed','pending','completed','cancelled'], true)) { $where[] = 'status = ?'; $params[] = $status; }
$sql = 'SELECT * FROM appointments' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY appointment_date ASC, appointment_time ASC, created_at DESC';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();

$pageTitle = 'Appointments';
require __DIR__ . '/includes/header.php';
?>
<div class="hero"><div><h1>Appointments</h1><div class="muted">Every booking saved by the AI is shown here.</div></div></div>
<div class="card" style="margin-bottom:18px"><form method="get" class="form-grid"><div class="field"><label>Search</label><input name="q" value="<?= e($search) ?>" placeholder="Name, phone, service, booking ID"></div><div class="field"><label>Status</label><select name="status"><option value="">All</option><?php foreach (['confirmed','pending','completed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div><div class="field full"><button class="btn btn-primary" type="submit">Filter</button></div></form></div>
<div class="card"><div class="table-wrap"><table class="table"><thead><tr><th>Booking ID</th><th>Customer</th><th>Phone</th><th>Date</th><th>Time</th><th>Service</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php if (!$rows): ?><tr><td colspan="8" class="empty">No appointments found.</td></tr><?php endif; ?>
<?php foreach ($rows as $row): ?><tr>
<td><?= e($row['booking_id']) ?></td><td><?= e($row['customer_name']) ?></td><td><?= e($row['customer_phone']) ?></td><td><?= e($row['appointment_date']) ?></td><td><?= e(substr($row['appointment_time'],0,5)) ?></td><td><?= e($row['service']) ?></td>
<td><span class="badge <?= e($row['status']) ?>"><?= e(ucfirst($row['status'])) ?></span></td>
<td><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><select name="status" onchange="this.form.submit()"><option value="confirmed" <?= $row['status']==='confirmed'?'selected':'' ?>>Confirmed</option><option value="pending" <?= $row['status']==='pending'?'selected':'' ?>>Pending</option><option value="completed" <?= $row['status']==='completed'?'selected':'' ?>>Completed</option><option value="cancelled" <?= $row['status']==='cancelled'?'selected':'' ?>>Cancelled</option></select></form></td>
</tr><?php endforeach; ?></tbody></table></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
