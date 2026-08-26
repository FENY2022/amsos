<?php
require_once 'amsos-requestdata-connect.php';
$connectionError = '';

$filterMemoryKey = 'amsos_requestdata_filters';
$rememberedFilters = isset($_SESSION[$filterMemoryKey]) && is_array($_SESSION[$filterMemoryKey]) ? $_SESSION[$filterMemoryKey] : array();

// Fetch filter values
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : (isset($rememberedFilters['date_filter']) ? $rememberedFilters['date_filter'] : 'this_month');
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : (isset($rememberedFilters['from_date']) ? $rememberedFilters['from_date'] : '');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : (isset($rememberedFilters['to_date']) ? $rememberedFilters['to_date'] : '');
$show_rows = isset($_GET['show_rows']) ? $_GET['show_rows'] : (isset($rememberedFilters['show_rows']) ? $rememberedFilters['show_rows'] : 100); // Default to 100 rows

if ($date_filter !== 'this_month' && $date_filter !== 'custom') {
    $date_filter = 'custom';
}

// Sanitize and validate show_rows
$show_rows = (int)$show_rows;
if ($show_rows < 1) {
    $show_rows = 100; // Set minimum to 100 rows
}

$_SESSION[$filterMemoryKey] = array(
    'date_filter' => $date_filter,
    'from_date' => $from_date,
    'to_date' => $to_date,
    'show_rows' => $show_rows
);

$shareScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$shareHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost');
$shareBasePath = rtrim(str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '')), '/');
$sharePath = ($shareBasePath === '' || $shareBasePath === '.') ? '/amsos-requestdata.php' : $shareBasePath . '/amsos-requestdata.php';
$shareUrl = $shareScheme . '://' . $shareHost . $sharePath . '?' . http_build_query(array(
    'date_filter' => $date_filter,
    'from_date' => $from_date,
    'to_date' => $to_date,
    'show_rows' => $show_rows
));

// Build query
$query = "SELECT srf.*, srffeedback.feedback AS rate 
          FROM srf 
          LEFT JOIN srffeedback ON srf.id = srffeedback.srf_id 
          WHERE srf.status = 'Completed'";

if ($date_filter === 'this_month') {
    $query .= " AND MONTH(STR_TO_DATE(srf.date, '%Y-%m-%d')) = MONTH(CURRENT_DATE()) AND YEAR(STR_TO_DATE(srf.date, '%Y-%m-%d')) = YEAR(CURRENT_DATE()) ";
    $date_label = "SRF Completed Requests for " . date('F Y');
} elseif (!empty($from_date) && !empty($to_date)) {
    // Sanitize date inputs
    $from_date = date('Y-m-d', strtotime($from_date));
    $to_date = date('Y-m-d', strtotime($to_date));
    
    $query .= " AND STR_TO_DATE(srf.date, '%Y-%m-%d') BETWEEN '$from_date' AND '$to_date' ";
    $date_label = "SRF Completed Requests: " . date('M j', strtotime($from_date)) . " - " . date('M j, Y', strtotime($to_date));
} else {
    $date_label = "SRF Completed Requests";
}

// Add GROUP BY to prevent duplicate ticket numbers
$query .= " GROUP BY srf.id ";

// Add ORDER BY and LIMIT
$query .= " ORDER BY STR_TO_DATE(srf.date, '%Y-%m-%d') ASC LIMIT $show_rows ";

// Execute query
$result = false;
if ($connectionError === '' && $conn) {
    $result = $conn->query($query);
    if (!$result) {
        error_log('AMSOS request data query failed: ' . $conn->error . ' | SQL: ' . $query);
    }
} else {
    error_log('AMSOS request data unavailable: ' . $connectionError);
}
$totalRecords = $result ? $result->num_rows : 0;

function getStarRating($feedback) {
    $ratings = array(
        'Excellent' => array('stars' => '&#9733;&#9733;&#9733;&#9733;&#9733;', 'color' => 'text-success'),
        'Very Satisfactory' => array('stars' => '&#9733;&#9733;&#9733;&#9733;&#9734;', 'color' => 'text-primary'),
        'Satisfactory' => array('stars' => '&#9733;&#9733;&#9733;&#9734;&#9734;', 'color' => 'text-info'),
        'Below Satisfactory' => array('stars' => '&#9733;&#9733;&#9734;&#9734;&#9734;', 'color' => 'text-warning'),
        'Poor' => array('stars' => '&#9733;&#9734;&#9734;&#9734;&#9734;', 'color' => 'text-danger')
    );
    return isset($ratings[$feedback]) ? "<span class='{$ratings[$feedback]['color']}'>{$ratings[$feedback]['stars']}</span>" : 'N/A';
}

$canEditRequests = isset($_SESSION['User_RoleSRF']) && $_SESSION['User_RoleSRF'] === 'Super_admin';
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
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --info-color: #0dcaf0;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--light-color);
        }

        .custom-card {
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            border: none;
        }

        .custom-header {
            background: var(--primary-color);
            color: white;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .custom-header h2 {
            margin: 0;
            font-weight: 600;
        }
        
        .custom-header small {
            opacity: 0.8;
        }

        .filter-card {
            background-color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .date-range-trigger {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid #d9e5f5;
            border-radius: 1rem;
            padding: 1rem 1.1rem;
            min-height: 74px;
            box-shadow: 0 0.25rem 0.75rem rgba(13, 110, 253, 0.06);
            transition: all 0.2s ease;
        }

        .date-range-trigger:hover,
        .date-range-trigger.show {
            border-color: #9ec5fe;
            box-shadow: 0 0.35rem 1rem rgba(13, 110, 253, 0.14);
            transform: translateY(-1px);
        }

        .date-picker-dropdown {
            width: min(100%, 760px);
            border-radius: 1.25rem;
            margin-top: 0.75rem !important;
            overflow: hidden;
        }

        .date-picker-shell {
            padding: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
        }

        .date-picker-presets {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .preset-chip {
            border: 1px solid #d7e3f3;
            background: #fff;
            color: #334155;
            border-radius: 999px;
            padding: 0.45rem 0.9rem;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .preset-chip:hover,
        .preset-chip.active {
            background: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0.35rem 0.8rem rgba(13, 110, 253, 0.18);
        }

        .date-picker-calendars {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .calendar-pane {
            background: #fff;
            border: 1px solid #e6eef8;
            border-radius: 1rem;
            padding: 0.85rem;
        }

        .calendar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .calendar-title {
            font-weight: 700;
            color: var(--dark-color);
        }

        .calendar-nav {
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            border: 1px solid #d7e3f3;
            background: #f8fbff;
            color: #0d6efd;
        }

        .weekday-row,
        .day-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 0.35rem;
        }

        .weekday-row {
            margin-bottom: 0.5rem;
        }

        .weekday-row span {
            text-align: center;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #64748b;
            text-transform: uppercase;
        }

        .day-cell {
            border: none;
            background: transparent;
            border-radius: 0.8rem;
            min-height: 40px;
            font-weight: 600;
            color: #334155;
            transition: all 0.15s ease;
        }

        .day-cell:hover {
            background: rgba(13, 110, 253, 0.08);
        }

        .day-cell.outside-month {
            color: #cbd5e1;
        }

        .day-cell.in-range {
            background: rgba(13, 110, 253, 0.12);
            color: #0b5ed7;
            border-radius: 0.6rem;
        }

        .day-cell.range-start,
        .day-cell.range-end {
            background: var(--primary-color);
            color: #fff;
            box-shadow: 0 0.35rem 0.75rem rgba(13, 110, 253, 0.2);
        }

        .day-cell.today {
            outline: 2px solid rgba(13, 110, 253, 0.25);
            outline-offset: -2px;
        }

        .date-picker-footer {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            padding-top: 1rem;
            margin-top: 1rem;
            border-top: 1px solid #e6eef8;
        }

        .airline-summary-box {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid #d9e5f5;
            border-radius: 1rem;
            padding: 1rem;
            min-height: 74px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        @media (max-width: 767.98px) {
            .date-picker-calendars {
                grid-template-columns: 1fr;
            }

            .date-picker-dropdown {
                width: calc(100vw - 2rem);
            }

            .date-picker-footer {
                flex-direction: column;
            }

            .date-picker-footer .btn {
                width: 100%;
            }
        }

        .table-custom {
            border-radius: 1rem;
            overflow: hidden;
        }
        
        .table-custom thead {
            background-color: var(--light-color);
        }

        .table-custom th {
            font-weight: 600;
            color: var(--dark-color);
            padding: 1rem;
        }

        .table-custom tbody tr {
            transition: all 0.2s ease-in-out;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer; /* Indicates it is clickable */
        }

        .table-custom tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
            transform: scale(1.01);
        }
        
        .table-custom tbody tr:last-child {
            border-bottom: none;
        }

        /* NEW: Last clicked row indicator style */
        .table-custom tbody tr.last-clicked-row {
            background-color: rgba(25, 135, 84, 0.1) !important;
            border-left: 4px solid var(--success-color);
        }

        .table-custom .badge {
            font-size: 0.85em;
            font-weight: 500;
        }

        .modal-content {
            border-radius: 1rem;
        }

        .modal-header {
            border-bottom: none;
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: none;
            padding: 1.5rem;
        }
        
        /* Mobile table styles */
        @media (max-width: 767.98px) {
            .table-responsive {
                border-radius: 1rem;
                border: 1px solid #dee2e6;
            }
            .table-custom thead {
                display: none;
            }
            .table-custom tbody tr {
                display: block;
                margin-bottom: 1rem;
                background: white;
                box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
            }
            .table-custom td {
                display: block;
                width: 100%;
                text-align: right;
                position: relative;
                padding-left: 50%;
                border: none;
            }
            .table-custom td::before {
                content: attr(data-label);
                position: absolute;
                left: 1rem;
                width: 45%;
                padding-right: 1rem;
                text-align: left;
                font-weight: 600;
                color: var(--dark-color);
            }
            .table-custom td:first-child {
                background: var(--light-color);
                border-top-left-radius: 1rem;
                border-top-right-radius: 1rem;
            }
            .table-custom td:last-child {
                border-bottom-left-radius: 1rem;
                border-bottom-right-radius: 1rem;
            }
            .table-custom .btn-toggle-details {
                display: block;
                width: 100%;
                text-align: center;
                border-top: 1px solid #dee2e6;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="card mb-4 custom-card">
            <div class="custom-header">
                <div class="d-flex align-items-center">
                    <img src="icon/amsos.ico" alt="Logo" style="height: 50px; margin-right: 15px;">
                    <div>
                        <h2 class="mb-0 text-white"><i class="bi bi-clipboard-data"></i> ICT-AMSOS Reports</h2>
                        <small class="text-white-50">Asset Management and Service Optimization System</small>
                    </div>
                </div>
            </div>
            <div class="card-body bg-white rounded-bottom-4 text-center">
                <h4 class="text-muted mb-2"><?= $date_label ?></h4>
                <div class="badge bg-primary fs-6 py-2 px-3">Total Records: <?= $totalRecords ?></div>
            </div>
        </div>

        <?php if ($connectionError !== ''): ?>
        <div class="alert alert-warning">
            Request data is temporarily unavailable. Please check the live server database connection.
        </div>
        <?php endif; ?>

        <?php if ($canEditRequests): ?>
            <div class="filter-card mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="mb-0 text-primary"><i class="bi bi-funnel me-2"></i>Filter Results</h5>
                    <span class="badge bg-light text-primary border">Airline-style date picker</span>
                </div>

                <form method="GET" id="dateRangeForm" class="row g-3 align-items-end">
                    <input type="hidden" name="date_filter" id="date_filter" value="<?= htmlspecialchars($date_filter) ?>">
                    <input type="hidden" name="from_date" id="from_date" value="<?= htmlspecialchars($from_date) ?>">
                    <input type="hidden" name="to_date" id="to_date" value="<?= htmlspecialchars($to_date) ?>">
                    <input type="hidden" name="show_rows" value="<?= htmlspecialchars($show_rows) ?>">

                    <div class="col-12 col-lg-8 position-relative">
                        <label class="form-label text-muted small mb-2">Date Range</label>
                        <button type="button" class="btn date-range-trigger w-100 text-start" id="dateRangeTrigger" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="text-uppercase text-muted small fw-semibold">Select range</div>
                                    <div class="fw-semibold fs-5" id="dateRangeText">Loading...</div>
                                </div>
                                <div class="text-end">
                                    <i class="bi bi-calendar3 fs-3 text-primary"></i>
                                </div>
                            </div>
                        </button>

                        <div class="dropdown-menu date-picker-dropdown shadow-lg border-0 p-0" aria-labelledby="dateRangeTrigger">
                            <div class="date-picker-shell">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div>
                                        <div class="text-uppercase text-muted small fw-semibold">Booking style</div>
                                        <div class="fw-semibold">Pick departure and return style dates</div>
                                    </div>
                                    <div class="small text-muted text-end" id="datePickerSummary">Choose a date range</div>
                                </div>

                                <div class="date-picker-presets">
                                    <button type="button" class="preset-chip" data-preset="today">Today</button>
                                    <button type="button" class="preset-chip" data-preset="this_week">This Week</button>
                                    <button type="button" class="preset-chip" data-preset="this_month">This Month</button>
                                    <button type="button" class="preset-chip" data-preset="clear">Clear</button>
                                </div>

                                <div class="date-picker-calendars">
                                    <div class="calendar-pane">
                                        <div class="calendar-head">
                                            <button type="button" class="calendar-nav" id="prevMonthBtn" aria-label="Previous month"><i class="bi bi-chevron-left"></i></button>
                                            <div class="calendar-title" id="leftMonthTitle"></div>
                                            <button type="button" class="calendar-nav" id="nextMonthBtn" aria-label="Next month"><i class="bi bi-chevron-right"></i></button>
                                        </div>
                                        <div class="weekday-row" id="leftWeekdays"></div>
                                        <div class="day-grid" id="leftCalendar"></div>
                                    </div>

                                    <div class="calendar-pane d-none d-lg-block">
                                        <div class="calendar-head">
                                            <div class="calendar-title" id="rightMonthTitle"></div>
                                            <span class="small text-muted">Return</span>
                                        </div>
                                        <div class="weekday-row" id="rightWeekdays"></div>
                                        <div class="day-grid" id="rightCalendar"></div>
                                    </div>
                                </div>

                                <div class="date-picker-footer">
                                    <button type="button" class="btn btn-outline-secondary" id="clearRangeBtn"><i class="bi bi-x-lg me-2"></i>Clear</button>
                                    <button type="button" class="btn btn-primary" id="applyRangeBtn"><i class="bi bi-check2 me-2"></i>Apply Filter</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label text-muted small mb-2">Current Selection</label>
                        <div class="airline-summary-box">
                            <div class="fw-semibold" id="activeDateRangeLabel">Any date</div>
                            <div class="small text-muted">Tap the field above to choose a flight-style range</div>
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4><i class="bi bi-table me-2"></i>Records</h4>
            <div class="d-flex flex-wrap justify-content-end gap-2">
                <button type="button" class="btn btn-outline-primary" id="copyShareLinkBtn" data-share-url="<?= htmlspecialchars($shareUrl) ?>">
                    <i class="bi bi-link-45deg me-2"></i>Copy Share Link
                </button>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear me-2"></i>Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#analysisModal">
                            <i class="bi bi-graph-up me-2"></i>Data Analysis
                        </a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#aiModal">
                            <i class="bi bi-robot me-2"></i>AI Insights
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#printModal">
                            <i class="bi bi-printer me-2"></i>View All
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>

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
                                <th class="d-md-none"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                            <?php $count = 1; while ($row = $result->fetch_assoc()): ?>
                            <tr id="row-<?= $row['id'] ?>" class="align-middle">
                                <td data-label="#">
                                    <span class="d-none d-md-block"><?= $count++ ?></span>
                                    <button class="btn btn-sm btn-link d-md-none p-0" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?= $row['id'] ?>" aria-expanded="false" aria-controls="details-<?= $row['id'] ?>">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                </td>
                                <td data-label="Ticket #"><span class="badge bg-secondary"><?= $row['ticketNumber'] ?></span></td>
                                <td data-label="Date"><?= date('M j, Y', strtotime($row['date'])) ?></td>
                                <td data-label="Requester">
                                    <div class="d-flex flex-column">
                                        <strong><?= $row['name'] ?></strong>
                                        <small class="text-muted"><?= $row['position'] ?></small>
                                    </div>
                                </td>
                                <td data-label="Office"><?= $row['office'] ?></td>
                                <td data-label="Request Type"><span class="badge bg-info"><?= $row['requestType'] ?></span></td>
                                <td data-label="Rating"><?= getStarRating($row['rate']) ?></td>
                                <td data-label="Actions">
                                    <div class="d-flex gap-2 justify-content-end justify-content-md-start">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" onclick="showModalLoading(this)" 
                                                data-bs-target="#viewDocumentModal<?= $row['id'] ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($canEditRequests): ?>
                                        <a href="edit-receive.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Login as Super Admin to edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr class="d-md-none">
                                <td colspan="8" class="p-0">
                                    <div class="collapse" id="details-<?= $row['id'] ?>">
                                        <div class="p-3 bg-light">
                                            <p><strong>#</strong>: <?= $count - 1 ?></p>
                                            <p><strong>Ticket #</strong>: <span class="badge bg-secondary"><?= $row['ticketNumber'] ?></span></p>
                                            <p><strong>Date</strong>: <?= date('M j, Y', strtotime($row['date'])) ?></p>
                                            <p><strong>Requester</strong>: <?= $row['name'] ?></p>
                                            <p><strong>Position</strong>: <small class="text-muted"><?= $row['position'] ?></small></p>
                                            <p><strong>Office</strong>: <?= $row['office'] ?></p>
                                            <p><strong>Request Type</strong>: <span class="badge bg-info"><?= $row['requestType'] ?></span></p>
                                            <p><strong>Rating</strong>: <?= getStarRating($row['rate']) ?></p>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" onclick="showModalLoading(this)" 
                                                        data-bs-target="#viewDocumentModal<?= $row['id'] ?>">
                                                    <i class="bi bi-eye me-2"></i>View Document
                                                </button>
                                                <?php if ($canEditRequests): ?>
                                                <a href="edit-receive.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-warning w-100">
                                                    <i class="bi bi-pencil me-2"></i>Edit
                                                </a>
                                                <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary w-100" disabled title="Login as Super Admin to edit">
                                                    <i class="bi bi-pencil me-2"></i>Edit
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <div class="modal fade" id="viewDocumentModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="viewDocumentModalLabel<?= $row['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="viewDocumentModalLabel<?= $row['id'] ?>"><i class="bi bi-file-earmark-text me-2"></i>View Document - Ticket #<?= $row['ticketNumber'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-0 position-relative">
                                            <div class="modal-iframe-loading d-flex align-items-center justify-content-center position-absolute top-0 start-0 w-100 h-100 bg-white" id="docLoading<?= $row['id'] ?>" style="z-index: 2;">
                                                <div class="text-center">
                                                    <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                                                    <div class="mt-2 text-muted small">Loading document...</div>
                                                </div>
                                            </div>
                                            <iframe 
                                                data-src="printform-request.php?id=<?= $row['id'] ?>" 
                                                style="width: 100%; height: 70vh; border: none; zoom: 1;" 
                                                id="docIframe<?= $row['id'] ?>">
                                            </iframe>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No request data available.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printModalLabel"><i class="bi bi-printer me-2"></i>View All Documents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 position-relative">
                    <div class="modal-iframe-loading d-flex align-items-center justify-content-center position-absolute top-0 start-0 w-100 h-100 bg-white" id="printLoading" style="z-index: 2;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                            <div class="mt-2 text-muted small">Loading document...</div>
                        </div>
                    </div>
                    <iframe 
                        data-src="print-all-requestdata.php?date_filter=<?= urlencode($date_filter) ?>&from_date=<?= urlencode($from_date) ?>&to_date=<?= urlencode($to_date) ?>&show_rows=<?= (int)$show_rows ?>" 
                        style="width: 100%; height: 70vh; border: none; zoom: 1;" 
                        id="printAllIframe">
                    </iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="analysisModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-graph-up"></i> Data Analysis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe src="analysis.php?date_filter=<?= $date_filter ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&show_rows=<?= $show_rows ?>" style="width: 100%; height: 70vh; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="aiModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-robot"></i> AI Insights</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>AI analysis is coming soon...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const weekdayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const state = {
                from: null,
                to: null,
                viewDate: new Date(),
                preset: 'custom'
            };

            const els = {};

            function pad(value) {
                return String(value).padStart(2, '0');
            }

            function toLocalIso(date) {
                return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
            }

            function parseLocalIso(value) {
                if (!value) return null;
                const parts = value.split('-').map(Number);
                if (parts.length !== 3 || parts.some(Number.isNaN)) return null;
                return new Date(parts[0], parts[1] - 1, parts[2]);
            }

            function cloneDate(date) {
                return new Date(date.getFullYear(), date.getMonth(), date.getDate());
            }

            function isSameDay(a, b) {
                return a && b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
            }

            function compareDates(a, b) {
                const left = cloneDate(a).setHours(0, 0, 0, 0);
                const right = cloneDate(b).setHours(0, 0, 0, 0);
                return left - right;
            }

            function addMonths(date, months) {
                return new Date(date.getFullYear(), date.getMonth() + months, 1);
            }

            function startOfWeek(date) {
                const result = cloneDate(date);
                result.setDate(result.getDate() - result.getDay());
                return result;
            }

            function formatLong(date) {
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
            }

            function formatMonth(date) {
                return date.toLocaleDateString('en-US', {
                    month: 'long',
                    year: 'numeric'
                });
            }

            function getRangeLabel() {
                if (state.preset === 'this_month') {
                    return 'This Month';
                }

                if (state.from && state.to) {
                    if (isSameDay(state.from, state.to)) {
                        return formatLong(state.from);
                    }
                    return `${formatLong(state.from)} - ${formatLong(state.to)}`;
                }

                if (state.from) {
                    return formatLong(state.from);
                }

                return 'Any date';
            }

            function syncHiddenFields() {
                const dateFilter = document.getElementById('date_filter');
                const fromInput = document.getElementById('from_date');
                const toInput = document.getElementById('to_date');

                if (!dateFilter || !fromInput || !toInput) return;

                dateFilter.value = state.preset === 'this_month' ? 'this_month' : 'custom';
                fromInput.value = state.from ? toLocalIso(state.from) : '';
                toInput.value = state.to ? toLocalIso(state.to) : '';
            }

            function updateText() {
                const rangeLabel = getRangeLabel();
                const rangeText = document.getElementById('dateRangeText');
                const activeLabel = document.getElementById('activeDateRangeLabel');
                const summary = document.getElementById('datePickerSummary');

                if (rangeText) rangeText.textContent = rangeLabel;
                if (activeLabel) activeLabel.textContent = rangeLabel;
                if (summary) summary.textContent = rangeLabel === 'Any date' ? 'No date restriction' : rangeLabel;
            }

            function renderWeekdays(target) {
                if (!target) return;
                target.innerHTML = weekdayLabels.map(day => `<span>${day}</span>`).join('');
            }

            function buildCalendar(monthDate) {
                const firstDay = new Date(monthDate.getFullYear(), monthDate.getMonth(), 1);
                const startDate = cloneDate(firstDay);
                startDate.setDate(startDate.getDate() - startDate.getDay());

                const today = new Date();
                const cells = [];

                for (let i = 0; i < 42; i++) {
                    const cellDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate() + i);
                    const inMonth = cellDate.getMonth() === monthDate.getMonth();
                    const iso = toLocalIso(cellDate);
                    const classes = ['day-cell', 'btn', 'btn-sm', 'p-0'];

                    if (!inMonth) classes.push('outside-month');
                    if (isSameDay(cellDate, today)) classes.push('today');

                    const hasRange = state.from && state.to;
                    if (hasRange && compareDates(cellDate, state.from) >= 0 && compareDates(cellDate, state.to) <= 0) {
                        classes.push('in-range');
                    }

                    if (state.from && isSameDay(cellDate, state.from)) classes.push('range-start');
                    if (state.to && isSameDay(cellDate, state.to)) classes.push('range-end');

                    cells.push(`
                        <button type="button" class="${classes.join(' ')}" data-date="${iso}" aria-label="${cellDate.toDateString()}">
                            ${cellDate.getDate()}
                        </button>
                    `);
                }

                return cells.join('');
            }

            function renderCalendars() {
                const leftTitle = document.getElementById('leftMonthTitle');
                const rightTitle = document.getElementById('rightMonthTitle');
                const leftCalendar = document.getElementById('leftCalendar');
                const rightCalendar = document.getElementById('rightCalendar');

                if (!leftTitle || !leftCalendar) return;

                const leftMonth = new Date(state.viewDate.getFullYear(), state.viewDate.getMonth(), 1);
                const rightMonth = addMonths(leftMonth, 1);

                leftTitle.textContent = formatMonth(leftMonth);
                leftCalendar.innerHTML = buildCalendar(leftMonth);

                if (rightTitle && rightCalendar) {
                    rightTitle.textContent = formatMonth(rightMonth);
                    rightCalendar.innerHTML = buildCalendar(rightMonth);
                }

                updatePresetStates();
            }

            function updatePresetStates() {
                document.querySelectorAll('.preset-chip').forEach(button => {
                    const preset = button.getAttribute('data-preset');
                    button.classList.toggle('active', state.preset === preset);
                });
            }

            function setRange(from, to, preset = 'custom') {
                state.from = from ? cloneDate(from) : null;
                state.to = to ? cloneDate(to) : null;
                state.preset = preset;
                state.viewDate = state.from || state.to || new Date();
                syncHiddenFields();
                updateText();
                renderCalendars();
            }

            function setPreset(preset) {
                const today = new Date();

                if (preset === 'today') {
                    setRange(today, today, 'today');
                    return;
                }

                if (preset === 'this_week') {
                    setRange(startOfWeek(today), today, 'this_week');
                    return;
                }

                if (preset === 'this_month') {
                    state.from = null;
                    state.to = null;
                    state.preset = 'this_month';
                    state.viewDate = today;
                    syncHiddenFields();
                    updateText();
                    renderCalendars();
                    return;
                }

                state.from = null;
                state.to = null;
                state.preset = 'custom';
                state.viewDate = today;
                syncHiddenFields();
                updateText();
                renderCalendars();
            }

            function handleDayClick(dateString) {
                const selected = parseLocalIso(dateString);
                if (!selected) return;

                if (!state.from || (state.from && state.to)) {
                    state.from = selected;
                    state.to = null;
                    state.preset = 'custom';
                } else if (compareDates(selected, state.from) < 0) {
                    state.to = cloneDate(state.from);
                    state.from = selected;
                    state.preset = 'custom';
                } else if (compareDates(selected, state.from) === 0) {
                    state.to = cloneDate(selected);
                    state.preset = 'custom';
                } else {
                    state.to = selected;
                    state.preset = 'custom';
                }

                state.viewDate = state.from || selected;
                syncHiddenFields();
                updateText();
                renderCalendars();
            }

            function applySelection() {
                if (state.preset !== 'this_month' && state.from && !state.to) {
                    state.to = cloneDate(state.from);
                }

                syncHiddenFields();

                const form = document.getElementById('dateRangeForm');
                const trigger = document.getElementById('dateRangeTrigger');
                const dropdown = trigger ? bootstrap.Dropdown.getOrCreateInstance(trigger) : null;

                if (dropdown) dropdown.hide();
                if (form) form.submit();
            }

            function clearSelection() {
                state.from = null;
                state.to = null;
                state.preset = 'custom';
                state.viewDate = new Date();
                syncHiddenFields();
                updateText();
                renderCalendars();
            }

            function initDatePicker() {
                const trigger = document.getElementById('dateRangeTrigger');
                const leftWeekdays = document.getElementById('leftWeekdays');
                const rightWeekdays = document.getElementById('rightWeekdays');
                const leftCalendar = document.getElementById('leftCalendar');
                const rightCalendar = document.getElementById('rightCalendar');
                const prevBtn = document.getElementById('prevMonthBtn');
                const nextBtn = document.getElementById('nextMonthBtn');
                const applyBtn = document.getElementById('applyRangeBtn');
                const clearBtn = document.getElementById('clearRangeBtn');

                if (!trigger || !leftCalendar || !prevBtn || !nextBtn || !applyBtn || !clearBtn) return;

                renderWeekdays(leftWeekdays);
                renderWeekdays(rightWeekdays);

                const dateFilter = document.getElementById('date_filter');
                const fromInput = document.getElementById('from_date');
                const toInput = document.getElementById('to_date');

                if (dateFilter && dateFilter.value === 'this_month') {
                    state.preset = 'this_month';
                    state.viewDate = new Date();
                } else if (fromInput && fromInput.value) {
                    state.from = parseLocalIso(fromInput.value);
                    state.to = toInput && toInput.value ? parseLocalIso(toInput.value) : null;
                    state.preset = 'custom';
                    state.viewDate = state.from || new Date();
                } else {
                    state.viewDate = new Date();
                }

                syncHiddenFields();
                updateText();
                renderCalendars();

                leftCalendar.addEventListener('click', function(event) {
                    const button = event.target.closest('[data-date]');
                    if (!button) return;
                    handleDayClick(button.getAttribute('data-date'));
                });

                if (rightCalendar) {
                    rightCalendar.addEventListener('click', function(event) {
                        const button = event.target.closest('[data-date]');
                        if (!button) return;
                        handleDayClick(button.getAttribute('data-date'));
                    });
                }

                document.querySelectorAll('.preset-chip').forEach(button => {
                    button.addEventListener('click', function() {
                        setPreset(this.getAttribute('data-preset'));
                    });
                });

                prevBtn.addEventListener('click', function() {
                    state.viewDate = addMonths(state.viewDate, -1);
                    renderCalendars();
                });

                nextBtn.addEventListener('click', function() {
                    state.viewDate = addMonths(state.viewDate, 1);
                    renderCalendars();
                });

                applyBtn.addEventListener('click', applySelection);
                clearBtn.addEventListener('click', clearSelection);

                const dropdown = document.querySelector('.date-picker-dropdown');
                if (dropdown) {
                    dropdown.addEventListener('click', function(event) {
                        event.stopPropagation();
                    });
                }
            }

            document.addEventListener('DOMContentLoaded', initDatePicker);
        })();

        function showModalLoading(button) {
            if (!button || button.dataset.loading === '1') return;
            button.dataset.originalHtml = button.innerHTML;
            button.dataset.loading = '1';
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            button.disabled = true;
        }

        function resetModalLoading(button) {
            if (!button) return;
            const original = button.dataset.originalHtml;
            if (original) {
                button.innerHTML = original;
            }
            button.disabled = false;
            button.dataset.loading = '0';
        }

        function loadModalIframe(modalEl) {
            if (!modalEl) return;
            const iframe = modalEl.querySelector('iframe[data-src]');
            if (!iframe) return;

            if (!iframe.getAttribute('src')) {
                iframe.setAttribute('src', iframe.getAttribute('data-src'));
            }

            const loading = modalEl.querySelector('.modal-iframe-loading');
            if (loading) loading.classList.remove('d-none');

            const setZoom = () => {
                iframe.style.zoom = window.innerWidth <= 767 ? '0.5' : '1';
            };

            setZoom();

            if (iframe.dataset.loaded === '1') {
                if (loading) loading.classList.add('d-none');
                return;
            }

            const onLoad = () => {
                iframe.dataset.loaded = '1';
                if (loading) loading.classList.add('d-none');
                iframe.removeEventListener('load', onLoad);
            };
            iframe.addEventListener('load', onLoad);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const copyShareLinkBtn = document.getElementById('copyShareLinkBtn');
            if (copyShareLinkBtn) {
                const originalHtml = copyShareLinkBtn.innerHTML;

                const copyText = function(text) {
                    if (navigator.clipboard && window.isSecureContext) {
                        return navigator.clipboard.writeText(text);
                    }

                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.setAttribute('readonly', '');
                    textarea.style.position = 'fixed';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.select();
                    const copied = document.execCommand('copy');
                    document.body.removeChild(textarea);

                    return copied ? Promise.resolve() : Promise.reject();
                };

                copyShareLinkBtn.addEventListener('click', function() {
                    copyText(copyShareLinkBtn.dataset.shareUrl).then(function() {
                        copyShareLinkBtn.innerHTML = '<i class="bi bi-check2 me-2"></i>Copied';
                        setTimeout(function() {
                            copyShareLinkBtn.innerHTML = originalHtml;
                        }, 1800);
                    }).catch(function() {
                        window.prompt('Copy this report link:', copyShareLinkBtn.dataset.shareUrl);
                    });
                });
            }

            document.querySelectorAll('.modal').forEach(function(modal) {
                modal.addEventListener('shown.bs.modal', function() {
                    const trigger = document.querySelector('[data-bs-target="#' + modal.id + '"][data-loading="1"]');
                    if (trigger) resetModalLoading(trigger);
                    loadModalIframe(modal);
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.modal').forEach(function(modal) {
                modal.addEventListener('shown.bs.modal', function() {
                    const iframe = modal.querySelector('iframe[data-src]');
                    if (!iframe || iframe.getAttribute('src')) return;

                    iframe.setAttribute('src', iframe.getAttribute('data-src'));
                    iframe.style.zoom = window.innerWidth <= 767 ? '0.5' : '1';
                });
            });

            // Existing mobile collapse icon toggle logic
            const tableBody = document.querySelector('.table-custom tbody');
            if (tableBody) {
                tableBody.addEventListener('show.bs.collapse', function (event) {
                    const button = event.target.closest('tr').querySelector('[data-bs-toggle="collapse"]');
                    if (button) {
                        button.querySelector('i').classList.remove('bi-plus-circle');
                        button.querySelector('i').classList.add('bi-dash-circle');
                    }
                });

                tableBody.addEventListener('hide.bs.collapse', function (event) {
                    const button = event.target.closest('tr').querySelector('[data-bs-toggle="collapse"]');
                    if (button) {
                        button.querySelector('i').classList.remove('bi-dash-circle');
                        button.querySelector('i').classList.add('bi-plus-circle');
                    }
                });

                // NEW: Last clicked row indicator logic
                const mainRows = document.querySelectorAll('.table-custom tbody tr.align-middle');

                mainRows.forEach(row => {
                    row.addEventListener('click', function() {
                        // Remove the highlight class from all main rows
                        mainRows.forEach(r => r.classList.remove('last-clicked-row'));
                        // Add the highlight class to the row just clicked
                        this.classList.add('last-clicked-row');
                    });
                });
            }
        });
    </script>
</body>
</html>
