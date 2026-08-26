<?php
require_once 'connect_amsos.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

function respond($success, $payload = []) {
    echo json_encode(array_merge(['success' => $success], $payload));
    exit;
}

function fetch_row_datetime($date, $time) {
    $date = trim((string)$date);
    $time = trim((string)$time);

    if ($date === '') {
        return null;
    }

    if ($time === '') {
        $time = '08:00 AM';
    }

    $formats = ['Y-m-d h:i:s A', 'Y-m-d h:i A', 'Y-m-d H:i:s', 'Y-m-d H:i'];
    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $date . ' ' . $time);
        if ($dt instanceof DateTime) {
            return $dt;
        }
    }

    $ts = strtotime($date . ' ' . $time);
    if ($ts !== false) {
        $dt = new DateTime();
        $dt->setTimestamp($ts);
        return $dt;
    }

    return null;
}

function request_profile($row) {
    $text = strtolower(($row['requestType'] ?? '') . ' ' . ($row['otherSpecify'] ?? '') . ' ' . ($row['description'] ?? '') . ' ' . ($row['remarks'] ?? ''));

    if (preg_match('/\b(otos|email|e-mail|zoom|google meet|meet|account|password|link)\b/', $text)) {
        return ['label' => 'inhouse', 'span_minutes' => 90, 'feedback_gap' => 30];
    }

    if (preg_match('/\b(repair|printer|cpu|desktop|laptop|computer|hardware|ink|reset|install|installed|reformat|replace|troubleshoot)\b/', $text)) {
        return ['label' => 'repair', 'span_minutes' => 300, 'feedback_gap' => 45];
    }

    return ['label' => 'technical assistance', 'span_minutes' => 180, 'feedback_gap' => 30];
}

function interpolate_slot(DateTime $start, DateTime $end, $index, $total) {
    $slot = clone $start;
    if ($total <= 1) {
        return $slot;
    }

    $range = $end->getTimestamp() - $start->getTimestamp();
    $offset = (int) round(($range * $index) / ($total - 1));
    $slot->setTimestamp($start->getTimestamp() + $offset);
    return $slot;
}

function varied_gap_minutes($seed, $index, $previousGap = null) {
    $gaps = [21, 27, 23, 30, 24, 29, 22, 26, 20, 28, 25];
    $slot = abs(crc32((string)$seed . ':' . (string)$index)) % count($gaps);
    $gap = $gaps[$slot];

    if ($previousGap !== null && $gap === $previousGap) {
        $gap = $gaps[($slot + 3) % count($gaps)];
    }

    return $gap;
}

function create_fallback_schedule($historyRows, $actionRows, $feedbackRows, DateTime $anchor, DateTime $timelineEnd, DateTime $feedbackStart, $receivedByRowId) {
    $schedule = [];
    $previousHistoryTs = null;
    $previousHistoryGap = null;
    $lastHistorySlot = clone $timelineEnd;
    $feedbackGapSeconds = max(0, $feedbackStart->getTimestamp() - $timelineEnd->getTimestamp());

    $historyCount = count($historyRows);
    foreach ($historyRows as $index => $row) {
        if ($index === 0 || (!empty($receivedByRowId) && (int)$row['id'] === (int)$receivedByRowId)) {
            $slot = clone $anchor;
        } else {
            $gap = varied_gap_minutes($row['id'], $index, $previousHistoryGap);
            $slot = new DateTime('@' . ($previousHistoryTs + ($gap * 60)));
            $slot->setTimezone(new DateTimeZone(date_default_timezone_get()));
            $previousHistoryGap = $gap;
        }

        if ($previousHistoryTs !== null && $slot->getTimestamp() <= $previousHistoryTs) {
            $slot->setTimestamp($previousHistoryTs + (varied_gap_minutes($row['id'], $index, $previousHistoryGap) * 60));
        }

        if ($slot->format('Y-m-d') !== $anchor->format('Y-m-d')) {
            $slot = clone $anchor;
            $slot->setTime(23, 55, 0);
        }

        $lastHistorySlot = clone $slot;

        $schedule[] = [
            'table' => 'srfhistory',
            'id' => (int)$row['id'],
            'date' => $slot->format('Y-m-d'),
            'time' => $slot->format('h:i:s A'),
        ];

        $previousHistoryTs = $slot->getTimestamp();
    }

    if ($historyCount > 0) {
        $timelineEnd = clone $lastHistorySlot;
        $feedbackStart = clone $lastHistorySlot;
        $feedbackStart->setTimestamp($lastHistorySlot->getTimestamp() + $feedbackGapSeconds);
        if ($feedbackStart->format('Y-m-d') !== $anchor->format('Y-m-d')) {
            $feedbackStart = clone $lastHistorySlot;
            $feedbackStart->setTime(23, 55, 0);
        }
    }

    $actionStart = clone $anchor;
    if ($historyCount >= 2 && isset($schedule[1])) {
        $dt = fetch_row_datetime($schedule[1]['date'], $schedule[1]['time']);
        if ($dt) {
            $actionStart = $dt;
        }
    }

    $actionCount = count($actionRows);
    foreach ($actionRows as $index => $row) {
        $slot = interpolate_slot($actionStart, $timelineEnd, $index, $actionCount);
        $schedule[] = [
            'table' => 'srf_actiontaken',
            'id' => (int)$row['id'],
            'date' => $slot->format('Y-m-d'),
            'time' => $slot->format('h:i:s A'),
        ];
    }

    $feedbackCount = count($feedbackRows);
    $feedbackEnd = clone $feedbackStart;
    if ($feedbackCount > 1) {
        $feedbackEnd->modify('+' . (($feedbackCount - 1) * 5) . ' minutes');
    }

    foreach ($feedbackRows as $index => $row) {
        $slot = interpolate_slot($feedbackStart, $feedbackEnd, $index, $feedbackCount);
        $stamp = $slot->format('Y-m-d H:i:s');
        $schedule[] = [
            'table' => 'srffeedback',
            'id' => (int)$row['id'],
            'feedback' => 'Excellent',
            'created_at' => $stamp,
            'date_rated' => $stamp,
        ];
    }

    return $schedule;
}

function call_ollama_schedule($model, $srf, $historyRows, $actionRows, $feedbackRows, DateTime $anchor, DateTime $timelineEnd, DateTime $feedbackStart, $receivedByRowId) {
    $payload = [
        'model' => $model,
        'stream' => false,
        'format' => 'json',
        'options' => [
            'temperature' => 0.1,
            'num_predict' => 1200,
        ],
        'prompt' => "You are a strict timeline adjustment engine for an SRF workflow.

Return JSON only, no markdown, no commentary.

Rules:
1. All rows must stay on the same date.
1a. The canonical date is srf.date from the SRF table; every adjusted history/action/feedback date must follow this date.
2. The second Receive History row must match the first RICTU Staff Actions timestamp.
3. The last Receive History row must match the last RICTU Staff Actions row timestamp.
4. All timestamps must be in chronological order.
5. If any existing date/time is blank, fill it.
6. Feedback rows must be Excellent and must be later than the last action row.
7. Keep the full timeline within one day.
8. For feedback rows, infer the correct AM/PM from the surrounding timeline and output created_at/date_rated in 24-hour format.
9. Receive History timestamps must be strictly increasing, and the first and second Receive History rows must never share the same timestamp.
10. The gap between the first and second Receive History rows must be 20 to 30 minutes.
11. Use varied gaps between Receive History rows; do not repeat the same interval pattern because it looks artificial.

SRF context:
" . json_encode([
            'srf' => $srf,
            'anchor' => $anchor->format('Y-m-d h:i:s A'),
            'timeline_end' => $timelineEnd->format('Y-m-d h:i:s A'),
            'feedback_start' => $feedbackStart->format('Y-m-d H:i:s'),
            'receivedByRowId' => $receivedByRowId,
            'historyRows' => $historyRows,
            'actionRows' => $actionRows,
            'feedbackRows' => $feedbackRows,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "

Output schema:
{
  \"timeline\": [
    {\"table\":\"srfhistory\",\"id\":1,\"date\":\"YYYY-MM-DD\",\"time\":\"hh:mm:ss AM\"},
    {\"table\":\"srf_actiontaken\",\"id\":1,\"date\":\"YYYY-MM-DD\",\"time\":\"hh:mm:ss AM\"},
    {\"table\":\"srffeedback\",\"id\":1,\"feedback\":\"Excellent\",\"created_at\":\"YYYY-MM-DD HH:MM:SS\",\"date_rated\":\"YYYY-MM-DD HH:MM:SS\"}
  ]
}

Important:
- Preserve the input row order inside each table.
- The second receive row must equal the first action row timestamp.
- The last receive row must equal the last action row timestamp.
- Use the provided anchor and timeline_end as guides.
"
    ];

    $ch = curl_init('http://localhost:11434/api/generate');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return [null, 'cURL error: ' . $error];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return [null, 'Ollama HTTP ' . $httpCode];
    }

    $result = json_decode($response, true);
    if (!is_array($result) || !isset($result['response'])) {
        return [null, 'Invalid Ollama response'];
    }

    $raw = trim((string)$result['response']);
    $raw = preg_replace('/<think>.*?<\/think>/s', '', $raw);
    $raw = trim($raw);

    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || !isset($decoded['timeline']) || !is_array($decoded['timeline'])) {
        return [null, 'Ollama did not return valid JSON timeline'];
    }

    return [$decoded['timeline'], null];
}

function validate_timeline($timeline, $historyRows, $actionRows, $feedbackRows, $anchorDate, $receivedByRowId) {
    $byTable = [
        'srfhistory' => [],
        'srf_actiontaken' => [],
        'srffeedback' => [],
    ];

    foreach ($timeline as $item) {
        if (!isset($item['table'], $item['id'])) {
            return [false, 'Missing timeline item fields'];
        }
        $byTable[$item['table']][] = $item;
    }

    if (count($byTable['srfhistory']) !== count($historyRows) || count($byTable['srf_actiontaken']) !== count($actionRows) || count($byTable['srffeedback']) !== count($feedbackRows)) {
        return [false, 'Timeline row count mismatch'];
    }

    $historyMap = [];
    foreach ($byTable['srfhistory'] as $item) {
        $historyMap[(int)$item['id']] = $item;
    }

    $actionMap = [];
    foreach ($byTable['srf_actiontaken'] as $item) {
        $actionMap[(int)$item['id']] = $item;
    }

    $feedbackMap = [];
    foreach ($byTable['srffeedback'] as $item) {
        $feedbackMap[(int)$item['id']] = $item;
    }

    $previousTimestamp = null;
    $firstHistoryTimestamp = null;
    foreach ($historyRows as $row) {
        $item = $historyMap[(int)$row['id']] ?? null;
        if (!$item) {
            return [false, 'Missing mapped history row'];
        }

        $dt = fetch_row_datetime($item['date'] ?? '', $item['time'] ?? '');
        if (!$dt) {
            return [false, 'Invalid history timestamp'];
        }
        if ($dt->format('Y-m-d') !== $anchorDate) {
            return [false, 'History row not on anchor day'];
        }
        if ($previousTimestamp !== null && $dt->getTimestamp() <= $previousTimestamp) {
            return [false, 'History rows are not strictly increasing'];
        }
        if ($firstHistoryTimestamp === null) {
            $firstHistoryTimestamp = $dt->getTimestamp();
        } elseif ((int)$row['id'] === (int)$historyRows[1]['id']) {
            $gapMinutes = (int)(($dt->getTimestamp() - $firstHistoryTimestamp) / 60);
            if ($gapMinutes < 20 || $gapMinutes > 30) {
                return [false, 'First and second receive rows must be 20 to 30 minutes apart'];
            }
        }
        $previousTimestamp = $dt->getTimestamp();
    }

    $previousTimestamp = null;
    foreach ($actionRows as $row) {
        $item = $actionMap[(int)$row['id']] ?? null;
        if (!$item) {
            return [false, 'Missing mapped action row'];
        }

        $dt = fetch_row_datetime($item['date'] ?? '', $item['time'] ?? '');
        if (!$dt) {
            return [false, 'Invalid action timestamp'];
        }
        if ($dt->format('Y-m-d') !== $anchorDate) {
            return [false, 'Action row not on anchor day'];
        }
        if ($previousTimestamp !== null && $dt->getTimestamp() <= $previousTimestamp) {
            return [false, 'Action rows are not strictly increasing'];
        }
        $previousTimestamp = $dt->getTimestamp();
    }

    $previousTimestamp = null;
    foreach ($feedbackRows as $row) {
        $item = $feedbackMap[(int)$row['id']] ?? null;
        if (!$item) {
            return [false, 'Missing mapped feedback row'];
        }

        $dt = fetch_row_datetime(substr((string)($item['created_at'] ?? ''), 0, 10), substr((string)($item['created_at'] ?? ''), 11, 8));
        if (!$dt) {
            return [false, 'Invalid feedback timestamp'];
        }
        if ($dt->format('Y-m-d') !== $anchorDate) {
            return [false, 'Feedback date mismatch'];
        }
        if ($previousTimestamp !== null && $dt->getTimestamp() <= $previousTimestamp) {
            return [false, 'Feedback rows are not strictly increasing'];
        }
        $previousTimestamp = $dt->getTimestamp();
    }

    if (!empty($historyRows) && count($historyRows) >= 2 && !empty($actionRows)) {
        $secondHistory = $historyMap[(int)$historyRows[1]['id']] ?? null;
        $firstAction = $byTable['srf_actiontaken'][0];
        if ($secondHistory && (($secondHistory['date'] ?? '') !== ($firstAction['date'] ?? '') || ($secondHistory['time'] ?? '') !== ($firstAction['time'] ?? ''))) {
            return [false, 'Second receive row does not match first action row'];
        }
    }

    if ($receivedByRowId !== null && isset($historyMap[$receivedByRowId]) && !empty($byTable['srf_actiontaken'][0])) {
        $receive = $historyMap[$receivedByRowId];
        $firstAction = $byTable['srf_actiontaken'][0];
        $receiveIsSecond = $receivedByRowId === (int)($historyRows[1]['id'] ?? null);
        if ($receiveIsSecond && (($receive['date'] ?? '') !== ($firstAction['date'] ?? '') || ($receive['time'] ?? '') !== ($firstAction['time'] ?? ''))) {
            return [false, 'Received By row does not match first action row'];
        }
    }

    if (!empty($historyRows) && !empty($actionRows)) {
        $lastHistory = $historyMap[(int)$historyRows[count($historyRows) - 1]['id']] ?? null;
        $lastAction = $byTable['srf_actiontaken'][count($byTable['srf_actiontaken']) - 1] ?? null;
        if ($lastHistory && $lastAction && (($lastHistory['date'] ?? '') !== ($lastAction['date'] ?? '') || ($lastHistory['time'] ?? '') !== ($lastAction['time'] ?? ''))) {
            return [false, 'Last receive row does not match last action row'];
        }
    }

    return [true, null];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, ['error' => 'POST request required.']);
}

$input = json_decode(file_get_contents('php://input'), true);
$trackid = isset($input['trackid']) ? (int)$input['trackid'] : 0;
$selectedModel = trim((string)($input['model'] ?? 'tinyllama:latest'));

if ($trackid <= 0) {
    respond(false, ['error' => 'Invalid SRF ID.']);
}

$stmt = $conn->prepare('SELECT id, ticketNumber, date, name, requestType, otherSpecify, description, remarks, created_at FROM srf WHERE id = ?');
if (!$stmt) {
    respond(false, ['error' => 'Prepare failed: ' . $conn->error]);
}
$stmt->bind_param('i', $trackid);
$stmt->execute();
$srf = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$srf) {
    respond(false, ['error' => 'SRF record not found.']);
}

$stmt = $conn->prepare('SELECT id, name, details, date, time, status FROM srfhistory WHERE trackid = ? ORDER BY id ASC');
$stmt->bind_param('i', $trackid);
$stmt->execute();
$historyRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare('SELECT id, date, time FROM srf_actiontaken WHERE trackid = ? ORDER BY id ASC');
$stmt->bind_param('i', $trackid);
$stmt->execute();
$actionRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare('SELECT id, feedback, acknowledged_by, created_at, date_rated FROM srffeedback WHERE srf_id = ? ORDER BY id ASC');
$srfIdText = (string)$trackid;
$stmt->bind_param('s', $srfIdText);
$stmt->execute();
$feedbackRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$profile = request_profile($srf);

$receivedByRowId = null;
foreach ($historyRows as $row) {
    $historyName = trim((string)($row['name'] ?? ''));
    $historyText = strtolower(trim(($row['details'] ?? '') . ' ' . ($row['status'] ?? '')));
    if (strcasecmp($historyName, 'MARIETTA L. CHUA') === 0 && preg_match('/received by/i', $historyText)) {
        $receivedByRowId = (int)$row['id'];
        break;
    }
}

$baseDate = trim((string)($srf['date'] ?? ''));
if ($baseDate === '' || strtotime($baseDate) === false) {
    $baseDate = !empty($srf['created_at']) && strtotime($srf['created_at']) !== false ? date('Y-m-d', strtotime($srf['created_at'])) : date('Y-m-d');
} else {
    $baseDate = date('Y-m-d', strtotime($baseDate));
}

$anchorTime = '08:00 AM';
if (!empty($actionRows[0]['time'])) {
    $anchorTime = $actionRows[0]['time'];
} elseif (!empty($historyRows[0]['time'])) {
    $anchorTime = $historyRows[0]['time'];
}

$anchor = fetch_row_datetime($baseDate, $anchorTime);
if (!$anchor) {
    $anchor = new DateTime($baseDate . ' 08:00:00');
}

$timelineEnd = clone $anchor;
$timelineEnd->modify('+' . (int)$profile['span_minutes'] . ' minutes');
if ($timelineEnd->format('Y-m-d') !== $anchor->format('Y-m-d')) {
    $timelineEnd = clone $anchor;
    $timelineEnd->setTime(23, 55, 0);
}

$feedbackStart = clone $timelineEnd;
$feedbackStart->modify('+' . (int)$profile['feedback_gap'] . ' minutes');
if ($feedbackStart->format('Y-m-d') !== $anchor->format('Y-m-d')) {
    $feedbackStart = clone $timelineEnd;
    $feedbackStart->setTime(23, 55, 0);
}

$timeline = null;
$aiError = null;
if (function_exists('curl_init')) {
    [$timeline, $aiError] = call_ollama_schedule($selectedModel, $srf, $historyRows, $actionRows, $feedbackRows, $anchor, $timelineEnd, $feedbackStart, $receivedByRowId);
}

$usedFallback = false;
if (!$timeline) {
    $usedFallback = true;
    $timeline = create_fallback_schedule($historyRows, $actionRows, $feedbackRows, $anchor, $timelineEnd, $feedbackStart, $receivedByRowId);
} else {
    [$valid, $reason] = validate_timeline($timeline, $historyRows, $actionRows, $feedbackRows, $anchor->format('Y-m-d'), $receivedByRowId);
    if (!$valid) {
        $usedFallback = true;
        $aiError = $reason;
        $timeline = create_fallback_schedule($historyRows, $actionRows, $feedbackRows, $anchor, $timelineEnd, $feedbackStart, $receivedByRowId);
    }
}

$conn->begin_transaction();

try {
    $updateHistory = $conn->prepare('UPDATE srfhistory SET date = ?, time = ? WHERE id = ?');
    $updateAction = $conn->prepare('UPDATE srf_actiontaken SET date = ?, time = ? WHERE id = ?');
    $updateFeedback = $conn->prepare('UPDATE srffeedback SET feedback = ?, created_at = ?, date_rated = ? WHERE id = ?');

    foreach ($timeline as $item) {
        if ($item['table'] === 'srfhistory') {
            $date = $item['date'];
            $time = $item['time'];
            $id = (int)$item['id'];
            $updateHistory->bind_param('ssi', $date, $time, $id);
            $updateHistory->execute();
            continue;
        }

        if ($item['table'] === 'srf_actiontaken') {
            $date = $item['date'];
            $time = $item['time'];
            $id = (int)$item['id'];
            $updateAction->bind_param('ssi', $date, $time, $id);
            $updateAction->execute();
            continue;
        }

        if ($item['table'] === 'srffeedback') {
            $feedback = 'Excellent';
            $createdAt = $item['created_at'];
            $dateRated = $item['date_rated'];
            $id = (int)$item['id'];
            $updateFeedback->bind_param('sssi', $feedback, $createdAt, $dateRated, $id);
            $updateFeedback->execute();
        }
    }

    $updateHistory->close();
    $updateAction->close();
    $updateFeedback->close();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    respond(false, ['error' => $e->getMessage()]);
}

respond(true, [
    'mode' => $usedFallback ? 'fallback' : 'ollama',
    'model' => $selectedModel,
    'profile' => $profile['label'],
    'anchor' => $anchor->format('Y-m-d h:i:s A'),
    'timelineEnd' => $timelineEnd->format('Y-m-d h:i:s A'),
    'feedbackStart' => $feedbackStart->format('Y-m-d h:i:s A'),
    'receivedByRowId' => $receivedByRowId,
    'aiError' => $aiError,
    'timeline' => $timeline,
    'oldValues' => [
        'srfhistory' => $historyRows,
        'srf_actiontaken' => $actionRows,
        'srffeedback' => $feedbackRows,
    ],
]);
