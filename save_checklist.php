<?php
// Database connection
require_once 'connect.php';

// Get the maintenance ID from the POST data
$maintenance_id = isset($_POST['inv_id']) ? intval($_POST['inv_id']) : 0;

// Save checklist data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $division = $conn->real_escape_string($_POST['division'] ?? '');
    $used_by = $conn->real_escape_string($_POST['used-by'] ?? '');
    $article = $conn->real_escape_string($_POST['article'] ?? '');
    $property_no = $conn->real_escape_string($_POST['property-no'] ?? '');
    $accounting_officer = $conn->real_escape_string($_POST['accounting-officer'] ?? '');
    $mr_number = $conn->real_escape_string($_POST['mr-number'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $remarks = $conn->real_escape_string($_POST['remarks'] ?? ''); // Capture remarks
    $tasks = $_POST['tasks'] ?? []; // Tasks that are currently checked
    $brand = $_POST['brand'] ?? []; // Tasks that are currently checked

    // Retrieve all tasks currently in the database for this maintenance ID
    $existingTasks = [];
    $sql = "SELECT task, month FROM inv_preventive_maintenance_schedule WHERE inv_id = '$maintenance_id'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $existingTasks[$row['task']][$row['month']] = true;
    }

    // Loop through the submitted tasks to insert or update
    foreach ($tasks as $task => $months) {
        foreach ($months as $month => $status) {
            $task = $conn->real_escape_string($task);
            $month = intval($month);
            $status = 1; // Checked

            $sql = "INSERT INTO inv_preventive_maintenance_schedule 
                    (inv_id, maintenance_id, division, used_by, article, property_no, accounting_officer, mr_number, description, remarks, task, month, status) 
                    VALUES 
                    ('$maintenance_id', '$maintenance_id', '$division', '$used_by', '$article', '$property_no', '$accounting_officer', '$mr_number', '$description', '$remarks', '$task', '$month', '$status')
                    ON DUPLICATE KEY UPDATE status = '$status', remarks = '$remarks'";
            $conn->query($sql);

            // Remove this task from the existing tasks list, as it is still checked
            unset($existingTasks[$task][$month]);
        }
    }

    // Delete tasks that are no longer checked
    foreach ($existingTasks as $task => $months) {
        foreach ($months as $month => $_) {
            $task = $conn->real_escape_string($task);
            $month = intval($month);
            $sql = "DELETE FROM inv_preventive_maintenance_schedule 
                    WHERE inv_id = '$maintenance_id' AND task = '$task' AND month = '$month'";
            $conn->query($sql);
        }
    }



    $NID = 1;  // Changed to an integer
    $stationID = $_SESSION['StationidSRF'];
    
    $sql = "SELECT * FROM srfsigner WHERE stationid = ? AND level = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $stationID, $NID);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);
    
    if ($results) {
        foreach ($results as $row) {
            $_SESSION['tracking'] = htmlspecialchars($row['personelid']); // Store in session
        }
    }
    


    // 📌 Add Notification Entry After Checklist Update
    $inv_id = $maintenance_id;
    $property_name = $article; // Assuming the article is the property name
    $property_number = $property_no;
    $notification_remarks = "Preventive maintenance updated for property: $property_name";
    $action = false; // Notification status set to pending
    $tracking = $_SESSION['tracking'];

    $notif_sql = "INSERT INTO inv_notification (inv_id, brand, property_name, property_number, details, remarks, action, tracking) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($notif_sql);
    $stmt->bind_param("isssssii", $inv_id, $brand, $property_name, $property_number, $remarks, $notification_remarks, $action, $tracking);

    if ($stmt->execute()) {
        echo "<script>alert('Checklist and notification updated successfully!');</script>";
    } else {
        echo "<script>alert('Failed to add notification: " . $stmt->error . "');</script>";
    }

    $stmt->close();

    echo "<script>
    window.location.href = 'mainmenu.php?dir=preventive_maintenance_form&id=$maintenance_id';
    </script>";
}

$conn->close();
?>
