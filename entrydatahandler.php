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

function ensureInventorySpecificationsTable($conn) {
    if (tableExists($conn, 'inventory_specifications')) {
        return true;
    }

    $sql = "CREATE TABLE IF NOT EXISTS inventory_specifications (
        id INT NOT NULL AUTO_INCREMENT,
        inventory_id INT NOT NULL,
        hdd_capacity VARCHAR(100) NULL,
        ssd_capacity VARCHAR(100) NULL,
        ram_capacity VARCHAR(100) NULL,
        memory_capacity VARCHAR(100) NULL,
        processor_type VARCHAR(255) NULL,
        display_size VARCHAR(100) NULL,
        display_resolution VARCHAR(100) NULL,
        battery_capacity VARCHAR(100) NULL,
        os_type VARCHAR(150) NULL,
        os_status VARCHAR(100) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_inventory_specifications_inventory_id (inventory_id),
        KEY idx_inventory_specifications_processor (processor_type),
        KEY idx_inventory_specifications_os (os_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!$conn->query($sql)) {
        throw new RuntimeException('Unable to initialize inventory_specifications: ' . $conn->error);
    }

    return true;
}

function buildCombinedSpecifications(array $specs, $fallback = '') {
    $labels = [
        'hdd_capacity' => 'HDD',
        'ssd_capacity' => 'SSD',
        'ram_capacity' => 'RAM',
        'memory_capacity' => 'Memory',
        'processor_type' => 'Processor',
        'display_size' => 'Display Size',
        'display_resolution' => 'Display Resolution',
        'battery_capacity' => 'Battery',
        'os_type' => 'OS',
        'os_status' => 'OS Status',
    ];

    $parts = [];

    foreach ($labels as $key => $label) {
        $value = trim((string)($specs[$key] ?? ''));
        if ($value !== '') {
            $parts[] = $label . ': ' . $value;
        }
    }

    if (!empty($parts)) {
        return implode('; ', $parts);
    }

    return trim((string)$fallback);
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
    $postedSpecifications = postValue('specifications');

    $structuredSpecifications = [
        'hdd_capacity' => trim((string)postValue('hdd-capacity')),
        'ssd_capacity' => trim((string)postValue('ssd-capacity')),
        'ram_capacity' => trim((string)postValue('ram-capacity')),
        'memory_capacity' => trim((string)postValue('memory-capacity')),
        'processor_type' => trim((string)postValue('processor-type')),
        'display_size' => trim((string)postValue('display-size')),
        'display_resolution' => trim((string)postValue('display-resolution')),
        'battery_capacity' => trim((string)postValue('battery-capacity')),
        'os_type' => trim((string)postValue('os-type')),
        'os_status' => trim((string)postValue('os-status')),
    ];

    // Server is authoritative: helper fields are also combined into the legacy
    // inv_inventory.specifications text so existing AMSOS reports keep working.
    $specifications = buildCombinedSpecifications($structuredSpecifications, $postedSpecifications);

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

    ensureInventorySpecificationsTable($conn);
    $conn->begin_transaction();

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
                $conn->rollback();
                $_SESSION['error'] = "A record with this Property Number or Serial Number already exists!";
                $_SESSION['focus_step'] = 2;
                $_SESSION['form_data'] = $_POST;
                redirectBack();
            }
        } else {
            $conn->rollback();
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

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Could not prepare inventory insert: ' . $conn->error);
    }

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Could not insert inventory record: ' . $error);
    }

    $inventoryId = (int)$stmt->insert_id;
    $stmt->close();

    if ($inventoryId <= 0) {
        throw new RuntimeException('Inventory record was inserted without a valid inventory ID.');
    }

    $specStmt = $conn->prepare(
        "INSERT INTO inventory_specifications (
            inventory_id,
            hdd_capacity,
            ssd_capacity,
            ram_capacity,
            memory_capacity,
            processor_type,
            display_size,
            display_resolution,
            battery_capacity,
            os_type,
            os_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            hdd_capacity = VALUES(hdd_capacity),
            ssd_capacity = VALUES(ssd_capacity),
            ram_capacity = VALUES(ram_capacity),
            memory_capacity = VALUES(memory_capacity),
            processor_type = VALUES(processor_type),
            display_size = VALUES(display_size),
            display_resolution = VALUES(display_resolution),
            battery_capacity = VALUES(battery_capacity),
            os_type = VALUES(os_type),
            os_status = VALUES(os_status),
            updated_at = CURRENT_TIMESTAMP"
    );

    if (!$specStmt) {
        throw new RuntimeException('Could not prepare structured specifications insert: ' . $conn->error);
    }

    $specStmt->bind_param(
        'issssssssss',
        $inventoryId,
        $structuredSpecifications['hdd_capacity'],
        $structuredSpecifications['ssd_capacity'],
        $structuredSpecifications['ram_capacity'],
        $structuredSpecifications['memory_capacity'],
        $structuredSpecifications['processor_type'],
        $structuredSpecifications['display_size'],
        $structuredSpecifications['display_resolution'],
        $structuredSpecifications['battery_capacity'],
        $structuredSpecifications['os_type'],
        $structuredSpecifications['os_status']
    );

    if (!$specStmt->execute()) {
        $error = $specStmt->error;
        $specStmt->close();
        throw new RuntimeException('Could not save structured specifications: ' . $error);
    }

    $specStmt->close();
    $conn->commit();

    $_SESSION['success'] = "Inventory record and specifications saved successfully!";
    unset($_SESSION['form_data']);

    $conn->close();
    redirectBack();
    } catch (Throwable $e) {
        if (isset($conn) && $conn instanceof mysqli) {
            try {
                $conn->rollback();
            } catch (Throwable $rollbackError) {
                error_log('entrydatahandler.php rollback failed: ' . $rollbackError->getMessage());
            }
        }

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
