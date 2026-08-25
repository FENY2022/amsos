<?php
require_once 'connect.php';
$connectionError = '';

$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'this_month';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$show_rows = isset($_GET['show_rows']) ? (int)$_GET['show_rows'] : 100;

if ($show_rows < 1) {
    $show_rows = 100;
}

function normalizeDate($value)
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);
    return ($date && $date->format('Y-m-d') === $value) ? $value : '';
}

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getStarRating($feedback)
{
    $ratings = array(
        'Excellent' => array('stars' => '&#9733;&#9733;&#9733;&#9733;&#9733;', 'color' => 'text-success'),
        'Very Satisfactory' => array('stars' => '&#9733;&#9733;&#9733;&#9733;&#9734;', 'color' => 'text-primary'),
        'Satisfactory' => array('stars' => '&#9733;&#9733;&#9733;&#9734;&#9734;', 'color' => 'text-info'),
        'Below Satisfactory' => array('stars' => '&#9733;&#9733;&#9734;&#9734;&#9734;', 'color' => 'text-warning'),
        'Poor' => array('stars' => '&#9733;&#9734;&#9734;&#9734;&#9734;', 'color' => 'text-danger')
    );

    return isset($ratings[$feedback]) ? "<span class=\"{$ratings[$feedback]['color']}\">{$ratings[$feedback]['stars']}</span>" : 'N/A';
}

$from_date = normalizeDate($from_date);
$to_date = normalizeDate($to_date);
$conditions = array("srf.status = 'Completed'");
$types = '';
$params = array();
$date_label = 'SRF Completed Requests';

if ($date_filter === 'this_month') {
    $conditions[] = "MONTH(STR_TO_DATE(srf.date, '%Y-%m-%d')) = MONTH(CURRENT_DATE())";
    $conditions[] = "YEAR(STR_TO_DATE(srf.date, '%Y-%m-%d')) = YEAR(CURRENT_DATE())";
    $date_label = 'SRF Completed Requests for ' . date('F Y');
} elseif ($from_date !== '' && $to_date !== '') {
    if ($from_date > $to_date) {
        $tmpDate = $from_date;
        $from_date = $to_date;
        $to_date = $tmpDate;
    }

    $safeFromDate = $conn ? $conn->real_escape_string($from_date) : $from_date;
    $safeToDate = $conn ? $conn->real_escape_string($to_date) : $to_date;
    $conditions[] = "STR_TO_DATE(srf.date, '%Y-%m-%d') BETWEEN '$safeFromDate' AND '$safeToDate'";
    $date_label = 'SRF Completed Requests: ' . date('M j', strtotime($from_date)) . ' - ' . date('M j, Y', strtotime($to_date));
}

$query = "
    SELECT srf.*, fb.feedback AS rate
    FROM srf
    LEFT JOIN (
        SELECT srf_id, MAX(feedback) AS feedback
        FROM srffeedback
        GROUP BY srf_id
    ) fb ON srf.id = fb.srf_id
    WHERE " . implode(' AND ', $conditions) . "
    ORDER BY STR_TO_DATE(srf.date, '%Y-%m-%d') ASC, srf.id ASC
    LIMIT " . $show_rows;

$result = false;
$queryError = '';

if ($connectionError === '') {
    $result = $conn->query($query);
    if (!$result) {
        $queryError = $conn->error;
    }
} else {
    $queryError = $connectionError;
}

if (!$result) {
    error_log('AMSOS request data query failed: ' . $queryError . ' | SQL: ' . $query);
}

$totalRecords = ($result instanceof mysqli_result) ? $result->num_rows : 0;
$canEditRequests = isset($_SESSION['User_RoleSRF']) && $_SESSION['User_RoleSRF'] === 'Super_admin';
$printUrl = 'print-all-requestdata.php?date_filter=' . urlencode($date_filter) . '&from_date=' . urlencode($from_date) . '&to_date=' . urlencode($to_date) . '&show_rows=' . (int)$show_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SRF Reports - ICT-AMSOS</title>
    <link rel="shortcut icon" type="image/x-icon" href="icon/amsos.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; font-family: "Segoe UI", Arial, sans-serif; }
        .custom-card { border: 0; border-radius: 1rem; box-shadow: 0 .5rem 1rem rgba(0,0,0,.08); }
        .custom-header { background: #0d6efd; color: #fff; border-radius: 1rem 1rem 0 0; padding: 1rem 1.5rem; }
        .table-custom thead { background: #0d6efd; color: #fff; }
        .modal iframe { width: 100%; height: 70vh; border: 0; }
        @media (max-width: 767px) { .table-responsive { border: 0; } }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="card mb-4 custom-card">
            <div class="custom-header d-flex align-items-center">
                <img src="icon/amsos.ico" alt="Logo" style="height: 50px; margin-right: 15px;">
                <div>
                    <h2 class="mb-0 text-white"><i class="bi bi-clipboard-data"></i> ICT-AMSOS Reports</h2>
                    <small class="text-white-50">Asset Management and Service Optimization System</small>
                </div>
            </div>
            <div class="card-body bg-white rounded-bottom-4 text-center">
                <h4 class="text-muted mb-2"><?php echo h($date_label); ?></h4>
                <div class="badge bg-primary fs-6 py-2 px-3">Total Records: <?php echo (int)$totalRecords; ?></div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0"><i class="bi bi-table me-2"></i>Records</h4>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#printModal"><i class="bi bi-printer me-2"></i>View All / Print All</button>
            </div>
        </div>

        <?php if (!$result): ?>
            <div class="alert alert-warning">Request data is temporarily unavailable. Please check the server error log for the DB query error.</div>
        <?php endif; ?>

        <div class="card custom-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-custom mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ticket #</th>
                                <th>Date</th>
                                <th>Requester</th>
                                <th>Office</th>
                                <th>Request Type</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $count = 1; while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo h(isset($row['ticketNumber']) ? $row['ticketNumber'] : ''); ?></span></td>
                                        <td><?php echo !empty($row['date']) ? h(date('M j, Y', strtotime($row['date']))) : ''; ?></td>
                                        <td><strong><?php echo h(isset($row['name']) ? $row['name'] : ''); ?></strong><br><small class="text-muted"><?php echo h(isset($row['position']) ? $row['position'] : ''); ?></small></td>
                                        <td><?php echo h(isset($row['office']) ? $row['office'] : ''); ?></td>
                                        <td><span class="badge bg-info"><?php echo h(isset($row['requestType']) ? $row['requestType'] : ''); ?></span></td>
                                        <td><?php echo getStarRating(isset($row['rate']) ? $row['rate'] : ''); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewDocumentModal<?php echo (int)$row['id']; ?>"><i class="bi bi-eye"></i></button>
                                            <?php if ($canEditRequests): ?>
                                                <a href="edit-receive.php?id=<?php echo (int)$row['id']; ?>" target="_blank" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Login as Super Admin to edit"><i class="bi bi-pencil"></i></button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="viewDocumentModal<?php echo (int)$row['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bi bi-file-earmark-text me-2"></i>View Document - Ticket #<?php echo h(isset($row['ticketNumber']) ? $row['ticketNumber'] : ''); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-0">
                                                    <iframe src="printform-request.php?id=<?php echo (int)$row['id']; ?>"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No request data available.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="printModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-printer me-2"></i>View All Documents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe src="<?php echo h($printUrl); ?>"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
