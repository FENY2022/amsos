<?php
// Include the database connection
require_once 'connect.php';

// =========================================================================
// AJAX HANDLER: Fetch repair history when the "View Comments" button is clicked
// =========================================================================
if (isset($_GET['action']) && $_GET['action'] == 'get_history' && isset($_GET['equip_id'])) {
    header('Content-Type: application/json');
    $equip_id = intval($_GET['equip_id']);
    
    // ⚠️ CHANGE THESE COLUMN NAMES to match your actual columns in `srfhistory`
    // Example: if your column is called `technician`, change `personnel` to `technician`
    $sql_history = "SELECT 
                        trackid, 
                        personnel, /* Update this if your personnel column is named differently */
                        remarks,   /* Update this if your comments column is named 'comment', 'action_taken', etc. */
                        date_added /* Update this to your actual date column */
                    FROM srfhistory 
                    WHERE equipment_id = $equip_id 
                    ORDER BY trackid DESC";
                    
    $res_history = $conn->query($sql_history);
    $history_data = [];
    
    if ($res_history) {
        while($h = $res_history->fetch_assoc()) {
            $history_data[] = $h;
        }
    }
    echo json_encode($history_data);
    exit; // Stop executing the rest of the page for AJAX requests
}
// =========================================================================

// SQL Query using INNER JOIN (Added i.id to select)
$sql = "SELECT 
            i.id,
            i.propertyNumber, 
            i.equipmentType, 
            i.brand, 
            i.specifications,
            i.actualUser, 
            COUNT(DISTINCT s.trackid) as repair_count 
        FROM inv_inventory i
        INNER JOIN srfhistory s ON i.id = s.equipment_id
        GROUP BY i.id
        ORDER BY repair_count DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Repair Frequency</title>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">

    <style>
        body { 
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5; 
            color: #333;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            background: #ffffff;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
            padding: 20px 25px;
        }
        .table > :not(caption) > * > * {
            padding: 1rem 0.75rem;
        }
        .badge-repair {
            font-size: 0.85rem;
            padding: 0.4em 0.8em;
            font-weight: 600;
        }
        .history-list .list-group-item {
            border-left: 4px solid #0d6efd;
            margin-bottom: 8px;
            border-radius: 6px !important;
            background: #f8f9fa;
        }
        /* Customizing DataTable inputs */
        div.dataTables_wrapper div.dataTables_filter input {
            border-radius: 20px;
            padding: 5px 15px;
        }
        /* Print Styles */
        @media print {
            body { background-color: #fff; }
            .card-custom { box-shadow: none; border: 1px solid #ddd; }
            .no-print { display: none !important; }
            .card-header-custom { color: black; background: transparent; border-bottom: 2px solid #000; }
        }
    </style>
</head>
<body>

<div class="container-fluid py-5 px-md-5">
    <div class="card card-custom">
        <!-- Header -->
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h4 class="m-0 fw-bold">
                <i class="fa-solid fa-screwdriver-wrench me-2"></i> Equipment Repair Frequency
            </h4>
            <button class="btn btn-light btn-sm fw-semibold shadow-sm no-print" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> Print Report
            </button>
        </div>
        
        <!-- Body -->
        <div class="card-body p-4">
            <div class="alert alert-light border-start border-4 border-info text-muted mb-4 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-info me-2"></i> 
                This table displays the total number of times each equipment has been repaired based on Service Request Form (SRF) tracking records.
            </div>
            
            <div class="table-responsive">
                <table id="repairTable" class="table table-hover align-middle border-top">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th scope="col" width="5%">#</th>
                            <th scope="col" width="15%">Property No.</th>
                            <th scope="col" width="15%">Actual User</th>
                            <th scope="col" width="12%">Equipment Type</th>
                            <th scope="col" width="13%">Brand</th>
                            <th scope="col" width="15%">Description</th>
                            <th scope="col" width="10%" class="text-center">Times Repaired</th>
                            <th scope="col" width="15%" class="text-center no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            $counter = 1;
                            while($row = $result->fetch_assoc()) {
                                $desc = htmlspecialchars($row['specifications']);
                                $short_desc = strlen($desc) > 40 ? substr($desc, 0, 40) . "..." : $desc;
                                
                                // Enhanced Badge Logic
                                $repairCount = $row['repair_count'];
                                if ($repairCount <= 1) {
                                    $badgeClass = 'bg-success';
                                } elseif ($repairCount <= 3) {
                                    $badgeClass = 'bg-warning text-dark';
                                } else {
                                    $badgeClass = 'bg-danger';
                                }

                                echo "<tr>";
                                echo "<td class='text-muted'>" . $counter++ . "</td>";
                                echo "<td class='fw-bold text-primary'>" . htmlspecialchars($row['propertyNumber']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['actualUser']) . "</td>";
                                echo "<td><span class='badge bg-light text-dark border'>" . htmlspecialchars($row['equipmentType']) . "</span></td>";
                                echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
                                
                                echo "<td>
                                        <span data-bs-toggle='tooltip' data-bs-placement='top' title='$desc' style='cursor: help; border-bottom: 1px dotted #999;'>
                                            $short_desc
                                        </span>
                                      </td>";
                                
                                echo "<td class='text-center'>
                                        <span class='badge badge-repair rounded-pill $badgeClass shadow-sm'>
                                            $repairCount <i class='fa-solid fa-tools ms-1'></i>
                                        </span>
                                      </td>";
                                      
                                // The new Action Button
                                echo "<td class='text-center no-print'>
                                        <button class='btn btn-sm btn-outline-primary view-history-btn' 
                                                data-id='{$row['id']}' 
                                                data-prop='{$row['propertyNumber']}'>
                                            <i class='fa-solid fa-comment-dots'></i> View Comments
                                        </button>
                                      </td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold" id="historyModalLabel">
            <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> 
            Repair History: <span id="modalPropNo" class="text-primary"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="historyContent" class="history-list">
            <!-- AJAX content will load here -->
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- jQuery (Required for DataTables & AJAX) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables Core & Bootstrap 5 Integration -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#repairTable').DataTable({
            "pageLength": 10,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search records..."
            },
            "order": [[6, "desc"]] // Sort by Times Repaired
        });

        // Initialize Bootstrap Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Handle Click on View Comments Button
        $('.view-history-btn').on('click', function() {
            let equipId = $(this).data('id');
            let propNo = $(this).data('prop');
            
            // Set Modal Title
            $('#modalPropNo').text(propNo);
            
            // Show loading spinner
            $('#historyContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading comments...</p></div>');
            
            // Open Modal
            $('#historyModal').modal('show');
            
            // Fetch Data via AJAX
            $.ajax({
                url: window.location.pathname,
                type: 'GET',
                data: { action: 'get_history', equip_id: equipId },
                dataType: 'json',
                success: function(response) {
                    if(response && response.length > 0) {
                        let html = '<div class="list-group list-group-flush">';
                        
                        response.forEach(function(item) {
                            // Format the content. 
                            // Make sure these match the keys in your PHP SELECT query at the top of the file!
                            let personnel = item.personnel ? item.personnel : 'Unknown Personnel';
                            let comments = item.remarks ? item.remarks : '<em class="text-muted">No comment recorded.</em>';
                            let dateAdded = item.date_added ? item.date_added : '';
                            
                            html += `
                                <div class="list-group-item p-3 shadow-sm">
                                    <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-user-gear text-secondary me-2"></i> ${personnel}</h6>
                                        <small class="text-muted"><i class="fa-regular fa-calendar me-1"></i> ${dateAdded}</small>
                                    </div>
                                    <p class="mb-1 text-dark" style="font-size: 0.95rem;">
                                        <i class="fa-solid fa-quote-left text-primary opacity-50 me-2"></i> ${comments}
                                    </p>
                                    <small class="text-muted">SRF Track ID: ${item.trackid}</small>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                        $('#historyContent').html(html);
                    } else {
                        $('#historyContent').html('<div class="alert alert-warning text-center"><i class="fa-solid fa-circle-exclamation me-2"></i> Wala pay comments or history ani nga equipment.</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#historyContent').html('<div class="alert alert-danger"><strong>Error!</strong> Please check your database column names at the top of the PHP file. They might not match your database exactly.</div>');
                }
            });
        });
    });
</script>

</body>
</html>

<?php
// Close the database connection safely
if(isset($conn) && $conn) {
    $conn->close();
}
?>