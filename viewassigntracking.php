<?php

require_once 'connect.php';

// Start session to access $_SESSION variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch distinct offices for the Office dropdown
// Ensure to sanitize and validate $_SESSION['OfficeSRF'] to prevent SQL injection
$userOffice = isset($_SESSION['OfficeSRF']) ? $conn->real_escape_string($_SESSION['OfficeSRF']) : '';

// Only fetch offices relevant to the user's assigned office
$officesResult = $conn->query("SELECT DISTINCT office FROM srfsigner WHERE office = '{$userOffice}' ORDER BY office ASC");
$offices = [];
if ($officesResult) {
    while ($row = $officesResult->fetch_assoc()) {
        $offices[] = $row;
    }
}

// Fetch the selected office and station from POST data if available
$selectedOffice = isset($_POST['office']) ? $conn->real_escape_string($_POST['office']) : '';
$selectedStation = isset($_POST['station']) ? $conn->real_escape_string($_POST['station']) : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRF Signer Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/signature_pad@4.1.0/dist/signature-pad.css">

    <style>
        /* General Body Styling */
        body {
            background-color: #e9ecef; /* A slightly darker light gray for more contrast */
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #343a40;
        }

        /* Main Container Styling */
        .container {
            background-color: #ffffff;
            padding: 40px; /* Increased padding */
            border-radius: 12px; /* More rounded corners */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Softer, more prominent shadow */
            margin-top: 60px; /* More space from the top */
            margin-bottom: 60px; /* More space at the bottom */
        }

        /* Headings */
        h2, h4 {
            color: #1a237e; /* Deep indigo for strong headings */
            margin-bottom: 25px;
            font-weight: 700; /* Bolder font weight */
            text-align: center; /* Center align main title */
        }

        h4 {
            font-weight: 600;
            margin-top: 35px; /* Space above table title */
            margin-bottom: 20px;
            color: #34495e; /* Darker grey-blue for sub-headings */
        }

        hr {
            border-top: 1px solid #dee2e6; /* Light border for separators */
            margin-top: 25px;
            margin-bottom: 30px;
        }

        /* Form Group Labels */
        .form-label {
            font-weight: 600; /* Bolder labels */
            color: #495057;
            margin-bottom: 8px; /* Space between label and input */
        }

        /* Form Selects (Dropdowns) */
        .form-select {
            border-radius: 6px; /* Slightly rounded for inputs */
            border: 1px solid #ced4da;
            padding: 10px 15px;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }

        /* Buttons */
        .btn {
            border-radius: 6px; /* Consistent rounding */
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.2s ease-in-out;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        /* Table Styling */
        .table {
            margin-top: 20px;
            border-collapse: separate; /* Allows for border-radius on cells */
            border-spacing: 0; /* Remove default cell spacing */
        }

        .table thead th {
            background-color: #007bff; /* Primary blue for header */
            color: white;
            vertical-align: middle;
            font-weight: 600;
            padding: 12px 15px; /* More padding */
            border-bottom: none; /* No double border with tbody */
        }
        .table thead th:first-child {
            border-top-left-radius: 8px; /* Rounded top-left corner for header */
        }
        .table thead th:last-child {
            border-top-right-radius: 8px; /* Rounded top-right corner for header */
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }
        .table tbody tr:hover {
            background-color: #e2f0ff; /* Lighter blue on hover */
            cursor: pointer;
        }
        .table tbody td {
            padding: 12px 15px; /* More padding for body cells */
            vertical-align: middle;
            border-top: 1px solid #e0e0e0; /* Lighter border between rows */
        }

        /* Dropdown within table actions */
        .table .dropdown-toggle {
            padding: 5px 10px; /* Smaller padding for action button */
            font-size: 0.875rem; /* Smaller font size */
        }
        .table .dropdown-menu {
            border-radius: 6px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        .table .dropdown-item {
            padding: 10px 15px;
            font-size: 0.9rem;
            transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
            display: flex; /* For icon alignment */
            align-items: center;
            gap: 8px; /* Space between icon and text */
        }
        .table .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #007bff;
        }
        .table .dropdown-item.text-danger:hover {
            background-color: #ffe6e6; /* Light red hover for danger */
            color: #dc3545;
        }
        .table .dropdown-item i {
            width: 20px; /* Fixed width for icons to align text */
            text-align: center;
        }

        /* Modal Overrides */
        .modal-content {
            border-radius: 12px; /* Consistent modal rounding */
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2); /* Stronger modal shadow */
        }
        .modal-header {
            border-bottom: none; /* Remove default border */
            padding: 20px 25px;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            display: flex; /* Ensures title and button are properly aligned */
            justify-content: space-between;
            align-items: center;
        }
        .modal-header .modal-title {
            font-weight: 600;
            color: #fff;
        }
        .modal-header .btn-close {
            filter: invert(1); /* Makes the close 'x' white */
            opacity: 0.8;
            transition: opacity 0.2s ease;
        }
        .modal-header .btn-close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 25px; /* More padding */
        }
        .modal-footer {
            border-top: none; /* Remove default border */
            padding: 20px 25px;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
            justify-content: flex-end; /* Align buttons to the right */
            gap: 10px; /* Space between buttons */
        }

        /* Specific modal content styles */
        .modal-body .form-label {
            margin-bottom: 5px;
        }
        .modal-body .form-control, .modal-body .form-select {
            border-radius: 5px;
        }
        .modal-body .border.rounded.p-3.bg-light {
            border: 1px dashed #cccccc !important; /* Dashed border for signature viewer */
            background-color: #fdfdfd !important; /* Very light background */
            min-height: 150px; /* Ensures space even if no image */
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-body .border.rounded.p-3.bg-light img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        .modal-body .text-muted {
            font-style: italic;
            color: #6c757d !important;
        }

        /* Signature Pad specific styles */
        .signature-pad-container {
            border: 2px solid #007bff; /* Primary color border for signature pad */
            background-color: #fdfefe; /* Off-white background */
            border-radius: 8px;
            overflow: hidden; /* Ensures drawing stays within bounds */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        canvas {
            touch-action: none; /* Improves drawing experience on touch devices */
        }

        /* Responsive Adjustments (using Bootstrap's grid for filter form already helps) */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin-top: 30px;
                margin-bottom: 30px;
            }
            h2 {
                font-size: 1.8rem;
                margin-bottom: 20px;
            }
            .form-label {
                font-size: 0.9rem;
            }
            .btn {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
            .table thead th, .table tbody td {
                padding: 10px;
                font-size: 0.9rem;
            }
            .table .dropdown-item {
                font-size: 0.85rem;
                padding: 8px 12px;
            }
            .modal-body iframe {
                min-height: 300px;
            }
        }

        /* Hide buttons that were commented out in HTML */
        .mb-4.d-flex.justify-content-end.gap-2 > button:nth-child(1),
        .mb-4.d-flex.justify-content-end.gap-2 > button:nth-child(2) {
            display: none; /* Hide the specific buttons that were commented out */
        }

        /* Ensure filter form buttons align */
        .form-group.col-md-2.d-grid {
            align-self: flex-end; /* Ensures button aligns with bottom of selects */
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">SRF Signer Management</h2>
    <hr>

    <form id="filterForm" method="POST" class="mb-4 row g-3 align-items-end">
        <div class="col-md-5">
            <label for="office" class="form-label"><i class="fas fa-building"></i> Office</label>
            <select id="office" name="office" class="form-select">
                <option value="">Select Office</option>
                <?php foreach ($offices as $office): ?>
                    <option value="<?php echo htmlspecialchars($office['office']); ?>" <?php echo $office['office'] === $selectedOffice ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($office['office']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-5">
            <label for="station" class="form-label"><i class="fas fa-map-marker-alt"></i> Station</label>
            <select id="station" name="station" class="form-select">
                <option value="">Select Station</option>
                </select>
        </div>

        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        </div>
    </form>
    <hr>

    <div class="mb-4 d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#viewOfficeAccountModal">
            <i class="fas fa-eye"></i> View Office Account
        </button>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSignerModal">
            <i class="fas fa-user-plus"></i> Add New Signer
        </button>
    </div>

    <h4><i class="fas fa-list-alt"></i> Signer List</h4>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Office</th>
                    <th>Station</th>
                    <th>Signer Name</th>
                    <th>Position</th>
                    <th>Level</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody id="resultTable">
                <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($selectedOffice)) {
                    $query = "SELECT * FROM srfsigner WHERE 1=1";

                    if (!empty($selectedOffice)) {
                        $query .= " AND Office = '{$selectedOffice}'";
                    }

                    if (!empty($selectedStation)) {
                        $query .= " AND Station = '{$selectedStation}'";
                    }

                    $query .= " ORDER BY level ASC";
                    
                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $id = htmlspecialchars($row['id']);
                            $office = htmlspecialchars($row['office']);
                            $station = htmlspecialchars($row['station']);
                            $name = htmlspecialchars($row['name']);
                            $position = htmlspecialchars($row['position'] ?? 'N/A');
                            $level = htmlspecialchars($row['level']);
                            $signaturePath = htmlspecialchars($row['signature'] ?? '');

                            echo "<tr>
                                    <td>{$id}</td>
                                    <td>{$office}</td>
                                    <td>{$station}</td>
                                    <td>{$name}</td>
                                    <td>{$position}</td>
                                    <td>{$level}</td>
                                    <td class='text-center'>
                                        <div class='dropdown'>
                                            <button class='btn btn-sm btn-secondary dropdown-toggle' type='button' id='dropdownMenuButton{$id}' data-bs-toggle='dropdown' aria-expanded='false'>
                                                Actions
                                            </button>
                                            <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton{$id}'>
                                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#viewSignatureModal{$id}'><i class='fas fa-signature'></i> View Signature</a></li>
                                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#addSignaturepadModal{$id}'><i class='fas fa-pencil-alt'></i> Signature Pad</a></li>
                                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#editSignerModal{$id}'><i class='fas fa-edit'></i> Edit Details</a></li>
                                                <li><a class='dropdown-item text-danger' href='#' data-bs-toggle='modal' data-bs-target='#deleteSignerModal{$id}'><i class='fas fa-trash-alt'></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>";

                            // Modals for each row (HTML structure remains the same)
                            echo "<div class='modal fade' id='viewSignatureModal{$id}' tabindex='-1' aria-labelledby='viewSignatureLabel{$id}' aria-hidden='true'>
                                <div class='modal-dialog modal-dialog-centered'>
                                    <div class='modal-content'>
                                        <div class='modal-header bg-info text-white'>
                                            <h5 class='modal-title' id='viewSignatureLabel{$id}'>Signature for {$name}</h5>
                                            <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body text-center'>
                                            <form action='add_signature.php' method='POST' enctype='multipart/form-data'>
                                                <input type='hidden' name='id' value='{$id}'>
                                                <div class='mb-3'>
                                                    <label class='form-label'>Current Signature:</label>
                                                    <div class='border rounded p-3 bg-light'>
                                                        ";
                                                        if (!empty($signaturePath) && file_exists($signaturePath)) {
                                                            echo "<img src='{$signaturePath}' alt='Signature' class='img-fluid' style='max-height: 200px;'>";
                                                        } else {
                                                            echo "<p class='text-muted'>No signature uploaded yet.</p>";
                                                        }
                                                        echo "
                                                    </div>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='signatureUpload{$id}' class='form-label'>Upload New Signature (Image file)</label>
                                                    <input type='file' class='form-control' id='signatureUpload{$id}' name='signature' accept='image/*' required>
                                                </div>
                                                <div class='modal-footer justify-content-between'>
                                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                                    <button type='submit' class='btn btn-success'><i class='fas fa-upload'></i> Upload Signature</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>";

                            echo "<div class='modal fade' id='addSignaturepadModal{$id}' tabindex='-1' aria-labelledby='addSignaturePadLabel{$id}' aria-hidden='true'>
                                <div class='modal-dialog modal-lg modal-dialog-centered'>
                                    <div class='modal-content'>
                                        <div class='modal-header bg-success text-white'>
                                            <h5 class='modal-title' id='addSignaturePadLabel{$id}'>Signature Pad for {$name}</h5>
                                            <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <form action='save_signature_pad.php' method='POST'>
                                            <div class='modal-body'>
                                                <input type='hidden' name='id' value='{$id}'>
                                                <div class='signature-pad-container mb-3'>
                                                    <canvas id='signaturePadCanvas{$id}' width='600' height='250'></canvas>
                                                </div>
                                                <input type='hidden' id='signatureDataInput{$id}' name='signatureData'>
                                                <div class='d-flex justify-content-between'>
                                                    <button type='button' class='btn btn-outline-secondary' id='clearPadBtn{$id}'><i class='fas fa-eraser'></i> Clear</button>
                                                    <button type='button' class='btn btn-primary' id='savePadBtn{$id}'><i class='fas fa-save'></i> Save Drawing</button>
                                                </div>
                                            </div>
                                            <div class='modal-footer'>
                                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                                <button type='submit' class='btn btn-success'><i class='fas fa-paper-plane'></i> Submit Signature</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>";
                            echo "<script src='https://cdn.jsdelivr.net/npm/signature_pad@4.1.0/dist/signature_pad.umd.min.js'></script>";
                            echo "<script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const canvas{$id} = document.getElementById('signaturePadCanvas{$id}');
                                    const signaturePad{$id} = new SignaturePad(canvas{$id});
                                    const clearBtn{$id} = document.getElementById('clearPadBtn{$id}');
                                    const saveBtn{$id} = document.getElementById('savePadBtn{$id}');
                                    const signatureDataInput{$id} = document.getElementById('signatureDataInput{$id}');

                                    document.getElementById('addSignaturepadModal{$id}').addEventListener('shown.bs.modal', function () {
                                        signaturePad{$id}.clear();
                                        const ratio = Math.max(window.devicePixelRatio || 1, 1);
                                        canvas{$id}.width = canvas{$id}.offsetWidth * ratio;
                                        canvas{$id}.height = canvas{$id}.offsetHeight * ratio;
                                        canvas{$id}.getContext('2d').scale(ratio, ratio);
                                        signaturePad{$id}.clear();
                                    });

                                    clearBtn{$id}.addEventListener('click', function () {
                                        signaturePad{$id}.clear();
                                        signatureDataInput{$id}.value = '';
                                    });

                                    saveBtn{$id}.addEventListener('click', function () {
                                        if (!signaturePad{$id}.isEmpty()) {
                                            const dataURL = signaturePad{$id}.toDataURL('image/png');
                                            signatureDataInput{$id}.value = dataURL;
                                            alert('Signature drawing captured! Click Submit Signature to save it.');
                                        } else {
                                            alert('Please draw a signature before saving.');
                                        }
                                    });
                                });
                            </script>";

                            echo "<div class='modal fade' id='editSignerModal{$id}' tabindex='-1' aria-labelledby='editSignerLabel{$id}' aria-hidden='true'>
                                <div class='modal-dialog modal-dialog-centered'>
                                    <div class='modal-content'>
                                        <div class='modal-header bg-warning text-white'>
                                            <h5 class='modal-title' id='editSignerLabel{$id}'>Edit Signer Details (ID: {$id})</h5>
                                            <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <form method='POST' action='editsrfsigner.php'>
                                            <div class='modal-body'>
                                                <input type='hidden' name='personelid' value='{$id}'>
                                                <div class='mb-3'>
                                                    <label for='editName{$id}' class='form-label'>Signer Name</label>
                                                    <input type='text' name='name' id='editName{$id}' value='{$name}' class='form-control' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='editPosition{$id}' class='form-label'>Position</label>
                                                    <input type='text' name='position' id='editPosition{$id}' value='{$position}' class='form-control' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='editLevel{$id}' class='form-label'>Level</label>
                                                    <input type='number' name='level' id='editLevel{$id}' value='{$level}' class='form-control' required min='1'>
                                                </div>
                                            </div>
                                            <div class='modal-footer'>
                                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                                                <button type='submit' class='btn btn-primary'><i class='fas fa-save'></i> Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>";

                            echo "<div class='modal fade' id='deleteSignerModal{$id}' tabindex='-1' aria-labelledby='deleteSignerLabel{$id}' aria-hidden='true'>
                                <div class='modal-dialog modal-dialog-centered'>
                                    <div class='modal-content'>
                                        <div class='modal-header bg-danger text-white'>
                                            <h5 class='modal-title' id='deleteSignerLabel{$id}'>Confirm Deletion</h5>
                                            <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <p>Are you sure you want to delete the signer <strong>{$name}</strong> (ID: {$id})? This action cannot be undone.</p>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                                            <a class='btn btn-danger' href='deletesrfsigner.php?delete={$id}'><i class='fas fa-trash-alt'></i> Delete Signer</a>
                                        </div>
                                    </div>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center text-muted'>No signers found for the selected criteria.</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center text-muted'>Please select an Office and click Filter to view signers.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addSignerModal" tabindex="-1" aria-labelledby="addSignerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addSignerModalLabel">Add New SRF Signer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="add_new_signer.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newSignerName" class="form-label">Signer Name</label>
                        <input type="text" class="form-control" id="newSignerName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="newSignerOffice" class="form-label">Office</label>
                        <select id="newSignerOffice" name="office" class="form-select" required>
                            <option value="">Select Office</option>
                            <?php foreach ($offices as $office): ?>
                                <option value="<?php echo htmlspecialchars($office['office']); ?>">
                                    <?php echo htmlspecialchars($office['office']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="newSignerStation" class="form-label">Station</label>
                        <select id="newSignerStation" name="station" class="form-select" required>
                            <option value="">Select Station</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="newSignerPosition" class="form-label">Position</label>
                        <input type="text" class="form-control" id="newSignerPosition" name="position" required>
                    </div>
                    <div class="mb-3">
                        <label for="newSignerLevel" class="form-label">Level</label>
                        <input type="number" class="form-control" id="newSignerLevel" name="level" required min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-plus-circle"></i> Add Signer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="viewOfficeAccountModal" tabindex="-1" aria-labelledby="viewOfficeAccountLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewOfficeAccountLabel"><i class="fas fa-desktop"></i> View Office Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe src="office_employee.php" style="width: 100%; height: 70vh; border: none;" title="Office Employee Data"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignTrackingModal" tabindex="-1" aria-labelledby="assignTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="assignTrackingModalLabel"><i class="fas fa-route"></i> Assign Tracking</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe src="mainmenu.php?dir=assigntracking" style="width: 100%; height: 70vh; border: none;" title="Assign Tracking"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var officeSelect = document.getElementById('office');
        var stationSelect = document.getElementById('station');
        var newSignerOfficeSelect = document.getElementById('newSignerOffice');
        var newSignerStationSelect = document.getElementById('newSignerStation');

        function fetchStations(officeValue, targetSelectElement, previouslySelectedStation = '') {
            if (!officeValue) {
                targetSelectElement.innerHTML = '<option value="">Select Station</option>';
                return;
            }
            fetch('fetch_station.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    'office': officeValue
                })
            })
            .then(response => response.text())
            .then(data => {
                targetSelectElement.innerHTML = data;
                if (previouslySelectedStation) {
                    targetSelectElement.value = previouslySelectedStation;
                } else {
                    targetSelectElement.value = '';
                }
            })
            .catch(error => {
                console.error("Fetch Error: ", error);
                targetSelectElement.innerHTML = '<option value="">Error loading stations</option>';
            });
        }

        var savedOffice = localStorage.getItem('selectedOffice');
        var savedStation = localStorage.getItem('selectedStation');

        if (savedOffice && officeSelect) {
            officeSelect.value = savedOffice;
            fetchStations(savedOffice, stationSelect, savedStation);
        }

        if (officeSelect) {
            officeSelect.addEventListener('change', function() {
                var officeValue = this.value;
                localStorage.setItem('selectedOffice', officeValue);
                localStorage.removeItem('selectedStation');
                fetchStations(officeValue, stationSelect);
            });
        }

        if (stationSelect) {
            stationSelect.addEventListener('change', function() {
                localStorage.setItem('selectedStation', this.value);
            });
        }

        if (newSignerOfficeSelect && newSignerStationSelect) {
            newSignerOfficeSelect.addEventListener('change', function() {
                fetchStations(this.value, newSignerStationSelect);
            });
        }
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get search input element
        const searchInput = document.querySelector('input[type="search"]');
        if (searchInput) {
            // Load previous search if exists
            const previousSearch = localStorage.getItem('previousSearch');
            if (previousSearch) {
                searchInput.value = previousSearch;
            }

            // Save search term when user types
            searchInput.addEventListener('input', function() {
                localStorage.setItem('previousSearch', this.value);
                // Keep the table visible/present
                document.querySelector('table')?.style.display = 'table';
            });

            // Save search term when form is submitted
            searchInput.closest('form')?.addEventListener('submit', function() {
                localStorage.setItem('previousSearch', searchInput.value);
                // Ensure table remains visible after form submission
                document.querySelector('table')?.style.display = 'table';
            });
        }
    });
</script>



</body>
</html>