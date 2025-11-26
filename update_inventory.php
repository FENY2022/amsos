<?php
require_once 'connect.php';  // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $id = $_POST['id'];
    $employeeName = $_POST['employeeName'];
    $equipmentType = $_POST['equipmentType'];
    $yearAcquired = $_POST['yearAcquired'];
    $shelfLife = $_POST['shelfLife'];
    $brand = $_POST['brand'];
    $specifications = $_POST['specifications'];
    $rangeCategory = $_POST['rangeCategory'];
    $softwareInstalled = $_POST['softwareInstalled'];
    $licensingModel = $_POST['licensingModel'];
    $softwareInstalled_2 = $_POST['softwareInstalled_2'];  // New field
    $licensingModel_2 = $_POST['licensingModel_2'];        // New field
    $serialNumber = $_POST['serialNumber'];  // You forgot to include serialNumber in the binding
    $propertyNumber = $_POST['propertyNumber'];
    $accountablePerson = $_POST['accountablePerson'];
    $sex = $_POST['sex'];
    $officeDivision = $_POST['officeDivision'];
    $statusOfEmployment = $_POST['statusOfEmployment'];
    $actualUser = $_POST['actualUser'];
    $actualUserSex = $_POST['actualUserSex'];
    $actualUserStatusOfEmployment = $_POST['actualUserStatusOfEmployment'];
    $natureOfWork = $_POST['natureOfWork'];
    $remarks = $_POST['remarks'];
    $office = $_POST['office'];

    // Prepare SQL query for updating the record
    $stmt = mysqli_prepare($conn, "UPDATE inv_inventory SET 
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
        serialNumber = ?,  /* Added serialNumber */
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
        office = ? 
        WHERE id = ?");

    if (!$stmt) {
        echo "Prepare failed: (" . mysqli_errno($conn) . ") " . mysqli_error($conn);
        exit;
    }

    // Bind parameters
    mysqli_stmt_bind_param($stmt, 
        "sssssssssssssssssssssssi",  /* Added 's' for serialNumber */
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
        $serialNumber,  /* Now included */
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
        $id
    );

    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        
        echo "<script>
                alert('Form submitted successfully!');
                window.location.href = 'mainmenu.php?dir=edupdate';
              </script>";

 

        exit; // Ensure no further code is executed after redirection
            } else {
                echo "Error updating record: " . mysqli_stmt_error($stmt); // Use mysqli_stmt_error for more specific error
            }

    // Close statement
    mysqli_stmt_close($stmt);
}
?>
