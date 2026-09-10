<?php
// Ensure this path is correct and points to your database connection file
require_once 'connect.php'; 

// Set header to indicate JSON response
header('Content-Type: application/json'); 

// Start session if not already started (needed for $_SESSION['OfficeSRF'])
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// For testing purposes, I'm defining a session variable if it's not set.
// In a live environment, $_SESSION['OfficeSRF'] should be reliably set during user login.
if (!isset($_SESSION['OfficeSRF'])) {
    // IMPORTANT: Replace 'some_default_office' with a real default or handle unauthenticated access.
    // In a real application, you might redirect if $_SESSION['OfficeSRF'] is not set.
    $_SESSION['OfficeSRF'] = 'some_default_office'; 
}

// Get the selected office division from the AJAX request
// Use null coalescing operator (??) for cleaner handling of unset $_GET variables
$officeDivision = $_GET['officeDivision'] ?? ''; 

$employees = []; // Initialize an empty array to hold employee names

function employeeLookupColumnExists($conn, $column) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventory_people' AND COLUMN_NAME = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("s", $column);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int)$count > 0;
}

// Only proceed if an office division is provided and the session office is set
if (!empty($officeDivision) && !empty($_SESSION['OfficeSRF'])) {
    try {
        if (!employeeLookupColumnExists($conn, 'full_name') || !employeeLookupColumnExists($conn, 'office') || !employeeLookupColumnExists($conn, 'officeDivision')) {
            echo json_encode($employees);
            $conn->close();
            exit();
        }

        // Select inventory people from the local inventory people master list.
        $sql = "SELECT full_name FROM inventory_people WHERE office = ? AND officeDivision = ? ORDER BY full_name ASC";
        
        // Use mysqli_prepare for secure execution to prevent SQL injection
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind parameters: 'ss' means two string parameters
            $stmt->bind_param("ss", $_SESSION['OfficeSRF'], $officeDivision);
            
            // Execute the prepared statement
            $stmt->execute();
            
            // Get the result set
            $result = $stmt->get_result();

            // Fetch results and add to the employees array
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Ensure HTML special characters are handled if employee names might contain them
                    $employees[] = htmlspecialchars($row['full_name']);
                }
            }
            $stmt->close(); // Close the statement
        } else {
            // Log the error for debugging purposes (check your PHP error logs)
            error_log("Failed to prepare statement in get_employees.php: " . $conn->error);
            // Optionally, return an empty array or an error message to the client
            // $employees = ['error' => 'Database query failed.']; 
        }
    } catch (Throwable $e) {
        error_log("Employee lookup failed in get_employees.php: " . $e->getMessage());
    }
}

// Return employee names as a JSON array
echo json_encode($employees);

// Close the database connection
$conn->close();
?>
