<?php
// Start PHP logic at the top
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../connect.php"; // Database connection

// Handle unified file upload
if (isset($_POST['updateFiles'])) {
    $edit_id = $_POST['edit_id'];
    $uploadDir = 'Sg/' . uniqid() . '/';
    $updates = [];

    // Check if at least one file was uploaded
    $hasUpload = (!empty($_FILES['below7DaysFile']['name']) || !empty($_FILES['morethan7DaysFile']['name']));
    
    if ($hasUpload) {
        if (!is_dir('../' . $uploadDir)) {
            mkdir('../' . $uploadDir, 0777, true);
        }

        date_default_timezone_set('Asia/Manila');
        $dateUploaded = date('m/d/Y');

        // Handle Below 7 Days File
        if (!empty($_FILES['below7DaysFile']['name'])) {
            $below7DaysFile = $_FILES['below7DaysFile']['name'];
            $below7DaysFilePath = $uploadDir . $below7DaysFile;
            move_uploaded_file($_FILES['below7DaysFile']['tmp_name'], '../' . $below7DaysFilePath);
            $updates[] = "Below7DaysFile='$below7DaysFilePath'";
        }

        // Handle More than 7 Days File
        if (!empty($_FILES['morethan7DaysFile']['name'])) {
            $morethan7DaysFile = $_FILES['morethan7DaysFile']['name'];
            $morethan7DaysFilePath = $uploadDir . $morethan7DaysFile;
            move_uploaded_file($_FILES['morethan7DaysFile']['tmp_name'], '../' . $morethan7DaysFilePath);
            $updates[] = "Morethan7DaysFile='$morethan7DaysFilePath'";
        }

        // If there are files to update, run the database query
        if (count($updates) > 0) {
            $updates[] = "DateUp='$dateUploaded'";
            $update_query = "UPDATE signatory_setup SET " . implode(", ", $updates) . " WHERE id='$edit_id'";
            
            if ($conn->query($update_query) === TRUE) {
                // Redirect with success flag for the toast notification
                echo '<script>window.location.href = "edit_file.php?id=' . $edit_id . '&status=success"; </script>';
                exit();
            } else {
                // Redirect with error flag
                echo '<script>window.location.href = "edit_file.php?id=' . $edit_id . '&status=error"; </script>';
                exit();
            }
        }
    }
}

// Fetch Data
$row = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $selectQuery = "SELECT * FROM signatory_setup WHERE id = $id";
    $result = mysqli_query($conn, $selectQuery);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signatory Setup Editor</title>
    
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include Lightbox CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
    <!-- Include Lightbox JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0f62fe;
            --primary-hover: #0353e9;
            --surface: #ffffff;
            --background: #f4f7f6;
            --text-main: #161616;
            --text-muted: #525252;
            --border: #e0e0e0;
            --success: #24a148;
            --error: #da1e28;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 900px;
            background-color: var(--surface);
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .header {
            padding: 24px 32px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fafafa;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .btn-outline {
            background-color: transparent;
            color: var(--text-main);
            border: 1px solid var(--border);
            font-size: 14px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-outline:hover {
            background-color: #f1f1f1;
        }

        .form-content {
            padding: 32px;
        }

        .grid-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }

        @media (max-width: 768px) {
            .grid-layout {
                grid-template-columns: 1fr;
            }
        }

        .upload-card {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 24px;
            background: #fff;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .upload-card-title {
            font-weight: 600;
            font-size: 15px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
            margin: 0;
        }

        .image-preview-wrapper {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 16px;
            text-align: center;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-preview-wrapper img {
            max-width: 100%;
            max-height: 180px;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .image-preview-wrapper img:hover {
            transform: scale(1.03);
            cursor: pointer;
        }

        .empty-state {
            color: var(--text-muted);
            font-size: 13px;
        }

        input[type="file"] {
            width: 100%;
            font-size: 13px;
            color: var(--text-muted);
        }

        input[type="file"]::file-selector-button {
            background-color: #f1f1f1;
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 8px 12px;
            margin-right: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        input[type="file"]::file-selector-button:hover {
            background-color: #e4e4e4;
        }

        .action-bar {
            padding: 24px 32px;
            background-color: #fafafa;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        /* --- Toast Notification Styles --- */
        #toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .toast {
            min-width: 300px;
            background-color: #fff;
            color: var(--text-main);
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
            transform: translateX(120%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.success {
            border-left: 4px solid var(--success);
        }

        .toast.error {
            border-left: 4px solid var(--error);
        }

        .toast-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            color: #fff;
            font-size: 14px;
        }

        .toast.success .toast-icon { background-color: var(--success); }
        .toast.error .toast-icon { background-color: var(--error); }
    </style>
</head>
<body>

<!-- Toast Notification Container -->
<div id="toast-container"></div>

<div class="container">
    <?php if ($row): ?>
        <div class="header">
            <h2>Signatory Setup</h2>
            <button class="btn-outline" type="button" onclick="window.location.href='backend.php'">Back to Dashboard</button>
        </div>

        <form method="post" action="" enctype="multipart/form-data" id="uploadForm" onsubmit="return validateForm()">
            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
            
            <div class="form-content grid-layout">
                
                <!-- Below 7 Days Column -->
                <div class="upload-card">
                    <h3 class="upload-card-title">Below 7 Days File</h3>
                    
                    <div class="image-preview-wrapper">
                        <?php if (!empty($row['Below7DaysFile'])) : ?>
                            <a href="<?php echo '../' . $row['Below7DaysFile']; ?>" data-lightbox="below7days" data-title="Below 7 Days File"> 
                                <img src="<?php echo '../' . $row['Below7DaysFile']; ?>" alt="Below 7 Days File">
                            </a>
                        <?php else : ?>
                            <span class="empty-state">No file uploaded yet</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 500; margin-bottom: 8px; display: block; color: var(--text-muted);">Replace Image (.png, .jpg)</label>
                        <input type="file" id="below7DaysFile" name="below7DaysFile" accept=".png, .jpg, .jpeg">
                    </div>
                </div>

                <!-- More Than 7 Days Column -->
                <div class="upload-card">
                    <h3 class="upload-card-title">More than 7 Days File</h3>
                    
                    <div class="image-preview-wrapper">
                        <?php if (!empty($row['Morethan7DaysFile'])) : ?>
                            <a href="<?php echo '../' . $row['Morethan7DaysFile']; ?>" data-lightbox="morethan7days" data-title="More than 7 Days File">
                                <img src="<?php echo '../' . $row['Morethan7DaysFile']; ?>" alt="More than 7 Days File">
                            </a>
                        <?php else : ?>
                            <span class="empty-state">No file uploaded yet</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label style="font-size: 12px; font-weight: 500; margin-bottom: 8px; display: block; color: var(--text-muted);">Replace Image (.png only)</label>
                        <input type="file" id="morethan7DaysFile" name="morethan7DaysFile" accept=".png">
                    </div>
                </div>

            </div>

            <div class="action-bar">
                <button type="submit" name="updateFiles" class="btn-primary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Save Changes
                </button>
            </div>
        </form>

    <?php else: ?>
        <div style="padding: 40px; text-align: center;">
            <h2 style="margin-bottom: 16px;">No Record Found</h2>
            <button class="btn-outline" type="button" onclick="window.location.href='backend.php'">Go Back</button>
        </div>
    <?php endif; ?>
</div>

<script>
    // Toast Notification System
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        // Add checkmark for success, X for error
        const iconSymbol = type === 'success' ? '✓' : '✕';
        
        toast.innerHTML = `
            <div class="toast-icon">${iconSymbol}</div>
            <div>${message}</div>
        `;
        
        container.appendChild(toast);
        
        // Trigger reflow for animation
        void toast.offsetWidth;
        toast.classList.add('show');
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400); // wait for exit animation
        }, 4000);
    }

    // Check URL parameters to show toast after a page reload
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('status')) {
            const status = urlParams.get('status');
            
            if (status === 'success') {
                showToast('Files successfully updated!', 'success');
            } else if (status === 'error') {
                showToast('An error occurred while updating files.', 'error');
            }
            
            // Clean up the URL so it doesn't show "?status=..." if the user refreshes manually
            const newUrl = window.location.pathname + "?id=" + urlParams.get('id');
            window.history.replaceState({}, document.title, newUrl);
        }
    };

    // Validate the form before submission
    function validateForm() {
        var file1 = document.getElementById("below7DaysFile").value;
        var file2 = document.getElementById("morethan7DaysFile").value;
        
        if (file1 === "" && file2 === "") {
            showToast("Please select at least one file to upload.", "error");
            return false; // Prevent form submission
        }
        
        // Visual feedback during upload
        const btn = document.querySelector('.btn-primary');
        btn.innerHTML = 'Saving...';
        btn.style.opacity = '0.7';
        btn.style.cursor = 'not-allowed';
        
        return true; 
    }
</script>

</body>
</html>