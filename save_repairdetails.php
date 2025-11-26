<?php

require_once "connect.php";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $inventory_id = $_POST['id']; // The unique ID passed with the button or form
    $employeeName = $_POST['employeeName']; 
    $item_name = $_POST['item_name'];
    $property_number = $_POST['property_number'];
    $date_repaired = $_POST['date_repaired']; // Fixed typo to 'date_repaired'
    $status = $_POST['status'];

    // Insert the record into the database
    $sql = "INSERT INTO srf_repair_history (inventory_id, employee_name, item_name, property_number, date_repaired, status) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $inventory_id, $employeeName, $item_name, $property_number, $date_repaired, $status);

    if ($stmt->execute()) {
        // If success, show JavaScript alert and redirect
        echo "<script>
                alert('Record inserted successfully!');
                window.location.href = 'mainmenu.php?dir=requestlist';
              </script>";
        exit;
    } else {
        // If failure, show error message in JavaScript alert
        echo "<script>
                alert('Error inserting record: " . $conn->error . "');
                window.location.href = 'mainmenu.php?dir=requestlist';
              </script>";
    }

    $stmt->close();
}

$conn->close();

?>
