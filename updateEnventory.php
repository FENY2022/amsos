<?php
session_start();
require_once 'connect.php';

// Initialize variables
$inventory_id = isset($_GET['id']) ? $_GET['id'] : '';
$error_message = '';
$success_message = '';

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
if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($inventory_id)) {
    // Collect form data
    $employeeName = $_GET['employeeName'];
    $equipmentType = $_GET['equipmentType'];
    $yearAcquired = $_GET['yearAcquired'];
    $shelfLife = $_GET['shelfLife'];
    $brand = $_GET['brand'];
    $specifications = $_GET['specifications'];
    $rangeCategory = $_GET['rangeCategory'];
    $softwareInstalled = $_GET['softwareInstalled'];
    $licensingModel = $_GET['licensingModel'];
    $softwareInstalled_2 = $_GET['softwareInstalled_2'];
    $licensingModel_2 = $_GET['licensingModel_2'];
    $serialNumber = $_GET['serialNumber'];
    $propertyNumber = $_GET['propertyNumber'];
    $accountablePerson = $_GET['accountablePerson'];
    $sex = $_GET['sex'];
    $officeDivision = $_GET['officeDivision'];
    $statusOfEmployment = $_GET['statusOfEmployment'];
    $actualUser = $_GET['actualUser'];
    $actualUserSex = $_GET['actualUserSex'];
    $actualUserStatusOfEmployment = $_GET['actualUserStatusOfEmployment'];
    $natureOfWork = $_GET['natureOfWork'];
    $remarks = $_GET['remarks'];
    $office = $_GET['office'];
    $amount = str_replace(',', '', $_GET['amount']); // Remove commas from amount
    $depreciation_value = $_GET['depreciation_value'];
    $mark_as_done = isset($_GET['mark_as_done']) ? 1 : 0;

    // Convert values to appropriate types
    $amount_int = (int)$amount;
    $depreciation_value_int = (int)$depreciation_value;
    $mark_as_done_str = (string)$mark_as_done;
    $inventory_id_int = (int)$inventory_id;

    // Prepare update statement
    $update_sql = "UPDATE inv_inventory SET 
        employeeName = ?,
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
        sex = ?,
        officeDivision = ?,
        statusOfEmployment = ?,
        actualUser = ?,
        actualUserSex = ?,
        actualUserStatusOfEmployment = ?,
        natureOfWork = ?,
        remarks = ?,
        office = ?,
        amount = ?,
        depreciation_value = ?,
        mark_as_done = ?
    WHERE id = ?";

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param(
        "ssssssssssssssssssssssssssi",
        $employeeName,
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
        $sex,
        $officeDivision,
        $statusOfEmployment,
        $actualUser,
        $actualUserSex,
        $actualUserStatusOfEmployment,
        $natureOfWork,
        $remarks,
        $office,
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