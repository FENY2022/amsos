<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<?php



require_once 'connect.php';
require_once 'session_checker.php';




// Retrieve form data
$ticketNumber = $_POST['ticketNumber'];
$date = $_POST['date'];
$name = $_POST['name'];
$idname = $_POST['idname'];
$divSecUnit = $_POST['divSecUnit'];
$position = $_POST['position'];
$contactNumber = $_POST['contactNumber'];
$email = $_POST['email'];
$requestType = $_POST['requestType'];
$otherSpecify = $_POST['otherSpecify'] ?? '';
$description = $_POST['description'];
$station = $_POST['station'];
$status = "On Process";
$office = $_SESSION['OfficeSRF'];
$Endsrd = "";
$level = 1;

// Handle equipment ID
$equipmentId = isset($_POST['equipment_id']) ? filter_var($_POST['equipment_id'], FILTER_SANITIZE_NUMBER_INT) : "";

// Handle file uploads
$uploadedFiles = [];
$uploadDir = 'attached_documents/';

// Create directory if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Check if files were uploaded
if (!empty($_FILES['uploadDocuments']['name'][0])) {
    foreach ($_FILES['uploadDocuments']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['uploadDocuments']['name'][$key]);
        $filePath = $uploadDir . uniqid() . '_' . $fileName; // Add a unique ID to avoid name collisions
        
               
        // Check for errors
        if ($_FILES['uploadDocuments']['error'][$key] !== UPLOAD_ERR_OK) {
            echo "Error uploading file: " . $_FILES['uploadDocuments']['name'][$key];
            continue;
        }

        // Validate file type and size (optional)
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf']; // Add allowed MIME types
        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        if (!in_array($_FILES['uploadDocuments']['type'][$key], $allowedTypes)) {
            echo "Invalid file type: " . $_FILES['uploadDocuments']['name'][$key];
            continue;
        }

        if ($_FILES['uploadDocuments']['size'][$key] > $maxFileSize) {
            echo "File too large: " . $_FILES['uploadDocuments']['name'][$key];
            continue;
        }

        // Move uploaded file to the target directory
        if (move_uploaded_file($tmpName, $filePath)) {
            $uploadedFiles[] = $filePath;
        } else {
            echo "Failed to move uploaded file: " . $_FILES['uploadDocuments']['name'][$key];
        }
    }
}

// Convert uploaded file paths to a comma-separated string for database storage
// Remove "attached_documents/" from each file path
$uploadedFiles = array_map(function($file) {
    return str_replace("attached_documents/", "", $file);
}, $uploadedFiles);

// Implode the array into a comma-separated string
$documents = implode(',', $uploadedFiles);




// First Query: Signatory Setup
$sql = "SELECT * FROM signatory_setup WHERE Station = ? AND date_endservice = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $station, $Endsrd);
$stmt->execute();
$result = $stmt->get_result();
$results = $result->fetch_all(MYSQLI_ASSOC);

if ($results) {
    foreach ($results as $row) {
        $_SESSION['StationSRF'] = htmlspecialchars($row['Station']);
    }
} else {
    echo '<tr><td colspan="number_of_columns">No records found</td></tr>';
}

$stmt->close();

$NID = 1; // Changed to an integer
$stationID = $_SESSION['StationidSRF'];

// Second Query: SRF Signer
$sql = "SELECT * FROM srfsigner WHERE stationid = ? AND level = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $stationID, $NID);
$stmt->execute();
$result = $stmt->get_result();
$results = $result->fetch_all(MYSQLI_ASSOC);

if ($results) {
    foreach ($results as $row) {
        $tracking = htmlspecialchars($row['personelid']);
    }
}

$stmt->close();

// Insert data into the database
$sql = "INSERT INTO srf (ticketNumber, date, name, idname, divSecUnit, position, contactNumber, email, requestType, otherSpecify, description, tracking, status, office, level, station, equipment_id, documents) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssissisis", $ticketNumber, $date, $name, $idname, $divSecUnit, $position, $contactNumber, $email, $requestType, $otherSpecify, $description, $tracking, $status, $office, $level, $station, $equipmentId, $documents);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $srfId = $stmt->insert_id; // Get the last inserted ID

    echo '
    <div class="modal fade show" id="receive_read' . $srfId . '" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog" style="max-width: 80%; height: 50vh;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printModalLabel">View Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe src="printform.php?id=' . $srfId . '" style="width: 100%; height: 85vh; border: none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        var myModal = new bootstrap.Modal(document.getElementById("receive_read' . $srfId . '"));
        myModal.show();
    
        // Redirect to request list when the modal is closed
        document.getElementById("receive_read' . $srfId . '").addEventListener("hidden.bs.modal", function () {
            window.top.location.href = "mainmenu.php?dir=srfactiontaken";

        });
    </script>';
    
} else {
    echo '<script>
        alert("Error submitting the form. Please try again.");
        window.location.href = "mainmenu.php?dir=srfrequestform";
    </script>';
}


$stmt->close();
$conn->close();

?>
