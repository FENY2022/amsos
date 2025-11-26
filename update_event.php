<?php

    require_once 'connect.php';
    
    $data = json_decode(file_get_contents("php://input"));
    
    if (isset($data->id)) {
        $stmt = $conn->prepare("UPDATE events SET event_date=?, remarks=?, zoom_link=?, password=?, email=? WHERE id=?");
        $stmt->bind_param("sssssi", $data->date, $data->remarks, $data->zoom_link, $data->password, $data->email, $data->id);
    
        if ($stmt->execute()) {
            echo "Event updated successfully.";
        } else {
            echo "Error updating event.";
        }
    
        $stmt->close();
    } else {
        echo "Invalid request.";
    }
    
    $conn->close();

?>
