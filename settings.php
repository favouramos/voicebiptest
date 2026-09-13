<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$pageTitle = 'Settings';
$vb = $config['voicebip'];
require __DIR__ . '/includes/header.php';
?>
<div class="hero"><div><h1>Integration Settings</h1><div class="muted">Values are read from config.php.</div></div></div>
<div class="split">
<div class="card"><h2 style="margin-top:0">Database</h2><p><b>Driver:</b> <?= e($config['db']['driver']) ?></p><p><b>Host:</b> <?= e($config['db']['host']) ?></p><p><b>Database:</b> <?= e($config['db']['name']) ?></p><p><b>User:</b> <?= e($config['db']['user']) ?></p></div>
<div class="card"><h2 style="margin-top:0">Voicebip</h2><p><b>Agent ID:</b> <?= e($vb['agent_id']) ?></p><p><b>Webhook URL:</b> <?= e($vb['webhook_url']) ?></p><p><b>API key:</b> <?= str_contains((string)$vb['api_key'], 'YOUR_') ? '<span class="danger-text">Not configured</span>' : '<span class="check">Configured</span>' ?></p><p><b>Signing secret:</b> <?= str_contains((string)$vb['signing_secret'], 'YOUR_') ? '<span class="danger-text">Not configured</span>' : '<span class="check">Configured</span>' ?></p></div>
</div>
<div class="actions" style="margin-top:18px"><a class="btn btn-primary" href="configure_voicebip.php">Configure Voicebip Agent</a></div>
<div class="card" style="margin-top:18px"><h2 style="margin-top:0">Voicebip setup</h2><p>After entering your credentials in <code>config.php</code>, use the provided <code>voicebip-tool-definition.json</code> and system prompt in the Voicebip Agent configuration. The webhook endpoint is:</p><div class="code"><?= e($vb['webhook_url']) ?></div><p class="small muted">The webhook verifies Voicebip HMAC-SHA256 signatures, handles tool.invocation, and stores call lifecycle events.</p></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
