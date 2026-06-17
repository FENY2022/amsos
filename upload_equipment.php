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
            <?php if (isset($_GET['uploaded']) && $_GET['uploaded'] == 1): ?>
                <div class="alert alert-success">File uploaded successfully!</div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <form action="upload_equipment.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="equipmentImage" class="form-label fw-semibold">Select Image</label>
                    <input type="file" id="equipmentImage" name="equipmentImage" class="form-control" accept="image/*" required>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-upload"></i> Upload
                </button>
            </form>
            <p class="text-center text-muted mt-3 mb-0 small">
                <i class="bi bi-info-circle"></i> Supported formats: JPG, PNG, GIF (Max: 10MB)
            </p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $targetDir = 'uploads/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $file = $_FILES['equipmentImage'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        header('Location: upload_equipment.php?error=Upload failed');
        exit;
    }
    if (!in_array($ext, $allowed)) {
        header('Location: upload_equipment.php?error=Invalid file type');
        exit;
    }
    if ($file['size'] > 10000000) {
        header('Location: upload_equipment.php?error=File too large (max 10MB)');
        exit;
    }

    $filename = uniqid() . '_' . basename($file['name']);
    $dest = $targetDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        header('Location: upload_equipment.php?uploaded=1');
        exit;
    } else {
        header('Location: upload_equipment.php?error=Failed to save file');
        exit;
    }
}
?>
