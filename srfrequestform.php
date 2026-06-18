<?php

require_once "session_checker.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request Form (SRF)</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 650px;
            margin: 40px auto;
        }

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            padding: 20px 30px;
            text-align: center;
        }

        .card-header h2 {
            font-weight: 700;
            color: #343a40;
            margin-bottom: 0;
            font-size: 1.8rem;
        }

        .card-body {
            padding: 30px;
        }

        /* Multi-step progress indicator */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            position: relative;
            padding: 0;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e9ecef;
            transform: translateY(-50%);
            z-index: 1;
        }

        .progress-step-item {
            list-style: none;
            text-align: center;
            position: relative;
            flex: 1;
            z-index: 2;
        }

        .progress-step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #6c757d;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            margin: 0 auto 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .progress-step-item.active .progress-step-circle {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .progress-step-item.completed .progress-step-circle {
            background-color: #198754;
            color: white;
            border-color: #198754;
        }

        .progress-step-label {
            font-size: 0.9em;
            color: #6c757d;
            font-weight: 500;
        }

        .progress-step-item.active .progress-step-label {
            color: #343a40;
            font-weight: 600;
        }

        /* Form sections */
        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            font-size: 1rem;
        }
        
        .form-control.is-invalid, .form-select.is-invalid, textarea.is-invalid {
            border-color: #dc3545;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .italic {
            font-style: italic;
            color: #6c757d;
        }

        .other-specify {
            display: none;
            margin-top: 10px;
        }

        /* Custom file upload area */
        .file-upload-area {
            border: 2px dashed #ced4da;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: border-color 0.3s ease, background-color 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .file-upload-area:hover {
            border-color: #0d6efd;
            background-color: #e9f5ff;
        }

        .file-upload-area i.bi-cloud-arrow-up {
            font-size: 3.5rem;
            color: #0d6efd;
            margin-bottom: 15px;
        }

        .file-upload-area p {
            font-size: 1.1rem;
            color: #495057;
            margin-bottom: 5px;
        }

        .file-upload-area p:last-of-type {
            margin-bottom: 15px;
        }

        .file-upload-area .btn-browse {
            background-color: #0d6efd;
            color: white;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .file-upload-area .btn-browse:hover {
            background-color: #0b5ed7;
        }

        .file-upload-area small {
            color: #6c757d;
            display: block;
            margin-top: 10px;
            font-size: 0.85rem;
        }

        #fileList .alert {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #e0f2f7;
            border-color: #a7d9ee;
            color: #084298;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        #fileList .alert .btn-close {
            filter: invert(30%) sepia(80%) saturate(2000%) hue-rotate(180deg) brightness(80%) contrast(90%);
        }

        /* Buttons at the bottom */
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .button-group .btn {
            min-width: 130px;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .button-group #prevBtn {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .button-group #prevBtn:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .button-group #prevBtn:disabled {
            background-color: #ccc;
            border-color: #ccc;
        }

        .button-group #nextBtn {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }
        .button-group #nextBtn:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .button-group #submitBtn {
            background-color: #198754;
            border-color: #198754;
            color: white;
        }
        .button-group #submitBtn:hover {
            background-color: #157347;
            border-color: #146c43;
        }
        .button-group #submitBtn.disabled {
            opacity: 0.7;
        }

        /* Modal specific styles */
        .modal-content {
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: none;
            padding: 20px 25px;
            background-color: #f8f9fa;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .modal-title {
            font-weight: 700;
            color: #343a40;
        }

        .modal-body {
            padding: 15px 25px 25px;
        }

        .modal .form-control {
            border-radius: 8px;
        }

        .table thead th {
            background-color: #f0f2f5;
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #e2e6ea;
            vertical-align: middle;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.95rem;
        }

        .table .btn.select-equipment {
            border-radius: 6px;
            padding: 6px 15px;
            font-size: 0.9rem;
            background-color: #28a745;
            border-color: #28a745;
        }
        .table .btn.select-equipment:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .wrap-text {
            white-space: normal;
            word-wrap: break-word;
            max-width: 250px;
        }

        /* Toast Notification */
        .toast-container {
            z-index: 1050;
        }
        .toast {
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .toast-header {
            border-bottom: none;
            background-color: transparent;
            color: inherit;
        }
        .toast .btn-close {
            filter: invert(100%);
        }
    </style>
</head>
<body>

<?php

function generateTicketNumber() {
    if (!isset($_SESSION['ticket_number'])) {
        $_SESSION['ticket_number'] = 1;
    }
    $date = date('Ymd');
    $uniqidPart = substr(uniqid(), -5);
    $ticketNumber = $date . '-' . sprintf('%03d', $_SESSION['ticket_number']) . '-' . $uniqidPart;
    $_SESSION['ticket_number']++;
    return $ticketNumber;
}

$ticketNumber = generateTicketNumber();
$date = date('Y-m-d');
?>


<div class="card">
    <div class="card-header">
        <h2 class="mb-0">SERVICE REQUEST FORM (SRF)</h2>
    </div>
    <div class="card-body">
        <ul class="progress-steps">
            <li class="progress-step-item active" id="step-1">
                <div class="progress-step-circle">1</div>
                <div class="progress-step-label">Requester Info</div>
            </li>
            <li class="progress-step-item" id="step-2">
                <div class="progress-step-circle">2</div>
                <div class="progress-step-label">Request Details</div>
            </li>
            <li class="progress-step-item" id="step-3">
                <div class="progress-step-circle">3</div>
                <div class="progress-step-label">Confirmation</div>
            </li>
        </ul>

        <form action="submit_srfhandler.php" method="POST" id="multiStepForm" enctype="multipart/form-data">
            <div class="form-section active" id="section-1">
                <div class="form-group">
                    <label for="ticketNumber" class="form-label">Ticket Number</label>
                    <input type="text" id="ticketNumber" value="<?php echo htmlspecialchars($ticketNumber); ?>" name="ticketNumber" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label for="date" class="form-label">Date</label>
                    <input type="text" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <?php
                    require_once 'connect_otos.php';
                    $station = $_SESSION['StationSRF'];
                    $stmt = $conn_otos->prepare("SELECT full_name, id, Div_Sec_Unit, Position, Contact_Number, Station FROM useremployee WHERE Station = ? ORDER BY full_name");
                    $stmt->bind_param("s", $station);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        echo '<label for="name" class="form-label">Name</label>';
                        echo '<select id="name" name="name" onchange="updateInfo(this.value)" class="form-select">';
                        echo '<option value="">Select your name</option>';
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="'. strtoupper(htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8')) . '" data-idname="' . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . '" data-divsecunit="' . htmlspecialchars($row['Div_Sec_Unit'], ENT_QUOTES, 'UTF-8') . '" data-position="' . htmlspecialchars($row['Position'], ENT_QUOTES, 'UTF-8') . '" data-contact="' . htmlspecialchars($row['Contact_Number'], ENT_QUOTES, 'UTF-8') . '" data-station="'. htmlspecialchars($row['Station'], ENT_QUOTES, 'UTF-8') . '">' . strtoupper(htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8')) . '</option>';
                        }
                        echo '</select>';
                    }
                    ?>
                </div>
                <input type="hidden" id="idname" name="idname" required> 
                <div class="form-group">
                    <label for="divSecUnit" class="form-label">Div/Sec/Unit:</label>
                    <input type="text" id="divSecUnit" name="divSecUnit" class="form-control" readonly required>
                </div>

                <div class="form-group">
                    <label for="station" class="form-label">Station:</label>
                    <input type="text" id="station" name="station" class="form-control" readonly required>
                </div>

                <div class="form-group">
                    <label for="position" class="form-label">Position:</label>
                    <input type="text" id="position" name="position" class="form-control" readonly required>
                </div>
                <div class="form-group">
                    <label for="contactNumber" class="form-label">Contact Number:</label>
                    <input type="text" id="contactNumber" name="contactNumber" class="form-control" readonly required>
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="your.email@example.com" required>
                </div>

                <div class="form-group">
                    <label for="requestType" class="form-label">Request Type</label>
                    <select id="requestType" name="requestType" onchange="showOtherSpecify()" class="form-select" required>
                        <option value="" selected disabled>-- Select Request Type --</option>
                        <option value="Zoom">Zoom</option>
                        <option value="Technical Assistance">Technical Assistance</option>
                        <option value="Asset/Borrow">Asset/Borrow</option>
                        <option value="Email">Email</option>
                        <option value="In House Software">In House Software (OTOS WEB+, EDATS, OLDPMS, D-SIGN)</option>
                        <option value="Other">Other (Specify)</option>
                    </select>
                    <input type="hidden" id="zoomTitle_hidden" name="zoomTitle_hidden">
                    <input type="hidden" id="zoomDateTime_hidden" name="zoomDateTime_hidden">
                    <input type="hidden" id="zoomMeetingId_hidden" name="zoomMeetingId_hidden">
                    <input type="hidden" id="zoomPassword_hidden" name="zoomPassword_hidden">
                    <input type="hidden" id="zoomBlended_hidden" name="zoomBlended_hidden">
                    <input type="hidden" id="zoomRemarks_hidden" name="zoomRemarks_hidden">
                    <input type="hidden" id="emailTo_hidden" name="emailTo_hidden">
                    <input type="hidden" id="emailSubject_hidden" name="emailSubject_hidden">
                    <input type="hidden" id="emailRemarks_hidden" name="emailRemarks_hidden">
                    <input type="hidden" id="softwareName_hidden" name="softwareName_hidden">
                    <input type="hidden" id="softwareRemarks_hidden" name="softwareRemarks_hidden">
                    <input type="hidden" id="otherTitle_hidden" name="otherTitle_hidden">
                    <input type="hidden" id="otherRemarks_hidden" name="otherRemarks_hidden">
                    <input type="hidden" id="taEquipmentId_hidden" name="taEquipmentId_hidden">
                    <input type="hidden" id="taDescription_hidden" name="taDescription_hidden">
                    <input type="hidden" id="borrowName_hidden" name="borrowName_hidden">
                    <input type="hidden" id="borrowDivSec_hidden" name="borrowDivSec_hidden">
                    <input type="hidden" id="borrowDescription_hidden" name="borrowDescription_hidden">
                    <input type="hidden" id="borrowDate_hidden" name="borrowDate_hidden">
                    <input type="hidden" id="borrowReturnDate_hidden" name="borrowReturnDate_hidden">
                </div>

                <div class="form-group">
                    <label for="equipment_id" class="form-label">Selected Equipment</label>
                    <div class="input-group">
                        <input type="text" id="equipment_id" name="equipment_id" class="form-control" placeholder="Select equipment" readonly >
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#searchEquipmentModal">
                            <i class="bi bi-search"></i> Select Equipment
                        </button>
                    </div>
                </div>
            </div><div class="form-section" id="section-2">
                <div class="form-group">
                    <label for="description" class="form-label">Description of Request</label>
                    <textarea id="description" name="description" class="form-control italic" placeholder="Please Clearly Write-down the details of the request" required></textarea>
                </div>

                <div class="form-group">
                    <label for="uploadDocumentsInput" class="form-label">Upload Documents:</label>
                    <div class="file-upload-area" id="dropArea">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p>Drag & Drop files here</p>
                        <p>or</p>
                        <label for="uploadDocumentsInput" class="btn btn-primary btn-browse">
                            <i class="bi bi-folder"></i> Browse Files
                        </label>
                        <input type="file" id="uploadDocumentsInput" name="uploadDocuments[]" multiple accept=".pdf,.doc,.docx,.jpg,.png" hidden>
                        <small>Supported formats: PDF, DOC, DOCX, JPG, PNG (Max: 5MB each)</small>
                    </div>
                    <div id="fileList" class="mt-2"></div>
                </div>
            </div><div class="button-group">
                <button type="button" id="prevBtn" class="btn btn-secondary" onclick="prevSection()" disabled>
                    <i class="bi bi-arrow-left"></i> Previous
                </button>
                <button type="button" id="nextBtn" class="btn btn-primary" onclick="validateAndProceed()">
                    Next <i class="bi bi-arrow-right"></i>
                </button>
                <button type="submit" id="submitBtn" class="btn btn-success" style="display: none;" disabled onclick="handleSubmit(event)">
                    <i class="bi bi-send-fill"></i> Submit Form
                </button>

            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="equipmentInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info d-flex align-items-start mb-0" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                    <div>
                        <strong>Why select equipment?</strong><br>
                        You must select an Equipment for Technical Assistance requests. This will help IT or the Help Desk identify your equipment, validate it faster, resolve the issue more quickly, and determine the necessary tools based on the selected equipment.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="showEquipmentSearch()">I Understand</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="searchEquipmentModal" tabindex="-1" aria-labelledby="searchEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchEquipmentModalLabel">Search Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
            </div>
            <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 11;">
                <div id="selectionToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            Equipment selected successfully!
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <input type="text" id="searchBox" class="form-control mb-3" placeholder="Search by Employee Name, ID, or Serial Number">
                <table class="table table-bordered table-hover" id="equipmentTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee Name</th>
                            <th>Specifications</th>
                            <th>Serial Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require_once 'connect.php';
                        $sql = "SELECT id, employeeName, specifications, serialNumber FROM inv_inventory WHERE office = '{$_SESSION['OfficeSRF']}'";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['employeeName']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['specifications']) . "</td>";
                                echo "<td class='wrap-text'>" . htmlspecialchars($row['serialNumber']) . "</td>";
                                echo "<td><button type='button' class='btn btn-success select-equipment' data-id='" . htmlspecialchars($row['id']) . "' data-name='" . htmlspecialchars($row['employeeName']) . "' data-specs='" . htmlspecialchars($row['specifications']) . "' data-serial='" . htmlspecialchars($row['serialNumber']) . "'>Select</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No equipment available</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="technicalAssistanceModal" tabindex="-1" aria-labelledby="technicalAssistanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="technicalAssistanceModalLabel">Technical Assistance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="form-label">Selected Equipment</label>
                    <div class="p-3 bg-light rounded border" id="taEquipmentDisplay">
                        No equipment selected.
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="taDescription" class="form-label">Description of Issue</label>
                    <textarea id="taDescription" class="form-control" placeholder="Describe the technical issue or assistance needed" rows="4" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTechnicalAssistance()">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="borrowModal" tabindex="-1" aria-labelledby="borrowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="borrowModalLabel">Asset/Borrow Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="form-label">Selected Equipment</label>
                    <div class="p-3 bg-light rounded border" id="borrowEquipmentDisplay">
                        No equipment selected.
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="borrowName" class="form-label">Borrower's Name</label>
                    <input type="text" id="borrowName" class="form-control" placeholder="Enter borrower's full name" required>
                </div>
                <div class="form-group mb-3">
                    <label for="borrowDivSec" class="form-label">Division/Section</label>
                    <input type="text" id="borrowDivSec" class="form-control" placeholder="Enter division or section" required>
                </div>
                <div class="form-group mb-3">
                    <label for="borrowDescription" class="form-label">Reason for Borrowing</label>
                    <textarea id="borrowDescription" class="form-control" placeholder="Why are you borrowing this equipment?" rows="3" required></textarea>
                </div>
                <div class="form-group mb-3">
                    <label for="borrowDate" class="form-label">Date Borrowed</label>
                    <input type="date" id="borrowDate" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label for="borrowReturnDate" class="form-label">Date to Return</label>
                    <input type="date" id="borrowReturnDate" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveBorrowDetails()">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="zoomModal" tabindex="-1" aria-labelledby="zoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="zoomModalLabel">Zoom Meeting Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="zoomTitle" class="form-label">Meeting Title</label>
                    <input type="text" id="zoomTitle" class="form-control" placeholder="Enter meeting title" required>
                </div>
                <div class="form-group mb-3">
                    <label for="zoomDateTime" class="form-label">Date & Time of Meeting</label>
                    <input type="datetime-local" id="zoomDateTime" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label for="zoomMeetingId" class="form-label">Meeting ID <small class="text-muted">(optional)</small></label>
                    <input type="text" id="zoomMeetingId" class="form-control" placeholder="Enter meeting ID">
                </div>
                <div class="form-group mb-3">
                    <label for="zoomPassword" class="form-label">Password <small class="text-muted">(optional)</small></label>
                    <input type="text" id="zoomPassword" class="form-control" placeholder="Enter meeting password">
                </div>
                <div class="form-group mb-3">
                    <label for="zoomBlended" class="form-label">Meeting Type</label>
                    <select id="zoomBlended" class="form-select">
                        <option value="Online">Online</option>
                        <option value="Blended">Blended</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label for="zoomRemarks" class="form-label">Remarks / Instructions <small class="text-muted">(optional)</small></label>
                    <textarea id="zoomRemarks" class="form-control" placeholder="Additional remarks or instructions" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveZoomDetails()">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailModalLabel">Email Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="emailTo" class="form-label">Recipient Email Address</label>
                    <input type="email" id="emailTo" class="form-control" placeholder="Enter recipient email address" required>
                </div>
                <div class="form-group mb-3">
                    <label for="emailSubject" class="form-label">Subject <small class="text-muted">(optional)</small></label>
                    <input type="text" id="emailSubject" class="form-control" placeholder="Enter email subject">
                </div>
                <div class="form-group mb-3">
                    <label for="emailRemarks" class="form-label">Remarks</label>
                    <textarea id="emailRemarks" class="form-control" placeholder="Additional remarks or instructions" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveEmailDetails()">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="softwareModal" tabindex="-1" aria-labelledby="softwareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="softwareModalLabel">In House Software</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="softwareName" class="form-label">Select Software</label>
                    <select id="softwareName" class="form-select" required>
                        <option value="" selected disabled>-- Select Software --</option>
                        <option value="OTOS WEB+">OTOS WEB+</option>
                        <option value="EDATS">EDATS</option>
                        <option value="OLDPMS">OLDPMS</option>
                        <option value="D-SIGN">D-SIGN</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label for="softwareRemarks" class="form-label">Remarks</label>
                    <textarea id="softwareRemarks" class="form-control" placeholder="Enter remarks or details" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveSoftwareDetails()">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="otherModal" tabindex="-1" aria-labelledby="otherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="otherModalLabel">Other Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="otherTitle" class="form-label">Title of Issue</label>
                    <input type="text" id="otherTitle" class="form-control" placeholder="Enter title of issue" required>
                </div>
                <div class="form-group mb-3">
                    <label for="otherRemarks" class="form-label">Remarks</label>
                    <textarea id="otherRemarks" class="form-control" placeholder="Enter remarks or details" rows="3" required></textarea>
                </div>
                <div class="form-group mb-3 text-center">
                    <label class="form-label">Scan QR to upload equipment image</label>
                    <div class="d-flex justify-content-center">
                        <img id="otherQRCode" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/upload_equipment.php?ticket=' . urlencode($ticketNumber)); ?>" alt="QR Code" class="img-fluid" style="max-width: 200px;">
                    </div>
                    <small class="text-muted d-block mt-2">Scan with your mobile device to upload equipment images</small>
                </div>
                <div class="form-group mb-3">
                    <label for="otherUpload" class="form-label">Upload Equipment Image</label>
                    <input type="file" id="otherUpload" name="otherUpload[]" class="form-control" accept="image/*" multiple>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveOtherDetails()">Save</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function updateInfo(selectedFullName) {
        var selectedOption = document.querySelector('#name option[value="' + selectedFullName + '"]');
        if (selectedOption) {
            document.getElementById('idname').value = selectedOption.getAttribute('data-idname') ? selectedOption.getAttribute('data-idname') : "";
            document.getElementById('divSecUnit').value = selectedOption.getAttribute('data-divsecunit') ? selectedOption.getAttribute('data-divsecunit') : "";
            document.getElementById('position').value = selectedOption.getAttribute('data-position') ? selectedOption.getAttribute('data-position') : "";
            document.getElementById('contactNumber').value = selectedOption.getAttribute('data-contact') ? selectedOption.getAttribute('data-contact') : "";
            document.getElementById('station').value = selectedOption.getAttribute('data-station') ? selectedOption.getAttribute('data-station') : "";
        } else {
            document.getElementById('idname').value = "";
            document.getElementById('divSecUnit').value = "";
            document.getElementById('position').value = "";
            document.getElementById('contactNumber').value = "";
            document.getElementById('station').value = "";
        }
    }

    function clearZoomHidden() {
        document.getElementById('zoomTitle_hidden').value = '';
        document.getElementById('zoomDateTime_hidden').value = '';
        document.getElementById('zoomMeetingId_hidden').value = '';
        document.getElementById('zoomPassword_hidden').value = '';
        document.getElementById('zoomBlended_hidden').value = '';
        document.getElementById('zoomRemarks_hidden').value = '';
    }

    function clearEmailHidden() {
        document.getElementById('emailTo_hidden').value = '';
        document.getElementById('emailSubject_hidden').value = '';
        document.getElementById('emailRemarks_hidden').value = '';
    }

    function clearSoftwareHidden() {
        document.getElementById('softwareName_hidden').value = '';
        document.getElementById('softwareRemarks_hidden').value = '';
    }

    function clearOtherHidden() {
        document.getElementById('otherTitle_hidden').value = '';
        document.getElementById('otherRemarks_hidden').value = '';
    }

    function clearTaHidden() {
        document.getElementById('taEquipmentId_hidden').value = '';
        document.getElementById('taDescription_hidden').value = '';
    }

    function clearBorrowHidden() {
        document.getElementById('borrowName_hidden').value = '';
        document.getElementById('borrowDivSec_hidden').value = '';
        document.getElementById('borrowDescription_hidden').value = '';
        document.getElementById('borrowDate_hidden').value = '';
        document.getElementById('borrowReturnDate_hidden').value = '';
    }

    function showEquipmentSearch() {
        var equipModal = new bootstrap.Modal(document.getElementById('searchEquipmentModal'));
        equipModal.show();
    }

    function showOtherSpecify() {
        var requestType = document.getElementById("requestType").value;
        var descriptionInput = document.getElementById('description');
        var prevType = showOtherSpecify._prevType || '';

        if (prevType !== requestType) {
            descriptionInput.value = "";
            toggleSubmitBtn();
            if (prevType === "Zoom") clearZoomHidden();
            if (prevType === "Email") clearEmailHidden();
            if (prevType === "In House Software") clearSoftwareHidden();
            if (prevType === "Other") clearOtherHidden();
            if (prevType === "Technical Assistance") clearTaHidden();
            if (prevType === "Asset/Borrow") clearBorrowHidden();
        }
        showOtherSpecify._prevType = requestType;

        if (requestType === "Zoom") {
            var zoomModal = new bootstrap.Modal(document.getElementById('zoomModal'));
            zoomModal.show();
        } else if (requestType === "Technical Assistance") {
            var equipInfoModal = new bootstrap.Modal(document.getElementById('equipmentInfoModal'));
            equipInfoModal.show();
        } else if (requestType === "Asset/Borrow") {
            var equipModal = new bootstrap.Modal(document.getElementById('searchEquipmentModal'));
            equipModal.show();
        } else if (requestType === "Email") {
            var emailModal = new bootstrap.Modal(document.getElementById('emailModal'));
            emailModal.show();
        } else if (requestType === "In House Software") {
            var softwareModal = new bootstrap.Modal(document.getElementById('softwareModal'));
            softwareModal.show();
        } else if (requestType === "Other") {
            var otherModal = new bootstrap.Modal(document.getElementById('otherModal'));
            otherModal.show();
        }
    }

    function formatDateTime(dt) {
        if (!dt) return 'N/A';
        var d = new Date(dt);
        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        var hours = d.getHours();
        var minutes = d.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12 || 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear() + ' at ' + hours + ':' + minutes + ' ' + ampm;
    }

    function saveZoomDetails() {
        var title = document.getElementById('zoomTitle').value;
        var dateTime = document.getElementById('zoomDateTime').value;
        var meetingId = document.getElementById('zoomMeetingId').value;
        var password = document.getElementById('zoomPassword').value;
        var blended = document.getElementById('zoomBlended').value;
        var remarks = document.getElementById('zoomRemarks').value;

        document.getElementById('zoomTitle_hidden').value = title;
        document.getElementById('zoomDateTime_hidden').value = dateTime;
        document.getElementById('zoomMeetingId_hidden').value = meetingId;
        document.getElementById('zoomPassword_hidden').value = password;
        document.getElementById('zoomBlended_hidden').value = blended;
        document.getElementById('zoomRemarks_hidden').value = remarks;

        var formattedDateTime = formatDateTime(dateTime);

        var description = '';
        description += 'Meeting Title: ' + (title || 'N/A') + '\n';
        description += 'Date & Time: ' + formattedDateTime + '\n';
        description += 'Meeting ID: ' + (meetingId || 'N/A') + '\n';
        description += 'Password: ' + (password || 'N/A') + '\n';
        description += 'Type: ' + blended + '\n';
        if (remarks) {
            description += 'Remarks: ' + remarks + '\n';
        }
        document.getElementById('description').value = description;
        toggleSubmitBtn();

        var zoomModalEl = document.getElementById('zoomModal');
        var zoomModal = bootstrap.Modal.getInstance(zoomModalEl);
        zoomModal.hide();
    }

    function saveEmailDetails() {
        var to = document.getElementById('emailTo').value;
        var subject = document.getElementById('emailSubject').value;
        var remarks = document.getElementById('emailRemarks').value;

        document.getElementById('emailTo_hidden').value = to;
        document.getElementById('emailSubject_hidden').value = subject;
        document.getElementById('emailRemarks_hidden').value = remarks;

        var description = '';
        description += 'Recipient: ' + (to || 'N/A') + '\n';
        if (subject) description += 'Subject: ' + subject + '\n';
        if (remarks) description += 'Remarks: ' + remarks + '\n';
        document.getElementById('description').value = description;
        toggleSubmitBtn();

        var emailModalEl = document.getElementById('emailModal');
        var emailModal = bootstrap.Modal.getInstance(emailModalEl);
        emailModal.hide();
    }

    function saveSoftwareDetails() {
        var software = document.getElementById('softwareName').value;
        var remarks = document.getElementById('softwareRemarks').value;

        document.getElementById('softwareName_hidden').value = software;
        document.getElementById('softwareRemarks_hidden').value = remarks;

        var description = '';
        description += 'Software: ' + software + '\n';
        description += 'Remarks: ' + remarks + '\n';
        document.getElementById('description').value = description;
        toggleSubmitBtn();

        var softwareModalEl = document.getElementById('softwareModal');
        var softwareModal = bootstrap.Modal.getInstance(softwareModalEl);
        softwareModal.hide();
    }

    function saveOtherDetails() {
        var title = document.getElementById('otherTitle').value;
        var remarks = document.getElementById('otherRemarks').value;

        document.getElementById('otherTitle_hidden').value = title;
        document.getElementById('otherRemarks_hidden').value = remarks;

        var description = '';
        description += 'Title of Issue: ' + title + '\n';
        description += 'Remarks: ' + remarks + '\n';
        document.getElementById('description').value = description;
        toggleSubmitBtn();

        var otherModalEl = document.getElementById('otherModal');
        var otherModal = bootstrap.Modal.getInstance(otherModalEl);
        otherModal.hide();
    }

    function saveTechnicalAssistance() {
        var description = document.getElementById('taDescription').value;
        if (!description.trim()) {
            showToast('errorToast', 'Please enter a description of the issue.');
            return;
        }

        var data = window._taEquipmentData || {};

        var fullDescription = '';
        fullDescription += 'Equipment ID: ' + (data.id || 'N/A') + '\n\n';
        fullDescription += 'Equipment: ' + (data.specs || 'N/A') + ' (S/N: ' + (data.serial || 'N/A') + ') - User: ' + (data.name || 'N/A') + '\n\n';
        fullDescription += '\nDescription:\n' + description;

        document.getElementById('description').value = fullDescription;
        document.getElementById('taEquipmentId_hidden').value = data.id || '';
        document.getElementById('taDescription_hidden').value = description;
        toggleSubmitBtn();

        var taModalEl = document.getElementById('technicalAssistanceModal');
        var taModal = bootstrap.Modal.getInstance(taModalEl);
        taModal.hide();
    }

    function saveBorrowDetails() {
        var name = document.getElementById('borrowName').value;
        var divSec = document.getElementById('borrowDivSec').value;
        var reason = document.getElementById('borrowDescription').value;
        var dateBorrowed = document.getElementById('borrowDate').value;
        var dateReturn = document.getElementById('borrowReturnDate').value;

        if (!name.trim() || !divSec.trim() || !reason.trim() || !dateBorrowed || !dateReturn) {
            showToast('errorToast', 'Please fill in all fields.');
            return;
        }

        var data = window._borrowEquipmentData || {};

        document.getElementById('borrowName_hidden').value = name;
        document.getElementById('borrowDivSec_hidden').value = divSec;
        document.getElementById('borrowDescription_hidden').value = reason;
        document.getElementById('borrowDate_hidden').value = dateBorrowed;
        document.getElementById('borrowReturnDate_hidden').value = dateReturn;

        var fullDescription = '';
        fullDescription += 'Equipment ID: ' + (data.id || 'N/A') + '\n\n';
        fullDescription += 'Equipment: ' + (data.specs || 'N/A') + ' (S/N: ' + (data.serial || 'N/A') + ') - User: ' + (data.name || 'N/A') + '\n\n';
        fullDescription += 'Borrower\'s Name: ' + name + '\n';
        fullDescription += 'Division/Section: ' + divSec + '\n';
        fullDescription += 'Date Borrowed: ' + dateBorrowed + '\n';
        fullDescription += 'Date to Return: ' + dateReturn + '\n\n';
        fullDescription += 'Reason for Borrowing:\n' + reason;

        document.getElementById('description').value = fullDescription;
        toggleSubmitBtn();

        var borrowModalEl = document.getElementById('borrowModal');
        var borrowModal = bootstrap.Modal.getInstance(borrowModalEl);
        borrowModal.hide();
    }

    const sections = document.querySelectorAll('.form-section');
    const progressSteps = document.querySelectorAll('.progress-step-item');
    let currentSection = 0;

    function showSection(index) {
        sections.forEach((section, i) => {
            section.classList.toggle('active', i === index);
        });

        progressSteps.forEach((step, i) => {
            step.classList.toggle('active', i === index);
            step.classList.toggle('completed', i < index);
        });
        if (index === 2) { 
             progressSteps[2].classList.add('active');
        } else if (index < 2) {
            progressSteps[2].classList.remove('active', 'completed');
        }

        document.getElementById('prevBtn').disabled = index === 0;
        document.getElementById('nextBtn').style.display = index === sections.length - 1 ? 'none' : 'inline-block';
        document.getElementById('submitBtn').style.display = index === sections.length - 1 ? 'inline-block' : 'none';
    }

    function validateAndProceed() {
        const currentActiveSection = sections[currentSection];
        const inputs = currentActiveSection.querySelectorAll('input:not([type="hidden"]), select, textarea');
        let allValid = true;
        var missing = [];

        inputs.forEach(input => {
            if (input.hasAttribute('required') && input.value.trim() === '') {
                allValid = false;
                input.classList.add('is-invalid');
                var label = document.querySelector('label[for="' + input.id + '"]');
                missing.push(label ? label.textContent.trim() : input.id);
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        if (currentSection === 1) {
            const descriptionInput = document.getElementById('description');
            if (descriptionInput.value.trim() === '' || descriptionInput.value.trim() === `Meeting Title:\nDate & Time:\nBlended or Online:`) {
                allValid = false;
                descriptionInput.classList.add('is-invalid');
                missing.push('Description of Request');
            } else {
                descriptionInput.classList.remove('is-invalid');
            }
        }
        
        const emailInput = document.getElementById('email');
        if (currentSection === 0 && emailInput && (!emailInput.value.includes('@') || emailInput.value.trim() === '')) {
            allValid = false;
            emailInput.classList.add('is-invalid');
            missing.push('Email Address');
        } else if (currentSection === 0 && emailInput) {
            emailInput.classList.remove('is-invalid');
        }
        
        const nameSelect = document.getElementById('name');
        if (currentSection === 0 && nameSelect && nameSelect.value.trim() === '') {
            allValid = false;
            nameSelect.classList.add('is-invalid');
            missing.push('Name');
        } else if (currentSection === 0 && nameSelect) {
            nameSelect.classList.remove('is-invalid');
        }

        if (currentSection === 0) {
            var requestType = document.getElementById('requestType').value;
            if (requestType === "Zoom" && !document.getElementById('zoomDateTime_hidden').value) {
                allValid = false;
                missing.push('Zoom Meeting Details');
            } else if (requestType === "In House Software" && !document.getElementById('softwareName_hidden').value) {
                allValid = false;
                missing.push('Software Details');
            } else if (requestType === "Email" && !document.getElementById('emailTo_hidden').value) {
                allValid = false;
                missing.push('Email Details');
            } else if (requestType === "Other" && !document.getElementById('otherTitle_hidden').value) {
                allValid = false;
                missing.push('Other Request Details');
            } else if (requestType === "Technical Assistance" && !document.getElementById('equipment_id').value.trim()) {
                allValid = false;
                missing.push('Equipment Selection');
            } else if (requestType === "Asset/Borrow" && !document.getElementById('borrowName_hidden').value) {
                allValid = false;
                missing.push('Asset/Borrow Details');
            }
        }

        if (!allValid) {
            showToast('errorToast', 'Please fill in: ' + missing.join(', ') + '.');
            return; 
        }
        
        if (currentSection < sections.length - 1) {
            currentSection++;
            showSection(currentSection);
        }
    }


    function prevSection() {
        if (currentSection > 0) {
            currentSection--;
            showSection(currentSection);
        }
    }

    function handleSubmit(e) {
        e.preventDefault();

        const description = document.getElementById('description').value.toLowerCase();
        const equipmentId = document.getElementById('equipment_id').value.trim();

        const hwKeywords = ['repair', 'install', 'no display',  'blue screen', 'laptop', 'desktop', 'computer', 'printer', 'ink', 'screen', 'monitor', 'keyboard', 'format', 'slow', 'virus', 'boot', 'hardware', 'device', 'broken', 'damaged', 'malfunction', 'crash', 'freeze', 'network', 'wifi', 'driver', 'cable', 'port', 'battery', 'charger', 'power', 'replacement', 'upgrade', 'disk', 'memory', 'ram', 'gpu', 'cpu', 'motherboard', 'fan', 'overheat', 'asus', 'dell', 'hp', 'lenovo', 'acer'];

        const isHardwareRequest = hwKeywords.some(keyword => description.includes(keyword));

        if (isHardwareRequest && (equipmentId === "" || equipmentId === null)) {
            showToast('errorToast', 'For repair, installation, or hardware issues, please select an Equipment first.');
            return;
        }

        var submitConfirmModal = new bootstrap.Modal(document.getElementById('submitConfirmModal'));
        submitConfirmModal.show();
    }

    function proceedSubmit() {
        var submitConfirmModal = bootstrap.Modal.getInstance(document.getElementById('submitConfirmModal'));
        submitConfirmModal.hide();

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.7';
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
        submitBtn.classList.add('disabled');

        setTimeout(() => {
            const form = submitBtn.closest('form');
            if (form) form.submit();
        }, 2000);
    }

    document.getElementById('searchBox').addEventListener('input', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#equipmentTable tbody tr');

        rows.forEach(row => {
            let id = row.cells[0].textContent.toLowerCase();
            let name = row.cells[1].textContent.toLowerCase();
            let serialNumber = row.cells[3].textContent.toLowerCase();
            row.style.display = (id.includes(filter) || name.includes(filter) || serialNumber.includes(filter)) ? '' : 'none';
        });
    });

    document.getElementById('equipmentTable').addEventListener('click', function (e) {
        if (e.target.classList.contains('select-equipment')) {
            let equipmentId = e.target.getAttribute('data-id');
            let equipmentName = e.target.getAttribute('data-name');
            let specifications = e.target.getAttribute('data-specs');
            let serialNumber = e.target.getAttribute('data-serial');

            let displayValue = `${specifications} (S/N: ${serialNumber}) - User: ${equipmentName}`;
            document.getElementById('equipment_id').value = displayValue;

            let requestType = document.getElementById('requestType').value;

            if (requestType === 'Technical Assistance') {
                window._taEquipmentData = {
                    id: equipmentId,
                    name: equipmentName,
                    specs: specifications,
                    serial: serialNumber
                };

                let searchModalEl = document.getElementById('searchEquipmentModal');
                let searchModal = bootstrap.Modal.getInstance(searchModalEl);
                searchModal.hide();

                searchModalEl.addEventListener('hidden.bs.modal', function () {
                    let taEquipmentDisplay = document.getElementById('taEquipmentDisplay');
                    taEquipmentDisplay.innerHTML = `
                        <strong>ID:</strong> ${equipmentId}<br>
                        <strong>Specifications:</strong> ${specifications}<br>
                        <strong>Serial Number:</strong> ${serialNumber}<br>
                        <strong>User:</strong> ${equipmentName}
                    `;
                    document.getElementById('taDescription').value = '';
                    let taModal = new bootstrap.Modal(document.getElementById('technicalAssistanceModal'));
                    taModal.show();
                }, { once: true });
            } else if (requestType === 'Asset/Borrow') {
                window._borrowEquipmentData = {
                    id: equipmentId,
                    name: equipmentName,
                    specs: specifications,
                    serial: serialNumber
                };

                let searchModalEl = document.getElementById('searchEquipmentModal');
                let searchModal = bootstrap.Modal.getInstance(searchModalEl);
                searchModal.hide();

                searchModalEl.addEventListener('hidden.bs.modal', function () {
                    let borrowEquipmentDisplay = document.getElementById('borrowEquipmentDisplay');
                    borrowEquipmentDisplay.innerHTML = `
                        <strong>ID:</strong> ${equipmentId}<br>
                        <strong>Specifications:</strong> ${specifications}<br>
                        <strong>Serial Number:</strong> ${serialNumber}<br>
                        <strong>User:</strong> ${equipmentName}
                    `;
                    document.getElementById('borrowName').value = '';
                    document.getElementById('borrowDivSec').value = '';
                    document.getElementById('borrowDescription').value = '';
                    document.getElementById('borrowDate').value = '';
                    document.getElementById('borrowReturnDate').value = '';
                    let borrowModal = new bootstrap.Modal(document.getElementById('borrowModal'));
                    borrowModal.show();
                }, { once: true });
            } else {
                showToast('selectionToast', 'Equipment selected successfully!');
                let modalElement = document.getElementById('searchEquipmentModal');
                let modalInstance = bootstrap.Modal.getInstance(modalElement);
                modalInstance.hide();
            }
        }
    });

    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('uploadDocumentsInput');
    const fileListContainer = document.getElementById('fileList'); 

    dropArea.addEventListener('click', (e) => {
        if (e.target === dropArea || e.target.closest('.file-upload-area') === dropArea) {
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.classList.add('border-primary');
        dropArea.style.borderColor = '#0d6efd';
        dropArea.style.backgroundColor = '#e9f5ff';
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('border-primary');
        dropArea.style.borderColor = '#ced4da';
        dropArea.style.backgroundColor = '#f8f9fa';
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.classList.remove('border-primary');
        dropArea.style.borderColor = '#ced4da';
        dropArea.style.backgroundColor = '#f8f9fa';
        const files = e.dataTransfer.files;

        const newFileList = new DataTransfer();
        for(let i = 0; i < fileInput.files.length; i++) {
            newFileList.items.add(fileInput.files[i]);
        }
        for(let i = 0; i < files.length; i++) {
            newFileList.items.add(files[i]);
        }
        fileInput.files = newFileList.files;
        
        handleFiles(fileInput.files);
    });

    function handleFiles(files) {
        fileListContainer.innerHTML = '';
        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const li = document.createElement('div');
                li.classList.add('alert', 'alert-info', 'alert-dismissible', 'fade', 'show', 'd-flex', 'align-items-center', 'py-2', 'px-3', 'mb-1');
                li.setAttribute('role', 'alert');
                li.innerHTML = `
                    <i class="bi bi-file-earmark-fill me-2"></i>
                    <span class="text-truncate">${file.name}</span> <span class="ms-auto me-2">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                    <button type="button" class="btn-close" aria-label="Close" data-file-index="${i}"></button>
                `;
                fileListContainer.appendChild(li);
            }
        }
    }

    fileListContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-close')) {
            const indexToRemove = parseInt(e.target.getAttribute('data-file-index'));
            const dt = new DataTransfer();
            const currentFiles = fileInput.files;

            for (let i = 0; i < currentFiles.length; i++) {
                if (i !== indexToRemove) {
                    dt.items.add(currentFiles[i]);
                }
            }
            fileInput.files = dt.files;
            handleFiles(fileInput.files);
        }
    });

    function showToast(toastId, message) {
        const toastElement = document.getElementById(toastId);
        if (toastElement) {
            const toastBody = toastElement.querySelector('.toast-body');
            if (toastBody) {
                toastBody.textContent = message;
            }
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 5000
            });
            toast.show();
        }
    }

    function toggleSubmitBtn() {
        const requestType = document.getElementById("requestType").value.trim();
        const description = document.getElementById("description").value.trim();
        const submitBtn = document.getElementById("submitBtn");

        if (requestType !== "" && description !== "") {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }

    document.getElementById("requestType").addEventListener("change", toggleSubmitBtn);
    document.getElementById("description").addEventListener("input", toggleSubmitBtn);

    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById("submitBtn").disabled = true;
    });

</script>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                Error message here.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>  
    </div>
</div>

<div class="modal fade" id="uploadReceivedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Received</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Ticket:</strong> <span id="uploadTicket"></span></p>
                <p><strong>File:</strong> <span id="uploadFilename"></span></p>
                <div id="uploadPreview" class="text-center mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Keep Upload</button>
                <button type="button" class="btn btn-danger" id="deleteUploadBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this upload?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="submitConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info d-flex align-items-start" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                    <div>
                        <strong>Reminder:</strong><br>
                        Your request will be forwarded to your Section/Division Chief for validation and approval.
                        Once approved by the Section/Division Chief, your request will be forwarded to the RICTU Help Desk for action.
                    </div>
                </div>
                <p class="mb-0 text-muted">Do you want to proceed with submitting this request?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="proceedSubmit()">Proceed</button>
            </div>
        </div>
    </div>
</div>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    var pusher = new Pusher('98d5a35431a9fefb0370', {
      cluster: 'ap3',
      forceTLS: true
    });

    var pendingUpload = null;

    function showDeleteConfirm() {
        var confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        confirmModal.show();
    }

    function deleteUpload() {
        if (!pendingUpload) return;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_upload.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            var resp = JSON.parse(xhr.responseText);
            if (resp.success) {
                var toastEl = document.getElementById('errorToast');
                toastEl.classList.remove('bg-danger');
                toastEl.classList.add('bg-success');
                toastEl.querySelector('.toast-body').textContent = 'Upload deleted.';
                var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
                toast.show();
            } else {
                var toastEl = document.getElementById('errorToast');
                toastEl.classList.remove('bg-success');
                toastEl.classList.add('bg-danger');
                toastEl.querySelector('.toast-body').textContent = 'Failed to delete upload.';
                var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
                toast.show();
            }
        };
        xhr.send('filename=' + encodeURIComponent(pendingUpload.filename));
        pendingUpload = null;
    }

    document.getElementById('deleteUploadBtn').addEventListener('click', showDeleteConfirm);
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
        deleteUpload();
        bootstrap.Modal.getInstance(document.getElementById('uploadReceivedModal')).hide();
    });

    var channel = pusher.subscribe('upload-channel');

    channel.bind('file-uploaded', function(data) {
        pendingUpload = data;
        document.getElementById('uploadTicket').textContent = data.ticket || 'N/A';
        document.getElementById('uploadFilename').textContent = data.filename || 'N/A';

        var ext = (data.filename || '').split('.').pop().toLowerCase();
        var preview = document.getElementById('uploadPreview');
        if (['jpg', 'jpeg', 'png', 'gif'].indexOf(ext) !== -1) {
            preview.innerHTML = '<img src="uploads/' + data.filename + '" class="img-fluid rounded" style="max-height: 200px;">';
        } else {
            preview.innerHTML = '<i class="bi bi-file-earmark fs-1 text-muted"></i><p class="text-muted small">No preview available</p>';
        }

        var modal = new bootstrap.Modal(document.getElementById('uploadReceivedModal'));
        modal.show();
    });
</script>

</body>
</html>