<?php
$message = '';
$alertType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $targetDir = 'uploads/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

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
        $filename = uniqid() . '_' . basename($file['name']);
        $dest = $targetDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $message = 'File uploaded successfully!';
            $alertType = 'success';
        } else {
            $message = 'Failed to save file.';
            $alertType = 'danger';
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
        .card-header { background: #fff; border-bottom: 1px solid #e0e0e0; text-align: center; padding: 20px 30px; }
        .card-header h4 { font-weight: 700; color: #343a40; }
        .card-body { padding: 30px; }
        .form-control { border-radius: 8px; padding: 10px 15px; }
        .btn { border-radius: 8px; padding: 12px 20px; font-weight: 600; width: 100%; }
        .toast-container { z-index: 1050; }
    </style>
</head>
<body>
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1060;">
        <div id="uploadToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Upload Equipment Image</h4>
        </div>
        <div class="card-body">
            <form id="uploadForm" action="upload_equipment.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="equipmentImage" class="form-label fw-semibold">Select Image</label>
                    <input type="file" id="equipmentImage" name="equipmentImage" class="form-control" accept="image/*" required>
                </div>
                <button type="submit" id="submitBtn" name="submit" class="btn btn-primary">
                    <span id="btnText">Upload</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </form>
            <p class="text-center text-muted mt-3 mb-0 small">Supported formats: JPG, PNG, GIF (Max: 10MB)</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('uploadForm').addEventListener('submit', function() {
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
            toastEl.classList.add('text-bg-<?php echo $alertType; ?>');
            toastEl.querySelector('.toast-body').textContent = '<?php echo htmlspecialchars($message, ENT_QUOTES); ?>';
            var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 4000 });
            toast.show();
        });
        <?php endif; ?>
    </script>
</body>
</html>
