<?php
// Database configuration
require_once 'connect.php';
require_once 'session_checker.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch data from the database
$Station = $_SESSION['StationSRF'];
$start_date = isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '';
$status_search = isset($_GET['status_search']) ? htmlspecialchars($_GET['status_search']) : '';

if (empty($start_date) && empty($end_date)) {
    $start_date = (new DateTime())->modify('-10 days')->format('Y-m-d');
    $end_date = (new DateTime())->format('Y-m-d');
}

$sql = "SELECT * FROM srf WHERE date BETWEEN ? AND ? AND status = ? AND office = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $start_date, $end_date, $status_search, $_SESSION['OfficeSRF']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .card-srf { margin-bottom: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-radius: 0.75rem; border: none; }
        .card-header { background: #fff; border-bottom: 1px solid #e9ecef; border-radius: 0.75rem 0.75rem 0 0; }
        .card-title { font-size: 1.25rem; font-weight: 600; color: #343a40; }
        .card-body { background: #f6f6f6; border-radius: 0 0 0.75rem 0.75rem; }
        .badge-status { font-size: 0.85rem; padding: 0.4em 0.8em; }
        .action-dropdown .btn { min-width: 120px; font-weight: bold; }
        .remarks-row { background: #fffbe6; padding: 1rem; margin-top: 1rem; border-radius: 0.5rem; border: 1px solid #ffecb5; }
        .chat-container { max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; background: #fff; border-radius: 0.5rem; }
        .message { margin-bottom: 0.5rem; }
        .modal-content { border-radius: 1rem; }
        .form-label { font-weight: 500; }
        .dropdown-item i { width: 1.5rem; }
        .btn-close { font-size: 1rem; }
        
        /* Star Rating Styles */
        .star-rating { display: inline-flex; align-items: center; margin-left: 10px; }
        .star-rating i { cursor: pointer; color: #ccc; font-size: 1.1rem; margin-right: 2px; transition: color 0.2s; }
        .star-rating i.hovered, .star-rating i.selected { color: #ffc107; /* Gold */ }
    </style>
</head>
<body>
<div class="container py-4">
    <h2 class="mb-4 text-center text-primary">Recent Service Requests</h2>
    <form class="row g-3 mb-4 p-3 bg-white rounded-3 shadow-sm" method="GET" action="fetchdateSRFTrecent.php" onsubmit="return validateDates();">
        <div class="col-md-3">
            <label for="start_date" class="form-label">Start Date:</label>
            <input type="date" id="start_date" name="start_date" class="form-control"
                   value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>" required>
        </div>
        <div class="col-md-3">
            <label for="end_date" class="form-label">End Date:</label>
            <input type="date" id="end_date" name="end_date" class="form-control"
                   value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>" required>
        </div>
        <div class="col-md-3">
            <label for="status_search" class="form-label">Status:</label>
            <select id="status_search" name="status_search" class="form-select" required>
                <option value="">Select Status</option>
                <option value="completed" <?php echo (isset($_GET['status_search']) && $_GET['status_search'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Assigned RICTU staff" <?php echo (isset($_GET['status_search']) && $_GET['status_search'] == 'Assigned RICTU staff') ? 'selected' : ''; ?>>Assigned RICTU staff</option>
                <option value="Now Serving" <?php echo (isset($_GET['status_search']) && $_GET['status_search'] == 'Now Serving') ? 'selected' : ''; ?>>Now Serving</option>
                <option value="Disapproved" <?php echo (isset($_GET['status_search']) && $_GET['status_search'] == 'Disapproved') ? 'selected' : ''; ?>>Disapproved</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Filter</button>
        </div>
    </form>

    <div class="row">
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $srfId = $row['id'];
                $name = $row['name'];
                $remarks = $row['Notification_remarks'];
                $documents = $row['documents'];
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card card-srf">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-ticket-alt text-primary me-2"></i> Ticket #<?php echo htmlspecialchars($row['ticketNumber']); ?>
                            </h5>
                            <span class="badge badge-status bg-<?php
                                switch ($row['status']) {
                                    case 'completed': echo 'success'; break;
                                    case 'Assigned RICTU staff': echo 'info'; break;
                                    case 'Now Serving': echo 'warning'; break;
                                    case 'Disapproved': echo 'danger'; break;
                                    default: echo 'secondary';
                                }
                            ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                        </div>
                        <div class="card-body">
                            <p class="card-text mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($row['name']); ?></p>
                            <p class="card-text mb-1"><strong>Division:</strong> <?php echo htmlspecialchars($row['divSecUnit']); ?></p>
                            <p class="card-text mb-1"><strong>Office:</strong> <?php echo htmlspecialchars($row['office']); ?></p>
                            <p class="card-text mb-1"><strong>Request Type:</strong> <?php echo htmlspecialchars($row['requestType']); ?></p>
                            <p class="card-text mb-1 d-flex align-items-center">
                                <strong>Description:</strong>&nbsp;<span><?php echo (strlen($row['description']) > 50 ? substr($row['description'], 0, 50) . "..." : $row['description']); ?></span>
                                
                                <?php if ($row['Notification_read'] != '1' && $row['tracking'] === '102'): ?>
                                    <span class="star-rating" id="rating-container-<?php echo $srfId; ?>" data-srfid="<?php echo $srfId; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                        <i class="far fa-star" data-rating="Poor" title="Poor"></i>
                                        <i class="far fa-star" data-rating="Below Satisfactory" title="Below Satisfactory"></i>
                                        <i class="far fa-star" data-rating="Satisfactory" title="Satisfactory"></i>
                                        <i class="far fa-star" data-rating="Very Satisfactory" title="Very Satisfactory"></i>
                                        <i class="far fa-star" data-rating="Excellent" title="Excellent"></i>
                                    </span>
                                <?php endif; ?>
                            </p>
                            
                            <hr class="my-3">

                            <div class="action-dropdown text-end">
                                <?php
                                if ($row['Notification_read'] == '1') {
                                    ?>
                                    <div class="dropdown">
                                        <button class="btn btn-danger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-cog me-1"></i> Action
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#history<?php echo $srfId; ?>"><i class="fas fa-history text-info me-2"></i> History</a></li>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#readnotificationchat<?php echo $srfId; ?>"><i class="fas fa-bell text-danger me-2"></i> Read Notification</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="toggleRemarks<?php echo $srfId; ?>()"><i class="fas fa-eye text-warning me-2"></i> Toggle Remarks</a></li>
                                        </ul>
                                    </div>
                                    <?php
                                } else {
                                    if ($row['tracking'] === '102') {
                                        ?>
                                        <div class="dropdown">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-cog me-1"></i> Action
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#history<?php echo $srfId; ?>"><i class="fas fa-history text-info me-2"></i> History</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="toggleRemarks<?php echo $srfId; ?>()"><i class="fas fa-eye text-warning me-2"></i> Toggle Remarks</a></li>
                                            </ul>
                                        </div>
                                        <?php
                                    } elseif ($row['tracking'] === '103') {
                                        ?>
                                        <div class="dropdown">
                                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-cog me-1"></i> Action
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#history<?php echo $srfId; ?>"><i class="fas fa-history text-success me-2"></i> History</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#read<?php echo $srfId; ?>"><i class="fas fa-book-reader text-info me-2"></i> Read</a></li>
                                                <li><a class="dropdown-item" href="mainmenu.php?dir=printform&id=<?php echo $srfId; ?>"><i class="fas fa-print text-primary me-2"></i> Print</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#print<?php echo $srfId; ?>"><i class="fas fa-eye text-info me-2"></i> View</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewupload<?php echo $srfId; ?>"><i class="fas fa-upload text-warning me-2"></i> View Uploaded</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#readnotificationchat<?php echo $srfId; ?>"><i class="fas fa-comments text-danger me-2"></i> Chat</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="toggleRemarks<?php echo $srfId; ?>()"><i class="fas fa-eye text-warning me-2"></i> Toggle Remarks</a></li>
                                            </ul>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="dropdown">
                                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-cog me-1"></i> Action
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#history<?php echo $srfId; ?>"><i class="fas fa-history text-success me-2"></i> History</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#read<?php echo $srfId; ?>"><i class="fas fa-book-reader text-info me-2"></i> Read</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#readnotificationchat<?php echo $srfId; ?>"><i class="fas fa-comments text-danger me-2"></i> Chat</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edituploadeddocuments<?php echo $srfId; ?>"><i class="fas fa-file-alt text-info me-2"></i> Documents</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="toggleRemarks<?php echo $srfId; ?>()"><i class="fas fa-eye text-warning me-2"></i> Toggle Remarks</a></li>
                                            </ul>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <div id="remarks<?php echo $srfId; ?>" class="remarks-row" style="display:none;">
                                <strong>Remarks:</strong> <?php echo htmlspecialchars($row['remarks']); ?>
                            </div>
                            <script>
                                function toggleRemarks<?php echo $srfId; ?>() {
                                    var remarksRow = document.getElementById('remarks<?php echo $srfId; ?>');
                                    remarksRow.style.display = remarksRow.style.display === 'none' ? 'block' : 'none';
                                }
                            </script>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12"><div class="alert alert-warning text-center">No records found.</div></div>';
        }
        $stmt->close();
        ?>
    </div>
</div>

<?php
// PHP loop to generate all modal HTML
$result->data_seek(0); // Reset the result pointer
while($row = $result->fetch_assoc()) {
    $srfId = $row['id'];
    $name = $row['name'];
    $remarks = $row['Notification_remarks'];
    $documents = $row['documents'];
    
    // Edit Uploaded Documents Modal
    echo '<div class="modal fade" id="edituploadeddocuments' . $srfId . '" tabindex="-1" aria-labelledby="edituploadeddocumentsLabel' . $srfId . '" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edituploadeddocumentsLabel' . $srfId . '">Edit Uploaded Documents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">';
                if (!empty($documents)) {
                    $docArray = explode(',', $documents);
                    echo '<div class="mb-3">
                        <label class="form-label">Existing Documents</label>
                        <ul class="list-group">';
                    foreach ($docArray as $doc) {
                        echo '<li class="list-group-item">
                                <a href="attached_documents/' . htmlspecialchars($doc) . '" target="_blank">' . htmlspecialchars($doc) . '</a>
                            </li>';
                    }
                    echo '</ul>
                        </div>';
                } else {
                    echo '<p class="text-muted">No documents uploaded.</p>';
                }
                echo '<form id="uploadForm' . $srfId . '" action="upload.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="documentName' . $srfId . '" class="form-label">Document Name</label>
                        <input type="text" class="form-control" id="documentName' . $srfId . '" name="documentName" placeholder="Enter document name" required>
                    </div>
                    <div class="mb-3">
                        <label for="documentFile' . $srfId . '" class="form-label">Upload File</label>
                        <input type="file" class="form-control" id="documentFile' . $srfId . '" name="documentFile" required>
                    </div>
                    <input type="hidden" name="srfId" value="' . $srfId . '">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitUploadForm(' . $srfId . ')">Save changes</button>
            </div>
        </div>
    </div>
</div>';

// Read Notification/Chat Modal
echo "<div class='modal fade' id='readnotificationchat{$srfId}' tabindex='-1' aria-labelledby='notifyLabel{$srfId}' aria-hidden='true'>
<div class='modal-dialog'>
    <div class='modal-content'>
        <div class='modal-header'>
            <h5 class='modal-title' id='notifyLabel{$srfId}'>Chat with User #{$srfId}</h5>
            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
        </div>
        <div class='modal-body'>
            <div class='chat-container' id='chatContainer{$srfId}'>
                <p>Loading messages...</p>
            </div>
            <form id='messageForm{$srfId}' method='POST'>
                <div class='mb-3'>
                    <label for='message{$srfId}' class='form-label'>Your Message</label>
                    <textarea class='form-control' id='message{$srfId}' name='message' rows='4' required></textarea>
                </div>
                <input type='hidden' name='srfId' value='{$srfId}'>
                <button type='submit' class='btn btn-primary'>Send Message</button>
            </form>
        </div>
    </div>
</div>
</div>";

// Rate Modal HAS BEEN REMOVED

// History Modal
echo "
<div class='modal fade' id='history{$srfId}' tabindex='-1' aria-hidden='true'>
    <div class='modal-dialog modal-xl modal-dialog-centered'>
        <div class='modal-content'>
            <div class='modal-header bg-secondary'>
                <h5 class='modal-title text-white'>View Details</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
            </div>

            <div class='modal-body p-0'>
                <iframe 
                    src='history.php?trackid={$srfId}'
                    style='width:100%; height:70vh; border:none;'
                    loading='lazy'>
                </iframe>
            </div>

            <div class='modal-footer'>
                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
            </div>
        </div>
    </div>
</div>";

// Disapproved Modal
echo "<div class='modal fade' id='disapproved{$srfId}' tabindex='-1' aria-hidden='true'>
    <div class='modal-dialog'>
        <form method='POST' action='disapproved.php'>
            <div class='modal-content'>
                <div class='modal-header bg-danger text-white'>
                    <h5 class='modal-title'>Disapprove Request</h5>
                    <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>
                    <p>ID: ({$srfId}) Are you sure you want to Disapprove this record?</p>
                    <input type='hidden' name='disapproved' value='{$srfId}'>
                    <div class='form-group'>
                        <label for='remarks'>Remarks</label>
                        <textarea class='form-control' id='remarks' name='remarks' rows='3' required></textarea>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                    <button type='submit' class='btn btn-danger'>Disapprove</button>
                </div>
            </div>
        </form>
    </div>
</div>";

echo '<div class="modal fade" id="print' . $srfId . '" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
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
</div>';

echo '<div class="modal fade" id="viewupload' . $srfId . '" tabindex="-1" aria-labelledby="viewuploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewuploadModalLabel">View Uploaded Documents</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe src="viewuploaded.php?id=' . $srfId . '" style="width: 100%; height: 85vh; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>';

echo "<div class='modal fade' id='approve{$srfId}' tabindex='-1' aria-hidden='true'>
    <div class='modal-dialog'>
        <form method='GET' action='approve.php'>
            <div class='modal-content'>
                <div class='modal-header bg-success text-white'>
                    <h5 class='modal-title'>Approve Request</h5>
                    <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>
                    <p>ID: ({$srfId}) Are you sure you want to approve this record? </p>
                    <input type='hidden' name='approve' value='{$srfId}'>
                    <input type='hidden' name='level' value='{$row['level']}'>
                    <input type='hidden' name='name' value='{$row['name']}'>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                    <button type='submit' class='btn btn-primary'>Approve</button>
                </div>
            </div>
        </form>
    </div>
</div>";

echo "<div class='modal fade' id='assign{$srfId}' tabindex='-1' aria-hidden='true'>
    <div class='modal-dialog'>
        <form method='GET' action='assign.php'>
            <div class='modal-content'>
                <div class='modal-header bg-secondary text-white'>
                    <h5 class='modal-title'>Assign Action</h5>
                    <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>
                    <div class='form-group'>
                        <label>ID</label>
                        <input type='text' name='assign' value='{$srfId}' class='form-control' required />
                    </div>
                    <div class='form-group'>
                        <label for='personnel'>Personnel</label>
                        <select id='personnel' name='personelid' class='form-select' required>
                            <option value=''>Select Personnel</option>";
                            $sql_personnel = "SELECT DISTINCT personelid, name FROM srfactionstaff";
                            $result_personnel = $conn->query($sql_personnel);
                            while ($personnelRow = $result_personnel->fetch_assoc()) {
                                $selected = ($personnelRow['name'] == $row['name']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($personnelRow['personelid']) . "' $selected>" . strtoupper(htmlspecialchars($personnelRow['name'])) . "</option>";
                            }
                            echo "<option value='102'>MARK AS DONE</option>
                        </select>
                    </div>
                    <div class='form-group'>
                        <label for='actionDate'>Date</label>
                        <input type='date' class='form-control' id='actionDate' name='actionDate' required>
                    </div>
                    <div class='form-group'>
                        <label for='actionTime'>Time</label>
                        <input type='time' class='form-control' id='actionTime' name='actionTime' required>
                    </div>
                    <div class='form-group'>
                        <label for='actionTaken'>Action Taken</label>
                        <textarea class='form-control' id='actionTaken' name='actionTaken' rows='3'></textarea>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                    <button type='submit' class='btn btn-primary'>Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>";

echo "<div class='modal fade' id='read{$srfId}' tabindex='-1' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header bg-info text-white'>
                <h5 class='modal-title'>View Details</h5>
                <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
            </div>
            <div class='modal-body'>
                <div class='mb-3'>
                    <label class='form-label'>Name</label>
                    <input type='text' value='{$row['name']}' class='form-control' disabled />
                </div>
                <div class='mb-3'>
                    <label class='form-label'>Request Type</label>
                    <input type='text' value='{$row['requestType']}' class='form-control' disabled />
                </div>
                <div class='mb-3'>
                    <label class='form-label'>Other</label>
                    <input type='text' value='{$row['otherSpecify']}' class='form-control' disabled />
                </div>
                <div class='mb-3'>
                    <label class='form-label'>Contact Number</label>
                    <input type='text' value='{$row['contactNumber']}' class='form-control' disabled />
                </div>
                <div class='mb-3'>
                    <label class='form-label'>Description</label>
                    <textarea class='form-control' rows='3' disabled>{$row['description']}</textarea>
                </div>
                <div class='mb-3'>
                    <label class='form-label'>Remarks</label>
                    <textarea class='form-control' rows='3' disabled>{$row['remarks']}</textarea>
                </div>
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
            </div>
        </div>
    </div>
</div>";

}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Automatic Star Rating logic
    const ratingContainers = document.querySelectorAll('.star-rating');
    
    ratingContainers.forEach(container => {
        const stars = container.querySelectorAll('i');
        const srfId = container.dataset.srfid;
        const userName = container.dataset.name;

        stars.forEach((star, index) => {
            // Hover effect to fill stars
            star.addEventListener('mouseover', () => {
                stars.forEach((s, i) => {
                    if (i <= index) {
                        s.classList.remove('far');
                        s.classList.add('fas', 'hovered');
                    } else {
                        s.classList.remove('fas', 'hovered');
                        s.classList.add('far');
                    }
                });
            });

            // Remove fill when mouse leaves the rating area
            container.addEventListener('mouseout', () => {
                stars.forEach(s => {
                    s.classList.remove('fas', 'hovered');
                    s.classList.add('far');
                });
            });

            // Handle the click (submit the rating via AJAX)
            star.addEventListener('click', () => {
                const ratingValue = star.dataset.rating;
                
                // Construct form data exactly as the old modal did
                const formData = new FormData();
                formData.append('srf_id', srfId);
                formData.append('feedback', ratingValue);
                formData.append('acknowledged_by', userName);
                
                // Add an extra flag in case you want to adapt rate.php to handle AJAX without full page redirects
                formData.append('ajax_request', '1');

                // Send the background POST request to rate.php
                fetch('rate.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Assuming success, replace the stars with a confirmed badge to prevent duplicate submits
                    container.innerHTML = `<span class="badge bg-success ms-2"><i class="fas fa-check me-1"></i>Rated: ${ratingValue}</span>`;
                })
                .catch(error => {
                    console.error('Error submitting rating:', error);
                    alert('There was a problem submitting your rating. Please try again.');
                });
            });
        });
    });

    // Handle chat modal events
    document.querySelectorAll('[id^=readnotificationchat]').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function (event) {
            const srfId = this.id.replace('readnotificationchat', '');
            const chatContainer = document.getElementById('chatContainer' + srfId);
            const messageForm = document.getElementById('messageForm' + srfId);
            
            function fetchMessages() {
                fetch('getMessagesUser.php?srfId=' + srfId)
                    .then(response => response.json())
                    .then(data => {
                        chatContainer.innerHTML = '';
                        data.forEach(msg => {
                            chatContainer.innerHTML += `
                                <div class='message'>
                                    <strong>${msg.sender}:</strong> ${msg.message}<br>
                                    <small class='text-muted'>${msg.created_at}</small>
                                </div><hr>`;
                        });
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    })
                    .catch(error => console.error('Error fetching messages:', error));
            }

            fetchMessages();

            messageForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(messageForm);
                fetch('sendMessage.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    messageForm.reset();
                    fetchMessages();
                })
                .catch(error => console.error('Error sending message:', error));
            });
        });
    });
});

function submitUploadForm(srfId) {
    var form = document.getElementById('uploadForm' + srfId);
    var formData = new FormData(form);
    fetch('upload.php', { method: 'POST', body: formData })
    .then(response => response.text())
    .then(data => {
        alert(data);
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while uploading the file.');
    });
}

window.addEventListener('beforeunload', function() {
    sessionStorage.setItem('scrollPosition', window.scrollY);
});
window.addEventListener('DOMContentLoaded', function() {
    var scrollPosition = sessionStorage.getItem('scrollPosition');
    if (scrollPosition !== null) {
        window.scrollTo(0, parseInt(scrollPosition, 10));
    }
});
</script>
</body>
</html>