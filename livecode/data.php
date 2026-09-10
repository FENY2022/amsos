<?php
header('Content-Type: application/json');

// Sample data
$data = [
    'Laptops' => 10,
    'Desktop Computers' => 15,
    'Tablets' => 5,
    'Cellphones' => 12,
    'Printers' => 8,
    'Cameras' => 7,
    'CCTV' => 6,
    'Routers' => 4,
    'Scanners' => 3,
    'UPS' => 9
];

echo json_encode($data);
?>
