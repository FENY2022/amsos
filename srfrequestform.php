<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request Form (SRF)</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); /* Stronger, softer shadow */
            overflow: hidden;
            width: 100%;
            max-width: 650px; /* Adjusted max-width */
            margin: 40px auto; /* Center horizontally with margin */
        }

        .card-header {
            background-color: #ffffff; /* White header background */
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
            margin-bottom: 35px; /* Increased margin */
            position: relative;
            padding: 0; /* Remove default ul padding */
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e9ecef; /* Lighter grey line */
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
            background-color: #e9ecef; /* Light grey for inactive */
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
            background-color: #0d6efd; /* Bootstrap primary blue */
            color: white;
            border-color: #0d6efd;
        }

        .progress-step-item.completed .progress-step-circle {
            background-color: #198754; /* Bootstrap success green */
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
            margin-bottom: 1.25rem; /* Consistent spacing */
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem; /* Space between label and input */
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
            display: flex; /* Use flexbox for centering content */
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .file-upload-area:hover {
            border-color: #0d6efd; /* Primary blue on hover */
            background-color: #e9f5ff; /* Light blue tint on hover */
        }

        .file-upload-area i.bi-cloud-arrow-up {
            font-size: 3.5rem; /* Larger icon */
            color: #0d6efd;
            margin-bottom: 15px;
        }

        .file-upload-area p {
            font-size: 1.1rem;
            color: #495057;
            margin-bottom: 5px; /* Reduce space between paragraphs */
        }

        .file-upload-area p:last-of-type {
            margin-bottom: 15px; /* Space before button */
        }

        .file-upload-area .btn-browse {
            background-color: #0d6efd;
            color: white;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            display: inline-flex; /* Align icon and text */
            align-items: center;
            gap: 8px; /* Space between icon and text */
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
            background-color: #e0f2f7; /* Light blue for file list items */
            border-color: #a7d9ee;
            color: #084298; /* Dark blue text */
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        #fileList .alert .btn-close {
            filter: invert(30%) sepia(80%) saturate(2000%) hue-rotate(180deg) brightness(80%) contrast(90%); /* Darker close button for light background */
        }

        /* Buttons at the bottom */
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .button-group .btn {
            min-width: 130px; /* Consistent button width */
            border-radius: 8px;
            padding: 12px 20px; /* Larger padding */
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none; /* Remove underline for anchor-like buttons if they were anchors */
        }

        .button-group #prevBtn {
            background-color: #6c757d; /* Darker grey for previous */
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
            background-color: #0d6efd; /* Primary blue */
            border-color: #0d6efd;
            color: white;
        }
        .button-group #nextBtn:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .button-group #submitBtn {
            background-color: #198754; /* Success green */
            border-color: #198754;
            color: white;
        }
        .button-group #submitBtn:hover {
            background-color: #157347;
            border-color: #146c43;
        }
        .button-group #submitBtn.disabled {
            opacity: 0.7; /* Visual feedback when disabled/submitting */
        }

        /* Modal specific styles */
        .modal-content {
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: none;
            padding: 20px 25px;
            background-color: #f8f9fa; /* Light header background for modal */
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
            background-color: #f0f2f5; /* Light grey for table header */
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #e2e6ea; /* Lighter grey on row hover */
        }

        .table td {
            vertical-align: middle;
            font-size: 0.95rem;
        }

        .table .btn.select-equipment {
            border-radius: 6px;
            padding: 6px 15px;
            font-size: 0.9rem;
            background-color: #28a745; /* Green for select button */
            border-color: #28a745;
        }
        .table .btn.select-equipment:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .wrap-text {
            white-space: normal;
            word-wrap: break-word;
            max-width: 250px; /* Adjust as needed for modal table */
        }

        /* Toast Notification */
        .toast-container {
            z-index: 1050; /* Ensure toast is above modals */
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
            filter: invert(100%); /* Make close button white for dark toasts */
        }
    </style>
</head>
<body>

<?php
// All your existing PHP code remains untouched here
require_once "session_checker.php";

function generateTicketNumber() {
    // Initialize session variable if not set
    if (!isset($_SESSION['ticket_number'])) {
        $_SESSION['ticket_number'] = 1;
    }
    // Format the date (adjust format as needed)
    $date = date('Ymd');

    // Generate a unique identifier
    $uniqidPart = substr(uniqid(), -5); // Take last 5 characters for brevity

    // Combine components
    $ticketNumber = $date . '-' . sprintf('%03d', $_SESSION['ticket_number']) . '-' . $uniqidPart;

    // Increment the counter for the next ticket
    $_SESSION['ticket_number']++;

    return $ticketNumber;
}

// Generate and display the ticket number
$ticketNumber = generateTicketNumber();
$date = date('Y-m-d');

// Assuming "toast.php" contains a function like showToast or similar mechanism
// If it directly echoes HTML, you might need to adjust it or remove it if using Bootstrap's toast.
// For this example, I'll rely on a JS showToast function for Bootstrap toasts.
// require_once "toast.php";
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
                    require_once 'connect_otos.php'; // Your original backend connection
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
                <input type="hidden" id="idname" name="idname" required> <div class="form-group">
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
                    <label for="requestType" class="form-label">Request Type</label>
                    <select id="requestType" name="requestType" onchange="showOtherSpecify()" class="form-select" required>
                        <option value="" selected disabled>-- Select Request Type --</option>
                        <option value="Zoom">Zoom</option>
                        <option value="Technical Assistance">Technical Assistance</option>
                        <option value="Asset/Borrow">Asset/Borrow</option>
                        <option value="Email">Email</option>
                        <option value="In House Software">In House Software</option>
                        <option value="Otos Web+">Otos Web+</option>
                        <option value="E-Dats">E-Dats</option>
                        <option value="Other">Other (Specify)</option>
                    </select>
                    <input type="text" id="otherSpecify" name="otherSpecify" class="form-control other-specify" placeholder="Please specify" style="display:none;">
                </div>


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
                        <!-- <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button> -->
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
                        require_once 'connect.php'; // Your original backend connection
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
                        $conn->close(); // Close the connection
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // JS for updating info based on name selection (your original logic)
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

    // JS for showing "Other Specify" field and updating description
function showOtherSpecify() {
    var requestType = document.getElementById("requestType").value;
    var descriptionInput = document.getElementById('description');

    // Reset description to a base template
    descriptionInput.value = "";

    if (requestType === "Zoom") {
        descriptionInput.value = `Meeting Title:\nDate & Time:\nBlended or Online:\n\n`;
    }
}


    // Multi-step form logic (your original core logic with UI updates)
    const sections = document.querySelectorAll('.form-section');
    const progressSteps = document.querySelectorAll('.progress-step-item'); // Get step items
    let currentSection = 0;

    function showSection(index) {
        sections.forEach((section, i) => {
            section.classList.toggle('active', i === index);
        });

        // Update progress step visual states
        progressSteps.forEach((step, i) => {
            step.classList.toggle('active', i === index);
            step.classList.toggle('completed', i < index);
        });
        // Special handling for step 3 if it's the target (though logic only has 2 steps)
        if (index === 2) { // If 'next' button is pressed on section 2, it means we're conceptually on 'step 3' for submission
             progressSteps[2].classList.add('active'); // Mark step 3 active for submission
        } else if (index < 2) {
            progressSteps[2].classList.remove('active', 'completed'); // Ensure step 3 is reset
        }


        document.getElementById('prevBtn').disabled = index === 0;
        document.getElementById('nextBtn').style.display = index === sections.length - 1 ? 'none' : 'inline-block';
        document.getElementById('submitBtn').style.display = index === sections.length - 1 ? 'inline-block' : 'none';
    }

    // Validation for moving to the next section
    function validateAndProceed() {
        const currentActiveSection = sections[currentSection];
        const inputs = currentActiveSection.querySelectorAll('input:not([type="hidden"]), select, textarea');
        let allValid = true;

        inputs.forEach(input => {
            if (input.hasAttribute('required') && input.value.trim() === '') {
                allValid = false;
                input.classList.add('is-invalid'); // Add Bootstrap validation style
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        // Specific validation for Description field on the second step
        if (currentSection === 1) {
            const descriptionInput = document.getElementById('description');
            if (descriptionInput.value.trim() === '' || descriptionInput.value.trim() === `Meeting Title:\nDate & Time:\nBlended or Online:`) {
                allValid = false;
                descriptionInput.classList.add('is-invalid');
            } else {
                descriptionInput.classList.remove('is-invalid');
            }
        }
        
        // Specific email format validation
        const emailInput = document.getElementById('email');
        if (currentSection === 0 && emailInput && (!emailInput.value.includes('@') || emailInput.value.trim() === '')) {
            allValid = false;
            emailInput.classList.add('is-invalid');
        } else if (currentSection === 0 && emailInput) {
            emailInput.classList.remove('is-invalid');
        }
        
        const nameSelect = document.getElementById('name');
        if (currentSection === 0 && nameSelect && nameSelect.value.trim() === '') {
            allValid = false;
            nameSelect.classList.add('is-invalid');
        } else if (currentSection === 0 && nameSelect) {
            nameSelect.classList.remove('is-invalid');
        }


        if (!allValid) {
            showToast('errorToast', 'Please fill in all required fields.');
            return; // Stop execution if any validation fails
        }
        
        // Proceed to next section if valid
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

    // Your original handleSubmit function
    function handleSubmit(e) {
        // Prevent immediate form submission
        e.preventDefault();

        // Get the submit button
        const submitBtn = e.target;

        // Disable the button immediately
        submitBtn.disabled = true;

        // Add visual feedback (optional)
        submitBtn.style.opacity = '0.7';
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
        submitBtn.classList.add('disabled'); // Add Bootstrap disabled class

        // Submit after 2 seconds
        setTimeout(() => {
            // Get the form element
            const form = submitBtn.closest('form');

            // Submit the form
            if (form) form.submit();
        }, 2000);
    }

    // Modal Search functionality (your original logic)
    document.getElementById('searchBox').addEventListener('input', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#equipmentTable tbody tr');

        rows.forEach(row => {
            let id = row.cells[0].textContent.toLowerCase();
            let name = row.cells[1].textContent.toLowerCase();
            let serialNumber = row.cells[3].textContent.toLowerCase(); // Also search by serial number
            row.style.display = (id.includes(filter) || name.includes(filter) || serialNumber.includes(filter)) ? '' : 'none';
        });
    });

    // Select equipment from modal (your original logic, enhanced with more detail)
    document.getElementById('equipmentTable').addEventListener('click', function (e) {
        if (e.target.classList.contains('select-equipment')) {
            let equipmentId = e.target.getAttribute('data-id');
            let equipmentName = e.target.getAttribute('data-name');
            let specifications = e.target.getAttribute('data-specs');
            let serialNumber = e.target.getAttribute('data-serial');

            // Update the main form's equipment field with more details as requested by the screenshot.
            // You can adjust this format as needed.
            document.getElementById('equipment_id').value = `${specifications} (S/N: ${serialNumber}) - User: ${equipmentName}`;

            // Show success toast
            showToast('selectionToast', 'Equipment selected successfully!');

            // Close the modal
            let modalElement = document.getElementById('searchEquipmentModal');
            let modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide();
        }
    });

    // File Upload Drag & Drop and Preview
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('uploadDocumentsInput');
    const fileListContainer = document.getElementById('fileList'); // Renamed from fileList to fileListContainer for clarity

    // Click on drop area triggers file input
    dropArea.addEventListener('click', (e) => {
        // Only trigger if click is directly on dropArea or its children other than actual input/button
        if (e.target === dropArea || e.target.closest('.file-upload-area') === dropArea) {
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.classList.add('border-primary'); // Highlight on drag over
        dropArea.style.borderColor = '#0d6efd'; // Custom hover color
        dropArea.style.backgroundColor = '#e9f5ff';
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('border-primary'); // Remove highlight on drag leave
        dropArea.style.borderColor = '#ced4da'; // Reset color
        dropArea.style.backgroundColor = '#f8f9fa';
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.classList.remove('border-primary');
        dropArea.style.borderColor = '#ced4da'; // Reset color
        dropArea.style.backgroundColor = '#f8f9fa';
        const files = e.dataTransfer.files;

        // Create a new FileList from dropped files and existing files
        const newFileList = new DataTransfer();
        // Add existing files
        for(let i = 0; i < fileInput.files.length; i++) {
            newFileList.items.add(fileInput.files[i]);
        }
        // Add new dropped files
        for(let i = 0; i < files.length; i++) {
            newFileList.items.add(files[i]);
        }
        fileInput.files = newFileList.files; // Assign updated FileList
        
        handleFiles(fileInput.files); // Update visual list with all files
    });

    function handleFiles(files) {
        fileListContainer.innerHTML = ''; // Clear previous list
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

    // Function to remove a file from the DataTransfer object
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
            fileInput.files = dt.files; // Update the actual file input
            handleFiles(fileInput.files); // Re-render the list
        }
    });


    // Custom Toast Function for Bootstrap
    function showToast(toastId, message) {
        const toastElement = document.getElementById(toastId);
        if (toastElement) {
            const toastBody = toastElement.querySelector('.toast-body');
            if (toastBody) {
                toastBody.textContent = message;
            }
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 5000 // Display for 5 seconds
            });
            toast.show();
        }
    }

        // Enable/disable submit button based on Request Type + Description
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

        // Watch for changes
        document.getElementById("requestType").addEventListener("change", toggleSubmitBtn);
        document.getElementById("description").addEventListener("input", toggleSubmitBtn);

        // Disable submit on page load
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

</body>
</html>
