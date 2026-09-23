<?php
// Ensure this path is correct and points to your database connection file
require_once 'connect.php'; 

// Set header to indicate JSON response
header('Content-Type: application/json'); 

// Start session if not already started (needed for $_SESSION['OfficeSRF'])
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$sessionOffice = trim((string)($_SESSION['OfficeSRF'] ?? ''));

// Get the selected office division from the AJAX request
// Use null coalescing operator (??) for cleaner handling of unset $_GET variables
$officeDivision = $_GET['officeDivision'] ?? ''; 

$employees = []; // Initialize an empty array to hold employee names

function employeeLookupColumnExists($conn, $table, $column) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int)$count > 0;
}

function loadEmployeesFromQuery($conn, $sql, $types, $params, $column) {
    $employees = [];
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Failed to prepare employee lookup: " . $conn->error);
        return $employees;
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $employees[] = htmlspecialchars($row[$column]);
    }

    $stmt->close();
    return $employees;
}

// Only proceed if an office division is provided and the session office is set
if (!empty($officeDivision) && !empty($sessionOffice)) {
    try {
        if (employeeLookupColumnExists($conn, 'inv_inventory', 'employeeName') && employeeLookupColumnExists($conn, 'inv_inventory', 'Office') && employeeLookupColumnExists($conn, 'inv_inventory', 'officeDivision')) {
            $employees = loadEmployeesFromQuery(
                $conn,
                "SELECT DISTINCT employeeName FROM inv_inventory WHERE UPPER(TRIM(Office)) = UPPER(TRIM(?)) AND UPPER(TRIM(officeDivision)) = UPPER(TRIM(?)) AND TRIM(employeeName) != '' ORDER BY employeeName ASC",
                "ss",
                [$sessionOffice, $officeDivision],
                'employeeName'
            );
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
