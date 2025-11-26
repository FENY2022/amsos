<?php
// Database configuration
require_once 'connect.php';
require_once 'session_checker.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// This script assumes a session is already started.
// If not, you would need session_start(); here.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request Dashboard</title>
    
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-gray: #f8f9fa;
            --medium-gray: #ecf0f1;
            --dark-gray: #555;
            --font-family: 'Poppins', sans-serif;
            --card-shadow: 0 8px 25px rgba(0,0,0,0.1);
            --card-hover-shadow: 0 16px 35px rgba(0,0,0,0.15);
        }

        body {
            background-color: var(--light-gray);
            font-family: var(--font-family);
            color: var(--dark-gray);
        }

        .container-fluid {
            padding: 2.5rem;
        }

        .dashboard-header {
            margin-bottom: 3rem;
            text-align: center;
        }

        .dashboard-header h1 {
            font-weight: 700;
            color: var(--secondary-color);
        }

        .request-card {
            background-color: #ffffff;
            border: 0;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            margin-bottom: 2.5rem;
            transition: all 0.3s ease-in-out;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .request-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-hover-shadow);
        }
        
        /* Bring card to front when its dropdown is active */
        .request-card.dropdown-active {
            z-index: 10;
        }

        .request-card .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--medium-gray);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .ticket-id {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .request-card .card-body {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .card-title {
            font-weight: 600;
            color: var(--secondary-color);
        }

        .request-info {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
            color: var(--dark-gray);
            font-size: 0.9rem;
        }

        .request-info .material-icons {
            color: var(--primary-color);
            margin-right: 10px;
            font-size: 20px;
        }
        
        .card-text {
            flex-grow: 1; /* Allows text to take available space */
            margin-top: 1rem;
            line-height: 1.6;
        }

        .status-pill {
            padding: 0.4em 0.8em;
            font-size: .75em;
            font-weight: 600;
            color: #fff;
            border-radius: 50rem;
        }

        /* Status-specific colors */
        .status-assigned-rictu-staff { background-color: var(--success-color); }
        .status-level-101 { background-color: var(--primary-color); }
        .status-level-2 { background-color: var(--warning-color); }
        .status-disapproved { background-color: var(--danger-color); }
        .status-default { background-color: #95a5a6; }
        
        .action-dropdown .dropdown-menu {
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border: none;
            border-radius: 8px;
        }
        
        /* --- CSS FOR MODAL CLOSE BUTTON --- */
        .modal-header .btn-close {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 50%;
            padding: 0.5rem;
            transition: all 0.2s ease-in-out;
            filter: invert(1) grayscale(100%) brightness(200%); /* Makes the 'X' icon white */
        }

        .modal-header .btn-close:hover {
            background-color: rgba(0, 0, 0, 0.4);
            transform: rotate(90deg);
        }

        .modal-header:not([class*="bg-"]) .btn-close {
            filter: none; /* Use the default black 'X' icon */
            background-color: var(--medium-gray);
        }

        .modal-header:not([class*="bg-"]) .btn-close:hover {
            background-color: #dcdfe2;
        }
        
        .ai-suggestion-btn {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 2px 8px;
            margin-left: 10px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="dashboard-header">
        <h1>Service Request Dashboard</h1>
    </div>

    <div class="row">
        <?php
        $idSRF = $_SESSION['idSRF'];
        $idSRF101 = 101;
        // Query to fetch requests for the logged-in user or new requests (level 101)
        $sql = "SELECT * FROM srf WHERE tracking = ? OR tracking = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idSRF, $idSRF101);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $currentTime = time();
        $formattedTime = date('H:i', $currentTime);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Assign variables for easier access
                $srfId = $row['id'];
                $email = $row['email'];
                $name = $row['name'];
                $ticketNumber = $row['ticketNumber'];
                $requestType = $row['requestType'];
                $otherSpecify = $row['otherSpecify'];
                $status = $row['status'];
                $equipment_id = $row['equipment_id'];
                $documents = $row['documents'];
                $description = $row['description'];
                
                // Determine status pill class for styling
                $status_class = 'status-default';
                $status_text = htmlspecialchars($status);
                
                if ($status == "Assigned RICTU staff") {
                    $status_class = 'status-assigned-rictu-staff';
                } elseif ($row['level'] == "101") {
                    $status_class = 'status-level-101';
                } elseif ($row['level'] == "2") {
                    $status_class = 'status-level-2';
                } elseif ($status == "Disapproved") {
                    $status_class = 'status-disapproved';
                }
        ?>

        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="request-card" id="request-card-<?php echo $srfId; ?>">
                <div class="card-header">
                    <span class="ticket-id">#<?php echo htmlspecialchars($ticketNumber); ?></span>
                    <span class="status-pill <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                </div>
                <div class="card-body">
                    <h5 class="card-title mb-3"><?php echo htmlspecialchars($requestType); ?></h5>
                    
                    <div class="request-info">
                        <i class="material-icons">person</i>
                        <span><?php echo htmlspecialchars($name); ?></span>
                    </div>
                    <div class="request-info">
                        <i class="material-icons">business</i>
                        <span><?php echo htmlspecialchars($row['office'] . ' - ' . $row['divSecUnit']); ?></span>
                    </div>
                    <div class="request-info">
                        <i class="material-icons">schedule</i>
                        <span>Created: <?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></span>
                    </div>

                    <p class="card-text text-muted small mt-2">
                        <?php echo substr(htmlspecialchars($description), 0, 120); ?>...
                    </p>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <div class="action-dropdown dropdown">
                            <?php
                            // Dynamically generate action buttons based on status and level
                            if ($row['status'] == "Assigned RICTU staff") {
                                echo "
                                <button class='btn btn-secondary dropdown-toggle' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                    Actions
                                </button>
                                <ul class='dropdown-menu dropdown-menu-end'>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#read_assign2{$srfId}'>View Details</a></li>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#readnotificationchat{$srfId}'>Chat</a></li>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#viewfile{$srfId}'>Documents</a></li>
                                    <li><hr class='dropdown-divider'></li>
                                    <li><a class='dropdown-item text-danger' href='#' data-bs-toggle='modal' data-bs-target='#disapproved{$srfId}'>Disapprove</a></li>
                                </ul>";
                            } elseif ($row['level'] == "101") {
                                echo "
                                <button class='btn btn-primary dropdown-toggle' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                    Actions
                                </button>
                                <ul class='dropdown-menu dropdown-menu-end'>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#assign{$srfId}'>Assign / Action</a></li>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#options{$srfId}'>Options</a></li>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#read_assign{$srfId}'>View Details</a></li>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#viewfile{$srfId}'>Documents</a></li>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#readnotificationchat{$srfId}'>Chat</a></li>
                                </ul>";
                            } elseif ($row['level'] == "2") {
                                echo "
                                <button class='btn btn-warning dropdown-toggle text-dark' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                    Actions
                                </button>
                                <ul class='dropdown-menu dropdown-menu-end'>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#receive_read{$srfId}'>View & Receive</a></li>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#viewfile{$srfId}'>Documents</a></li>
                                    <li><hr class='dropdown-divider'></li>
                                    <li><a class='dropdown-item text-danger' href='#' data-bs-toggle='modal' data-bs-target='#disapproved{$srfId}'>Disapprove</a></li>
                                </ul>";
                            } else { // Default for other statuses
                                echo "
                                <button class='btn btn-outline-secondary dropdown-toggle' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                    Actions
                                </button>
                                <ul class='dropdown-menu dropdown-menu-end'>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#printModal{$srfId}'>View Details</a></li>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#viewfile{$srfId}'>Documents</a></li>
                                    <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#editdetails_1{$srfId}'>Edit</a></li>
                                    <li><hr class='dropdown-divider'></li>
                                    <li><a class='dropdown-item text-danger' href='#' data-bs-toggle='modal' data-bs-target='#disapproved{$srfId}'>Disapprove</a></li>
                                </ul>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
            // --- ALL MODALS AND PER-CARD SCRIPTS GO HERE ---
            // This block is inside the while loop, so each card gets its own set of modals with unique IDs.
            
            // Edit Description Modal
            echo "<div class='modal fade' id='editdetails_1{$srfId}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><h5 class='modal-title'>Edit Description</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><form action='update_description.php' method='POST'><div class='modal-body'><input type='hidden' name='srf_id' value='{$srfId}'><div class='mb-3'><label for='description{$srfId}' class='form-label'>Description</label><textarea class='form-control' id='description{$srfId}' name='description' rows='4' required>{$description}</textarea></div></div><div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='submit' class='btn btn-primary'>Update</button></div></form></div></div></div>";

            // View/Manage Documents Modal
            echo "<div class='modal fade' id='viewfile{$srfId}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><h5 class='modal-title'>Manage Documents</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body'>";
            if (!empty($documents)) {
                $docArray = explode(',', $documents);
                echo '<div class="mb-3"><label class="form-label">Existing Documents</label><ul class="list-group">';
                foreach ($docArray as $doc) {
                    echo '<li class="list-group-item d-flex justify-content-between align-items-center"><a href="attached_documents/' . htmlspecialchars(trim($doc)) . '" target="_blank">' . htmlspecialchars(trim($doc)) . '</a><i class="material-icons text-primary">visibility</i></li>';
                }
                echo '</ul></div><hr>';
            } else { echo '<p class="text-center text-muted">No documents have been uploaded.</p><hr>'; }
            echo "<form id='uploadForm{$srfId}'><div class='mb-3'><label for='documentName{$srfId}' class='form-label'>New Document Name</label><input type='text' class='form-control' id='documentName{$srfId}' name='documentName' placeholder='e.g., Diagnostic Report' required></div><div class='mb-3'><label for='documentFile{$srfId}' class='form-label'>Upload File</label><input type='file' class='form-control' id='documentFile{$srfId}' name='documentFile' required></div><input type='hidden' name='srfId' value='{$srfId}'></form></div><div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='button' class='btn btn-primary' onclick='submitUploadForm({$srfId})'>Upload New</button></div></div></div></div>";

            // Chat Modal
            echo "<div class='modal fade' id='readnotificationchat{$srfId}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><h5 class='modal-title'>Chat for #{$ticketNumber}</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body'><div id='chatContainer{$srfId}' style='max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; border-radius: 8px;'><p class='text-center text-muted'>Loading messages...</p></div><form id='messageForm{$srfId}'><div class='mb-3'><textarea class='form-control' name='message' rows='3' placeholder='Type your message...' required></textarea></div><input type='hidden' name='srfId' value='{$srfId}'><button type='submit' class='btn btn-primary w-100'>Send</button></form></div></div></div></div>";
            echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                const chatModal = document.getElementById('readnotificationchat{$srfId}');
                if (!chatModal) return;
                const chatContainer = document.getElementById('chatContainer{$srfId}');
                const messageForm = document.getElementById('messageForm{$srfId}');
                if (!messageForm) return;

                function fetchMessages() {
                    fetch('getMessages.php?srfId={$srfId}').then(response => response.json()).then(data => {
                        chatContainer.innerHTML = '';
                        if (data.length === 0) {
                            chatContainer.innerHTML = `<p class='text-center text-muted'>No messages yet. Start the conversation!</p>`;
                        } else {
                            data.forEach(msg => {
                                chatContainer.innerHTML += `<div class='message mb-2'><strong>\${msg.sender}:</strong> \${msg.message}<br><small class='text-muted'>\${new Date(msg.created_at).toLocaleString()}</small></div><hr class='my-1'>`;
                            });
                        }
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }).catch(error => console.error('Error fetching messages:', error));
                }
                
                chatModal.addEventListener('shown.bs.modal', fetchMessages);
                
                messageForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    fetch('sendMessage.php', { method: 'POST', body: formData })
                        .then(response => response.text())
                        .then(() => { this.reset(); fetchMessages(); })
                        .catch(error => console.error('Error sending message:', error));
                });
            });
            </script>";

            // Iframe Modals for Viewing Documents/Forms
            $iframeModalTitles = [
                'read_assign2' => 'View Document', 'read_assign' => 'View Document',
                'receive_read' => 'View & Receive Document', 'printModal' => 'View Document Details'
            ];
            $iframeModalFooters = [
                'read_assign2' => "<button type='button' data-bs-toggle='modal' data-bs-target='#receive_staff{$srfId}' class='btn btn-success'>Receive</button>",
                'read_assign' => "<button type='button' data-bs-toggle='modal' data-bs-target='#assign{$srfId}' class='btn btn-primary'>Assign</button>",
                'receive_read' => "<button type='button' data-bs-toggle='modal' data-bs-target='#approve{$srfId}' class='btn btn-success'>Receive</button><button type='button' class='btn btn-info text-white' data-bs-toggle='modal' data-bs-target='#options{$srfId}'>View Equipment</button>",
                'printModal' => "<button type='button' data-bs-toggle='modal' data-bs-target='#approve{$srfId}' class='btn btn-success'>Approve</button><button type='button' class='btn btn-info text-white' data-bs-toggle='modal' data-bs-target='#options{$srfId}'>Options</button>"
            ];
            foreach($iframeModalTitles as $id => $title) {
                $footer = $iframeModalFooters[$id];
                echo "<div class='modal fade' id='{$id}{$srfId}' tabindex='-1'><div class='modal-dialog modal-xl'><div class='modal-content'><div class='modal-header'><h5 class='modal-title'>{$title}</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body p-0'><iframe src='printform.php?id={$srfId}' style='width: 100%; height: 75vh; border: none;'></iframe></div><div class='modal-footer'>{$footer}<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button></div></div></div></div>";
            }

            // Simple Confirmation Modals
            echo "<div class='modal fade' id='receive_staff{$srfId}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><h5 class='modal-title'>Confirm Reception</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body'><p>Are you sure you want to mark this request as received?</p></div><div class='modal-footer'><form action='receive_action.php' method='post'><input type='hidden' name='srfId' value='{$srfId}'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button><button type='submit' class='btn btn-success'>Confirm</button></form></div></div></div></div>";
            echo "<div class='modal fade' id='disapproved{$srfId}' tabindex='-1'><div class='modal-dialog'><form method='POST' action='disapproved.php'><div class='modal-content'><div class='modal-header bg-danger text-white'><h5 class='modal-title'>Disapprove Request</h5><button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button></div><div class='modal-body'><p>Are you sure you want to disapprove request #{$ticketNumber}?</p><input type='hidden' name='disapproved' value='{$srfId}'><input type='hidden' name='level' value='{$row['level']}'><input type='hidden' name='name' value='{$name}'><div class='form-group'><label for='remarks_disapprove_{$srfId}'>Remarks (Required)</label><textarea class='form-control' id='remarks_disapprove_{$srfId}' name='remarks' rows='3' required></textarea></div></div><div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='submit' class='btn btn-danger'>Confirm Disapproval</button></div></div></form></div></div>";
            echo "<div class='modal fade' id='approve{$srfId}' tabindex='-1'><div class='modal-dialog'><form method='GET' action='approve.php'><div class='modal-content'><div class='modal-header bg-success text-white'><h5 class='modal-title'>Confirm Action</h5><button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button></div><div class='modal-body'><p>Do you really want to approve request #{$ticketNumber}?</p><input type='hidden' name='approve' value='{$srfId}'><input type='hidden' name='level' value='{$row['level']}'><input type='hidden' name='name' value='{$name}'><input type='hidden' name='description' value='{$description}'><input type='hidden' name='requestType' value='{$requestType}'><input type='hidden' name='equipment_id' value='{$equipment_id}'></div><div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='submit' class='btn btn-success' onclick='this.disabled=true; this.form.submit();'>Yes, Approve</button></div></div></form></div></div>";

            // Options Modal (Complex Form)
            echo "<div class='modal fade' id='options{$srfId}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><div class='modal-header bg-info text-white'><h5 class='modal-title'>Options & Updates</h5><button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button></div><div class='modal-body'><form method='POST' action='options.php' enctype='multipart/form-data'><input type='hidden' name='srfId' value='{$srfId}' /><div class='d-grid gap-2 mb-3'><a href='mainmenu.php?dir=search_inventory&id={$srfId}' class='btn btn-success'><i class='material-icons' style='vertical-align: middle; font-size: 1.2em;'>inventory</i> View Inventory</a><button type='button' class='btn btn-primary open-equipment' data-id='{$equipment_id}'><i class='material-icons' style='vertical-align: middle; font-size: 1.2em;'>devices</i> View Equipment Details</button></div><div class='mb-3'><label>Changes Made</label><div class='form-check'><input class='form-check-input' type='checkbox' name='changes[]' value='SSD Changed' id='ssd_{$srfId}'><label class='form-check-label' for='ssd_{$srfId}'>SSD Changed</label></div><div class='form-check'><input class='form-check-input' type='checkbox' name='changes[]' value='Power Chord Changed' id='pwr_{$srfId}'><label class='form-check-label' for='pwr_{$srfId}'>Power Chord Changed</label></div><div class='form-check'><input class='form-check-input' type='checkbox' name='changes[]' value='Battery Changed' id='batt_{$srfId}'><label class='form-check-label' for='batt_{$srfId}'>Battery Changed</label></div><div class='form-check'><input class='form-check-input' type='checkbox' name='changes[]' value='Screen Changed' id='scr_{$srfId}'><label class='form-check-label' for='scr_{$srfId}'>Screen Changed</label></div></div><div class='mb-3'><label for='remarks_options_{$srfId}'>Remarks (Required)</label><textarea class='form-control' id='remarks_options_{$srfId}' name='remarks' rows='3' required></textarea></div><div class='mb-3'><label for='fileToUpload_{$srfId}'>Upload Supporting Document</label><input type='file' name='fileToUpload' class='form-control' id='fileToUpload_{$srfId}'></div><div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='submit' name='action' value='submit' class='btn btn-info text-white'>Submit Updates</button></div></form></div></div></div></div>";

            // Assign Action Modal (Complex Form) - MODIFIED FOR AI SUGGESTION
            echo "<div class='modal fade' id='assign{$srfId}' tabindex='-1'><div class='modal-dialog'><form method='GET' action='assign.php'><div class='modal-content'><div class='modal-header'><h5 class='modal-title'>Assign Action</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body'><input type='hidden' name='assign' value='{$srfId}'/><div class='row g-3'><div class='col-md-6'><label>Date</label><input type='date' class='form-control' name='action_date' required></div><div class='col-md-6'><label>Time</label><input type='time' class='form-control' name='action_time' value='{$formattedTime}' required></div></div><div class='mt-3'><label class='form-label d-flex justify-content-between align-items-center'><span>Action Taken</span><button type='button' id='ai-btn-{$srfId}' class='btn btn-outline-primary ai-suggestion-btn' onclick='getAiSuggestion({$srfId}, \"" . addslashes($requestType) . "\", \"" . addslashes($description) . "\")'><i class='material-icons' style='font-size: 1em; vertical-align: text-bottom;'>auto_awesome</i> Suggest</button></label><textarea class='form-control' id='action_taken_{$srfId}' name='action_taken' rows='3' required></textarea></div><div class='mt-3'><label>Assign To</label><select name='personelid' class='form-select' onchange='updateNameInTextField(this, {$srfId}); document.getElementById(\"submitBtn_{$srfId}\").disabled = false;' required><option disabled selected value=''>Select Personnel...</option>";
            $sql2 = "SELECT DISTINCT personelid, name FROM srfactionstaff WHERE Office = ?"; 
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("s", $_SESSION['OfficeSRF']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            if ($result2->num_rows > 0) { while ($officeRow = $result2->fetch_assoc()) { echo "<option value='" . htmlspecialchars($officeRow['personelid']) . "'>" . strtoupper(htmlspecialchars($officeRow['name'])) . "</option>"; } }
            echo "<option value='102'>MARK AS DONE</option>";
            echo "</select></div><input type='hidden' name='assignedperson_1' id='assignedperson_1_{$srfId}'><input type='hidden' name='email' value='{$email}'/><input type='hidden' name='name' value='{$name}'/><input type='hidden' name='ticketNumber' value='{$ticketNumber}'/><input type='hidden' name='requestType' value='{$requestType}'/><input type='hidden' name='otherSpecify' value='{$otherSpecify}'/><input type='hidden' name='equipment_id' value='{$equipment_id}'/></div><div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='submit' id='submitBtn_{$srfId}' class='btn btn-primary' onclick='this.disabled=true;this.form.submit();' disabled>Submit</button></div></div></form></div></div>";

        ?>

        <?php
            } // end while loop
        } else {
            // Display if no requests are found
            echo '<div class="col-12"><div class="alert alert-info text-center p-4"><h3>No Service Requests Found</h3><p>There are currently no requests matching your criteria.</p></div></div>';
        }
        $stmt->close();
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Global scripts
document.addEventListener("DOMContentLoaded", function () {
    // Event delegation for dynamically created "View Equipment" buttons
    document.body.addEventListener("click", function(e) {
        if (e.target.matches(".open-equipment")) {
            const equipmentId = e.target.getAttribute("data-id");
            if (equipmentId) {
                // Redirect to the equipment page
                window.location.href = `mainmenu.php?dir=equipment_page&equipment_id=${equipmentId}`;
            }
        }
    });

    // Add a class to the parent card when a dropdown is shown for z-index stacking
    const dropdowns = document.querySelectorAll('.action-dropdown');
    dropdowns.forEach(function(dropdown) {
        const card = dropdown.closest('.request-card');
        if (card) {
            dropdown.addEventListener('show.bs.dropdown', function () {
                card.classList.add('dropdown-active');
            });
            dropdown.addEventListener('hide.bs.dropdown', function () {
                card.classList.remove('dropdown-active');
            });
        }
    });
});

// Function to handle the file upload form submission via Fetch API
function submitUploadForm(srfId) {
    const form = document.getElementById('uploadForm' + srfId);
    const formData = new FormData(form);
    
    // Basic client-side validation
    if (!form.documentName.value || !form.documentFile.files[0]) {
        alert('Please provide both a document name and a file.');
        return;
    }

    fetch('upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Show success/error message from server
        location.reload(); // Reload the page to see changes
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during the upload.');
    });
}

// Function to update the hidden input with the selected personnel's name
function updateNameInTextField(selectElement, srfId) {
    const selectedText = selectElement.options[selectElement.selectedIndex].text;
    document.getElementById('assignedperson_1_' + srfId).value = selectedText;
}

// NEW FUNCTION FOR AI SUGGESTION
async function getAiSuggestion(srfId, requestType, description) {
    const button = document.getElementById(`ai-btn-${srfId}`);
    const textarea = document.getElementById(`action_taken_${srfId}`);
    
    // Set loading state
    const originalButtonHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Thinking...`;
    textarea.placeholder = 'Generating AI suggestion...';

    try {
        const response = await fetch('ai_suggestion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `requestType=${encodeURIComponent(requestType)}&description=${encodeURIComponent(description)}`
        });

        if (!response.ok) {
            throw new Error(`Server error: ${response.statusText}`);
        }

        const suggestion = await response.text();
        textarea.value = suggestion.trim(); // Populate the textarea

    } catch (error) {
        console.error('Error fetching AI suggestion:', error);
        textarea.value = 'Sorry, could not get a suggestion at this time.';
    } finally {
        // Restore button state
        button.disabled = false;
        button.innerHTML = originalButtonHtml;
        textarea.placeholder = '';
    }
}

</script>

</body>
</html>