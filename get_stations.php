<?php

require_once "connect.php" ;

$office = isset($_GET['office']) ? $_GET['office'] : '';

if ($office) {
   

    $sql = "SELECT DISTINCT Station FROM signatory_setup WHERE Office = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $office);
    $stmt->execute();
    $result = $stmt->get_result();

    $stations = [];
    while ($row = $result->fetch_assoc()) {
        $stations[] = $row['Station'];
    }

    echo json_encode($stations);



}
?>
