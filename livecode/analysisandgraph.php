<?php
// inventory.php

// Database connection
require_once 'connect.php'; // Ensure this file establishes $conn as a mysqli object

// Check for database connection errors early
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get the office from the session
$sessionOffice = $_SESSION['OfficeSRF'] ?? '';

// Fetch data for inventory display and analysis
$query = "SELECT * FROM inv_inventory WHERE office = ? ORDER BY id DESC"; // Filter by office and order by ID
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $sessionOffice);
$stmt->execute();
$result = $stmt->get_result();

$equipmentData = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $equipmentData[] = $row;
    }
} else {
    // Handle case where query fails or no data
    if (!$result) {
        error_log("Database query failed: " . $conn->error); // Log the actual error
        // Optionally, display a user-friendly message
        // echo "<div class='alert alert-danger'>Error fetching inventory data. Please try again later.</div>";
    }
}

// Equipment Type Analysis
$equipmentCounts = [];
foreach ($equipmentData as $item) {
    $type = $item['equipmentType'];
    if (!isset($equipmentCounts[$type])) {
        $equipmentCounts[$type] = 0;
    }
    $equipmentCounts[$type]++;
}

// Prepare JSON for JavaScript Chart.js
$equipmentTypes = array_keys($equipmentCounts);
$equipmentCountsValues = array_values($equipmentCounts);

// Find most common equipment (handle empty data)
$mostCommonEquipment = 'N/A';
if (!empty($equipmentCounts)) {
    $mostCommonEquipment = array_search(max($equipmentCounts), $equipmentCounts);
}

// Close database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Inventory Management Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Import Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');



        /* --- Header Styles (Added) --- */
        .header-dashboard {
            background-color: #5b6cb8; /* A shade of purple-blue */
            color: #fff;
            padding: 30px 40px; /* Generous padding */
            margin-bottom: 40px; /* Space below the header */
            border-bottom-left-radius: 15px; /* Rounded corners for the bottom */
            border-bottom-right-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); /* Soft shadow */
            display: flex;
            align-items: center; /* Vertically align items */
            justify-content: space-between; /* Space out title and user */
            flex-wrap: wrap; /* Allow wrapping on small screens */
        }

        .header-dashboard h1 {
            font-size: 2.2em; /* Larger main title */
            margin: 0;
            display: flex;
            align-items: center;
            font-weight: 600; /* Semi-bold */
        }

        .header-dashboard h1 .fa-boxes { /* Assuming you want to use the box icon for inventory */
            font-size: 1.2em; /* Slightly larger icon than text */
            margin-right: 15px; /* Space between icon and text */
        }

        .header-dashboard p {
            font-size: 1.1em; /* Subtitle font size */
            opacity: 0.9; /* Slightly transparent for less prominence */
            margin: 5px 0 0 0; /* Margin below subtitle */
            padding-left: calc(2.2em + 15px); /* Align with main title after icon */
        }

        .header-dashboard .admin-user {
            background-color: rgba(255, 255, 255, 0.2); /* Semi-transparent white for button */
            color: #fff;
            padding: 8px 15px;
            border-radius: 20px; /* Pill shape */
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 8px; /* Space between icon and text */
            transition: background-color 0.3s ease;
        }

        .header-dashboard .admin-user:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        /* --- Enhanced Button Styles --- */
        .styled-button {
            /* Primary button color - a more vibrant, but still professional green */
            background-color: #28a745;
            color: white; /* White text for contrast */
            padding: 12px 28px; /* Slightly more generous padding */
            font-size: 17px; /* A bit larger for prominence */
            font-weight: 600; /* Semi-bold for readability */
            border: none;
            border-radius: 8px; /* More modern, slightly larger rounded corners */
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease; /* Added transform and box-shadow for more dynamic interaction */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            display: inline-block; /* Ensures padding and margin behave as expected */
            text-decoration: none; /* In case it's an anchor tag styled as a button */
            text-align: center;
            white-space: nowrap; /* Prevent text wrapping on small buttons */
            margin-right: 10px; /* Space between buttons */
            margin-bottom: 10px; /* Space below buttons for wrapping */
        }

        .styled-button:hover {
            background-color: #218838; /* Darker green on hover */
            transform: translateY(-2px); /* Slight lift effect */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); /* Enhanced shadow on hover */
        }

        /* Focus states for accessibility (keyboard navigation) */
        .styled-button:focus-visible {
            outline: 3px solid #007bff; /* Clear blue outline */
            outline-offset: 2px; /* Space between button and outline */
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.25); /* Glow effect for focus */
        }

        .styled-button:active {
            background-color: #1e7e34; /* Even darker on click */
            transform: translateY(0); /* Press down effect */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Reduced shadow */
        }

        /* Optional: Secondary Button Style (for modals, etc.) */
        .btn-secondary { /* Overriding Bootstrap's default secondary button */
            background-color: #6c757d !important; /* Important to override Bootstrap */
            border-color: #6c757d !important;
            color: white !important;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #5a6268 !important;
            border-color: #5a6268 !important;
        }


        /* --- Infographic Section --- */
        .infographic {
            display: flex;
            flex-wrap: wrap; /* Allows cards to wrap on smaller screens */
            justify-content: center; /* Center cards when they wrap */
            gap: 25px; /* Consistent spacing between cards */
            margin: 40px 0; /* More vertical margin */
            padding: 0; /* No inner padding for the container itself */
        }

        .info-card {
            background: #ffffff; /* Pure white background for cards */
            border-radius: 12px; /* Slightly more rounded corners for a softer look */
            padding: 25px 30px; /* More generous padding inside cards */
            text-align: center;
            flex: 1; /* Allow cards to grow and shrink */
            min-width: 280px; /* Minimum width for cards before wrapping */
            max-width: 32%; /* Keep it around 3 cards per row for larger screens */
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08); /* More pronounced, but soft shadow */
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth transition for hover */
            border: 1px solid #e0e0e0; /* Subtle border for definition */
        }

        .info-card:hover {
            transform: translateY(-5px); /* Lift effect on hover */
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15); /* Enhanced shadow on hover */
        }

        .info-card h4 {
            font-size: 1.35em; /* Slightly larger heading */
            margin-bottom: 12px;
            color: #0056b3; /* A nice blue for headings to add some color */
            font-weight: 700; /* Bold heading */
        }

        .info-card p {
            font-size: 2.2em; /* Significantly larger for key metrics */
            font-weight: 800; /* Extra bold for impact */
            color: #28a745; /* Green color for the main numbers */
            margin: 0;
            line-height: 1.2; /* Tighter line height for large numbers */
        }

        /* Icon styling for info cards */
        .info-card .fa {
            font-size: 3em; /* Large icon size */
            color: #6c757d; /* Muted color for icons */
            margin-bottom: 15px;
            display: block; /* Ensures it takes up full width */
        }

        /* Form styling */
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        /* Chart styling */
        #equipmentChart {
            max-height: 400px; /* Limit chart height */
            width: 100%; /* Ensure responsiveness */
        }
    </style>
</head>
<body>
    <div class="header-dashboard">
        <div>
            <h1 style="font-family: 'Roboto', sans-serif;"><i class="fa fa-boxes"></i> Inventory Management Dashboard</h1>
            <p style="font-family: 'Roboto', sans-serif;">Overview of all equipment and assets</p>
        </div>

    </div>

    <div class="container mt-5">
        <h2 class="mt-5 mb-3">Key Inventory Insights</h2>
        <div class="infographic">
            <div class="info-card">
                <i class="fa fa-boxes fa-3x mb-2" style="color: #6c757d;"></i>
                <h4>Total Inventory Items</h4>
                <p><?= count($equipmentData); ?></p>
            </div>
            <div class="info-card">
                <i class="fa fa-tag fa-3x mb-2" style="color: #6c757d;"></i>
                <h4>Most Common Equipment Type</h4>
                <p><?= htmlspecialchars($mostCommonEquipment); ?></p>
            </div>
            <div class="info-card">
                <i class="fa fa-chart-pie fa-3x mb-2" style="color: #6c757d;"></i>
                <h4>Unique Equipment Types</h4>
                <p><?= count($equipmentTypes); ?></p>
            </div>
        </div>

        <hr class="my-5">

        <div class="card p-4">
            <h2 class="mb-3">Equipment Type Distribution</h2>
            <div class="form-group mb-4">
                <label for="equipmentFilter">Filter by Equipment Type:</label>
                <select id="equipmentFilter" class="form-control">
                    <option value="All">All Equipment</option>
                    <?php foreach ($equipmentTypes as $equipmentType): ?>
                        <option value="<?= htmlspecialchars($equipmentType); ?>">
                            <?= htmlspecialchars($equipmentType); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <canvas id="equipmentChart"></canvas>
        </div>

        <hr class="my-5">

        <h2 class="mb-3">Related Functions</h2>
        <div class="mb-5">
            <button type="button" onclick="window.location.href='mainmenu.php?dir=analysisandgraph_datafilter'" class="styled-button">
                <i class="fa fa-chart-line"></i> Go to Data Filter Analysis
            </button>
            <button type="button" data-bs-toggle="modal" data-bs-target="#showasset" class="styled-button">
                <i class="fa fa-calendar-alt"></i> Asset Lifecycle Timeline
            </button>
            <button type="button" data-bs-toggle="modal" data-bs-target="#darsrfModal" class="styled-button">
                <i class="fa fa-file-alt"></i> Data Analytics and Reporting on SRF
            </button>
        </div>

        <div class="modal fade" id="showasset" tabindex="-1" aria-labelledby="showassetLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl"> <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="showassetLabel"><i class="fa fa-calendar-alt"></i> Asset Lifecycle Timeline</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">x</button>
                    </div>
                    <div class="modal-body">
                        <iframe src="lifecyclewarrantymonitoring.php" style="width: 100%; height: 80vh; border: none;" title="Asset Lifecycle Timeline"></iframe>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="darsrfModal" tabindex="-1" aria-labelledby="darsrfLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl"> <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="darsrfLabel"><i class="fa fa-file-alt"></i> Data Analytics and Reporting on SRF</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">x</button>
                    </div>
                    <div class="modal-body">
                        <iframe src="srfhistory.php" style="width: 100%; height: 80vh; border: none;" title="SRF Data Analytics"></iframe>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        const ctx = document.getElementById('equipmentChart').getContext('2d');
        let originalLabels = <?= json_encode($equipmentTypes); ?>;
        let originalData = <?= json_encode($equipmentCountsValues); ?>;

        let equipmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: originalLabels,
                datasets: [{
                    label: 'Number of Equipment',
                    data: originalData,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)', // Red
                        'rgba(54, 162, 235, 0.7)', // Blue
                        'rgba(255, 206, 86, 0.7)', // Yellow
                        'rgba(75, 192, 192, 0.7)', // Green
                        'rgba(153, 102, 255, 0.7)', // Purple
                        'rgba(255, 159, 64, 0.7)', // Orange
                        'rgba(199, 199, 199, 0.7)', // Grey
                        'rgba(83, 102, 255, 0.7)', // Indigo
                        'rgba(201, 75, 75, 0.7)' // Dark Red
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(201, 75, 75, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Allows height to be set
                plugins: {
                    legend: {
                        display: false // No need for legend with single dataset
                    },
                    title: {
                        display: true,
                        text: 'Count of Equipment by Type',
                        font: {
                            size: 18,
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Items'
                        },
                        ticks: {
                            precision: 0 // Ensure whole numbers for counts
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Equipment Type'
                        }
                    }
                }
            }
        });

        document.getElementById('equipmentFilter').addEventListener('change', function () {
            let selected = this.value;
            if (selected === 'All') {
                equipmentChart.data.labels = originalLabels;
                equipmentChart.data.datasets[0].data = originalData;
                // Reapply all background colors if going back to 'All'
                equipmentChart.data.datasets[0].backgroundColor = [
                    'rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)', 'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)',
                    'rgba(199, 199, 199, 0.7)', 'rgba(83, 102, 255, 0.7)', 'rgba(201, 75, 75, 0.7)'
                ];
                equipmentChart.data.datasets[0].borderColor = [
                    'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)',
                    'rgba(199, 199, 199, 1)', 'rgba(83, 102, 255, 1)', 'rgba(201, 75, 75, 1)'
                ];
            } else {
                let filteredIndex = originalLabels.indexOf(selected);
                if (filteredIndex !== -1) {
                    equipmentChart.data.labels = [selected];
                    equipmentChart.data.datasets[0].data = [originalData[filteredIndex]];
                    // Use a single color for the filtered bar
                    equipmentChart.data.datasets[0].backgroundColor = ['rgba(75, 192, 192, 0.7)'];
                    equipmentChart.data.datasets[0].borderColor = ['rgba(75, 192, 192, 1)'];
                } else {
                    equipmentChart.data.labels = [selected];
                    equipmentChart.data.datasets[0].data = [0]; // Show 0 if not found
                    equipmentChart.data.datasets[0].backgroundColor = ['rgba(200, 200, 200, 0.7)']; // Grey for no data
                    equipmentChart.data.datasets[0].borderColor = ['rgba(200, 200, 200, 1)'];
                }
            }
            equipmentChart.update();
        });
    </script>
</body>
</html>