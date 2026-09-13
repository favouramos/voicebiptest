<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf((string)($_POST['csrf'] ?? ''));

    $apiKey = trim((string)($config['voicebip']['api_key'] ?? ''));
    $agentId = trim((string)($config['voicebip']['agent_id'] ?? ''));
    $base = rtrim((string)($config['voicebip']['api_base'] ?? 'https://api.voicebip.com/v1'), '/');

    if ($apiKey === '' || str_contains($apiKey, 'YOUR_') || $agentId === '' || str_contains($agentId, 'YOUR_')) {
        $error = 'Add your Voicebip API key and Agent ID to config.php first.';
    } else {
        $prompt = @file_get_contents(__DIR__ . '/system-prompt.txt');
        $tools = @file_get_contents(__DIR__ . '/voicebip-tool-definition.json');
        $toolDecoded = json_decode((string)$tools, true);

        if (!is_string($prompt) || !is_array($toolDecoded)) {
            $error = 'The system prompt or tool-definition file could not be read.';
        } else {
            $payload = json_encode([
                'webhook_url' => $config['voicebip']['webhook_url'],
                'system_prompt' => $prompt,
                'tool_definitions' => json_encode($toolDecoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $ch = curl_init($base . '/agents/' . rawurlencode($agentId));
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => 'PATCH',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
            ]);
            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response === false || $curlError !== '') {
                $error = 'Voicebip request failed: ' . $curlError;
            } else {
                $decoded = json_decode($response, true);
                if ($httpCode >= 200 && $httpCode < 300) {
                    $message = 'Voicebip agent updated successfully. Your webhook and appointment tools are now configured.';
                } else {
                    $detail = is_array($decoded) ? (string)($decoded['message'] ?? $decoded['error'] ?? 'Unknown Voicebip error') : 'Unknown Voicebip error';
                    $error = 'Voicebip returned HTTP ' . $httpCode . ': ' . $detail;
                }
            }
        }
    }
}

$pageTitle = 'Configure Voicebip';
require __DIR__ . '/includes/header.php';
?>
<div class="hero"><div><h1>Configure Voicebip Agent</h1><div class="muted">Push the supplied appointment tools, system prompt and webhook URL to your existing agent.</div></div></div>
<?php if ($message): ?><div class="notice notice-ok"><?= e($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="notice notice-err"><?= e($error) ?></div><?php endif; ?>
<div class="card">
    <h2 style="margin-top:0">Target</h2>
    <p><b>Agent:</b> <?= e($config['voicebip']['agent_id']) ?></p>
    <p><b>Webhook:</b> <?= e($config['voicebip']['webhook_url']) ?></p>
    <p><b>Tools:</b> check availability + save appointment</p>
    <form method="post" style="margin-top:18px"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><button class="btn btn-primary" type="submit">Configure this Voicebip Agent</button></form>
    <p class="small muted" style="margin-top:14px">This uses your server-side Voicebip API key from config.php. It does not display the key.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
