<?php
session_start();
require_once 'connect.php';

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

    if ($office === '' || $officeDivision === '') {
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

    if (!isValidInventoryPersonName($name)) {
        return null;
    }

    $normalizedName = strtoupper($name);
    $office = trim((string)$office);
    $officeDivision = trim((string)$officeDivision);
    $employmentStatus = trim((string)$employmentStatus);

    $stmt = $conn->prepare("SELECT id FROM inventory_people WHERE normalized_name = ? LIMIT 1");
    $stmt->bind_param("s", $normalizedName);
    $stmt->execute();
    $stmt->bind_result($personId);
    if ($stmt->fetch()) {
        $stmt->close();
        $stmt = $conn->prepare("UPDATE inventory_people SET office_id = ?, office = ?, officeDivision = ?, employment_status = IF(? = '', employment_status, ?) WHERE id = ?");
        $stmt->bind_param("issssi", $officeId, $office, $officeDivision, $employmentStatus, $employmentStatus, $personId);
        $stmt->execute();
        $stmt->close();
        return (int)$personId;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO inventory_people (full_name, normalized_name, office_id, office, officeDivision, employment_status, source) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissss", $name, $normalizedName, $officeId, $office, $officeDivision, $employmentStatus, $source);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    return (int)$newId;
}

// Initialize variables
$inventory_id = $_POST['id'] ?? $_GET['id'] ?? '';
$error_message = '';
$success_message = '';

function requestValue($key, $default = '') {
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function requestHas($key) {
    return isset($_POST[$key]) || isset($_GET[$key]);
}

// Fetch inventory details for editing
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

// Handle form submission
if (in_array($_SERVER["REQUEST_METHOD"], ["GET", "POST"], true) && !empty($inventory_id)) {
    // Collect form data
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

    // Convert values to appropriate types
    $amount_int = (int)$amount;
    $depreciation_value_int = (int)$depreciation_value;
    $mark_as_done_str = (string)$mark_as_done;
    $inventory_id_int = (int)$inventory_id;

    // Prepare update statement
    $update_sql = "UPDATE inv_inventory SET 
        employeeName = ?,
        employee_person_id = ?,
        equipmentType = ?,
        yearAcquired = ?,
        shelfLife = ?,
        brand = ?,
        specifications = ?,
        rangeCategory = ?,
        softwareInstalled = ?,
        licensingModel = ?,
        softwareInstalled_2 = ?,
        licensingModel_2 = ?,
        serialNumber = ?,
        propertyNumber = ?,
        accountablePerson = ?,
        accountable_person_id = ?,
        sex = ?,
        officeDivision = ?,
        statusOfEmployment = ?,
        actualUser = ?,
        actual_user_id = ?,
        actualUserSex = ?,
        actualUserStatusOfEmployment = ?,
        natureOfWork = ?,
        remarks = ?,
        office = ?,
        office_id = ?,
        amount = ?,
        depreciation_value = ?,
        mark_as_done = ?
    WHERE id = ?";

    $update_stmt = $conn->prepare($update_sql);
    $types = "si" . str_repeat("s", 13) . "i" . str_repeat("s", 4) . "i" . str_repeat("s", 5) . "i" . "sssi";
    $update_stmt->bind_param(
        $types,
        $employeeName,
        $employeePersonId,
        $equipmentType,
        $yearAcquired,
        $shelfLife,
        $brand,
        $specifications,
        $rangeCategory,
        $softwareInstalled,
        $licensingModel,
        $softwareInstalled_2,
        $licensingModel_2,
        $serialNumber,
        $propertyNumber,
        $accountablePerson,
        $accountablePersonId,
        $sex,
        $officeDivision,
        $statusOfEmployment,
        $actualUser,
        $actualUserId,
        $actualUserSex,
        $actualUserStatusOfEmployment,
        $natureOfWork,
        $remarks,
        $office,
        $officeId,
        $amount,
        $depreciation_value,
        $mark_as_done_str,
        $inventory_id_int
    );

    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Inventory record updated successfully!";
        header("Location: editEnventory.php?id=$inventory_id_int");
    } else {
        $_SESSION['error_message'] = "Error updating inventory record: " . $conn->error;    }
}
?>
