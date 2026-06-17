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
    <style>
        body { background: #f0f2f5; display: flex; align-items: center; min-height: 100vh; }
        .card { border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); width: 100%; max-width: 500px; margin: 20px auto; }
        .card-header { background: #fff; border-bottom: 1px solid #e0e0e0; text-align: center; padding: 20px 30px; }
        .card-header h4 { font-weight: 700; color: #343a40; }
        .card-body { padding: 30px; }
        .form-control { border-radius: 8px; padding: 10px 15px; }
        .btn { border-radius: 8px; padding: 12px 20px; font-weight: 600; width: 100%; }
        .alert { border-radius: 8px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h4>Upload Equipment Image</h4>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $alertType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form action="upload_equipment.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="equipmentImage" class="form-label fw-semibold">Select Image</label>
                    <input type="file" id="equipmentImage" name="equipmentImage" class="form-control" accept="image/*" required>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">
                    Upload
                </button>
            </form>
            <p class="text-center text-muted mt-3 mb-0 small">Supported formats: JPG, PNG, GIF (Max: 10MB)</p>
        </div>
    </div>
</body>
</html>
