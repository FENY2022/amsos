<?php
ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';
require_once 'connect_otos.php';

header('Content-Type: application/json');

function sendOtosJson($payload) {
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode($payload);
    exit;
}

function normalizeOtosInventoryName($name) {
    return strtoupper(trim((string)$name));
}

function isValidOtosInventoryName($name) {
    $name = trim((string)$name);
    if ($name === '') {
        return false;
    }

    $upperName = strtoupper($name);
    if (in_array($upperName, ['N/A', 'NA', '0', 'NOT FOUND'], true)) {
        return false;
    }

    return !preg_match('/^[0-9]/', $name);
}

function getOrCreateOtosOfficeDivisionId($conn, $office, $officeDivision) {
    $office = trim((string)$office);
    $officeDivision = trim((string)$officeDivision);

    if ($office === '' || $officeDivision === '') {
        return null;
    }

    $stmt = $conn->prepare("SELECT id FROM office_divisions WHERE UPPER(office) = UPPER(?) AND UPPER(officeDivision) = UPPER(?) LIMIT 1");
    $stmt->bind_param('ss', $office, $officeDivision);
    $stmt->execute();
    $stmt->bind_result($officeId);
    if ($stmt->fetch()) {
        $stmt->close();
        return (int)$officeId;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO office_divisions (office, officeDivision) VALUES (?, ?)");
    $stmt->bind_param('ss', $office, $officeDivision);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    return (int)$newId;
}

function sanitizeOtosIds($ids) {
    if (!is_array($ids)) {
        return [];
    }

    return array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
        return $id > 0;
    })));
}

function fetchActiveOtosEmployeesByIds($conn_otos, $office, $otosUserIds) {
    if (empty($otosUserIds)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($otosUserIds), '?'));
    $types = str_repeat('i', count($otosUserIds));
    $stmt = $conn_otos->prepare("SELECT id, Full_Name, Office, Station, Div_Sec_Unit, Employment_Status FROM useremployee WHERE Office = ? AND id IN ($placeholders) AND status_toggle = 'active' AND is_active = 1");
    $bindTypes = 's' . $types;
    $params = array_merge([$office], $otosUserIds);
    $stmt->bind_param($bindTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[(int)$row['id']] = $row;
    }

    $stmt->close();
    return $employees;
}

function findCloseInventoryNameMatches($conn, $office, $employees, $threshold) {
    if (empty($employees)) {
        return [];
    }

    $stmt = $conn->prepare("SELECT id, full_name, normalized_name, source FROM inventory_people WHERE office = ?");
    $stmt->bind_param('s', $office);
    $stmt->execute();
    $result = $stmt->get_result();
    $inventoryPeople = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $matches = [];
    foreach ($employees as $employee) {
        $otosId = (int)$employee['id'];
        $otosName = trim((string)$employee['Full_Name']);
        $normalizedName = normalizeOtosInventoryName($otosName);

        if ($normalizedName === '') {
            continue;
        }

        $exactStmt = $conn->prepare("SELECT id FROM inventory_people WHERE otos_user_id = ? OR normalized_name = ? LIMIT 1");
        $exactStmt->bind_param('is', $otosId, $normalizedName);
        $exactStmt->execute();
        $exactStmt->store_result();
        $exactExists = $exactStmt->num_rows > 0;
        $exactStmt->close();

        if ($exactExists) {
            continue;
        }

        foreach ($inventoryPeople as $person) {
            similar_text($normalizedName, $person['normalized_name'], $score);
            $score = round($score, 1);

            if ($score >= $threshold) {
                $matches[] = [
                    'otos_user_id' => $otosId,
                    'otos_name' => $otosName,
                    'inventory_person_id' => (int)$person['id'],
                    'inventory_name' => $person['full_name'],
                    'inventory_source' => $person['source'],
                    'similarity' => $score,
                ];

                break;
            }
        }
    }

    return $matches;
}

$office = trim((string)($_SESSION['OfficeSRF'] ?? ''));
$payload = json_decode(file_get_contents('php://input'), true);
$includeOtosUserIds = sanitizeOtosIds($payload['include_otos_user_ids'] ?? ($payload['otos_user_ids'] ?? []));
$removeOtosUserIds = sanitizeOtosIds($payload['remove_otos_user_ids'] ?? []);
$confirmDuplicates = !empty($payload['confirm_duplicates']);

if ($office === '') {
    sendOtosJson(['success' => false, 'message' => 'No office found in session.']);
}

if (empty($includeOtosUserIds) && empty($removeOtosUserIds)) {
    sendOtosJson(['success' => false, 'message' => 'No changes selected.']);
}

$employeesToInclude = fetchActiveOtosEmployeesByIds($conn_otos, $office, $includeOtosUserIds);

$closeMatches = findCloseInventoryNameMatches($conn, $office, $employeesToInclude, 80);
if (!empty($closeMatches) && !$confirmDuplicates) {
    sendOtosJson([
        'success' => false,
        'needs_confirmation' => true,
        'message' => 'Possible duplicate or close-name match found before saving.',
        'matches' => $closeMatches,
    ]);
}

$inserted = 0;
$linked = 0;
$skipped = 0;
$removed = 0;

$conn->begin_transaction();

foreach ($removeOtosUserIds as $otosIdToRemove) {
    $deleteStmt = $conn->prepare("DELETE FROM inventory_people WHERE otos_user_id = ? AND source = 'otos'");
    $deleteStmt->bind_param('i', $otosIdToRemove);
    $deleteStmt->execute();
    $removed += $deleteStmt->affected_rows;
    $deleteStmt->close();
}

foreach ($employeesToInclude as $employee) {
    $otosId = (int)$employee['id'];
    $fullName = trim((string)$employee['Full_Name']);
    $normalizedName = normalizeOtosInventoryName($fullName);
    $employeeOffice = trim((string)$employee['Office']);
    $officeDivision = trim((string)$employee['Div_Sec_Unit']);
    $employmentStatus = trim((string)$employee['Employment_Status']);

    if (!isValidOtosInventoryName($fullName)) {
        $skipped++;
        continue;
    }

    $localStmt = $conn->prepare("SELECT id, otos_user_id FROM inventory_people WHERE otos_user_id = ? OR normalized_name = ? LIMIT 1");
    $localStmt->bind_param('is', $otosId, $normalizedName);
    $localStmt->execute();
    $localStmt->bind_result($personId, $existingOtosId);
    if ($localStmt->fetch()) {
        $localStmt->close();

        if (empty($existingOtosId)) {
            $updateStmt = $conn->prepare("UPDATE inventory_people SET otos_user_id = ? WHERE id = ? AND otos_user_id IS NULL");
            $updateStmt->bind_param('ii', $otosId, $personId);
            $updateStmt->execute();
            $updateStmt->close();
            $linked++;
        } else {
            $skipped++;
        }
        continue;
    }
    $localStmt->close();

    $officeId = getOrCreateOtosOfficeDivisionId($conn, $employeeOffice, $officeDivision);
    $source = 'otos';
    $insertStmt = $conn->prepare("INSERT INTO inventory_people (otos_user_id, full_name, normalized_name, office_id, office, officeDivision, employment_status, source) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param('ississss', $otosId, $fullName, $normalizedName, $officeId, $employeeOffice, $officeDivision, $employmentStatus, $source);
    $insertStmt->execute();
    $insertStmt->close();
    $inserted++;
}

$conn->commit();
$conn->close();
$conn_otos->close();

sendOtosJson([
    'success' => true,
    'message' => "Saved. Inserted: $inserted, linked existing: $linked, removed: $removed, skipped: $skipped."
]);
?>
