<?php
require_once 'connect.php';

// Fetch filter values from the URL
$date_filter = $_GET['date_filter'] ?? 'this_month';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

// Build the base WHERE clause for all queries
$where_clause = " WHERE srf.status = 'Completed' ";
if ($date_filter === 'this_month') {
    $where_clause .= " AND MONTH(STR_TO_DATE(srf.date, '%Y-%m-%d')) = MONTH(CURRENT_DATE()) ";
    $date_label = "SRF Data Analysis for " . date('F Y');
} elseif ($date_filter === 'last_month') {
    $where_clause .= " AND MONTH(STR_TO_DATE(srf.date, '%Y-%m-%d')) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) ";
    $date_label = "SRF Data Analysis: Last Month";
} elseif (!empty($from_date) && !empty($to_date)) {
    $from_date = date('Y-m-d', strtotime($from_date));
    $to_date = date('Y-m-d', strtotime($to_date));
    $where_clause .= " AND STR_TO_DATE(srf.date, '%Y-%m-%d') BETWEEN '$from_date' AND '$to_date' ";
    $date_label = "SRF Data Analysis: " . date('M j, Y', strtotime($from_date)) . " - " . date('M j, Y', strtotime($to_date));
} else {
    $date_label = "SRF Data Analysis";
}

// SQL query to count requests by requestType
$requestTypeQuery = "SELECT requestType, COUNT(*) as count FROM srf " . $where_clause . " GROUP BY requestType ORDER BY count DESC";
$requestTypeResult = $conn->query($requestTypeQuery);

// SQL query to count requests by office
$officeQuery = "SELECT office, COUNT(*) as count FROM srf " . $where_clause . " GROUP BY office ORDER BY count DESC LIMIT 5";
$officeResult = $conn->query($officeQuery);

// SQL query to count feedback ratings
$ratingQuery = "SELECT srffeedback.feedback, COUNT(*) as count FROM srf LEFT JOIN srffeedback ON srf.id = srffeedback.srf_id " . $where_clause . " AND srffeedback.feedback IS NOT NULL GROUP BY srffeedback.feedback";
$ratingResult = $conn->query($ratingQuery);

// SQL query to count total completed requests
$totalQuery = "SELECT COUNT(*) as total FROM srf " . $where_clause;
$totalResult = $conn->query($totalQuery);
$totalRequests = $totalResult->fetch_assoc()['total'];

// Calculate average rating
$ratingAvgQuery = "SELECT AVG(CASE feedback 
                        WHEN 'Excellent' THEN 5
                        WHEN 'Very Satisfactory' THEN 4
                        WHEN 'Satisfactory' THEN 3
                        WHEN 'Below Satisfactory' THEN 2
                        WHEN 'Poor' THEN 1
                        ELSE NULL END) as avg_rating 
                   FROM srffeedback 
                   JOIN srf ON srffeedback.srf_id = srf.id " . $where_clause;
$ratingAvgResult = $conn->query($ratingAvgQuery);
$avgRating = $ratingAvgResult->fetch_assoc()['avg_rating'];

// Prepare data for Chart.js
$requestTypeData = [];
while ($row = $requestTypeResult->fetch_assoc()) {
    $requestTypeData[] = ['label' => $row['requestType'], 'count' => $row['count']];
}

$officeData = [];
while ($row = $officeResult->fetch_assoc()) {
    $officeData[] = ['label' => $row['office'], 'count' => $row['count']];
}

$ratingData = [
    'Excellent' => 0, 'Very Satisfactory' => 0, 'Satisfactory' => 0, 'Below Satisfactory' => 0, 'Poor' => 0
];
while ($row = $ratingResult->fetch_assoc()) {
    $ratingData[$row['feedback']] = $row['count'];
}

// Convert data to JSON
$requestTypeJson = json_encode($requestTypeData);
$officeJson = json_encode($officeData);
$ratingJson = json_encode($ratingData);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SRF Data Analysis - ICT-AMSOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            min-height: 100vh;
            padding-bottom: 2rem;
        }
        
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            border: none;
        }
        
        .summary-card {
            text-align: center;
            padding: 1.5rem;
        }
        
        .summary-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0.5rem 0;
        }
        
        .summary-card .title {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
        }
        
        .summary-card i {
            font-size: 2.5rem;
            background: linear-gradient(120deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .filter-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
        }
        
        .filter-btn {
            background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
            border: none;
            transition: var(--transition);
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            padding: 1rem;
        }
        
        .rating-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
            padding: 0.8rem;
            background: rgba(248, 249, 250, 0.7);
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .rating-item:hover {
            background: white;
            transform: translateX(5px);
        }
        
        .rating-bar {
            height: 10px;
            border-radius: 5px;
            margin-top: 5px;
            background: linear-gradient(90deg, var(--success-color), var(--accent-color));
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
        }
        
        .rating-value {
            min-width: 30px;
            font-weight: 600;
            text-align: right;
            margin-left: 10px;
            color: var(--primary-color);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .chart-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .chart-card .card-body {
            flex: 1;
        }
        
        .page-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 2px;
        }
        
        .back-btn {
            background: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            transition: var(--transition);
        }
        
        .back-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="bi bi-bar-chart-line-fill me-2"></i>
                ICT-AMSOS Analytics
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-file-earmark-text me-1"></i> Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-gear me-1"></i> Settings</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div class="mb-3 mb-md-0">
                <h2 class="page-title"><i class="bi bi-graph-up me-2"></i>SRF Data Analysis</h2>
                <p class="text-muted mb-0">Comprehensive analysis of service request forms and customer feedback</p>
            </div>
            <a href="reports.php" class="btn btn-outline-primary back-btn">
                <i class="bi bi-arrow-left me-1"></i> Back to Reports
            </a>
        </div>

        <div class="filter-container">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Filter Period</label>
                    <select class="form-select" name="date_filter" id="date_filter">
                        <option value="this_month" <?= $date_filter === 'this_month' ? 'selected' : '' ?>>This Month</option>
                        <option value="last_month" <?= $date_filter === 'last_month' ? 'selected' : '' ?>>Last Month</option>
                        <option value="custom" <?= !empty($from_date) ? 'selected' : '' ?>>Custom Range</option>
                    </select>
                </div>
                
                <div class="col-md-3" id="from_date_container" style="<?= empty($from_date) ? 'display:none;' : '' ?>">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?= $from_date ?>">
                </div>
                
                <div class="col-md-3" id="to_date_container" style="<?= empty($to_date) ? 'display:none;' : '' ?>">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?= $to_date ?>">
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary filter-btn w-100">
                        <i class="bi bi-funnel me-1"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="text-muted mb-0">Analysis Period</h5>
                <h4 class="fw-bold text-primary"><?= $date_label ?></h4>
            </div>
            <div class="d-flex">
                <button class="btn btn-outline-secondary me-2">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="card summary-card">
                <i class="bi bi-file-earmark-text"></i>
                <div class="number"><?= $totalRequests ?></div>
                <div class="title">Total Requests</div>
            </div>
            
            <div class="card summary-card">
                <i class="bi bi-check-circle"></i>
                <div class="number"><?= $avgRating ? number_format($avgRating, 1) : '0.0' ?>/5.0</div>
                <div class="title">Average Rating</div>
            </div>
            
            <div class="card summary-card">
                <i class="bi bi-people"></i>
                <div class="number"><?= count($officeData) ?></div>
                <div class="title">Departments</div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card chart-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-pie-chart me-2"></i>Requests by Type</span>
                        <span class="badge bg-light text-dark"><?= $totalRequests ?> requests</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="requestTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card chart-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-building me-2"></i>Top Departments</span>
                        <span class="badge bg-light text-dark">Top 5</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="officeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-5">
                <div class="card chart-card">
                    <div class="card-header">
                        <i class="bi bi-star me-2"></i>Customer Satisfaction
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="ratingChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-graph-up me-2"></i>Feedback Distribution</span>
                        <span class="badge bg-light text-dark"><?= array_sum($ratingData) ?> responses</span>
                    </div>
                    <div class="card-body">
                        <?php 
                        $ratingColors = [
                            'Excellent' => '#28a745',
                            'Very Satisfactory' => '#17a2b8',
                            'Satisfactory' => '#ffc107',
                            'Below Satisfactory' => '#fd7e14',
                            'Poor' => '#dc3545'
                        ];
                        
                        $totalResponses = array_sum($ratingData);
                        foreach($ratingData as $rating => $count): 
                            if($totalResponses > 0) {
                                $percentage = ($count / $totalResponses) * 100;
                            } else {
                                $percentage = 0;
                            }
                        ?>
                        <div class="rating-item">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <span><?= $rating ?></span>
                                    <span class="rating-value"><?= $count ?></span>
                                </div>
                                <div class="progress mt-2">
                                    <div class="progress-bar" 
                                         role="progressbar" 
                                         style="width: <?= $percentage ?>%; background-color: <?= $ratingColors[$rating] ?>;" 
                                         aria-valuenow="<?= $percentage ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                            <i class="bi bi-star-fill ms-2" style="color: <?= $ratingColors[$rating] ?>"></i>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle date inputs based on filter selection
        document.getElementById('date_filter').addEventListener('change', function() {
            const showCustom = this.value === 'custom';
            document.getElementById('from_date_container').style.display = showCustom ? 'block' : 'none';
            document.getElementById('to_date_container').style.display = showCustom ? 'block' : 'none';
        });
        
        // Data from PHP
        const requestTypeData = <?= $requestTypeJson ?>;
        const officeData = <?= $officeJson ?>;
        const ratingData = <?= $ratingJson ?>;
        
        // Generate dynamic colors
        function generateColors(count) {
            const colors = [];
            const baseColors = [
                'rgba(67, 97, 238, 0.8)',
                'rgba(72, 149, 239, 0.8)',
                'rgba(76, 201, 240, 0.8)',
                'rgba(58, 12, 163, 0.8)',
                'rgba(136, 96, 208, 0.8)',
                'rgba(0, 168, 232, 0.8)',
                'rgba(106, 76, 147, 0.8)'
            ];
            
            for(let i = 0; i < count; i++) {
                colors.push(baseColors[i % baseColors.length]);
            }
            return colors;
        }
        
        // Chart for Request Types (Doughnut Chart)
        const requestTypeLabels = requestTypeData.map(item => item.label);
        const requestTypeCounts = requestTypeData.map(item => item.count);
        new Chart(document.getElementById('requestTypeChart'), {
            type: 'doughnut',
            data: {
                labels: requestTypeLabels,
                datasets: [{
                    label: 'Number of Requests',
                    data: requestTypeCounts,
                    backgroundColor: generateColors(requestTypeLabels.length),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.chart.getDatasetMeta(0).total;
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
        
        // Chart for Offices (Horizontal Bar Chart)
        const officeLabels = officeData.map(item => item.label);
        const officeCounts = officeData.map(item => item.count);
        new Chart(document.getElementById('officeChart'), {
            type: 'bar',
            data: {
                labels: officeLabels,
                datasets: [{
                    label: 'Requests',
                    data: officeCounts,
                    backgroundColor: generateColors(officeLabels.length),
                    borderColor: '#fff',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.raw} requests`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Chart for Ratings (Bar Chart)
        const ratingLabels = ['Poor', 'Below Satisfactory', 'Satisfactory', 'Very Satisfactory', 'Excellent'];
        const ratingCounts = ratingLabels.map(label => ratingData[label]);
        new Chart(document.getElementById('ratingChart'), {
            type: 'bar',
            data: {
                labels: ratingLabels,
                datasets: [{
                    label: 'Number of Ratings',
                    data: ratingCounts,
                    backgroundColor: [
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(253, 126, 20, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(40, 167, 69, 0.8)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.raw} ratings`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>