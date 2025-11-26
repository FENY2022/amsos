<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request List</title>

    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --info-color: #0dcaf0; /* Added for consistency */
            --warning-color: #ffc107; /* Added for consistency */
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --body-bg: #f4f7f6;
            --card-bg: #ffffff;
            --font-family: 'Poppins', sans-serif;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--body-bg);
            color: var(--dark-color);
            line-height: 1.5; /* Slightly reduced line height for compression */
        }

        .container-fluid { /* Use container-fluid for full width, or container for fixed width */
            padding-top: 40px;
            padding-bottom: 40px;
        }

        .page-header {
            margin-bottom: 2rem;
            color: var(--dark-color);
            text-align: center;
            font-weight: 700;
        }
        
        .page-header i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .card {
            border: none;
            border-radius: 0.75rem; /* Rounded corners for cards */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Softer, deeper shadow */
            margin-bottom: 2rem;
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
        }
        .card-header i {
            margin-right: 10px;
        }

        .card-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--dark-color);
        }

        .form-control, .form-select {
            border-radius: 0.5rem; /* Rounded input fields */
            padding: 0.75rem 1rem;
            border-color: #e0e0e0;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .btn {
            border-radius: 0.5rem; /* Rounded buttons */
            padding: 0.6rem 1rem; /* Slightly reduced padding */
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9rem; /* Slightly smaller font for buttons */
        }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
        .btn-primary:hover { background-color: #0a58ca; border-color: #0a58ca; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2); }
        .btn-success { background-color: var(--success-color); border-color: var(--success-color); }
        .btn-success:hover { background-color: #157347; border-color: #157347; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(25, 135, 84, 0.2); }
        .btn-danger { background-color: var(--danger-color); border-color: var(--danger-color); }
        .btn-danger:hover { background-color: #bb2d3b; border-color: #bb2d3b; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2); }
        .btn-info { background-color: var(--info-color); border-color: var(--info-color); }
        .btn-info:hover { background-color: #0a738c; border-color: #0a738c; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(13, 188, 253, 0.2); }
        .btn-secondary { background-color: var(--secondary-color); border-color: var(--secondary-color); }
        .btn-secondary:hover { background-color: #565e64; border-color: #565e64; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(108, 117, 125, 0.2); }
        .btn-warning { background-color: var(--warning-color); border-color: var(--warning-color); color: var(--dark-color); }
        .btn-warning:hover { background-color: #ffca2c; border-color: #ffca2c; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(255, 193, 7, 0.2); }

        .table-responsive {
            margin-top: 1.5rem;
            overflow-x: auto;
            box-shadow: none;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 4px; /* Reduced vertical spacing between rows */
            margin-bottom: 0;
            width: 100%;
        }
        
        .table th, .table td {
            vertical-align: middle;
            padding: 0.7rem 1rem; /* Reduced padding for more compact rows */
            border: none;
            font-size: 0.9rem; /* Slightly smaller font for table content */
        }
        .table th:first-child, .table td:first-child {
            padding-left: 1.25rem; /* Ensure left alignment for first column */
        }
        .table th:last-child, .table td:last-child {
            padding-right: 1.25rem; /* Ensure right alignment for last column */
        }


        .table thead th {
            background-color: var(--light-color);
            color: var(--dark-color);
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table tbody tr {
            background-color: var(--card-bg);
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        /* Specific styling for the collapsible details row */
        .collapse-row {
            background-color: #fcfcfc !important;
            box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.05);
            border-top: 2px solid var(--primary-color);
            border-radius: 0.75rem;
            margin-top: -4px; /* Adjusted margin-top to match new border-spacing */
            padding: 1rem; /* Reduced padding here as well */
            display: none;
        }
        .collapse-row td {
            padding: 1rem; /* Consistent with parent */
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Adjusted minmax for compactness */
            gap: 0.8rem; /* Slightly reduced gap */
            margin-bottom: 1.2rem; /* Reduced margin */
        }
        .details-grid strong {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.95rem; /* Slightly smaller font for labels */
        }
        .details-grid div {
            padding: 0.3rem 0; /* Reduced padding */
            font-size: 0.85rem; /* Smaller font for content */
        }

        /* Expand/Collapse Button */
        .collapse-btn {
            background: none;
            border: 1px solid #ccc;
            color: var(--secondary-color);
            width: 28px; /* Slightly smaller button */
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 0.85rem; /* Adjusted icon size */
        }
        .collapse-btn:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        .collapse-btn i {
            font-size: 0.85em; /* Adjusted icon size */
        }
        
        #record-count-label {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem; /* Reduced padding */
            background-color: var(--light-color);
            border-radius: 0.75rem;
            font-weight: 500;
            margin-bottom: 1.2rem; /* Reduced margin */
            color: var(--dark-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            font-size: 0.95rem; /* Slightly smaller font */
        }
        #record-count-label i {
            margin-right: 6px; /* Reduced margin */
            color: var(--primary-color);
        }

        /* Status Badges */
        .badge {
            font-size: 0.75rem; /* Smaller badge font */
            padding: 0.3em 0.6em; /* Reduced badge padding */
            border-radius: 0.3rem; /* Slightly smaller border-radius for badges */
        }


        /* Responsive styling for mobile view */
        @media (max-width: 991.98px) { /* Changed breakpoint to lg for more columns */
            .table thead {
                display: none;
            }

            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            .table tr {
                margin-bottom: 0.8rem; /* Slightly reduced margin between mobile rows */
                border: 1px solid #e9ecef;
                border-radius: 0.75rem;
                overflow: hidden;
            }
            .table td {
                border: none;
                position: relative;
                padding-left: 45%; /* Adjusted padding for labels to prevent overlap */
                text-align: right;
                font-size: 0.85rem; /* Smaller font for mobile cells */
                padding-top: 0.5rem; /* Reduced padding for mobile cells */
                padding-bottom: 0.5rem;
            }
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 1rem; /* Adjusted left position for label */
                font-weight: 600;
                text-align: left;
                width: calc(45% - 1rem); /* Adjusted width for label */
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                color: var(--primary-color);
            }
            .table td:last-child {
                border-bottom: none;
            }

            /* Responsive adjustments for expanded row */
            .collapse-row td {
                padding: 0.8rem; /* Further reduced padding for mobile expanded row */
            }
            .details-grid {
                grid-template-columns: 1fr; /* Stack items vertically on small screens */
            }
        }

        .dropdown-menu {
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .dropdown-item {
            padding: 0.6rem 1rem; /* Reduced padding for dropdown items */
            font-weight: 500;
            font-size: 0.85rem; /* Smaller font for dropdown items */
        }
        .dropdown-item:hover {
            background-color: var(--light-color);
        }
        /* Specific dropdown item colors - ensure text is visible */
        .dropdown-item.bg-info, .dropdown-item.bg-danger, .dropdown-item.bg-success, .dropdown-item.bg-primary, .dropdown-item.bg-warning, .dropdown-item.bg-dark {
            color: white !important;
            margin: 0.15rem 0.3rem; /* Reduced margin */
            border-radius: 0.3rem;
        }
        .dropdown-item.bg-warning {
            color: var(--dark-color) !important;
        }
        .dropdown-item i {
            margin-right: 0.4rem; /* Reduced margin */
        }


        /* Modal styling */
        .modal-content {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .modal-header {
            border-bottom: none;
            padding: 1.2rem 1.5rem; /* Slightly reduced padding */
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        .modal-title {
            font-weight: 700;
            font-size: 1.15rem; /* Slightly smaller title font */
        }
        .modal-header .btn-close {
            filter: invert(1);
            opacity: 1;
        }
        .modal-body {
            padding: 1.5rem 1.8rem; /* Slightly reduced padding */
            font-size: 0.95rem; /* Consistent body font size */
        }
        .modal-footer {
            border-top: none;
            padding: 0.8rem 1.8rem 1.2rem; /* Reduced padding */
            background-color: var(--light-color);
        }

        /* Chat modal specific styles */
        .chat-container {
            max-height: 250px; /* Reduced max height */
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            padding: 10px; /* Reduced padding */
            margin-bottom: 15px;
            background-color: #fcfcfc;
            display: flex;
            flex-direction: column;
            font-size: 0.9rem; /* Smaller font for chat */
        }
        .chat-container .message {
            margin-bottom: 8px; /* Reduced margin */
            padding: 6px 10px; /* Reduced padding */
            border-radius: 0.75rem;
            max-width: 85%; /* Slightly wider messages */
        }
        .chat-container .message small {
            font-size: 0.75rem; /* Smaller timestamp */
        }
        .chat-container hr {
            margin: 3px 0; /* Reduced margin */
            border-color: #eee;
        }

        /* Rating modal specific styles */
        .form-check {
            padding: 0.4rem 0; /* Reduced padding */
        }
        .form-check-label {
            font-size: 1rem; /* Adjusted font size */
        }
        .form-check-label i {
            margin-right: 0.4rem; /* Reduced margin */
            font-size: 1.1em; /* Adjusted icon size */
        }

        /* Iframe styling for print/view modals */
        .modal-dialog.modal-xl iframe {
            border-radius: 0.75rem;
            background-color: #f9f9f9;
        }

    </style>
</head>
<body>

<?php
// Database configuration
require_once 'connect.php';
require_once 'session_checker.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>

<div class="container-fluid">
    <h2 class="page-header"><i class="fas fa-list-alt"></i> Request List</h2>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filter Requests
        </div>
        <div class="card-body">
            <form method="GET" action="fetchdateSRFT.php" onsubmit="return validateDates();">
                <div class="row align-items-end">
                    <div class="col-md-5 mb-3">
                        <label for="start_date" class="form-label">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>" required>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="end_date" class="form-label">End Date:</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>" required>
                    </div>
                    <div class="col-md-2 d-grid mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php
            // Initialize $Station - Assuming $_SESSION['StationSRF'] is set by session_checker.php
            $Station = isset($_SESSION['StationSRF']) ? $_SESSION['StationSRF'] : 'default_station'; // Fallback for testing

            $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
            $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

            if (empty($startDate) || empty($endDate)) {
                $startDate = (new DateTime())->modify('-30 days')->format('Y-m-d'); // Default to last 30 days
                $endDate = (new DateTime())->format('Y-m-d'); // Current date
            }

            // Prepare and execute the query to fetch SRF data
            $sql = "SELECT * FROM srf WHERE station = ? AND date BETWEEN ? AND ?";
            $stmt = $conn->prepare($sql);
            
            // Bind the parameters
            $stmt->bind_param("sss", $Station, $startDate, $endDate);
            $stmt->execute();
            
            // Fetch the result
            $result = $stmt->get_result();

            $total_records = $result->num_rows; // Get total records before fetching them all
            echo '<div id="record-count-label"><i class="fas fa-list-ol"></i> Found <strong>'. $total_records . '</strong> Records</div>';
            ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ticket Number</th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Request Type</th>
                            <th>Status</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($total_records > 0) {
                            // Reset result pointer if already fetched
                            $result->data_seek(0);
                            while($row = $result->fetch_assoc()) {
                                $srfId = htmlspecialchars($row['id']);
                                $name = htmlspecialchars($row['name']);
                                $remarks = htmlspecialchars($row['Notification_remarks']);
                                $documents = htmlspecialchars($row['documents']);
                        ?>
                                <tr>
                                    <td data-label="ID"><strong><?php echo $srfId; ?></strong></td>
                                    <td data-label="Ticket Number"><?php echo htmlspecialchars($row['ticketNumber']); ?></td>
                                    <td data-label="Date"><?php echo date('F j, Y', strtotime($row['date'])); ?></td>
                                    <td data-label="Name"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td data-label="Request Type"><?php echo htmlspecialchars($row['requestType']); ?></td>
                                    <td data-label="Status">
                                        <?php 
                                            $status = htmlspecialchars($row['status']);
                                            $badge_class = 'bg-secondary';
                                            if ($status === 'Pending') {
                                                $badge_class = 'bg-warning text-dark';
                                            } elseif ($status === 'Approved') {
                                                $badge_class = 'bg-success';
                                            } elseif ($status === 'Disapproved') {
                                                $badge_class = 'bg-danger';
                                            } elseif ($status === 'Completed') { // Assuming 'Completed' is a final status
                                                $badge_class = 'bg-primary';
                                            }
                                            echo "<span class='badge {$badge_class}'>{$status}</span>";
                                        ?>
                                    </td>
                                    <td data-label="Action">
                                        <div class="dropdown">
                                            <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" id="dropdownMenuButton<?php echo $srfId; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-h"></i> Action
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $srfId; ?>">
                                                <?php if ($row['Notification_read'] == '1') { ?>
                                                    <li><a class="dropdown-item bg-danger text-white" href="#" data-bs-toggle="modal" data-bs-target="#readnotificationchat<?php echo $srfId; ?>"><i class="fas fa-comment-dots"></i> Read Chat**</a></li>
                                                <?php } else { ?>
                                                    <?php if ($row['tracking'] === '102') { // Mark as Done flow ?>
                                                        <li><a class="dropdown-item bg-success text-white" href="#" data-bs-toggle="modal" data-bs-target="#rate<?php echo $srfId; ?>"><i class="fas fa-star"></i> Rate***</a></li>
                                                    <?php } elseif ($row['tracking'] === '103') { // Completed/Approved flow - Keep minimal here, more in details ?>
                                                        <li><a class="dropdown-item bg-info text-white" href="#" data-bs-toggle="modal" data-bs-target="#readnotificationchat<?php echo $srfId; ?>"><i class="fas fa-comments"></i> Chat**</a></li>
                                                    <?php } else { // Default flow for active requests ?>
                                                        <!-- <li><a class="dropdown-item bg-success text-white" href="#" data-bs-toggle="modal" data-bs-target="#approve<?php echo $srfId; ?>"><i class="fas fa-check-circle"></i> Approve</a></li> -->
                                                        <!-- <li><a class="dropdown-item bg-danger text-white" href="#" data-bs-toggle="modal" data-bs-target="#disapproved<?php echo $srfId; ?>"><i class="fas fa-times-circle"></i> Disapprove</a></li> -->
                                                        <!-- <li><a class="dropdown-item bg-info text-white" href="#" data-bs-toggle="modal" data-bs-target="#assign<?php echo $srfId; ?>"><i class="fas fa-user-plus"></i> Assign</a></li> -->
                                                        <li><a class="dropdown-item bg-dark text-white" href="#" data-bs-toggle="modal" data-bs-target="#readnotificationchat<?php echo $srfId; ?>"><i class="fas fa-comments"></i> Chat**</a></li>
                                                    <?php } ?>
                                                    <?php if ($row['Notification_read'] == '0' && !empty($remarks)) { // For unread remarks notification ?>
                                                        <li><a class="dropdown-item bg-primary text-white" href="#" data-bs-toggle="modal" data-bs-target="#readnotification<?php echo $srfId; ?>"><i class="fas fa-eye"></i> View Notification</a></li>
                                                    <?php } ?>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </td>
                                    <td data-label="Details">
                                        <button class='collapse-btn' onclick='toggleRow(this)'><i class="fas fa-plus"></i></button>
                                    </td>
                                </tr>
                                <tr class='collapse-row' style='display: none;' data-srfid="<?php echo $srfId; ?>" data-equipmentid="<?php echo htmlspecialchars($row['equipment_id']); ?>">
                                    <td colspan='8'> <div class="details-grid">
                                            <div><strong>Division/Sec/Unit:</strong><br><?php echo htmlspecialchars($row['divSecUnit']); ?></div>
                                            <div><strong>Office:</strong><br><?php echo htmlspecialchars($row['office']); ?></div>
                                            <div><strong>Position:</strong><br><?php echo htmlspecialchars($row['position']); ?></div>
                                            <div><strong>Contact Number:</strong><br><?php echo htmlspecialchars($row['contactNumber']); ?></div>
                                            <div><strong>Other Specify:</strong><br><?php echo !empty($row['otherSpecify']) ? htmlspecialchars($row['otherSpecify']) : 'N/A'; ?></div>
                                            <div><strong>Description:</strong><br><?php echo !empty($row['description']) ? htmlspecialchars($row['description']) : 'N/A'; ?></div>
                                            <div><strong>Remarks:</strong><br><?php echo !empty($row['remarks']) ? nl2br(htmlspecialchars($row['remarks'])) : 'N/A'; ?></div>
                                        </div>
                                        <hr>
                                        <strong>Additional Actions:</strong><br>
                                        <div class='mt-2 d-flex flex-wrap gap-2'>
                                            <button class='btn btn-sm btn-outline-info' data-bs-toggle='modal' data-bs-target='#read<?php echo $srfId; ?>'><i class="fas fa-info-circle"></i> View Full Details</button>
                                            <button class='btn btn-sm btn-outline-primary' data-bs-toggle='modal' data-bs-target='#edituploadeddocuments<?php echo $srfId; ?>'><i class="fas fa-file-alt"></i> Documents</button>
                                            <a class='btn btn-sm btn-outline-secondary' target='_blank' href='mainmenu.php?dir=printform&id=<?php echo $srfId; ?>'><i class="fas fa-print"></i> Print Form</a>
                                            <button class='btn btn-sm btn-outline-secondary' data-bs-toggle='modal' data-bs-target='#viewupload<?php echo $srfId; ?>'><i class="fas fa-eye"></i> View Uploaded</button>
                                            <button class='btn btn-sm btn-outline-dark' data-bs-toggle='modal' data-bs-target='#history<?php echo $srfId; ?>'><i class="fas fa-history"></i> History</button>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="edituploadeddocuments<?php echo $srfId; ?>" tabindex="-1" aria-labelledby="edituploadeddocumentsLabel<?php echo $srfId; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-info">
                                                <h5 class="modal-title" id="edituploadeddocumentsLabel<?php echo $srfId; ?>"><i class="fas fa-file-upload"></i> Edit Uploaded Documents</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                            </div>
                                            <div class="modal-body">
                                                <?php if (!empty($documents)) {
                                                    $docArray = explode(',', $documents); ?>
                                                    <div class="mb-3">
                                                        <label class="form-label">Existing Documents</label>
                                                        <ul class="list-group list-group-flush rounded-3 border">
                                                            <?php foreach ($docArray as $doc) { ?>
                                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                    <a href="attached_documents/<?php echo htmlspecialchars($doc); ?>" target="_blank" class="text-decoration-none text-primary">
                                                                        <i class="fas fa-paperclip me-2"></i><?php echo htmlspecialchars($doc); ?>
                                                                    </a>
                                                                    </li>
                                                            <?php } ?>
                                                        </ul>
                                                    </div>
                                                <?php } else { ?>
                                                    <p class="text-muted">No documents uploaded.</p>
                                                <?php } ?>

                                                <form id="uploadForm<?php echo $srfId; ?>" action="upload.php" method="post" enctype="multipart/form-data">
                                                    <div class="mb-3">
                                                        <label for="documentName<?php echo $srfId; ?>" class="form-label">Document Name</label>
                                                        <input type="text" class="form-control" id="documentName<?php echo $srfId; ?>" name="documentName" placeholder="Enter document name" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="documentFile<?php echo $srfId; ?>" class="form-label">Upload File</label>
                                                        <input type="file" class="form-control" id="documentFile<?php echo $srfId; ?>" name="documentFile" required>
                                                    </div>
                                                    <input type="hidden" name="srfId" value="<?php echo $srfId; ?>">
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="button" class="btn btn-primary" onclick="submitUploadForm(<?php echo $srfId; ?>)"><i class="fas fa-save"></i> Save changes</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="readnotificationchat<?php echo $srfId; ?>" tabindex="-1" aria-labelledby="notifyLabel<?php echo $srfId; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger">
                                                <h5 class="modal-title" id="notifyLabel<?php echo $srfId; ?>"><i class="fas fa-comments"></i> Chat with User #<?php echo $srfId; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="chat-container" id="chatContainer<?php echo $srfId; ?>">
                                                    <p class="text-center text-muted">Loading messages...</p>
                                                </div>
                                                <form id="messageForm<?php echo $srfId; ?>" method="POST">
                                                    <div class="mb-3">
                                                        <label for="message<?php echo $srfId; ?>" class="form-label">Your Message</label>
                                                        <textarea class="form-control" id="message<?php echo $srfId; ?>" name="message" rows="3" placeholder="Type your message here..." required></textarea>
                                                    </div>
                                                    <input type="hidden" name="srfId" value="<?php echo $srfId; ?>">
                                                    <input type="hidden" name="sender" value="<?php echo htmlspecialchars($name); ?>"> <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Message</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="rate<?php echo $srfId; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="rate.php">
                                            <div class="modal-content">
                                                <div class="modal-header bg-secondary">
                                                    <h5 class="modal-title text-white"><i class="fas fa-star-half-alt"></i> Rate Service</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">FEEDBACK RATING:</label>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="feedback" value="Excellent" id="excellent<?php echo $srfId; ?>" required>
                                                            <label class="form-check-label" for="excellent<?php echo $srfId; ?>"><i class="fas fa-smile text-success"></i> Excellent</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="feedback" value="Very Satisfactory" id="verysat<?php echo $srfId; ?>" required>
                                                            <label class="form-check-label" for="verysat<?php echo $srfId; ?>"><i class="fas fa-laugh text-primary"></i> Very Satisfactory</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="feedback" value="Satisfactory" id="sat<?php echo $srfId; ?>" required>
                                                            <label class="form-check-label" for="sat<?php echo $srfId; ?>"><i class="fas fa-meh text-warning"></i> Satisfactory</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="feedback" value="Below Satisfactory" id="belowSat<?php echo $srfId; ?>" required>
                                                            <label class="form-check-label" for="belowSat<?php echo $srfId; ?>"><i class="fas fa-frown text-danger"></i> Below Satisfactory</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="feedback" value="Poor" id="poor<?php echo $srfId; ?>" required>
                                                            <label class="form-check-label" for="poor<?php echo $srfId; ?>"><i class="fas fa-sad-tear text-dark"></i> Poor</label>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="acknowledgedBy<?php echo $srfId; ?>" class="form-label">Acknowledged by:</label>
                                                        <input type="text" class="form-control" value="<?php echo $name; ?>" name="acknowledged_by" id="acknowledgedBy<?php echo $srfId; ?>" placeholder="Enter name" required>
                                                    </div>
                                                    <input type="hidden" name="srf_id" value="<?php echo $srfId; ?>">
                                                    <div class="table-responsive">
                                                        <div id="table-content1-<?php echo $srfId; ?>">
                                                            <p class="text-muted text-center">Loading history...</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Rating</button>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="modal fade" id="readnotification<?php echo $srfId; ?>" tabindex="-1" aria-labelledby="modalLabel<?php echo $srfId; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary">
                                                <h5 class="modal-title" id="modalLabel<?php echo $srfId; ?>"><i class="fas fa-bell"></i> Notification Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Remarks:</strong></p>
                                                <p><?php echo nl2br($remarks); ?></p>
                                            </div>
                                            <div class="modal-footer">
                                                <form method="POST" action="update_notification.php">
                                                    <input type="hidden" name="srf_id" value="<?php echo $srfId; ?>">
                                                    <button type="submit" class="btn btn-primary">Ok</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="history<?php echo $srfId; ?>" data-equipmentid="<?php echo htmlspecialchars($row['equipment_id']); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-secondary">
                                                <h5 class="modal-title text-white"><i class="fas fa-history"></i> Request History (ID: <?php echo $srfId; ?>)</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    <div id="table-content-<?php echo $srfId; ?>">
                                                        <p class="text-center text-muted">Loading history...</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="disapproved<?php echo $srfId; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="disapproved.php">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger">
                                                    <h5 class="modal-title text-white"><i class="fas fa-times-circle"></i> Disapprove Request</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-3">Are you sure you want to Disapprove this record (ID: <strong><?php echo $srfId; ?></strong>)?</p>
                                                    <input type="hidden" name="disapproved" value="<?php echo $srfId; ?>">
                                                    <div class="mb-3">
                                                        <label for="remarks_disapprove_<?php echo $srfId; ?>" class="form-label">Remarks</label>
                                                        <textarea class="form-control" id="remarks_disapprove_<?php echo $srfId; ?>" name="remarks" rows="3" required placeholder="Enter reason for disapproval"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-danger"><i class="fas fa-ban"></i> Disapprove</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="modal fade" id="print<?php echo $srfId; ?>" tabindex="-1" aria-labelledby="printModalLabel<?php echo $srfId; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-xl"> <div class="modal-content">
                                            <div class="modal-header bg-info">
                                                <h5 class="modal-title" id="printModalLabel<?php echo $srfId; ?>"><i class="fas fa-file-pdf"></i> View Document (ID: <?php echo $srfId; ?>)</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                            </div>
                                            <div class="modal-body p-0"> <iframe src="printform.php?id=<?php echo $srfId; ?>" style="width: 100%; height: 85vh; border: none; border-radius: 1rem;"></iframe>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="viewupload<?php echo $srfId; ?>" tabindex="-1" aria-labelledby="viewUploadModalLabel<?php echo $srfId; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-xl"> <div class="modal-content">
                                            <div class="modal-header bg-info">
                                                <h5 class="modal-title" id="viewUploadModalLabel<?php echo $srfId; ?>"><i class="fas fa-eye"></i> View Uploaded Documents (ID: <?php echo $srfId; ?>)</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                            </div>
                                            <div class="modal-body p-0">
                                                <iframe src="viewuploaded.php?id=<?php echo $srfId; ?>" style="width: 100%; height: 85vh; border: none; border-radius: 1rem;"></iframe>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="approve<?php echo $srfId; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="GET" action="approve.php">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success">
                                                    <h5 class="modal-title text-white"><i class="fas fa-check-circle"></i> Approve Request</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-3">Are you sure you want to approve this record (ID: <strong><?php echo $srfId; ?></strong>)?</p>
                                                    <input type="hidden" name="approve" value="<?php echo $srfId; ?>">
                                                    <input type="hidden" name="level" value="<?php echo htmlspecialchars($row['level']); ?>">
                                                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($row['name']); ?>">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approve</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="modal fade" id="assign<?php echo $srfId; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="GET" action="assign.php">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info">
                                                    <h5 class="modal-title text-white"><i class="fas fa-user-plus"></i> Assign Action to Personnel</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="assignId<?php echo $srfId; ?>" class="form-label">Request ID</label>
                                                        <input type="text" name="assign" value="<?php echo $srfId; ?>" class="form-control" id="assignId<?php echo $srfId; ?>" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="personnel<?php echo $srfId; ?>" class="form-label">Personnel</label>
                                                        <select id="personnel<?php echo $srfId; ?>" name="personelid" class="form-select" required>
                                                            <option disabled selected value="">Select Personnel</option>
                                                            <?php
                                                            // Re-fetch personnel data inside the loop for dynamic modal content
                                                            $sql_personnel = "SELECT DISTINCT personelid, name FROM srfactionstaff";
                                                            $result_personnel = $conn->query($sql_personnel);
                                                            if ($result_personnel->num_rows > 0) {
                                                                while ($p_row = $result_personnel->fetch_assoc()) {
                                                                    $selected_personnel = (isset($row['assigned_personelid']) && $p_row['personelid'] == $row['assigned_personelid']) ? 'selected' : ''; // Assuming 'assigned_personelid' exists in srf table
                                                                    echo "<option value='" . htmlspecialchars($p_row['personelid']) . "' $selected_personnel>" . strtoupper(htmlspecialchars($p_row['name'])) . "</option>";
                                                                }
                                                            }
                                                            ?>
                                                            <option value="102">MARK AS DONE</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="actionDate<?php echo $srfId; ?>" class="form-label">Date</label>
                                                        <input type="date" class="form-control" id="actionDate<?php echo $srfId; ?>" name="actionDate" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="actionTime<?php echo $srfId; ?>" class="form-label">Time</label>
                                                        <input type="time" class="form-control" id="actionTime<?php echo $srfId; ?>" name="actionTime" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="actionTaken<?php echo $srfId; ?>" class="form-label">Action Taken</label>
                                                        <textarea class="form-control" id="actionTaken<?php echo $srfId; ?>" name="actionTaken" rows="3" placeholder="Describe action taken..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary"><i class="fas fa-share-square"></i> Assign</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="modal fade" id="read<?php echo $srfId; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="updateBorrower.php">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info">
                                                    <h5 class="modal-title text-white"><i class="fas fa-info-circle"></i> Request Details (ID: <?php echo $srfId; ?>)</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Name</label>
                                                        <input type="text" value="<?php echo htmlspecialchars($row['name']); ?>" class="form-control" readonly>
                                                        <input type="hidden" name="borrower_id" value="<?php echo $srfId; ?>"/>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Ticket Number</label>
                                                        <input type="text" value="<?php echo htmlspecialchars($row['ticketNumber']); ?>" class="form-control" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Date</label>
                                                        <input type="text" value="<?php echo date('F j, Y', strtotime($row['date'])); ?>" class="form-control" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Division/Sec/Unit</label>
                                                        <input type="text" value="<?php echo htmlspecialchars($row['divSecUnit']); ?>" class="form-control" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Office</label>
                                                        <input type="text" value="<?php echo htmlspecialchars($row['office']); ?>" class="form-control" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Position</label>
                                                        <input type="text" value="<?php echo htmlspecialchars($row['position']); ?>" class="form-control" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Contact Number</label>
                                                        <input type="text" value="<?php echo htmlspecialchars($row['contactNumber']); ?>" class="form-control" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Request Type</label>
                                                        <input type="text" value="<?php echo htmlspecialchars($row['requestType']); ?>" class="form-control" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Other Specify</label>
                                                        <input type="text" value="<?php echo htmlspecialchars($row['otherSpecify']); ?>" class="form-control" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <textarea class="form-control" rows="3" readonly><?php echo htmlspecialchars($row['description']); ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Status</label>
                                                        <input type="text" value="<?php echo htmlspecialchars($row['status']); ?>" class="form-control" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Remarks</label>
                                                        <textarea class="form-control" rows="3" readonly><?php echo htmlspecialchars($row['remarks']); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                        <?php
                            } // End while loop
                        } else {
                            echo "<tr><td colspan='8' class='text-center py-4 text-muted'><i class='fas fa-exclamation-circle me-2'></i> No records found for the selected criteria.</td></tr>";
                        }

                        // Close the statement
                        $stmt->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// JavaScript for date validation
function validateDates() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;

    if (new Date(startDate) > new Date(endDate)) {
        // Using a custom message box instead of alert, but for a quick form validation, this is common.
        // For a full app, you'd show a Bootstrap alert/toast here.
        alert('Start Date cannot be after End Date!');
        return false;
    }
    return true;
}

// JavaScript for Toggling Details Row
function toggleRow(button) {
    const icon = button.querySelector('i');
    const mainRow = button.closest("tr");
    const collapseRow = mainRow.nextElementSibling; // The row directly after the main row

    if (collapseRow && collapseRow.classList.contains('collapse-row')) {
        if (collapseRow.style.display === "none" || collapseRow.style.display === "") {
            collapseRow.style.display = "table-row";
            icon.classList.remove('fa-plus');
            icon.classList.add('fa-minus');
        } else {
            collapseRow.style.display = "none";
            icon.classList.remove('fa-minus');
            icon.classList.add('fa-plus');
        }
    }
}


// JavaScript for Fetching History Modal Content
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('show.bs.modal', function(event) {
            // Only proceed for history modals
            if (modal.id.startsWith('history')) {
                var srfId = modal.id.replace('history', '');
                // Correctly get equipmentId from the collapse-row's data-equipmentid
                // Find the specific collapse-row related to this srfId
                const collapseRowElement = document.querySelector(`tr.collapse-row[data-srfid="${srfId}"]`);
                var equipmentId = collapseRowElement ? collapseRowElement.getAttribute('data-equipmentid') : null;

                var tableContentDiv = document.querySelector('#table-content-' + srfId);

                // Display loading message
                tableContentDiv.innerHTML = '<p class="text-center text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading history...</p>';

                fetch('fetch_table_historymodal.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        id: srfId,
                        equipment_id: equipmentId 
                    })
                })
                .then(response => response.text())
                .then(data => {
                    tableContentDiv.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error loading history:', error);
                    tableContentDiv.innerHTML = '<p class="text-center text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading data.</p>';
                });
            }
            // For chat modals
            if (modal.id.startsWith('readnotificationchat')) {
                var srfId = modal.id.replace('readnotificationchat', '');
                const chatContainer = document.getElementById('chatContainer' + srfId);
                const messageForm = document.getElementById('messageForm' + srfId);

                // Function to fetch messages
                function fetchMessages() {
                    chatContainer.innerHTML = '<p class="text-center text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading messages...</p>';
                    fetch('getMessagesUser.php?srfId=' + srfId)
                        .then(response => response.json())
                        .then(data => {
                            chatContainer.innerHTML = '';
                            if (data.length > 0) {
                                data.forEach(msg => {
                                    // Determine if it's the current user's message (adjust logic if 'sender' is not reliable)
                                    // You need to replace "<?php echo htmlspecialchars($name); ?>" with the actual logged-in user's name
                                    const senderIsCurrentUser = (msg.sender === "<?php echo htmlspecialchars(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Unknown'); ?>"); // Example: match by PHP session user name
                                    chatContainer.innerHTML += `
                                        <div class='message ${senderIsCurrentUser ? 'user-message' : 'other-message'}'>
                                            <strong>${msg.sender}:</strong> ${msg.message}<br>
                                            <small class='text-muted'>${msg.created_at}</small>
                                        </div>`;
                                });
                            } else {
                                chatContainer.innerHTML = '<p class="text-center text-muted">No messages yet. Start the conversation!</p>';
                            }
                            scrollToBottom(chatContainer); // Scroll to the bottom after loading messages
                        })
                        .catch(error => {
                            console.error('Error fetching messages:', error);
                            chatContainer.innerHTML = '<p class="text-center text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading messages.</p>';
                        });
                }

                // Function to scroll to the bottom of the chat container
                function scrollToBottom(element) {
                    element.scrollTop = element.scrollHeight;
                }

                // Load messages when the modal is opened
                fetchMessages();

                // Submit message form
                messageForm.onsubmit = function (e) {
                    e.preventDefault();
                    const formData = new FormData(messageForm);

                    fetch('sendMessage.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        console.log('Message sent response:', data); // Log the server response
                        messageForm.reset();
                        fetchMessages(); // Refresh messages after sending
                    })
                    .catch(error => {
                        console.error('Error sending message:', error);
                        alert('An error occurred while sending the message.'); // Fallback alert
                    });
                };
            }
        });
    });
});

// JavaScript for Upload Form Submission
function submitUploadForm(srfId) {
    var form = document.getElementById('uploadForm' + srfId);
    var formData = new FormData(form);

    fetch('upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log('Upload response:', data); // Log the response
        var modalElement = document.getElementById('edituploadeddocuments' + srfId);
        var modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
        location.reload(); // Reload the page to show updated documents
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while uploading the file.'); 
    });
}

// Reload page on modal close (general for all modals)
document.addEventListener('DOMContentLoaded', function () {
    var modals = document.querySelectorAll('.modal');
    modals.forEach(function (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            // Only reload if data might have changed.
            // For example, after submitting a form inside the modal, or actions like approve/disapprove.
            // For modals that just display info (like 'print', 'viewupload', 'read'), reloading might not be necessary.
            if (modal.id.startsWith('edituploadeddocuments') || modal.id.startsWith('rate') || modal.id.startsWith('disapproved') || modal.id.startsWith('approve') || modal.id.startsWith('assign') || modal.id.startsWith('readnotification') || modal.id.startsWith('readnotificationchat')) {
                location.reload();
            }
        });
    });
});
</script>


<script>
// Retain table scroll position and expanded rows using localStorage

// Save scroll position and expanded rows before unload
window.addEventListener('beforeunload', function() {
    // Save scroll position
    var tableDiv = document.querySelector('.table-responsive');
    if (tableDiv) {
        localStorage.setItem('srf_table_scroll', tableDiv.scrollLeft + ',' + tableDiv.scrollTop);
    }
    // Save expanded rows
    var expandedRows = [];
    document.querySelectorAll('.collapse-row').forEach(function(row, idx) {
        if (row.style.display === 'table-row') {
            expandedRows.push(row.getAttribute('data-srfid'));
        }
    });
    localStorage.setItem('srf_table_expanded', JSON.stringify(expandedRows));
});

// Restore scroll position and expanded rows on load
document.addEventListener('DOMContentLoaded', function() {
    // Restore scroll position
    var tableDiv = document.querySelector('.table-responsive');
    var scroll = localStorage.getItem('srf_table_scroll');
    if (tableDiv && scroll) {
        var parts = scroll.split(',');
        tableDiv.scrollLeft = parseInt(parts[0] || 0, 10);
        tableDiv.scrollTop = parseInt(parts[1] || 0, 10);
    }
    // Restore expanded rows
    var expandedRows = localStorage.getItem('srf_table_expanded');
    if (expandedRows) {
        try {
            var ids = JSON.parse(expandedRows);
            ids.forEach(function(srfId) {
                var collapseRow = document.querySelector('tr.collapse-row[data-srfid="' + srfId + '"]');
                if (collapseRow) {
                    collapseRow.style.display = 'table-row';
                    // Change icon to minus
                    var mainRow = collapseRow.previousElementSibling;
                    if (mainRow) {
                        var btn = mainRow.querySelector('.collapse-btn i');
                        if (btn) {
                            btn.classList.remove('fa-plus');
                            btn.classList.add('fa-minus');
                        }
                    }
                }
            });
        } catch(e) {}
    }
});
</script>




</body>
</html>