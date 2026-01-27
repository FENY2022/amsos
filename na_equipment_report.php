<?php
// Include the database connection logic from your provided file
require_once('connect.php');

// SQL Query to fetch specific columns where equipmentType is "N/A"
$sql = "SELECT employeeName, equipmentType FROM inv_inventory WHERE equipmentType = 'N/A'";
$result = mysqli_query($conn, $sql);

$records = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
    }
}
$count = count($records);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | N/A Filter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/web/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .inventory-card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .table-thead-custom { background-color: #4e73df; color: white; }
        .toast-container { z-index: 1055; }
        .status-badge { font-size: 0.85rem; padding: 0.5em 1em; border-radius: 50px; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary text-white p-3 rounded-3 me-3">
                    <i class="bi bi-box-seam fs-3"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0">Inventory Records</h2>
                    <p class="text-muted">Displaying items with Unspecified Equipment Types (N/A)</p>
                </div>
            </div>

            <div class="card inventory-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-thead-custom">
                                <tr>
                                    <th class="ps-4 py-3">#</th>
                                    <th class="py-3">Employee Name</th>
                                    <th class="py-3 text-center">Equipment Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($count > 0): ?>
                                    <?php foreach ($records as $index => $row): ?>
                                    <tr>
                                        <td class="ps-4 text-muted"><?php echo $index + 1; ?></td>
                                        <td class="fw-semibold text-dark"><?php echo htmlspecialchars($row['employeeName']); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary status-badge">
                                                <?php echo htmlspecialchars($row['equipmentType']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="bi bi-search d-block fs-1 mb-2"></i>
                                            No records found with Equipment Type "N/A".
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 text-end">
                    <small class="text-muted">Total Found: <strong><?php echo $count; ?></strong></small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-info-circle-fill me-2"></i>
                Successfully loaded <?php echo $count; ?> records marked as "N/A".
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize and show the toast notification on page load
        const toastElement = document.getElementById('liveToast');
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
        toast.show();
    });
</script>

</body>
</html>