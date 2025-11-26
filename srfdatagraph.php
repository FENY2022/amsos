<?php
require_once 'connect.php'; // Include your actual connection file

// Default values for date filter (if not set)
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '2000-01-01'; // Default to an early date
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); // Default to today's date

// Validate the dates to prevent SQL injection
if (DateTime::createFromFormat('Y-m-d', $startDate) === false || DateTime::createFromFormat('Y-m-d', $endDate) === false) {
    die('Invalid date format.');
}

// Use prepared statements to prevent SQL injection
$sql = "SELECT requestType, COUNT(*) as count FROM srf WHERE date BETWEEN ? AND ? GROUP BY requestType";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

// Arrays to hold the request types and the count of each request type
$requestTypes = [];
$requestCounts = [];

if ($result->num_rows > 0) {
    // Fetching data from the database
    while ($row = $result->fetch_assoc()) {
        $requestTypes[] = $row['requestType']; // Add each request type to array
        $requestCounts[] = $row['count']; // Add the count of each request type to array
    }
} else {
    echo "No data found";
}

// Convert PHP arrays to JSON format to use in JavaScript
$requestTypes = json_encode($requestTypes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$requestCounts = json_encode($requestCounts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Service Request Completed</title>
    <style>
        .chart-container {
            width: 80%;
            max-width: 800px;
            margin: 0 auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-form {
            text-align: center;
            margin-bottom: 20px;
        }
        .no-data {
            text-align: center;
            color: red;
        }

        button {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
   
        }

        button:hover {
            background-color: #218838;
        }

        form {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="filter-form">
        <!-- Form for filtering by date -->
        <form method="GET" action="fetchdate.php" onsubmit="return validateDates();" >
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>" required>
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>" required>
            <button type="submit">Filter</button>
        </form>
    </div>

    <div class="chart-container">
        <h2>Total Service Request Completed</h2>
        <canvas id="myChart"></canvas>
        <p class="no-data" id="noDataMessage" style="display: none;">No data available for the selected date range.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Get data from PHP
        const requestTypes = <?php echo $requestTypes; ?>;
        const requestCounts = <?php echo $requestCounts; ?>;

        // Check if there is data before rendering the chart
        if (requestTypes.length > 0 && requestCounts.length > 0) {
            const ctx = document.getElementById('myChart').getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: requestTypes, // Use the request types from the database
                    datasets: [{
                        label: '# of Requests',
                        data: requestCounts, // Use the request counts from the database
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        } else {
            // Display 'no data' message if no data is available
            document.getElementById('noDataMessage').style.display = 'block';
            document.getElementById('myChart').style.display = 'none';
        }

        // Function to validate date inputs
        function validateDates() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                alert('End Date must be greater than or equal to Start Date');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
