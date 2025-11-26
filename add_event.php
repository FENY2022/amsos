<?php

        // add_event.php
        require_once 'calendarSchedulerdb.php';
        
        // Process form data
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $event_date = $_POST['event_date'];
            $remarks = $_POST['remarks'];
            $zoom_link = $_POST['zoom_link'];
            $password = $_POST['password'];
            $email = $_POST['email'];
        
            // Prepare the SQL statement
            $sql = "INSERT INTO events (event_date, remarks, zoom_link, password, email) 
                    VALUES (?, ?, ?, ?, ?)";
        
            // Prepare the statement
            if ($stmt = $conn->prepare($sql)) {
                // Bind parameters to the prepared statement
                $stmt->bind_param("sssss", $event_date, $remarks, $zoom_link, $password, $email);
        
                // Execute the statement
                if ($stmt->execute()) {
                    echo "<script>
                        alert('Event added successfully!');
                        window.location.href = 'mainmenu.php?dir=calendarScheduler';
                    </script>";
                } else {
                    echo "<script>
                        alert('Error: " . $stmt->error . "');
                        window.location.href = 'mainmenu.php?dir=calendarScheduler';
                    </script>";
                }
        
                // Close the statement
                $stmt->close();
            } else {
                echo "<script>
                    alert('Error: " . $conn->error . "');
                    window.location.href = 'mainmenu.php?dir=calendarScheduler';
                </script>";
            }
        
            // Close the connection
            $conn->close();
        }
        

?>