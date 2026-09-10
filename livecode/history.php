<?php
include 'connect.php';

// 1. Check if trackid is passed in the URL
if (isset($_GET['trackid'])) {
    $trackid = $_GET['trackid'];

    // 2. Prepare statement
    $sql = "SELECT trackid, name, details, date, time, status FROM srfhistory WHERE trackid = ? ORDER BY date DESC, time DESC";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $trackid);
        $stmt->execute();
        $result2 = $stmt->get_result();

        // Start Output
        echo '<!DOCTYPE html>
              <html lang="en">
              <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    body { background-color: #f8f9fa; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
                    
                    /* Timeline CSS */
                    .timeline {
                        position: relative;
                        padding-left: 3rem;
                        margin: 0 0 0 1rem;
                        border-left: 2px solid #e9ecef;
                    }
                    .timeline-item {
                        position: relative;
                        margin-bottom: 2rem;
                    }
                    .timeline-marker {
                        position: absolute;
                        left: -3.6rem;
                        top: 0;
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        text-align: center;
                        line-height: 40px;
                        color: #fff;
                        box-shadow: 0 0 0 4px #fff;
                    }
                    .timeline-content {
                        background: #fff;
                        border-radius: 0.5rem;
                        padding: 1.25rem;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                        position: relative;
                        border: 1px solid #eaeaea;
                    }
                    .timeline-content:before {
                        content: "";
                        position: absolute;
                        left: -8px;
                        top: 15px;
                        width: 15px;
                        height: 15px;
                        background: #fff;
                        transform: rotate(45deg);
                        border-left: 1px solid #eaeaea;
                        border-bottom: 1px solid #eaeaea;
                    }
                    .time-stamp {
                        font-size: 0.85rem;
                        color: #6c757d;
                        margin-bottom: 0.5rem;
                        display: block;
                    }
                    .user-avatar {
                        width: 24px;
                        height: 24px;
                        border-radius: 50%;
                        background: #e9ecef;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        margin-right: 5px;
                        font-size: 0.7rem;
                    }
                </style>
              </head>
              <body>';

        echo "<div class='container-fluid p-4'>";
        
        if ($result2->num_rows > 0) {
            echo "<h5 class='mb-4 text-primary'><i class='fas fa-history me-2'></i>History for Ticket #".htmlspecialchars($trackid)."</h5>";
            echo "<div class='timeline'>";
            
            while($row = $result2->fetch_assoc()) {
                // Determine styling based on Status keywords
                $statusLower = strtolower($row['status']);
                $bgClass = 'bg-secondary';
                $icon = 'fa-circle';

                if (strpos($statusLower, 'completed') !== false || strpos($statusLower, 'done') !== false || strpos($statusLower, 'approve') !== false) {
                    $bgClass = 'bg-success';
                    $icon = 'fa-check';
                } elseif (strpos($statusLower, 'disapproved') !== false || strpos($statusLower, 'cancelled') !== false) {
                    $bgClass = 'bg-danger';
                    $icon = 'fa-times';
                } elseif (strpos($statusLower, 'pending') !== false || strpos($statusLower, 'serving') !== false) {
                    $bgClass = 'bg-warning text-dark';
                    $icon = 'fa-clock';
                } elseif (strpos($statusLower, 'assign') !== false) {
                    $bgClass = 'bg-info text-white';
                    $icon = 'fa-user-tag';
                }

                // Format Date
                $dateObj = DateTime::createFromFormat('Y-m-d', $row['date']);
                $formattedDate = $dateObj ? $dateObj->format('M d, Y') : $row['date'];
                
                // Convert Time to 12hr format if needed, assuming stored as H:i:s
                $timeObj = DateTime::createFromFormat('H:i:s', $row['time']);
                $formattedTime = $timeObj ? $timeObj->format('g:i A') : $row['time'];

                echo "<div class='timeline-item'>";
                    // The Icon Marker
                    echo "<div class='timeline-marker {$bgClass}'>";
                        echo "<i class='fas {$icon}'></i>";
                    echo "</div>";

                    // The Content Card
                    echo "<div class='timeline-content'>";
                        echo "<span class='time-stamp'><i class='far fa-clock me-1'></i> {$formattedDate} at {$formattedTime}</span>";
                        
                        echo "<h6 class='fw-bold text-dark mb-1'>" . htmlspecialchars($row['status']) . "</h6>";
                        
                        if(!empty($row['details'])) {
                            echo "<p class='text-muted mb-2 small'>" . htmlspecialchars($row['details']) . "</p>";
                        }

                        echo "<div class='d-flex align-items-center mt-2 border-top pt-2'>";
                            echo "<div class='user-avatar'><i class='fas fa-user text-secondary'></i></div>";
                            echo "<small class='text-secondary fw-semibold'>" . htmlspecialchars($row['name']) . "</small>";
                        echo "</div>";

                    echo "</div>"; // End content
                echo "</div>"; // End item
            }

            echo "</div>"; // End timeline container
        } else {
            echo "<div class='alert alert-light text-center border shadow-sm p-5'>
                    <img src='https://cdn-icons-png.flaticon.com/512/7486/7486754.png' width='80' class='mb-3 opacity-50'>
                    <h6 class='text-muted'>No history records found for Track ID: " . htmlspecialchars($trackid) . "</h6>
                  </div>";
        }
        echo "</div>"; // End main container
        echo "</body></html>";
        
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    echo "<div class='container p-3'><div class='alert alert-danger'>No Track ID provided.</div></div>";
}

// Close the database connection
$conn->close();
?>