<?php
// Database connection
require_once 'connect.php';
require_once 'repair_history_helpers.php';

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
    $completedTasks = [];

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
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            $completedTasks[] = $task . ' (' . $monthName . ')';

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

    $actionStaff = $_SESSION['Full_NameSRF'] ?? '';
    $taskSummary = !empty($completedTasks) ? implode('; ', array_unique($completedTasks)) : 'Preventive maintenance checklist updated.';
    $actionTaken = trim($remarks) !== '' ? $remarks : $taskSummary;
    repairHistoryInsertPreventiveMaintenance($conn, $maintenance_id, $taskSummary, 'Completed', $actionStaff, $actionTaken);

    echo "<script>
    alert('Checklist updated successfully!');
    window.location.href = 'mainmenu.php?dir=preventive_maintenance_form&id=$maintenance_id';
        </script>";
}

$conn->close();
?>
