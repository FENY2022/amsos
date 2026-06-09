<?php
// Include the database connection
require_once 'connect.php';

// =========================================================================
// ACTION: EXPORT TO EXCEL - SUMMARY ONLY (UPDATED WITH SRF IDs PER ROW)
// =========================================================================
if (isset($_GET['action']) && $_GET['action'] == 'export_summary') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Equipment_Repair_Summary.csv');
    $output = fopen('php://output', 'w');
    
    // Output Column Headers
    fputcsv($output, [
        'Property No', 
        'Actual User', 
        'Equipment Type', 
        'Brand', 
        'Description', 
        'Total Times Repaired',
        'SRF Track ID' // <-- Changed to singular, as it will be one ID per row
    ]);
    
    // Main Query: Generates one row per distinct track ID
    $export_sql = "
        SELECT 
            i.propertyNumber, 
            i.actualUser, 
            i.equipmentType, 
            i.brand, 
            i.specifications,
            (SELECT COUNT(DISTINCT s_sub.trackid) FROM srfhistory s_sub WHERE s_sub.equipment_id = i.id) as repair_count,
            DISTINCT_TRACKS.trackid as srf_id
        FROM inv_inventory i
        INNER JOIN (SELECT DISTINCT equipment_id, trackid FROM srfhistory) DISTINCT_TRACKS 
            ON i.id = DISTINCT_TRACKS.equipment_id
        ORDER BY repair_count DESC, i.propertyNumber ASC, srf_id DESC
    ";
                    
    $export_res = $conn->query($export_sql);
    
    if ($export_res) {
        while ($row = $export_res->fetch_assoc()) {
            fputcsv($output, [
                $row['propertyNumber'],
                $row['actualUser'],
                $row['equipmentType'],
                $row['brand'],
                $row['specifications'],
                $row['repair_count'],
                $row['srf_id'] // <-- Output the single SRF ID for this specific row
            ]);
        }
    }
    fclose($output);
    exit;
}

// =========================================================================
// ACTION: EXPORT TO EXCEL - REMARKS ONLY 
// =========================================================================
if (isset($_GET['action']) && $_GET['action'] == 'export_remarks_only') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Repair_Remarks_History.csv');
    $output = fopen('php://output', 'w');
    
    // Output Column Headers
    fputcsv($output, [
        'Property No', 
        'SRF Track ID', 
        'Date Recorded', 
        'Time Recorded', 
        'Action Staff', 
        'Remarks / Action Taken'
    ]);
    
    // Query linking inventory (just for property number) and remarks
    $export_sql = "
        SELECT 
            i.propertyNumber, 
            DISTINCT_TRACKS.trackid,
            r.date,
            r.time,
            r.actionstaff,
            r.action_taken
        FROM inv_inventory i
        INNER JOIN (SELECT DISTINCT equipment_id, trackid FROM srfhistory) DISTINCT_TRACKS 
            ON i.id = DISTINCT_TRACKS.equipment_id
        INNER JOIN srfstaff_remarks r 
            ON DISTINCT_TRACKS.trackid = r.track_id
        ORDER BY i.propertyNumber ASC, DISTINCT_TRACKS.trackid DESC
    ";
    
    $export_res = $conn->query($export_sql);
    
    if ($export_res) {
        while ($row = $export_res->fetch_assoc()) {
            $action_taken = $row['action_taken'] ? str_replace(array("\r", "\n"), " | ", $row['action_taken']) : 'No remarks recorded';
            $staff = $row['actionstaff'] ? $row['actionstaff'] : 'N/A';
            $date = $row['date'] ? $row['date'] : 'N/A';
            $time = $row['time'] ? $row['time'] : 'N/A';
            
            fputcsv($output, [
                $row['propertyNumber'],
                $row['trackid'],
                $date,
                $time,
                $staff,
                $action_taken
            ]);
        }
    }
    fclose($output);
    exit;
}

// =========================================================================
// ACTION: EXPORT TO EXCEL - DETAILED WITH REMARKS (COMBINED)
// =========================================================================
if (isset($_GET['action']) && $_GET['action'] == 'export_remarks') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Equipment_Detailed_Remarks.csv');
    $output = fopen('php://output', 'w');
    
    // Output Column Headers
    fputcsv($output, [
        'Property No', 
        'Actual User', 
        'Equipment Type', 
        'Brand', 
        'Total Times Repaired', 
        'SRF Track ID', 
        'Date Recorded', 
        'Time Recorded', 
        'Action Staff', 
        'Remarks / Action Taken'
    ]);
    
    // Query linking inventory, routing history, and remarks
    $export_sql = "
        SELECT 
            i.propertyNumber, 
            i.actualUser, 
            i.equipmentType, 
            i.brand, 
            (SELECT COUNT(DISTINCT s_sub.trackid) FROM srfhistory s_sub WHERE s_sub.equipment_id = i.id) as repair_count,
            DISTINCT_TRACKS.trackid,
            r.date,
            r.time,
            r.actionstaff,
            r.action_taken
        FROM inv_inventory i
        INNER JOIN (SELECT DISTINCT equipment_id, trackid FROM srfhistory) DISTINCT_TRACKS 
            ON i.id = DISTINCT_TRACKS.equipment_id
        LEFT JOIN srfstaff_remarks r 
            ON DISTINCT_TRACKS.trackid = r.track_id
        ORDER BY repair_count DESC, i.propertyNumber ASC, DISTINCT_TRACKS.trackid DESC
    ";
    
    $export_res = $conn->query($export_sql);
    
    if ($export_res) {
        while ($row = $export_res->fetch_assoc()) {
            $action_taken = $row['action_taken'] ? str_replace(array("\r", "\n"), " | ", $row['action_taken']) : 'No remarks recorded';
            $staff = $row['actionstaff'] ? $row['actionstaff'] : 'N/A';
            $date = $row['date'] ? $row['date'] : 'N/A';
            $time = $row['time'] ? $row['time'] : 'N/A';
            
            fputcsv($output, [
                $row['propertyNumber'],
                $row['actualUser'],
                $row['equipmentType'],
                $row['brand'],
                $row['repair_count'],
                $row['trackid'],
                $date,
                $time,
                $staff,
                $action_taken
            ]);
        }
    }
    fclose($output);
    exit;
}

// =========================================================================
// AJAX HANDLER: Fetch repair history when the "View Remarks" button is clicked
// =========================================================================
if (isset($_GET['action']) && $_GET['action'] == 'get_history' && isset($_GET['equip_id'])) {
    header('Content-Type: application/json');
    $equip_id = intval($_GET['equip_id']);
    
    $sql_history = "SELECT 
                        track_id, 
                        action_taken,
                        date,
                        time
                    FROM srfstaff_remarks 
                    WHERE track_id IN (SELECT trackid FROM srfhistory WHERE equipment_id = $equip_id)
                    ORDER BY id DESC";
                    
    $res_history = $conn->query($sql_history);
    $history_data = [];
    
    if ($res_history) {
        while($h = $res_history->fetch_assoc()) {
            $history_data[] = $h;
        }
    }
    echo json_encode($history_data);
    exit; 
}
// =========================================================================

// SQL Query for the main HTML table
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
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        div.dataTables_wrapper div.dataTables_filter input {
            border-radius: 20px;
            padding: 5px 15px;
        }
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
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h4 class="m-0 fw-bold">
                <i class="fa-solid fa-screwdriver-wrench me-2"></i> Equipment Repair Frequency
            </h4>
            <div class="no-print">
                <div class="dropdown d-inline-block me-2">
                    <button class="btn btn-success btn-sm fw-semibold shadow-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                        <li>
                            <a class="dropdown-item" href="?action=export_summary">
                                <i class="fa-solid fa-table text-secondary me-2"></i> Equipment Summary Only
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="?action=export_remarks_only">
                                <i class="fa-solid fa-comment-dots text-secondary me-2"></i> Repair Remarks Only
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="?action=export_remarks">
                                <i class="fa-solid fa-list-check text-secondary me-2"></i> Detailed (Combined)
                            </a>
                        </li>
                    </ul>
                </div>
                
                <button class="btn btn-light btn-sm fw-semibold shadow-sm" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i> Print
                </button>
            </div>
        </div>
        
        <div class="card-body p-4">
            <div class="alert alert-light border-start border-4 border-info text-muted mb-4 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-info me-2"></i> 
                This table displays the total number of times each equipment has been repaired based on Service Request Form (SRF) tracking records.
            </div>
            
            <div class="table-responsive">
                <table id="repairTable" class="table table-hover align-middle border-top w-100">
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
                                      
                                echo "<td class='text-center no-print'>
                                        <button class='btn btn-sm btn-outline-primary view-history-btn' 
                                                data-id='{$row['id']}' 
                                                data-prop='{$row['propertyNumber']}'>
                                            <i class='fa-solid fa-comment-dots'></i> View Remarks
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

<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold" id="historyModalLabel">
            <i class="fa-solid fa-clipboard-check me-2 text-primary"></i> 
            Staff Remarks: <span id="modalPropNo" class="text-primary"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="historyContent" class="history-list">
            </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#repairTable').DataTable({
            "pageLength": 10,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search records..."
            },
            "order": [[6, "desc"]] 
        });

        table.on('draw', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Event Delegation for viewing modal history
        $('#repairTable tbody').on('click', '.view-history-btn', function() {
            let equipId = $(this).data('id');
            let propNo = $(this).data('prop');
            
            $('#modalPropNo').text(propNo);
            $('#historyContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading remarks...</p></div>');
            $('#historyModal').modal('show');
            
            $.ajax({
                url: window.location.pathname,
                type: 'GET',
                data: { action: 'get_history', equip_id: equipId },
                dataType: 'json',
                success: function(response) {
                    if(response && response.length > 0) {
                        let html = '<div class="list-group list-group-flush">';
                        
                        response.forEach(function(item) {
                            let actionTaken = item.action_taken ? item.action_taken.replace(/\n/g, '<br>') : '<em class="text-muted">No action details recorded.</em>';
                            let dateAdded = (item.date ? item.date : '') + ' ' + (item.time ? item.time : '');
                            let trackId = item.track_id;
                            
                            html += `
                                <div class="list-group-item p-3 shadow-sm">
                                    <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-list-check text-secondary me-2"></i> Remark</h6>
                                        <small class="text-muted"><i class="fa-regular fa-calendar me-1"></i> ${dateAdded}</small>
                                    </div>
                                    <div class="p-2 mb-2 bg-white border rounded">
                                        <p class="mb-0 text-dark" style="font-size: 0.95rem;">
                                            ${actionTaken}
                                        </p>
                                    </div>
                                    <small class="text-muted"><strong>SRF Track ID:</strong> ${trackId}</small>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                        $('#historyContent').html(html);
                    } else {
                        $('#historyContent').html('<div class="alert alert-warning text-center"><i class="fa-solid fa-circle-exclamation me-2"></i> Wala pay staff remarks para ani nga equipment.</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#historyContent').html('<div class="alert alert-danger"><strong>Error!</strong> Could not load data. Ensure database connection is active.</div>');
                }
            });
        });
    });
</script>

</body>
</html>

<?php
if(isset($conn) && $conn) {
    $conn->close();
}
?>