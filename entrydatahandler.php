<?php
session_start();
require_once 'connect.php';

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
    
    // --- Robust N/A and Whitespace Handling for Serial Number (This part is correct) ---
    $serialNumberInput = isset($_POST['serialNumber']) ? trim($_POST['serialNumber']) : '';
    if (strtoupper($serialNumberInput) === 'N/A' || $serialNumberInput === '') {
        $serialNumber = 'N/A';
    } else {
        $serialNumber = $serialNumberInput;
    }

    // --- Robust N/A and Whitespace Handling for Property Number (This part is correct) ---
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
    $office = $_SESSION['OfficeSRF'];

    // --- NEW: Smarter Uniqueness Check ---
    $check_clauses = [];
    $params = [];
    $types = "";

    // 1. Only add propertyNumber to the check if it's a real value (not 'N/A').
    if (strtoupper($propertyNumber) !== 'N/A') {
        $check_clauses[] = "propertyNumber = ?";
        $params[] = $propertyNumber;
        $types .= "s";
    }

    // 2. Only add serialNumber to the check if it's a real value (not 'N/A').
    if (strtoupper($serialNumber) !== 'N/A') {
        $check_clauses[] = "serialNumber = ?";
        $params[] = $serialNumber;
        $types .= "s";
    }

    // 3. Only run the database query if there is at least one real value to check.
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
    // --- End of new uniqueness check logic ---

    // --- Prepare and execute the insertion query ---
    $sql = "INSERT INTO inv_inventory (employeeName, equipmentType, yearAcquired, shelfLife, brand, specifications, rangeCategory, softwareInstalled, licensingModel, serialNumber, propertyNumber, accountablePerson, sex, officeDivision, statusOfEmployment, actualUser, actualUserSex, actualUserStatusOfEmployment, natureOfWork, remarks, amount, depreciation_value, office) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssssssssssssssssssdss",
            $employeeName, $equipmentType, $yearAcquired, $shelfLife, $brand, $specifications, $rangeCategory, $softwareInstalled, $licensingModel, $serialNumber, $propertyNumber, $accountablePerson, $sex, $officeDivision, $statusOfEmployment, $actualUser, $actualUserSex, $actualUserStatusOfEmployment, $natureOfWork, $remarks, $amount, $depreciation_value, $office);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Inventory record saved successfully!";
            unset($_SESSION['form_data']);
        } else {
            $_SESSION['error'] = "Error: Could not execute the insertion query.";
            $_SESSION['form_data'] = $_POST;
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Error: Could not prepare the insertion query.";
        $_SESSION['form_data'] = $_POST;
    }

    $conn->close();
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>