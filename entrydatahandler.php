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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // --- Collect and sanitize form data ---
    $amount = str_replace(',', '', $_POST['amount']);
    $depreciation_value = $_POST['depreciation_value'] ?? "";
    $employeeName = $_POST['employeeName'];
    $equipmentType = $_POST['equipmentType'];
    $yearAcquired = $_POST['yearAcquired'];
    $shelfLife = $_POST['shelfLife'];
    $brand = $_POST['brand'];
    $specifications = $_POST['specifications'];
    $rangeCategory = $_POST['rangeCategory'];
    $softwareInstalled = $_POST['softwareInstalled'];
    $licensingModel = $_POST['licensingModel'];
    
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

    $accountablePerson = $_POST['accountablePerson'];
    $sex = $_POST['sex'];
    $officeDivision = $_POST['officeDivision'];
    $statusOfEmployment = $_POST['statusOfEmployment'];
    $actualUser = $_POST['actualUser'];
    $actualUserSex = $_POST['actualUserSex'];
    $actualUserStatusOfEmployment = $_POST['actualUserStatusOfEmployment'];
    $natureOfWork = $_POST['natureOfWork'];
    $remarks = $_POST['remarks'];
    
    // ---------------------------------------------------------
    // 1. CAPTURE THE NEW VARIABLE
    // ---------------------------------------------------------
    $computer_specs = $_POST['computer_specs']; 
    
    $office = $_SESSION['OfficeSRF'];
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
                $_SESSION['form_data'] = $_POST;
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            }
        } else {
            $_SESSION['error'] = "Error preparing the uniqueness check query.";
            $_SESSION['form_data'] = $_POST;
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }

    // --- Prepare and execute the insertion query ---
    
    // ---------------------------------------------------------
    // 2. ADD COLUMN TO SQL INSERT
    // Added 'computer_specs' to columns and '?' to values
    // ---------------------------------------------------------
    $sql = "INSERT INTO inv_inventory (employeeName, employee_person_id, equipmentType, yearAcquired, shelfLife, brand, specifications, rangeCategory, softwareInstalled, licensingModel, serialNumber, propertyNumber, accountablePerson, accountable_person_id, sex, officeDivision, statusOfEmployment, actualUser, actual_user_id, actualUserSex, actualUserStatusOfEmployment, natureOfWork, remarks, amount, depreciation_value, office, office_id, computer_specs) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        // ---------------------------------------------------------
        // 3. UPDATE BIND_PARAM
        // Added 's' to type string (now 24 chars) and '$computer_specs' to variables
        // ---------------------------------------------------------
        $types = "si" . str_repeat("s", 11) . "i" . str_repeat("s", 4) . "i" . str_repeat("s", 4) . "dssis";
        $stmt->bind_param($types,
            $employeeName, $employeePersonId, $equipmentType, $yearAcquired, $shelfLife, $brand, $specifications, $rangeCategory, $softwareInstalled, $licensingModel, $serialNumber, $propertyNumber, $accountablePerson, $accountablePersonId, $sex, $officeDivision, $statusOfEmployment, $actualUser, $actualUserId, $actualUserSex, $actualUserStatusOfEmployment, $natureOfWork, $remarks, $amount, $depreciation_value, $office, $officeId, $computer_specs);

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
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
