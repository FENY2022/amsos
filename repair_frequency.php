<?php
require_once 'connect.php';

function repairHistoryRows($conn) {
    $sql = "SELECT
                rh.*,
                COALESCE(sr.status, rh.status) AS current_status,
                repair_counts.total_srf_repairs
            FROM srf_repair_history rh
            LEFT JOIN srf sr ON rh.srf_id = sr.id
            LEFT JOIN (
                SELECT inventory_id, COUNT(*) AS total_srf_repairs
                FROM srf_repair_history
                WHERE record_type IN ('SRF Repair', 'Historical Repair')
                GROUP BY inventory_id
            ) repair_counts ON rh.inventory_id = repair_counts.inventory_id
            ORDER BY rh.date_recorded DESC, rh.id DESC";

    return $conn->query($sql);
}

if (isset($_GET['action']) && $_GET['action'] === 'export') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Unified_Repair_History.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, [
        'Type',
        'Property No',
        'Actual User',
        'Equipment Type',
        'Brand',
        'Equipment ID',
        'Total SRF Repairs',
        'SRF ID',
        'Preventive ID',
        'Status',
        'Date Recorded',
        'Time Recorded',
        'Action Staff',
        'Issue / PM Details',
        'Action Taken'
    ]);

    $result = repairHistoryRows($conn);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['record_type'],
                $row['property_number'],
                $row['actual_user'],
                $row['equipment_type'],
                $row['brand'],
                $row['inventory_id'],
                $row['total_srf_repairs'] ?: 0,
                $row['srf_id'] ?: ($row['source_id'] ?: 'N/A'),
                $row['preventive_id'] ?: 'N/A',
                $row['current_status'] ?: 'N/A',
                $row['date_recorded'] ?: 'N/A',
                $row['time_recorded'] ?: 'N/A',
                $row['action_staff'] ?: 'N/A',
                $row['issue_description'] ? str_replace(["\r", "\n"], ' | ', $row['issue_description']) : 'N/A',
                $row['action_taken'] ? str_replace(["\r", "\n"], ' | ', $row['action_taken']) : 'N/A'
            ]);
        }
    }

    fclose($output);
    exit;
}

$result = repairHistoryRows($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repair and Maintenance History</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            background: #fff;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
            padding: 20px 25px;
        }
        .table > :not(caption) > * > * {
            padding: 0.9rem 0.75rem;
        }
        div.dataTables_wrapper div.dataTables_filter input {
            border-radius: 20px;
            padding: 5px 15px;
        }
        @media print {
            body { background-color: #fff; }
            .card-custom { box-shadow: none; border: 1px solid #ddd; }
            .no-print { display: none !important; }
            .card-header-custom { color: black; background: transparent; border-bottom: 2px solid #000; }
        }
    </style>
</head>
<body>
<div class="container-fluid py-5 px-md-5">
    <div class="card card-custom">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h4 class="m-0 fw-bold">
                <i class="fa-solid fa-screwdriver-wrench me-2"></i> Repair and Preventive Maintenance History
            </h4>
            <div class="no-print">
                <a class="btn btn-success btn-sm fw-semibold shadow-sm me-2" href="?action=export">
                    <i class="fa-solid fa-file-excel me-1"></i> Export CSV
                </a>
                <button class="btn btn-light btn-sm fw-semibold shadow-sm" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i> Print
                </button>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="alert alert-light border-start border-4 border-info text-muted mb-4 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-info me-2"></i>
                This table displays equipment service history from the new central <strong>srf_repair_history</strong> table, including SRF repairs and preventive maintenance records.
            </div>

            <div class="table-responsive">
                <table id="repairTable" class="table table-hover align-middle border-top w-100">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Property No.</th>
                            <th>Actual User</th>
                            <th>Equipment Type</th>
                            <th>Brand</th>
                            <th>Equipment ID</th>
                            <th>Total SRF Repairs</th>
                            <th>SRF ID</th>
                            <th>Preventive ID</th>
                            <th>Status</th>
                            <th>Date Recorded</th>
                            <th>Time Recorded</th>
                            <th>Action Staff</th>
                            <th>Issue / PM Details</th>
                            <th>Action Taken</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            $counter = 1;
                            while ($row = $result->fetch_assoc()) {
                                $recordType = htmlspecialchars($row['record_type'] ?? '');
                                $propertyNumber = htmlspecialchars($row['property_number'] ?? '');
                                $actualUser = htmlspecialchars($row['actual_user'] ?? '');
                                $equipmentType = htmlspecialchars($row['equipment_type'] ?? '');
                                $brand = htmlspecialchars($row['brand'] ?? '');
                                $inventoryId = htmlspecialchars($row['inventory_id'] ?? '');
                                $totalRepairs = htmlspecialchars($row['total_srf_repairs'] ?? 0);
                                $srfId = htmlspecialchars(!empty($row['srf_id']) ? $row['srf_id'] : (!empty($row['source_id']) ? $row['source_id'] : 'N/A'));
                                $preventiveId = htmlspecialchars(!empty($row['preventive_id']) ? $row['preventive_id'] : 'N/A');
                                $status = !empty($row['current_status']) ? $row['current_status'] : 'N/A';
                                $statusEscaped = htmlspecialchars($status);
                                $dateRecorded = htmlspecialchars(!empty($row['date_recorded']) ? $row['date_recorded'] : 'N/A');
                                $timeRecorded = htmlspecialchars(!empty($row['time_recorded']) ? $row['time_recorded'] : 'N/A');
                                $actionStaff = htmlspecialchars(!empty($row['action_staff']) ? $row['action_staff'] : 'N/A');
                                $issueDescription = htmlspecialchars(!empty($row['issue_description']) ? str_replace(["\r", "\n"], ' | ', $row['issue_description']) : 'N/A', ENT_QUOTES);
                                $actionTaken = htmlspecialchars(!empty($row['action_taken']) ? str_replace(["\r", "\n"], ' | ', $row['action_taken']) : 'No action recorded', ENT_QUOTES);
                                $shortIssue = strlen($issueDescription) > 70 ? substr($issueDescription, 0, 70) . '...' : $issueDescription;
                                $shortAction = strlen($actionTaken) > 70 ? substr($actionTaken, 0, 70) . '...' : $actionTaken;

                                if ($recordType === 'Preventive Maintenance') {
                                    $typeBadgeClass = 'bg-info text-dark';
                                } elseif ($recordType === 'Historical Repair') {
                                    $typeBadgeClass = 'bg-secondary';
                                } else {
                                    $typeBadgeClass = 'bg-primary';
                                }
                                $statusLower = strtolower($status);
                                if ($statusLower === 'completed') {
                                    $statusBadgeClass = 'bg-primary';
                                } elseif ($statusLower === 'disapproved') {
                                    $statusBadgeClass = 'bg-danger';
                                } elseif (strpos($statusLower, 'assign') !== false || strpos($statusLower, 'process') !== false) {
                                    $statusBadgeClass = 'bg-warning text-dark';
                                } elseif ($status === 'N/A') {
                                    $statusBadgeClass = 'bg-light text-dark border';
                                } else {
                                    $statusBadgeClass = 'bg-secondary';
                                }

                                echo '<tr>';
                                echo '<td class="text-muted">' . $counter++ . '</td>';
                                echo '<td><span class="badge ' . $typeBadgeClass . '">' . $recordType . '</span></td>';
                                echo '<td class="fw-bold text-primary">' . $propertyNumber . '</td>';
                                echo '<td>' . $actualUser . '</td>';
                                echo '<td><span class="badge bg-light text-dark border">' . $equipmentType . '</span></td>';
                                echo '<td>' . $brand . '</td>';
                                echo '<td>' . $inventoryId . '</td>';
                                echo '<td class="text-center"><span class="badge rounded-pill bg-dark">' . $totalRepairs . '</span></td>';
                                echo '<td>' . $srfId . '</td>';
                                echo '<td>' . $preventiveId . '</td>';
                                echo '<td><span class="badge ' . $statusBadgeClass . '">' . $statusEscaped . '</span></td>';
                                echo '<td>' . $dateRecorded . '</td>';
                                echo '<td>' . $timeRecorded . '</td>';
                                echo '<td class="fw-semibold">' . $actionStaff . '</td>';
                                echo '<td><span data-bs-toggle="tooltip" data-bs-placement="top" title="' . $issueDescription . '" style="cursor: help; border-bottom: 1px dotted #999;">' . $shortIssue . '</span></td>';
                                echo '<td><span data-bs-toggle="tooltip" data-bs-placement="top" title="' . $actionTaken . '" style="cursor: help; border-bottom: 1px dotted #999;">' . $shortAction . '</span></td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#repairTable').DataTable({
            pageLength: 10,
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Search records...'
            },
            order: [[11, 'desc']],
            scrollX: true
        });

        function refreshTooltips() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        table.on('draw', refreshTooltips);
        refreshTooltips();
    });
</script>
</body>
</html>
<?php
if (isset($conn) && $conn) {
    $conn->close();
}
?>
