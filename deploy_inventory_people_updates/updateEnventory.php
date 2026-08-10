<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

$inventory_id = $_POST['id'] ?? $_GET['id'] ?? '';
$error_message = '';
$success_message = '';

function requestValue($key, $default = '') {
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function requestHas($key) {
    return isset($_POST[$key]) || isset($_GET[$key]);
}

if (!empty($inventory_id)) {
    $sql = "SELECT * FROM inv_inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inventory_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $inventory = $result->fetch_assoc();
    } else {
        $error_message = "Inventory record not found!";
    }
} else {
    $error_message = "Invalid inventory ID!";
}

if (in_array($_SERVER["REQUEST_METHOD"], ["GET", "POST"], true) && !empty($inventory_id)) {
    try {
        $employeeName = requestValue('employeeName');
        $equipmentType = requestValue('equipmentType');
        $yearAcquired = requestValue('yearAcquired');
        $shelfLife = requestValue('shelfLife');
        $brand = requestValue('brand');
        $specifications = requestValue('specifications');
        $rangeCategory = requestValue('rangeCategory');
        $softwareInstalled = requestValue('softwareInstalled');
        $licensingModel = requestValue('licensingModel');
        $softwareInstalled_2 = requestValue('softwareInstalled_2');
        $licensingModel_2 = requestValue('licensingModel_2');
        $serialNumber = requestValue('serialNumber');
        $propertyNumber = requestValue('propertyNumber');
        $accountablePerson = requestValue('accountablePerson');
        $sex = requestValue('sex');
        $officeDivision = requestValue('officeDivision');
        $statusOfEmployment = requestValue('statusOfEmployment');
        $actualUser = requestValue('actualUser');
        $actualUserSex = requestValue('actualUserSex');
        $actualUserStatusOfEmployment = requestValue('actualUserStatusOfEmployment');
        $natureOfWork = requestValue('natureOfWork');
        $remarks = requestValue('remarks');
        $office = requestValue('office');
        $amount = str_replace(',', '', requestValue('amount', '0'));
        $depreciation_value = requestValue('depreciation_value', '0');
        $mark_as_done = requestHas('mark_as_done') ? 1 : 0;
        $officeId = getOrCreateOfficeDivisionId($conn, $office, $officeDivision);
        $employeePersonId = getOrCreateInventoryPersonId($conn, $employeeName, $officeId, $office, $officeDivision, $statusOfEmployment, 'employeeName');
        $accountablePersonId = getOrCreateInventoryPersonId($conn, $accountablePerson, $officeId, $office, $officeDivision, $statusOfEmployment, 'accountablePerson');
        $actualUserId = getOrCreateInventoryPersonId($conn, $actualUser, $officeId, $office, $officeDivision, $actualUserStatusOfEmployment, 'actualUser');

        $amount_int = (int)$amount;
        $depreciation_value_int = (int)$depreciation_value;
        $inventory_id_int = (int)$inventory_id;

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
            'softwareInstalled_2' => ['s', $softwareInstalled_2],
            'licensingModel_2' => ['s', $licensingModel_2],
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
            'office' => ['s', $office],
            'office_id' => ['i', $officeId],
            'amount' => ['i', $amount_int],
            'depreciation_value' => ['i', $depreciation_value_int],
            'mark_as_done' => ['i', $mark_as_done],
        ];

        $assignments = [];
        $types = '';
        $params = [];
        foreach ($fields as $column => $definition) {
            if (columnExists($conn, 'inv_inventory', $column)) {
                $assignments[] = "$column = ?";
                $types .= $definition[0];
                $params[] = $definition[1];
            }
        }

        if (empty($assignments)) {
            throw new RuntimeException('No matching inv_inventory columns found for update.');
        }

        $types .= 'i';
        $params[] = $inventory_id_int;
        $update_sql = 'UPDATE inv_inventory SET ' . implode(', ', $assignments) . ' WHERE id = ?';

        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param($types, ...$params);

        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Inventory record updated successfully!";
            header("Location: editEnventory.php?id=$inventory_id_int");
        } else {
            $_SESSION['error_message'] = "Error updating inventory record: " . $conn->error;
        }
    } catch (Throwable $e) {
        error_log('updateEnventory.php failed for ID ' . $inventory_id . ': ' . $e->getMessage());
        $_SESSION['error_message'] = 'Error updating inventory record. Please check the server error log.';
        header('Location: editEnventory.php?id=' . (int)$inventory_id);
        exit;
    }
}
?>
