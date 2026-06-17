<?php
require_once 'vendor/autoload.php';

$options = array(
  'cluster' => 'ap3',
  'useTLS' => true,
);

$pusher = new Pusher\Pusher(
    '98d5a35431a9fefb0370', 
    'd4c2ad94090a33d8abaf', 
    '2129830',
    $options
);

$ticket = isset($_GET['ticket']) ? $_GET['ticket'] : '';
$message = '';
$alertType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket = isset($_POST['ticket']) ? $_POST['ticket'] : '';
    $targetDir = 'uploads/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    if (!isset($_FILES['equipmentImage']) || $_FILES['equipmentImage']['error'] === UPLOAD_ERR_NO_FILE) {
        $message = 'Please select a file to upload.';
        $alertType = 'danger';
    } else {
        $file = $_FILES['equipmentImage'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $message = 'Upload failed.';
            $alertType = 'danger';
        } elseif (!in_array($ext, $allowed)) {
            $message = 'Invalid file type. Only JPG, PNG, GIF allowed.';
            $alertType = 'danger';
        } elseif ($file['size'] > 10000000) {
            $message = 'File too large (max 10MB).';
            $alertType = 'danger';
        } else {
            $prefix = $ticket ? preg_replace('/[^a-zA-Z0-9_-]/', '', $ticket) . '_' : '';
            $filename = $prefix . uniqid() . '_' . basename($file['name']);
            $dest = $targetDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $message = 'File uploaded successfully!';
                $alertType = 'success';
                try {
                    // Kini nga linya mo-trigger sa event padulong sa listener sa srfrequestform.php
                    $pusher->trigger('upload-channel', 'file-uploaded', array('ticket' => $ticket, 'filename' => $filename));
                } catch (Exception $e) {
                    error_log("PUSHER ERROR: " . $e->getMessage()); // I-log ang error kung mapakyas
                }
            } else {
                $message = 'Failed to save file.';
                $alertType = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Equipment Image</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f0f2f5; display: flex; align-items: center; min-height: 100vh; }
        .card { border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); width: 100%; max-width: 500px; margin: 20px auto; }
        .card-header { background: #fff; border-bottom: 1px solid #e0e0e0; padding: 20px; text-align: center; border-radius: 15px 15px 0 0; }
        .card-body { padding: 30px; }
        .file-upload-wrapper { border: 2px dashed #ced4da; border-radius: 10px; padding: 30px; text-align: center; background: #f8f9fa; cursor: pointer; transition: 0.3s; }
        .file-upload-wrapper:hover { border-color: #0d6efd; background: #e9f5ff; }
        .file-upload-wrapper i { font-size: 3rem; color: #0d6efd; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Upload Equipment</h4>
        </div>
        <div class="card-body">
            
            <?php if ($message): ?>
            <div id="uploadToast" class="toast align-items-center text-bg-<?php echo $alertType; ?> border-0 mb-3 mx-auto" role="alert" aria-live="assertive" aria-atomic="true" style="display:block; width:100%;">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo htmlspecialchars($message, ENT_QUOTES); ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close" onclick="this.parentElement.parentElement.style.display='none'"></button>
                </div>
            </div>
            <?php endif; ?>

            <form id="uploadForm" action="upload_equipment.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="ticket" value="<?php echo htmlspecialchars($ticket); ?>">
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Select Image</label>
                    <div class="file-upload-wrapper" onclick="document.getElementById('equipmentImage').click()">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p class="mt-2 mb-0">Click to browse or drag file here</p>
                    </div>
                    <input class="form-control d-none" type="file" id="equipmentImage" name="equipmentImage" accept=".jpg,.jpeg,.png,.gif">
                </div>
                
                <button type="submit" id="submitBtn" class="btn btn-primary w-100 py-2">
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span id="btnText"><i class="bi bi-upload me-2"></i>Upload File</span>
                </button>
            </form>
            <p class="text-center text-muted mt-3 mb-0 small">Supported formats: JPG, PNG, GIF (Max: 10MB)</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        var fileInput = document.getElementById('equipmentImage');
        if (!fileInput.files || !fileInput.files.length) {
            e.preventDefault();
            alert("Please select a file first.");
            return;
        }
        var btn = document.getElementById('submitBtn');
        var text = document.getElementById('btnText');
        var spinner = document.getElementById('btnSpinner');
        btn.disabled = true;
        text.textContent = 'Uploading...';
        spinner.classList.remove('d-none');
    });

    <?php if ($message): ?>
    document.addEventListener('DOMContentLoaded', function() {
        var toastEl = document.getElementById('uploadToast');
        if (toastEl) {
            setTimeout(function() {
                toastEl.style.display = 'none';
            }, 4000);
        }
    });
    <?php endif; ?>
</script>
</body>
</html>