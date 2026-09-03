<?php
// Database connection
// Assuming 'connect.php' properly sets up $conn and $_SESSION['OfficeSRF']
require_once 'connect.php';

// --- MODIFIED: Set current month as default if no month is selected ---
$currentYear = date('Y');
$currentMonthNum = date('m');
$defaultMonthKey = date('Y-m', mktime(0, 0, 0, $currentMonthNum, 1, $currentYear));

$selectedMonth = isset($_GET['month']) ? $_GET['month'] : $defaultMonthKey; // Default to current month


// Fetch pending requests for the selected office
$office = $_SESSION['OfficeSRF'];

// Start with the base query for all pending requests for the office.
// The month filtering will now primarily happen on the client-side via JavaScript.
$sql = "SELECT * FROM srf WHERE status NOT IN ('Completed', 'Disapproved') AND office = ?";

$sql .= " ORDER BY date DESC, ticketNumber DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $office); // Only bind the office parameter here
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}

function srfWaitingGetSignerByPersonelId($conn, $personelId)
{
    $stmt = $conn->prepare("SELECT name, role, position, station FROM srfsigner WHERE personelid = ? ORDER BY level ASC LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $personelId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row ?: null;
}

function srfWaitingGetActionStaffByPersonelId($conn, $personelId)
{
    $stmt = $conn->prepare("SELECT name, role, station FROM srfactionstaff WHERE personelid = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $personelId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row ?: null;
}

function srfWaitingGetNextSigner($conn, $office, $station, $level)
{
    $nextLevel = (int)$level + 1;
    $stmt = $conn->prepare("SELECT name, role, position, station FROM srfsigner WHERE office = ? AND station = ? AND level = ? ORDER BY id ASC LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("ssi", $office, $station, $nextLevel);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row ?: null;
}

function srfWaitingFormatPerson($person)
{
    if (!$person) {
        return '';
    }

    $name = trim((string)($person['name'] ?? ''));
    $position = trim((string)($person['position'] ?? ''));
    $role = trim((string)($person['role'] ?? ''));
    $station = trim((string)($person['station'] ?? ''));
    $details = array_filter([$position ?: $role, $station]);

    return $name . (!empty($details) ? ' (' . implode(' - ', $details) . ')' : '');
}

function srfWaitingBuildRouteInfo($conn, $row)
{
    $status = (string)($row['status'] ?? '');
    $level = (int)($row['level'] ?? 0);
    $tracking = (int)($row['tracking'] ?? 0);
    $office = (string)($row['office'] ?? '');
    $station = (string)($row['station'] ?? '');

    if (stripos($status, 'Disapproved') !== false) {
        return ['current' => 'Request disapproved', 'next' => 'No next destination'];
    }

    if (stripos($status, 'Completed') !== false || $tracking === 102) {
        return ['current' => 'Request completed', 'next' => 'Released/closed'];
    }

    if ($level >= 101) {
        $staff = srfWaitingGetActionStaffByPersonelId($conn, $tracking);
        $staffName = srfWaitingFormatPerson($staff);

        if (stripos($status, 'Now Serving') !== false) {
            return [
                'current' => $staffName ? 'Now serving at RICTU - ' . $staffName : 'Now serving at RICTU Help Desk',
                'next' => 'Action taken / completion of request'
            ];
        }

        if (stripos($status, 'Assigned') !== false) {
            return [
                'current' => $staffName ? 'Assigned to RICTU staff - ' . $staffName : 'Assigned to RICTU staff',
                'next' => 'Receive request, perform action, then complete'
            ];
        }

        return [
            'current' => 'At RICTU / Chief RICTU queue',
            'next' => 'Assign to RICTU action staff'
        ];
    }

    $currentSigner = srfWaitingFormatPerson(srfWaitingGetSignerByPersonelId($conn, $tracking));
    $nextSigner = srfWaitingFormatPerson(srfWaitingGetNextSigner($conn, $office, $station, $level));

    return [
        'current' => $currentSigner ? 'For approval/signature of ' . $currentSigner : 'For approval/signature',
        'next' => $nextSigner ? 'Forward to ' . $nextSigner : 'Forward to ICT/RICTU'
    ];
}

// Calculate initial statistics (based on the fetched data for the selected month/all)
// This will now operate on ALL pending requests initially, which is correct for dashboard stats
$totalPending = count($requests);
$assignedCount = 0;
$onProcessCount = 0;
$nowServingCount = 0; // Initialize Now Serving Count
$urgentCount = 0;

foreach ($requests as $req) {
    if (strpos($req['status'], 'Assigned') !== false) {
        $assignedCount++;
    }
    if (strpos($req['status'], 'Process') !== false || strpos($req['status'], 'Serving') !== false) {
        $onProcessCount++;
    }
    // New condition for 'Now Serving'
    if (strpos($req['status'], 'Now Serving') !== false) {
        $nowServingCount++;
    }
    $requestDate = new DateTime($req['date']);
    $now = new DateTime();
    $interval = $now->diff($requestDate);
    if ($interval->days > 5) {
        $urgentCount++;
    }
}

// --- IMPORTANT CHANGE FOR MONTHS DROPDOWN GENERATION ---
// Generate months for the dropdown - this now needs to scan all available dates
// from the fetched $requests to ensure all months with data are present.
$months = [];
$earliestYear = date('Y') - 2; // Default if no requests at all, show last 2 years + current

// If there are any requests, find the earliest date among ALL of them
if (!empty($requests)) {
    $allDates = array_column($requests, 'date');
    if (!empty($allDates)) {
        // Find the absolute earliest date among all fetched requests
        $firstDate = new DateTime(min($allDates));
        $earliestYear = min($earliestYear, (int)$firstDate->format('Y'));
    }
}

$currentYear = (int)date('Y');
$currentMonthNum = (int)date('m');

// Iterate from the current year back to the earliest year found or default
for ($y = $currentYear; $y >= $earliestYear; $y--) {
    // For the current year, iterate only up to the current month (July 2025)
    // For past years, iterate all 12 months (e.g., Dec 2024 down to Jan 2024)
    $startMonth = ($y == $currentYear) ? $currentMonthNum : 12;
    for ($m = $startMonth; $m >= 1; $m--) {
        $monthKey = date('Y-m', mktime(0, 0, 0, $m, 1, $y));
        $monthName = date('F Y', mktime(0, 0, 0, $m, 1, $y));
        $months[$monthKey] = $monthName;
    }
}
krsort($months); // Sort months by key (YYYY-MM) in reverse chronological order (latest first)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #4a69bd; /* A slightly softer blue */
            --secondary-dark: #2c3e50;
            --success-green: #2ecc71;
            --warning-orange: #f39c12;
            --danger-red: #e74c3c;
            --info-purple: #9b59b6;
            --light-grey: #ecf0f1;
            --dark-text: #34495e;
            --card-border-radius: 12px;
            --shadow-light: 0 4px 15px rgba(0,0,0,0.08);
            --shadow-hover: 0 8px 25px rgba(0,0,0,0.12);
        }

        body {
            background-color: var(--light-grey);
            font-family: 'Inter', sans-serif; /* Using a more modern font */
            color: var(--dark-text);
        }

        .header {
            background: linear-gradient(105deg, var(--primary-blue), #6a89cc);
            color: white;
            padding: 30px 0;
            margin-bottom: 40px;
            box-shadow: var(--shadow-light);
            border-bottom-left-radius: 25px;
            border-bottom-right-radius: 25px;
        }

        .header h1 {
            font-weight: 700;
            font-size: 2.5rem;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .stats-card {
            border-radius: var(--card-border-radius);
            box-shadow: var(--shadow-light);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 25px;
            border: none;
            overflow: hidden;
            background-color: white;
            position: relative;
        }

        .stats-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .card-icon {
            font-size: 2.5rem;
            opacity: 0.2;
            position: absolute;
            top: 15px;
            right: 20px;
        }

        .card-header-stats {
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .card-value {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1;
            margin-top: 10px;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
        }

        .status-badge .fas {
            font-size: 0.6em;
            margin-right: 6px;
        }
        
        /* Status specific styles */
        .status-New {
            background-color: rgba(74, 105, 189, 0.15); /* primary-blue lighter */
            color: var(--primary-blue);
        }
        .status-Assigned {
            background-color: rgba(155, 89, 182, 0.15); /* info-purple lighter */
            color: var(--info-purple);
        }
        .status-Processing, .status-OnProcess, .status-NowServing {
            background-color: rgba(243, 156, 18, 0.15); /* warning-orange lighter */
            color: var(--warning-orange);
        }
        .status-Forwarded {
            background-color: rgba(46, 204, 113, 0.15); /* success-green lighter */
            color: var(--success-green);
        }
        .status-Urgent {
            background-color: rgba(231, 76, 60, 0.15); /* danger-red lighter */
            color: var(--danger-red);
        }

        .priority-label {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
        }
        .priority-high {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-red);
        }
        .priority-medium {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-orange);
        }
        .priority-low {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-green);
        }

        .request-card {
            border-radius: var(--card-border-radius);
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
            margin-bottom: 20px;
            border-left: 6px solid var(--primary-blue);
            background-color: white;
            overflow: hidden;
        }

        .request-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-4px);
        }

        .request-card.priority-high-border {
            border-left-color: var(--danger-red);
        }
        .request-card.priority-medium-border {
            border-left-color: var(--warning-orange);
        }
        .request-card.priority-low-border {
            border-left-color: var(--success-green);
        }

        .request-title {
            font-weight: 700;
            color: var(--secondary-dark);
            font-size: 1.25rem;
            margin-bottom: 8px;
        }

        .request-details span {
            font-size: 0.9rem;
            color: #6c757d;
            margin-right: 15px;
            display: inline-flex;
            align-items: center;
        }
        .request-details span i {
            margin-right: 5px;
            color: #aeb6bf;
        }

        .route-info {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 14px;
        }

        .route-step {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 14px;
        }

        .route-label {
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.6px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .route-value {
            color: var(--secondary-dark);
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .route-step i {
            color: var(--primary-blue);
            margin-right: 6px;
        }

        @media (max-width: 768px) {
            .route-info {
                grid-template-columns: 1fr;
            }
        }

        .action-btn {
            border-radius: 25px;
            padding: 8px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .filter-btn {
            border-radius: 25px;
            padding: 8px 20px;
            margin-right: 10px;
            margin-bottom: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            background-color: white;
            color: var(--primary-blue);
            border: 1px solid var(--primary-blue);
            transition: all 0.2s ease;
        }
        .filter-btn.active, .filter-btn:hover {
            background-color: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
            box-shadow: 0 2px 8px rgba(74, 105, 189, 0.3);
            transform: translateY(-2px);
        }
        .filter-btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px; /* Adds space between buttons */
        }

        .search-container {
            position: relative;
            width: 100%;
        }

        .search-container i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #aeb6bf;
        }

        .search-container input, .month-select {
            padding-left: 45px;
            border-radius: 25px;
            border: 1px solid #ced4da;
            height: 45px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background-color: white; /* Ensure consistent background */
        }
        .search-container input:focus, .month-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.25rem rgba(74, 105, 189, 0.25);
            outline: none; /* Remove default outline */
        }
        .month-select {
            padding-left: 15px; /* Adjust padding for dropdown */
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        .select-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            pointer-events: none; /* Make icon unclickable */
        }


        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 0;
            font-weight: 700;
            color: var(--secondary-dark);
            font-size: 1.8rem;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 70px;
            height: 4px;
            background: var(--primary-blue);
            border-radius: 3px;
        }

        .request-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 30px;
        }

        .expand-btn {
            border-radius: 999px;
            font-weight: 700;
            padding: 8px 16px;
        }

        .request-section:fullscreen {
            background: var(--light-grey);
            overflow: auto;
            padding: 30px;
        }

        .request-section.is-expanded {
            background: var(--light-grey);
            bottom: 0;
            left: 0;
            overflow: auto;
            padding: 30px;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 9999;
        }

        @media (max-width: 576px) {
            .request-section-header {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background-color: white;
            border-radius: var(--card-border-radius);
            box-shadow: var(--shadow-light);
            margin-top: 30px;
        }

        .empty-state i {
            font-size: 6rem;
            color: #e9ecef;
            margin-bottom: 25px;
        }

        .empty-state h4 {
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .empty-state p {
            color: #868e96;
            font-size: 1.1rem;
        }

        .pagination .page-item .page-link {
            border-radius: 20px;
            margin: 0 5px;
            min-width: 40px;
            text-align: center;
            color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            color: white;
        }
        .pagination .page-item:not(.active) .page-link:hover {
            background-color: var(--light-grey);
        }
        /* Adjusted the admin user badge */
        .admin-badge {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-clipboard-list me-3"></i>Service Request Dashboard</h1>
                    <p class="mb-0">Overview of all pending technical assistance requests</p>
                </div>

            </div>
        </div>
    </div>
    
    <div class="container mb-5">
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-tasks card-icon text-primary"></i>
                        <div class="card-header-stats">Total Pending</div>
                        <div class="card-value text-primary" id="totalPendingCount">
                            <?php echo $totalPending; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-users card-icon text-info"></i>
                        <div class="card-header-stats">Assigned</div>
                        <div class="card-value text-info" id="assignedCount">
                            <?php echo $assignedCount; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-hourglass-half card-icon text-warning"></i>
                        <div class="card-header-stats">On Process</div>
                        <div class="card-value text-warning" id="onProcessCount">
                            <?php echo $onProcessCount; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-bell-concierge card-icon text-warning"></i> <div class="card-header-stats">Now Serving</div>
                        <div class="card-value text-warning" id="nowServingCount">
                            <?php echo $nowServingCount; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-exclamation-triangle card-icon text-danger"></i>
                        <div class="card-header-stats">Urgent</div>
                        <div class="card-value text-danger" id="urgentCount">
                            <?php echo $urgentCount; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-5 align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="filter-btn-group">
                    <button class="btn filter-btn active" data-filter="all">All Statuses</button>
                    <button class="btn filter-btn" data-filter="Assigned">Assigned</button>
                    <button class="btn filter-btn" data-filter="OnProcess">On Process</button>
                    <button class="btn filter-btn" data-filter="Now Serving">Now Serving</button> <button class="btn filter-btn" data-filter="Forwarded">Forwarded</button>
                    <button class="btn filter-btn" data-filter="Urgent">Urgent</button>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="position-relative">
                    <i class="fas fa-calendar-alt select-icon"></i>
                    <select class="form-select month-select text-center" id="monthFilter" name="month" style="padding-right: 30x;">
                        <option value="all">All Months</option>
                        <?php foreach ($months as $key => $name): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($key == $selectedMonth) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search requests...">
                </div>
            </div>
        </div>
        
        <div class="row request-section" id="requestSection">
            <div class="col-md-12">
                <div class="request-section-header">
                    <h3 class="section-title">Pending Service Requests</h3>
                    <button type="button" class="btn btn-outline-primary expand-btn" id="expandRequestSection">
                        <i class="fas fa-expand me-1"></i> Expand
                    </button>
                </div>
                
                <div id="requestList" class="list-group">
                    <?php if (!empty($requests)): ?>
                        <?php foreach($requests as $row): ?>
                            <?php
                                // Determine status class and priority
                                $statusText = $row['status'];
                                $statusClass = '';
                                $priorityClass = 'priority-medium';
                                $priorityText = 'Medium';
                                $cardBorderClass = 'priority-medium-border';

                                if (strpos($statusText, 'Assigned') !== false) {
                                    $statusClass = 'status-Assigned';
                                    $priorityText = 'Medium';
                                    $priorityClass = 'priority-medium';
                                    $cardBorderClass = 'priority-medium-border';
                                } elseif (strpos($statusText, 'On Process') !== false) { // Specific check for "On Process"
                                    $statusClass = 'status-Processing';
                                    $priorityText = 'High';
                                    $priorityClass = 'priority-high';
                                    $cardBorderClass = 'priority-high-border';
                                } elseif (strpos($statusText, 'Now Serving') !== false) { // Specific check for "Now Serving"
                                    $statusClass = 'status-NowServing';
                                    $priorityText = 'High';
                                    $priorityClass = 'priority-high';
                                    $cardBorderClass = 'priority-high-border';
                                } elseif (strpos($statusText, 'Forwarded') !== false) {
                                    $statusClass = 'status-Forwarded';
                                    $priorityText = 'Low';
                                    $priorityClass = 'priority-low';
                                    $cardBorderClass = 'priority-low-border';
                                } else {
                                    $statusClass = 'status-New'; // Default for other pending statuses
                                    $priorityText = 'Medium';
                                    $priorityClass = 'priority-medium';
                                    $cardBorderClass = 'priority-medium-border';
                                }
                                
                                // Override priority if urgent based on date
                                $requestDate = new DateTime($row['date']);
                                $now = new DateTime();
                                $interval = $now->diff($requestDate);
                                $daysOld = $interval->days;
                                
                                if ($daysOld > 5) {
                                    $statusClass = 'status-Urgent'; // Visual highlight for urgent status
                                    $priorityClass = 'priority-high';
                                    $priorityText = 'URGENT';
                                    $cardBorderClass = 'priority-high-border'; // Urgent gets highest priority border
                                }

                                $routeInfo = srfWaitingBuildRouteInfo($conn, $row);
                            ?>
                             
                            <div class="request-card card <?php echo $cardBorderClass; ?>" 
                                data-status="<?php echo htmlspecialchars($row['status']); ?>" 
                                data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                                data-ticket="<?php echo htmlspecialchars($row['ticketNumber']); ?>" 
                                data-type="<?php echo htmlspecialchars($row['requestType']); ?>"
                                data-route="<?php echo htmlspecialchars($routeInfo['current'] . ' ' . $routeInfo['next']); ?>"
                                data-days-old="<?php echo $daysOld; ?>"
                                data-date-month="<?php echo (new DateTime($row['date']))->format('Y-m'); ?>">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-lg-7 col-md-12 mb-3 mb-lg-0">
                                            <div class="d-flex align-items-start">
                                                <div class="me-3 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px; flex-shrink: 0;">
                                                    <i class="fas fa-ticket-alt text-primary-blue fs-4"></i>
                                                </div>
                                                <div>
                                                    <h5 class="request-title mb-1"><?php echo htmlspecialchars($row['ticketNumber']); ?></h5>
                                                     <div class="request-details d-flex flex-wrap">
                                                         <span><i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($row['name']); ?></span>
                                                         <span><i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($row['divSecUnit']); ?></span>
                                                         <span><i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($row['requestType']); ?></span>
                                                     </div>
                                                     <div class="route-info">
                                                         <div class="route-step">
                                                             <div class="route-label"><i class="fas fa-location-dot"></i>Current Request Status</div>
                                                             <div class="route-value"><?php echo htmlspecialchars($routeInfo['current']); ?></div>
                                                         </div>
                                                         <div class="route-step">
                                                             <div class="route-label"><i class="fas fa-arrow-right-long"></i>Next Document Destination</div>
                                                             <div class="route-value"><?php echo htmlspecialchars($routeInfo['next']); ?></div>
                                                         </div>
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                        <div class="col-lg-5 col-md-12 text-lg-end">
                                            <div class="d-flex flex-wrap justify-content-lg-end align-items-center mt-3 mt-lg-0">
                                                <span class="status-badge <?php echo $statusClass; ?> me-2 mb-2 mb-lg-0">
                                                    <i class="fas fa-circle"></i> <?php echo htmlspecialchars($statusText); ?>
                                                </span>
                                                <span class="priority-label <?php echo $priorityClass; ?> me-2 mb-2 mb-lg-0">
                                                    <i class="fas fa-flag me-1"></i> Priority: <?php echo htmlspecialchars($priorityText); ?>
                                                </span>
                                                <span class="badge bg-light text-dark mb-2 mb-lg-0" style="font-size: 0.8rem; padding: 6px 12px; border-radius: 15px;">
                                                    <i class="fas fa-calendar-alt me-1"></i> <?php echo (new DateTime($row['date']))->format('M d, Y'); ?>
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-lg-end mt-3">
                                                <button class="btn btn-primary action-btn me-2">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </button>
                                                <button class="btn btn-outline-secondary action-btn me-2">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </button>
                                                <button class="btn btn-success action-btn">
                                                    <i class="fas fa-check me-1"></i> Resolve
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard-check"></i>
                            <h4>No Pending Requests Found</h4>
                            <p class="text-muted">It looks like all service requests have been efficiently handled for this selection. Great job!</p>
                            <button class="btn btn-primary mt-3 action-btn" onclick="location.reload()">
                                <i class="fas fa-plus me-1"></i> Create New Request
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <nav class="mt-5 d-flex justify-content-center" id="paginationControls" style="display: <?php echo !empty($requests) ? 'flex' : 'none'; ?>;">
                    <ul class="pagination">
                        </ul>
                </nav>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const searchInput = document.getElementById('searchInput');
            const monthFilter = document.getElementById('monthFilter');
            const requestList = document.getElementById('requestList');
            const requestSection = document.getElementById('requestSection');
            const expandRequestSection = document.getElementById('expandRequestSection');
            const paginationControls = document.getElementById('paginationControls');
            const paginationUl = paginationControls.querySelector('.pagination');

            let allRequestCards = Array.from(document.querySelectorAll('.request-card')); // Store all cards initially
            let filteredCards = []; // Cards that pass status, month, and search filters
            let itemsPerPage = 5; // Number of items per page
            let currentPage = 1;

            // --- Pagination Functions ---
            function displayPage(page) {
                currentPage = page;
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;

                // Hide all cards first
                allRequestCards.forEach(card => card.style.display = 'none');

                // Display only the cards for the current page
                const paginatedCards = filteredCards.slice(startIndex, endIndex);
                paginatedCards.forEach(card => card.style.display = '');

                updateStats(filteredCards); // Update stats based on *filtered* cards (not just displayed page)

                setupPagination(); // Re-generate pagination controls
            }

            function setupPagination() {
                paginationUl.innerHTML = ''; // Clear existing pagination
                const pageCount = Math.ceil(filteredCards.length / itemsPerPage);

                if (pageCount <= 1) {
                    paginationControls.style.display = 'none';
                    return;
                } else {
                    paginationControls.style.display = 'flex';
                }

                // Previous button
                const prevLi = document.createElement('li');
                prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                prevLi.innerHTML = `<a class="page-link" href="#" tabindex="-1" aria-disabled="${currentPage === 1}" data-page="${currentPage - 1}">Previous</a>`;
                paginationUl.appendChild(prevLi);

                // Page numbers
                for (let i = 1; i <= pageCount; i++) {
                    const li = document.createElement('li');
                    li.className = `page-item ${i === currentPage ? 'active' : ''}`;
                    li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
                    paginationUl.appendChild(li);
                }

                // Next button
                const nextLi = document.createElement('li');
                nextLi.className = `page-item ${currentPage === pageCount ? 'disabled' : ''}`;
                nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>`;
                paginationUl.appendChild(nextLi);

                // Add event listeners to newly created pagination links
                paginationUl.querySelectorAll('.page-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetPage = parseInt(this.dataset.page);
                        if (!isNaN(targetPage) && targetPage > 0 && targetPage <= pageCount) {
                            displayPage(targetPage);
                        }
                    });
                });
            }

            // --- Stats Update Function ---
            function updateStats(cardsToCount) {
                let currentTotal = cardsToCount.length;
                let currentAssigned = 0;
                let currentOnProcess = 0;
                let currentNowServing = 0; // Added
                let currentUrgent = 0;

                cardsToCount.forEach(card => {
                    const status = card.dataset.status;
                    const daysOld = parseInt(card.dataset.daysOld);

                    if (status.includes('Assigned')) {
                        currentAssigned++;
                    }
                    if (status.includes('On Process')) { // Specific check for "On Process"
                        currentOnProcess++;
                    }
                    if (status.includes('Now Serving')) { // Specific check for "Now Serving"
                        currentNowServing++;
                    }
                    if (daysOld > 5) { // Urgent is still based on being old, regardless of specific status
                        currentUrgent++;
                    }
                });

                document.getElementById('totalPendingCount').innerText = currentTotal;
                document.getElementById('assignedCount').innerText = currentAssigned;
                document.getElementById('onProcessCount').innerText = currentOnProcess;
                document.getElementById('nowServingCount').innerText = currentNowServing; // Update Now Serving count
                document.getElementById('urgentCount').innerText = currentUrgent;
            }

            // --- Main Filter Function (modified to integrate with pagination) ---
            function filterRequests() {
                const activeStatusFilter = document.querySelector('.filter-btn.active').dataset.filter;
                const selectedMonth = monthFilter.value;
                const searchTerm = searchInput.value.toLowerCase();
                
                filteredCards = allRequestCards.filter(card => {
                    const status = card.dataset.status;
                    const name = card.dataset.name.toLowerCase();
                    const ticket = card.dataset.ticket.toLowerCase();
                    const type = card.dataset.type.toLowerCase();
                    const route = card.dataset.route.toLowerCase();
                    const daysOld = parseInt(card.dataset.daysOld);
                    const cardMonth = card.dataset.dateMonth;

                    const matchesSearch = name.includes(searchTerm) || 
                                            ticket.includes(searchTerm) || 
                                            type.includes(searchTerm) ||
                                            route.includes(searchTerm) ||
                                            status.toLowerCase().includes(searchTerm);

                    let matchesStatusFilter = false;
                    if (activeStatusFilter === 'all') {
                        matchesStatusFilter = true;
                    } else if (activeStatusFilter === 'Urgent') {
                        matchesStatusFilter = daysOld > 5;
                    } else if (activeStatusFilter.includes(',')) { // This part is now likely less needed with distinct buttons
                        const filterStatuses = activeStatusFilter.split(',');
                        matchesStatusFilter = filterStatuses.some(filterStatus => status.includes(filterStatus.trim()));
                    } else {
                        // For single status filters like 'Assigned', 'On Process', 'Now Serving', 'Forwarded'
                        matchesStatusFilter = status.includes(activeStatusFilter);
                    }

                    const matchesMonthFilter = (selectedMonth === 'all' || cardMonth === selectedMonth);

                    return matchesSearch && matchesStatusFilter && matchesMonthFilter;
                });

                // Handle empty state display
                if (filteredCards.length === 0) {
                    let emptyState = document.querySelector('.empty-state');
                    if (!emptyState) { // Create if it doesn't exist
                        emptyState = document.createElement('div');
                        emptyState.className = 'empty-state';
                        emptyState.innerHTML = `
                            <i class="fas fa-frown"></i>
                            <h4>No Results Found</h4>
                            <p class="text-muted">No requests match your current filters or search terms.</p>
                            <button class="btn btn-primary mt-3 action-btn" onclick="location.reload()">
                                <i class="fas fa-redo me-1"></i> Clear Filters
                            </button>
                        `;
                        requestList.after(emptyState);
                    }
                    emptyState.style.display = 'block';
                    paginationControls.style.display = 'none';
                    updateStats([]); // Clear stats when no results
                } else {
                    let emptyState = document.querySelector('.empty-state');
                    if (emptyState) {
                        emptyState.style.display = 'none';
                    }
                    displayPage(1); // Go to the first page of the new filtered results
                }
            }

            // --- Event Listeners ---
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                    filterRequests();
                });
            });

            searchInput.addEventListener('keyup', filterRequests);
            monthFilter.addEventListener('change', filterRequests);

            function setExpandButtonState(isExpanded) {
                expandRequestSection.innerHTML = isExpanded
                    ? '<i class="fas fa-compress me-1"></i> Exit Full Screen'
                    : '<i class="fas fa-expand me-1"></i> Expand';
            }

            expandRequestSection.addEventListener('click', async function() {
                if (document.fullscreenElement === requestSection || requestSection.classList.contains('is-expanded')) {
                    if (document.fullscreenElement && document.exitFullscreen) {
                        await document.exitFullscreen();
                    } else {
                        requestSection.classList.remove('is-expanded');
                        setExpandButtonState(false);
                    }
                    return;
                }

                if (requestSection.requestFullscreen) {
                    await requestSection.requestFullscreen();
                } else {
                    requestSection.classList.add('is-expanded');
                    setExpandButtonState(true);
                }
            });

            document.addEventListener('fullscreenchange', function() {
                setExpandButtonState(document.fullscreenElement === requestSection);
            });

            // Set the month filter dropdown to reflect the initial PHP selection
            // This ensures that if you navigate to ?month=2025-06, the dropdown shows June selected.
            monthFilter.value = "<?php echo htmlspecialchars($selectedMonth); ?>";

            // Initial filter and pagination setup when page loads
            filterRequests(); 
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
