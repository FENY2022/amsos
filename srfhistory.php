<?php

        require_once 'connect.php';

        // Initialize variables
        $start_date = '';
        $end_date = '';
        $total_records = 0;
        $data_summary = [];

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];

            // Prepare SQL query to fetch filtered data
             $sql = "SELECT * FROM srfhistory WHERE date >= '$start_date' AND date <= '$end_date' and office = '".$_SESSION['OfficeSRF']."'";

            
            $result = $conn->query($sql);

            // Initialize counters
            $total_records = $result->num_rows;
            $status_count = [];

            // Loop through the data to create summaries
            while ($row = $result->fetch_assoc()) {
                $status = $row['status'];  // Assuming there's a 'status' column in the table

                // Count occurrences of each status
                if (!isset($status_count[$status])) {
                    $status_count[$status] = 0;
                }
                $status_count[$status]++;
            }

            $data_summary = $status_count;
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Analytics and Reporting SRF</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* General Body Styling */
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
            color: #333;
        }

        /* Header Styling */
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        /* Filter Form Styling */
        .filter-form {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }

        .filter-form .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 10px;
            flex: 1;
            margin-right: 10px;
        }

        .filter-form label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        .filter-form input[type="date"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        /* Button Styling */
        .filter-form button {
            padding: 10px 20px;
            border: none;
            background-color: #007BFF;
            color: #fff;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .filter-form button:hover {
            background-color: #0056b3;
        }

        /* Summary Card Styling */
        .summary-card {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .summary-card h3 {
            margin: 0;
        }

        /* Chart Container Styling */
        .chart-container {
            width: 80%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-top: 20px;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #007BFF;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

<h2>Data Analytics and Reporting</h2>

<!-- Filter Form -->
<form method="POST" class="filter-form">
    <div class="form-group">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" required>
    </div>

    <div class="form-group">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" required>
    </div>

    <button type="submit">Filter Data</button>
</form>

<?php if (!empty($data_summary)): ?>
    <div class="summary-card">
        <h3>Total Records: <?php echo $total_records; ?></h3>
    </div>

    <div class="chart-container">
        <canvas id="statusChart"></canvas>
    </div>
<?php endif; ?>

<!-- Display filtered table if data exists -->
<?php if (!empty($data_summary)): ?>
    <h3>Filtered Data:</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch and display filtered records
            $result = $conn->query("SELECT * FROM srfhistory WHERE date >= '$start_date' AND date <= '$end_date'");
            while ($row = $result->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['details']; ?></td> <!-- Change 'details' based on your table -->
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
    // Prepare data for Chart.js
    const statusLabels = <?php echo json_encode(array_keys($data_summary)); ?>;
    const statusCounts = <?php echo json_encode(array_values($data_summary)); ?>;

    const ctx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: statusLabels,
            datasets: [{
                label: 'Occurrences by Status',
                data: statusCounts,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)'
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
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>
