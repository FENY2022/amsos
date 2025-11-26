<?php
// Database connection
include 'connect.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $equipment_id = $_POST['equipment_id'];

    // Output CSS for Timeline
    echo "
    <style>
        /* Timeline Styles */
        .timeline {
            position: relative;
            padding: 20px 0;
            list-style: none;
        }
        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 30px;
            width: 4px;
            height: 100%;
            background: #ddd;
        }
        .timeline-item {
            position: relative;
            margin: 10px 0 20px;
            padding-left: 60px;
        }
        .timeline-icon {
            position: absolute;
            top: 10px;
            left: 20px;
            width: 20px;
            height: 20px;
            background-color: #6c757d;
            border-radius: 50%;
            border: 3px solid white;
        }
        .timeline-content {
            padding: 10px 15px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .timeline-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .timeline-details {
            margin-bottom: 10px;
            font-size: 14px;
            color: #666;
        }
        .timeline-datetime {
            font-size: 12px;
            color: #999;
        }
        @media (max-width: 768px) {
            .timeline-item {
                padding-left: 40px;
            }
            .timeline-icon {
                left: 10px;
            }
            .timeline-content {
                padding: 8px 10px;
            }
            .timeline-title {
                font-size: 14px;
            }
            .timeline-details {
                font-size: 12px;
            }
            .timeline-datetime {
                font-size: 10px;
            }
        }
    </style>";

    // Prepare the SQL query with the condition:
    // "trackid = ? OR (equipment_id = ? AND equipment_id <> 0)"
    $stmt = $conn->prepare("
        SELECT DISTINCT 
            trackid, 
            name, 
            details, 
            date, 
            time, 
            status
        FROM srfhistory
        WHERE 
            trackid = ?
            OR (equipment_id = ? AND equipment_id <> 0)
    ");

    // Assuming 'id' can be treated as a string. If it's an integer, switch to "ii".
    $stmt->bind_param("ss", $id, $equipment_id);

    // Execute the query
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='timeline'>"; // Start timeline container

        while ($row = $result->fetch_assoc()) {
            // Escape data to prevent XSS
            $name   = htmlspecialchars($row['name'],   ENT_QUOTES);
            $details= htmlspecialchars($row['details'],ENT_QUOTES);
            $date   = htmlspecialchars($row['date'],   ENT_QUOTES);
            $time   = htmlspecialchars($row['time'],   ENT_QUOTES);
            $status = htmlspecialchars($row['status'], ENT_QUOTES);

            // Timeline item structure
            echo "
            <div class='timeline-item'>
                <div class='timeline-icon'></div>
                <div class='timeline-content'>
                    <h4 class='timeline-title'>{$name} - {$status}</h4>
                    <p class='timeline-details'>{$details}</p>
                    <span class='timeline-datetime'>Date: {$date} | Time: {$time}</span>
                </div>
            </div>";
        }

        echo "</div>"; // End timeline container
    } else {
        echo "<p>No history found. Please check back later.</p>";
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>
