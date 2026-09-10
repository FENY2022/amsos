<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['filename'])) {
    echo json_encode(['success' => false]);
    exit;
}

$filename = basename($_POST['filename']);
$filepath = 'uploads/' . $filename;

if (file_exists($filepath)) {
    unlink($filepath);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'File not found']);
}
?>
