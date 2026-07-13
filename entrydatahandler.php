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
