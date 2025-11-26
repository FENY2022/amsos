<?php

require_once 'connect.php'; // Database connection

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the input from the form
    $equipment_name = $_POST['firstname'];

    // First, check if the equipment name already exists
    $check_sql = "SELECT * FROM inv_typeofequipment WHERE equipment_name = ?";
    if ($check_stmt = $conn->prepare($check_sql)) {
        // Bind the parameter
        $check_stmt->bind_param("s", $equipment_name);

        // Execute the statement
        $check_stmt->execute();

        // Store the result
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // Equipment name already exists, show an alert and go back
            echo "<script>
                    alert('Equipment name already exists!');
                    window.history.back();
                </script>";
        } else {
            // Equipment name doesn't exist, insert the new record
            $sql = "INSERT INTO inv_typeofequipment (equipment_name) VALUES (?)";

            if ($stmt = $conn->prepare($sql)) {
                // Bind parameters
                $stmt->bind_param("s", $equipment_name); // "s" means the parameter is a string

                // Execute the statement
                if ($stmt->execute()) {
                    // Show success message and go back
                    echo "<script>
                            alert('Equipment added successfully!');
                            window.history.back();
                        </script>";
                } else {
                    // Handle execution error
                    echo "Error: Could not execute the query: " . $conn->error;
                }

                // Close the prepared statement
                $stmt->close();
            } else {
                // Handle prepare error
                echo "Error: Could not prepare the query: " . $conn->error;
            }
        }

        // Close the check statement
        $check_stmt->close();
    } else {
        // Handle prepare error for the check query
        echo "Error: Could not prepare the query: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
}
?>
