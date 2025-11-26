<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Form with Graph</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-container {
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-group button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #218838;
        }
        .graph-container {
            margin-top: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Inventory Form</h2>
        <div class="form-container">
            <form id="inventoryForm">
                <table>
                    <tr>
                        <th>Employee's Name</th>
                        <td><input type="text" id="employeeName" name="employeeName" required></td>
                    </tr>
                    <tr>
                        <th>Type of ICT Equipment</th>
                        <td><input type="text" id="ictType" name="ictType" required></td>
                    </tr>
                    <tr>
                        <th>Year Acquired</th>
                        <td><input type="number" id="yearAcquired" name="yearAcquired" required></td>
                    </tr>
                    <tr>
                        <th>Shelf Life</th>
                        <td><input type="number" id="shelfLife" name="shelfLife" required></td>
                    </tr>
                    <tr>
                        <th>Brand</th>
                        <td><input type="text" id="brand" name="brand" required></td>
                    </tr>
                    <tr>
                        <th>Specifications / Descriptions</th>
                        <td><textarea id="specifications" name="specifications" rows="3" required></textarea></td>
                    </tr>
                    <tr>
                        <th>Range Category (for Computers)</th>
                        <td><input type="text" id="rangeCategory" name="rangeCategory"></td>
                    </tr>
                    <tr>
                        <th>Software Installed</th>
                        <td><input type="text" id="softwareInstalled" name="softwareInstalled"></td>
                    </tr>
                    <tr>
                        <th>Licensing Model</th>
                        <td><input type="text" id="licensingModel" name="licensingModel"></td>
                    </tr>
                    <tr>
                        <th>Serial Number</th>
                        <td><input type="text" id="serialNumber" name="serialNumber" required></td>
                    </tr>
                    <tr>
                        <th>Property Number</th>
                        <td><input type="text" id="propertyNumber" name="propertyNumber" required></td>
                    </tr>
                    <tr>
                        <th>Accountable Person</th>
                        <td><input type="text" id="accountablePerson" name="accountablePerson" required></td>
                    </tr>
                    <tr>
                        <th>Sex</th>
                        <td>
                            <select id="accountableSex" name="accountableSex">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Office / Division</th>
                        <td><input type="text" id="officeDivision" name="officeDivision" required></td>
                    </tr>
                    <tr>
                        <th>Status of Employment</th>
                        <td><input type="text" id="employmentStatus" name="employmentStatus" required></td>
                    </tr>
                    <tr>
                        <th>Actual User</th>
                        <td><input type="text" id="actualUser" name="actualUser" required></td>
                    </tr>
                    <tr>
                        <th>Sex</th>
                        <td>
                            <select id="actualUserSex" name="actualUserSex">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Status of Employment</th>
                        <td><input type="text" id="actualUserEmploymentStatus" name="actualUserEmploymentStatus" required></td>
                    </tr>
                    <tr>
                        <th>Nature of Work</th>
                        <td><input type="text" id="natureOfWork" name="natureOfWork" required></td>
                    </tr>
                    <tr>
                        <th>Remarks</th>
                        <td><textarea id="remarks" name="remarks" rows="3"></textarea></td>
                    </tr>
                </table>
                <div class="form-group">
                    <button type="submit">Submit</button>
                </div>
            </form>
        </div>
        <div class="graph-container">
            <h2>Graph Placeholder</h2>
            <canvas id="inventoryChart" width="400" height="400"></canvas>
        </div>
    </div>

    <!-- Include Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sample data for the graph
        const ctx = document.getElementById('inventoryChart').getContext('2d');
        const inventoryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['2020', '2021', '2022', '2023'],
                datasets: [{
                    label: 'Number of ICT Equipment Acquired',
                    data: [5, 10, 15, 20],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
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
    </script>
</body>
</html>
