<?php
require_once 'connect.php';

// Fetch filter values
$date_filter = $_GET['date_filter'] ?? 'this_month';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$show_rows = $_GET['show_rows'] ?? 100; // Default to 100 rows

// Sanitize and validate show_rows
$show_rows = (int)$show_rows;
if ($show_rows < 1) {
    $show_rows = 100; // Set minimum to 100 rows
}

// Build query
$query = "SELECT srf.*, srffeedback.feedback AS rate 
          FROM srf 
          LEFT JOIN srffeedback ON srf.id = srffeedback.srf_id 
          WHERE srf.status = 'Completed'";

if ($date_filter === 'this_month') {
    $query .= " AND MONTH(STR_TO_DATE(srf.date, '%Y-%m-%d')) = MONTH(CURRENT_DATE()) ";
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
$result = $conn->query($query);

function getStarRating($feedback) {
    $ratings = [
        'Excellent' => ['stars' => '★★★★★', 'color' => 'text-success'],
        'Very Satisfactory' => ['stars' => '★★★★☆', 'color' => 'text-primary'],
        'Satisfactory' => ['stars' => '★★★☆☆', 'color' => 'text-info'],
        'Below Satisfactory' => ['stars' => '★★☆☆☆', 'color' => 'text-warning'],
        'Poor' => ['stars' => '★☆☆☆☆', 'color' => 'text-danger'],
    ];
    return isset($ratings[$feedback]) ? "<span class='{$ratings[$feedback]['color']}'>{$ratings[$feedback]['stars']}</span>" : 'N/A';
}
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
        }

        .table-custom tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
            transform: scale(1.01);
        }
        
        .table-custom tbody tr:last-child {
            border-bottom: none;
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
                <div class="badge bg-primary fs-6 py-2 px-3">Total Records: <?= $result->num_rows ?></div>
            </div>
        </div>

        <?php if (!isset($_SESSION['User_RoleSRF']) || $_SESSION['User_RoleSRF'] != "Super_admin"): ?>
        <?php else: ?>
            <div class="filter-card mb-4">
                <h5 class="mb-3 text-primary"><i class="bi bi-funnel me-2"></i>Filter Results</h5>
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="date_filter" class="form-label text-muted small">Date Range</label>
                        <select name="date_filter" id="date_filter" class="form-select" onchange="toggleDateRange()">
                            <option value="this_month" <?= $date_filter === 'this_month' ? 'selected' : '' ?>>This Month</option>
                            <option value="custom" <?= $date_filter === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="from_date" class="form-label text-muted small">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="<?= $from_date ?>" id="from_date" 
                               <?= $date_filter === 'custom' ? '' : 'disabled' ?>>
                    </div>
                    <div class="col-md-3">
                        <label for="to_date" class="form-label text-muted small">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="<?= $to_date ?>" id="to_date" 
                               <?= $date_filter === 'custom' ? '' : 'disabled' ?>>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-2"></i>Apply Filter</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4><i class="bi bi-table me-2"></i>Records</h4>
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
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                data-bs-target="#viewDocumentModal<?= $row['id'] ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
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
                                                <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" 
                                                        data-bs-target="#viewDocumentModal<?= $row['id'] ?>">
                                                    <i class="bi bi-eye me-2"></i>View Document
                                                </button>
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
                                        <div class="modal-body p-0">
                                            <iframe 
                                                src="printform-request.php?id=<?= $row['id'] ?>" 
                                                style="width: 100%; height: 70vh; border: none; zoom: 1;" 
                                                id="docIframe<?= $row['id'] ?>">
                                            </iframe>
                                            <script>
                                                // Responsive zoom for iframe on mobile
                                                (function() {
                                                    function setIframeZoom() {
                                                        var iframe = document.getElementById('docIframe<?= $row['id'] ?>');
                                                        if (!iframe) return;
                                                        if (window.innerWidth <= 767) {
                                                            iframe.style.zoom = "0.5";
                                                        } else {
                                                            iframe.style.zoom = "1";
                                                        }
                                                    }
                                                    window.addEventListener('resize', setIframeZoom);
                                                    document.addEventListener('DOMContentLoaded', setIframeZoom);
                                                })();
                                            </script>
              
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
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
                <div class="modal-body p-0">
                    <iframe 
                        src="print-all.php?date_filter=<?= $date_filter ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&show_rows=<?= $show_rows ?>" 
                        style="width: 100%; height: 70vh; border: none; zoom: 1;" 
                        id="printAllIframe">
                    </iframe>
                    <script>
                        // Responsive zoom for iframe on mobile
                        (function() {
                            function setIframeZoom() {
                                var iframe = document.getElementById('printAllIframe');
                                if (!iframe) return;
                                if (window.innerWidth <= 767) {
                                    iframe.style.zoom = "0.5";
                                } else {
                                    iframe.style.zoom = "1";
                                }
                            }
                            window.addEventListener('resize', setIframeZoom);
                            document.addEventListener('DOMContentLoaded', setIframeZoom);
                        })();
                    </script>
           
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
        function toggleDateRange() {
            const filter = document.getElementById("date_filter").value;
            document.getElementById("from_date").disabled = filter !== "custom";
            document.getElementById("to_date").disabled = filter !== "custom";
        }

        document.addEventListener('DOMContentLoaded', function() {
            const tableBody = document.querySelector('.table-custom tbody');
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
        });
    </script>
</body>
</html>