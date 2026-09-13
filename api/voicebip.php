<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

$rawBody = file_get_contents('php://input');
if ($rawBody === false || $rawBody === '') {
    tool_json(['error' => 'empty_body']);
}

if (!verify_voicebip_signature($rawBody)) {
    http_response_code(401);
    tool_json(['error' => 'invalid_signature']);
}

$event = json_decode($rawBody, true);
if (!is_array($event)) {
    http_response_code(400);
    tool_json(['error' => 'invalid_json']);
}

$eventType = (string)($event['event_type'] ?? '');

// Custom tool invocation: the caller is waiting for this JSON response.
if ($eventType === 'tool.invocation') {
    handle_tool_invocation($event);
}

// Lifecycle events are acknowledged quickly and saved for audit/debugging.
handle_lifecycle_event($event);
tool_json(['received' => true]);

function handle_tool_invocation(array $event): never
{
    global $config;
    $tool = $event['tool'] ?? [];
    $name = (string)($tool['name'] ?? '');
    $args = $tool['arguments'] ?? [];
    if (!is_array($args)) {
        tool_json(['error' => 'invalid_arguments']);
    }

    $pdo = db();

    if ($name === 'check_appointment_availability') {
        $date = parse_date_input((string)($args['appointment_date'] ?? ''));
        $time = parse_time_input((string)($args['appointment_time'] ?? ''));
        if (!$date || !$time) {
            tool_json(['available' => false, 'error' => 'invalid_date_or_time', 'message' => 'Please provide the date as YYYY-MM-DD and time as HH:MM.']);
        }

        $stmt = $pdo->prepare('SELECT booking_id, status FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND status <> ? LIMIT 1');
        $stmt->execute([$date, $time, 'cancelled']);
        $existing = $stmt->fetch();

        if ($existing) {
            tool_json(['available' => false, 'message' => 'That appointment slot is already booked. Ask the caller for another time.']);
        }
        tool_json(['available' => true, 'message' => 'That appointment slot is available.']);
    }

    if ($name === 'save_appointment') {
        save_appointment($event, $args);
    }

    tool_json(['error' => 'unknown_tool', 'tool' => $name]);
}

function save_appointment(array $event, array $args): never
{
    global $config;
    $required = ['customer_name', 'customer_phone', 'appointment_date', 'appointment_time', 'service'];
    foreach ($required as $key) {
        if (trim((string)($args[$key] ?? '')) === '') {
            tool_json(['success' => false, 'error' => 'missing_' . $key, 'message' => 'A required booking detail is missing.']);
        }
    }

    $date = parse_date_input((string)$args['appointment_date']);
    $time = parse_time_input((string)$args['appointment_time']);
    if (!$date || !$time) {
        tool_json(['success' => false, 'error' => 'invalid_date_or_time', 'message' => 'The date/time format is invalid.']);
    }

    $slot = new DateTimeImmutable($date . ' ' . $time, new DateTimeZone('Africa/Lagos'));
    $now = new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos'));
    if ($slot <= $now) {
        tool_json(['success' => false, 'error' => 'past_appointment', 'message' => 'That time has already passed. Ask the caller for a future time.']);
    }

    $phone = normalize_phone((string)$args['customer_phone']);
    $name = trim((string)$args['customer_name']);
    $service = trim((string)$args['service']);
    $notes = trim((string)($args['notes'] ?? ''));
    $callId = (string)($event['call_id'] ?? '');
    $toolCallId = (string)($event['tool']['tool_call_id'] ?? '');
    $agentId = (string)($event['agent_id'] ?? '');
    $bookingId = 'BK-' . strtoupper(bin2hex(random_bytes(4)));

    $pdo = db();

    // First check the slot, then insert atomically enough for a normal PHP/MySQL request.
    $stmt = $pdo->prepare('SELECT booking_id FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND status <> ? LIMIT 1');
    $stmt->execute([$date, $time, 'cancelled']);
    if ($stmt->fetch()) {
        tool_json(['success' => false, 'error' => 'slot_unavailable', 'message' => 'That appointment time has just been booked. Ask the caller for another time.']);
    }

    if ($toolCallId !== '') {
        $stmt = $pdo->prepare('SELECT booking_id, customer_name, appointment_date, appointment_time FROM appointments WHERE tool_call_id = ? LIMIT 1');
        $stmt->execute([$toolCallId]);
        if ($existing = $stmt->fetch()) {
            tool_json([
                'success' => true,
                'already_saved' => true,
                'booking_id' => $existing['booking_id'],
                'customer_name' => $existing['customer_name'],
                'appointment_date' => $existing['appointment_date'],
                'appointment_time' => substr($existing['appointment_time'], 0, 5),
                'message' => 'The appointment was already saved.',
            ]);
        }
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO appointments (booking_id, call_id, tool_call_id, agent_id, customer_name, customer_phone, appointment_date, appointment_time, service, notes, status, source, raw_arguments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $bookingId,
            $callId ?: null,
            $toolCallId ?: null,
            $agentId ?: null,
            $name,
            $phone,
            $date,
            $time,
            $service,
            $notes ?: null,
            $config['booking']['default_status'] ?? 'confirmed',
            'voicebip',
            json_encode($args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    } catch (PDOException $e) {
        // Unique slot or tool-call constraints can happen under concurrent requests.
        if ((int)($e->errorInfo[1] ?? 0) === 1062 || str_contains(strtolower($e->getMessage()), 'unique')) {
            tool_json(['success' => false, 'error' => 'slot_unavailable', 'message' => 'That appointment time is already booked. Ask the caller for another time.']);
        }
        error_log('Appointment save failed: ' . $e->getMessage());
        tool_json(['success' => false, 'error' => 'database_error', 'message' => 'The booking could not be saved right now.']);
    }

    tool_json([
        'success' => true,
        'booking_id' => $bookingId,
        'customer_name' => $name,
        'appointment_date' => $date,
        'appointment_time' => substr($time, 0, 5),
        'service' => $service,
        'message' => 'Appointment saved successfully. Confirm the booking details with the caller.',
    ]);
}

function handle_lifecycle_event(array $event): void
{
    $pdo = db();
    $eventId = (string)($event['event_id'] ?? ($_SERVER['HTTP_X_VOICEBIP_EVENT_ID'] ?? ''));
    if ($eventId === '') {
        return;
    }
    $payload = $event['payload'] ?? [];
    $stmt = $pdo->prepare('INSERT INTO call_events (event_id, event_type, agent_id, call_id, from_number, to_number, payload) VALUES (?, ?, ?, ?, ?, ?, ?)');
    try {
        $stmt->execute([
            $eventId,
            (string)($event['event_type'] ?? 'unknown'),
            (string)($event['agent_id'] ?? ''),
            (string)($payload['call_id'] ?? ''),
            (string)($event['from'] ?? ($payload['from_number'] ?? '')),
            (string)($event['number'] ?? ($payload['to_number'] ?? '')),
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    } catch (PDOException $e) {
        // Voicebip may retry an event with the same event_id. Treat duplicate as already processed.
        if (!str_contains(strtolower($e->getMessage()), 'duplicate') && !str_contains(strtolower($e->getMessage()), 'unique')) {
            error_log('Voicebip event save failed: ' . $e->getMessage());
        }
    }
}
