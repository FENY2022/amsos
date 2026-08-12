<?php
session_start();
require_once 'connect.php';

function tableExists($conn, $table) {
    static $cache = [];

    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return false;
    }

    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->bind_param("s", $table);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $cache[$table] = (int)$count > 0;
        $stmt->close();
    } catch (Throwable $e) {
        error_log('tableExists failed for ' . $table . ': ' . $e->getMessage());
        $cache[$table] = false;
    }

    return $cache[$table];
}

function columnExists($conn, $table, $column) {
    static $cache = [];
    $key = $table . '.' . $column;

    if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
        return false;
    }

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->bind_param("ss", $table, $column);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $cache[$key] = (int)$count > 0;
        $stmt->close();
    } catch (Throwable $e) {
        error_log('columnExists failed for ' . $key . ': ' . $e->getMessage());
        $cache[$key] = false;
    }

    return $cache[$key];
}

function postValue($key, $default = '') {
    return $_POST[$key] ?? $default;
}

function redirectBack() {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'entrydata.php'));
    exit();
}

function isValidInventoryPersonName($name) {
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

function getOrCreateOfficeDivisionId($conn, $office, $officeDivision) {
    $office = trim((string)$office);
    $officeDivision = trim((string)$officeDivision);

    if ($office === '' || $officeDivision === '' || !tableExists($conn, 'office_divisions')) {
        return null;
    }

    $stmt = $conn->prepare("SELECT id FROM office_divisions WHERE UPPER(office) = UPPER(?) AND UPPER(officeDivision) = UPPER(?) LIMIT 1");
    $stmt->bind_param("ss", $office, $officeDivision);
    $stmt->execute();
    $stmt->bind_result($officeId);
    if ($stmt->fetch()) {
        $stmt->close();
        return (int)$officeId;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO office_divisions (office, officeDivision) VALUES (?, ?)");
    $stmt->bind_param("ss", $office, $officeDivision);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    return (int)$newId;
}

function getOrCreateInventoryPersonId($conn, $name, $officeId, $office, $officeDivision, $employmentStatus, $source) {
    $name = trim((string)$name);

    if (!isValidInventoryPersonName($name) || !tableExists($conn, 'inventory_people') || !columnExists($conn, 'inventory_people', 'full_name')) {
        return null;
    }

    $normalizedName = strtoupper($name);
    $office = trim((string)$office);
    $officeDivision = trim((string)$officeDivision);
    $employmentStatus = trim((string)$employmentStatus);

    $lookupColumn = columnExists($conn, 'inventory_people', 'normalized_name') ? 'normalized_name' : 'full_name';
    $lookupValue = $lookupColumn === 'normalized_name' ? $normalizedName : $name;

    $stmt = $conn->prepare("SELECT id FROM inventory_people WHERE $lookupColumn = ? LIMIT 1");
    $stmt->bind_param("s", $lookupValue);
    $stmt->execute();
    $stmt->bind_result($personId);
    if ($stmt->fetch()) {
        $stmt->close();
        $updates = [];
        $types = '';
        $params = [];

        if (columnExists($conn, 'inventory_people', 'office_id')) {
            $updates[] = 'office_id = ?';
            $types .= 'i';
            $params[] = $officeId;
        }
        if (columnExists($conn, 'inventory_people', 'office')) {
            $updates[] = 'office = ?';
            $types .= 's';
            $params[] = $office;
        }
        if (columnExists($conn, 'inventory_people', 'officeDivision')) {
            $updates[] = 'officeDivision = ?';
            $types .= 's';
            $params[] = $officeDivision;
        }
        if ($employmentStatus !== '' && columnExists($conn, 'inventory_people', 'employment_status')) {
            $updates[] = 'employment_status = ?';
            $types .= 's';
            $params[] = $employmentStatus;
        }

        if (!empty($updates)) {
            $types .= 'i';
            $params[] = $personId;
            $stmt = $conn->prepare('UPDATE inventory_people SET ' . implode(', ', $updates) . ' WHERE id = ?');
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
        }

        return (int)$personId;
    }
    $stmt->close();

    $columns = ['full_name'];
    $placeholders = ['?'];
    $types = 's';
    $params = [$name];

    $optionalColumns = [
        'normalized_name' => ['s', $normalizedName],
        'office_id' => ['i', $officeId],
        'office' => ['s', $office],
        'officeDivision' => ['s', $officeDivision],
        'employment_status' => ['s', $employmentStatus],
        'source' => ['s', $source],
    ];

    foreach ($optionalColumns as $column => $definition) {
        if (columnExists($conn, 'inventory_people', $column)) {
            $columns[] = $column;
            $placeholders[] = '?';
            $types .= $definition[0];
            $params[] = $definition[1];
        }
    }

    $stmt = $conn->prepare('INSERT INTO inventory_people (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')');
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    return (int)$newId;
}

function normalizeIctScanValue($value) {
    $value = strtolower(trim((string)$value));
    $value = preg_replace('/[^a-z0-9\s-]/', ' ', $value);
    $value = preg_replace('/\s+/', ' ', $value);
    return trim($value);
}

function containsIctKeyword($text, $keyword) {
    $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
    return (bool)preg_match($pattern, $text);
}

function evaluateIctInventoryEntry(array $data) {
    $positiveStrong = ['laptop', 'desktop', 'computer', 'monitor', 'printer', 'scanner', 'router', 'switch', 'modem', 'ups'];
    $positive = ['keyboard', 'mouse', 'ssd', 'ram', 'processor', 'motherboard', 'network', 'hard drive', 'hdd', 'server', 'access point', 'accesspoint'];
    $negative = ['chair', 'table', 'cabinet', 'paper', 'folder', 'book', 'furniture', 'sofa', 'desk', 'notebook'];

    $equipmentType = normalizeIctScanValue($data['equipmentType'] ?? '');
    $scannedFields = normalizeIctScanValue(implode(' ', [
        $data['computer_specs'] ?? '',
        $data['specifications'] ?? '',
        $data['softwareInstalled'] ?? '',
        $data['remarks'] ?? '',
        $data['rangeCategory'] ?? ''
    ]));

    $score = 0;
    $positiveMatches = [];
    $negativeMatches = [];

    foreach ($positiveStrong as $keyword) {
        if (containsIctKeyword($equipmentType, $keyword)) {
            $score += 3;
            $positiveMatches[] = $keyword;
        }
    }

    foreach ($positive as $keyword) {
        if (containsIctKeyword($equipmentType, $keyword)) {
            $score += 2;
            $positiveMatches[] = $keyword;
        } elseif (containsIctKeyword($scannedFields, $keyword)) {
            $score += 1;
            $positiveMatches[] = $keyword;
        }
    }

    foreach ($negative as $keyword) {
        if (containsIctKeyword($equipmentType, $keyword) || containsIctKeyword($scannedFields, $keyword)) {
            $score -= 2;
            $negativeMatches[] = $keyword;
        }
    }

    $positiveMatches = array_values(array_unique($positiveMatches));
    $negativeMatches = array_values(array_unique($negativeMatches));

    if ($score >= 6) {
        $label = 'ICT Verified';
    } elseif ($score >= 2) {
        $label = 'Needs Review';
    } else {
        $label = 'Possible Non-ICT';
    }

    return [
        'score' => $score,
        'label' => $label,
        'positiveMatches' => $positiveMatches,
        'negativeMatches' => $negativeMatches,
    ];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
    // --- Collect and sanitize form data ---
    $amount = str_replace(',', '', postValue('amount', '0'));
    $depreciation_value = postValue('depreciation_value', '0');
    $employeeName = postValue('employeeName');
    $equipmentType = postValue('equipmentType');
    $yearAcquired = postValue('yearAcquired');
    $shelfLife = postValue('shelfLife');
    $brand = postValue('brand');
    $specifications = postValue('specifications');
    $rangeCategory = postValue('rangeCategory');
    $softwareInstalled = postValue('softwareInstalled');
    $licensingModel = postValue('licensingModel');
    
    // --- Robust N/A and Whitespace Handling for Serial Number ---
    $serialNumberInput = isset($_POST['serialNumber']) ? trim($_POST['serialNumber']) : '';
    if (strtoupper($serialNumberInput) === 'N/A' || $serialNumberInput === '') {
        $serialNumber = 'N/A';
    } else {
        $serialNumber = $serialNumberInput;
    }

    // --- Robust N/A and Whitespace Handling for Property Number ---
    $propertyNumberInput = isset($_POST['propertyNumber']) ? trim($_POST['propertyNumber']) : '';
    if (strtoupper($propertyNumberInput) === 'N/A' || $propertyNumberInput === '') {
        $propertyNumber = 'N/A';
    } else {
        $propertyNumber = $propertyNumberInput;
    }

    $accountablePerson = postValue('accountablePerson');
    $sex = postValue('sex');
    $officeDivision = postValue('officeDivision');
    $statusOfEmployment = postValue('statusOfEmployment');
    $actualUser = postValue('actualUser');
    $actualUserSex = postValue('actualUserSex');
    $actualUserStatusOfEmployment = postValue('actualUserStatusOfEmployment');
    $natureOfWork = postValue('natureOfWork');
    $remarks = postValue('remarks');
    
    // ---------------------------------------------------------
    // 1. CAPTURE THE NEW VARIABLE
    // ---------------------------------------------------------
    $computer_specs = postValue('computer_specs'); 
    
    $office = $_SESSION['OfficeSRF'] ?? '';
    $ictValidation = evaluateIctInventoryEntry([
        'equipmentType' => $equipmentType,
        'computer_specs' => $computer_specs,
        'specifications' => $specifications,
        'softwareInstalled' => $softwareInstalled,
        'remarks' => $remarks,
        'rangeCategory' => $rangeCategory,
    ]);

    if ($ictValidation['score'] < 2) {
        $_SESSION['warning'] = 'System detected this entry may not be ICT equipment. Please review.';
    } elseif ($ictValidation['score'] < 6) {
        $_SESSION['warning'] = 'System detected this entry needs review to confirm ICT classification.';
    }

    $officeId = getOrCreateOfficeDivisionId($conn, $office, $officeDivision);
    $employeePersonId = getOrCreateInventoryPersonId($conn, $employeeName, $officeId, $office, $officeDivision, $statusOfEmployment, 'employeeName');
    $accountablePersonId = getOrCreateInventoryPersonId($conn, $accountablePerson, $officeId, $office, $officeDivision, $statusOfEmployment, 'accountablePerson');
    $actualUserId = getOrCreateInventoryPersonId($conn, $actualUser, $officeId, $office, $officeDivision, $actualUserStatusOfEmployment, 'actualUser');

    // --- Smarter Uniqueness Check ---
    $check_clauses = [];
    $params = [];
    $types = "";

    if (strtoupper($propertyNumber) !== 'N/A') {
        $check_clauses[] = "propertyNumber = ?";
        $params[] = $propertyNumber;
        $types .= "s";
    }

    if (strtoupper($serialNumber) !== 'N/A') {
        $check_clauses[] = "serialNumber = ?";
        $params[] = $serialNumber;
        $types .= "s";
    }

    if (!empty($check_clauses)) {
        $check_sql = "SELECT COUNT(*) FROM inv_inventory WHERE " . implode(" OR ", $check_clauses);

        if ($check_stmt = $conn->prepare($check_sql)) {
            $check_stmt->bind_param($types, ...$params);
            $check_stmt->execute();
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($count > 0) {
                $_SESSION['error'] = "A record with this Property Number or Serial Number already exists!";
                $_SESSION['focus_step'] = 2;
                $_SESSION['form_data'] = $_POST;
                redirectBack();
            }
        } else {
            $_SESSION['error'] = "Error preparing the uniqueness check query.";
            $_SESSION['form_data'] = $_POST;
            redirectBack();
        }
    }

    $fields = [
        'employeeName' => ['s', $employeeName],
        'employee_person_id' => ['i', $employeePersonId],
        'equipmentType' => ['s', $equipmentType],
        'yearAcquired' => ['s', $yearAcquired],
        'shelfLife' => ['s', $shelfLife],
        'brand' => ['s', $brand],
        'specifications' => ['s', $specifications],
        'rangeCategory' => ['s', $rangeCategory],
        'softwareInstalled' => ['s', $softwareInstalled],
        'licensingModel' => ['s', $licensingModel],
        'serialNumber' => ['s', $serialNumber],
        'propertyNumber' => ['s', $propertyNumber],
        'accountablePerson' => ['s', $accountablePerson],
        'accountable_person_id' => ['i', $accountablePersonId],
        'sex' => ['s', $sex],
        'officeDivision' => ['s', $officeDivision],
        'statusOfEmployment' => ['s', $statusOfEmployment],
        'actualUser' => ['s', $actualUser],
        'actual_user_id' => ['i', $actualUserId],
        'actualUserSex' => ['s', $actualUserSex],
        'actualUserStatusOfEmployment' => ['s', $actualUserStatusOfEmployment],
        'natureOfWork' => ['s', $natureOfWork],
        'remarks' => ['s', $remarks],
        'amount' => ['d', (float)$amount],
        'depreciation_value' => ['s', $depreciation_value],
        'office' => ['s', $office],
        'office_id' => ['i', $officeId],
        'computer_specs' => ['s', $computer_specs],
    ];

    $columns = [];
    $placeholders = [];
    $types = '';
    $params = [];

    foreach ($fields as $column => $definition) {
        if (columnExists($conn, 'inv_inventory', $column)) {
            $columns[] = $column;
            $placeholders[] = '?';
            $types .= $definition[0];
            $params[] = $definition[1];
        }
    }

    if (empty($columns)) {
        throw new RuntimeException('No matching inv_inventory columns found for insert.');
    }

    $sql = 'INSERT INTO inv_inventory (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Inventory record saved successfully!";
            unset($_SESSION['form_data']);
        } else {
            $_SESSION['error'] = "Error: Could not execute the insertion query. " . $stmt->error;
            error_log('entrydatahandler.php insert execute failed: ' . $stmt->error);
            $_SESSION['form_data'] = $_POST;
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Error: Could not prepare the insertion query. " . $conn->error;
        error_log('entrydatahandler.php insert prepare failed: ' . $conn->error);
        $_SESSION['form_data'] = $_POST;
    }

    $conn->close();
    redirectBack();
    } catch (Throwable $e) {
        error_log('entrydatahandler.php failed: ' . $e->getMessage());
        $_SESSION['error'] = 'Error saving inventory record. Please check the server error log.';
        $_SESSION['form_data'] = $_POST;
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->close();
        }
        redirectBack();
    }
}
?>
