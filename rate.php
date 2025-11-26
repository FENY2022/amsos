<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve the submitted form data
    $srf_id = intval($_POST['srf_id']);
    $feedback = htmlspecialchars(trim($_POST['feedback']));
    $acknowledged_by = htmlspecialchars(trim($_POST['acknowledged_by']));

    // Ensure all required fields are provided
    if (empty($srf_id) || empty($feedback) || empty($acknowledged_by)) {
        echo '<script>alert("Please fill in all required fields."); window.history.back();</script>';
        exit;
    }

    // Insert feedback into srffeedback table
    $query1 = "INSERT INTO srffeedback (srf_id, feedback, acknowledged_by, date_rated, created_at) VALUES (?, ?, ?, NOW(), NOW())";
    $stmt1 = $conn->prepare($query1);
    $stmt1->bind_param("iss", $srf_id, $feedback, $acknowledged_by);

    // Update tracking in the srf table
    $new_value = '103'; // New tracking value
    $query2 = "UPDATE srf SET tracking = ? WHERE id = ?";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param("si", $new_value, $srf_id);

    // Execute the queries and handle errors
    $success1 = $stmt1->execute();
    $success2 = $stmt2->execute();

    if ($success1 && $success2) {
        echo '<script>alert("FEEDBACK RATING: Thank you for your valuable feedback. Your insights help the RICTU Help Desk to continuously enhance our technical assistance to support DENR Caraga\'s internal customers better."); window.history.back();</script>';
    } else {
        // Combine errors for better debugging
        $error1 = $stmt1->error;
        $error2 = $stmt2->error;
        echo "Error submitting feedback or updating tracking: $error1 $error2";
    }

    // Close statements and connection
    $stmt1->close();
    $stmt2->close();
    $conn->close();
}
?>
