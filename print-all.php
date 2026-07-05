    <?php 

    require_once 'connect_amsos.php';

    // Fetch filter values
    $date_filter = $_GET['date_filter'] ?? 'this_month';
    $from_date = $_GET['from_date'] ?? '';
    $to_date = $_GET['to_date'] ?? '';
    $date_filter = $_GET['date_filter'] ?? '';
    $from_date = $_GET['from_date'] ?? '';
    $to_date = $_GET['to_date'] ?? '';
    $show_rows = $_GET['show_rows'] ?? 10; // Default to 10 rows

    // Sanitize and validate show_rows
    $show_rows = (int)$show_rows;
    if ($show_rows < 1) {
        $show_rows = 10; // Set minimum to 10 rows
    }

    // Build query
    $query = "SELECT srf.*, srffeedback.feedback AS rate 
              FROM srf 
              LEFT JOIN srffeedback ON srf.id = srffeedback.srf_id 
              WHERE srf.status = 'Completed'";

    if ($date_filter === 'this_month') {
        $query .= " AND MONTH(STR_TO_DATE(srf.date, '%Y-%m-%d')) = MONTH(CURRENT_DATE()) ";
        $date_label = "SRF Completed Requests for " . date('F Y');
    } elseif (!empty($from_date) && !empty($to_date)) {
        // Sanitize date inputs
        $from_date = date('Y-m-d', strtotime($from_date));
        $to_date = date('Y-m-d', strtotime($to_date));
    
        $query .= " AND STR_TO_DATE(srf.date, '%Y-%m-%d') BETWEEN '$from_date' AND '$to_date' ";
        $date_label = "SRF Completed Requests: " . date('M j', strtotime($from_date)) . " - " . date('M j, Y', strtotime($to_date));
    } else {
        $date_label = "SRF Completed Requests";
    }

// Add ORDER BY and LIMIT
$query .= " ORDER BY STR_TO_DATE(srf.date, '%Y-%m-%d') ASC LIMIT $show_rows ";

// Execute query
$result = $conn->query($query);

    // Initialize counter
    $counter = 1;



    ?>


            <?php while($row = mysqli_fetch_assoc($result)): ?>


                            <?php if ($counter === 1): ?>
                                <div style="margin-bottom: 10px;"><span class="badge bg-info" style="font-weight: bold;">Total Requests: <?= mysqli_num_rows($result) ?></span></div>
                            <?php endif; ?>                            <div style="margin-bottom: 10px;"><span class="badge bg-danger" style="color: red;">Request #<?= $counter++ ?></span></div>
                <iframe src="printform-request.php?id=<?= $row['id'] ?>" style="width: 100%; height: 270vh; border: none; margin-bottom: 20px;"></iframe>
                <br><br>
                </tr>


            <?php endwhile; ?>
  
