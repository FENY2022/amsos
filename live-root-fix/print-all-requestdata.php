<?php
require_once 'connect.php';
$connectionError = '';

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

function ratingLabel($feedback)
{
    $feedback = trim((string)$feedback);
    return $feedback !== '' ? $feedback : 'No rating';
}

$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'this_month';
$from_date = normalizeDate(isset($_GET['from_date']) ? $_GET['from_date'] : '');
$to_date = normalizeDate(isset($_GET['to_date']) ? $_GET['to_date'] : '');
$show_rows = isset($_GET['show_rows']) ? (int)$_GET['show_rows'] : 100;

if ($show_rows < 1) {
    $show_rows = 100;
}

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
    SELECT srf.id, srf.ticketNumber, srf.date, srf.name, srf.office, srf.requestType, fb.feedback AS rate
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
    error_log('AMSOS print all request data query failed: ' . $queryError . ' | SQL: ' . $query);
}

$totalRows = ($result instanceof mysqli_result) ? $result->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Documents</title>
    <link rel="shortcut icon" type="image/x-icon" href="icon/amsos.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { min-height: 100vh; background: #eef4fb; color: #0f172a; font-family: "Segoe UI", Arial, sans-serif; }
        .page-wrap { max-width: 1480px; margin: 0 auto; padding: 24px 16px 42px; }
        .toolbar { border-radius: 22px; background: linear-gradient(135deg, #0f172a, #1d4ed8); color: #fff; padding: 22px; box-shadow: 0 18px 40px rgba(15,23,42,.16); margin-bottom: 18px; }
        .toolbar h1 { font-size: clamp(1.35rem, 2vw, 2rem); font-weight: 800; margin: 0; }
        .toolbar p { margin: 8px 0 0; color: rgba(255,255,255,.82); }
        .summary-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 14px 16px; box-shadow: 0 8px 24px rgba(15,23,42,.06); margin-bottom: 16px; }
        .document-list { display: grid; grid-template-columns: 1fr; gap: 18px; }
        .document-card { overflow: hidden; border: 1px solid #dbe5f0; border-radius: 20px; background: #fff; box-shadow: 0 14px 34px rgba(15,23,42,.08); }
        .document-head { display: flex; justify-content: space-between; gap: 12px; padding: 14px 16px; background: linear-gradient(180deg,#fff,#f8fafc); border-bottom: 1px solid #e2e8f0; }
        .document-head h2 { margin: 0; font-size: 1rem; font-weight: 800; }
        .document-head small { color: #64748b; }
        .badge-soft { align-self: flex-start; border-radius: 999px; padding: 7px 10px; background: #e0f2fe; color: #075985; font-size: .78rem; font-weight: 800; white-space: nowrap; }
        iframe { display: block; width: 100%; height: 285vh; border: 0; background: #fff; }
        .empty-state { border-radius: 18px; background: #fff; padding: 42px 18px; color: #64748b; text-align: center; box-shadow: 0 8px 24px rgba(15,23,42,.06); }

        @media print {
            @page { size: A4 portrait; margin: 0; }
            html, body { background: #fff; width: 210mm; height: 297mm; }
            .no-print { display: none !important; }
            .page-wrap { max-width: none; padding: 0; }
            .document-card { border: 0; border-radius: 0; box-shadow: none; width: 210mm; height: 297mm; overflow: hidden; break-after: page; page-break-after: always; }
            .document-head { display: none; }
            iframe { width: 138.89%; height: 410mm; transform: scale(.72); transform-origin: top left; }
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="toolbar no-print">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    <h1><i class="bi bi-files me-2"></i>View All Documents</h1>
                    <p>All completed SRF requests currently shown in the Request Data table are listed here for viewing and printing.</p>
                </div>
                <div class="d-flex align-items-start gap-2">
                    <button type="button" class="btn btn-light fw-bold" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print All</button>
                </div>
            </div>
        </div>

        <div class="summary-card no-print">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                <div><strong><?php echo h($date_label); ?></strong></div>
                <div class="text-muted">Documents: <?php echo (int)$totalRows; ?> | Rows limit: <?php echo (int)$show_rows; ?></div>
            </div>
        </div>

        <?php if (!$result): ?>
            <div class="alert alert-warning no-print">Documents are temporarily unavailable. Please check the server error log for the DB query error.</div>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="document-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <section class="document-card">
                        <div class="document-head no-print">
                            <div>
                                <h2>Ticket #<?php echo h(isset($row['ticketNumber']) ? $row['ticketNumber'] : ''); ?></h2>
                                <small><?php echo h(isset($row['name']) ? $row['name'] : ''); ?> | <?php echo h(isset($row['office']) ? $row['office'] : ''); ?> | <?php echo h(isset($row['requestType']) ? $row['requestType'] : ''); ?></small>
                            </div>
                            <span class="badge-soft"><?php echo h(ratingLabel(isset($row['rate']) ? $row['rate'] : '')); ?></span>
                        </div>
                        <iframe src="printform-request.php?id=<?php echo (int)$row['id']; ?>"></iframe>
                    </section>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">No completed SRF documents are available for this filter.</div>
        <?php endif; ?>
    </div>
</body>
</html>
