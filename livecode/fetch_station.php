<?php
// Include your database connection
require_once 'connect.php';

if(isset($_POST['office'])){
    $office = $_POST['office'];

    // Prepare and execute the query using prepared statements
    if ($stmt = $conn->prepare("SELECT DISTINCT station FROM srfsigner WHERE office = ? ORDER BY station ASC")) {
        $stmt->bind_param("s", $office);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo '<option value="">Select Station</option>';
        while ($station = $result->fetch_assoc()) {
            echo '<option value="'.$station['station'].'">'.$station['station'].'</option>';
        }

        $stmt->close();
    } else {
        echo 'Error preparing statement: ' . $conn->error;
    }
}
?>
