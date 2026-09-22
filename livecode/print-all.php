<?php
require_once 'connect_amsos.php';

function normalizeDateOrDefault($value, $default)
{
    $value = trim((string)$value);
    if ($value === '') {
        return $default;
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        return $default;
    }

    return $value;
}

$defaultStartDate = (new DateTime('first day of this month'))->format('Y-m-d');
$defaultEndDate = (new DateTime())->format('Y-m-d');

$startDate = normalizeDateOrDefault($_GET['start_date'] ?? '', $defaultStartDate);
$endDate = normalizeDateOrDefault($_GET['end_date'] ?? '', $defaultEndDate);

if ($startDate > $endDate) {
    [$startDate, $endDate] = [$endDate, $startDate];
}

$query = "
    SELECT
        srf.id,
        srf.ticketNumber,
        srf.date,
        srf.name,
        srf.requestType,
        srf.office,
        fb.feedback AS rate
    FROM srf
    INNER JOIN (
        SELECT sf1.*
        FROM srffeedback sf1
        INNER JOIN (
            SELECT srf_id, MAX(id) AS max_id
            FROM srffeedback
            GROUP BY srf_id
        ) latest ON latest.max_id = sf1.id
    ) fb ON srf.id = fb.srf_id
    WHERE srf.status = 'Completed'
      AND fb.feedback IS NOT NULL
      AND fb.feedback <> ''
      AND STR_TO_DATE(srf.date, '%Y-%m-%d') BETWEEN ? AND ?
    ORDER BY STR_TO_DATE(srf.date, '%Y-%m-%d') ASC, srf.id ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$totalRows = $result ? $result->num_rows : 0;
$dateRangeLabel = date('M j, Y', strtotime($startDate)) . ' - ' . date('M j, Y', strtotime($endDate));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print All Rated</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
            min-height: 100vh;
        }

        .page-wrap {
            max-width: 1450px;
            margin: 0 auto;
            padding: 24px 16px 40px;
        }

        .hero {
            border: 0;
            border-radius: 24px;
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 50%, #38bdf8 100%);
            color: #fff;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.16);
            margin-bottom: 18px;
            overflow: hidden;
        }

        .hero-inner {
            padding: 24px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
        }

        .hero h1 {
            font-size: clamp(1.35rem, 2vw, 2rem);
            font-weight: 800;
            margin: 0;
        }

        .hero p {
            margin: 8px 0 0;
            color: rgba(255,255,255,.84);
            max-width: 64ch;
        }

        .hero-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: #fff;
            border-radius: 18px;
            padding: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            border: 1px solid rgba(15, 23, 42, 0.05);
        }

        .stat-label { color: #64748b; font-size: .82rem; margin-bottom: 8px; }
        .stat-value { color: #0f172a; font-size: 1.5rem; font-weight: 800; line-height: 1; }
        .stat-note { color: #94a3b8; font-size: .82rem; margin-top: 6px; }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .report-card {
            border: 0;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.08);
            background: #fff;
        }

        .report-head {
            padding: 16px 18px;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            border-bottom: 1px solid #e2e8f0;
        }

        .report-title {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            margin: 0;
        }

        .report-title h2 {
            font-size: 1rem;
            font-weight: 800;
            margin: 0;
            color: #0f172a;
        }

        .report-title small {
            display: block;
            margin-top: 4px;
            color: #64748b;
        }

        .report-badge {
            flex: 0 0 auto;
            border-radius: 999px;
            padding: 8px 12px;
            font-weight: 800;
            font-size: .8rem;
            background: #e0f2fe;
            color: #075985;
            white-space: nowrap;
        }

        .report-body {
            background: #0b1220;
        }

        .report-body iframe {
            display: block;
            width: 100%;
            height: 285vh;
            border: 0;
            background: #fff;
        }

        .empty-state {
            background: #fff;
            border-radius: 18px;
            padding: 42px 18px;
            text-align: center;
            color: #64748b;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        @media (max-width: 992px) {
            .stat-grid,
            .report-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="hero">
            <div class="hero-inner">
                <div>
                    <h1><i class="fa-solid fa-layer-group mr-2"></i>Print All Rated</h1>
                    <p>Only completed SRFs with an existing rating are included here. Each report is embedded for quick printing.</p>
                    <div class="mt-2"><span class="report-badge"><?php echo htmlspecialchars($dateRangeLabel); ?></span></div>
                </div>
                <div class="hero-actions">
                    <button class="btn btn-light font-weight-bold" onclick="window.print()"><i class="fa-solid fa-print mr-1"></i> Print Page</button>
                </div>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">Printable Reports</div>
                <div class="stat-value"><?php echo (int)$totalRows; ?></div>
                <div class="stat-note">Completed + rated only</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Layout</div>
                <div class="stat-value">Responsive</div>
                <div class="stat-note">Two columns on desktop</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Mode</div>
                <div class="stat-value">Embedded Print</div>
                <div class="stat-note">Open individual reports below</div>
            </div>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="report-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="report-card">
                        <div class="report-head">
                            <div class="report-title">
                                <div>
                                    <h2>Ticket #<?php echo htmlspecialchars($row['ticketNumber'] ?? ''); ?></h2>
                                    <small><?php echo htmlspecialchars($row['name'] ?? ''); ?> | <?php echo htmlspecialchars($row['requestType'] ?? ''); ?> | <?php echo htmlspecialchars($row['office'] ?? ''); ?></small>
                                </div>
                                <div class="report-badge"><?php echo htmlspecialchars($row['rate'] ?? ''); ?></div>
                            </div>
                        </div>
                        <div class="report-body">
                            <iframe src="printform-request.php?id=<?php echo urlencode((string)$row['id']); ?>"></iframe>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">No completed and rated SRFs are available for printing.</div>
        <?php endif; ?>
    </div>
</body>
</html>
  
